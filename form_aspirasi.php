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
    
    $foto_name = NULL;
    
    // Handle file upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['foto']['size'];
        
        // Validasi tipe file
        if (!in_array($filetype, $allowed)) {
            $error = 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan!';
        }
        // Validasi ukuran file (max 5MB)
        elseif ($filesize > 5242880) {
            $error = 'Ukuran file terlalu besar! Maksimal 5MB.';
        }
        else {
            // Generate unique filename
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
            // Delete uploaded file if database insert fails
            if ($foto_name && file_exists('uploads/' . $foto_name)) {
                unlink('uploads/' . $foto_name);
            }
        }
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
    <style>
        .file-upload-box {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .file-upload-box:hover {
            border-color: #4f46e5;
            background-color: #f7fafc;
        }
        .file-upload-box.drag-over {
            border-color: #4f46e5;
            background-color: #eef2ff;
        }
        #preview-image {
            max-height: 300px;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navbar -->
    <nav class="bg-indigo-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-school text-white text-xl sm:text-2xl mr-2 sm:mr-3"></i>
                    <span class="text-white text-base sm:text-xl font-bold">Pengaduan Sekolah</span>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <span class="text-white text-sm sm:text-base">
                        <i class="fas fa-user-circle mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline"><?php echo $_SESSION['nama_lengkap']; ?></span>
                        <span class="text-indigo-200 text-xs sm:text-sm ml-1 sm:ml-2">(Siswa)</span>
                    </span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-2 sm:px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-sign-out-alt mr-0 sm:mr-2"></i><span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        
        <!-- Navigation Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-4 sm:mb-6 overflow-x-auto">
            <div class="flex border-b whitespace-nowrap">
                <a href="dashboard.php" class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200 text-sm sm:text-base">
                    <i class="fas fa-home mr-1 sm:mr-2"></i>Dashboard
                </a>
                <a href="form_aspirasi.php" class="px-4 sm:px-6 py-3 sm:py-4 text-indigo-600 border-b-2 border-indigo-600 font-semibold text-sm sm:text-base">
                    <i class="fas fa-plus-circle mr-1 sm:mr-2"></i>Buat Aspirasi
                </a>
                <a href="list_aspirasi.php" class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200 text-sm sm:text-base">
                    <i class="fas fa-list mr-1 sm:mr-2"></i>Daftar Aspirasi
                </a>
            </div>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-8">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="bg-indigo-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
                        <i class="fas fa-edit text-indigo-600 text-xl sm:text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Form Aspirasi Siswa</h1>
                        <p class="text-gray-600 text-sm sm:text-base">Sampaikan pengaduan atau masukan terkait sarana sekolah</p>
                    </div>
                </div>

                <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 sm:mb-6 flex items-center" role="alert">
                    <i class="fas fa-check-circle text-lg sm:text-xl mr-2 sm:mr-3"></i>
                    <div class="text-sm sm:text-base">
                        <strong class="font-bold">Berhasil!</strong>
                        <span class="block sm:inline"> <?php echo $success; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 sm:mb-6 flex items-center" role="alert">
                    <i class="fas fa-exclamation-circle text-lg sm:text-xl mr-2 sm:mr-3"></i>
                    <div class="text-sm sm:text-base">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"> <?php echo $error; ?></span>
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
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
                            placeholder="Jelaskan secara detail kondisi, masalah, atau usulan perbaikan yang Anda inginkan..."></textarea>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Semakin detail informasi yang Anda berikan, semakin mudah kami memproses aspirasi Anda.</p>
                    </div>

                    <!-- Upload Foto -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-camera mr-2 text-indigo-600"></i>Upload Foto Bukti (Opsional)
                        </label>
                        <div class="file-upload-box rounded-lg p-4 sm:p-6 text-center cursor-pointer" id="file-upload-box">
                            <input type="file" id="foto" name="foto" accept="image/*" class="hidden">
                            <div id="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt text-4xl sm:text-5xl text-gray-400 mb-3"></i>
                                <p class="text-sm sm:text-base text-gray-600 mb-1">
                                    <span class="font-semibold text-indigo-600">Klik untuk upload</span> atau drag & drop
                                </p>
                                <p class="text-xs sm:text-sm text-gray-500">JPG, PNG, GIF (Max. 5MB)</p>
                            </div>
                            <div id="preview-container" class="hidden">
                                <img id="preview-image" src="" alt="Preview" class="mx-auto rounded-lg mb-3">
                                <p id="file-name" class="text-sm text-gray-700 font-medium mb-2"></p>
                                <button type="button" id="remove-image" class="text-red-500 hover:text-red-700 text-sm">
                                    <i class="fas fa-trash mr-1"></i>Hapus Foto
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 sm:p-4 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 text-lg sm:text-xl mr-2 sm:mr-3 mt-1"></i>
                            <div class="text-xs sm:text-sm text-gray-700">
                                <strong class="font-semibold">Tips:</strong>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Berikan informasi selengkap mungkin</li>
                                    <li>Sebutkan lokasi dengan spesifik</li>
                                    <li>Upload foto untuk memperjelas kondisi</li>
                                    <li>Jelaskan dampak yang ditimbulkan</li>
                                    <li>Aspirasi Anda akan diproses maksimal 3 hari kerja</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                        <button type="submit" 
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105 shadow-md text-sm sm:text-base">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Aspirasi
                        </button>
                        <button type="reset" id="reset-btn"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-200 text-sm sm:text-base">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        const fileUploadBox = document.getElementById('file-upload-box');
        const fileInput = document.getElementById('foto');
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const fileName = document.getElementById('file-name');
        const removeImageBtn = document.getElementById('remove-image');
        const resetBtn = document.getElementById('reset-btn');

        // Click to upload
        fileUploadBox.addEventListener('click', () => {
            fileInput.click();
        });

        // File input change
        fileInput.addEventListener('change', handleFileSelect);

        // Drag and drop
        fileUploadBox.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadBox.classList.add('drag-over');
        });

        fileUploadBox.addEventListener('dragleave', () => {
            fileUploadBox.classList.remove('drag-over');
        });

        fileUploadBox.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadBox.classList.remove('drag-over');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });

        function handleFileSelect() {
            const file = fileInput.files[0];
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Hanya file JPG, PNG, dan GIF yang diizinkan!');
                    fileInput.value = '';
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5242880) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    fileInput.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    fileName.textContent = file.name;
                    uploadPlaceholder.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // Remove image
        removeImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            previewImage.src = '';
            fileName.textContent = '';
            uploadPlaceholder.classList.remove('hidden');
            previewContainer.classList.add('hidden');
        });

        // Reset form
        resetBtn.addEventListener('click', () => {
            setTimeout(() => {
                fileInput.value = '';
                previewImage.src = '';
                fileName.textContent = '';
                uploadPlaceholder.classList.remove('hidden');
                previewContainer.classList.add('hidden');
            }, 10);
        });
    </script>

</body>
</html>
