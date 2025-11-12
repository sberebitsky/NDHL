<?php
// --- load credentials from environment or a config file (recommended) ---
// For example, use getenv('DB_PASSWORD') instead of hardcoding.
$db_host = 'db.luddy.indiana.edu';
$db_user = 'i308f25_sambereb';
$db_pass = 'nutty0606spuds';
$db_name = 'i308f25_sambereb';

// Create connection (mysqli)
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Collect and validate POST data (basic)
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
    // Optional: send an email notification
    $to = "sambereb@iu.edu";
    $subject = "New Hockey League Signup";
    $message = "Name: $name\nPSN: $psn\nPosition: $position\nAvailability: $availability\nColor: $color";
    $headers = "From: noreply@yourdomain.edu";

    // send email (may require mail server config on the host)
    @mail($to, $subject, $message, $headers);

    // Redirect to a thank-you page (better UX)
    header("Location: /thank-you.html");
    exit;
} else {
    echo "Error saving signup: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

