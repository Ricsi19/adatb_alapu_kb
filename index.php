<?php
session_start();
$_SESSION['logged'];
$_SESSION['admin'];
$_SESSION['torzsvasarlo'];
$_SESSION['user_id'];
//felhasznalo adatainak elmentese sessionbe belepes utan
if($_SESSION['logged'] == null && $_SESSION['admin'] == null && $_SESSION['torzsvasarlo'] == null && $_SESSION['user_id'] == null){
    $_SESSION['logged'] = false;
    $_SESSION['admin'] = false;
    $_SESSION['torzsvasarlo'] = false;
    $_SESSION['user_id'] = -1;
}
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
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="konyvek.php">Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php">Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php">Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php">Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php">Kosár</a></li>'; } ?>
      </ul>
    </nav>
<?php
$c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1");
if($c){
    echo '<h1 style="margin-top:80px; text-align:center;">Sikeres csatlakozás az adatbázishoz!</h1> <div class="center"><img src="lessgo.gif" alt="lessgo"></div>';
    oci_close($c);
}
else{
    echo '<h1 style="margin-top:80px; text-align:center;">Sikertelen csatlakozás az adatbázishoz! :( </h1><div class="center"><img src="nemar.gif" alt="nemar" ></div>';
}
?>
<div class="info">
    <p>Adatbázis alapú rendszerek gyakorlat projektmunka 2023/24/2 IB152L-5 csütörtök 14-16</p>
    <p>Könyvesbolt</p>
    <p>Máté Bence</p>
    <p>Rigó László</p>
    <p>Szabó Richárd István</p>
</div>
</body>
</html>
