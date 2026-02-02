<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_siswa = isset($_GET['siswa']) ? $_GET['siswa'] : '';

$where_clauses = array();

if ($role == 'siswa') {
    $where_clauses[] = "a.user_id = '$user_id'";
}

if ($filter_tanggal) {
    $where_clauses[] = "DATE(a.tanggal_pengaduan) = '$filter_tanggal'";
}

if ($filter_bulan) {
    $where_clauses[] = "DATE_FORMAT(a.tanggal_pengaduan, '%Y-%m') = '$filter_bulan'";
}

if ($filter_kategori) {
    $where_clauses[] = "a.kategori_id = '$filter_kategori'";
}

if ($filter_status) {
    $where_clauses[] = "a.status = '$filter_status'";
}

if ($filter_siswa && $role == 'admin') {
    $where_clauses[] = "a.user_id = '$filter_siswa'";
}

$where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

if ($role == 'admin') {
    $query = "SELECT a.*, u.nama_lengkap, u.kelas, k.nama_kategori 
              FROM aspirasi a 
              JOIN users u ON a.user_id = u.id 
              JOIN kategori k ON a.kategori_id = k.id 
              $where_sql
              ORDER BY a.tanggal_pengaduan DESC";
} else {
    $query = "SELECT a.*, k.nama_kategori 
              FROM aspirasi a 
              JOIN kategori k ON a.kategori_id = k.id 
              $where_sql
              ORDER BY a.tanggal_pengaduan DESC";
}

$result = mysqli_query($conn, $query);

$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

if ($role == 'admin') {
    $siswa = mysqli_query($conn, "SELECT id, nama_lengkap, kelas FROM users WHERE role='siswa' ORDER BY nama_lengkap");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Aspirasi - Aplikasi Pengaduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">


    <nav class="bg-indigo-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-school text-white text-2xl mr-3"></i>
                    <span class="text-white text-xl font-bold">Pengaduan Sekolah</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white">
                        <i class="fas fa-user-circle mr-2"></i>
                        <?php echo $_SESSION['nama_lengkap']; ?>
                        <span class="text-indigo-200 text-sm ml-2">(<?php echo ucfirst($role); ?>)</span>
                    </span>
                    <a href="logout.php"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="flex flex-wrap border-b">
                <a href="dashboard.php"
                    class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <?php if ($role == 'siswa'): ?>
                    <a href="form_aspirasi.php"
                        class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-plus-circle mr-2"></i>Buat Aspirasi
                    </a>
                <?php endif; ?>
                <a href="list_aspirasi.php"
                    class="px-6 py-4 text-indigo-600 border-b-2 border-indigo-600 font-semibold">
                    <i class="fas fa-list mr-2"></i>Daftar Aspirasi
                </a>
                <?php if ($role == 'admin'): ?>
                    <a href="laporan.php"
                        class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-chart-bar mr-2"></i>Laporan
                    </a>
                    <a href="kelola_user.php"
                        class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-users mr-2"></i>Kelola User
                    </a>
                    <a href="kelola_kategori.php"
                        class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-tags mr-2"></i>Kelola Kategori
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-clipboard-list mr-3 text-indigo-600"></i>Daftar Aspirasi
                </h1>
                <p class="text-gray-600 mt-1">Total: <?php echo mysqli_num_rows($result); ?> aspirasi</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-filter mr-2"></i>Filter Aspirasi
            </h3>

            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="tanggal" value="<?php echo $filter_tanggal; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <input type="month" name="bulan" value="<?php echo $filter_bulan; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="kategori"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Kategori</option>
                        <?php
                        mysqli_data_seek($kategori, 0);
                        while ($kat = mysqli_fetch_assoc($kategori)):
                            ?>
                            <option value="<?php echo $kat['id']; ?>" <?php echo $filter_kategori == $kat['id'] ? 'selected' : ''; ?>>
                                <?php echo $kat['nama_kategori']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending
                        </option>
                        <option value="diproses" <?php echo $filter_status == 'diproses' ? 'selected' : ''; ?>>Diproses
                        </option>
                        <option value="selesai" <?php echo $filter_status == 'selesai' ? 'selected' : ''; ?>>Selesai
                        </option>
                        <option value="ditolak" <?php echo $filter_status == 'ditolak' ? 'selected' : ''; ?>>Ditolak
                        </option>
                    </select>
                </div>

                <?php if ($role == 'admin'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Siswa</label>
                        <select name="siswa"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Siswa</option>
                            <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $filter_siswa == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo $s['nama_lengkap'] . ' (' . $s['kelas'] . ')'; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="list_aspirasi.php"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-indigo-600 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Tanggal</th>
                            <?php if ($role == 'admin'): ?>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Siswa</th>
                            <?php endif; ?>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Kategori</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Judul</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Lokasi</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Prioritas</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700"><?php echo $no++; ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        <?php echo date('d/m/Y', strtotime($row['tanggal_pengaduan'])); ?>
                                    </td>
                                    <?php if ($role == 'admin'): ?>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            <?php echo $row['nama_lengkap']; ?><br>
                                            <span class="text-xs text-gray-500"><?php echo $row['kelas']; ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            <?php echo $row['nama_kategori']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 max-w-xs">
                                        <?php echo substr($row['judul'], 0, 50) . (strlen($row['judul']) > 50 ? '...' : ''); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo $row['lokasi']; ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php
                                        $prioritas_colors = [
                                            'rendah' => 'bg-green-100 text-green-800',
                                            'sedang' => 'bg-yellow-100 text-yellow-800',
                                            'tinggi' => 'bg-red-100 text-red-800'
                                        ];
                                        ?>
                                        <span
                                            class="<?php echo $prioritas_colors[$row['prioritas']]; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                            <?php echo ucfirst($row['prioritas']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'diproses' => 'bg-blue-100 text-blue-800',
                                            'selesai' => 'bg-green-100 text-green-800',
                                            'ditolak' => 'bg-red-100 text-red-800'
                                        ];
                                        ?>
                                        <span
                                            class="<?php echo $status_colors[$row['status']]; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="detail_aspirasi.php?id=<?php echo $row['id']; ?>"
                                            class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $role == 'admin' ? '9' : '8'; ?>"
                                    class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Tidak ada data aspirasi</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>

</html>