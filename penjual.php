<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['id_penjual'])) {
    header("Location: index.php");
    exit;
}

// Tambah barang
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_barang'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    mysqli_query($conn, "INSERT INTO barang (nama_barang, harga, stok) VALUES ('$nama', '$harga', '$stok')");
}

// Update barang
if (isset($_POST['update'])) {
    $id_barang = $_POST['id_barang'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    mysqli_query($conn, "UPDATE barang SET harga = '$harga', stok = '$stok' WHERE id_barang = '$id_barang'");
}

// Hapus barang - MODIFIED CODE HERE
if (isset($_POST['hapus'])) {
    $id_barang = $_POST['id_barang'];
    
    // Check if product is used in any order details
    $check_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM detail_pesanan WHERE id_barang = '$id_barang'");
    $check_result = mysqli_fetch_assoc($check_query);
    
    if ($check_result['count'] > 0) {
        // Product is used in orders, show error message
        echo "<script>alert('Tidak dapat menghapus barang karena masih digunakan dalam pesanan!');
        window.location='penjual.php';</script>";
    } else {
        // Safe to delete
        mysqli_query($conn, "DELETE FROM barang WHERE id_barang = '$id_barang'");
        echo "<script>alert('Barang berhasil dihapus!');
        window.location='penjual.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistem Manajemen Inventaris</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="penjual.css">
</head>
<body>

<div class="header">
    <h1>Sistem Manajemen Inventaris</h1>
</div>

<div class="container">
    <div class="card">
        <h2>Kelola Stok dan Harga Barang</h2>
        
        <form method="POST">
            <div class="form-group">
                <div class="form-control">
                    <label>Nama Barang</label>
                    <input type="text" name="nama_barang" placeholder="Masukkan nama barang" required>
                </div>
                <div class="form-control">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" placeholder="Masukkan harga" step="0.01" required>
                </div>
                <div class="form-control">
                    <label>Stok</label>
                    <input type="number" name="stok" placeholder="Masukkan jumlah stok" required>
                </div>
                <button type="submit" name="tambah" class="btn btn-primary">Tambah Barang</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Barang</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Harga (Rp)</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $data = mysqli_query($conn, "SELECT * FROM barang");
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) {
                        echo "<tr>
                            <form method='POST'>
                                <td>{$row['nama_barang']}</td>
                                <td>
                                    <input type='number' name='harga' value='{$row['harga']}' step='0.01'>
                                </td>
                                <td>
                                    <input type='number' name='stok' value='{$row['stok']}'>
                                </td>
                                <td class='action-btns'>
                                    <input type='hidden' name='id_barang' value='{$row['id_barang']}'>
                                    <button type='submit' name='update' class='btn btn-success'>Simpan</button>
                                    <button type='submit' name='hapus' class='btn btn-danger' onclick=\"return confirm('Yakin ingin menghapus barang ini?');\">Hapus</button>
                                </td>
                            </form>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>Tidak ada data barang</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="logout-container">
        <a href="logout.php" class="btn btn-outline">Logout</a>
    </div>
</div>

</body>
</html>