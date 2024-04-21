<?php
session_start();
require "functions.php";
  $uzenetek = [];
  $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1");
  if (isset($_POST["regiszt"])) {  
      $uzenetek = regisztracio_ellenorzes($_POST["username"], $_POST["location"], $_POST["email"], $_POST["mobilphone"], $_POST["password"], $_POST["password2"]);
    }
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        h1{
            margin-top:80px;
            text-align:center;
        }
    </style>
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="konyvek.php" >Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php" >Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php" class="active">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php" >Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php" >Adminisztáció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php" >Kosár</a></li>'; } ?>
      </ul>
    </nav>
    <h1>Regisztráció</h1>
    <form method="POST">
      <fieldset>
        <label>Felhasználónév: <input type="text" name="username" required ></label>
        <label>E-mail cím: <input type="email" name="email" required ></label>
        <label>Telefonszám: (06 20 123 4567 formátumban)<input type="text" name="mobilphone"  maxlength="14" required ></label>
        <label>Lakcím: <input type="text" name="location" required ></label>
        <label>Jelszó: (minimum 5 karakter) <input type="password" name="password"  required ></label>
        <label>Jelszó mégegyszer: <input type="password" name="password2" required ></label>
      </fieldset>
      <input type="submit" value="Regisztráció!" name="regiszt" >
    </form>
    <?php echo '<h2 style="color: red; text-align:center;">' . implode($uzenetek) . "</h2>"; ?>
</body>
</html>