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

// Remove from cart
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: keranjang.php");
    exit;
}

// Update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        if (isset($_SESSION['cart'][$id])) {
            // Check if has enough stock
            $check_stock = mysqli_query($conn, "SELECT stok FROM barang WHERE id_barang = '$id'");
            $stock = mysqli_fetch_assoc($check_stock);
            
            if ($qty > 0 && $qty <= $stock['stok']) {
                $_SESSION['cart'][$id]['quantity'] = $qty;
            }
        }
    }
    $success_message = "Keranjang berhasil diperbarui!";
}

// Process order
if (isset($_POST['confirm_order']) && !empty($_SESSION['cart'])) {
    $id_pelanggan = $_SESSION['id_pelanggan'];
    $tanggal = date('Y-m-d H:i:s');
    $total_amount = 0;
    
    // Calculate total
    foreach ($_SESSION['cart'] as $id => $item) {
        $total_amount += $item['harga'] * $item['quantity'];
    }
    
    // Insert order
    $insert_order = mysqli_query($conn, "INSERT INTO pesanan (id_pelanggan, tanggal, total) VALUES ('$id_pelanggan', '$tanggal', '$total_amount')");
    
    if ($insert_order) {
        $id_pesanan = mysqli_insert_id($conn);
        $all_items_available = true;
        
        // Insert order items and update stock
        foreach ($_SESSION['cart'] as $id => $item) {
            // Check if stock is still available
            $check_stock = mysqli_query($conn, "SELECT stok FROM barang WHERE id_barang = '$id'");
            $stock = mysqli_fetch_assoc($check_stock);
            
            if ($stock['stok'] >= $item['quantity']) {
                // Insert order item
                mysqli_query($conn, "INSERT INTO detail_pesanan (id_pesanan, id_barang, jumlah, harga) VALUES ('$id_pesanan', '$id', '{$item['quantity']}', '{$item['harga']}')");
                
                // Update stock
                mysqli_query($conn, "UPDATE barang SET stok = stok - {$item['quantity']} WHERE id_barang = '$id'");
            } else {
                $all_items_available = false;
                break;
            }
        }
        
        if ($all_items_available) {
            // Clear cart
            $_SESSION['cart'] = array();
            $_SESSION['order_success'] = true;
            $_SESSION['id_pesanan'] = $id_pesanan;
            $_SESSION['total_amount'] = $total_amount;
            
            // Redirect to order confirmation
            header("Location: konfirmasi_pesanan.php");
            exit;
        } else {
            // If some items are not available, rollback
            mysqli_query($conn, "DELETE FROM detail_pesanan WHERE id_pesanan = '$id_pesanan'");
            mysqli_query($conn, "DELETE FROM pesanan WHERE id_pesanan = '$id_pesanan'");
            $error_message = "Maaf, beberapa barang tidak tersedia. Silakan periksa kembali keranjang Anda.";
        }
    } else {
        $error_message = "Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.";
    }
}

// Count cart items
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
    <title>Keranjang Belanja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pembeli.css">
    <style>
        .back-button {
            display: inline-flex;
            align-items: center;
            color: #4339F2;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .back-button svg {
            margin-right: 5px;
            width: 16px;
            height: 16px;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-total {
            font-size: 18px;
            font-weight: 600;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eaedf3;
        }
        
        .order-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .order-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .order-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }
        
        .empty-cart-container {
            text-align: center;
            padding: 40px 0;
        }
        
        .empty-cart-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-cart-message {
            font-size: 18px;
            color: #777;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Keranjang Belanja</h1>
</div>

<div class="container">
    <a href="pembeli.php" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" fill="currentColor"/>
        </svg>
        Kembali ke Daftar Produk
    </a>
    
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
        <h2>Keranjang Belanja</h2>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart-container">
                <svg class="empty-cart-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M16.53 11l3.97-7h-16.5l3.97 7h8.56zm.47-9c.832 0 1.59.443 1.976 1.152l4.024 7.348-4.284 7.5h-11.716l-4-7.5 3.98-7.347c.403-.722 1.166-1.153 1.99-1.153h8.03zm-6.5 14h3.5v2h-3.5v-2zm5.5 0h3.5v2h-3.5v-2zm-11 0h3.5v2h-3.5v-2z" fill="#aaa"/>
                </svg>
                <p class="empty-cart-message">Keranjang belanja Anda masih kosong.</p>
                <a href="pembeli.php" class="btn btn-primary">Mulai Berbelanja</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        foreach ($_SESSION['cart'] as $id => $item): 
                            $subtotal = $item['harga'] * $item['quantity'];
                            $total += $subtotal;
                            
                            // Get current stock
                            $check_stock = mysqli_query($conn, "SELECT stok FROM barang WHERE id_barang = '$id'");
                            $stock = mysqli_fetch_assoc($check_stock);
                        ?>
                        <tr>
                            <td><?php echo $item['nama']; ?></td>
                            <td class="price">Rp <?php echo number_format($item['harga'], 2, ',', '.'); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $stock['stok']; ?>" class="quantity-input">
                            </td>
                            <td class="price">Rp <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            <td>
                                <a href="?remove=<?php echo $id; ?>" class="btn btn-danger btn-small">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="order-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="summary-row">
                        <span>Subtotal (<?php echo $cart_count; ?> barang)</span>
                        <span class="price">Rp <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span class="price total-price">Rp <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="order-buttons">
                    <button type="submit" name="update_cart" class="btn btn-outline">Perbarui Keranjang</button>
                    <button type="submit" name="confirm_order" class="btn btn-success">Konfirmasi Pesanan</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>