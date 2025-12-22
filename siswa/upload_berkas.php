<?php include '../config/database.php'; ?>

<form method="post" enctype="multipart/form-data">
    <label>Ijazah</label>
    <input type="file" name="ijazah"><br>

    <label>Akta</label>
    <input type="file" name="akta"><br>

    <label>Kartu Keluarga</label>
    <input type="file" name="kk"><br>

    <button name="upload">Upload</button>
</form>

<?php
if (isset($_POST['upload'])) {
    $ijazah = $_FILES['ijazah']['name'];
    $akta   = $_FILES['akta']['name'];
    $kk     = $_FILES['kk']['name'];

    move_uploaded_file($_FILES['ijazah']['tmp_name'], "../assets/berkas/$ijazah");
    move_uploaded_file($_FILES['akta']['tmp_name'], "../assets/berkas/$akta");
    move_uploaded_file($_FILES['kk']['tmp_name'], "../assets/berkas/$kk");

    mysqli_query($koneksi, "INSERT INTO berkas VALUES(NULL,1,'$ijazah','$akta','$kk')");

    echo "Upload berhasil";
}
?>