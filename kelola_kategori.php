<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Aplikasi Pengaduan</title>
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
                        <span class="text-indigo-200 text-sm ml-2">(Admin)</span>
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
                <a href="list_aspirasi.php"
                    class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-list mr-2"></i>Daftar Aspirasi
                </a>
                <a href="laporan.php"
                    class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-chart-bar mr-2"></i>Laporan
                </a>
                <a href="kelola_user.php"
                    class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-users mr-2"></i>Kelola User
                </a>
                <a href="kelola_kategori.php"
                    class="px-6 py-4 text-indigo-600 border-b-2 border-indigo-600 font-semibold">
                    <i class="fas fa-tags mr-2"></i>Kelola Kategori
                </a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center"
                role="alert">
                <i class="fas fa-check-circle text-xl mr-3"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center"
                role="alert">
                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-tag mr-2 text-indigo-600"></i>
                        <?php echo $edit_kategori ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?>
                    </h2>

                    <form method="POST" action="" class="space-y-4">
                        <?php if ($edit_kategori): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_kategori['id']; ?>">
                        <?php endif; ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori *</label>
                            <input type="text" name="nama_kategori" required
                                value="<?php echo $edit_kategori ? $edit_kategori['nama_kategori'] : ''; ?>"
                                placeholder="Contoh: Ruang Kelas"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea name="deskripsi" rows="4" placeholder="Jelaskan tentang kategori ini..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"><?php echo $edit_kategori ? $edit_kategori['deskripsi'] : ''; ?></textarea>
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i><?php echo $edit_kategori ? 'Update' : 'Tambah'; ?>
                            </button>
                            <?php if ($edit_kategori): ?>
                                <a href="kelola_kategori.php"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-center transition duration-200">
                                    <i class="fas fa-times mr-2"></i>Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
                            <div class="text-sm text-gray-700">
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
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-tags mr-2 text-indigo-600"></i>Daftar Kategori
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_query)): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-800 text-lg mb-1">
                                            <i class="fas fa-folder text-indigo-600 mr-2"></i>
                                            <?php echo $kategori['nama_kategori']; ?>
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-3">
                                            <?php echo $kategori['deskripsi'] ?: 'Tidak ada deskripsi'; ?>
                                        </p>
                                        <div class="flex items-center space-x-4 text-sm">
                                            <span
                                                class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full font-semibold">
                                                <i class="fas fa-clipboard-list mr-1"></i>
                                                <?php echo $kategori['jumlah_aspirasi']; ?> Aspirasi
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end space-x-2 mt-3 pt-3 border-t border-gray-200">
                                    <a href="kelola_kategori.php?edit=<?php echo $kategori['id']; ?>"
                                        class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded hover:bg-blue-50 transition duration-200">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <?php if ($kategori['jumlah_aspirasi'] == 0): ?>
                                        <a href="kelola_kategori.php?delete=<?php echo $kategori['id']; ?>"
                                            onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                            class="text-red-600 hover:text-red-800 px-3 py-1 rounded hover:bg-red-50 transition duration-200">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 px-3 py-1 cursor-not-allowed"
                                            title="Tidak dapat dihapus karena masih digunakan">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php
                    mysqli_data_seek($kategori_query, 0);
                    if (mysqli_num_rows($kategori_query) == 0):
                        ?>
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-tags text-6xl mb-4 text-gray-300"></i>
                            <p class="text-lg">Belum ada kategori</p>
                            <p class="text-sm">Tambahkan kategori pertama Anda di form sebelah kiri</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
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

                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-indigo-50 rounded-lg">
                            <div class="text-3xl font-bold text-indigo-600"><?php echo $total_kategori; ?></div>
                            <div class="text-sm text-gray-600 mt-1">Total Kategori</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-3xl font-bold text-green-600"><?php echo $total_aspirasi; ?></div>
                            <div class="text-sm text-gray-600 mt-1">Total Aspirasi</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-3xl font-bold text-blue-600">
                                <?php echo $total_kategori > 0 ? round($total_aspirasi / $total_kategori, 1) : 0; ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Rata-rata per Kategori</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>

</html>