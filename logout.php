<?php
// 1. Inisialisasi session
session_start();

// 2. Kosongkan semua variabel session
$_SESSION = array();

// 3. Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Hancurkan session
session_destroy();

// 5. Redireksi kembali ke login.php dengan pesan sukses
header("Location: login.php?status=logout_success");
exit;
?>