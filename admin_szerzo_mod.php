<?php
session_start();
include "functions.php";
$uzenetek = [];
$szerzo_data = listSzerzo();

if (isset($_POST["deleteszerzo"])) {
    if(isset($_POST["szerzo_id"])) {
        deleteSzerzo($_POST["szerzo_id"]);
        header("Location: admin_szerzo_mod.php");
    }
}

if (isset($_POST["addszerzo"])) {
    $hibak = 0;
    if (!isset($_POST["nev"]) || trim($_POST["nev"]) === ""){
        $uzenetek[] = "Szerző nevének megadása kötelező!";
        $hibak++;
    }else{
        $szerzo_nev = $_POST["nev"];
    }

    if($hibak == 0){
        $uzenetek[] = addSzerzo($szerzo_nev);
        header("Refresh: 1 admin_szerzo_mod.php");
    }

}

if (isset($_POST["editChosenSzerzo"])){
    $hibak = 0;
    if (!isset($_POST["szerzo_nev"]) || trim($_POST["szerzo_nev"]) === ""){
        $uzenetek[] = "Szerző nevének megadása kötelező!";
        $hibak++;
    }else{
        $szerzo_nev = $_POST["szerzo_nev"];
        $szerzo_id = $_POST["szerzo_id"];
    }

    if($hibak == 0){
        $uzenetek[] = editSzerzo($szerzo_id,$szerzo_nev);
        header("Refresh: 1 admin_szerzo_mod.php");
    }


}

if (isset($_POST['editszerzo'])) {
    foreach ($szerzo_data as $szerzo) {
        if ($szerzo['SZERZO_ID'] == $_POST['szerzo_id']) {
            $szerzo_to_edit = $szerzo;
            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/admin.css">
    <title>Szerzők hozzáadása</title>
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

<h1 style="margin-top:80px;text-align:center;">Szerzők</h1>

<?php if (!isset($szerzo)): ?>
    <h2 style="margin-top:80px;text-align:center;">Hozzáadás</h2>


    <form method="post">
        <label for="nev">Szerző neve:</label><br>
        <input type="text" id="nev" name="nev"><br><br>
        <input type="submit" value="Hozzáadás" name="addszerzo">
    </form>
<?php endif; ?>

<?php echo '<h2 style="color: red; text-align:center;">' . implode($uzenetek) . "</h2>"; ?>


<?php if (isset($szerzo)): ?>
    <h2 style="margin-top:80px;text-align:center;">Módosítás</h2>

    <form method="post">
        <label for="nev"><?php echo $szerzo['NEV']; ?> nevének módosítása:</label><br>
        <input type="text" id="szerzo_id" name="szerzo_id" value="<?php echo $szerzo['SZERZO_ID']; ?>" hidden>
        <input type="text" id="szerzo_nev" name="szerzo_nev" value="<?php echo $szerzo['NEV']; ?>"><br><br>
        <input type="submit" value="Mentés" name="editChosenSzerzo">
    </form>
    <form>
        <input type="submit" value="Mégse">
    </form>



<?php endif; ?>



<h2 style="margin-top: 40px; text-align: center;">Szerzők listája</h2>
<table style="margin: auto; width: 50%;" border="1">
    <tr>
        <th>Szerző ID</th>
        <th>Szerző neve</th>
        <th>Műveletek</th>
    </tr>
    <?php foreach ($szerzo_data as $szerzo): ?>
        <tr>
            <td><?php echo $szerzo['SZERZO_ID']; ?></td>
            <td><?php echo $szerzo['NEV']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="szerzo_id" value="<?php echo $szerzo['SZERZO_ID']; ?>">
                    <input type="submit" value="Módosítás" name="editszerzo">
                </form>
                <form method="post" onsubmit="return confirmDelete('<?php echo $szerzo['NEV']; ?>')">
                    <input type="hidden" name="szerzo_id" value="<?php echo $szerzo['SZERZO_ID']; ?>">
                    <input type="submit" value="Törlés" name="deleteszerzo">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>


<script>
    function confirmDelete(szerzoName) {
        return confirm("Biztosan törölni szeretné ezt a szerzőt: " + szerzoName + "?");
    }
</script>

</body>
</html>
