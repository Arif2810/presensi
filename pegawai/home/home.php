<?php 
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Pegawai'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Home';

include('../layout/header.php'); 

$lokasi_presensi = $_SESSION['lokasi_presensi'];
$result = mysqli_query($connection, "SELECT * FROM  lokasi_presensi WHERE nama_lokasi = '$lokasi_presensi'");

while($lokasi = mysqli_fetch_array($result)){
  $latitude_kantor = $lokasi['latitude'];
  $longitude_kantor = $lokasi['longitude'];
  $radius = $lokasi['radius'];
  $zona_waktu = $lokasi['zona_waktu'];
  $jam_pulang = $lokasi['jam_pulang'];
}

if($zona_waktu == 'WIB'){
  date_default_timezone_set('Asia/Jakarta');
}
elseif($zona_waktu == 'WITA'){
  date_default_timezone_set('Asia/Makassar');
}
elseif($zona_waktu == 'WIT'){
  date_default_timezone_set('Asia/Jayapura');
}
?>

<style>
  .parent-date{
    display: grid;
    grid-template-columns: auto auto auto auto auto;
    font-size: 20px;
    text-align: center;
    justify-content: center;
  }

  .parent-clock{
    display: grid;
    grid-template-columns: auto auto auto auto auto;
    font-size: 30px;
    text-align: center;
    font-weight: bold;
    justify-content: center;
  }
</style>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">
    <div class="row">
      <div class="col-md-2"></div>
      <div class="col-md-4">
        <div class="card text-center h-100">
          <div class="card-header">Presensi Masuk</div>
          <div class="card-body">
            <?php 
            $id_pegawai = $_SESSION['id'];
            $tanggal_hari_ini = date('Y-m-d');

            $cek_presensi_masuk = mysqli_query($connection, "SELECT * FROM presensi WHERE id_pegawai = '$id_pegawai' AND tanggal_masuk = '$tanggal_hari_ini'");
            ?>

            <?php if(mysqli_num_rows($cek_presensi_masuk) == 0){ ?>
            <div class="parent-date">
              <div id="tanggal-masuk"></div>
              <div class="ms-2"></div>
              <div id="bulan-masuk"></div>
              <div class="ms-2"></div>
              <div id="tahun-masuk"></div>
            </div>
            <div class="parent-clock">
              <div id="jam-masuk"></div>
              <div>:</div>
              <div id="menit-masuk"></div>
              <div>:</div>
              <div id="detik-masuk"></div>
            </div>

            <form action="<?= base_url('pegawai/presensi/presensi_masuk.php') ?>" method="POST">
              <input type="hidden" name="latitude_pegawai" id="latitude-pegawai">
              <input type="hidden" name="longitude_pegawai" id="longitude-pegawai">
              <input type="hidden" value="<?= $latitude_kantor ?>" name="latitude_kantor">
              <input type="hidden" value="<?= $longitude_kantor ?>" name="longitude_kantor">
              <input type="hidden" value="<?= $radius ?>" name="radius">
              <input type="hidden" value="<?= $zona_waktu ?>" name="zona_waktu">
              <input type="hidden" value="<?= date('Y-m-d') ?>" name="tgl_masuk">
              <input type="hidden" value="<?= date('H:i:s') ?>" name="jam_masuk">
              <button type="submit" name="masuk" class="btn btn-primary mt-3">Masuk</button>
            </form>
            <?php }
            else{ ?>
              <i class="fa-regular fa-circle-check fa-4x text-success mb-2"></i>
              <h4>Anda sudah melakukan <br> presensi masuk</h4>
            <?php } ?>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-header">Presensi Keluar</div>
            <div class="card-body text-center">

              <?php 
              $ambil_data_presensi = mysqli_query($connection, "SELECT * FROM presensi WHERE id_pegawai = '$id_pegawai' AND tanggal_masuk = '$tanggal_hari_ini'");
              $waktu_sekarang = date('H:i:s');
              
              if(strtotime($waktu_sekarang) <= strtotime($jam_pulang)){ ?>
                <i class="fa-regular fa-circle-xmark fa-4x text-danger mb-2"></i>
                <h4>Belum waktunya pulang</h4>
                <?php }
              else if(strtotime($waktu_sekarang) >= strtotime($jam_pulang) && mysqli_num_rows($ambil_data_presensi) == 0){ ?>
                <i class="fa-regular fa-circle-xmark fa-4x text-danger mb-2"></i>
                <h4>Silahkan melakukan presensi masuk <br> terlebih dahulu!</h4>
              <?php }
              else{ ?>

                <?php while($cek_presensi_keluar = mysqli_fetch_array($ambil_data_presensi)){ ?>
                  <?php if($cek_presensi_keluar['tanggal_masuk'] && $cek_presensi_keluar['tanggal_keluar'] == '0000-00-00'){ ?>
                    <div class="parent-date">
                      <div id="tanggal-keluar"></div>
                      <div class="ms-2"></div>
                      <div id="bulan-keluar"></div>
                      <div class="ms-2"></div>
                      <div id="tahun-keluar"></div>
                    </div>
                    <div class="parent-clock">
                      <div id="jam-keluar"></div>
                      <div>:</div>
                      <div id="menit-keluar"></div>
                      <div>:</div>
                      <div id="detik-keluar"></div>
                    </div>
  
                    <form action="<?= base_url('pegawai/presensi/presensi_keluar.php') ?>" method="POST">
                      <input type="hidden" name="id" value="<?= $cek_presensi_keluar['id'] ?>">
                      <input type="hidden" name="latitude_pegawai" id="latitude-pegawai">
                      <input type="hidden" name="longitude_pegawai" id="longitude-pegawai">
                      <input type="hidden" value="<?= $latitude_kantor ?>" name="latitude_kantor">
                      <input type="hidden" value="<?= $longitude_kantor ?>" name="longitude_kantor">
                      <input type="hidden" value="<?= $radius ?>" name="radius">
                      <input type="hidden" value="<?= $zona_waktu ?>" name="zona_waktu">
                      <input type="hidden" value="<?= date('Y-m-d') ?>" name="tgl_keluar">
                      <input type="hidden" value="<?= date('H:i:s') ?>" name="jam_keluar">
                      <button type="submit" name="keluar" class="btn btn-danger mt-3">Keluar</button>
                    </form>
                  <?php }
                  else{ ?>
                    <i class="fa-regular fa-circle-check fa-4x text-success mb-2"></i>
                    <h4>Anda telah melakukan <br> presensi keluar</h4>
                  <?php } ?>
                <?php } ?>
              <?php } ?>
            </div>
        </div>
      </div>

      <div class="col-md-2"></div>
    </div>
  </div>
</div>

<script>
  // Set waktu presensi masuk
  window.setTimeout("waktuMasuk()", 1000);
  namaBulan = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'Nopember',
    'Desember'
  ];

  function waktuMasuk(){
    const waktu = new Date();
    setTimeout("waktuMasuk()", 1000);
    document.getElementById("tanggal-masuk").innerHTML = waktu.getDate();
    document.getElementById("bulan-masuk").innerHTML = namaBulan[waktu.getMonth()];
    document.getElementById("tahun-masuk").innerHTML = waktu.getFullYear();
    document.getElementById("jam-masuk").innerHTML = waktu.getHours();
    document.getElementById("menit-masuk").innerHTML = waktu.getMinutes();
    document.getElementById("detik-masuk").innerHTML = waktu.getSeconds();
  }


  // Set waktu presensi keluar
  window.setTimeout("waktuKeluar()", 1000);
  namaBulan = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'Nopember',
    'Desember'
  ];

  function waktuKeluar(){
    const waktu = new Date();
    setTimeout("waktuKeluar()", 1000);
    document.getElementById("tanggal-keluar").innerHTML = waktu.getDate();
    document.getElementById("bulan-keluar").innerHTML = namaBulan[waktu.getMonth()];
    document.getElementById("tahun-keluar").innerHTML = waktu.getFullYear();
    document.getElementById("jam-keluar").innerHTML = waktu.getHours();
    document.getElementById("menit-keluar").innerHTML = waktu.getMinutes();
    document.getElementById("detik-keluar").innerHTML = waktu.getSeconds();
  }

  getLocation();

  function getLocation(){
    if(navigator.geolocation){
      navigator.geolocation.getCurrentPosition(showPosition);
    }
    else{
      alert("Browser anda tidak mendukung!");
    }
  }

  function showPosition(position){
    $('#latitude-pegawai').val(position.coords.latitude);
    $('#longitude-pegawai').val(position.coords.longitude);
  }
</script>

<?php include('../layout/footer.php'); ?>
        