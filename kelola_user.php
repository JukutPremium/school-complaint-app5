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

    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM aspirasi WHERE user_id='$id'");
    $count = mysqli_fetch_assoc($check)['count'];

    if ($count > 0) {
        $error = 'User tidak dapat dihapus karena masih memiliki aspirasi!';
    } else {
        $delete_query = "DELETE FROM users WHERE id='$id' AND role!='admin'";
        if (mysqli_query($conn, $delete_query)) {
            $success = 'User berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus user!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? clean_input($_POST['id']) : '';
    $username = clean_input($_POST['username']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $role = clean_input($_POST['role']);
    $kelas = clean_input($_POST['kelas']);
    $password = $_POST['password'];

    if ($id) {
        if (!empty($password)) {
            $password_hash = md5($password);
            $query = "UPDATE users SET username='$username', password='$password_hash', nama_lengkap='$nama_lengkap', email='$email', role='$role', kelas='$kelas' WHERE id='$id'";
        } else {
            $query = "UPDATE users SET username='$username', nama_lengkap='$nama_lengkap', email='$email', role='$role', kelas='$kelas' WHERE id='$id'";
        }

        if (mysqli_query($conn, $query)) {
            $success = 'User berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate user: ' . mysqli_error($conn);
        }
    } else {
        if (empty($password)) {
            $error = 'Password harus diisi untuk user baru!';
        } else {
            $password_hash = md5($password);
            $query = "INSERT INTO users (username, password, nama_lengkap, email, role, kelas) VALUES ('$username', '$password_hash', '$nama_lengkap', '$email', '$role', '$kelas')";

            if (mysqli_query($conn, $query)) {
                $success = 'User berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan user: ' . mysqli_error($conn);
            }
        }
    }
}

$users_query = mysqli_query($conn, "SELECT u.*, COUNT(a.id) as jumlah_aspirasi FROM users u LEFT JOIN aspirasi a ON u.id = a.user_id GROUP BY u.id ORDER BY u.role, u.nama_lengkap");

$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = clean_input($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$edit_id'");
    $edit_user = mysqli_fetch_assoc($edit_result);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Aplikasi Pengaduan</title>
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
                <a href="kelola_user.php" class="px-6 py-4 text-indigo-600 border-b-2 border-indigo-600 font-semibold">
                    <i class="fas fa-users mr-2"></i>Kelola User
                </a>
                <a href="kelola_kategori.php"
                    class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
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
                        <i class="fas fa-user-plus mr-2 text-indigo-600"></i>
                        <?php echo $edit_user ? 'Edit User' : 'Tambah User Baru'; ?>
                    </h2>

                    <form method="POST" action="" class="space-y-4">
                        <?php if ($edit_user): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                        <?php endif; ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                            <input type="text" name="username" required
                                value="<?php echo $edit_user ? $edit_user['username'] : ''; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Password <?php echo $edit_user ? '(kosongkan jika tidak diubah)' : '*'; ?>
                            </label>
                            <input type="password" name="password" <?php echo $edit_user ? '' : 'required'; ?>
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" required
                                value="<?php echo $edit_user ? $edit_user['nama_lengkap'] : ''; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email"
                                value="<?php echo $edit_user ? $edit_user['email'] : ''; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                            <select name="role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="siswa" <?php echo ($edit_user && $edit_user['role'] == 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                                <option value="admin" <?php echo ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                            <input type="text" name="kelas" value="<?php echo $edit_user ? $edit_user['kelas'] : ''; ?>"
                                placeholder="Contoh: XII RPL 1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i><?php echo $edit_user ? 'Update' : 'Tambah'; ?>
                            </button>
                            <?php if ($edit_user): ?>
                                <a href="kelola_user.php"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-center transition duration-200">
                                    <i class="fas fa-times mr-2"></i>Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-users mr-2 text-indigo-600"></i>Daftar User
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-indigo-600 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Username</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Nama</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Role</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Kelas</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold">Aspirasi</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($user = mysqli_fetch_assoc($users_query)): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                            <?php echo $user['username']; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700"><?php echo $user['nama_lengkap']; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo $user['email'] ?: '-'; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <?php if ($user['role'] == 'admin'): ?>
                                                <span
                                                    class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-shield-alt mr-1"></i>Admin
                                                </span>
                                            <?php else: ?>
                                                <span
                                                    class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-user mr-1"></i>Siswa
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo $user['kelas'] ?: '-'; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <span
                                                class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $user['jumlah_aspirasi']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <div class="flex justify-center space-x-2">
                                                <a href="kelola_user.php?edit=<?php echo $user['id']; ?>"
                                                    class="text-blue-600 hover:text-blue-800" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <a href="kelola_user.php?delete=<?php echo $user['id']; ?>"
                                                        onclick="return confirm('Yakin ingin menghapus user ini?')"
                                                        class="text-red-600 hover:text-red-800" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>

</html>