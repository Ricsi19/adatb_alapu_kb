<?php
session_start();
$c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1"); 
//Ezt csak arra hasznaltam hogy a mar meglevo konyveknek toltsek fel boritokepet, ezt a BLOB eltarolast meg kell tartani mert fontos es nehez! xd
//Mivel nem lehet adatbazisbol exportalni blobot, itt toltsetek fel boritot(kepek mappaba beraktam oket), nezzetek meg melyik idhez melyik konyv tartozik es ird at a $sql-t
if(isset($_POST['feltolt'])){
    $image = file_get_contents($_FILES['fileToUpload']['tmp_name']);
    $sql = "UPDATE system.konyv SET borito = empty_blob() WHERE konyv_id = 14 RETURNING borito INTO :borito";
    $result = oci_parse($c, $sql);
    $blob = oci_new_descriptor($c, OCI_D_LOB);
    oci_bind_by_name($result, ":borito", $blob, -1, OCI_B_BLOB);
    oci_execute($result, OCI_DEFAULT) or die ("Unable to execute query");
    if(!$blob->save($image)) {
        oci_rollback($c);
    }
    else {
        oci_commit($c);
    }
    oci_free_statement($result);
    $blob->free();
}
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
</body>
</html>