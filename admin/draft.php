<?php
// draft.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

$servername = "db.luddy.indiana.edu";
$username = "i308f25_sambereb";
$password = "nutty0606spuds";
$dbname = "i308f25_sambereb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Team list (as provided)
$teams = [
    "Kansas City Scouts",
    "Boston Bruins",
    "Ottawa Senators",
    "Quebec Nordiques",
    "Edmonton Oilers",
    "Hartford Whalers"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Draft Night — Terminal</title>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto&display=swap" rel="stylesheet">
  <style>
    body { 
      font-family: 'Roboto', monospace; 
      background: linear-gradient(135deg,#0f0f0f,#1c1c1c); 
      color:#fff; 
      padding:30px; 
    }
    .terminal { 
      width: 720px; 
      margin: 30px auto; 
      background:#0b0b0b; 
      border-radius:10px; 
      box-shadow:0 6px 20px rgba(0,0,0,0.7); 
      padding:20px; border:1px solid #2b2b2b; 
    }
    h1 { 
      font-family: 'Oswald', sans-serif; 
      color: white; 
      text-align:center; 
      margin:0 0 18px 0; 
    }
    b {
      color: red;
    }
    label { 
      display:block; 
      margin:12px 0 6px; 
      color:#ddd; 
    }
    input[type="text"], select { 
      width:100%; 
      padding:10px; 
      background:#111; 
      border:1px solid #333; 
      color:#fff; 
      border-radius:6px; 
      box-sizing:border-box; 
    }
    .line { 
      display:flex;
      gap:10px; 
      margin-top:12px; 
    }
    .line input, .line select 
    { flex:1; 
    }
    button { 
      margin-top:14px; 
      padding:10px 18px; 
      background:red; 
      color:#fff; 
      border:none; 
      border-radius:6px; 
      cursor:pointer; 
      font-weight:700; 
    }
    #log { 
      margin-top:18px; 
      height:220px; 
      overflow:auto; 
      background:#070707; 
      padding:12px; 
      border-radius:6px; 
      border:1px solid #222; 
      color:#0f0; 
      font-family:monospace; 
      white-space:pre-wrap; 
    }
    .small { 
      font-size:0.9rem; 
      color:#bbb; 
      margin-top:8px; 
    }
    a.btn-back { 
      display:inline-block; 
      margin-top:12px; color:#fff; 
      background:#222; 
      padding:8px 12px; 
      text-decoration:none; 
      border-radius:6px; 
    }
  </style>
</head>
<body>
  <div class="terminal">
    <h1>Draft Night <b>Terminal</b></h1>
    <p class="small">Type the EXACT PSN (case-sensitive) and choose the Team. Then press Submit.</p>

    <form id="draftForm">
      <label for="psn">Player PSN</label>
      <input type="text" id="psn" name="psn" placeholder="Enter EXACT PSN (e.g. JohnDoe123)" required autocomplete="on">

      <label for="team">Select Team</label>
      <select id="team" name="team" required>
        <option value="" disabled selected>-- Choose team --</option>
        <?php foreach ($teams as $t): ?>
          <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
        <?php endforeach; ?>
      </select>

      <div class="line">
        <button type="submit">Submit Draft</button>
        <a class="btn-back" href="admin_home.php">Back to Admin</a>
      </div>
    </form>

    <div id="log" aria-live="polite"></div>
  </div>

<script>
(function(){
  const form = document.getElementById('draftForm');
  const log = document.getElementById('log');

  function appendLog(text){
    const now = new Date().toLocaleTimeString();
    log.textContent = `[${now}] ${text}\n` + log.textContent;
  }

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    const psn = document.getElementById('psn').value.trim();
    const team = document.getElementById('team').value;

    if (!psn || !team) {
      appendLog('ERROR: PSN and Team are required.');
      return;
    }

    appendLog(`Attempting to draft ${psn} → ${team} ...`);

    try {
      const body = new URLSearchParams();
      body.append('psn', psn);
      body.append('team', team);

      const resp = await fetch('/~sambereb/NDHL/draft_player.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
      });

      const text = await resp.text();
      appendLog(text);
    } catch (err) {
      appendLog('ERROR: Network or server error: ' + err.message);
    }

    // Keep the PSN field and allow multiple quick drafts; optionally clear it:
    document.getElementById('psn').value = '';
    document.getElementById('psn').focus();
  });
})();
</script>
</body>
</html>
