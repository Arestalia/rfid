<?php
session_start();
include "../database/db.php";
include "../admin/includes/header.php";
$database = new Database();
$conn = $database->getConnection();



$message = "";
if (isset($_POST["submit"])) {
    $nama = $_POST["nama"];
    $kelas = $_POST["kelas"];
    $rfid = $_POST["rfid"];
    $nisn = $_POST["nisn"];
    $sub_kelas = $_POST["jurusanKelas"];

    if (empty($nama) || empty($kelas) || empty($sub_kelas) || empty($nisn)) {
        $message = "Masukan Data yang benar";
    } else {
        $stmt = $conn->prepare("SELECT id FROM siswa WHERE nama LIKE ? AND kelas = ? AND jurusan = ? AND nisn = ?");
        $nama_like = '%' . $nama . '%';
        $stmt->bind_param("ssss", $nama_like, $kelas, $sub_kelas, $nisn);
        $stmt->execute();
        $stmt->bind_result($siswa_id);
        $result = $stmt->fetch();
        if (!$result) {
            $stmt->close();
            $sql_insert_data = "INSERT INTO siswa (nama, kelas, jurusan, nisn, rfid) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_insert_data);
            $stmt->bind_param("sssss", $nama, $kelas, $sub_kelas, $nisn, $rfid);
            if ($stmt->execute()) {
                $message = "Berhasil menambahkan data siswa";
            } else {
                $message = "Error: " . $conn->error;
            }
        } else {
            $message = "Siswa sudah ada di database";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Siswa</title>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>

<body>

    <p class="error" id="temporaryMessage"><?= $message ?></p>
    <div id=" addModal" class="modal-add">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-item">
                    <label for="namaSiswa">Nama</label>
                    <input type="text" name="nama" id="nama" required>
                </div>
                <div class="modal-item">
                    <label for="kelas">Kelas</label>
                    <select id="kelas" name="kelas" required>
                        <option value="">Semua</option>
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
                    </select>
                </div>

                <div class="modal-item">

                    <label for="jurusan">Jurusan</label>

                    <div class="jurusan-select">
                        <select id="jurusan" name="jurusan" required>
                            <option value="">Semua</option>
                            <option value="AKL">AKL</option>
                            <option value="PPLG">PPLG</option>
                            <option value="TKJ">TKJ</option>
                            <option value="DKV">DKV</option>
                            <option value="MPLB">MPLB</option>
                            <option value="ULW">ULW</option>
                            <option value="BDP">BDP</option>
                            <option value="TABUS">TABUS</option>
                            <option value="KULINER">KULINER</option>
                            <option value="PHT">PHT</option>
                        </select>
                        <select id="sub_kelas" name="jurusanKelas" required>
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="modal-item">
                        <label for="Nisn">NISN</label>
                        <input type="text" name="nisn" id="nisn" pattern="[0-9]*" minlength="10" maxlength="10" required>
                    </div>
                    <script>
                        document.getElementById('nisn').addEventListener('input', function(e) {
                            this.value = this.value.replace(/[^0-9]/g, '');
                        });
                    </script>
                </div>
                <div class="modal-item">
                    <label for="namaSiswa">RFID</label>
                    <input type="text" name="rfid" id="rfid" minlength="1" maxlength="10" required>
                </div>
                <script>
                    document.getElementById('rfid').addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                </script>
                <button type="submit" id="submit" name="submit" class="btn-submit">Tambahkan Data Siswa</button>
                <button type="button" id="kembali" name="kembali" class="btn-back"><a href="index.php">Kembali</a></button>

            </form>
        </div>
    </div>

    <script src="../assets/js/list.js"></script>
</body>

</html>
<?php
include "includes/footer.php";
?>