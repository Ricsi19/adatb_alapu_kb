<?php
session_start();
require "functions.php";
//logout gomb megnyomasara a session valtozok false-ra allitodnak(kijelentkezik)
if(isset($_POST['logout'])){
    $_SESSION['logged'] = false;
    $_SESSION['admin'] = false;
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/konyv.css">
    <title>Document</title>
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="konyvek.php" >Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php">Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php" >Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php" >Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php" >Kosár</a></li>'; } ?>
      </ul>
    </nav>
<?php 
$uzenetek = "";
if(isset($_POST['kosarba'])){
    $uzenetek = "";
    if($_SESSION['user_id'] == -1){
        $uzenetek = "Nem vagy bejelentkezve!";
    }
    else{
        $uzenetek = kosarba_helyez($_GET['id'], $_POST['db'], $_SESSION['user_id']);
    }
}
$konyv = get_konyv($_GET['id']);
$imagedata = base64_encode($konyv['BORITO']);
konyv_toString($konyv);
echo '<form method="post"><input type="submit" value="Kosárba rakom!" name="kosarba" id="kosarba"><input type="number" name="db" value="1" id="kosarba" required ></form>';
$keszletek = get_konyv_keszlet($_GET['id']);
echo '<h1 style="color:red;">'.$uzenetek.'</h1><br><div class="grid-item">';
echo '<h1>Készlet</h1><br><div class="grid-item">';
if($keszletek == null){
    echo'Egyik raktárban sem elérhető!';
}
else{
    for($i = 0; $i < count($keszletek)  ; $i++){
        echo '<p>'. $keszletek[$i]['NEV'] .'</p><p>'.$keszletek[$i]['DB'].'</p>';
    }
}
echo '</div>';
?>
</div>
</body>
</html>