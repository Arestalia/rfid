<?php
include "../database/db.php";
session_start();

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare('SELECT role FROM accounts WHERE id = ?');
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

$delete_old_data_sql = "DELETE FROM absensi WHERE tanggal < DATE_SUB(NOW(), INTERVAL 31 DAY)";
$conn->query($delete_old_data_sql);

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filter setup
$filters = [];
$filter_sql = "";

if (!empty($_GET['kelas'])) {
    $filters[] = "siswa.kelas = ?";
}
if (!empty($_GET['jurusan'])) {
    $filters[] = "siswa.jurusan = ?";
}
if (!empty($_GET['tanggal'])) {
    $filters[] = "absensi.tanggal = ?";
}
if (!empty($_GET['waktu'])) {
    $filters[] = "absensi.waktu = ?";
}
if (!empty($_GET['status'])) {
    $filters[] = "absensi.status = ?";
}
if (!empty($_GET["search"])) {
    $filters[] = "siswa.nama LIKE ?";
}

if (count($filters) > 0) {
    $filter_sql = "WHERE " . implode(" AND ", $filters);
}

// Get total number of records for pagination
$sql_count = "SELECT COUNT(*) FROM absensi 
              INNER JOIN siswa ON absensi.siswa_id = siswa.id $filter_sql";
$stmt_count = $conn->prepare($sql_count);

$bind_types = "";
$bind_values = [];

if (!empty($_GET['kelas'])) {
    $bind_types .= "s";
    $bind_values[] = $_GET['kelas'];
}
if (!empty($_GET['jurusan'])) {
    $bind_types .= "s";
    $bind_values[] = $_GET['jurusan'];
}
if (!empty($_GET['tanggal'])) {
    $bind_types .= "s";
    $bind_values[] = $_GET['tanggal'];
}
if (!empty($_GET['waktu'])) {
    $bind_types .= "s";
    $bind_values[] = $_GET['waktu'];
}
if (!empty($_GET['status'])) {
    $bind_types .= "s";
    $bind_values[] = $_GET['status'];
}
if (!empty($_GET['search'])) {
    $bind_types .= "s";
    $bind_values[] = '%' . $_GET['search'] . '%';
}

// Bind parameters for the count query
if (!empty($bind_types)) {
    $stmt_count->bind_param($bind_types, ...$bind_values);
}

$stmt_count->execute();
$stmt_count->bind_result($total_records);
$stmt_count->fetch();
$stmt_count->close();

$total_pages = ceil($total_records / $records_per_page);

// Main query with limit and offset for pagination
$sql_get_all = "SELECT absensi.siswa_id, absensi.waktu, absensi.tanggal, absensi.status, 
                siswa.nama, siswa.nisn, siswa.kelas, siswa.jurusan 
                FROM absensi 
                INNER JOIN siswa ON absensi.siswa_id = siswa.id $filter_sql
                LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql_get_all);

$bind_types .= "ii";
$bind_values[] = $records_per_page;
$bind_values[] = $offset;

// Bind all parameters
if (!empty($bind_types)) {
    $stmt->bind_param($bind_types, ...$bind_values);
}

$stmt->execute();

// Store result to use num_rows
$stmt->store_result();

$hasData = $stmt->num_rows > 0;

if ($hasData) {
    // Bind the result variables
    $stmt->bind_result($siswa_id, $waktu, $tanggal, $status, $nama, $nisn, $kelas, $jurusan);
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Home Page</title>
    <link rel="stylesheet" href="../assets/css/list.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.6.0/css/all.css">
</head>

<body class="loggedin">
    <div class="list-container">
        <div class="list-search">
            <form>
                <input type="text" name="search" id="search" placeholder="Cari nama siswa">
                <button type="submit">Cari</button>
            </form>

            <div></div>
            <div class="filter-section">
                <?php if ($role === 'admin') { ?>
                    <button type="button" class="tambah-siswa" onclick="toggleModalAdd()">Tambah Siswa RFID Ketinggalan</button>

                <?php } ?>
                <a href="ekspor.php">
                    <button type="button" class="expor-data">Export Data ke Excel</button>
                </a>

                <form action="hapus_semua_data.php" method="post" onsubmit="return confirm('Apakah kamu yakin ingin menghapus semua data?');">
                    <button type="submit" class="hapus-semua-data">Hapus Semua Data</button>
                </form>

                <button type="button" class="filter" onclick="toggleModal()">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </div>


        <?php if (isset($_SESSION['message'])) : ?>
            <div id="message" class="message">
                <p><?= htmlspecialchars($_SESSION['message']); ?></p>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class=" table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nisn</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($hasData) : ?>
                        <?php while ($stmt->fetch()) : ?>
                            <tr>
                                <td><?= htmlspecialchars($nisn) ?></td>
                                <td><?= htmlspecialchars($nama) ?></td>
                                <td><?= htmlspecialchars($kelas) ?></td>
                                <td><?= htmlspecialchars($jurusan) ?></td>
                                <td><?= htmlspecialchars($tanggal) ?></td>
                                <td><?= htmlspecialchars($waktu) ?></td>
                                <td><?= htmlspecialchars($status) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="empty-message">Data Kosong</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page=<?= $page - 1 ?>" class="btn-prev">Previous</a>
            <?php else : ?>
                <a class="btn-prev disabled">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <?php if ($i == $page) : ?>
                    <strong><?= $i ?></strong>
                <?php else : ?>
                    <a class="page" href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages) : ?>
                <a href="?page=<?= $page + 1 ?>" class="btn-next">Next</a>
            <?php else : ?>
                <a class="btn-next disabled">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <div id="addModal" class="modal-add">
        <div class="modal-content">
            <span class="close" onclick="toggleModalAdd()">&times;</span>
            <form method="GET" action="input_siswa_telat.php">
                <div class="modal-item">
                    <label for="namaSiswa">Nama</label>
                    <input type="text" name="nama" id="nama">
                </div>
                <div class="modal-item">
                    <label for="kelas">Kelas</label>
                    <select id="kelas" name="kelas">
                        <option value="">Semua</option>
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
                    </select>
                </div>

                <div class="modal-item">

                    <label for="jurusan">Jurusan</label>

                    <div class="jurusan-select">
                        <select id="jurusan" name="jurusan">
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
                        <select id="sub_kelas" name="jurusanKelas">
                            <option value="">Semua</option>
                        </select>
                    </div>
                </div>

                <div class="modal-item">
                    <label for="tanggalSekarang">Tanggal</label>
                    <input class="tanggal" type="date" id="tanggalSekarang" name="tanggalSekarang">
                </div>

                <div class="modal-item">
                    <label for="waktuSekarang">Waktu</label>
                    <input class="waktu" type="time" id="waktuSekarang" name="waktuSekarang">
                </div>
                <button type="submit" class="btn-submit">Absenkan Siswa</button>

            </form>
        </div>
    </div>
    <div id="filterModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="toggleModal()">&times;</span>
            <form method="GET">
                <div class="modal-item">
                    <label for="kelas">Kelas</label>
                    <select id="kelas" name="kelas">
                        <option value="">Semua</option>
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
                    </select>
                </div>

                <div class="modal-item">

                    <label for="jurusan">Jurusan</label>

                    <div class="jurusan-select">
                        <select id="jurusan" name="jurusan">
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
                        <select id="sub_kelas" name="jurusan">
                            <option value="">Semua</option>
                            <!-- Options will be populated dynamically by JavaScript -->
                        </select>
                    </div>
                </div>

                <div class="modal-item">
                    <label for="tanggal">Tanggal</label>
                    <input class="tanggal" type="date" id="tanggal" name="tanggal">
                </div>

                <div class="modal-item">
                    <label for="waktu">Waktu</label>
                    <input class="waktu" type="time" id="waktu" name="waktu">
                </div>

                <div class="modal-item">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        <option value="masuk">masuk</option>
                        <option value="telat">telat</option>
                        <option value="pulang">pulang</option>
                        <option value="diluar jam absen">diluar jam absen</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Terapkan Filter</button>

            </form>
        </div>
    </div>
    <script src="../assets/js/list.js"></script>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>