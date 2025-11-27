<?php
// draft_player.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo "ERROR: Not authenticated.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "ERROR: Invalid request method.";
    exit;
}

$psn = trim($_POST['psn'] ?? '');
$team = trim($_POST['team'] ?? '');

if ($psn === '' || $team === '') {
    echo "ERROR: Missing PSN or Team.";
    exit;
}

$servername = "db.luddy.indiana.edu";
$username = "i308f25_sambereb";
$password = "nutty0606spuds";
$dbname = "i308f25_sambereb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "ERROR: DB connection failed.";
    exit;
}

// 1) Find player by exact PSN
$stmt = $conn->prepare("SELECT id, name FROM signups WHERE psn = ?");
if (!$stmt) {
    echo "ERROR: Prepare failed.";
    exit;
}
$stmt->bind_param("s", $psn);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "ERROR: PSN not found: " . htmlspecialchars($psn);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $res->fetch_assoc();
$player_id = (int) $row['id'];
$player_name = $row['name'];
$stmt->close();

// 2) Check if already drafted
$chk = $conn->prepare("SELECT id, team, drafted_at FROM team_roster WHERE player_id = ?");
$chk->bind_param("i", $player_id);
$chk->execute();
$chkres = $chk->get_result();
if ($chkres->num_rows > 0) {
    $existing = $chkres->fetch_assoc();
    echo "ERROR: Player already drafted to {$existing['team']} on {$existing['drafted_at']}.";
    $chk->close();
    $conn->close();
    exit;
}
$chk->close();

// 3) Insert into team_roster
$ins = $conn->prepare("INSERT INTO team_roster (player_id, team) VALUES (?, ?)");
if (!$ins) {
    echo "ERROR: Prepare failed (insert).";
    $conn->close();
    exit;
}
$ins->bind_param("is", $player_id, $team);
$ok = $ins->execute();

if ($ok) {
    echo "SUCCESS: " . htmlspecialchars($psn) . " ({$player_name}) drafted to " . htmlspecialchars($team) . ".";
} else {
    // if UNIQUE constraint prevents insert (already drafted concurrently), show message
    if ($conn->errno === 1062) {
        echo "ERROR: Player already drafted (duplicate).";
    } else {
        echo "ERROR: Database insert failed.";
    }
}

$ins->close();
$conn->close();
