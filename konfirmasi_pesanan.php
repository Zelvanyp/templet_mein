<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: index.php");
    exit;
}

// Check if there's an order success
if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header("Location: pembeli.php");
    exit;
}

$id_pesanan = $_SESSION['id_pesanan'];
$total_amount = $_SESSION['total_amount'];

// Get order details
$query_order = mysqli_query($conn, "SELECT p.*, pl.nama_pelanggan as nama_pelanggan 
                                     FROM pesanan p 
                                     JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
                                     WHERE p.id_pesanan = '$id_pesanan'");
$order = mysqli_fetch_assoc($query_order);

// Get order items
$query_items = mysqli_query($conn, "SELECT dp.*, b.nama_barang 
                                     FROM detail_pesanan dp 
                                     JOIN barang b ON dp.id_barang = b.id_barang 
                                     WHERE dp.id_pesanan = '$id_pesanan'");

// Clear order success session
unset($_SESSION['order_success']);
unset($_SESSION['id_pesanan']);
unset($_SESSION['total_amount']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pesanan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pembeli.css">
    <style>
        .order-confirmation {
            text-align: center;
            padding: 20px 0;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            color: #10B981;
        }
        
        .order-number {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }
        
        .order-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaedf3;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #555;
        }
        
        .back-to-shop {
            margin-top: 40px;
        }
        
        .print-button {
            margin-right: 10px;
            background-color: #6B7280;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        @media print {
            .header, .action-buttons, .back-to-shop {
                display: none;
            }
            
            body {
                background-color: white;
            }
            
            .container {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            
            .card {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Konfirmasi Pesanan</h1>
</div>

<div class="container">
    <div class="card">
        <div class="order-confirmation">
            <svg class="success-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6.25 8.891l-1.421-1.409-6.105 6.218-3.078-2.937-1.396 1.436 4.5 4.319 7.5-7.627z" fill="currentColor"/>
            </svg>
            
            <h2>Pesanan Berhasil Dibuat!</h2>
            <p class="order-number">Nomor Pesanan: <strong>#<?php echo str_pad($id_pesanan, 5, '0', STR_PAD_LEFT); ?></strong></p>
            
            <div class="order-details">
                <h3>Detail Pesanan</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Tanggal Pesanan</span>
                    <span><?php echo date('d/m/Y H:i', strtotime($order['tanggal'])); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Nama Pelanggan</span>
                    <span><?php echo $order['nama_pelanggan']; ?></span>
                </div>
                
                <h4 style="margin-top: 20px;">Item yang Dibeli</h4>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($query_items)): 
                            $subtotal = $item['harga'] * $item['jumlah'];
                        ?>
                        <tr>
                            <td><?php echo $item['nama_barang']; ?></td>
                            <td class="price">Rp <?php echo number_format($item['harga'], 2, ',', '.'); ?></td>
                            <td><?php echo $item['jumlah']; ?></td>
                            <td class="price">Rp <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td class="price total-price">Rp <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="action-buttons">
                <button onclick="window.print()" class="btn print-button">
                    Cetak Pesanan
                </button>
                <a href="pembeli.php" class="btn btn-primary">Kembali ke Toko</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>