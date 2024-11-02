<?php
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Data Ketidakhadiran';
include('../layout/header.php');

$result = mysqli_query($connection, "SELECT * FROM ketidakhadiran ORDER BY id DESC");

// print_r(mysqli_fetch_array($result));
// die;

?>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">

  <table class="table table-bordered">
      <tr class="text-center">
        <th>No</th>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th>Deskripsi</th>
        <th>FIle</th>
        <th>Status Pengajuan</th>
      </tr>

      <?php if(mysqli_num_rows($result) === 0){ ?>
        <tr>
          <td colspan="6">Data ketidakhadiran masih kosong!</td>
        </tr>
      <?php }
      else{
        $no = 1;
        while($data = mysqli_fetch_array($result)): ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= date('d F Y', strtotime($data['tanggal'])) ?></td>
          <td><?= $data['keterangan']; ?></td>
          <td><?= $data['deskripsi']; ?></td>
          <td class="text-center">
            <a target="_blank" href="<?= base_url('assets/file_ketidakhadiran/'.$data['file']) ?>" class="badge badge-pill bg-primary" download>Download</a>
          </td>
          <td class="text-center">
            <?php if($data['status_pengajuan'] == 'Pending'){ ?>
              <a href="<?= base_url('admin/data_ketidakhadiran/detail.php?id='. $data['id']) ?>" class="badge badge-pill bg-warning">Pending</a>
              <?php }
            else if($data['status_pengajuan'] == 'Rejected'){ ?>
              <a href="<?= base_url('admin/data_ketidakhadiran/detail.php?id='. $data['id']) ?>" class="badge badge-pill bg-danger">Rejected</a>
              <?php }
            else{ ?>
              <a href="<?= base_url('admin/data_ketidakhadiran/detail.php?id='. $data['id']) ?>" class="badge badge-pill bg-success">Approve</a>
            <?php } ?>
          </td>
        </tr>
        
        <?php endwhile;?>
      <?php } ?>

    </table>

  

  </div>
</div>





<?php include('../layout/footer.php'); ?>