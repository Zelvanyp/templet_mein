<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $id = $_POST['id'];

    $cek_pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id_pelanggan = '$id'");
    $cek_penjual   = mysqli_query($conn, "SELECT * FROM penjual WHERE id_penjual = '$id'");

    if (mysqli_num_rows($cek_pelanggan) > 0) {
        $_SESSION['id_pelanggan'] = $id;
        header("Location: pembeli.php");
        exit;
    } elseif (mysqli_num_rows($cek_penjual) > 0) {
        $_SESSION['id_penjual'] = $id;
        header("Location: penjual.php");
        exit;
    } else {
        $error = "ID tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Penjualan</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<div class="login-container">
    <h1>Login Penjualan</h1>
    <p class="subtitle">Login Sistem Penjualan</p>

    <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>

    <form method="POST">
        <input type="text" name="id" placeholder="Masukkan ID" required>
        <button type="submit" name="login">Login</button>
    </form>

    <div class="register-prompt">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>
</div>

</body>
</html>
