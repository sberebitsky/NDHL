<?php
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

$sql = "SELECT * FROM signups"; 
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Signups</title>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      color: #fff;
      background: linear-gradient(135deg, #0f0f0f, #1c1c1c);
    }
    h1 {
      text-align: center;
      font-family: 'Oswald', sans-serif;
      font-size: 3rem;
      margin: 40px 0;
      text-shadow: 2px 2px 5px #000;
    }
    table {
      width: 80%;
      margin: 40px auto;
      border-collapse: collapse;
      box-shadow: 0 4px 10px rgba(0,0,0,0.6);
    }
    th, td {
      border: 1px solid #fff;
      padding: 15px;
      text-align: center;
    }
    th {
      background-color: red;
    }
    tr:nth-child(even) {
      background-color: #222;
    }
    tr:nth-child(odd) {
      background-color: #333;
    }
    button {
      display: block;
      margin: 20px auto;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      background-color: red;
      color: #fff;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background-color: #ff4d4d;
    }
    /* Navbar */
    nav {
        background: #000;
        display: flex;
        justify-content: center;
        padding: 20px 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.7);
    }

    .dropdown-menu {
        list-style: none;
        display: flex;
        margin: 0;
        padding: 0;
    }

    .dropdown-menu > li {
        position: relative;
        margin: 0 20px;
    }

    .dropdown-menu > li > a {
        text-decoration: none;
        color: #fff;
        font-size: 1.5rem;
        transition: color 0.3s;
    }

    .dropdown-menu > li > a:hover {
        color: red;
    }

    .submenu {
        position: absolute;
        top: 100%;
        left: 0;
        background: red;
        list-style: none;
        margin: 0;
        padding: 0;
        display: none;
        border-radius: 5px;
    }

    .submenu li a {
        display: block;
        color: #fff;
        padding: 10px 20px;
        text-decoration: none;
        transition: background 0.3s;
    }

    .submenu li a:hover {
        background: #000;
    }

    .dropdown-menu > li:hover .submenu {
        display: block;
    }
  </style>
</head>
<body>
<!-- Navbar -->
<nav>
    <ul class="dropdown-menu">
        <li><a href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/index.html">Home</a></li>
        <li>
            <a href="#">Explore</a>
            <ul class="submenu">
                <li><a href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/AboutUs.html">About Us</a></li>
                <li><a href="https://gofund.me/096d9da3">Donations</a></li>
                <li><a href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/ContactUs.html">Contact</a></li>
            </ul>
        </li>
        <li>
            <a href="#">The League</a>
            <ul class="submenu">
                <li><a href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/LeagueInfo.html">League Info</a></li>
                <li><a href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/LeagueStandings.html">League Standings</a></li>
                <li><a href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/Rosters.html">Rosters</a></li>
            </ul>
        </li>
        <li><a href="https://www.youtube.com/@nxnosamo3367">Subscribe</a></li>
        <li><a href="https://discord.gg/b6vMyJ5pmw">Discord</a></li>
        <li><a href="https://cgi.luddy.indiana.edu/~sambereb/NDHL/login.html">Admin Login</a></li>
    </ul>
</nav>

<h1><b>Signups</b> Overview</h1>

<table>
  <tr>
    <?php
    if ($result->num_rows > 0) {
        // Print column headers
        while ($fieldinfo = $result->fetch_field()) {
            echo "<th>{$fieldinfo->name}</th>";
        }
        echo "</tr>";

        // Print data rows
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<td colspan='5'>No signups found</td>";
    }
    ?>
</table>

<button onclick="window.location.href='logout.php'">Logout</button>

</body>
</html>
<?php $conn->close(); ?>
