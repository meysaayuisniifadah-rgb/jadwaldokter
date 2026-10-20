<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, langsung redirect ke dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // Mencegah SQL Injection dengan Prepared Statement
        $stmt = mysqli_prepare($conn, "SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                // Verifikasi password (mengdukung password_hash maupun plain fallback untuk pengujian)
                if (password_verify($password, $row['password']) || $password === $row['password']) {
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                    $_SESSION['role'] = $row['role'];

                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Password yang Anda masukkan salah!";
                }
            } else {
                $error = "Username tidak ditemukan!";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Terjadi kesalahan sistem pada database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System - MEDSCHED</title>
    <style>
        :root {
            --primary: #1A56DB;
            --primary-hover: #1d4ed8;
            --primary-soft: #f0f5ff;
            --teal: #0D9488;
            --bg-main: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --status-danger: #EF4444;
            --status-danger-bg: #fef2f2;
            --status-danger-border: #fecaca;
            --radius-md: 10px;
            --radius-lg: 16px;
            --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 2px 4px -2px rgba(15, 23, 42, 0.04);
            --shadow-lg: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background-color: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
            padding: 32px 28px;
            transition: all 0.2s ease-in-out;
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--teal) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 3px 8px rgba(26, 86, 219, 0.25);
        }

        .brand-logo svg {
            width: 22px;
            height: 22px;
        }

        .brand-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.1;
            letter-spacing: -0.2px;
        }

        .brand-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .form-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .form-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .alert-error {
            background-color: var(--status-danger-bg);
            color: #991b1b;
            border: 1px solid var(--status-danger-border);
            padding: 10px 12px;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper svg {
            position: absolute;
            left: 12px;
            width: 16px;
            height: 16px;
            color: var(--text-muted);
            stroke-width: 2;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px 9px 36px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 13px;
            outline: none;
            background-color: var(--surface);
            color: var(--text-dark);
            transition: all 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.12);
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: var(--primary);
            color: white;
            font-size: 13px;
            font-weight: 700;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            box-shadow: 0 2px 6px rgba(26, 86, 219, 0.25);
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
            padding-top: 16px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </div>
            <div>
                <div class="brand-title">MEDSCHED</div>
                <div class="brand-subtitle">Sistem Penjadwalan Dokter</div>
            </div>
        </div>

<h2 class="form-title">Selamat Datang</h2>
        <p class="form-subtitle">Masukkan akun Anda untuk masuk ke sistem</p>

        <!-- Pesan Notifikasi Berhasil Logout -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'logout_success'): ?>
            <div style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 10px 12px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>Anda telah berhasil keluar dari sistem.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>

            <div class="alert-error">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Masuk ke System
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <div style="margin-top: 18px; padding: 10px; background-color: var(--primary-soft); border: 1px dashed var(--primary); border-radius: var(--radius-md); font-size: 11px; color: var(--text-dark);">
            <strong style="color: var(--primary);">🔑 Akun Login Default (Demo):</strong><br>
            • Username: <b>admin</b><br>
            • Password: <b>admin123</b>
        </div>

        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> MedSched System. Hak Cipta Dilindungi.
        </div>
    </div>

</body>
</html>