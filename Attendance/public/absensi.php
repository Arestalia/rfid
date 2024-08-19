<?php

include  '../database/db.php';
session_start();
$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare('SELECT role FROM accounts WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role != "admin") {
    header("Location: list.php");
    exit;
}

$time = new DateTime("now", new DateTimeZone('Asia/Jakarta'));
$timeFormat = $time->format('H:i');
//$timeFormat = "16:01";
$dateFormat = $time->format('Y-m-d');
//$date = date_create("2024-08-10");
//$dateFormat = date_format($date, "Y/m/d");

$full_timeFormat = $time->format(('H:i:s'));
$full_dateFormat = $dateFormat . " " . $full_timeFormat;
//$full_dateFormat = $dateFormat . " 18:00:00";
$message = " ";

if (isset($_POST["submit"])) {
    $rfid = $_POST["rfid"];


    if (strlen($rfid) == 0 || !preg_match('/^\d{10}$/', $rfid)) {
        $message = "Please Masukan RFID Yang benar";
        clearMessage();
        return false;
    }


    // $sql_check_date = "SELECT * FROM absensi WHERE siswa_id = (SELECT id FROM siswa WHERE rfid = '$rfid') ORDER BY tanggal DESC LIMIT 2";
    // $sql_check_result = $conn->query($sql_check_date);

    $sql_check_rfid = "SELECT * FROM siswa WHERE rfid = ?";
    $stmt = $conn->prepare($sql_check_rfid);
    $stmt->bind_param("s", $rfid);
    $stmt->execute();

    $stmt->store_result();
    if ($stmt->num_rows > 0) {


        $sql_check_date = "SELECT * FROM absensi WHERE siswa_id = (SELECT id FROM siswa WHERE rfid = ?) ORDER BY tanggal DESC LIMIT 2";
        $stmt = $conn->prepare($sql_check_date);
        $stmt->bind_param('s', $rfid);
        $stmt->execute();

        $stmt->store_result();
        if ($stmt->num_rows < 2) {
            $sql_check_query = "SELECT siswa_id, waktu, tanggal, status FROM absensi WHERE siswa_id = (SELECT id FROM siswa WHERE rfid = ?) ORDER BY waktu DESC LIMIT 1";
            // $sql_check_result = $conn->query($sql_check_query);
            $stmt = $conn->prepare($sql_check_query);
            $stmt->bind_param('s', $rfid);
            $stmt->execute();

            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($siswa_id, $waktu, $tanggal, $status);
                $stmt->fetch();

                $last_status = $status;
                $last_time = $waktu;

                if ($tanggal != $dateFormat) {
                    // hari berbeda boleh absen
                    if ($timeFormat >= "06:00" && $timeFormat <= "07:30") {
                        $status = "masuk";
                    } else if ($timeFormat > "07:30" && $timeFormat < "16:00") {
                        $status = "telat";
                    } else if ($timeFormat >= "16:00" && $timeFormat <= "17:00") {
                        $status = "pulang";
                    } else if ($timeFormat > "17:00" && $timeFormat < "23:59") {
                        $status = "diluar jam absen";
                    } else {
                        $status = "diluar jam absen";
                    }

                    $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $stmt = $conn->prepare($sql_insert_query);
                    $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                    if ($stmt->execute()) {
                        $message = "Berhasil absen " . $status;
                        clearMessage();
                    } else {
                        echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert_query . "<br>" . $conn->error;
                    }
                }


                if ($last_status == "diluar jam absen") {

                    if ($last_time >= "16:00:00") {
                        $message = "Siswa sudah pernah absen pulang ";
                        clearMessage();
                    } else if ($timeFormat >= "16:00" && $timeFormat <= "17:00") {
                        $status = "pulang";

                        $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                        $stmt = $conn->prepare($sql_insert_query);
                        $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                        if ($stmt->execute()) {

                            $message = "Berhasil absen pulang";
                            clearMessage();
                        } else {
                            echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                        }
                    } else if ($timeFormat > "17:00" && $timeFormat < "23:00") {
                        $status = "diluar jam absen";

                        $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                        $stmt = $conn->prepare($sql_insert_query);
                        $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                        if ($stmt->execute()) {

                            $message = "Berhasil absen pulang";
                            clearMessage();
                        } else {
                            echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                        }
                    } else {
                        $message = "Siswa sudah pernah absen";
                        clearMessage();
                    }
                } else if ($last_status != 'pulang') {
                    if ($timeFormat >= "16:00" && $timeFormat <= "17:00") {
                        $status = "pulang";

                        $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                        $stmt = $conn->prepare($sql_insert_query);
                        $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                        if ($stmt->execute()) {
                            $message = "Berhasil absen pulang";
                            clearMessage();
                        } else {
                            echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                        }
                    } else if ($timeFormat > "17:00" && $timeFormat < "23:59") {
                        $status = "diluar jam absen";

                        $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                        $stmt = $conn->prepare($sql_insert_query);
                        $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                        if ($stmt->execute()) {

                            $message = "Berhasil absen pulang";
                            clearMessage();
                            echo '  
                                <script type="text/javascript">
                                    setTimeout(function() {
                                        window.location.href = "absensi.php";
                                    }, 500);
                                </script>
                            ';
                        } else {
                            echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                        }
                    } else {

                        $message = "Siswa sudah absen";
                        clearMessage();
                    }
                } else {
                    $message = "Anda sudah mengisi absen pulang ";
                    clearMessage();
                }
            } else {
                if ($timeFormat >= "06:00" && $timeFormat <= "07:30") {
                    $status = "masuk";

                    $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $stmt = $conn->prepare($sql_insert_query);
                    $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                    if ($stmt->execute()) {
                        $message = "Berhasil absen Masuk";
                        clearMessage();
                    } else {
                        echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                    }
                } else if ($timeFormat > "07:30" && $timeFormat < "16:00") {
                    $status = "telat";

                    $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $stmt = $conn->prepare($sql_insert_query);
                    $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                    if ($stmt->execute()) {
                        $message = "Berhasil absen Masuk Tetapi Telat";
                        clearMessage();
                    } else {
                        echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                    }
                } else if ($timeFormat >= "16:00" && $timeFormat <= "17:00") {
                    $statusMasuk = "telat";
                    $statusPulang = "pulang";

                    $sql_insert_query_masuk = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $sql_insert_query_pulang = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $stmt = $conn->prepare($sql_insert_query_pulang);
                    $stmt2 = $conn->prepare($sql_insert_query_masuk);

                    $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $statusPulang);
                    $stmt2->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $statusMasuk);

                    $stmt2->execute();
                    if ($stmt->execute()) {
                        $message = "Berhasil absen pulang";
                        clearMessage();
                    } else {
                        echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                    }
                } else if ($timeFormat > "17:00" && $timeFormat < "23:59") {
                    $statusMasuk = "telat";
                    $statusPulang = "diluar jam absen";

                    $sql_insert_query_masuk = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $sql_insert_query_pulang = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $stmt = $conn->prepare($sql_insert_query_pulang);
                    $stmt2 = $conn->prepare($sql_insert_query_masuk);

                    $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $statusPulang);
                    $stmt2->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $statusMasuk);

                    $stmt2->execute();
                    if ($stmt->execute()) {

                        $message = "Berhasil absen pulang";
                        clearMessage();
                    } else {
                        echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                    }
                } else {
                    $status = "diluar jam absen";

                    $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = ?), ?, ?, ?)";
                    $stmt = $conn->prepare($sql_insert_query);
                    $stmt->bind_param('ssss', $rfid, $full_timeFormat, $dateFormat, $status);

                    if ($stmt->execute()) {
                        $message = "Absen Berhasil tetapi diluar jam absen";
                        clearMessage();
                    } else {
                        echo "Awwww Ada Error Kang Tolong Hubungi Admin : " . $sql_insert . "<br>" . $conn->error;
                    }
                }
            }
        } else {
            $message = "Siswa sudah mengisi semua jadwal absen, jika belom silahkan hubungi Admin";
            clearMessage();
        }
    } else {
        $message = "Kartu ini tidak terdaftar, Tolong hubungi Admin";
        clearMessage();
    }
}
function clearMessage()
{
    echo '  
            <script type="text/javascript">
                setTimeout(function() {
                    var messageElement = document.getElementById("temporaryMessage");
                        if (messageElement) {
                            messageElement.remove();
                            document.getElementById("absenForm").reset();
                        }

                    }, 500);
            </script>
    ';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa</title>
    <link rel="stylesheet" href="css/absensi.css">
</head>

<body>
    <div class="absensi-container">
        <h1 class="title">ABSEN SISWA</h1>
        <p class="time"><?= $timeFormat ?></p>
        <p class="error" id="temporaryMessage"><?= $message ?></p>
        <div class=" section-check">
            <h1>Masuk</h1>
            <h1>Keluar</h1>
        </div>
        <form method="post" name="absenForm" class="absenForm" onsubmit="return validateRFID()">
            <div class="rfid-input">
                <img src="image/rfid-scanner-icon.webp" alt="Ini RFID">
                <input type="text" name="rfid" id="rfid" maxlength="10">
            </div>
            <button type="submit" name="submit" id="submit" class="btn-submit">Submit</button>
        </form>

    </div>

    <script src="assets/js/script.js"></script>
</body>

</html>