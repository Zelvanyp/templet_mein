<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    $tipe = $_POST['tipe'];
    $id = $_POST['id'];
    $nama = $_POST['nama'];

    if ($tipe === 'pelanggan') {
        $alamat = $_POST['alamat'];
        $telepon = $_POST['telepon'];
        $query = "INSERT INTO pelanggan (id_pelanggan, nama_pelanggan, alamat, telepon) 
                  VALUES ('$id', '$nama', '$alamat', '$telepon')";
    } else {
        $query = "INSERT INTO penjual (id_penjual) 
                  VALUES ('$id')";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Gagal mendaftar. Cek kembali data atau ID mungkin sudah digunakan.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
    <script>
        function toggleForm() {
            var tipe = document.getElementById("tipe").value;
            document.getElementById("pelangganForm").style.display = tipe === "pelanggan" ? "block" : "none";
        }
    </script>
</head>
<body>

<h2>Form Registrasi</h2>

<form method="POST">
    <select name="tipe" id="tipe" onchange="toggleForm()" required>
        <option value="">Pilih Tipe</option>
        <option value="pelanggan">Pelanggan</option>
        <option value="penjual">Penjual</option>
    </select><br>

    <input type="text" name="id" placeholder="ID (unik)" required><br>
    <input type="text" name="nama" placeholder="Nama Lengkap" required><br>

    <div id="pelangganForm" style="display:none;">
        <input type="text" name="alamat" placeholder="Alamat"><br>
        <input type="text" name="telepon" placeholder="No Telepon"><br>
    </div>

    <button type="submit" name="register">Daftar</button>
</form>

<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

<a href="index.php">← Kembali ke Login</a>

</body>
</html>
