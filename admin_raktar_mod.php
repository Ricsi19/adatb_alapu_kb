<?php
session_start();
include "functions.php";
$shopId;

// szerzok kilistazasa
function getShops() {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1" ,"UTF8");
    $stmt = oci_parse($c, 'SELECT aruhaz_id, nev FROM system.aruhazak');
    oci_execute($stmt);
    $shops = array();
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $shops[$row['ARUHAZ_ID']] = $row['NEV'];
    }
    oci_free_statement($stmt);
    oci_close($c);
    return $shops;
}

function getShopNameById($shopId) {
    $shops = getShops();
    // Check if the shop ID exists in the array, return the name if found, otherwise return an empty string
    return isset($shops[$shopId]) ? $shops[$shopId] : "";
}

// Function to get the books for the selected shop
function getBooksForShop($shopId) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'SELECT 
                               K.konyv_id AS Book_id, 
                               K.cim AS Title, 
                               NVL(KA.db, 0) AS amount_in_choosenStore
                           FROM 
                               system.Konyv K
                           LEFT JOIN 
                               system.KonyvAruhaz KA ON K.konyv_id = KA.konyv_id AND KA.aruhaz_id = :chosenStoreId
                           ORDER BY
                               K.konyv_id ASC
    ');
    oci_bind_by_name($stmt, ':chosenStoreId', $shopId); // Bind the parameter
    oci_execute($stmt);
    
    $books = array();
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $books[] = $row;
    }
    
    oci_free_statement($stmt);
    oci_close($c);
    
    return $books;
}

// Handle form submission
if (isset($_GET['listBooksForShop'])) {
    $shopId = $_GET['aruhaz_id'];
    $books = getBooksForShop($shopId);
}

if (isset($_POST["add"])) {
    modify_konyvaruhaz_db($_POST["konyv_id"], $shopId, $_POST["add_val"]);
    header("Location: ".$_SERVER['PHP_SELF']."?aruhaz_id=".$shopId."&listBooksForShop=Listazz");
}

if (isset($_POST["remove"])) {
    $remove_val = $_POST["remove_val"] * (-1);
    modify_konyvaruhaz_db($_POST["konyv_id"], $shopId, $remove_val);
    header("Location: ".$_SERVER['PHP_SELF']."?aruhaz_id=".$shopId."&listBooksForShop=Listazz");
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
    <h1 style="margin-top:80px;text-align:center;">Raktárkészlet</h1>


    <form method="get">
        <select id="aruhaz_id" name="aruhaz_id" required>
            <?php
            $shops = getShops();
            foreach ($shops as $id => $name) {
                echo "<option value='$id'>$name</option>";
            }
            ?>
        </select>

        <input type="submit" value="Listazz" name="listBooksForShop">
    </form>

    <?php if (isset($books)): ?>

    <h2 style="margin-top:80px;text-align:center;"> <?php echo getShopNameById($shopId) ?> készlete: </h2>

    <table  style="margin: auto; width: 50%;" border="1">
        <tr>
            <th>Könyv ID</th>
            <th>Cím</th>
            <th>DB</th>
            <th colspan="2">Műveletek</th>
        </tr>
        <?php foreach ($books as $book): ?>
        <tr>
            <td><?php echo $book['BOOK_ID']; ?></td>
            <td><?php echo $book['TITLE']; ?></td>
            <td><?php echo $book['AMOUNT_IN_CHOOSENSTORE']; ?></td>
            <td>
                <form method="post">
                    <input type="number" min="0" max="100" name="add_val" value="0">
                    <input type="hidden" name="konyv_id" value=" <?php echo $book['BOOK_ID'];?>">
                    <input type="submit" name="add" value="Hozzáad">
                </form>
            </td>
            <td>
                <form method="post">
                    <input type="number" min="0" max="100" name="remove_val" value="0">
                    <input type="hidden" name="konyv_id" value=" <?php echo $book['BOOK_ID'];?>">
                    <input type="submit" name="remove" value="Levon">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

</body>
</html>