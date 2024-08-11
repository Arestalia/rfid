<?php
include "database/db.php";
session_start();

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Memeriksa peran pengguna
$stmt = $conn->prepare('SELECT role FROM accounts WHERE id = ?');
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role != "admin") {
    header("Location: list.php");
    exit;
}

// Memeriksa apakah formulir telah dikirimkan
if (empty($_GET['nama']) || empty($_GET['tanggalSekarang']) || empty($_GET["kelas"]) || empty($_GET["jurusanKelas"]) || empty($_GET['waktuSekarang'])) {
    $_SESSION['message'] = 'Wajib isi semua kolom!';
    header('Location: list.php');
    exit;
}

$time = new DateTime("now", new DateTimeZone('Asia/Jakarta'));
$nama = $_GET['nama'];
$kelas = $_GET['kelas'];
$jurusan = $_GET['jurusanKelas'];
$tanggalGet = $_GET['tanggalSekarang'];
$waktuGet = $_GET['waktuSekarang'];

// Format waktu dan tanggal untuk disimpan ke database
$full_timeFormat = $time->format('H:i:s');
$dateFormat = $time->format('Y-m-d');

// Memeriksa apakah nama siswa ada di database
$stmt = $conn->prepare("SELECT id FROM siswa WHERE nama LIKE ? AND kelas = ? AND jurusan = ?");
$nama_like = '%' . $nama . '%';
$stmt->bind_param("sss", $nama_like, $kelas, $jurusan);
$stmt->execute();
$stmt->bind_result($siswa_id);

if ($stmt->fetch()) {
    $stmt->close();

    $sql_check_date = "SELECT * FROM absensi WHERE siswa_id = ? ORDER BY tanggal DESC LIMIT 2";
    $stmt = $conn->prepare($sql_check_date);
    $stmt->bind_param("i", $siswa_id);
    $stmt->execute();

    $stmt->store_result();
    if ($stmt->num_rows >= 2) {
        $_SESSION['message'] = "Siswa sudah pernah absen masuk dan pulang pada tanggal yang sama!";
        header('Location: list.php');
        exit;
    }
    $stmt->close();

    $sql_check_query = "SELECT waktu, tanggal, status FROM absensi WHERE siswa_id = ? ORDER BY waktu DESC LIMIT 1";
    $stmt = $conn->prepare($sql_check_query);
    $stmt->bind_param("i", $siswa_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($waktu, $tanggal, $status);
        $stmt->fetch();

        $last_status = $status;
        $last_time = $waktu;

        if ($last_status == "diluar jam absen") {
            if ($last_time >= "16:00:00") {
                $_SESSION['message'] = "Siswa sudah pernah absen pulang";
                header('Location: list.php');
                exit;
            } else if ($timeFormat > "17:00:00" && $timeFormat <= "23:00:00") {
                $status = "diluar jam absen";

                $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_insert_query);
                $stmt->bind_param('isss', $siswa_id, $full_timeFormat, $dateFormat, $status);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Berhasil absen pulang";
                    header('Location: list.php');
                    exit;
                } else {
                    echo "Awwww Ada Error Kang Tolong Hubungi Admin: " . $conn->error;
                }
            }
        } else if ($last_status != 'pulang') {
            if ($timeFormat >= "16:00:00" && $timeFormat <= "17:00:00") {
                $status = "pulang";

                $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_insert_query);
                $stmt->bind_param('isss', $siswa_id, $full_timeFormat, $dateFormat, $status);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Berhasil absen pulang";
                    header('Location: list.php');
                    exit;
                } else {
                    echo "Awwww Ada Error Kang Tolong Hubungi Admin: " . $conn->error;
                }
            } else if ($timeFormat > "17:00:00" && $timeFormat <= "23:59:00") {
                $status = "diluar jam absen";

                $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_insert_query);
                $stmt->bind_param('isss', $siswa_id, $full_timeFormat, $dateFormat, $status);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Berhasil absen pulang";
                    header('Location: list.php');
                    exit;
                } else {
                    echo "Awwww Ada Error Kang Tolong Hubungi Admin: " . $conn->error;
                }
            } else {
                $_SESSION['message'] = "Siswa sudah absen";
                header('Location: list.php');
                exit;
            }
        } else {
            $_SESSION['message'] = "Anda sudah mengisi absen pulang";
            header('Location: list.php');
            exit;
        }
        $stmt->close();
    } else {
        // Menentukan status berdasarkan waktu yang diinputkan
        $status = "";
        $time = strtotime($waktuGet);
        if ($time >= strtotime("06:00:00") && $time <= strtotime("07:30:00")) {
            $status = "masuk";
            $message = "Berhasil Mengabsenkan Siswa";
        } elseif ($time > strtotime("07:30:00") && $time < strtotime("16:00:00")) {
            $status = "telat";
            $message = "Berhasil absen Siswa telat";
        } elseif ($time >= strtotime("16:00:00") && $time <= strtotime("17:00:00")) {
            $status = "pulang";
            $message = "Berhasil absen Siswa pulang";
        } elseif ($time > strtotime("17:00:00") && $time <= strtotime("23:59:00")) {
            $statusMasuk = "telat";
            $statusPulang = "diluar jam absen";

            $sql_insert_query_masuk = "INSERT INTO absensi (siswa_id, tanggal, waktu, status) VALUES (?, ?, ?, ?)";
            $sql_insert_query_pulang = "INSERT INTO absensi (siswa_id, tanggal, waktu, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_insert_query_pulang);
            $stmt2 = $conn->prepare($sql_insert_query_masuk);

            $stmt->bind_param("isss", $siswa_id, $tanggalGet, $waktuGet, $statusPulang);
            $stmt2->bind_param("isss", $siswa_id, $tanggalGet, $waktuGet, $statusMasuk);

            $stmt2->execute();
            if ($stmt->execute()) {
                $_SESSION['message'] = "Berhasil absen pulang";
                header('Location: list.php');
                exit;
            } else {
                echo "Awwww Ada Error Kang Tolong Hubungi Admin: " . $conn->error;
            }
        } else {
            $status = "diluar jam absen";
            $message = "Absen Berhasil tetapi diluar jam absen Lohh";
        }

        // Menambahkan data ke tabel absensi
        $stmt = $conn->prepare("INSERT INTO absensi (siswa_id, tanggal, waktu, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $siswa_id, $tanggalGet, $waktuGet, $status);
        if ($stmt->execute()) {
            $_SESSION['message'] = $message;
            header('Location: list.php');
            exit;
        } else {
            $_SESSION['message'] = "Awwww Ada Error Kang Tolong Hubungi Admin: " . $conn->error;
            header('Location: list.php');
            exit;
        }
    }
} else {
    echo "Nama siswa tidak ditemukan.";
    $stmt->close();
}
