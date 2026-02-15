<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

$page_title = 'Kelola Kategori';
$success = '';
$error = '';

if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);

    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM aspirasi WHERE kategori_id='$id'");
    $count = mysqli_fetch_assoc($check)['count'];

    if ($count > 0) {
        $error = 'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $count . ' aspirasi!';
    } else {
        $delete_query = "DELETE FROM kategori WHERE id='$id'";
        if (mysqli_query($conn, $delete_query)) {
            $success = 'Kategori berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus kategori!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? clean_input($_POST['id']) : '';
    $nama_kategori = clean_input($_POST['nama_kategori']);
    $deskripsi = clean_input($_POST['deskripsi']);

    if ($id) {
        $query = "UPDATE kategori SET nama_kategori='$nama_kategori', deskripsi='$deskripsi' WHERE id='$id'";

        if (mysqli_query($conn, $query)) {
            $success = 'Kategori berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate kategori: ' . mysqli_error($conn);
        }
    } else {
        $query = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')";

        if (mysqli_query($conn, $query)) {
            $success = 'Kategori berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan kategori: ' . mysqli_error($conn);
        }
    }
}

$kategori_query = mysqli_query($conn, "SELECT k.*, COUNT(a.id) as jumlah_aspirasi 
                                       FROM kategori k 
                                       LEFT JOIN aspirasi a ON k.id = a.kategori_id 
                                       GROUP BY k.id 
                                       ORDER BY k.nama_kategori");

$edit_kategori = null;
if (isset($_GET['edit'])) {
    $edit_id = clean_input($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM kategori WHERE id='$edit_id'");
    $edit_kategori = mysqli_fetch_assoc($edit_result);
}

include 'includes/header.php';
?>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 sm:mb-6 alert-auto-hide" role="alert">
    <div class="flex items-center">
        <i class="fas fa-check-circle text-lg sm:text-xl mr-2 sm:mr-3"></i>
        <span class="text-sm sm:text-base"><?php echo $success; ?></span>
    </div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 sm:mb-6 alert-auto-hide" role="alert">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-lg sm:text-xl mr-2 sm:mr-3"></i>
        <span class="text-sm sm:text-base"><?php echo $error; ?></span>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-tag mr-2 text-indigo-600"></i>
                <?php echo $edit_kategori ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?>
            </h2>

            <form method="POST" action="" class="space-y-3 sm:space-y-4">
                <?php if ($edit_kategori): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_kategori['id']; ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Nama Kategori *</label>
                    <input type="text" name="nama_kategori" required
                        value="<?php echo $edit_kategori ? $edit_kategori['nama_kategori'] : ''; ?>"
                        placeholder="Contoh: Ruang Kelas"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm sm:text-base">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="4" placeholder="Jelaskan tentang kategori ini..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm sm:text-base"><?php echo $edit_kategori ? $edit_kategori['deskripsi'] : ''; ?></textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-save mr-2"></i><?php echo $edit_kategori ? 'Update' : 'Tambah'; ?>
                    </button>
                    <?php if ($edit_kategori): ?>
                        <a href="kelola_kategori.php"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-center transition duration-200 text-sm sm:text-base">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="mt-4 sm:mt-6 bg-blue-50 border-l-4 border-blue-500 p-3 sm:p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 text-lg sm:text-xl mr-2 sm:mr-3 mt-0.5"></i>
                    <div class="text-xs sm:text-sm text-gray-700">
                        <strong class="font-semibold">Tips:</strong>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>Nama kategori harus jelas dan mudah dipahami</li>
                            <li>Kategori yang masih digunakan tidak dapat dihapus</li>
                            <li>Deskripsi membantu siswa memilih kategori yang tepat</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-tags mr-2 text-indigo-600"></i>Daftar Kategori
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <?php 
                $has_data = false;
                mysqli_data_seek($kategori_query, 0);
                while ($kategori = mysqli_fetch_assoc($kategori_query)): 
                    $has_data = true;
                ?>
                    <div class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-md transition duration-200">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 text-base sm:text-lg mb-1">
                                    <i class="fas fa-folder text-indigo-600 mr-2"></i>
                                    <?php echo $kategori['nama_kategori']; ?>
                                </h3>
                                <p class="text-xs sm:text-sm text-gray-600 mb-3">
                                    <?php echo $kategori['deskripsi'] ?: 'Tidak ada deskripsi'; ?>
                                </p>
                                <div class="flex items-center space-x-4 text-xs sm:text-sm">
                                    <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full font-semibold">
                                        <i class="fas fa-clipboard-list mr-1"></i>
                                        <?php echo $kategori['jumlah_aspirasi']; ?> Aspirasi
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2 mt-3 pt-3 border-t border-gray-200">
                            <a href="kelola_kategori.php?edit=<?php echo $kategori['id']; ?>"
                                class="text-blue-600 hover:text-blue-800 px-2 sm:px-3 py-1 rounded hover:bg-blue-50 transition duration-200 text-xs sm:text-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <?php if ($kategori['jumlah_aspirasi'] == 0): ?>
                                <a href="kelola_kategori.php?delete=<?php echo $kategori['id']; ?>"
                                    onclick="return confirmDelete('Yakin ingin menghapus kategori ini?')"
                                    class="text-red-600 hover:text-red-800 px-2 sm:px-3 py-1 rounded hover:bg-red-50 transition duration-200 text-xs sm:text-sm">
                                    <i class="fas fa-trash mr-1"></i>Hapus
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 px-2 sm:px-3 py-1 cursor-not-allowed text-xs sm:text-sm"
                                    title="Tidak dapat dihapus karena masih digunakan">
                                    <i class="fas fa-trash mr-1"></i>Hapus
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if (!$has_data): ?>
                <div class="col-span-2 text-center py-12 text-gray-500">
                    <i class="fas fa-tags text-5xl sm:text-6xl mb-4 text-gray-300"></i>
                    <p class="text-base sm:text-lg">Belum ada kategori</p>
                    <p class="text-xs sm:text-sm">Tambahkan kategori pertama Anda di form sebelah kiri</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mt-4 sm:mt-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>Statistik Kategori
            </h3>

            <?php
            mysqli_data_seek($kategori_query, 0);
            $total_kategori = mysqli_num_rows($kategori_query);
            $total_aspirasi = 0;

            while ($kat = mysqli_fetch_assoc($kategori_query)) {
                $total_aspirasi += $kat['jumlah_aspirasi'];
            }
            ?>

            <div class="grid grid-cols-3 gap-3 sm:gap-4">
                <div class="text-center p-3 sm:p-4 bg-indigo-50 rounded-lg">
                    <div class="text-2xl sm:text-3xl font-bold text-indigo-600"><?php echo $total_kategori; ?></div>
                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Total Kategori</div>
                </div>
                <div class="text-center p-3 sm:p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl sm:text-3xl font-bold text-green-600"><?php echo $total_aspirasi; ?></div>
                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Total Aspirasi</div>
                </div>
                <div class="text-center p-3 sm:p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl sm:text-3xl font-bold text-blue-600">
                        <?php echo $total_kategori > 0 ? round($total_aspirasi / $total_kategori, 1) : 0; ?>
                    </div>
                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Rata-rata</div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
