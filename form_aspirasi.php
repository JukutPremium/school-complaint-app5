<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: index.php');
    exit();
}

$success = '';
$error = '';

$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $kategori_id = clean_input($_POST['kategori_id']);
    $judul = clean_input($_POST['judul']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $lokasi = clean_input($_POST['lokasi']);
    $prioritas = clean_input($_POST['prioritas']);
    
    $query = "INSERT INTO aspirasi (user_id, kategori_id, judul, deskripsi, lokasi, prioritas, status) 
              VALUES ('$user_id', '$kategori_id', '$judul', '$deskripsi', '$lokasi', '$prioritas', 'pending')";
    
    if (mysqli_query($conn, $query)) {
        $success = 'Aspirasi berhasil diajukan!';
        $_POST = array();
    } else {
        $error = 'Gagal mengajukan aspirasi: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Aspirasi - Aplikasi Pengaduan</title>
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
                        <span class="text-indigo-200 text-sm ml-2">(Siswa)</span>
                    </span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="flex flex-wrap border-b">
                <a href="dashboard.php" class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="form_aspirasi.php" class="px-6 py-4 text-indigo-600 border-b-2 border-indigo-600 font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>Buat Aspirasi
                </a>
                <a href="list_aspirasi.php" class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-list mr-2"></i>Daftar Aspirasi
                </a>
            </div>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="flex items-center mb-6">
                    <div class="bg-indigo-100 p-3 rounded-full mr-4">
                        <i class="fas fa-edit text-indigo-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Form Aspirasi Siswa</h1>
                        <p class="text-gray-600">Sampaikan pengaduan atau masukan terkait sarana sekolah</p>
                    </div>
                </div>

                <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <div>
                        <strong class="font-bold">Berhasil!</strong>
                        <span class="block sm:inline"> <?php echo $success; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <div>
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"> <?php echo $error; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    
                    <div>
                        <label for="kategori_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2 text-indigo-600"></i>Kategori <span class="text-red-500">*</span>
                        </label>
                        <select id="kategori_id" name="kategori_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($kat = mysqli_fetch_assoc($kategori)): ?>
                            <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label for="judul" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heading mr-2 text-indigo-600"></i>Judul Aspirasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="judul" name="judul" required maxlength="200"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Contoh: Kipas Angin Rusak di Kelas XII RPL 1">
                    </div>

                    <div>
                        <label for="lokasi" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="lokasi" name="lokasi" required maxlength="100"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Contoh: Ruang XII RPL 1, Lantai 2">
                    </div>

                    <div>
                        <label for="prioritas" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-exclamation-triangle mr-2 text-indigo-600"></i>Tingkat Prioritas <span class="text-red-500">*</span>
                        </label>
                        <select id="prioritas" name="prioritas" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="rendah">Rendah - Tidak Mendesak</option>
                            <option value="sedang" selected>Sedang - Perlu Segera Ditangani</option>
                            <option value="tinggi">Tinggi - Sangat Mendesak</option>
                        </select>
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-align-left mr-2 text-indigo-600"></i>Deskripsi Lengkap <span class="text-red-500">*</span>
                        </label>
                        <textarea id="deskripsi" name="deskripsi" required rows="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Jelaskan secara detail kondisi, masalah, atau usulan perbaikan yang Anda inginkan..."></textarea>
                        <p class="text-sm text-gray-500 mt-1">Semakin detail informasi yang Anda berikan, semakin mudah kami memproses aspirasi Anda.</p>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
                            <div class="text-sm text-gray-700">
                                <strong class="font-semibold">Tips:</strong>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Berikan informasi selengkap mungkin</li>
                                    <li>Sebutkan lokasi dengan spesifik</li>
                                    <li>Jelaskan dampak yang ditimbulkan</li>
                                    <li>Aspirasi Anda akan diproses maksimal 3 hari kerja</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" 
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105 shadow-md">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Aspirasi
                        </button>
                        <button type="reset" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-200">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>