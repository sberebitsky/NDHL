<?php
// ===============================
// NDHL EASHL Stats Display
// ===============================

// --- Database connection info (update as needed) ---
$servername = "db.luddy.indiana.edu";
$username   = "i308f25_sambereb";
$password   = "nutty0606spuds";
$dbname     = "i308f25_sambereb";

// --- Connect to DB ---
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<p style='color:red;'>❌ Connection failed: " . $conn->connect_error . "</p>");
}

// --- Get all matches ---
$matches_sql = "
    SELECT m.match_id, 
           DATE_FORMAT(m.timestamp, '%Y-%m-%d %H:%i') AS match_time,
           m.home_club_id, m.away_club_id,
           m.home_score, m.away_score
    FROM eashl_matches AS m
    ORDER BY m.timestamp DESC
";
$matches = $conn->query($matches_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NDHL Match Stats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        h2 { margin-top: 40px; }
        table { background: #fff; }
        .match-header { background: #00274c; color: #fff; padding: 10px; border-radius: 6px; }
        .score { font-weight: bold; font-size: 1.3em; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Match Stats</h1>

    <?php while ($match = $matches->fetch_assoc()): 
        $match_id = $match['match_id'];

        // Fetch team stats for this match
        $teams_sql = "
            SELECT club_id, goals, shots, passes_completed, passes_attempted,
                   pp_goals, pp_opportunities, time_on_attack, result
            FROM eashl_team_stats
            WHERE match_id = '$match_id'
        ";
        $teams = $conn->query($teams_sql);

        // Fetch player stats for this match
        $players_sql = "
            SELECT player_name, club_id, position, goals, assists, shots, hits,
                   giveaways, takeaways, plus_minus, faceoffs_won, faceoffs_lost,
                   save_pct, goals_against, time_on_ice
            FROM eashl_player_stats
            WHERE match_id = '$match_id'
            ORDER BY club_id, position
        ";
        $players = $conn->query($players_sql);

        // Format score display
        $score_str = "{$match['home_score']} - {$match['away_score']}";
    ?>
    <div class="match-section mb-5">
        <div class="match-header mb-3">
            <h4>Match ID: <?= $match_id ?> | <?= $match['match_time'] ?></h4>
            <p class="score">Score: <?= $score_str ?></p>
        </div>

        <h5>🏆 Team Stats</h5>
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-dark">
                <tr>
                    <th>Club ID</th>
                    <th>Result</th>
                    <th>Goals</th>
                    <th>Shots</th>
                    <th>Passing %</th>
                    <th>Power Plays</th>
                    <th>Time on Attack (s)</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($team = $teams->fetch_assoc()): 
                $pass_pct = $team['passes_attempted'] > 0 ? 
                            round(($team['passes_completed'] / $team['passes_attempted']) * 100, 1) : 0;
                $pp_str = "{$team['pp_goals']} / {$team['pp_opportunities']}";
            ?>
                <tr>
                    <td><?= htmlspecialchars($team['club_id']) ?></td>
                    <td><?= htmlspecialchars($team['result']) ?></td>
                    <td><?= $team['goals'] ?></td>
                    <td><?= $team['shots'] ?></td>
                    <td><?= $pass_pct ?>%</td>
                    <td><?= $pp_str ?></td>
                    <td><?= $team['time_on_attack'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <h5>👤 Player Stats</h5>
        <table class="table table-hover table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>Player</th>
                    <th>Club ID</th>
                    <th>Pos</th>
                    <th>G</th>
                    <th>A</th>
                    <th>S</th>
                    <th>H</th>
                    <th>GV</th>
                    <th>TK</th>
                    <th>+/-</th>
                    <th>FO (W-L)</th>
                    <th>Save%</th>
                    <th>GA</th>
                    <th>TOI (s)</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $players->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['player_name']) ?></td>
                    <td><?= htmlspecialchars($p['club_id']) ?></td>
                    <td><?= htmlspecialchars($p['position']) ?></td>
                    <td><?= $p['goals'] ?></td>
                    <td><?= $p['assists'] ?></td>
                    <td><?= $p['shots'] ?></td>
                    <td><?= $p['hits'] ?></td>
                    <td><?= $p['giveaways'] ?></td>
                    <td><?= $p['takeaways'] ?></td>
                    <td><?= $p['plus_minus'] ?></td>
                    <td><?= $p['faceoffs_won'] ?> - <?= $p['faceoffs_lost'] ?></td>
                    <td><?= $p['save_pct'] ? number_format($p['save_pct'] * 100, 1) . "%" : "-" ?></td>
                    <td><?= $p['goals_against'] ?></td>
                    <td><?= $p['time_on_ice'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endwhile; ?>

</div>
</body>
</html>

<?php
$conn->close();
?>
