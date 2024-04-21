<?php
session_start();
include "functions.php";
$uzenetek = [];
$aruhaz_data = listShop();

if (isset($_POST["editChosenAruhaz"])){
    $hibak = validateShop($_POST["shopname"], $_POST["location"], $uzenetek);
    if ($hibak === 0) {
        $uzenetek[] = editShop($_POST["aruhaz_id"], $_POST["shopname"], $_POST["location"]);
        header("Refresh: 1 admin_aruhaz_mod.php");
    }
}

if (isset($_POST["addshop"])) {
    $hibak = validateShop($_POST["shopname"], $_POST["location"]);
    if ($hibak === 0) {
        $uzenetek[] = addShop($_POST["shopname"], $_POST["location"]);
    }
}

if (isset($_POST["deletearuhaz"]) && isset($_POST["aruhaz_id"])) {
    deleteAruhaz($_POST["aruhaz_id"]);
}

if (isset($_POST['editAruhaz'])) {
    foreach ($aruhaz_data as $aruhaz) {
        if ($aruhaz['ARUHAZ_ID'] == $_POST['aruhaz_id']) {
            $aruhaz_to_edit = $aruhaz;
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
    <title>Document</title>
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
<h1 style="margin-top:80px;text-align:center;">Áruházak</h1>
<?php if (!isset($aruhaz)): ?>
    <h2 style="margin-top:80px;text-align:center;"> hozzáadás</h2>

    <form method="post" enctype="multipart/form-data">
        <label for="shopname">Név:</label>
        <input type="text" id="shopname" name="shopname" required>

        <label for="location">Cím:</label>
        <input type="text" id="location" name="location" required><br><br>

        <input type="submit" value="Hozzáadás" name="addshop">
    </form>
<?php endif; ?>


<?php if (isset($aruhaz)): ?>
    <h2 style="margin-top:80px;text-align:center;"><?php echo $aruhaz['NEV']; ?> módosítása</h2>

    <form method="post">
        <label for="shopname">Név:</label>
        <input type="text" id="aruhaz_id" name="aruhaz_id" value="<?php echo $aruhaz['ARUHAZ_ID']; ?>" hidden>
        <input type="text" id="shopname" name="shopname" value="<?php echo $aruhaz['NEV']; ?>" required>

        <label for="location">Cím:</label>
        <input type="text" id="location" name="location" value="<?php echo $aruhaz['CIM']; ?>" required><br><br>
        <input type="submit" value="Mentés" name="editChosenAruhaz">
    </form>
    <form>
        <input type="submit" value="Mégse" name="megse">
    </form>
<?php endif; ?>

<?php echo '<h2 style="color: green; text-align:center;">' . implode($uzenetek) . "</h2>"; ?>



<h2 style="margin-top:80px;text-align:center;"> Lista:</h2>
<table style="margin: auto; width: 50%;" border="1">
    <tr>
        <th>Áruház ID</th>
        <th>Név</th>
        <th>Cím</th>
        <th>Műveletek</th>
    </tr>
    <?php foreach ($aruhaz_data as $aruhaz): ?>
        <tr>
            <td><?php echo $aruhaz['ARUHAZ_ID']; ?></td>
            <td><?php echo $aruhaz['NEV']; ?></td>
            <td><?php echo $aruhaz['CIM']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="aruhaz_id" value="<?php echo $aruhaz['ARUHAZ_ID']; ?>">
                    <input type="submit" value="Módosítás" name="editAruhaz">
                </form>
                <form method="post" onsubmit="return confirmDelete('<?php echo $aruhaz['NEV']; ?>')">
                    <input type="hidden" name="aruhaz_id" value="<?php echo $aruhaz['ARUHAZ_ID']; ?>">
                    <input type="submit" value="Törlés"  name="deletearuhaz">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<script>
    function confirmDelete(aruhazName) {
        return confirm("Biztosan törölni szeretné ezt az áruházat: " + aruhazName + "?");
    }
</script>


</body>
</html>