<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: index.php');
    exit();
}

$page_title = 'Form Aspirasi';
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
    
    $foto_name = NULL;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['foto']['size'];
        
        if (!in_array($filetype, $allowed)) {
            $error = 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan!';
        } elseif ($filesize > 5242880) {
            $error = 'Ukuran file terlalu besar! Maksimal 5MB.';
        } else {
            $foto_name = 'aspirasi_' . time() . '_' . uniqid() . '.' . $filetype;
            $target_path = 'uploads/' . $foto_name;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                $error = 'Gagal mengupload foto!';
                $foto_name = NULL;
            }
        }
    }
    
    if (empty($error)) {
        $foto_sql = $foto_name ? "'$foto_name'" : "NULL";
        $query = "INSERT INTO aspirasi (user_id, kategori_id, judul, deskripsi, lokasi, prioritas, foto, status) 
                  VALUES ('$user_id', '$kategori_id', '$judul', '$deskripsi', '$lokasi', '$prioritas', $foto_sql, 'pending')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Aspirasi berhasil diajukan!';
            $_POST = array();
        } else {
            $error = 'Gagal mengajukan aspirasi: ' . mysqli_error($conn);
            if ($foto_name && file_exists('uploads/' . $foto_name)) {
                unlink('uploads/' . $foto_name);
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 lg:p-8">
        <div class="flex items-center mb-4 sm:mb-6">
            <div class="bg-indigo-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
                <i class="fas fa-edit text-indigo-600 text-xl sm:text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Form Aspirasi Siswa</h1>
                <p class="text-gray-600 text-xs sm:text-sm">Sampaikan pengaduan atau masukan terkait sarana sekolah</p>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 sm:mb-6 alert-auto-hide" role="alert">
            <div class="flex items-start">
                <i class="fas fa-check-circle text-lg sm:text-xl mr-2 sm:mr-3 mt-0.5"></i>
                <div class="text-sm sm:text-base">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline"> <?php echo $success; ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 sm:mb-6 alert-auto-hide" role="alert">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-lg sm:text-xl mr-2 sm:mr-3 mt-0.5"></i>
                <div class="text-sm sm:text-base">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"> <?php echo $error; ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
            <div>
                <label for="kategori_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-tag mr-2 text-indigo-600"></i>Kategori <span class="text-red-500">*</span>
                </label>
                <select id="kategori_id" name="kategori_id" required
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
                    <option value="">-- Pilih Kategori --</option>
                    <?php 
                    mysqli_data_seek($kategori, 0);
                    while ($kat = mysqli_fetch_assoc($kategori)): 
                    ?>
                    <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label for="judul" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-heading mr-2 text-indigo-600"></i>Judul Aspirasi <span class="text-red-500">*</span>
                </label>
                <input type="text" id="judul" name="judul" required maxlength="200"
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
                    placeholder="Contoh: Kipas Angin Rusak di Kelas XII RPL 1">
            </div>

            <div>
                <label for="lokasi" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>Lokasi <span class="text-red-500">*</span>
                </label>
                <input type="text" id="lokasi" name="lokasi" required maxlength="100"
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
                    placeholder="Contoh: Ruang XII RPL 1, Lantai 2">
            </div>

            <div>
                <label for="prioritas" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-exclamation-triangle mr-2 text-indigo-600"></i>Tingkat Prioritas <span class="text-red-500">*</span>
                </label>
                <select id="prioritas" name="prioritas" required
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
                    <option value="">-- Pilih Prioritas --</option>
                    <option value="rendah">Rendah - Tidak mendesak</option>
                    <option value="sedang">Sedang - Perlu perhatian</option>
                    <option value="tinggi">Tinggi - Segera ditangani</option>
                </select>
            </div>

            <div>
                <label for="deskripsi" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-align-left mr-2 text-indigo-600"></i>Deskripsi Lengkap <span class="text-red-500">*</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" required rows="6"
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
                    placeholder="Jelaskan detail permasalahan atau aspirasi Anda..."></textarea>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Jelaskan masalah secara detail agar lebih mudah ditangani</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-camera mr-2 text-indigo-600"></i>Upload Foto (Opsional)
                </label>
                <div class="file-upload-box rounded-lg p-4 sm:p-6 text-center cursor-pointer">
                    <input type="file" id="foto" name="foto" accept="image/*" class="hidden" onchange="previewImage(this)">
                    <label for="foto" class="cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-3xl sm:text-4xl text-gray-400 mb-2 sm:mb-3"></i>
                        <p class="text-sm sm:text-base text-gray-600 mb-1 sm:mb-2">
                            <span class="font-semibold text-indigo-600">Klik untuk upload</span> atau drag & drop
                        </p>
                        <p class="text-xs sm:text-sm text-gray-500">JPG, JPEG, PNG, atau GIF (Max 5MB)</p>
                    </label>
                </div>
                
                <div id="preview-container" class="hidden mt-4">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Preview:</p>
                    <img id="preview-image" class="rounded-lg shadow-md max-h-64 sm:max-h-80 w-auto mx-auto" alt="Preview">
                    <button type="button" onclick="removeImage()" class="mt-2 text-red-600 hover:text-red-800 text-xs sm:text-sm font-semibold">
                        <i class="fas fa-times mr-1"></i>Hapus Foto
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-4">
                <button type="submit"
                    class="w-full sm:flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 transform hover:scale-105 shadow-md text-sm sm:text-base">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Aspirasi
                </button>
                <a href="list_aspirasi.php"
                    class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 text-center text-sm sm:text-base">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function removeImage() {
    document.getElementById('foto').value = '';
    document.getElementById('preview-container').classList.add('hidden');
}
</script>

<?php include 'includes/footer.php'; ?>
