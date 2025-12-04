<?php
session_start();

// Check login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

$name = $_SESSION['name'];

// Database connection
$servername = "db.luddy.indiana.edu";
$username = "i308f25_sambereb";
$password = "nutty0606spuds";
$dbname = "i308f25_sambereb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Total signups
$sql = "SELECT COUNT(*) AS total FROM signups";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_signups = $row['total'];

// Position counts
$positions = ['Center', 'Winger', 'Defenseman', 'Goalie'];
$counts = [];

foreach ($positions as $pos) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM signups WHERE position = ?");
    $stmt->bind_param("s", $pos);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $counts[$pos] = $result['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      color: #fff;
      background: linear-gradient(135deg, #0f0f0f, #1c1c1c);
      text-align: center;
      min-height: 100vh;
    }

    h1 {
      font-family: 'Oswald', sans-serif;
      font-size: 3rem;
      margin-top: 60px;
      text-shadow: 2px 2px 5px #000;
    }

    h1 b {
      color: red;
    }

    /* Dashboard Grid */
    .dashboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      padding: 50px;
      width: 90%;
      margin: 0 auto;
    }

    .card {
      background: #111;
      border-radius: 15px;
      text-align: center;
      padding: 40px 20px;
      color: #fff;
      box-shadow: 0 4px 20px rgba(255, 0, 0, 0.2);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle at center, rgba(255, 0, 0, 0.25), transparent 70%);
      transform: rotate(45deg);
      opacity: 0;
      transition: opacity 0.4s ease;
    }

    .card:hover::before {
      opacity: 1;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 0 25px rgba(255, 0, 0, 0.4);
    }

    .card h2 {
      font-family: 'Oswald', sans-serif;
      font-size: 1.5rem;
      color: red;
      text-shadow: 0 0 10px red;
      margin-bottom: 10px;
    }

    .card p {
      font-size: 3rem;
      font-weight: bold;
      color: #fff;
      margin: 0;
      text-shadow: 0 0 10px rgba(255, 0, 0, 0.6);
    }

    /* Unique Card Colors */
    .card.total h2 { color: #ff3333; }
    .card.center h2 { color: #ff4444; }
    .card.winger h2 { color: #ff9900; }
    .card.defenseman h2 { color: #00c8ff; }
    .card.goalie h2 { color: #00ff9d; }

    /* Buttons */
    button {
      display: inline-block;
      margin: 40px 20px;
      padding: 15px 30px;
      border: none;
      border-radius: 5px;
      background-color: red;
      color: #fff;
      font-size: 1.2rem;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
    }

    button:hover {
      background-color: #ff4d4d;
      transform: scale(1.05);
    }

    footer {
      position: absolute;
      bottom: 20px;
      width: 100%;
      text-align: center;
      color: #666;
    }
  </style>
</head>
<body>

  <h1>Welcome, <b><?php echo htmlspecialchars($name); ?></b>!</h1>

  <div class="dashboard">
    <div class="card total">
      <h2>Total Signups</h2>
      <p id="total"><?php echo $total_signups; ?></p>
    </div>

    <div class="card center">
      <h2>Centers</h2>
      <p id="center"><?php echo $counts['Center']; ?></p>
    </div>

    <div class="card winger">
      <h2>Wingers</h2>
      <p id="winger"><?php echo $counts['Winger']; ?></p>
    </div>

    <div class="card defenseman">
      <h2>Defensemen</h2>
      <p id="defenseman"><?php echo $counts['Defenseman']; ?></p>
    </div>

    <div class="card goalie">
      <h2>Goalies</h2>
      <p id="goalie"><?php echo $counts['Goalie']; ?></p>
    </div>
  </div>

  <div>
    <button onclick="window.location.href='view_signups.php'">View Signups</button>
    <button onclick="window.location.href='draft.php'">Draft Night</button>
    <button onclick="window.location.href='logout.php'">Logout</button>
  </div>

  <script>
    // Animate counting effect for each card
    function animateValue(id, end) {
      const el = document.getElementById(id);
      let start = 0;
      const duration = 1000;
      const stepTime = Math.abs(Math.floor(duration / end));
      const timer = setInterval(() => {
        start += 1;
        el.textContent = start;
        if (start >= end) clearInterval(timer);
      }, stepTime);
    }

    animateValue("total", <?php echo $total_signups; ?>);
    animateValue("center", <?php echo $counts['Center']; ?>);
    animateValue("winger", <?php echo $counts['Winger']; ?>);
    animateValue("defenseman", <?php echo $counts['Defenseman']; ?>);
    animateValue("goalie", <?php echo $counts['Goalie']; ?>);
  </script>

</body>
</html>
