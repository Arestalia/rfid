<?php

include "database/db.php";
$database = new Database();
$conn = $database->getConnection();


$time = new DateTime("now", new DateTimeZone('Asia/Jakarta'));
// $timeFormat = $time->format('H:i');
$timeFormat = "10:01";
$dateFormat = $time->format('Y-m-d');
$full_timeFormat = $time->format(('H:i:s'));
// $full_dateFormat = $dateFormat . " " . $full_timeFormat;
$full_dateFormat = $dateFormat . " 18:00:00";
$message = "";

if (isset($_POST["submit"])) {
    $rfid = $_POST["rfid"];


    if (strlen($rfid) == 0) {
        $message = "Please Masukan RFID Yang benar";
        echo '
                        <script type="text/javascript">
                            setTimeout(function() {
                                window.location.href = "absensi.php";
                            }, 500);
                        </script>
                    ';
        exit();
    }

    $sql_check_date = "SELECT * FROM absensi WHERE siswa_id = (SELECT id FROM siswa WHERE rfid = '$rfid') ORDER BY tanggal DESC LIMIT 2";
    $sql_check_result = $conn->query($sql_check_date);

    if ($sql_check_result->num_rows < 2) {
        $sql_check_query = "SELECT * FROM absensi WHERE siswa_id = (SELECT id FROM siswa WHERE rfid = '$rfid') ORDER BY waktu DESC LIMIT 1";
        $sql_check_result = $conn->query($sql_check_query);

        var_dump($sql_check_result->fetch_assoc());
        echo '
        <script type="text/javascript">
            setTimeout(function() {
                window.location.href = "absensi.php";
            }, 5000);
        </script>
    ';
        if ($sql_check_result->num_rows > 0) {
            $last_absen = $sql_check_result->fetch_assoc();
            $last_status = $last_absen['status'];
            $last_time = $last_absen['waktu'];

            if ($last_status == "diluar jam absen") {
                if ($last_time >= "16:00:00") {
                    $message = "Siswa sudah pernah absen pulang ";
                    echo '
                            <script type="text/javascript">
                                setTimeout(function() {
                                    window.location.href = "absensi.php";
                                }, 500);
                            </script>
                        ';
                } else if ($timeFormat > "17:00" && $timeFormat < "23:00") {
                    $status = "diluar jam absen";

                    $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = '$rfid'), '$full_timeFormat', '$dateFormat', '$status')";
                    if ($conn->query($sql_insert_query) === true) {

                        $message = "Berhasil absen pulang";
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
                }
            } else if ($last_status != 'pulang') {
                if ($timeFormat >= "16:00" && $timeFormat <= "17:00") {
                    $status = "pulang";

                    $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = '$rfid'), '$full_timeFormat', '$dateFormat', '$status')";
                    if ($conn->query($sql_insert_query) === true) {
                        $message = "Berhasil absen pulang";
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
                } else if ($timeFormat > "17:00" && $timeFormat < "23:00") {
                    $status = "diluar jam absen";

                    $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = '$rfid'), '$full_timeFormat', '$dateFormat', '$status')";
                    if ($conn->query($sql_insert_query) === true) {

                        $message = "Berhasil absen pulang";
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

                    $message = "Sudah Jam lebih dari jam absen ";
                    echo '
                            <script type="text/javascript">
                                setTimeout(function() {
                                    window.location.href = "absensi.php";
                                }, 500);
                            </script>
                        ';
                }
            } else {
                echo "hllo";
                $message = "Anda sudah mengisi absen pulang ";
                echo '
                            <script type="text/javascript">
                                setTimeout(function() {
                                    window.location.href = "absensi.php";
                                }, 500);
                            </script>
                        ';
            }
        } else {
            if ($timeFormat >= '06:00' && $timeFormat <= '07:30') {
                $status = "masuk";

                $sql_insert_query = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = '$rfid'), '$full_timeFormat', '$dateFormat', '$status')";
                if ($conn->query($sql_insert_query) === true) {
                    $message = "Berhasil absen Masuk";
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
            } else if ($timeFormat >= "16:00" && $timeFormat <= "17:00") {
                $statusMasuk = "telat";
                $statusPulang = "pulang";

                $sql_insert_query_masuk = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = '$rfid'), '$full_timeFormat', '$dateFormat', '$statusMasuk')";
                $sql_insert_query_pulang = "INSERT INTO absensi (siswa_id, waktu, tanggal, status) VALUES ((SELECT id FROM siswa WHERE rfid = '$rfid'), '$full_timeFormat', '$dateFormat', '$statusPulang')";

                $result = $conn->query($sql_insert_query_masuk);
                if ($conn->query($sql_insert_query_pulang) === true) {
                    $message = "Berhasil absen pulang";
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
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa</title>
</head>

<body>
    <h1>Absensi Siswa</h1>
    <h1><?= $timeFormat ?></h1>
    <p><?= $message ?></p>
    <form method="post" name="absenFOrm" onsubmit="return validateRFID()">
        RFID: <input type="text" name="rfid" id="rfid" maxlength="10"><br>
        <button type="submit" name="submit" id="submit">Submit</button>
    </form>

    <script src="assets/script.js"></script>
</body>

</html>