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
            $foto_name = 'feedback_' . time() . '_' . uniqid() . '.' . $filetype;
            $target_path = 'uploads/' . $foto_name;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                $error = 'Gagal mengupload foto!';
                $foto_name = NULL;
            }
        }
    }
    
    if (empty($error)) {
        $foto_sql = $foto_name ? "'$foto_name'" : "NULL";
        $insert_query = "INSERT INTO umpan_balik (aspirasi_id, admin_id, keterangan, progres, foto) 
                         VALUES ('$aspirasi_id', '$user_id', '$keterangan', '$progres', $foto_sql)";

        if (mysqli_query($conn, $insert_query)) {
            $success = 'Umpan balik berhasil ditambahkan!';
            $feedback_result = mysqli_query($conn, $feedback_query);
        } else {
            $error = 'Gagal menambahkan umpan balik!';
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
    <title>Detail Aspirasi - Aplikasi Pengaduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .file-upload-box {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .file-upload-box:hover {
            border-color: #10b981;
            background-color: #f7fafc;
        }
        .file-upload-box.drag-over {
            border-color: #10b981;
            background-color: #ecfdf5;
        }
        #preview-image-feedback {
            max-height: 200px;
            object-fit: contain;
        }
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }
        .image-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeModal()">
        <span class="close-modal">&times;</span>
        <img id="modalImage" src="" alt="Full size image">
    </div>

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
                        <span class="text-indigo-200 text-xs sm:text-sm ml-1 sm:ml-2">(<?php echo ucfirst($role); ?>)</span>
                    </span>
                    <a href="logout.php"
                        class="bg-red-500 hover:bg-red-600 text-white px-2 sm:px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
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
                <a href="dashboard.php"
                    class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200 text-sm sm:text-base">
                    <i class="fas fa-home mr-1 sm:mr-2"></i>Dashboard
                </a>
                <?php if ($role == 'siswa'): ?>
                    <a href="form_aspirasi.php"
                        class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-plus-circle mr-1 sm:mr-2"></i>Buat Aspirasi
                    </a>
                <?php endif; ?>
                <a href="list_aspirasi.php"
                    class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200 text-sm sm:text-base">
                    <i class="fas fa-list mr-1 sm:mr-2"></i>Daftar Aspirasi
                </a>
                <?php if ($role == 'admin'): ?>
                    <a href="kelola_user.php"
                        class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-users mr-1 sm:mr-2"></i>Kelola User
                    </a>
                    <a href="kelola_kategori.php"
                        class="px-4 sm:px-6 py-3 sm:py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-tags mr-1 sm:mr-2"></i>Kelola Kategori
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <a href="list_aspirasi.php"
            class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold mb-4 sm:mb-6 text-sm sm:text-base">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Aspirasi
        </a>

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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

            <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2 sm:mb-0"><?php echo $aspirasi['judul']; ?></h1>
                        <?php
                        $status_colors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'diproses' => 'bg-blue-100 text-blue-800',
                            'selesai' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800'
                        ];
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs sm:text-sm font-semibold <?php echo $status_colors[$aspirasi['status']]; ?> w-fit">
                            <?php echo ucfirst($aspirasi['status']); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                            <span class="text-xs sm:text-sm"><?php echo format_tanggal($aspirasi['tanggal_pengaduan']); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-tag mr-2 text-indigo-600"></i>
                            <span class="text-xs sm:text-sm"><?php echo $aspirasi['nama_kategori']; ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                            <span class="text-xs sm:text-sm"><?php echo $aspirasi['lokasi']; ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <?php
                            $prioritas_colors = [
                                'rendah' => 'text-green-600',
                                'sedang' => 'text-yellow-600',
                                'tinggi' => 'text-red-600'
                            ];
                            ?>
                            <i class="fas fa-exclamation-triangle mr-2 <?php echo $prioritas_colors[$aspirasi['prioritas']]; ?>"></i>
                            <span class="text-xs sm:text-sm">Prioritas <?php echo ucfirst($aspirasi['prioritas']); ?></span>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-gray-800 mb-2 text-sm sm:text-base">Deskripsi:</h3>
                        <p class="text-gray-700 leading-relaxed text-sm sm:text-base"><?php echo nl2br($aspirasi['deskripsi']); ?></p>
                    </div>

                    <?php if ($aspirasi['foto']): ?>
                    <div class="border-t pt-4 mt-4">
                        <h3 class="font-semibold text-gray-800 mb-2 text-sm sm:text-base">
                            <i class="fas fa-image mr-2 text-indigo-600"></i>Foto Bukti:
                        </h3>
                        <img src="uploads/<?php echo $aspirasi['foto']; ?>" 
                             alt="Foto Bukti" 
                             class="rounded-lg shadow-md cursor-pointer hover:opacity-90 transition max-w-full h-auto"
                             onclick="openModal('uploads/<?php echo $aspirasi['foto']; ?>')">
                    </div>
                    <?php endif; ?>

                    <?php if ($aspirasi['tanggal_selesai']): ?>
                        <div class="mt-4 p-3 bg-green-50 border-l-4 border-green-500 rounded">
                            <p class="text-xs sm:text-sm text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Diselesaikan pada:</strong>
                                <?php echo format_tanggal($aspirasi['tanggal_selesai']); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-comments mr-2 text-indigo-600"></i>Histori Umpan Balik
                    </h2>

                    <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                        <div class="space-y-4">
                            <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                <div class="border-l-4 border-indigo-500 bg-gray-50 p-3 sm:p-4 rounded">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2 gap-2">
                                        <div>
                                            <span class="font-semibold text-gray-800 text-sm sm:text-base">
                                                <?php echo $role == 'admin' ? $feedback['admin_nama'] : 'Admin'; ?>
                                            </span>
                                            <span class="text-xs sm:text-sm text-gray-500 block sm:inline sm:ml-2">
                                                <?php echo format_tanggal_waktu($feedback['tanggal_feedback']); ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-20 sm:w-24 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-indigo-600 h-2 rounded-full"
                                                    style="width: <?php echo $feedback['progres']; ?>%"></div>
                                            </div>
                                            <span class="text-xs sm:text-sm font-semibold text-indigo-600"><?php echo $feedback['progres']; ?>%</span>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 text-sm sm:text-base"><?php echo nl2br($feedback['keterangan']); ?></p>
                                    
                                    <?php if ($feedback['foto']): ?>
                                    <div class="mt-3">
                                        <p class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-image mr-1 text-indigo-600"></i>Foto Tanggapan:
                                        </p>
                                        <img src="uploads/<?php echo $feedback['foto']; ?>" 
                                             alt="Foto Tanggapan" 
                                             class="rounded-lg shadow-md cursor-pointer hover:opacity-90 transition max-w-full sm:max-w-xs h-auto"
                                             onclick="openModal('uploads/<?php echo $feedback['foto']; ?>')">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-comments text-3xl sm:text-4xl mb-2"></i>
                            <p class="text-sm sm:text-base">Belum ada umpan balik</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="space-y-4 sm:space-y-6">

                <?php if ($role == 'admin'): ?>
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-edit mr-2 text-indigo-600"></i>Update Status
                        </h3>
                        <form method="POST" action="">
                            <select name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3 focus:ring-2 focus:ring-indigo-500 text-sm sm:text-base">
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
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                                <i class="fas fa-save mr-2"></i>Update Status
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-comment-medical mr-2 text-indigo-600"></i>Tambah Umpan Balik
                        </h3>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Progres (%)</label>
                                <input type="number" name="progres" min="0" max="100" value="0" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm sm:text-base">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                                <textarea name="keterangan" rows="4" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm sm:text-base"
                                    placeholder="Masukkan keterangan umpan balik..."></textarea>
                            </div>
                            
                            <!-- Upload Foto untuk Admin -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-camera mr-1 text-green-600"></i>Upload Foto (Opsional)
                                </label>
                                <div class="file-upload-box rounded-lg p-4 text-center cursor-pointer" id="file-upload-box-feedback">
                                    <input type="file" id="foto-feedback" name="foto" accept="image/*" class="hidden">
                                    <div id="upload-placeholder-feedback">
                                        <i class="fas fa-cloud-upload-alt text-3xl sm:text-4xl text-gray-400 mb-2"></i>
                                        <p class="text-xs sm:text-sm text-gray-600 mb-1">
                                            <span class="font-semibold text-green-600">Klik untuk upload</span>
                                        </p>
                                        <p class="text-xs text-gray-500">JPG, PNG, GIF (Max. 5MB)</p>
                                    </div>
                                    <div id="preview-container-feedback" class="hidden">
                                        <img id="preview-image-feedback" src="" alt="Preview" class="mx-auto rounded-lg mb-2">
                                        <p id="file-name-feedback" class="text-xs sm:text-sm text-gray-700 font-medium mb-2"></p>
                                        <button type="button" id="remove-image-feedback" class="text-red-500 hover:text-red-700 text-xs sm:text-sm">
                                            <i class="fas fa-trash mr-1"></i>Hapus Foto
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="tambah_feedback"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                                <i class="fas fa-paper-plane mr-2"></i>Kirim Umpan Balik
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-user mr-2 text-indigo-600"></i>Info Pengadu
                        </h3>
                        <div class="space-y-2 text-xs sm:text-sm">
                            <p><strong>Nama:</strong> <?php echo $aspirasi['nama_lengkap']; ?></p>
                            <p><strong>Kelas:</strong> <?php echo $aspirasi['kelas']; ?></p>
                            <p><strong>Email:</strong> <?php echo $aspirasi['email']; ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 sm:p-4 rounded">
                        <h3 class="font-semibold text-blue-800 mb-2 text-sm sm:text-base">
                            <i class="fas fa-info-circle mr-2"></i>Informasi
                        </h3>
                        <p class="text-xs sm:text-sm text-blue-700">
                            Admin akan memberikan umpan balik dan memproses aspirasi Anda. Pantau terus status dan progres
                            perbaikan.
                        </p>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

    <script>
        // Image modal functions
        function openModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.classList.add('active');
            modalImg.src = imageSrc;
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('active');
        }

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Admin feedback file upload
        <?php if ($role == 'admin'): ?>
        const fileUploadBoxFeedback = document.getElementById('file-upload-box-feedback');
        const fileInputFeedback = document.getElementById('foto-feedback');
        const uploadPlaceholderFeedback = document.getElementById('upload-placeholder-feedback');
        const previewContainerFeedback = document.getElementById('preview-container-feedback');
        const previewImageFeedback = document.getElementById('preview-image-feedback');
        const fileNameFeedback = document.getElementById('file-name-feedback');
        const removeImageBtnFeedback = document.getElementById('remove-image-feedback');

        // Click to upload
        fileUploadBoxFeedback.addEventListener('click', () => {
            fileInputFeedback.click();
        });

        // File input change
        fileInputFeedback.addEventListener('change', handleFileSelectFeedback);

        // Drag and drop
        fileUploadBoxFeedback.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadBoxFeedback.classList.add('drag-over');
        });

        fileUploadBoxFeedback.addEventListener('dragleave', () => {
            fileUploadBoxFeedback.classList.remove('drag-over');
        });

        fileUploadBoxFeedback.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadBoxFeedback.classList.remove('drag-over');
            
            if (e.dataTransfer.files.length) {
                fileInputFeedback.files = e.dataTransfer.files;
                handleFileSelectFeedback();
            }
        });

        function handleFileSelectFeedback() {
            const file = fileInputFeedback.files[0];
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Hanya file JPG, PNG, dan GIF yang diizinkan!');
                    fileInputFeedback.value = '';
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5242880) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    fileInputFeedback.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImageFeedback.src = e.target.result;
                    fileNameFeedback.textContent = file.name;
                    uploadPlaceholderFeedback.classList.add('hidden');
                    previewContainerFeedback.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // Remove image
        removeImageBtnFeedback.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInputFeedback.value = '';
            previewImageFeedback.src = '';
            fileNameFeedback.textContent = '';
            uploadPlaceholderFeedback.classList.remove('hidden');
            previewContainerFeedback.classList.add('hidden');
        });
        <?php endif; ?>
    </script>

</body>

</html>
