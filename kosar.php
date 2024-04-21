<?php
session_start();
require "functions.php";
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <title>Document</title>
    <style>
        h1{
            margin-top:80px;
            text-align:center;
        }
        a{
        text-decoration: none;
        cursor: default; 
        color:black;
        }
        .grid-item {
        background-color: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.8);
        padding: 20px;
        font-size: 30px;
        text-align: center;
        }
        .grid-container {
        display: grid;
        grid-template-columns: auto auto auto;
        background-color: #2196F3;
        padding: 10px;
        }
    </style>
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="konyvek.php" >Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php">Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php">Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php" >Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php" class="active">Kosár</a></li>'; } ?>
      </ul>
    </nav>
<h1>Kosár tartalma</h1>
<?php
$kosartartalom = get_kosar($_SESSION['user_id']);
$sum = 0;
for($i = 0; $i < count($kosartartalom); $i++){
    $konyvekar = 0;
    $konyv = get_konyv($kosartartalom[$i]['KONYV_ID']);
    konyv_toString($konyv);
    $konyvekar = $kosartartalom[$i]['DB'] * $konyv['AR'];
    echo '<h2 style="text-align:center;">'.$kosartartalom[$i]['DB'].'DB</h2>';
    echo '<h2 style="text-align:center;">'.$konyvekar.'Ft összesen</h2>';
    $sum += $konyvekar;
}
echo '<h1>'.$sum.'Ft összesen a kosár tartalma';
if(isset($_POST['rendeles'])){
    if(count(get_kosar($_SESSION['user_id'])) != 0){
        rendeles_keszit($_SESSION['user_id']);
        kosar_urit($_SESSION['user_id']);
        echo '<script>
        alert("Sikeres rendelés!");
        </script>';
    }
    else{
        echo '<h1>Üres a kosár!</h1>';
    }
}
?>
<form method="post">
    <input type="submit", value="Megrendelem!", name="rendeles">
</form>
</body>
</html>