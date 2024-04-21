<?php
session_start();
require "functions.php";

//logout gomb megnyomasara a session valtozok false-ra allitodnak(kijelentkezik)
if(isset($_POST['cancel'])){
    header("Location: profile.php");
}
$user = get_user_by_id($_SESSION["user_id"]);
$uzenetek = [];
if (isset($_POST['edit'])) {
    $uzenetek = profil_szerkesztes_ellenorzes($_SESSION['user_id'], $_POST['username'], $_POST['email'], $_POST['mobilphone'], $_POST['location'], $_POST['password'], $_POST['password2']);
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

    <h1>Felhasználói adatok szerkesztése</h1>
    <div class="bar"></div>

    <form method="POST">
        <fieldset>
            <label>Felhasználónév: <input type="text" name="username" value="<?php echo $user['USERNAME'] ?>" ></label>
            <label>E-mail cím: <input type="email" name="email" value="<?php echo $user['EMAIL'] ?>" ></label>
            <label>Telefonszám: (06 20 123 4567 formátumban)<input type="text" name="mobilphone"  maxlength="14" value="<?php echo $user['TELEFONSZAM'] ?>" ></label>
            <label>Lakcím: <input type="text" name="location" value="<?php echo $user['LAKCIM'] ?>" ></label>
            <label>Jelszó: (minimum 5 karakter) <input type="password" name="password"></label>
            <label>Jelszó mégegyszer: <input type="password" name="password2"></label>
            <p>*Ha a jelszót nem kívánja módosítani, hagyja üresen</p>
        </fieldset>
        <input type="submit" value="Mentés" name="edit" >
        <input type="submit" value="Mégsem" name="cancel">
    </form>
</div>
<?php echo '<h2 style="color: red; text-align:center;">' . implode($uzenetek) . "</h2>"; ?>
</body>
</html>