<?php
session_start();

$servername = "db.luddy.indiana.edu";
$username = "i308f25_sambereb";
$password = "nutty0606spuds";
$dbname = "i308f25_sambereb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$teams = [
    "Kansas City Scouts",
    "Boston Bruins",
    "Ottawa Senators",
    "Quebec Nordiques",
    "Edmonton Oilers",
    "Hartford Whalers"
];

$selected = $_GET['team'] ?? '';
$selected = in_array($selected, $teams) ? $selected : '';

$roster = [];
if ($selected !== '') {
    $stmt = $conn->prepare("
        SELECT s.psn, s.name, s.position, s.color, s.submitted_at, r.drafted_at
        FROM team_roster r
        JOIN signups s ON r.player_id = s.id
        WHERE r.team = ?
        ORDER BY r.drafted_at ASC
    ");
    $stmt->bind_param("s", $selected);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $roster[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Rosters</title>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto&display=swap" rel="stylesheet">
  <style>
    body { 
      font-family: 'Roboto', sans-serif; 
      background: linear-gradient(135deg,#0f0f0f,#1c1c1c); 
      color: #fff; 
      margin: 0; 
      padding: 30px; 
    }
    .container { 
      width: 90%; 
      max-width: 960px; 
      margin: 0 auto; 
    }
    header { 
      text-align: center; 
      margin-bottom: 24px; 
    }
    header h1 { 
      font-family: 'Oswald',sans-serif; 
      color: white; 
      margin:0; 
    }
    b {
      color: red;
    }

    .panel { 
      background: #111; 
      padding: 18px; 
      border-radius: 10px; 
      border: 1px solid #222; 
      box-shadow: 0 6px 18px rgba(0,0,0,0.6); 
    }
    select { 
      width: 100%; 
      padding: 10px; 
      border-radius: 6px;
      background: #0b0b0b; 
      color: #fff; 
      border: 1px solid #333; 
    }
    table { 
      width: 100%; 
      margin-top: 16px; 
      border-collapse: collapse; 
    }
    th, td { 
      padding: 10px; 
      text-align: left; 
      border-bottom: 1px solid #222; 
    }
    th { 
      background:rgba(255,0,0,0.12); 
      color:#fff; 
    }
    .none { 
      padding:20px; 
      color:#bbb; 
      text-align:center; 
    }
    a.back { 
      display:inline-block; 
      margin-top:12px; 
      color:#fff; 
      background:#222; 
      padding:8px 12px; 
      text-decoration:none; 
      border-radius:6px; 
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Team <b>Rosters</b></h1>
      <p style="color:#bbb;">Select a team to view its drafted players.</p>
    </header>

    <div class="panel">
      <form method="get" action="rosters.php">
        <label for="team">Team</label>
        <select id="team" name="team" onchange="this.form.submit()">
          <option value="">-- Choose a team --</option>
          <?php foreach ($teams as $t): ?>
            <option value="<?php echo htmlspecialchars($t); ?>" <?php if ($selected === $t) echo 'selected'; ?>>
              <?php echo htmlspecialchars($t); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if ($selected === ''): ?>
        <div class="none">No team selected. Pick a team from the dropdown above.</div>
      <?php else: ?>
        <h2 style="margin-top:18px;"><?php echo htmlspecialchars($selected); ?> — Roster</h2>

        <?php if (count($roster) === 0): ?>
          <div class="none">No players have been drafted to this team yet.</div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>PSN</th>
                <th>Name</th>
                <th>Position</th>
                <th>Color</th>
                <th>Signed At</th>
                <th>Drafted At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($roster as $p): ?>
                <tr>
                  <td><?php echo htmlspecialchars($p['psn']); ?></td>
                  <td><?php echo htmlspecialchars($p['name']); ?></td>
                  <td><?php echo htmlspecialchars($p['position']); ?></td>
                  <td><?php echo htmlspecialchars($p['color']); ?></td>
                  <td><?php echo htmlspecialchars($p['submitted_at']); ?></td>
                  <td><?php echo htmlspecialchars($p['drafted_at']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      <?php endif; ?>
      <a class="back" href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/index.html">Return to Home</a>
    </div>
  </div>
</body>
</html>
