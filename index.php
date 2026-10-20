<?php
session_start();

// Proteksi Halaman - Cek apakah user sudah login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Ambil Data Statistik untuk Dashboard
$total_dokter = 0;
$total_jadwal = 0;
$jadwal_hari_ini = 0;

$res_doc = mysqli_query($conn, "SELECT COUNT(*) as total FROM dokter");
if ($res_doc) {
    $total_dokter = mysqli_fetch_assoc($res_doc)['total'];
}

$res_jdw = mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwal_praktik");
if ($res_jdw) {
    $total_jadwal = mysqli_fetch_assoc($res_jdw)['total'];
}

// Map nama hari bahasa Indonesia
$days_map = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$hari_ini = $days_map[date('l')];

$res_today = mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwal_praktik WHERE hari = '$hari_ini'");
if ($res_today) {
    $jadwal_hari_ini = mysqli_fetch_assoc($res_today)['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penjadwalan Dokter - MedSched Pro</title>
    <style>
        :root {
            --primary: #1A56DB;
            --primary-hover: #1d4ed8;
            --primary-soft: #f0f5ff;
            --teal: #0D9488;
            --teal-soft: #f0fdfa;
            --bg-main: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --status-available: #10B981;
            --status-available-bg: #ecfdf5;
            --status-available-border: #a7f3d0;
            --status-warning: #F59E0B;
            --status-warning-bg: #fffbeb;
            --status-warning-border: #fef08a;
            --status-danger: #EF4444;
            --status-danger-bg: #fef2f2;
            --status-danger-border: #fecaca;
            --shadow-xs: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
            --shadow-sm: 0 1px 3px 0 rgba(15, 23, 42, 0.06), 0 1px 2px -1px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 2px 4px -2px rgba(15, 23, 42, 0.04);
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --transition: all 0.15s ease-in-out;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Inter, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-dark);
            line-height: 1.45;
            display: flex;
            min-height: 100vh;
            font-size: 13px;
        }

        aside {
            width: 240px;
            background-color: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
            box-shadow: 1px 0 6px rgba(0,0,0,0.02);
            transition: var(--transition);
        }

        .brand {
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border);
        }

        .brand-logo {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--teal) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 2px 6px rgba(26, 86, 219, 0.2);
        }

        .brand-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.1;
            letter-spacing: -0.2px;
        }

        .brand-subtitle {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .nav-list {
            list-style: none;
            padding: 12px 8px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            flex: 1;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            transition: var(--transition);
        }

        .nav-item a:hover {
            background-color: var(--primary-soft);
            color: var(--primary);
        }

        .nav-item.active a {
            background-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(26, 86, 219, 0.25);
        }

        .nav-item.logout-item a {
            color: var(--status-danger);
        }

        .nav-item.logout-item a:hover {
            background-color: var(--status-danger-bg);
            color: var(--status-danger);
        }

        .nav-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .nav-icon svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        main {
            margin-left: 240px;
            flex: 1;
            padding: 24px 28px;
            max-width: calc(100% - 240px);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .page-header h2 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.3px;
        }

        .page-header p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .admin-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-xs);
        }

        .admin-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--teal-soft);
            color: var(--teal);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 10px;
        }

        .admin-text span {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.1;
        }

        .admin-text small {
            font-size: 9px;
            color: var(--text-muted);
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: var(--surface);
            border-radius: var(--radius-md);
            padding: 14px 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            border-color: var(--border-hover);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .stat-icon.blue { background-color: var(--primary-soft); color: var(--primary); }
        .stat-icon.teal { background-color: var(--teal-soft); color: var(--teal); }
        .stat-icon.green { background-color: var(--status-available-bg); color: var(--status-available); }

        .stat-info h3 {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.1;
            margin-bottom: 2px;
        }

        .stat-info p {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .content-card {
            background-color: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background-color: var(--surface);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-header h3 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .card-body {
            padding: 18px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: var(--bg-main);
            color: var(--text-muted);
            font-weight: 700;
            padding: 8px 12px;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-dark);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background-color: #f1f5f9;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-blue { background-color: var(--primary-soft); color: var(--primary); }
        .badge-teal { background-color: var(--teal-soft); color: var(--teal); }

        .status-available {
            background-color: var(--status-available-bg);
            color: var(--status-available);
            border: 1px solid var(--status-available-border);
        }

        .status-warning {
            background-color: var(--status-warning-bg);
            color: var(--status-warning);
            border: 1px solid var(--status-warning-border);
        }

        .status-danger {
            background-color: var(--status-danger-bg);
            color: var(--status-danger);
            border: 1px solid var(--status-danger-border);
        }

        .status-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-available .status-dot { background-color: var(--status-available); }
        .status-warning .status-dot { background-color: var(--status-warning); }
        .status-danger .status-dot { background-color: var(--status-danger); }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-group label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .form-control {
            padding: 7px 10px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 12px;
            outline: none;
            background-color: var(--surface);
            color: var(--text-dark);
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(26, 86, 219, 0.12);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary { 
            background-color: var(--primary); 
            color: white; 
            box-shadow: 0 1px 3px rgba(26, 86, 219, 0.2);
        }
        .btn-primary:hover { 
            background-color: var(--primary-hover);
        }

        .btn-teal {
            background-color: var(--teal);
            color: white;
            box-shadow: 0 1px 3px rgba(13, 148, 136, 0.2);
        }
        .btn-teal:hover {
            background-color: #0f766e;
        }

        .btn-danger { 
            background-color: var(--status-danger-bg); 
            color: var(--status-danger); 
            border: 1px solid var(--status-danger-border);
        }
        .btn-danger:hover { 
            background-color: var(--status-danger); 
            color: white; 
        }

        .btn-secondary { 
            background-color: #f1f5f9; 
            color: var(--text-dark); 
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { 
            background-color: #e2e8f0; 
        }

        .btn-sm { 
            padding: 4px 8px; 
            font-size: 11px; 
            border-radius: 4px;
        }

        .btn svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .actions {
            display: flex;
            gap: 4px;
        }

        .alert {
            padding: 10px 14px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .alert-success { background-color: var(--status-available-bg); color: #065f46; border: 1px solid var(--status-available-border); }
        .alert-danger { background-color: var(--status-danger-bg); color: #991b1b; border: 1px solid var(--status-danger-border); }

        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            background: var(--surface);
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            align-items: center;
        }

        .filter-bar .form-control {
            min-width: 150px;
        }

        @media (max-width: 900px) {
            aside {
                width: 60px;
            }
            .brand-text, .nav-text, .admin-text {
                display: none;
            }
            .brand {
                justify-content: center;
                padding: 12px 6px;
            }
            main {
                margin-left: 60px;
                max-width: calc(100% - 60px);
                padding: 16px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            aside, .btn, .filter-bar, .actions, .top-bar, #alertBox {
                display: none !important;
            }
            main {
                margin: 0 !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
            .content-card {
                border: none !important;
                box-shadow: none !important;
            }
            table {
                border: 1px solid #000;
            }
            th, td {
                border: 1px solid #000 !important;
            }
        }
    </style>
</head>
<body>

    <aside>
        <div class="brand">
            <div class="brand-logo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </div>
            <div class="brand-text">
                <div class="brand-title">MEDSCHED</div>
                <div class="brand-subtitle">Sistem Penjadwalan</div>
            </div>
        </div>
        <ul class="nav-list">
            <li class="nav-item active" id="nav-dashboard">
                <a href="#dashboard" onclick="switchTab('dashboard')">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item" id="nav-dokter">
                <a href="#dokter" onclick="switchTab('dokter')">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                    </span>
                    <span class="nav-text">Data Dokter</span>
                </a>
            </li>
            <li class="nav-item" id="nav-jadwal">
                <a href="#jadwal" onclick="switchTab('jadwal')">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </span>
                    <span class="nav-text">Jadwal Praktik</span>
                </a>
            </li>
            <li class="nav-item" id="nav-laporan">
                <a href="#laporan" onclick="switchTab('laporan')">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    </span>
                    <span class="nav-text">Laporan Jadwal</span>
                </a>
            </li>
            <li class="nav-item logout-item" style="margin-top: auto;">
                <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </span>
                    <span class="nav-text">Keluar</span>
                </a>
            </li>
        </ul>
    </aside>

    <main>
        <div class="top-bar">
            <div class="page-header">
                <h2 id="pageTitle">Dashboard Utama</h2>
                <p id="pageSubTitle">Ringkasan Informasi & Jadwal Praktik Dokter Real-Time</p>
            </div>
            <div class="admin-badge">
                <div class="admin-avatar">ADM</div>
                <div class="admin-text">
                    <span><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Administrator'); ?></span>
                    <small>Sistem Informasi RS</small>
                </div>
            </div>
        </div>

        <div id="alertBox" class="alert alert-danger" style="display: none;">
            <span id="alertMessage">Pesan Notifikasi</span>
            <button type="button" class="btn btn-sm btn-secondary" onclick="hideAlert()">✕</button>
        </div>

        <!-- ==================== DASHBOARD TAB ==================== -->
        <section id="sec-dashboard" class="tab-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div class="stat-info">
                        <h3 id="statTotalDokter"><?php echo $total_dokter; ?></h3>
                        <p>Total Dokter Terdaftar</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon teal">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div class="stat-info">
                        <h3 id="statTotalJadwal"><?php echo $total_jadwal; ?></h3>
                        <p>Total Alokasi Jadwal</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="stat-info">
                        <h3 id="statJadwalHariIni"><?php echo $jadwal_hari_ini; ?></h3>
                        <p>Praktik Hari Ini (<?php echo $hari_ini; ?>)</p>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3>Jadwal Praktik Dokter Hari Ini</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Dokter</th>
                                    <th>Spesialisasi</th>
                                    <th>Ruangan Praktik</th>
                                    <th>Jam Operasional</th>
                                    <th>Status Praktik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query_today = "SELECT j.*, d.nama, d.spesialis 
                                                FROM jadwal_praktik j 
                                                JOIN dokter d ON j.dokter_id = d.id 
                                                WHERE j.hari = '$hari_ini' 
                                                ORDER BY j.jam_mulai ASC";
                                $res_today_list = mysqli_query($conn, $query_today);

                                if ($res_today_list && mysqli_num_rows($res_today_list) > 0):
                                    while ($row = mysqli_fetch_assoc($res_today_list)):
                                        $statusClass = 'status-available';
                                        if ($row['status'] == 'Penuh') $statusClass = 'status-warning';
                                        if ($row['status'] == 'Berhalangan') $statusClass = 'status-danger';
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($row['spesialis']); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['ruangan']); ?></td>
                                        <td><strong><?php echo substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <span class="status-dot"></span>
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 18px;">Tidak ada jadwal praktik dokter untuk hari ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== DOKTER TAB ==================== -->
        <section id="sec-dokter" class="tab-content" style="display: none;">
            <div class="content-card">
                <div class="card-header">
                    <h3 id="formDoctorTitle">Form Input Data Dokter</h3>
                </div>
                <div class="card-body">
                    <form id="formDokter" onsubmit="alert('Pengembangan CRUD Dokter aktif'); return false;">
                        <input type="hidden" id="doc_id">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>NIP / ID Dokter *</label>
                                <input type="text" id="doc_nip" class="form-control" placeholder="Contoh: D-101" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap Dokter *</label>
                                <input type="text" id="doc_nama" class="form-control" placeholder="dr. Budi Santoso, Sp.PD" required>
                            </div>
                            <div class="form-group">
                                <label>Spesialisasi *</label>
                                <select id="doc_spesialis" class="form-control" required>
                                    <option value="">-- Pilih Spesialisasi --</option>
                                    <option value="Spesialis Penyakit Dalam">Spesialis Penyakit Dalam</option>
                                    <option value="Spesialis Anak">Spesialis Anak</option>
                                    <option value="Spesialis Kandungan (Obgyn)">Spesialis Kandungan (Obgyn)</option>
                                    <option value="Spesialis Jantung">Spesialis Jantung</option>
                                    <option value="Spesialis Gigi & Mulut">Spesialis Gigi & Mulut</option>
                                    <option value="Spesialis Mata">Spesialis Mata</option>
                                    <option value="Dokter Umum">Dokter Umum</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nomor HP / WA *</label>
                                <input type="text" id="doc_telepon" class="form-control" placeholder="081234567890" required>
                            </div>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn btn-primary">Simpan Data Dokter</button>
                            <button type="reset" class="btn btn-secondary">Batal / Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3>Daftar Dokter Terdaftar</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama Dokter</th>
                                    <th>Spesialisasi</th>
                                    <th>No. Telepon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res_all_doc = mysqli_query($conn, "SELECT * FROM dokter ORDER BY nama ASC");
                                if ($res_all_doc && mysqli_num_rows($res_all_doc) > 0):
                                    while ($doc = mysqli_fetch_assoc($res_all_doc)):
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($doc['nip']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($doc['nama']); ?></td>
                                        <td><span class="badge badge-teal"><?php echo htmlspecialchars($doc['spesialis']); ?></span></td>
                                        <td><?php echo htmlspecialchars($doc['telepon']); ?></td>
                                    </tr>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== JADWAL TAB ==================== -->
        <section id="sec-jadwal" class="tab-content" style="display: none;">
            <div class="content-card">
                <div class="card-header">
                    <h3 id="formJadwalTitle">Form Penjadwalan Praktik</h3>
                </div>
                <div class="card-body">
                    <form id="formJadwal" onsubmit="alert('Pengembangan CRUD Jadwal aktif'); return false;">
                        <input type="hidden" id="jdwl_id">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Pilih Dokter *</label>
                                <select id="jdwl_dokter_id" class="form-control" required>
                                    <option value="">-- Pilih Dokter --</option>
                                    <?php
                                    $res_select_doc = mysqli_query($conn, "SELECT id, nama, spesialis FROM dokter ORDER BY nama ASC");
                                    while ($d = mysqli_fetch_assoc($res_select_doc)) {
                                        echo "<option value='{$d['id']}'>{$d['nama']} - {$d['spesialis']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Hari Praktik *</label>
                                <select id="jdwl_hari" class="form-control" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Jam Mulai *</label>
                                <input type="time" id="jdwl_jam_mulai" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Jam Selesai *</label>
                                <input type="time" id="jdwl_jam_selesai" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Ruangan Praktik *</label>
                                <input type="text" id="jdwl_ruangan" class="form-control" placeholder="Poli Dalam R.1" required>
                            </div>
                            <div class="form-group">
                                <label>Kuota Pasien *</label>
                                <input type="number" id="jdwl_kuota" class="form-control" placeholder="15" min="1" required>
                            </div>
                            <div class="form-group">
                                <label>Status Ketersediaan *</label>
                                <select id="jdwl_status" class="form-control" required>
                                    <option value="Tersedia">Tersedia (Hadir)</option>
                                    <option value="Penuh">Penuh (Kuota Habis)</option>
                                    <option value="Berhalangan">Berhalangan (Libur)</option>
                                </select>
                            </div>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn btn-teal">Simpan Jadwal Praktik</button>
                            <button type="reset" class="btn btn-secondary">Batal / Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- ==================== LAPORAN TAB ==================== -->
        <section id="sec-laporan" class="tab-content" style="display: none;">
            <div class="content-card">
                <div class="card-header">
                    <h3>Cetak Laporan Jadwal Praktik Dokter</h3>
                    <button class="btn btn-primary" onclick="window.print()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        Cetak Laporan
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Nama Dokter</th>
                                    <th>Spesialisasi</th>
                                    <th>Ruangan</th>
                                    <th>Jam Operasional</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query_all_jdw = "SELECT j.*, d.nama, d.spesialis 
                                                  FROM jadwal_praktik j 
                                                  JOIN dokter d ON j.dokter_id = d.id 
                                                  ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), j.jam_mulai ASC";
                                $res_all_jdw = mysqli_query($conn, $query_all_jdw);

                                if ($res_all_jdw && mysqli_num_rows($res_all_jdw) > 0):
                                    while ($jdw = mysqli_fetch_assoc($res_all_jdw)):
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($jdw['hari']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($jdw['nama']); ?></td>
                                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($jdw['spesialis']); ?></span></td>
                                        <td><?php echo htmlspecialchars($jdw['ruangan']); ?></td>
                                        <td><?php echo substr($jdw['jam_mulai'], 0, 5) . ' - ' . substr($jdw['jam_selesai'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($jdw['status']); ?></td>
                                    </tr>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function switchTab(tabName) {
            // Sembunyikan semua konten tab
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.style.display = 'none');

            // Nonaktifkan semua status navigasi
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => item.classList.remove('active'));

            // Tampilkan tab yang dipilih
            const targetTab = document.getElementById('sec-' + tabName);
            if (targetTab) {
                targetTab.style.display = 'block';
            }

            // Aktifkan navigasi yang sesuai
            const targetNav = document.getElementById('nav-' + tabName);
            if (targetNav) {
                targetNav.classList.add('active');
            }

            // Perbarui judul halaman
            const titleMap = {
                'dashboard': ['Dashboard Utama', 'Ringkasan Informasi & Jadwal Praktik Dokter Real-Time'],
                'dokter': ['Kelola Data Dokter', 'Tambah, edit, dan hapus data dokter terdaftar'],
                'jadwal': ['Jadwal Praktik Dokter', 'Pengaturan jadwal operasional & ketersediaan kuota'],
                'laporan': ['Laporan Jadwal Praktik', 'Rekapitulasi jadwal operasional dokter siap cetak']
            };

            if (titleMap[tabName]) {
                document.getElementById('pageTitle').innerText = titleMap[tabName][0];
                document.getElementById('pageSubTitle').innerText = titleMap[tabName][1];
            }
        }

        function hideAlert() {
            document.getElementById('alertBox').style.display = 'none';
        }
    </script>
</body>
</html>