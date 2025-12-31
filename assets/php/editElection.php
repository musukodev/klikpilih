<?php
session_start();
if (!isset($_SESSION["login"])) {
    header("Location: /admin");
    exit;
}
require $_SERVER['DOCUMENT_ROOT'] . '/backend/koneksi.php';


// Ambil data mentah (Format YYYY-MM-DD)
$nama_sesi = $_POST['nama'];
$tgl_mulai = $_POST['tgl_mulai'];
$jam_mulai = $_POST['jam_mulai'];
$tgl_selesai = $_POST['tgl_selesai'];
$jam_selesai = $_POST['jam_selesai'];
$id = $_POST['id_select_sesi_pemilihan'] - 1;
// 2. PENGGABUNGAN (CONCATENATE)
// Format DATETIME MySQL adalah: "YYYY-MM-DD HH:MM:SS"
// Kita gabungkan tanggal [spasi] jam
$start_datetime = $tgl_mulai . ' ' . $jam_mulai . ':00'; // Tambah :00 untuk detik
$end_datetime   = $tgl_selesai . ' ' . $jam_selesai . ':00';

$foto_sampul = $_FILES['foto_sampul']['name'];
$foto_sampul_temp = $_FILES['foto_sampul']['tmp_name'];

$rt_panitia = $_POST['rt_panitia'];


if ($_SESSION["rt_panitia"] = "RW"){
    $sql_cari = "SELECT id_sesi FROM sesi_pemilihan ORDER BY id_sesi ASC LIMIT 1 OFFSET ?";
    $stmt_cari = $conn->prepare($sql_cari);
    $stmt_cari->bind_param("i", $id);
} else {
    $sql_cari = "SELECT id_sesi FROM sesi_pemilihan where rt_panitia = ? ORDER BY id_sesi ASC LIMIT 1 OFFSET ?";
    $stmt_cari = $conn->prepare($sql_cari);
    $stmt_cari->bind_param("si", $rt_panitia, $id);
}
mysqli_stmt_execute($stmt_cari);
$result = $stmt_cari->get_result();
echo $result->num_rows;
if ($result->num_rows > 0) {
    $row = mysqli_fetch_assoc($result);
    $id_sesi = $row['id_sesi'];
    if (isset($foto_sampul) && $_FILES['foto_sampul']['error'] != UPLOAD_ERR_NO_FILE) {
        $ekstensiFile  = strtolower(pathinfo($foto_sampul, PATHINFO_EXTENSION));
        $namaBaru = "sessionpic_" . time() . "." . $ekstensiFile;
        $folder = "../../assets/img/";
        $target = $folder . $namaBaru;
        $stmt = $conn->prepare("UPDATE sesi_pemilihan SET nama_sesi = ?, waktu_mulai = ?, waktu_selesai = ?, foto_sampul = ? WHERE id_sesi = ?");
        $stmt->bind_param("ssssi", $nama_sesi, $start_datetime, $end_datetime, $target, $id_sesi);
        move_uploaded_file($foto_sampul_temp, $target);
    } else {
        $stmt = $conn->prepare("UPDATE sesi_pemilihan SET nama_sesi = ?, waktu_mulai = ?, waktu_selesai = ? WHERE id_sesi = ?");
        $stmt->bind_param("sssi", $nama_sesi, $start_datetime, $end_datetime, $id_sesi);
    }
    mysqli_stmt_execute($stmt);
}

?>
<script>
    window.location.href = "/admin/dashboard";
</script>