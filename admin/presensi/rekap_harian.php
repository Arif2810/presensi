<?php 
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Rekap Presensi Harian';

include('../layout/header.php'); 

if(empty($_GET['tanggal_dari'])){
  $tanggal_hari_ini = date('Y-m-d');
  $result = mysqli_query($connection, "SELECT presensi.*, pegawai.nama, pegawai.lokasi_presensi FROM presensi JOIN pegawai ON pegawai.id = presensi.id_pegawai WHERE tanggal_masuk = '$tanggal_hari_ini' ORDER BY tanggal_masuk DESC");
  $tanggal = date('d F Y', strtotime($tanggal_hari_ini));
}
else{
  $tanggal_dari = $_GET['tanggal_dari'];
  $tanggal_sampai = $_GET['tanggal_sampai'];
  $result = mysqli_query($connection, "SELECT presensi.*, pegawai.nama, pegawai.lokasi_presensi FROM presensi JOIN pegawai ON pegawai.id = presensi.id_pegawai WHERE tanggal_masuk BETWEEN '$tanggal_dari' AND '$tanggal_sampai' ORDER BY tanggal_masuk DESC");
  $tanggal = date('d F Y', strtotime($tanggal_dari)).' sampai '.date('d F Y', strtotime($tanggal_sampai));
}


// echo $jam_masuk_kantor;
// die;


// $rekap = mysqli_fetch_array($result);
// print_r($rekap);
// die;

?>

<div class="page-body">
  <div class="container-xl">

    <div class="row">
      <div class="col-md-2">
        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#exampleModal">
          Export Excel
        </button>
      </div>

      <div class="col-md-8">
        <form action="" method="GET">
          <div class="input-group">
            <input type="date" class="form-control" name="tanggal_dari">
            <input type="date" class="form-control mx-2" name="tanggal_sampai">
            <button type="submit" class="btn btn-primary">Tampilkan</button>
            <button type="refres" class="btn btn-success mx-2">Refres</button>
          </div>
        </form>
      </div>
    </div>

    <span>Rekap presensi tanggal <?= $tanggal; ?></span>
    <table class="table table-bordered mt-2">
      <tr class="text-center">
        <th>No.</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Jam Masuk</th>
        <th>Jam Pulang</th>
        <th>Total Jam</th>
        <th>Total Terlambat</th>
      </tr>

      <?php if(mysqli_num_rows($result) === 0){ ?>
        <tr>
          <td colspan="6">Data rekap presensi masih kosong!</td>
        </tr>
      <?php } ?>

      <?php 
      $no = 1;
      foreach($result as $rekap):
        // Menghitung total jam kerja
        $jam_tanggal_masuk = date('Y-m-d H:i:s', strtotime($rekap['tanggal_masuk'].' '.$rekap['jam_masuk']));
        $jam_tanggal_keluar = date('Y-m-d H:i:s', strtotime($rekap['tanggal_masuk'].' '.$rekap['jam_keluar']));

        $timestamp_masuk = strtotime($jam_tanggal_masuk);
        $timestamp_keluar = strtotime($jam_tanggal_keluar);

        $selisih = $timestamp_keluar - $timestamp_masuk;
        $total_jam = floor($selisih / 3600);

        $selisih -= $total_jam * 3600;
        $selisih_menit = floor($selisih / 60);

        // Menghitung total jam terlambat 
        $lokasi_presensi = $rekap['lokasi_presensi'];
        $lokasi = mysqli_query($connection, "SELECT * FROM lokasi_presensi WHERE nama_lokasi = '$lokasi_presensi'");

        while($lokasi_result = mysqli_fetch_array($lokasi)){
          $jam_masuk_kantor = date('H:i:s', strtotime($lokasi_result['jam_masuk']));
        }

        $jam_masuk = date('H:i:s', strtotime($rekap['jam_masuk']));
        $timestamp_jam_masuk_pegawai = strtotime($jam_masuk);
        $timestamp_jam_masuk_kantor = strtotime($jam_masuk_kantor);

        $terlambat = $timestamp_jam_masuk_pegawai - $timestamp_jam_masuk_kantor;
        $total_jam_terlambat = floor($terlambat / 3600);
        $terlambat -= $total_jam_terlambat * 3600;
        $selisih_menit_terlambat = floor($terlambat / 60);
      ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= $rekap['nama']; ?></td>
          <td><?= date('d F Y', strtotime($rekap['tanggal_masuk'])); ?></td>
          <td class="text-center"><?= $rekap['jam_masuk']; ?></td>
          <td class="text-center"><?= $rekap['jam_keluar']; ?></td>
          <td class="text-center">
            <?php if($rekap['tanggal_keluar'] == '0000-00-00'){ ?>
              <span>0 jam 0 menit</span>
              <?php }
            else{ ?>
              <?= $total_jam.' jam '. $selisih_menit.' menit'; ?>
            <?php } ?>
          </td>
          <td class="text-center">
            <?php if($total_jam_terlambat < 0){ ?>
              <span class="badge bg-success">On Time</span>
            <?php }
            else{ ?>
              <?= $total_jam_terlambat.' jam '. $selisih_menit_terlambat.' menit'; ?>
            <?php } ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

  </div>
</div>


<!-- Modal -->
<div class="modal" id="exampleModal" tabindex="-1">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Export Excel Rekap Presensi Harian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?= base_url('admin/presensi/rekap_harian_excel.php') ?>" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label for="">Tanggal Awal</label>
            <input type="date" class="form-control" name="tanggal_dari">
          </div>
          <div class="mb-3">
            <label for="">Tanggal Akhir</label>
            <input type="date" class="form-control" name="tanggal_sampai">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Export</button>
        </div>
      </form>
    </div>
  </div>
</div>



<?php include('../layout/footer.php'); ?>