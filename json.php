<?php
$host="localhost";
$user="root";
$password="";	
$koneksi = mysqli_connect("localhost", "root", "", "museum_louvre");
$query = mysqli_query($koneksi, "SELECT * FROM chart");

$table = array();
$table['cols'] = array(
	/* Disini kita mendefinisikan fatka pada tabel database
	 * masing-masing kolom akan kita ubah menjadi array
	 * Kolom tersebut adalah parameter (string) dan nilai (integer/number)
	 * Pada bagian ini kita juga memberi penamaan pada hasil chart nanti
	 */
    array('label' => 'Year', 'type' => 'string'),
    array('label' => 'Visitors', 'type' => 'number')
	
);
// melakukan query yang akan menampilkan array data
$rows = array();
while($r = mysqli_fetch_assoc($query)) {
    $temp = array();
	// masing-masing kolom kita masukkan sebagai array sementara
	$temp[] = array('v' => $r['tahun']);
	$temp[] = array('v' => (int) $r['jumlah_pengunjung']);
    $rows[] = array('c' => $temp);
}
// mempopulasi row tabel
$table['rows'] = $rows;
// encode tabel ke bentuk json
$jsonTable = json_encode($table);

// set up header untuk JSON, wajib.
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

// menampilkan data hasil query ke bentuk json
echo $jsonTable;
?>