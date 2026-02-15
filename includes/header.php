<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Aplikasi Pengaduan'; ?> - Aplikasi Pengaduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
        }
        .mobile-menu.active {
            max-height: 500px;
        }
        
        @media (max-width: 768px) {
            .user-info-text {
                display: none;
            }
        }
        
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
        
        @media (max-width: 768px) {
            .responsive-table {
                border: 0;
            }
            .responsive-table thead {
                display: none;
            }
            .responsive-table tr {
                margin-bottom: 1rem;
                display: block;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                background: white;
            }
            .responsive-table td {
                display: block;
                text-align: right;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #f3f4f6;
            }
            .responsive-table td:last-child {
                border-bottom: 0;
            }
            .responsive-table td:before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                color: #6b7280;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <nav class="bg-indigo-600 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <i class="fas fa-school text-white text-xl sm:text-2xl mr-2 sm:mr-3"></i>
                    <span class="text-white text-base sm:text-xl font-bold">Pengaduan Sekolah</span>
                </div>
                
                <div class="hidden md:flex items-center space-x-4">
                    <span class="text-white text-sm lg:text-base">
                        <i class="fas fa-user-circle mr-2"></i>
                        <span class="user-info-text"><?php echo $_SESSION['nama_lengkap']; ?></span>
                        <span class="text-indigo-200 text-xs lg:text-sm ml-2">(<?php echo ucfirst($_SESSION['role']); ?>)</span>
                    </span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 lg:px-4 py-2 rounded-lg transition duration-200 text-sm lg:text-base">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
                
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-indigo-200 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div id="mobile-menu" class="mobile-menu md:hidden bg-indigo-700">
            <div class="px-4 py-3 space-y-3">
                <div class="text-white border-b border-indigo-500 pb-3">
                    <i class="fas fa-user-circle mr-2"></i>
                    <?php echo $_SESSION['nama_lengkap']; ?>
                    <span class="block text-indigo-200 text-sm mt-1"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
                <a href="logout.php" class="block bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-200 text-center">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>
    <div class="bg-white shadow-md sticky top-16 z-40">
        <div class="max-w-7xl mx-auto">
            <div class="flex overflow-x-auto">
                <a href="dashboard.php" class="tab-item flex-shrink-0 px-4 sm:px-6 py-3 sm:py-4 <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50'; ?> transition duration-200 text-sm sm:text-base whitespace-nowrap">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                
                <?php if ($_SESSION['role'] == 'siswa'): ?>
                <a href="form_aspirasi.php" class="tab-item flex-shrink-0 px-4 sm:px-6 py-3 sm:py-4 <?php echo (basename($_SERVER['PHP_SELF']) == 'form_aspirasi.php') ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50'; ?> transition duration-200 text-sm sm:text-base whitespace-nowrap">
                    <i class="fas fa-plus-circle mr-2"></i>Buat Aspirasi
                </a>
                <?php endif; ?>
                
                <a href="list_aspirasi.php" class="tab-item flex-shrink-0 px-4 sm:px-6 py-3 sm:py-4 <?php echo (basename($_SERVER['PHP_SELF']) == 'list_aspirasi.php') ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50'; ?> transition duration-200 text-sm sm:text-base whitespace-nowrap">
                    <i class="fas fa-list mr-2"></i>Daftar Aspirasi
                </a>
                
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="laporan.php" class="tab-item flex-shrink-0 px-4 sm:px-6 py-3 sm:py-4 <?php echo (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50'; ?> transition duration-200 text-sm sm:text-base whitespace-nowrap">
                    <i class="fas fa-chart-bar mr-2"></i>Laporan
                </a>
                <a href="kelola_user.php" class="tab-item flex-shrink-0 px-4 sm:px-6 py-3 sm:py-4 <?php echo (basename($_SERVER['PHP_SELF']) == 'kelola_user.php') ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50'; ?> transition duration-200 text-sm sm:text-base whitespace-nowrap">
                    <i class="fas fa-users mr-2"></i>Kelola User
                </a>
                <a href="kelola_kategori.php" class="tab-item flex-shrink-0 px-4 sm:px-6 py-3 sm:py-4 <?php echo (basename($_SERVER['PHP_SELF']) == 'kelola_kategori.php') ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50'; ?> transition duration-200 text-sm sm:text-base whitespace-nowrap">
                    <i class="fas fa-tags mr-2"></i>Kelola Kategori
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
