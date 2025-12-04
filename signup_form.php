<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'db.luddy.indiana.edu';
$db_user = 'i308f25_sambereb';
$db_pass = 'nutty0606spuds';
$db_name = 'i308f25_sambereb';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Collect and validate POST data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$psn = isset($_POST['psn']) ? trim($_POST['psn']) : '';
$position = isset($_POST['position']) ? trim($_POST['position']) : '';
$availability = isset($_POST['availability']) ? trim($_POST['availability']) : '';
$color = isset($_POST['color']) ? trim($_POST['color']) : '';

if ($name === '' || $psn === '') {
    die("Name and PSN are required.");
}

// Prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO signups (name, psn, position, availability, color) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("sssss", $name, $psn, $position, $availability, $color);

if ($stmt->execute()) {
    $to = "sambereb@iu.edu";
    $subject = "New Hockey League Signup";
    $message = "Name: $name\nPSN: $psn\nPosition: $position\nAvailability: $availability\nColor: $color";
    $headers = "From: noreply@yourdomain.edu";
    // send email to host
    @mail($to, $subject, $message, $headers);
    // Redirects to thank-you page
    header("Location: /~sambereb/NDHL/thank-you.html");
    exit;
} else {
    echo "Error saving signup: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>

