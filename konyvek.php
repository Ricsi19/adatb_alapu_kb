<?php
session_start();
require 'functions.php';
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
        <li><a href="konyvek.php" class="active">Könyvek</a>
          <?php if($_SESSION['logged']){ echo '<li><a href="profile.php">Profil</a></li>'; } ?>
          <?php if(!$_SESSION['logged']){echo '<li style="float:right"><a  href="regist.php">Regisztráció</a></li>
          <li style="float:right"><a  href="login.php">Belépés</a></li>';} ?>
          <?php if($_SESSION['logged'] && $_SESSION['admin']){echo '<li><a  href="adminisztracio.php" >Adminisztráció</a></li>';} ?> 
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="rendelesek.php">Rendelések</a></li>'; } ?>
          <?php if($_SESSION['logged']){ echo '<li style="float:right"><a href="kosar.php">Kosár</a></li>'; } ?>
      </ul>
    </nav>
    <h1>Rendezés:</h1>
    <form method="post">
      <input type="submit" value="A-Z" name="a-z">
      <input type="submit" value="Z-A", name="z-a">
      <input type="submit" value="olcsó->drága" name="olcsodraga">
      <input type="submit" value="drága->olcsó" name="dragaolcso">
      <input type="submit" value="legújabb elől" name="uj">
      <input type="submit" value="legrégebbi elől" name="regi"><br>
        <!-- Todo: összefésülni az eredeti kóddal #1 -->
        <div id="search_bar">
            <input type="text" name="cim" placeholder="Cím">
            <input type="text" name="szerzo" placeholder="Szerző">

            <select name="mufaj">
                <option value=""></option>
                <?php get_mufajok(); ?>
            </select>
            <input type="submit" value="Keresés" name="kereses">
        </div>
    </form>


    <div class="grid-container">
<?php
//megnyomott gomb alapjan konyvek rendezese es kiiratasa gridbe
if(isset($_POST['a-z'])){
  $konyvek = get_konyvek_sorted(0);
for($i = 0; $i < count($konyvek)  ; $i++){
  konyv_toString($konyvek[$i]);};
}
elseif(isset($_POST['z-a'])){
  $konyvek = get_konyvek_sorted(1);
for($i = 0; $i < count($konyvek)  ; $i++){
  konyv_toString($konyvek[$i]);};
}
elseif(isset($_POST['olcsodraga'])){
  $konyvek = get_konyvek_sorted(2);
for($i = 0; $i < count($konyvek)  ; $i++){
  konyv_toString($konyvek[$i]);};
}
elseif(isset($_POST['dragaolcso'])){
  $konyvek = get_konyvek_sorted(3);
for($i = 0; $i < count($konyvek)  ; $i++){
  konyv_toString($konyvek[$i]);};
}
elseif(isset($_POST['uj'])){
  $konyvek = get_konyvek_sorted(4);
for($i = 0; $i < count($konyvek)  ; $i++){
  konyv_toString($konyvek[$i]);}
}
elseif(isset($_POST['regi'])){
  $konyvek = get_konyvek_sorted(5);
for($i = 0; $i < count($konyvek)  ; $i++){
  konyv_toString($konyvek[$i]);};
}
// Todo: összefésülni az eredeti kóddal #2
elseif(isset($_POST["kereses"])){
    if ($_POST['cim'] === null) {
        $cim = "";
    } else {
        $cim = $_POST['cim'];
    }
    $konyvek = get_konyvek_grouped($cim, $_POST['szerzo'], $_POST['mufaj']);
for ($i = 0; $i < count($konyvek); $i++) {
  konyv_toString($konyvek[$i]);}
}
else{
  $konyvek = get_konyvek_sorted(0);
  for($i = 0; $i < count($konyvek)  ; $i++){
    konyv_toString($konyvek[$i]);};
}
?>
</div>
</body>
</html>
