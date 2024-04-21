<?php
session_start();
require "functions.php";

$rendeles_rekordok = get_rendeles_by_user_id($_SESSION["user_id"]);
$j = 0;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/rendelesek.css">
    <title>Document</title>
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="konyvek.php" >Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php" >Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php" >Regisztráció</a></li>
          <li style="float:right"><a  href="login.php" >Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php" >Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php" class="active">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php" >Kosár</a></li>'; } ?>
      </ul>
    </nav>
<h1 style="margin-top:80px; text-align:center;">Rendelések</h1>

<div id="main_container">

    <?php
    $rendeles;
    for ($i = 0; $i < count($rendeles_rekordok); $i++) {
            $rendeles = "<div class='rendeles'>
                    <h1>Rendelés azonosító: ". $rendeles_rekordok[$i]["RENDELES_ID"] ."</h1>
                    <hr>
                    <div class='rendeles_tartalom'>";
            $j = $i;
            $cimek = "<div class='names'>";
            $arak = "<div class='prices'>";
            do {
                $cimek = $cimek . "<p>" . $rendeles_rekordok[$j]["CIM"] . "</p>";
                $arak = $arak . "<p>" . $rendeles_rekordok[$j]["DB"] . " db<br>" . $rendeles_rekordok[$j]["AR"] . " Ft</p>";
                $j++;
                if ($j >= count($rendeles_rekordok)) {break;}
            } while($rendeles_rekordok[$j]["RENDELES_ID"] === $rendeles_rekordok[$i]["RENDELES_ID"]);
            $i = $j - 1;
            $cimek = $cimek . "</div>";
            $arak = $arak . "</div>";

            $rendeles = $rendeles . $cimek . $arak .
                "</div>
                 <hr>
                 <div class='total'>
                    <p>Összesen:</p>
                    <p>" . $rendeles_rekordok[$i]['TOTAL'] . " Ft</p>
                 </div>
                 </div>";
            echo $rendeles;
        }
    ?>
</div>

</body>
</html>