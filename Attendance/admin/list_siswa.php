<?php
include "../database/db.php";
include "../admin/includes/header.php";
session_start();

$database = new Database();
$conn = $database->getConnection();



// Pagination setup
$limit = 10; // Number of entries to show per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_POST['search']) ? $_POST['search'] : '';

// Prepare SQL for counting total records
$sqlCount = "SELECT COUNT(*) FROM siswa WHERE nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ? OR nisn LIKE ?";
$stmtCount = $conn->prepare($sqlCount);
$searchLike = "%$search%";
$stmtCount->bind_param('ssss', $searchLike, $searchLike, $searchLike, $searchLike);
$stmtCount->execute();
$stmtCount->bind_result($totalRecords);
$stmtCount->fetch();
$stmtCount->close();

// Prepare SQL for fetching paginated records
$sql = "SELECT * FROM siswa WHERE nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ? OR nisn LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssii', $searchLike, $searchLike, $searchLike, $searchLike, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa</title>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>

<body>
    <div class="navigation">
        <form method="post" action="list_siswa.php" class="search-form">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
        <a href="add.php" class="btn-add">Tambah Data Siswa</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>NISN</th>
                <th>RFID</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['kelas']) ?></td>
                    <td><?= htmlspecialchars($row['jurusan']) ?></td>
                    <td><?= htmlspecialchars($row['nisn']) ?></td>
                    <td><?= htmlspecialchars($row['rfid']) ?></td>
                    <td>
                        <!-- Form for edit and delete actions -->
                        <form method="post" action="edit.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <button type="submit" class="btn-edit">Edit</button>
                        </form>
                        <form method="post" action="delete_temp_data.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this item?');">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php
        $totalPages = ceil($totalRecords / $limit);
        for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="list_siswa.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php
    include "includes/footer.php";
    ?>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>