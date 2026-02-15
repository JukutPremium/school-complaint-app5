<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$page_title = 'Detail Aspirasi';
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
            if ($foto_name && file_exists('uploads/' . $foto_name)) {
                unlink('uploads/' . $foto_name);
            }
        }
    }
}

$status_colors = [
    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'diproses' => 'bg-blue-100 text-blue-800 border-blue-300',
    'selesai' => 'bg-green-100 text-green-800 border-green-300',
    'ditolak' => 'bg-red-100 text-red-800 border-red-300'
];

$prioritas_colors = [
    'rendah' => 'bg-green-100 text-green-800',
    'sedang' => 'bg-yellow-100 text-yellow-800',
    'tinggi' => 'bg-red-100 text-red-800'
];

include 'includes/header.php';
?>

<style>
.image-modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}
.image-modal img {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
    margin-top: 50px;
}
.image-modal .close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}
</style>

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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
    
    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
        
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 pb-4 border-b">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2 sm:mb-0">Detail Aspirasi</h1>
                <a href="list_aspirasi.php" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm sm:text-base">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>

            <div class="mb-4 sm:mb-6">
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-3"><?php echo $aspirasi['judul']; ?></h2>
                <div class="flex flex-wrap gap-2">
                    <span class="<?php echo $status_colors[$aspirasi['status']]; ?> px-3 py-1 rounded-full text-xs sm:text-sm font-semibold border">
                        <?php echo ucfirst($aspirasi['status']); ?>
                    </span>
                    <span class="<?php echo $prioritas_colors[$aspirasi['prioritas']]; ?> px-3 py-1 rounded-full text-xs sm:text-sm font-semibold">
                        Prioritas: <?php echo ucfirst($aspirasi['prioritas']); ?>
                    </span>
                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold">
                        <?php echo $aspirasi['nama_kategori']; ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <?php if ($role == 'admin'): ?>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Pelapor</p>
                    <p class="text-sm sm:text-base font-semibold text-gray-800">
                        <i class="fas fa-user mr-2 text-indigo-600"></i>
                        <?php echo $aspirasi['nama_lengkap']; ?>
                    </p>
                    <p class="text-xs text-gray-600 ml-6"><?php echo $aspirasi['kelas']; ?></p>
                </div>
                <?php endif; ?>
                
                <div>
                    <p class="text-xs text-gray-500 mb-1">Lokasi</p>
                    <p class="text-sm sm:text-base font-semibold text-gray-800">
                        <i class="fas fa-map-marker-alt mr-2 text-red-600"></i>
                        <?php echo $aspirasi['lokasi']; ?>
                    </p>
                </div>
                
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal Pengaduan</p>
                    <p class="text-sm sm:text-base font-semibold text-gray-800">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>
                        <?php echo date('d F Y, H:i', strtotime($aspirasi['tanggal_pengaduan'])); ?>
                    </p>
                </div>
                
                <?php if ($aspirasi['tanggal_selesai']): ?>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal Selesai</p>
                    <p class="text-sm sm:text-base font-semibold text-gray-800">
                        <i class="fas fa-check-circle mr-2 text-green-600"></i>
                        <?php echo date('d F Y, H:i', strtotime($aspirasi['tanggal_selesai'])); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <div class="mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">
                    <i class="fas fa-file-alt mr-2 text-indigo-600"></i>Deskripsi
                </h3>
                <p class="text-sm sm:text-base text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-lg">
                    <?php echo nl2br($aspirasi['deskripsi']); ?>
                </p>
            </div>

            <?php if ($aspirasi['foto']): ?>
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3">
                    <i class="fas fa-image mr-2 text-indigo-600"></i>Foto Pengaduan
                </h3>
                <img src="uploads/<?php echo $aspirasi['foto']; ?>" 
                     alt="Foto Aspirasi" 
                     class="rounded-lg shadow-md max-w-full h-auto cursor-pointer hover:opacity-90 transition duration-200"
                     onclick="openImageModal(this.src)">
            </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-comments mr-2"></i>Riwayat Umpan Balik
                <span class="text-sm sm:text-base font-normal text-gray-600 ml-2">
                    (<?php echo mysqli_num_rows($feedback_result); ?> feedback)
                </span>
            </h2>

            <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                <div class="space-y-4">
                    <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                    <div class="border-l-4 border-indigo-500 bg-gray-50 p-4 rounded-lg">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-2">
                            <div class="mb-2 sm:mb-0">
                                <p class="font-semibold text-gray-800 text-sm sm:text-base">
                                    <i class="fas fa-user-tie mr-2 text-indigo-600"></i>
                                    <?php echo $feedback['admin_nama']; ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo date('d F Y, H:i', strtotime($feedback['tanggal_feedback'])); ?>
                                </p>
                            </div>
                            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                Progres: <?php echo $feedback['progres']; ?>%
                            </span>
                        </div>
                        <p class="text-sm sm:text-base text-gray-700 mt-2">
                            <?php echo nl2br($feedback['keterangan']); ?>
                        </p>
                        <?php if ($feedback['foto']): ?>
                        <img src="uploads/<?php echo $feedback['foto']; ?>" 
                             alt="Foto Feedback" 
                             class="mt-3 rounded-lg shadow-md max-w-full sm:max-w-md h-auto cursor-pointer hover:opacity-90 transition duration-200"
                             onclick="openImageModal(this.src)">
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-3xl sm:text-4xl mb-2 block"></i>
                    <p class="text-sm sm:text-base">Belum ada umpan balik</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($role == 'admin'): ?>
    <div class="lg:col-span-1 space-y-4 sm:space-y-6">
        
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-edit mr-2"></i>Ubah Status
            </h3>
            <form method="POST" action="">
                <select name="status" required
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 mb-3 text-sm sm:text-base">
                    <option value="pending" <?php echo $aspirasi['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="diproses" <?php echo $aspirasi['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="selesai" <?php echo $aspirasi['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="ditolak" <?php echo $aspirasi['status'] == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                </select>
                <button type="submit" name="update_status"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 sm:py-3 rounded-lg transition duration-200 text-sm sm:text-base">
                    <i class="fas fa-save mr-2"></i>Update Status
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-comment-medical mr-2"></i>Tambah Umpan Balik
            </h3>
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-3 sm:space-y-4">
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Progres (%)</label>
                    <input type="number" name="progres" min="0" max="100" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm sm:text-base"
                        placeholder="0-100">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" required rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm sm:text-base"
                        placeholder="Jelaskan update atau progres terbaru..."></textarea>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Upload Foto (Opsional)</label>
                    <div class="file-upload-box rounded-lg p-4 text-center cursor-pointer">
                        <input type="file" id="foto-feedback" name="foto" accept="image/*" class="hidden" onchange="previewImage(this)">
                        <label for="foto-feedback" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-2xl sm:text-3xl text-gray-400 mb-2 block"></i>
                            <p class="text-xs sm:text-sm text-gray-600">Klik untuk upload</p>
                        </label>
                    </div>
                    <div id="preview-container" class="hidden mt-3">
                        <img id="preview-image" class="rounded-lg shadow-md max-h-48 w-auto mx-auto" alt="Preview">
                    </div>
                </div>

                <button type="submit" name="tambah_feedback"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 sm:py-3 rounded-lg transition duration-200 text-sm sm:text-base">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Feedback
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="imageModal" class="image-modal" onclick="closeImageModal()">
    <span class="close">&times;</span>
    <img id="modalImage" src="">
</div>

<script>
function openImageModal(src) {
    document.getElementById('imageModal').style.display = 'block';
    document.getElementById('modalImage').src = src;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
