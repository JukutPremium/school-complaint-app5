<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

$stats = array();

$stats['total'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM aspirasi"))['count'];

$status_query = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM aspirasi GROUP BY status");
while ($row = mysqli_fetch_assoc($status_query)) {
    $stats['status_' . $row['status']] = $row['count'];
}

$kategori_query = mysqli_query($conn, "SELECT k.nama_kategori, COUNT(a.id) as count 
                                       FROM kategori k 
                                       LEFT JOIN aspirasi a ON k.id = a.kategori_id 
                                       GROUP BY k.id, k.nama_kategori 
                                       ORDER BY count DESC");

$prioritas_query = mysqli_query($conn, "SELECT prioritas, COUNT(*) as count FROM aspirasi GROUP BY prioritas");

$bulan_query = mysqli_query($conn, "SELECT 
                                        DATE_FORMAT(tanggal_pengaduan, '%Y-%m') as bulan,
                                        COUNT(*) as count 
                                    FROM aspirasi 
                                    WHERE tanggal_pengaduan >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                                    GROUP BY DATE_FORMAT(tanggal_pengaduan, '%Y-%m')
                                    ORDER BY bulan DESC");

$siswa_query = mysqli_query($conn, "SELECT u.id, u.nama_lengkap, u.kelas, COUNT(a.id) as count 
                                    FROM users u 
                                    LEFT JOIN aspirasi a ON u.id = a.user_id 
                                    WHERE u.role = 'siswa' 
                                    GROUP BY u.id, u.nama_lengkap, u.kelas 
                                    ORDER BY count DESC 
                                    LIMIT 5");

$avg_time = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(DATEDIFF(tanggal_selesai, tanggal_pengaduan)) as avg_days 
                                                     FROM aspirasi 
                                                     WHERE status = 'selesai' AND tanggal_selesai IS NOT NULL"));

$bulan_names = array();
$bulan_counts = array();
mysqli_data_seek($bulan_query, 0);
while ($bulan = mysqli_fetch_assoc($bulan_query)) {
    $date = $bulan['bulan'] . '-01';
    $bulan_names[] = date('F Y', strtotime($date));
    $bulan_counts[] = $bulan['count'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Aplikasi Pengaduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="list_aspirasi.php" class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-list mr-2"></i>Daftar Aspirasi
                </a>
                <a href="laporan.php" class="px-6 py-4 text-indigo-600 border-b-2 border-indigo-600 font-semibold">
                    <i class="fas fa-chart-bar mr-2"></i>Laporan
                </a>
                <a href="kelola_user.php" class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-users mr-2"></i>Kelola User
                </a>
                <a href="kelola_kategori.php" class="px-6 py-4 text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-tags mr-2"></i>Kelola Kategori
                </a>
            </div>
        </div>

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-chart-line mr-3 text-indigo-600"></i>Laporan & Statistik
            </h1>
            <p class="text-gray-600 mt-1">Analisis data pengaduan sarana sekolah</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold uppercase">Total</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total']; ?></p>
                    </div>
                    <i class="fas fa-clipboard-list text-indigo-600 text-3xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold uppercase">Pending</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo isset($stats['status_pending']) ? $stats['status_pending'] : 0; ?></p>
                    </div>
                    <i class="fas fa-clock text-yellow-600 text-3xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold uppercase">Diproses</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo isset($stats['status_diproses']) ? $stats['status_diproses'] : 0; ?></p>
                    </div>
                    <i class="fas fa-spinner text-blue-600 text-3xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold uppercase">Selesai</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo isset($stats['status_selesai']) ? $stats['status_selesai'] : 0; ?></p>
                    </div>
                    <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>Aspirasi per Kategori
                </h3>
                <canvas id="kategoriChart"></canvas>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>Status Aspirasi
                </h3>
                <canvas id="statusChart"></canvas>
            </div>

        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-chart-line mr-2 text-indigo-600"></i>Trend Aspirasi (6 Bulan Terakhir)
            </h3>
            <canvas id="trendChart"></canvas>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-trophy mr-2 text-yellow-500"></i>Top 5 Siswa Aktif
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Rank</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Nama</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Kelas</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-600">Aspirasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php 
                            $rank = 1;
                            while ($siswa = mysqli_fetch_assoc($siswa_query)): 
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <?php
                                    $medal = ['🥇', '🥈', '🥉'];
                                    echo $rank <= 3 ? $medal[$rank-1] : $rank;
                                    ?>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800"><?php echo $siswa['nama_lengkap']; ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo $siswa['kelas']; ?></td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full font-semibold">
                                        <?php echo $siswa['count']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php 
                            $rank++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>Tingkat Prioritas
                </h3>
                <div class="space-y-4">
                    <?php 
                    $prioritas_data = array();
                    while ($prioritas = mysqli_fetch_assoc($prioritas_query)) {
                        $prioritas_data[$prioritas['prioritas']] = $prioritas['count'];
                    }
                    
                    $prioritas_info = [
                        'tinggi' => ['color' => 'red', 'icon' => 'exclamation-triangle'],
                        'sedang' => ['color' => 'yellow', 'icon' => 'exclamation-circle'],
                        'rendah' => ['color' => 'green', 'icon' => 'info-circle']
                    ];
                    
                    foreach ($prioritas_info as $key => $info):
                        $count = isset($prioritas_data[$key]) ? $prioritas_data[$key] : 0;
                        $percentage = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
                    ?>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-semibold text-gray-700">
                                <i class="fas fa-<?php echo $info['icon']; ?> mr-2 text-<?php echo $info['color']; ?>-600"></i>
                                <?php echo ucfirst($key); ?>
                            </span>
                            <span class="text-sm font-bold text-gray-800"><?php echo $count; ?> (<?php echo round($percentage, 1); ?>%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-<?php echo $info['color']; ?>-500 h-3 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </div>

    <script>
        const kategoriData = {
            labels: [
                <?php 
                mysqli_data_seek($kategori_query, 0);
                while ($kat = mysqli_fetch_assoc($kategori_query)) {
                    echo "'" . $kat['nama_kategori'] . "',";
                }
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    mysqli_data_seek($kategori_query, 0);
                    while ($kat = mysqli_fetch_assoc($kategori_query)) {
                        echo $kat['count'] . ",";
                    }
                    ?>
                ],
                backgroundColor: [
                    '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'
                ]
            }]
        };

        new Chart(document.getElementById('kategoriChart'), {
            type: 'doughnut',
            data: kategoriData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        const statusData = {
            labels: ['Pending', 'Diproses', 'Selesai', 'Ditolak'],
            datasets: [{
                label: 'Jumlah',
                data: [
                    <?php echo isset($stats['status_pending']) ? $stats['status_pending'] : 0; ?>,
                    <?php echo isset($stats['status_diproses']) ? $stats['status_diproses'] : 0; ?>,
                    <?php echo isset($stats['status_selesai']) ? $stats['status_selesai'] : 0; ?>,
                    <?php echo isset($stats['status_ditolak']) ? $stats['status_ditolak'] : 0; ?>
                ],
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444']
            }]
        };

        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: statusData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        const trendData = {
            labels: [
                <?php 
                $reversed_names = array_reverse($bulan_names);
                foreach ($reversed_names as $name) {
                    echo "'" . $name . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Jumlah Aspirasi',
                data: [
                    <?php 
                    $reversed_counts = array_reverse($bulan_counts);
                    echo implode(',', $reversed_counts);
                    ?>
                ],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: trendData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>