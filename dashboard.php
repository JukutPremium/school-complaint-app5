<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$page_title = 'Dashboard';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'admin') {
    $total_aspirasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi"))['total'];
    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE status='pending'"))['total'];
    $diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE status='diproses'"))['total'];
    $selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE status='selesai'"))['total'];
} else {
    $total_aspirasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE user_id='$user_id'"))['total'];
    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE user_id='$user_id' AND status='pending'"))['total'];
    $diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE user_id='$user_id' AND status='diproses'"))['total'];
    $selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirasi WHERE user_id='$user_id' AND status='selesai'"))['total'];
}

if ($role == 'admin') {
    $query_recent = "SELECT a.*, u.nama_lengkap, u.kelas, k.nama_kategori 
                     FROM aspirasi a 
                     JOIN users u ON a.user_id = u.id 
                     JOIN kategori k ON a.kategori_id = k.id 
                     ORDER BY a.tanggal_pengaduan DESC LIMIT 5";
} else {
    $query_recent = "SELECT a.*, k.nama_kategori 
                     FROM aspirasi a 
                     JOIN kategori k ON a.kategori_id = k.id 
                     WHERE a.user_id = '$user_id' 
                     ORDER BY a.tanggal_pengaduan DESC LIMIT 5";
}
$recent_aspirasi = mysqli_query($conn, $query_recent);

include 'includes/header.php';
?>

<div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-4 sm:p-6 mb-4 sm:mb-6 text-white">
    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2">
        <i class="fas fa-hand-wave mr-2 sm:mr-3"></i>Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!
    </h1>
    <p class="text-sm sm:text-base text-indigo-100">
        <?php if ($role == 'admin'): ?>
            Kelola pengaduan sarana dan prasarana sekolah dengan mudah dan efisien.
        <?php else: ?>
            Sampaikan aspirasi Anda untuk meningkatkan sarana dan prasarana sekolah.
        <?php endif; ?>
    </p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6">

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border-l-4 border-indigo-500">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div class="mb-3 sm:mb-0">
                <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase">Total Aspirasi</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?php echo $total_aspirasi; ?></p>
            </div>
            <div class="bg-indigo-100 p-3 sm:p-4 rounded-full">
                <i class="fas fa-clipboard-list text-indigo-600 text-xl sm:text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border-l-4 border-yellow-500">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div class="mb-3 sm:mb-0">
                <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase">Pending</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?php echo $pending; ?></p>
            </div>
            <div class="bg-yellow-100 p-3 sm:p-4 rounded-full">
                <i class="fas fa-clock text-yellow-600 text-xl sm:text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border-l-4 border-blue-500">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div class="mb-3 sm:mb-0">
                <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase">Diproses</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?php echo $diproses; ?></p>
            </div>
            <div class="bg-blue-100 p-3 sm:p-4 rounded-full">
                <i class="fas fa-spinner text-blue-600 text-xl sm:text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border-l-4 border-green-500">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div class="mb-3 sm:mb-0">
                <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase">Selesai</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?php echo $selesai; ?></p>
            </div>
            <div class="bg-green-100 p-3 sm:p-4 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-xl sm:text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
    <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">
        <i class="fas fa-history mr-2"></i>Aspirasi Terbaru
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
                    <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (mysqli_num_rows($recent_aspirasi) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($recent_aspirasi)): ?>
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
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $role == 'admin' ? '6' : '5'; ?>"
                            class="px-3 sm:px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl sm:text-4xl mb-2"></i>
                            <p class="text-sm sm:text-base">Belum ada aspirasi</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (mysqli_num_rows($recent_aspirasi) > 0): ?>
        <div class="mt-4 text-center">
            <a href="list_aspirasi.php" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm sm:text-base">
                Lihat Semua Aspirasi <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
