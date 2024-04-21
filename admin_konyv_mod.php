<?php
session_start();
require "functions.php";

$uzenetek[]="";
$konyv_data = listKonyv();

function getWriters() {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'SELECT szerzo_id, nev FROM system.szerzo');
    oci_execute($stmt);
    $writers = array();
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $writers[$row['SZERZO_ID']] = $row['NEV'];
    }
    oci_free_statement($stmt);
    oci_close($c);
    return $writers;
}

// form validálások
if (isset($_POST["addbook"])) {
    $hibak = 0;

    if (!isset($_POST["title"]) || trim($_POST["title"]) === ""){
        $uzenetek[] = "Add meg a könyv címét!";
        $hibak++;
    }else{
        $title = $_POST["title"];
    }

    if (!isset($_POST["publisher"]) || trim($_POST["publisher"]) === ""){
        $uzenetek[] = "Add meg a kiadót!";
        $hibak++;
    }else{
        $publisher = $_POST["publisher"];
    }

    if (!isset($_POST["price"]) || $_POST["price"] <= 0 ){
        $uzenetek[] = "Add meg a könyv árát (nem lehet 0 vagy negatív szám)!";
        $hibak++;
    }else{
        $price = $_POST["price"];
    }

    if (!isset($_POST["date"]) || $_POST["date"] > date("Y") ){
        $uzenetek[] = "Add meg a könyv kiadási évét!";
        $hibak++;
    }else{
        $date = $_POST["date"];
    }

    if (!isset($_POST["genre"]) || trim($_POST["genre"]) === ""){
        $uzenetek[] = "Add meg a könyv műfaját!";
        $hibak++;
    }else{
        $genre = $_POST["genre"];
    }

    if (!isset($_FILES["cover_art"])){
        $uzenetek[] = "Add meg a könyv borítóját!";
        $hibak++;
    }else{
        $cover_art = file_get_contents($_FILES["cover_art"]["tmp_name"]);
    }

    $szerzo_id = $_POST["szerzo_id"];

    if($hibak==0){
        $uzenetek[] = addBook($title, $publisher, $price, $date, $cover_art, $genre, $szerzo_id);
        header("Refresh: 1 admin_konyv_mod.php");
    }
}

if (isset($_POST["editChosenBook"])) {
    $hibak = 0;

    if (!isset($_POST["title"]) || trim($_POST["title"]) === ""){
        $uzenetek[] = "Add meg a könyv címét!";
        $hibak++;
    }else{
        $title = $_POST["title"];
    }

    if (!isset($_POST["publisher"]) || trim($_POST["publisher"]) === ""){
        $uzenetek[] = "Add meg a kiadót!";
        $hibak++;
    }else{
        $publisher = $_POST["publisher"];
    }

    if (!isset($_POST["price"]) || $_POST["price"] <= 0 ){
        $uzenetek[] = "Add meg a könyv árát (nem lehet 0 vagy negatív szám)!";
        $hibak++;
    }else{
        $price = $_POST["price"];
    }

    if (!isset($_POST["date"]) || $_POST["date"] > date("Y") ){
        $uzenetek[] = "Add meg a könyv kiadási évét!";
        $hibak++;
    }else{
        $date = $_POST["date"];
    }

    if (!isset($_POST["genre"]) || trim($_POST["genre"]) === ""){
        $uzenetek[] = "Add meg a könyv műfaját!";
        $hibak++;
    }else{
        $genre = $_POST["genre"];
    }

    if (empty($_FILES["new_cover_art"]["tmp_name"])) {
        $cover_art = null; // Set cover_art to null if no file was uploaded
    } else {
        $cover_art = file_get_contents($_FILES["new_cover_art"]["tmp_name"]);
    }
    $book_id = $_POST["konyv_id"];

    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1");
    $sql = "SELECT * FROM system.irta WHERE konyv_id = :book_id";
    $stmt = oci_parse($c, $sql);
    oci_bind_by_name($stmt, ':book_id', $book_id);
    oci_execute($stmt);

    // Check if any rows were returned
    if (oci_fetch_assoc($stmt)) {
        // Book ID exists in the irta table
        $isConnected = true;
    } else {
        // Book ID does not exist in the irta table
        $isConnected = false;
    }
    oci_free_statement($stmt);

    oci_close($c);

    $szerzo_id = $_POST["szerzo_id"];

    if($hibak==0){
        if(editBook($book_id, $title, $publisher, $price, $date, $cover_art, $genre, $szerzo_id, $isConnected)){
            $uzenetek[] = "sikeres módosítás";
            $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1");
            $stmt = oci_parse($c, 'SELECT * FROM system.konyv ORDER BY konyv_id ASC');
            oci_execute($stmt);

            $konyv_data = array();
            while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                $konyv_data[] = $row;
            }
            oci_free_statement($stmt);
        }
    }
}

if (isset($_POST['editBook'])) {
    foreach ($konyv_data as $konyv) {
        if ($konyv['KONYV_ID'] == $_POST['konyv_id']) {
            $konyv_to_edit = $konyv;

            $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1");
            $stmt = oci_parse($c, 'SELECT * FROM system.IRTA WHERE KONYV_ID = :konyv_id_bv');
            oci_bind_by_name($stmt, ':konyv_id_bv', $konyv['KONYV_ID']);

            oci_execute($stmt);

            // Store szerzo data in an array
            while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                $szerzo_data = $row;
            }
            oci_free_statement($stmt);

            break;
        }
    }
}

if (isset($_POST["deletebook"])) {
    if(isset($_POST["konyv_id"])) {
        deleteBook($_POST["konyv_id"]);
        header("Location: admin_konyv_mod.php");
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
    <title>Új könyv hozzáadása</title>
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
<?php if (!isset($konyv)): ?>
    <h1 style="margin-top:80px;text-align:center;">Új könyv hozzáadása</h1>

    <form method="post" enctype="multipart/form-data">
        <label for="title">Cím:</label><br>
        <input type="text" id="title" name="title" required><br><br>

        <label for="publisher">Kiadó:</label><br>
        <input type="text" id="publisher" name="publisher" required><br><br>

        <label for="price">Ár:</label><br>
        <input type="number" id="price" name="price" required><br><br>

        <label for="date">Megjelenés éve:</label><br>
        <input type="number" id="date" name="date" min="0" max="<?php echo date('Y'); ?>" required><br><br>

        <label for="genre">Műfaj:</label><br>
        <input type="text" id="genre" name="genre" required><br><br>

        <label for="cover_art">Borítókép:</label><br>
        <input type="file" id="cover_art" name="cover_art" accept="image/*" required><br><br>

        <label for="szerzo_id">Szerző:</label><br>
        <select id="szerzo_id" name="szerzo_id" required>
            <?php
            $writers = getWriters();
            foreach ($writers as $id => $name) {
                echo "<option value='$id'>$name</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" value="Hozzáadás" name="addbook">
    </form>
<?php endif; ?>



<?php if (isset($konyv)): ?>
    <h1 style="margin-top:80px;text-align:center;">"<?php echo $konyv['CIM']?>" módosítása</h1>

    <form method="post" enctype="multipart/form-data">
        <input type="text" id="konyv_id" name="konyv_id" value="<?php echo $konyv['KONYV_ID']?>" required hidden><br><br>
        <label for="title">Cím:</label><br>
        <input type="text" id="title" name="title" value="<?php echo $konyv['CIM']?>" required><br><br>

        <label for="publisher">Kiadó:</label><br>
        <input type="text" id="publisher" name="publisher" value="<?php echo $konyv['KIADO']?>" required><br><br>

        <label for="price">Ár:</label><br>
        <input type="number" id="price" name="price" value="<?php echo $konyv['AR']?>" required><br><br>

        <label for="date">Megjelenés éve:</label><br>
        <input type="number" id="date" name="date" min="0" max="<?php echo date('Y'); ?>" value="<?php echo $konyv['KIADAS']?>" required><br><br>

        <label for="genre">Műfaj:</label><br>
        <input type="text" id="genre" name="genre" value="<?php echo $konyv['MUFAJ']?>" required><br><br>

        <label for="new_cover_art">Jelenlegi borítókép:</label><br>
        <img src="data:image/jpeg;base64,<?php if(!is_null($konyv['BORITO'])){echo base64_encode($konyv['BORITO']->load());}  ?>" height="150px"><br>
        <label for="new_cover_art">Új borítókép (ne adjon meg képet ha nem akar módosítani):</label>
        <input type="file" id="new_cover_art" name="new_cover_art" accept="image/*"><br><br>

        <label for="szerzo_id">Szerző:</label><br>
        <?php
        // Check if there is no szerzo data for the current book
        if (empty($szerzo_data)) {
            echo "<h2>A Könyvhöz jelenleg nincs szerző kötve, kérem válasszon új szerzőt!</h2>";
            echo "<input type='text' id='connection' name='connection' value='false' required hidden>";
        }else{
            echo "<input type='text' id='connection' name='connection' value='true' required hidden>";
        }
        ?>
        <select id="szerzo_id" name="szerzo_id" required>
            <?php
            $writers = getWriters();
            foreach ($writers as $id => $name) {
                // Check if the current writer ID matches the szerzo_id from $szerzo_data
                $selected = ($id == $szerzo_data['SZERZO_ID']) ? 'selected' : '';
                echo "<option value='$id' $selected>$name</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" value="Mentés" name="editChosenBook">
    </form>
    <form>
        <input type="submit" value="Mégse" name="megse">
    </form>
<?php endif; ?>


<?php echo '<h2 style="color: green; text-align:center;">' . implode($uzenetek) . "</h2>"; ?>

<table  style="margin: auto; width: 50%;" border="1">
    <tr>
        <th>Könyv ID</th>
        <th>Cím</th>
        <th>Kiado</th>
        <th>Ar</th>
        <th>Kiadas</th>
        <th>Borito</th>
        <th>Mufaj</th>
        <th>Műveletek</th>
    </tr>
    <?php foreach ($konyv_data as $konyv): ?>
        <tr>
            <td><?php echo $konyv['KONYV_ID']; ?></td>
            <td><?php echo $konyv['CIM']; ?></td>
            <td><?php echo $konyv['KIADO']; ?></td>
            <td><?php echo $konyv['AR']; ?></td>
            <td><?php echo $konyv['KIADAS']; ?></td>
            <td>
                <?php
                // Check if the 'BORITO' field is not null
                if (!is_null($konyv['BORITO'])) {
                    // Get the binary data from the OCILob field
                    $imageData = $konyv['BORITO']->load();
                    // Encode the binary data as base64
                    $encodedImageData = base64_encode($imageData);
                    // Echo the image tag with the base64-encoded data
                    echo '<img src="data:image/jpeg;base64,' . $encodedImageData . '" height=150px>';
                }
                ?>
            </td>
            <td><?php echo $konyv['MUFAJ']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="konyv_id" value="<?php echo $konyv['KONYV_ID']; ?>">
                    <input type="submit" value="Módosítás" name="editBook">
                </form>
                <form method="post" onsubmit="return confirmDelete('<?php echo $konyv['CIM']; ?>')">
                    <input type="hidden" name="konyv_id" value="<?php echo $konyv['KONYV_ID']; ?>">
                    <input type="submit" value="Törlés"  name="deletebook">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<script>
    function confirmDelete(bookName) {
        return confirm("Biztosan törölni szeretné ezt az könyvet: \"" + bookName + "\" ?");
    }
</script>

</body>
</html>
