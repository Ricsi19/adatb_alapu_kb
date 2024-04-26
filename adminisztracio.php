<?php
session_start();
$c = oci_connect("localuser", "admin123", "//localhost:1521/XEPDB1");
$aruhaz_konyv_mufaj = oci_parse($c, 
'SELECT A.nev AS aruhaz_neve, K.mufaj, AVG(K.ar) AS atlagos_ar
FROM system.aruhazak A
INNER JOIN system.konyvaruhaz KA ON A.aruhaz_id = KA.aruhaz_id
INNER JOIN system.konyv K ON KA.konyv_id = K.konyv_id
GROUP BY A.nev, K.mufaj');
oci_execute($aruhaz_konyv_mufaj, OCI_DEFAULT) or die(oci_error());

$felhasznalo_rendelt_konyv = oci_parse($c, 
'SELECT F.username, COUNT(RT.rendeles_id) AS rendelt_konyvek_szama
FROM SYSTEM.FELHASZNALOK F
LEFT JOIN SYSTEM.RENDELES R ON F.user_id = R.user_id
LEFT JOIN SYSTEM.RENDELESTARTALOM RT ON R.rendeles_id = RT.rendeles_id
GROUP BY F.username
HAVING COUNT(RT.rendeles_id) > 0');
oci_execute($felhasznalo_rendelt_konyv, OCI_DEFAULT);

$konyv_csokkeno = oci_parse($c, 
'SELECT K.cim, S.nev AS szerzo_neve, K.ar
FROM SYSTEM.KONYV K
INNER JOIN SYSTEM.IRTA I ON K.konyv_id = I.konyv_id
INNER JOIN SYSTEM.SZERZO S ON I.szerzo_id = S.szerzo_id
ORDER BY K.ar DESC');
oci_execute($konyv_csokkeno, OCI_DEFAULT);

$aruhaz_mufaj_draga = oci_parse($c, 
'SELECT A.nev AS aruhaz_neve, K.mufaj, AVG(K.ar) AS atlagos_ar
FROM SYSTEM.ARUHAZAK A
INNER JOIN SYSTEM.KONYVARUHAZ KA ON A.aruhaz_id = KA.aruhaz_id
INNER JOIN SYSTEM.KONYV K ON KA.konyv_id = K.konyv_id
GROUP BY A.nev, K.mufaj
HAVING AVG(K.ar) > 3000');
oci_execute($aruhaz_mufaj_draga, OCI_DEFAULT);

$user_koltott_osszeg = oci_parse($c, 
'SELECT F.username, SUM(K.ar * RT.db) AS osszertek
FROM SYSTEM.FELHASZNALOK F
LEFT JOIN SYSTEM.RENDELES R ON F.user_id = R.user_id
LEFT JOIN SYSTEM.RENDELESTARTALOM RT ON R.rendeles_id = RT.rendeles_id
LEFT JOIN SYSTEM.KONYV K ON RT.konyv_id = K.konyv_id
GROUP BY F.username
HAVING SUM(K.ar * RT.db) > 0');
oci_execute($user_koltott_osszeg, OCI_DEFAULT);

$konyvek_kosarban = oci_parse($c, 
'SELECT K.cim, K.ar, SUM(KO.db) AS kosarban_db
FROM SYSTEM.KONYV K
INNER JOIN SYSTEM.KOSAR KO ON K.konyv_id = KO.konyv_id
GROUP BY K.cim, K.ar');
oci_execute($konyvek_kosarban, OCI_DEFAULT);

$aruhaz_kulonbozo_konyvek = oci_parse($c, 
'SELECT A.nev AS aruhaz_neve, K.mufaj, COUNT(KA.konyv_id) AS konyv_darabszam
FROM SYSTEM.ARUHAZAK A
INNER JOIN SYSTEM.KONYVARUHAZ KA ON A.aruhaz_id = KA.aruhaz_id
INNER JOIN SYSTEM.KONYV K ON KA.konyv_id = K.konyv_id
GROUP BY A.nev, K.mufaj');
oci_execute($aruhaz_kulonbozo_konyvek, OCI_DEFAULT);

$szerzok_konyv_sum = oci_parse($c, 
'SELECT S.nev AS szerzo_neve, COUNT(I.konyv_id) AS konyvek_szama
FROM SYSTEM.SZERZO S
LEFT JOIN SYSTEM.IRTA I ON S.szerzo_id = I.szerzo_id
GROUP BY S.nev
HAVING COUNT(I.konyv_id) > 0');
oci_execute($szerzok_konyv_sum, OCI_DEFAULT);

oci_close($c);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <title>Document</title>
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="konyvek.php">Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php">Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php">Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a href="adminisztracio.php" class="active">Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php">Kosár</a></li>'; } ?>
      </ul>
    </nav>
<h1 style="margin-top:80px;text-align:center;">Adminisztrációs műveletek:</h1>
<h2>
    <a href="admin_szerzo_mod.php">Szerzők</a>
</h2>
<h2>
    <a href="admin_konyv_mod.php">Könyvek</a>
</h2>
<h2>
    <a href="admin_aruhaz_mod.php">Áruházak</a>
</h2>
<h2>
    <a href="admin_felhasznalo_mod.php">Felhasználók</a>
</h2>
<h2>
    <a href="admin_raktar_mod.php">Raktár Készlet</a>
</h2>
<div style="text-align:center;">
<h1 style="margin-top:80px;text-align:center;">Admin lekérdezések:</h1>
<?php
echo '<h2>Áruházak nevei és az ott elérhető könyvek átlagos árai műfajonként: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($aruhaz_konyv_mufaj);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($aruhaz_konyv_mufaj, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($aruhaz_konyv_mufaj, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Felhasználók és a rendelt könyvek száma felhasználónként: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($felhasznalo_rendelt_konyv);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($felhasznalo_rendelt_konyv, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($felhasznalo_rendelt_konyv, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Könyvek címe és szerzőjének neve, rendezve az árak szerint csökkenő sorrendben: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($konyv_csokkeno);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($konyv_csokkeno, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($konyv_csokkeno, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Áruházak és az ott elérhető könyvek átlagos ára, műfajonként, csak azokra a műfajokra korlátozva, ahol az átlagos ár meghaladja az 3000 Ft-ot: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($aruhaz_mufaj_draga);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($aruhaz_mufaj_draga, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($aruhaz_mufaj_draga, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Felhasználók és a rendelt könyvek összértéke felhasználónként: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($user_koltott_osszeg);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($user_koltott_osszeg, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($user_koltott_osszeg, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Könyvek címe, ára és azok darabszáma a kosarakban: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($konyvek_kosarban);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($konyvek_kosarban, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($konyvek_kosarban, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Áruházak neve és az ott elérhető külöböző könyvek darabszáma műfajonként: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($aruhaz_kulonbozo_konyvek);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($aruhaz_kulonbozo_konyvek, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($aruhaz_kulonbozo_konyvek, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Szerzők és általuk írt könyvek száma: </h2>';
echo '<table border="1" style="margin-left: auto; margin-right: auto;">';
$nfields = oci_num_fields($szerzok_konyv_sum);
echo '<tr>';
for ($i = 1; $i<=$nfields; $i++){
    $field = oci_field_name($szerzok_konyv_sum, $i);
    echo '<td>' . $field . '</td>';
}
echo '</tr>';
while ( $row = oci_fetch_array($szerzok_konyv_sum, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<tr>';
    foreach ($row as $item) {
        echo '<td>' . $item . '</td>';
    }
    echo '</tr>';
}
echo '</table>';
?>
</div>
</body>
</html>