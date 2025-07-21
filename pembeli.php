<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: index.php");
    exit;
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add to cart functionality
if (isset($_POST['add_to_cart'])) {
    $id_barang = $_POST['id_barang'];
    $quantity = $_POST['quantity'];
    
    // Check if item exists and has enough stock
    $check_item = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang = '$id_barang' AND stok >= '$quantity'");
    if (mysqli_num_rows($check_item) > 0) {
        $item = mysqli_fetch_assoc($check_item);
        
        // Check if already in cart, update quantity if it is
        if (isset($_SESSION['cart'][$id_barang])) {
            $_SESSION['cart'][$id_barang]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$id_barang] = array(
                'nama' => $item['nama_barang'],
                'harga' => $item['harga'],
                'quantity' => $quantity
            );
        }
        
        // Success message
        $success_message = "Produk berhasil ditambahkan ke keranjang!";
    } else {
        // Error message
        $error_message = "Stok tidak mencukupi!";
    }
}

// Count items in cart
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistem Manajemen Inventaris</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pembeli.css">
    <style>
        .cart-button {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            background-color: #4339F2;
            color: white;
            padding: 10px 15px;
            border-radius: 30px;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .cart-icon {
            margin-right: 8px;
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #EF4444;
            color: white;
            font-size: 12px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Cart Icon SVG */
        .cart-svg {
            width: 24px;
            height: 24px;
            fill: white;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Sistem Manajemen Inventaris</h1>
</div>

<!-- Cart Button -->
<a href="keranjang.php" class="cart-button">
    <div class="cart-icon">
        <svg class="cart-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M10 19.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5zm3.5-1.5c-.828 0-1.5.671-1.5 1.5s.672 1.5 1.5 1.5 1.5-.671 1.5-1.5c0-.828-.672-1.5-1.5-1.5zm1.336-5l1.977-7h-16.813l2.938 7h11.898zm4.969-10l-3.432 12h-12.597l.839 2h13.239l3.474-12h1.929l.743-2h-4.195z"/>
        </svg>
        <?php if ($cart_count > 0): ?>
        <span class="cart-count"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </div>
    <span>Keranjang</span>
</a>

<div class="container">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Daftar Barang Tersedia</h2>
        
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
                $barang = mysqli_query($conn, "SELECT * FROM barang");
                if (mysqli_num_rows($barang) > 0) {
                    while ($row = mysqli_fetch_assoc($barang)) {
                        echo "<tr>
                                <td>{$row['nama_barang']}</td>
                                <td class='price'>Rp " . number_format($row['harga'], 2, ',', '.') . "</td>
                                <td class='stock'>{$row['stok']}</td>
                                <td>
                                    <form method='POST' class='cart-form'>
                                        <input type='hidden' name='id_barang' value='{$row['id_barang']}'>
                                        <div class='quantity-controls'>
                                            <input type='number' name='quantity' value='1' min='1' max='{$row['stok']}' class='quantity-input'>
                                            <button type='submit' name='add_to_cart' class='btn btn-primary'>Beli</button>
                                        </div>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>Tidak ada barang tersedia</td></tr>";
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