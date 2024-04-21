<?php
session_start();
$uzenetek = "";
$c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1");
if(isset($_POST["login"])){
    if(!isset($_POST["username"]) || trim($_POST["username"]) === "" || !isset($_POST["password"]) || trim($_POST["password"]) === ""){
      $uzenetek = "Felhasználónév és jelszó megadása kötelező!";
    }
    else{
        $password = $_POST["password"];
        $username = $_POST["username"];
        $stmt = oci_parse($c, 'select * from system.felhasznalok where username = :username');
        oci_bind_by_name($stmt, ':username', $username, -1);
        oci_execute($stmt, OCI_DEFAULT);
        $user = oci_fetch_array($stmt, OCI_ASSOC);
        if($user != null && password_verify($password, $user["JELSZO"])){
            $_SESSION["logged"] = true;
            $_SESSION["user_id"] = $user["USER_ID"];
            $uzenetek = 'Sikeres bejelentkezés!';
            if($user['ADMIN'] == 1){
                $_SESSION['admin'] = true;   
            }
            if($user['TORZSVASARLO'] == 1){
                $_SESSION['torzsvasarlo'] = true;
            }
        }
        else{
            $uzenetek = "Hibás felhasználónév vagy jelszó!";
        }
    }
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
        <li><a href="konyvek.php" >Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php">Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php" class="active">Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php" >Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php" >Kosár</a></li>'; } ?>
      </ul>
    </nav>
<h1 style="margin-top:80px; text-align:center;">Bejelentkezés</h1>
    <form method="post">
      <fieldset>
        <label>Felhasználónév: <input type="text" name="username"  ></label>
        <label>Jelszó: <input type="password" name="password"  ></label>
      </fieldset>
      <input type="submit" value="Belépés!" name="login">
    </form>
    <?php echo '<h2 style="color: red; text-align:center;">' . $uzenetek . "</h2>"; ?>
</body>
</html>