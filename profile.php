<?php
session_start();
require "functions.php";
//logout gomb megnyomasara a session valtozok false-ra allitodnak(kijelentkezik)
if(isset($_POST['logout'])){
    $_SESSION['logged'] = false;
    $_SESSION['admin'] = false;
    $_SESSION['torzsvasarlo'] = false;
    $_SESSION['user_id'] = -1;
    header("Location: index.php");
}
$user = get_user_by_id($_SESSION["user_id"]);
$torzsvasarlo = "";
if ($_SESSION["torzsvasarlo"]) {
    $torzsvasarlo = "Ön törzsvásárló!";
} else {
    $db = get_purchased_books($_SESSION["user_id"]);
    $torzsvasarlo = get_books_remaining($db);
}
if(isset($_POST["edit"])) {
    header("Location: profile_edit.php");
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/profile.css">
    <title>Document</title>
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="konyvek.php" >Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php" class="active">Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php" >Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php" >Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php" >Kosár</a></li>'; } ?>
      </ul>
    </nav>

<div id="main_container">

    <h1>Felhasználói adatok</h1>
    <div class="bar"></div>

    <div class="container">
        <p>Felhasználónév:</p>
        <p><?php echo $user["USERNAME"] ?></p>
    </div>

    <div class="container">
        <p>E-mail:</p>
        <p><?php echo $user["EMAIL"] ?></p>
    </div>

    <div class="container">
        <p>Telefonszám:</p>
        <p><?php echo $user["TELEFONSZAM"] ?></p>
    </div>

    <div class="container">
        <p>Lakcím:</p>
        <p><?php echo $user["LAKCIM"] ?></p>
    </div>

    <div class="bar"></div>

    <h2>Törzsvásárlói státusz</h2>

    <p> <?php echo $torzsvasarlo ?> </p>

</div>

<form method="post">
    <input type="submit" value="Adatok módosítása" name="edit">
    <input type="submit" value="Kijelentkezés" name="logout">
</form>
</body>
</html>