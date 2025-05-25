<?php 
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Rekap Presensi Bulanan';

include('../layout/header.php'); 

if(empty($_GET['filter_bulan'])){
  $bulan_sekarang = date('Y-m');
  $result = mysqli_query($connection, "SELECT presensi.*, pegawai.nama, pegawai.lokasi_presensi FROM presensi JOIN pegawai ON pegawai.id = presensi.id_pegawai WHERE DATE_FORMAT(tanggal_masuk, '%Y-%m') = '$bulan_sekarang' ORDER BY tanggal_masuk DESC");
  $bulan = date('Y-m');
}
else{
  $tahun_bulan = $_GET['filter_tahun'] .'-'. $_GET['filter_bulan'];
  $result = mysqli_query($connection, "SELECT presensi.*, pegawai.nama, pegawai.lokasi_presensi FROM presensi JOIN pegawai ON pegawai.id = presensi.id_pegawai WHERE DATE_FORMAT(tanggal_masuk, '%Y-%m') = '$tahun_bulan' ORDER BY tanggal_masuk DESC");
  $bulan = $_GET['filter_tahun'] .'-'. $_GET['filter_bulan'];
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
            <select name="filter_bulan" class="form-control">
              <option value="">-- Pilih Bulan --</option>
              <option value="01">Januari</option>
              <option value="02">Februari</option>
              <option value="03">Maret</option>
              <option value="04">April</option>
              <option value="05">Mei</option>
              <option value="06">Juni</option>
              <option value="07">Juli</option>
              <option value="08">Agustus</option>
              <option value="09">September</option>
              <option value="10">Oktober</option>
              <option value="11">November</option>
              <option value="12">Desember</option>
            </select>

            <!-- <select name="filter_tahun" class="form-control mx-2">
              <option value="">-- Pilih Tahun --</option>
              <option value="2020">2020</option>
              <option value="2021">2021</option>
              <option value="2022">2022</option>
              <option value="2023">2023</option>
              <option value="2024">2024</option>
            </select> -->

            <?php
            $tahunSekarang = date('Y');
            ?>

            <select name="filter_tahun" class="form-control mx-2">
              <option value="">-- Pilih Tahun --</option>
              <?php for ($i = 5; $i >= 0; $i--): 
                $tahun = $tahunSekarang - $i;
              ?>
                <option value="<?= $tahun ?>"><?= $tahun ?></option>
              <?php endfor; ?>
            </select>

            <button type="submit" class="btn btn-primary">Tampilkan</button>
            <button type="refres" class="btn btn-success mx-2">Refres</button>
          </div>
        </form>
      </div>
    </div>


    <span>Rekap Presensi Bulan <?= date('F Y', strtotime($bulan)); ?></span>
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
        <h5 class="modal-title">Export Excel Rekap Presensi Bulanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?= base_url('admin/presensi/rekap_bulanan_excel.php') ?>" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label for="">Bulan</label>
            <select name="filter_bulan" class="form-control">
              <option value="">-- Pilih Bulan --</option>
              <option value="01">Januari</option>
              <option value="02">Februari</option>
              <option value="03">Maret</option>
              <option value="04">April</option>
              <option value="05">Mei</option>
              <option value="06">Juni</option>
              <option value="07">Juli</option>
              <option value="08">Agustus</option>
              <option value="09">September</option>
              <option value="10">Oktober</option>
              <option value="11">November</option>
              <option value="12">Desember</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="">Tahun</label>
            <select name="filter_tahun" class="form-control">
              <option value="">-- Pilih Tahun --</option>
              <option value="2020">2020</option>
              <option value="2021">2021</option>
              <option value="2022">2022</option>
              <option value="2023">2023</option>
              <option value="2024">2024</option>
            </select>
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