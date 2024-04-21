<?php
session_start();
require "functions.php";
$users = get_users();

if (isset($_POST["delete_user"])) {
    if (isset($_POST["user_id"])) {
        delete_user($_POST["user_id"]);
        header("Location: admin_felhasznalo_mod.php");
    }
}
if (isset($_POST["edit_user"])) {
    header("Location: admin_profile_edit.php?user_id=" . $_POST["user_id"]);
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <h1 style="margin-top:80px;text-align:center;">Felhasználók:<br></h1>
        <table  style="margin: auto; width: 50%;" border="1">
            <tr>
                <th>Felhasználó ID</th>
                <th>Felhasználónév</th>
                <th>Email</th>
                <th>Lakcím</th>
                <th>Telefonszám</th>
                <th>Admin</th>
                <th>Törzsvásárló</th>
                <th>Módosítás</th>
                <th>Törlés</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['USER_ID']; ?></td>
                    <td><?php echo $user['USERNAME']; ?></td>
                    <td><?php echo $user['EMAIL']; ?></td>
                    <td><?php echo $user['LAKCIM']; ?></td>
                    <td><?php echo $user['TELEFONSZAM']; ?></td>
                    <td><?php echo ($user['ADMIN']) ? "Igen" : "Nem" ?></td>
                    <td><?php echo ($user['TORZSVASARLO']) ? "Igen" : "Nem"; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?php echo $user['USER_ID']; ?>">
                            <input type="submit" value="Szerkesztés" name="edit_user">
                        </form>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirmDelete('<?php echo $user['USERNAME']; ?>')">
                            <input type="hidden" name="user_id" value="<?php echo $user['USER_ID']; ?>">
                            <input type="submit" value="Törlés" name="delete_user">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

    <script>
        function confirmDelete(username) {
            return confirm("Biztosan törölni szeretné ezt a felhasználót: " + username + "?");
        }
    </script>

</body>
</html>