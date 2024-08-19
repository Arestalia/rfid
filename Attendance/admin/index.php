<?php
include "../database/db.php";
session_start();
$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit;
}

$stmt = $conn->prepare('SELECT role FROM accounts WHERE id = ?');
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role != "admin") {
    header("Location: public/absensi.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>

<body>
    <?php
    include "includes/header.php";
    ?>
    <h2>Selamat datang admint</h2>
    <?php
    include "includes/footer.php";
    ?>
</body>

</html>
