<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';
require 'vendor/PHPMailer/src/Exception.php';
require '../connect.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_email"])) {
    $idTransaksi = $_GET['id'] ?? null;
    $username = $_SESSION["username"];

    // Fetch transaction
    $query = "SELECT * FROM transaksi
              JOIN customer ON transaksi.username = customer.username
              WHERE transaksi.idTransaksi = '$idTransaksi' AND transaksi.username = '$username'";
    $result = mysqli_query($connect, $query);
    $detailTransaksi = mysqli_fetch_assoc($result);

    if (!$detailTransaksi) {
        die("Data transaksi tidak ditemukan.");
    }

    $tanggalTransaksi = strtotime($detailTransaksi["tanggalTransaksi"]);
    $tanggalFormatted = date("j F Y", $tanggalTransaksi);

    $mail = new PHPMailer(true);

    try {
        // Server email config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'satriogemintang@gmail.com';
        $mail->Password   = 'lwsy ahdu mqvu haqb'; // Use App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Email recipients
        $mail->setFrom('apotekbarengwarga@gmail.com', 'Apotek Bareng Warga');
        $mail->addAddress($detailTransaksi['email'], $detailTransaksi['namaLengkap']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Terima kasih atas transaksi Anda!';
        $mail->Body    = "
            <p>Halo {$detailTransaksi['namaLengkap']},</p>
            <p>Terima kasih telah berbelanja di Apotek Bareng Warga.</p>
            <p>Untuk melihat dan mencetak struk transaksi Anda, silakan klik tombol di bawah ini:</p>
            <a href='https://localhost/APTKBARENGWRGA/Customer/detailTransaksi.php?id={$idTransaksi}' style='padding: 10px; background-color: #28a745; color: white; text-decoration: none;'>Lihat Struk</a>
            <p>Salam sehat,<br>Tim Apotek</p>
        ";
        $mail->AltBody = "Halo {$detailTransaksi['namaLengkap']},\nTerima kasih telah berbelanja.\nDetail transaksi ID: {$idTransaksi}.";

        $mail->send();
        
        header("Location: ../Customer/detailTransaksi.php?id=$idTransaksi");
        exit;

        echo "<script>alert('Email berhasil dikirim!'); window.location.href='detailTransaksi.php?id={$idTransaksi}';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal mengirim email: {$mail->ErrorInfo}');</script>";
    }
} else {
    echo "<script>alert('Permintaan tidak valid'); window.history.back();</script>";
}
