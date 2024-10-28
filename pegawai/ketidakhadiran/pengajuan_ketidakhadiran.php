<?php
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Pegawai'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Pengajuan Ketidakhadiran';
include('../layout/header.php');  

if(isset($_POST['submit'])){
  $id = $_POST['id_pegawai'];
  $keterangan = $_POST['keterangan'];
  $tanggal = $_POST['tanggal'];
  $deskripsi = $_POST['deskripsi'];
  $status_pengajuan = 'Pending';

  if(isset($_FILES['file'])){
    $file = $_FILES['file'];
    $nama_file = $file['name'];
    $file_tmp = $file['tmp_name'];
    $ukuran_file = $file['size'];
    $file_dir = '../../assets/file_ketidakhadiran/'.$nama_file;

    $ambil_ext = pathinfo($nama_file, PATHINFO_EXTENSION);
    $ext_diizinkan = ['jpg', 'png', 'jpeg', 'pdf'];
    $max_size = 10 * 1024 * 1024;

    move_uploaded_file($file_tmp, $file_dir);
  }

  if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(empty($keterangan)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Keterangan wajib diisi!";
    }
    if(empty($tanggal)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Tanggal wajib diisi!";
    }
    if(empty($deskripsi)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Deskripsi wajib diisi!";
    }

    if(!in_array(strtolower($ambil_ext), $ext_diizinkan)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Hanya file jpg, jpeg, dan png yang diizinkan!";
    }

    if($ukuran_file > $max_size){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Ukuran file melebihi 10 MB!";
    }

    if(!empty($pesan_kesalahan)){
      $_SESSION['validasi'] = implode('<br>',$pesan_kesalahan);
    }
    else{ 
      $result = mysqli_query($connection, "INSERT INTO ketidakhadiran(id_pegawai, keterangan, deskripsi, tanggal, status_pengajuan, file) VALUES('$id', '$keterangan', '$deskripsi', '$tanggal', '$status_pengajuan', '$nama_file')");

      $_SESSION['berhasil'] = 'Data berhasil disimpan';
      header('Location: ketidakhadiran.php');
      exit;
    }
  }

}

$id = $_SESSION['id'];
$result = mysqli_query($connection, "SELECT * FROM ketidakhadiran WHERE id_pegawai = '$id' ORDER BY id DESC");

?>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">
    <div class="card col-md-6">
      <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
          <input type="hidden" value="<?= $_SESSION['id'] ?>" name="id_pegawai">
          <div class="mb-3">
            <label for="">Keterangan</label>
            <select name="keterangan" id="" class="form-control">
              <option value="">-- Pilih Keterangan --</option>
              <option <?= (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Cuti') ? 'selected' : '' ?> value="Cuti">Cuti</option>
              <option <?= (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Izin') ? 'selected' : '' ?> value="Izin">Izin</option>
              <option <?= (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Sakit') ? 'selected' : '' ?> value="Sakit">Sakit</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" cols="30" rows="5"></textarea>
          </div>
          <div class="mb-3">
            <label for="">Tanggal</label>
            <input type="date" name="tanggal" id="" class="form-control">
          </div>
          <div class="mb-3">
            <label for="">Surat Keterangan</label>
            <input type="file" name="file" id="" class="form-control">
          </div>

          <button type="submit" class="btn btn-primary" name="submit">Ajukan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include('../layout/footer.php'); ?>