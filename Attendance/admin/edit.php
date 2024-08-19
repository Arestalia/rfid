<?php
include "../database/db.php";
include "../admin/includes/header.php";
session_start();

$database = new Database();
$conn = $database->getConnection();



$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id > 0) {
    if ($stmt = $conn->prepare('SELECT * FROM siswa WHERE id = ?')) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $siswa = $result->fetch_assoc();
        $stmt->close();
    }
}

if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    $nisn = $_POST['nisn'];
    $rfid = $_POST['rfid'];

    if ($stmt = $conn->prepare('UPDATE siswa SET nama = ?, kelas = ?, jurusan = ?, nisn = ?, rfid = ? WHERE id = ?')) {
        $stmt->bind_param('sssssi', $nama, $kelas, $jurusan, $nisn, $rfid, $id);
        if ($stmt->execute()) {
            header('Location: list_siswa.php');
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
    <link rel="stylesheet" href="assets/css/edit.css">
</head>

<body>
    <div class="form-container">
        <h1>Edit Data Siswa</h1>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($siswa['id']) ?>">
            <label for="nama">Nama:</label>
            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($siswa['nama']) ?>" required>
            <label for="kelas">Kelas:</label>
            <input type="text" name="kelas" id="kelas" value="<?= htmlspecialchars($siswa['kelas']) ?>" required>
            <label for="jurusan">Jurusan:</label>
            <input type="text" name="jurusan" id="jurusan" value="<?= htmlspecialchars($siswa['jurusan']) ?>" required>
            <label for="nisn">NISN:</label>
            <input type="text" name="nisn" id="nisn" value="<?= htmlspecialchars($siswa['nisn']) ?>" required>
            <label for="rfid">RFID:</label>
            <input type="text" name="rfid" id="rfid" value="<?= htmlspecialchars($siswa['rfid']) ?>" required>
            <button type="submit" name="update">Update</button>
            <a href="list_siswa.php" class="btn-back">Kembali ke List</a>
        </form>
    </div>
    <?php
    include "includes/footer.php";
    ?>
</body>

</html>