<?php
session_start();

// Your credentials
$sam_username = "bitsky66";
$sam_password = "nxnosamo";
$peyton_username = "Pxyrxn";
$peyton_password = "ndhl2026";
$ashton_username = "Soupy";
$ashton_password = "ndhl2026";
$griffon_username = "Gman";
$griffon_password = "ndhl2026";

// Get input from form
$username = $_POST['username'];
$password = $_POST['password'];

// Check credentials
if ($username === $sam_username && $password === $sam_password) {
    $_SESSION['loggedin'] = true;
    $_SESSION['name'] = "Sam";
    header("Location: admin/admin_home.php");
    exit;
} elseif ($username === $peyton_username && $password === $peyton_password) {
    $_SESSION['loggedin'] = true;
    $_SESSION['name'] = "Peyton";
    header("Location: admin/admin_home.php");
    exit;
} elseif ($username === $ashton_username && $password === $ashton_password) {
    $_SESSION['loggedin'] = true;
    $_SESSION['name'] = "Ashton";
    header("Location: admin/admin_home.php");
    exit;
} elseif ($username === $griffon_username && $password === $griffon_password) {
    $_SESSION['loggedin'] = true;
    $_SESSION['name'] = "Griffon";
    header("Location: admin/admin_home.php");
    exit;
} else {
    echo "<script>alert('Invalid credentials.'); window.location.href='login.html';</script>";
    exit;
}
?>
