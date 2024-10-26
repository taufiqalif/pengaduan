<?php
session_start();
require_once("database.php");
// header("Location: ../index?status=success");
// Atasi Undefined
$nama = $email = $telpon = $alamat = $pengaduan = $captcha = $is_valid = "";
$namaError = $emailError = $telponError = $alamatError = $pengaduanError = $captchaError = "";

if (isset($_POST['submit'])) {
    $nomor     = $_POST['nomor'];
    $nama      = $_POST['nama'];
    $email     = $_POST['email'];
    $telpon    = $_POST['telpon'];
    $alamat    = $_POST['alamat'];
    $tujuan    = $_POST['tujuan'];
    $pengaduan = $_POST['pengaduan'];
    $captcha   = $_POST['captcha'];
    $is_valid  = true;

    validate_input();

    // Proses unggah file foto jika ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $foto_tmp  = $_FILES['foto']['tmp_name'];
        $foto_name = basename($_FILES['foto']['name']);
        $foto_path = '../foto/' . $foto_name;

        if (move_uploaded_file($foto_tmp, $foto_path)) {
            if ($is_valid) {
                $sql = "INSERT INTO `laporan` (`id`, `nama`, `email`, `telpon`, `alamat`, `tujuan`, `foto`, `isi`, `tanggal`, `status`) 
                        VALUES (:nomor, :nama, :email, :telpon, :alamat, :tujuan, :foto, :isi, CURRENT_TIMESTAMP, :status)";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':nomor', $nomor);
                $stmt->bindValue(':nama', $nama);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':telpon', $telpon);
                $stmt->bindValue(':alamat', htmlspecialchars($alamat));
                $stmt->bindValue(':tujuan', $tujuan);
                $stmt->bindValue(':foto', $foto_name);
                $stmt->bindValue(':isi', htmlspecialchars($pengaduan));
                $stmt->bindValue(':status', "Menunggu");

                if ($stmt->execute()) {
                    header("Location: ../index?status=success");
                    exit();
                } else {
                    echo "Gagal menyimpan data ke database.";
                }
            }
        } else {
            echo "Gagal mengunggah file foto.";
        }
    } else {
        echo "File foto tidak ada atau gagal diunggah.";
    }
}

// Fungsi Untuk Melakukan Pengecekan Dari Setiap Inputan Di Masing-masing Fungsi
function validate_input()
{
    global $nama, $email, $telpon, $alamat, $pengaduan, $captcha, $is_valid;
    cek_nama($nama);
    cek_email($email);
    cek_telpon($telpon);
    cek_alamat($alamat);
    cek_pengaduan($pengaduan);
    cek_captcha($captcha);

    if (isset($_FILES['foto']['name'])) {
        cek_foto($_FILES['foto']['name']);
    }
}

// Fungsi validasi foto, dengan parameter $foto
function cek_foto($foto): void
{
    global $is_valid, $fotoError;
    echo "cek_foto      : ", $foto, "<br>";
    if (!preg_match("/\.(jpg|jpeg|png|gif|bmp)$/i", $foto)) {
        $fotoError = "Foto hanya boleh jpg, jpeg, png, gif, atau bmp.";
        $is_valid = false;
    }
}

// validasi nama
function cek_nama($nama)
{
    global $nama, $is_valid, $namaError;
    echo "cek_nama      : ", $nama, "<br>";
    if (!preg_match("/^[a-zA-Z ]*$/", $nama)) { // cek nama bukan huruf
        $namaError = "Nama Hanya Boleh Huruf dan Spasi";
        $is_valid = false;
    } else { // jika nama valid kosongkan error
        $namaError = "";
    }
}

// validasi email
function cek_email($email)
{
    global $email, $is_valid, $emailError;
    echo "cek_email     : ", $email, "<br>";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // cek format email
        $emailError = "Email Tidak Valid";
        $is_valid = false;
    } else { // jika email valid kosongkan eror
        $emailError = "";
    }
}

// validasi telpon
function cek_telpon($telpon)
{
    global $telpon, $telponError, $is_valid;
    echo "cek_telpon    : ", $telpon, "<br>";
    if (!preg_match("/^[0-9]*$/", $telpon)) { // cek telpon hanya boleh angka
        $telponError = "Telpon Hanya Boleh Angka";
        $is_valid = false;
    } elseif (strlen($telpon) != 12) { // cek panjang telpon harus >= 6
        $telponError = "Panjang Telpon Harus 12 Digit";
        $is_valid = false;
    } else { // jika telpon valid kosongkan error
        $telponError = "";
    }
}

// validasi alamat
function cek_alamat($alamat)
{
    global $alamat, $is_valid, $alamatError;
    echo "cek_alamat    : ", $alamat, "<br>";
    if (!preg_match("/^[a-zA-Z0-9 ]*$/", $alamat)) { // cek fullname bukan huruf
        $alamatError = "Alamat Hanya Boleh Huruf dan Angka";
        $is_valid = false;
    } else { // jika fullname valid kosongkan error
        $alamatError = "";
    }
}

// validasi pengaduan
function cek_pengaduan($pengaduan)
{
    global $pengaduan, $is_valid, $pengaduanError;
    echo "cek_pengaduan : ", $pengaduan, "<br>";
    if (strlen($pengaduan) > 2048) { // cek fullname bukan huruf
        $pengaduanError = "Isi Pengaduan Tidak Boleh Huruf Lebih Dari 2048 Karakter";
        $is_valid = false;
    } else { // jika pengaduan valid kosongkan error
        $pengaduanError = "";
    }
}

// validasi captcha
function cek_captcha($captcha)
{
    global $captcha, $is_valid, $captchaError;
    echo "cek_captcha   : ", $captcha, "<br>";
    if ($captcha != $_SESSION['bilangan']) { // cek fullname bukan huruf
        $captchaError = "Captcha Salah atau Silahkan Reload Browser Anda";
        $is_valid = false;
    } else { // jika pengaduan valid kosongkan error
        $captchaError = "";
    }
}
