<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$page_title = 'Daftar Aspirasi';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get filters
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_prioritas = isset($_GET['prioritas']) ? $_GET['prioritas'] : '';
$filter_siswa = isset($_GET['siswa']) ? $_GET['siswa'] : '';

$where_clauses = array();

if ($role == 'siswa') {
    $where_clauses[] = "a.user_id = '$user_id'";
}

if ($filter_tanggal) {
    $where_clauses[] = "DATE(a.tanggal_pengaduan) = '$filter_tanggal'";
}

if ($filter_bulan) {
    $where_clauses[] = "MONTH(a.tanggal_pengaduan) = '$filter_bulan'";
}

if ($filter_kategori) {
    $where_clauses[] = "a.kategori_id = '$filter_kategori'";
}

if ($filter_status) {
    $where_clauses[] = "a.status = '$filter_status'";
}

if ($filter_prioritas) {
    $where_clauses[] = "a.prioritas = '$filter_prioritas'";
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

include 'includes/header.php';
?>

<div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-0">
            <i class="fas fa-filter mr-2"></i>Filter Data
        </h2>
        <?php if ($role == 'siswa'): ?>
        <a href="form_aspirasi.php" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 text-center text-sm sm:text-base">
            <i class="fas fa-plus-circle mr-2"></i>Buat Aspirasi Baru
        </a>
        <?php endif; ?>
    </div>

    <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm">
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
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm">
                <option value="">Semua Status</option>
                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="diproses" <?php echo $filter_status == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                <option value="selesai" <?php echo $filter_status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                <option value="ditolak" <?php echo $filter_status == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>

        <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Bulan</label>
            <select name="bulan" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm">
                <option value="">Semua Bulan</option>
                <?php
                $bulan_list = [
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember'
                ];
                foreach ($bulan_list as $key => $nama_bulan) {
                    $selected = ($filter_bulan == $key) ? 'selected' : '';
                    echo "<option value='$key' $selected>$nama_bulan</option>";
                }
                ?>
            </select>
        </div>

        <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Prioritas</label>
            <select name="prioritas" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm">
                <option value="">Semua Prioritas</option>
                <option value="rendah" <?php echo (isset($_GET['prioritas']) && $_GET['prioritas'] == 'rendah') ? 'selected' : ''; ?>>Rendah</option>
                <option value="sedang" <?php echo (isset($_GET['prioritas']) && $_GET['prioritas'] == 'sedang') ? 'selected' : ''; ?>>Sedang</option>
                <option value="tinggi" <?php echo (isset($_GET['prioritas']) && $_GET['prioritas'] == 'tinggi') ? 'selected' : ''; ?>>Tinggi</option>
            </select>
        </div>

        <?php if ($role == 'admin'): ?>
        <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Siswa</label>
            <select name="siswa" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm">
                <option value="">Semua Siswa</option>
                <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                <option value="<?php echo $s['id']; ?>" <?php echo $filter_siswa == $s['id'] ? 'selected' : ''; ?>>
                    <?php echo $s['nama_lengkap']; ?> - <?php echo $s['kelas']; ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="sm:col-span-2 lg:col-span-<?php echo $role == 'admin' ? '5' : '5'; ?> flex gap-2">
            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 text-xs sm:text-sm">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="list_aspirasi.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 text-center text-xs sm:text-sm">
                <i class="fas fa-sync mr-2"></i>Reset
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-4 sm:p-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-list mr-2"></i>Daftar Aspirasi
            <span class="text-sm sm:text-base font-normal text-gray-600 ml-2">
                (<?php echo mysqli_num_rows($result); ?> data)
            </span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full responsive-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <?php if ($role == 'admin'): ?>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Siswa</th>
                        <?php endif; ?>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Judul</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lokasi</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Prioritas</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-700" data-label="Tanggal">
                                <?php echo date('d/m/Y', strtotime($row['tanggal_pengaduan'])); ?>
                            </td>
                            <?php if ($role == 'admin'): ?>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-700" data-label="Siswa">
                                <?php echo $row['nama_lengkap']; ?><br>
                                <span class="text-xs text-gray-500"><?php echo $row['kelas']; ?></span>
                            </td>
                            <?php endif; ?>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm" data-label="Kategori">
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold">
                                    <?php echo $row['nama_kategori']; ?>
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-700" data-label="Judul">
                                <?php echo substr($row['judul'], 0, 50) . (strlen($row['judul']) > 50 ? '...' : ''); ?>
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-700" data-label="Lokasi">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                <?php echo $row['lokasi']; ?>
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm" data-label="Prioritas">
                                <?php
                                $prioritas_colors = [
                                    'rendah' => 'bg-green-100 text-green-800',
                                    'sedang' => 'bg-yellow-100 text-yellow-800',
                                    'tinggi' => 'bg-red-100 text-red-800'
                                ];
                                ?>
                                <span class="<?php echo $prioritas_colors[$row['prioritas']]; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                    <?php echo ucfirst($row['prioritas']); ?>
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm" data-label="Status">
                                <?php
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'diproses' => 'bg-blue-100 text-blue-800',
                                    'selesai' => 'bg-green-100 text-green-800',
                                    'ditolak' => 'bg-red-100 text-red-800'
                                ];
                                ?>
                                <span class="<?php echo $status_colors[$row['status']]; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm" data-label="Aksi">
                                <a href="detail_aspirasi.php?id=<?php echo $row['id']; ?>"
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold inline-block">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $role == 'admin' ? '8' : '7'; ?>"
                                class="px-3 sm:px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl sm:text-4xl mb-2 block"></i>
                                <p class="text-sm sm:text-base">Tidak ada data aspirasi</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>