<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek jika tombol delete diklik
if (isset($_POST['id'])) {
    $id = $_POST['id'];


    // delete siswa pada abseni
    $sql = "DELETE FROM absensi WHERE siswa_id = '$id'";
    $conn->query($sql);

    // Query untuk menghapus data
    $sql = "DELETE FROM siswa WHERE id = '$id'";

    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        echo "Data berhasil dihapus";
        header('Location: list_siswa.php'); // redirect to the list page
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Tutup koneksi
$conn->close();
