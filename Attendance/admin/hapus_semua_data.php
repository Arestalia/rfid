<?php

$servername = "localhost";  // Nama host
$username = "root";         // Nama pengguna database
$password = "";             // Kata sandi pengguna
$dbname = "rfid";  // Nama database

// Membuat koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk menghapus semua data dari tabel
$sql = "DELETE FROM absensi";

if ($conn->query($sql) === TRUE) {
    echo "Semua data berhasil dihapus.";
} else {
    echo "Error: " . $conn->error;
}

// Tutup koneksi
$conn->close();

// Redirect kembali ke halaman sebelumnya
header("Location: list.php");
exit();
