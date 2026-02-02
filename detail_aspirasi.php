<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (!isset($_GET['id'])) {
    header('Location: list_aspirasi.php');
    exit();
}

$aspirasi_id = clean_input($_GET['id']);

if ($role == 'admin') {
    $query = "SELECT a.*, u.nama_lengkap, u.kelas, u.email, k.nama_kategori 
              FROM aspirasi a 
              JOIN users u ON a.user_id = u.id 
              JOIN kategori k ON a.kategori_id = k.id 
              WHERE a.id = '$aspirasi_id'";
} else {
    $query = "SELECT a.*, k.nama_kategori 
              FROM aspirasi a 
              JOIN kategori k ON a.kategori_id = k.id 
              WHERE a.id = '$aspirasi_id' AND a.user_id = '$user_id'";
}

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: list_aspirasi.php');
    exit();
}

$aspirasi = mysqli_fetch_assoc($result);

$feedback_query = "SELECT ub.*, u.nama_lengkap as admin_nama 
                   FROM umpan_balik ub 
                   JOIN users u ON ub.admin_id = u.id 
                   WHERE ub.aspirasi_id = '$aspirasi_id' 
                   ORDER BY ub.tanggal_feedback DESC";
$feedback_result = mysqli_query($conn, $feedback_query);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && $role == 'admin') {
    $status_baru = clean_input($_POST['status']);

    $update_query = "UPDATE aspirasi SET status = '$status_baru'";

    if ($status_baru == 'selesai') {
        $update_query .= ", tanggal_selesai = NOW()";
    }

    $update_query .= " WHERE id = '$aspirasi_id'";

    if (mysqli_query($conn, $update_query)) {
        $success = 'Status berhasil diperbarui!';
        $aspirasi['status'] = $status_baru;
    } else {
        $error = 'Gagal memperbarui status!';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_feedback']) && $role == 'admin') {
    $keterangan = clean_input($_POST['keterangan']);
    $progres = clean_input($_POST['progres']);

    $insert_query = "INSERT INTO umpan_balik (aspirasi_id, admin_id, keterangan, progres) 
                     VALUES ('$aspirasi_id', '$user_id', '$keterangan', '$progres')";

    if (mysqli_query($conn, $insert_query)) {
        $success = 'Umpan balik berhasil ditambahkan!';
        $feedback_result = mysqli_query($conn, $feedback_query);
    } else {
        $error = 'Gagal menambahkan umpan balik!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aspirasi - Aplikasi Pengaduan</title>
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
                    class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
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

        <a href="list_aspirasi.php"
            class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold mb-6">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Aspirasi
        </a>

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

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h1 class="text-2xl font-bold text-gray-800"><?php echo $aspirasi['judul']; ?></h1>
                        <?php
                        $status_colors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'diproses' => 'bg-blue-100 text-blue-800',
                            'selesai' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800'
                        ];
                        ?>
                        <span
                            class="<?php echo $status_colors[$aspirasi['status']]; ?> px-3 py-1 rounded-full text-sm font-semibold">
                            <?php echo ucfirst($aspirasi['status']); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                            <span class="text-sm"><?php echo format_tanggal($aspirasi['tanggal_pengaduan']); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-tag mr-2 text-indigo-600"></i>
                            <span class="text-sm"><?php echo $aspirasi['nama_kategori']; ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                            <span class="text-sm"><?php echo $aspirasi['lokasi']; ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <?php
                            $prioritas_colors = [
                                'rendah' => 'text-green-600',
                                'sedang' => 'text-yellow-600',
                                'tinggi' => 'text-red-600'
                            ];
                            ?>
                            <i
                                class="fas fa-exclamation-triangle mr-2 <?php echo $prioritas_colors[$aspirasi['prioritas']]; ?>"></i>
                            <span class="text-sm">Prioritas <?php echo ucfirst($aspirasi['prioritas']); ?></span>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Deskripsi:</h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br($aspirasi['deskripsi']); ?></p>
                    </div>

                    <?php if ($aspirasi['tanggal_selesai']): ?>
                        <div class="mt-4 p-3 bg-green-50 border-l-4 border-green-500 rounded">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Diselesaikan pada:</strong>
                                <?php echo format_tanggal($aspirasi['tanggal_selesai']); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-comments mr-2 text-indigo-600"></i>Histori Umpan Balik
                    </h2>

                    <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                        <div class="space-y-4">
                            <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                <div class="border-l-4 border-indigo-500 bg-gray-50 p-4 rounded">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="font-semibold text-gray-800">
                                                <?php echo $role == 'admin' ? $feedback['admin_nama'] : 'Admin'; ?>
                                            </span>
                                            <span class="text-sm text-gray-500 ml-2">
                                                <?php echo format_tanggal_waktu($feedback['tanggal_feedback']); ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-indigo-600 h-2 rounded-full"
                                                    style="width: <?php echo $feedback['progres']; ?>%"></div>
                                            </div>
                                            <span
                                                class="text-sm font-semibold text-indigo-600"><?php echo $feedback['progres']; ?>%</span>
                                        </div>
                                    </div>
                                    <p class="text-gray-700"><?php echo nl2br($feedback['keterangan']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-comments text-4xl mb-2"></i>
                            <p>Belum ada umpan balik</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="space-y-6">

                <?php if ($role == 'admin'): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-edit mr-2 text-indigo-600"></i>Update Status
                        </h3>
                        <form method="POST" action="">
                            <select name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3 focus:ring-2 focus:ring-indigo-500">
                                <option value="pending" <?php echo $aspirasi['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Pending</option>
                                <option value="diproses" <?php echo $aspirasi['status'] == 'diproses' ? 'selected' : ''; ?>>
                                    Diproses</option>
                                <option value="selesai" <?php echo $aspirasi['status'] == 'selesai' ? 'selected' : ''; ?>>
                                    Selesai</option>
                                <option value="ditolak" <?php echo $aspirasi['status'] == 'ditolak' ? 'selected' : ''; ?>>
                                    Ditolak</option>
                            </select>
                            <button type="submit" name="update_status"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i>Update Status
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-comment-medical mr-2 text-indigo-600"></i>Tambah Umpan Balik
                        </h3>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Progres (%)</label>
                                <input type="number" name="progres" min="0" max="100" value="0" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                                <textarea name="keterangan" rows="4" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Masukkan keterangan umpan balik..."></textarea>
                            </div>
                            <button type="submit" name="tambah_feedback"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-paper-plane mr-2"></i>Kirim Umpan Balik
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-user mr-2 text-indigo-600"></i>Info Pengadu
                        </h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Nama:</strong> <?php echo $aspirasi['nama_lengkap']; ?></p>
                            <p><strong>Kelas:</strong> <?php echo $aspirasi['kelas']; ?></p>
                            <p><strong>Email:</strong> <?php echo $aspirasi['email']; ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <h3 class="font-semibold text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>Informasi
                        </h3>
                        <p class="text-sm text-blue-700">
                            Admin akan memberikan umpan balik dan memproses aspirasi Anda. Pantau terus status dan progres
                            perbaikan.
                        </p>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

</body>

</html>