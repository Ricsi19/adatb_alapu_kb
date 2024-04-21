<?php

////// KÖNYVEKHEZ TARTOZÓ FÜGGVÉNYEK \\\\\\

//id alapjan konyv lekerdezese
function get_konyv($konyv_id) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT * FROM system.konyv WHERE konyv_id = :konyv_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":konyv_id", $konyv_id);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    $konyv = oci_fetch_assoc($result);
    $konyv['BORITO'] = $konyv['BORITO']->load();
    oci_free_statement($result);
    oci_close($c);
    return $konyv;
}

//osszes konyv lekerdezese tombbe rendezve, $sort a rendezes, 0 = A-Z, 1 = Z-A, 2 = ar szerint novekvo, 3 = ar szerint csokkeno, 4 = legujabb, 5 = legregebbi
function get_konyvek_sorted($sort) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "";
    $konyvek;
    if($sort == 0){
        $sql = "SELECT * FROM system.konyv ORDER BY cim ASC";
    }
    elseif($sort == 1){
        $sql = "SELECT * FROM system.konyv ORDER BY cim DESC";
    }
    elseif($sort == 2){
        $sql = "SELECT * FROM system.konyv ORDER BY ar ASC";
    }
    elseif($sort == 3){
        $sql = "SELECT * FROM system.konyv ORDER BY ar DESC";
    }
    elseif($sort == 4){
        $sql = "SELECT * FROM system.konyv ORDER BY kiadas DESC";
    }
    elseif($sort == 5){
        $sql = "SELECT * FROM system.konyv ORDER BY kiadas ASC";
    }
    else{
        die("Hibás lekérdezés!");
    }
    $result = oci_parse($c, $sql);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    while($konyv = oci_fetch_assoc($result)){
        if(!is_null($konyv['BORITO'])){
        $konyv['BORITO'] = $konyv['BORITO']->load();
        }
        $konyvek[] = $konyv;
    }
    oci_free_statement($result);
    oci_close($c);
    return $konyvek;
}

//könyvek keresése cím, szerző, műfaj alapján
function get_konyvek_grouped($cim, $szerzo, $mufaj) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT * FROM system.konyv
            INNER JOIN system.irta ON konyv.konyv_id = irta.konyv_id
            INNER JOIN system.szerzo ON szerzo.szerzo_id = irta.szerzo_id
            WHERE konyv.cim LIKE :cim AND szerzo.nev LIKE :szerzo AND konyv.mufaj LIKE :mufaj";
    $konyvek = [];
    $result = oci_parse($c, $sql);
    $cim_bind = "%" . $cim . "%";
    $szerzo_bind = "%" . strtoupper($szerzo) . "%";
    $mufaj_bind = "%" . $mufaj . "%";
    oci_bind_by_name($result, ":cim", $cim_bind);
    oci_bind_by_name($result, ":szerzo", $szerzo_bind);
    oci_bind_by_name($result, ":mufaj", $mufaj_bind);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    while ($konyv = oci_fetch_assoc($result)) {
        if(!is_null($konyv['BORITO'])){
        $konyv['BORITO'] = $konyv['BORITO']->load();
        }
        $konyvek[] = $konyv;
    }
    oci_free_statement($result);
    oci_close($c);
    return $konyvek;
}

//konyv aruhazi keszletenek lekerdezese
function get_konyv_keszlet($konyv_id){
    $keszletek = [];
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT aruhazak.nev, konyvaruhaz.db FROM system.konyv 
    INNER JOIN system.konyvaruhaz ON konyv.konyv_id = konyvaruhaz.konyv_id
    INNER JOIN system.aruhazak ON aruhazak.aruhaz_id = konyvaruhaz.aruhaz_id
    WHERE konyv.konyv_id = :konyv_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":konyv_id", $konyv_id);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
        while($keszlet = oci_fetch_assoc($result)){
            $keszletek[] = $keszlet;
        }
    oci_free_statement($result);
    oci_close($c);
    return $keszletek;
}

//konyv kiiratasa
function konyv_toString($konyv){
    $szerzok = [];
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT szerzo.nev FROM system.szerzo 
    INNER JOIN system.irta ON irta.szerzo_id = szerzo.szerzo_id
    INNER JOIN system.konyv ON irta.konyv_id = konyv.konyv_id
    WHERE konyv.konyv_id = :konyv_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":konyv_id", $konyv['KONYV_ID']);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    while($szerzo = oci_fetch_assoc($result)){
        $szerzok[] = $szerzo['NEV'];
    }
    $imagedata = base64_encode($konyv['BORITO']);
    echo '<a href="konyv.php?id='.$konyv['KONYV_ID'].'"><div class="grid-item"><p>'. $konyv['CIM'] .'</p><img src="data:image/jpeg;base64,'.$imagedata.'" height=300px><p>'.implode("<br>", $szerzok).'</p><p>'.$konyv['KIADO'].'</p><p>'.$konyv['KIADAS'].'</p>'.$konyv['MUFAJ'].'<p>'.$konyv['AR'].'Ft</p></div></a>';
    oci_free_statement($result);
    oci_close($c);
}

// adott id-hoz tartozó felhasználó által megvett könyvek száma (a számmal tér vissza, nem tömbbel)
function get_purchased_books($id) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT SUM(system.rendelestartalom.db) AS db FROM system.felhasznalok
            INNER JOIN system.rendeles ON felhasznalok.user_id = rendeles.user_id
            INNER JOIN system.rendelestartalom ON rendeles.rendeles_id = rendelestartalom.rendeles_id
            WHERE felhasznalok.user_id = :user_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":user_id", $id);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    $db = oci_fetch_assoc($result);
    oci_free_statement($result);
    oci_close($c);
    return $db["DB"];
}

// könyvek listázása
function listKonyv() {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'SELECT * FROM system.konyv ORDER BY konyv_id ASC');
    oci_execute($stmt);

    $konyv_data = array();
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $konyv_data[] = $row;
    }
    oci_free_statement($stmt);
    oci_close($c);
    return $konyv_data;
}

// Uj konyv felvitele adatbazisba minden adataval es szerzo_id-javal az irta tablat is kitolti
// Nem checkel nev ismetlest, mert tobb konyvnek is lehet ugyanaz a neve
function addBook($title, $publisher, $price, $date, $cover_art, $genre, $szerzo_id) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'select * from system.konyv');
    oci_execute($stmt, OCI_DEFAULT);


    oci_free_statement($stmt);
    $maxid = oci_parse($c, 'SELECT MAX(konyv_id) FROM system.konyv');
    oci_execute($maxid, OCI_DEFAULT);
    $maxid_d = oci_fetch_array($maxid);
    oci_free_statement($maxid);
    $book_id = end($maxid_d)+1;


    $newK = oci_parse($c, 'INSERT INTO system.konyv(konyv_id, cim, kiado, ar, kiadas, borito, mufaj) 
                             VALUES(:konyv_id_bv, :cim_bv, :kiado_bv, :ar_bv, :kiadas_bv, NULL, :mufaj_bv)');
    oci_bind_by_name($newK, ':konyv_id_bv', $book_id);
    oci_bind_by_name($newK, ':cim_bv', $title);
    oci_bind_by_name($newK, ':kiado_bv', $publisher);
    oci_bind_by_name($newK, ':ar_bv', $price);
    oci_bind_by_name($newK, ':kiadas_bv', $date);
    oci_bind_by_name($newK, ':mufaj_bv', $genre);
    oci_execute($newK, OCI_DEFAULT);
    oci_commit($c);
    oci_free_statement($newK);


    $sql = "UPDATE system.konyv SET borito = empty_blob() WHERE konyv_id = $book_id RETURNING borito INTO :borito";
    $result = oci_parse($c, $sql);
    $blob = oci_new_descriptor($c, OCI_D_LOB);
    oci_bind_by_name($result, ":borito", $blob, -1, OCI_B_BLOB);
    oci_execute($result, OCI_DEFAULT) or die ("Unable to execute query");
    if(!$blob->save($cover_art)) {
        oci_rollback($c);
    }
    else {
        oci_commit($c);
    }
    oci_free_statement($result);
    $blob->free();

    $newConnection = oci_parse($c, 'INSERT INTO system.irta(konyv_id, szerzo_id) VALUES(:konyv_id_bv, :szerzo_id_bv)');
    oci_bind_by_name($newConnection, ':konyv_id_bv', $book_id);
    oci_bind_by_name($newConnection, ':szerzo_id_bv', $szerzo_id);
    oci_execute($newConnection, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($newConnection);

    oci_close($c);
    return "Sikeres könyv felvitel!";
}

// könyv szerkesztése
function editBook($book_id, $title, $publisher, $price, $date, $cover_art, $genre, $szerzo_id, $isConnected) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'select * from system.konyv');
    oci_execute($stmt, OCI_DEFAULT);

    if($cover_art==null){
        $editK = oci_parse($c, 'UPDATE system.konyv SET cim = :cim_bv, kiado = :kiado_bv, ar = :ar_bv, kiadas = :kiadas_bv, mufaj = :mufaj_bv WHERE konyv_id = :konyv_id_bv');
        oci_bind_by_name($editK, ':cim_bv', $title);
        oci_bind_by_name($editK, ':kiado_bv', $publisher);
        oci_bind_by_name($editK, ':ar_bv', $price);
        oci_bind_by_name($editK, ':kiadas_bv', $date);
        oci_bind_by_name($editK, ':mufaj_bv', $genre);
        oci_bind_by_name($editK, ':konyv_id_bv', $book_id);
        oci_execute($editK, OCI_COMMIT_ON_SUCCESS);
        oci_commit($c);
        oci_free_statement($editK);
    } else {
        $editK = oci_parse($c, 'UPDATE system.konyv SET cim = :cim_bv, kiado = :kiado_bv, ar = :ar_bv, kiadas = :kiadas_bv, borito = empty_blob(), mufaj = :mufaj_bv WHERE konyv_id = :konyv_id_bv');
        oci_bind_by_name($editK, ':cim_bv', $title);
        oci_bind_by_name($editK, ':kiado_bv', $publisher);
        oci_bind_by_name($editK, ':ar_bv', $price);
        oci_bind_by_name($editK, ':kiadas_bv', $date);
        oci_bind_by_name($editK, ':mufaj_bv', $genre);
        oci_bind_by_name($editK, ':konyv_id_bv', $book_id);
        oci_execute($editK, OCI_COMMIT_ON_SUCCESS);
        oci_commit($c);
        oci_free_statement($editK);

        // Now handle the blob update
        $sql = "UPDATE system.konyv SET borito = empty_blob() WHERE konyv_id = $book_id RETURNING borito INTO :borito";
        $result = oci_parse($c, $sql);
        $blob = oci_new_descriptor($c, OCI_D_LOB);
        oci_bind_by_name($result, ":borito", $blob, -1, OCI_B_BLOB);
        oci_execute($result, OCI_DEFAULT) or die ("Unable to execute query");
        if(!$blob->save($cover_art)) {
            oci_rollback($c);
        }
        else {
            oci_commit($c);
        }
        oci_free_statement($result);
        $blob->free();
    }



    if($isConnected){
        //return "UPDATE";
        $sqlcommand = "UPDATE system.irta SET SZERZO_ID = :szerzo_id_bv WHERE KONYV_ID = :konyv_id_bv";
        $editConnection = oci_parse($c, $sqlcommand);
        oci_bind_by_name($editConnection, ':konyv_id_bv', $book_id);
        oci_bind_by_name($editConnection, ':szerzo_id_bv', $szerzo_id);
        oci_execute($editConnection, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($editConnection);

        oci_close($c);
    }else{
        //return "INSERT";
        $newConnection = oci_parse($c, 'INSERT INTO system.irta(konyv_id, szerzo_id) VALUES(:konyv_id_bv, :szerzo_id_bv)');
        oci_bind_by_name($newConnection, ':konyv_id_bv', $book_id);
        oci_bind_by_name($newConnection, ':szerzo_id_bv', $szerzo_id);
        oci_execute($newConnection, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($newConnection);

        oci_close($c);
    }

    return true;
}

// könyv törlése
function deleteBook($konyv_id){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'DELETE FROM system.konyv WHERE konyv_id = :konyv_id_bv');
    oci_bind_by_name($stmt, ':konyv_id_bv', $konyv_id);
    oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($stmt);
    oci_close($c);
    return("Sikeres törlés!");
}

////// SZEZŐKHÖZ TARTOZÓ FÜGGVÉNYEK \\\\\\

// szerzok kilistazasa
function listSzerzo() {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'SELECT * FROM system.szerzo ORDER BY szerzo_id ASC');
    oci_execute($stmt);
    $szerzo_data = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $szerzo_data[] = $row;
    }
    oci_free_statement($stmt);
    oci_close($c);
    return $szerzo_data;
}

// szerző hozzáadása adatbázishoz
function addSzerzo($szerzo_nev){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'select * from system.szerzo where nev = :nev');
    oci_bind_by_name($stmt, ':nev', $szerzo_nev,-1);
    oci_execute($stmt, OCI_DEFAULT);
    $check = oci_fetch_array($stmt, OCI_ASSOC);

    if($check==null){
        oci_free_statement($stmt);
        $maxid = oci_parse($c, 'SELECT MAX(szerzo_id) FROM system.szerzo');
        oci_execute($maxid, OCI_DEFAULT);
        $maxid_d = oci_fetch_array($maxid);
        oci_free_statement($maxid);
        $rlymaxid = end($maxid_d)+1;
        $newsz = oci_parse($c, 'INSERT INTO SYSTEM.SZERZO(szerzo_id, nev)
        VALUES(:szerzo_id_bv, :szerzo_nev_bv)');
        oci_bind_by_name($newsz, ':szerzo_id_bv', $rlymaxid);
        $szerzo_nev_uppercase = strtoupper($szerzo_nev);
        oci_bind_by_name($newsz, ":szerzo_nev_bv", $szerzo_nev_uppercase);
        oci_execute($newsz, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($newsz);
        return "Sikeres szerző felvitel!";
    }
    elseif($check != null){
        return "Ez a szerző már szerepel a listában!";
    }
    else{
        return "Ez az e-mail cím már használatban van!";
    }
    oci_close($c);
}

// szerző törlése
function deleteSzerzo($szerzo_id){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'DELETE FROM system.szerzo WHERE szerzo_id = :szerzo_id');
    oci_bind_by_name($stmt, ':szerzo_id', $szerzo_id);
    oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($stmt);
    oci_close($c);
}

// szerző szerkesztése
function editSzerzo($szerzo_id, $szerzo_ujnev){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $szerzo_ujnev_uppercase = strtoupper($szerzo_ujnev);
    $stmt = oci_parse($c, 'UPDATE system.szerzo SET nev = :szerzo_ujnev WHERE szerzo_id = :szerzo_id');
    oci_bind_by_name($stmt, ':szerzo_ujnev', $szerzo_ujnev_uppercase);
    oci_bind_by_name($stmt, ':szerzo_id', $szerzo_id);
    oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($stmt);
    oci_close($c);
    return "Sikeres név változtatás!";
}

////// KOSÁRHOZ TARTOZÓ FÜGGVÉNYEK \\\\\\

//Felhasznalo kosar tartalmanak lekerdezese
function get_kosar($user_id){
    $tartalom = [];
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT * FROM system.kosar WHERE user_id = :user_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":user_id", $user_id);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    while($inf = oci_fetch_assoc($result)){
        $tartalom[] = $inf;
    }
    oci_free_statement($result);
    oci_close($c);
    return $tartalom;
}
//kosar kiuritese rendeles utan
function kosar_urit($user_id){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "DELETE FROM system.kosar WHERE user_id = :user_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":user_id", $user_id);
    oci_execute($result, OCI_COMMIT_ON_SUCCESS) or die ("Sikertelen lekérdezés");
    oci_free_statement($result);
    oci_close($c);
}

//konyv kosarba helyezese
function kosarba_helyez($konyv_id, $db, $user_id){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $tmp = get_konyv_keszlet($konyv_id);
    $raktar = 0;
    for($i = 0; $i < count($tmp);$i++){
        $raktar += $tmp[$i]['DB'];
    }
    if($raktar < $db){
        oci_close($c);
        return "Nincs elég raktáron!";
    }
    else{
        // IDE KELL MAJD EGY TRIGGER
        $sql2 = "INSERT INTO system.kosar VALUES(:user_id, :konyv_id, :db)";
        $result = oci_parse($c, $sql2);
        oci_bind_by_name($result, ":user_id", $user_id);
        oci_bind_by_name($result, ":konyv_id", $konyv_id);
        oci_bind_by_name($result, ":db", $db);
        oci_execute($result, OCI_COMMIT_ON_SUCCESS) or die('Sikertelen lekérdezés');
        oci_free_statement($result);
        oci_close($c);
        return "Sikeresen a kosárba téve!";
    }
}

////// RENDELÉSHEZ TARTOZÓ FÜGGVÉNYEK \\\\\\

// rendelés készítése
function rendeles_keszit($user_id){

    $kosar = get_kosar($user_id);
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT MAX(rendeles_id) FROM system.rendeles";
    $result = oci_parse($c, $sql);
    oci_execute($result, OCI_COMMIT_ON_SUCCESS) or die ("Sikertelen lekérdezés");
    $tmp = oci_fetch_assoc($result);
    $maxr_id = $tmp['MAX(RENDELES_ID)']+1;
    oci_free_statement($result);

    $sql1 = "INSERT INTO system.rendeles (user_id, rendeles_id) VALUES(:user_id, :rendeles_id)";
    $result = oci_parse($c, $sql1);
    oci_bind_by_name($result, ":user_id", $user_id);
    oci_bind_by_name($result, ":rendeles_id", $maxr_id);
    oci_execute($result, OCI_COMMIT_ON_SUCCESS) or die ("Sikertelen lekérdezés");
    oci_free_statement($result);


    $sql2 = "INSERT INTO system.rendelestartalom VALUES(:rendeles_id, :konyv_id, :db)";
    $sql3 = "SELECT * FROM (SELECT * FROM system.konyvaruhaz WHERE konyv_id = :konyv_id ORDER BY db DESC) WHERE ROWNUM = 1";
    $sql4 = "UPDATE system.konyvaruhaz SET db = :db WHERE konyv_id = :konyv_id AND aruhaz_id = :aruhaz_id";
    $result = oci_parse($c, $sql2);
    $aruhaz_result = oci_parse($c, $sql3);
    $aruhaz_update = oci_parse($c, $sql4);
    for($i = 0; $i < count($kosar); $i++){
        oci_bind_by_name($aruhaz_result, ":konyv_id", $kosar[$i]["KONYV_ID"]);
        oci_execute($aruhaz_result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
        $aruhaz = oci_fetch_assoc($aruhaz_result);

        $newdb = $aruhaz["DB"] - $kosar[$i]["DB"];
        oci_bind_by_name($aruhaz_update, ":konyv_id", $kosar[$i]["KONYV_ID"]);
        oci_bind_by_name($aruhaz_update, ":aruhaz_id", $aruhaz["ARUHAZ_ID"]);
        oci_bind_by_name($aruhaz_update, ":db", $newdb);
        oci_execute($aruhaz_update, OCI_COMMIT_ON_SUCCESS) or die ("Sikertelen lekérdezés");

        oci_bind_by_name($result, ":rendeles_id", $maxr_id);
        oci_bind_by_name($result, ":konyv_id", $kosar[$i]['KONYV_ID']);
        oci_bind_by_name($result, ":db", $kosar[$i]['DB']);
        oci_execute($result, OCI_COMMIT_ON_SUCCESS) or die ("Sikertelen lekérdezés");
    }
    oci_free_statement($aruhaz_result);
    oci_free_statement($aruhaz_update);
    oci_free_statement($result);
    oci_close($c);
}

// rendelés lekérdezése felhasználó id szerint
function get_rendeles_by_user_id($id) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT rendeles.rendeles_id, konyv.cim, rendelestartalom.db, konyv.ar * rendelestartalom.db AS ar,
             SUM(konyv.ar * rendelestartalom.db) OVER (PARTITION BY rendeles.rendeles_id) AS total
             FROM system.rendeles
             INNER JOIN system.rendelestartalom ON rendeles.rendeles_id = rendelestartalom.rendeles_id
             INNER JOIN system.konyv ON rendelestartalom.konyv_id = konyv.konyv_id
             WHERE rendeles.user_id = :user_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":user_id", $id);
    oci_execute($result, OCI_DEFAULT) or die("Sikertelen lekérdezés");
    $records = [];
    while ($record = oci_fetch_assoc($result)) {
        $records[] = $record;
    }
    oci_free_statement($result);
    oci_close($c);
    return $records;
}

////// REGISZTRÁCIÓS FÜGGVÉNYEK \\\\\\

//uj user felvitele adatbazisba regisztracional
function regist_user($username, $location, $email, $mobilphone, $password){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'select * from system.felhasznalok where username = :username');
    $stmt2 = oci_parse($c, 'select * from system.felhasznalok where email = :email');
    oci_bind_by_name($stmt, ':username', $username,-1);
    oci_bind_by_name($stmt2, ':email', $email,-1);
    oci_execute($stmt, OCI_DEFAULT);
    oci_execute($stmt2, OCI_DEFAULT);
    $check = oci_fetch_array($stmt, OCI_ASSOC);
    $check2 = oci_fetch_array($stmt2, OCI_ASSOC);
    if($check == null && $check2 == null){
        oci_free_statement($stmt);
        oci_free_statement($stmt2);
        $maxid = oci_parse($c, 'SELECT MAX(user_id) FROM system.Felhasznalok');
        oci_execute($maxid, OCI_DEFAULT);
        $maxid_d = oci_fetch_array($maxid);
        oci_free_statement($maxid);
        $rlymaxid = end($maxid_d)+1;
        $encpass = password_hash($password, PASSWORD_DEFAULT);
        $newu = oci_parse($c, 'INSERT INTO SYSTEM.FELHASZNALOK(user_id, username, jelszo, lakcim, email, telefonszam, admin, torzsvasarlo)
        VALUES(:user_id_bv, :username_bv, :password_bv, :location_bv, :email_bv, :mobilphone_bv, 0,0)');
        oci_bind_by_name($newu, ':user_id_bv', $rlymaxid);
        oci_bind_by_name($newu, ":username_bv", $username);
        oci_bind_by_name($newu, ":password_bv", $encpass);
        oci_bind_by_name($newu, ":location_bv", $location);
        oci_bind_by_name($newu, ":email_bv", $email);
        oci_bind_by_name($newu, ":mobilphone_bv", $mobilphone);
        oci_execute($newu, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($newu);
        return "Sikeres regisztráció!";
      }
      elseif($check != null){
        return "Ez a felhasználónév már foglalt!";
      }
      else{
        return "Ez az e-mail cím már használatban van!";
      }
      oci_close($c);
}


//regisztráció ellenőrzése
function regisztracio_ellenorzes($username, $location, $email, $mobilphone, $password, $password2) {
    $hibak = 0;
    $uzenetek = [];
    //Ertelemszeruen ahogy latszik a kodbol, inputokat ellenorzunk
    if ($username === null || trim($_POST["username"]) === ""){
        $uzenetek[] = "Felhasználónév megadása kötelező!";
        $hibak++;
    }
    if ($location === null || trim($_POST["location"]) === ""){
        $uzenetek[] = "Lakcím megadása kötelező!";
        $hibak++;
    }
    if ($email === null || trim($_POST["email"]) === ""){
        $uzenetek[] = "E-mail megadása kötelező!";
        $hibak++;
    }
    if ($mobilphone === null || trim($_POST["mobilphone"]) === ""){
        $uzenetek[] = "Telefonszám megadása kötelező!";
        $hibak++;
    }
    if ($password === null || trim($_POST["password"]) === "" || $password2 === null || trim($_POST["password2"]) === ""){
        $uzenetek[] = "Jelszó és ellenőrző jelszó megadása kötelező!";
        $hibak++;
    }
    //egyeb hibak kezelese(kriteriumoknak nem megfelelo hibak)
    if (strlen($password) < 5){
        $uzenetek[] = "Minimum 5 karakter kell legyen a jelszó!";
        $hibak++;
    }
    if ($password !== $password2){
        $uzenetek[] = "Nem egyezik a két jelszó!";
        $hibak++;
    }
    if($hibak == 0){
        $uzenetek[] = regist_user($username, $location, $email, $mobilphone, $password);
    }
    return $uzenetek;
}

////// FELHASZNÁLÓHOZ TARTOZÓ FÜGGVÉNYEK \\\\\\

function get_users() {
    $users = [];
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT * FROM system.felhasznalok ORDER BY user_id ASC";
    $result = oci_parse($c, $sql);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    while($user = oci_fetch_assoc($result)) {
        $users[] = $user;
    }
    oci_free_statement($result);
    oci_close($c);
    return $users;
}

// felhasználó lekérdezése id alapján
function get_user_by_id($id) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT * FROM system.felhasznalok WHERE user_id = :user_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":user_id", $id);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    $user = oci_fetch_assoc($result);
    oci_free_statement($result);
    oci_close($c);
    return $user;
}

//felhasználó törlése id alapján
function delete_user($user_id) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "DELETE FROM system.felhasznalok WHERE user_id = :user_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":user_id", $user_id);
    oci_execute($result, OCI_COMMIT_ON_SUCCESS) or die ("Sikertelen törlés");
    oci_free_statement($result);
    oci_close($c);
}

// adatok szerkesztésénél megadott inputok ellenőrzése, ha minden jó módosítjuk is
function profil_szerkesztes_ellenorzes($id, $username, $email, $mobilphone, $location, $password, $password2) {
    $hibak = 0;
    $uzenetek = [];
    if ($username === null || trim($username) === ""){
        $uzenetek[] = "A felhasználónevet nem lehet üresen hagyni!";
        $hibak++;
    }
    if ($location === null || trim($location) === ""){
        $uzenetek[] = "A lakcímet nem lehet üresen hagyni!";
        $hibak++;
    }
    if ($email === null || trim($email) === ""){
        $uzenetek[] = "Az E-mail címet nem lehet üresen hagyni!";
        $hibak++;
    }
    if ($mobilphone === null || trim($mobilphone) === ""){
        $uzenetek[] = "A telefonszámot nem lehet üresen hagyni!";
        $hibak++;
    }
    if (($password !== null && trim($password) !== "" && ($password2 === null || trim($password2) === ""))
        || (($password === null || trim($password) === "") && $password2 !== null && trim($password2) !== "")) {
        $uzenetek[] = "A jelszó megváltoztatásához mindkét mezőt ki kell tölteni!";
        $hibak++;
    }
    if ($password !== null && trim($password) !== "" && $password2 !== null && trim($password2) !== "") {
        if (strlen($password) < 5) {
            $uzenetek[] = "Minimum 5 karakter kell legyen a jelszó!";
            $hibak++;
        }
        if ($password !== $password2) {
            $uzenetek[] = "Nem egyezik a két jelszó!";
            $hibak++;
        }
        if ($hibak == 0) {
            edit_user_pw($id, $password);
        }
    }
    if($hibak == 0){
        $uzenetek[] = edit_user_no_pw($id, $username, $location, $email, $mobilphone);
    }
    return $uzenetek;
}

// felhasználó adatainak módosítása a jelszó kivételével
function edit_user_no_pw($id, $username, $location, $email, $mobilphone) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, "SELECT * FROM system.felhasznalok WHERE username = :username AND user_id != :user_id");
    $stmt2 = oci_parse($c, "SELECT * FROM system.felhasznalok WHERE email = :email AND user_id != :user_id");
    oci_bind_by_name($stmt, ":username", $username);
    oci_bind_by_name($stmt, ":user_id", $id);
    oci_bind_by_name($stmt2, ":email", $email);
    oci_bind_by_name($stmt2, ":user_id", $id);
    oci_execute($stmt, OCI_DEFAULT);
    oci_execute($stmt2, OCI_DEFAULT);
    $check = oci_fetch_assoc($stmt);
    $check2 = oci_fetch_assoc($stmt2);
    if ($check == null && $check2 == null) {
        oci_free_statement($stmt);
        oci_free_statement($stmt2);
        $sql = "UPDATE system.felhasznalok SET
                username = :username, lakcim = :lakcim, email = :email, telefonszam = :telefonszam
                WHERE user_id = :user_id";
        $result = oci_parse($c, $sql);
        oci_bind_by_name($result, ":user_id", $id);
        oci_bind_by_name($result, ":username", $username);
        oci_bind_by_name($result, ":lakcim", $location);
        oci_bind_by_name($result, ":email", $email);
        oci_bind_by_name($result, ":telefonszam", $mobilphone);
        oci_execute($result, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($result);
        oci_close($c);
        if (!$_SESSION["admin"]) {
            header("Location: profile.php");
        }
    } elseif ($check != null) {
        oci_close($c);
        return "Ez a felhasználónév már foglalt!";
    } else {
        oci_close($c);
        return "EZ az e-mail cím már foglalt!";
    }
}

// felhasználó jelszavának módosítása
function edit_user_pw($id, $password) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $jelszo = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE system.felhasznalok SET jelszo = :jelszo WHERE user_id = :user_id";
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":jelszo", $jelszo);
    oci_bind_by_name($result, ":user_id", $id);
    oci_execute($result, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($result);
    oci_close($c);
}

// felhasználó admin\törzsvásárlói státuszának módosítása
function edit_user_privileges($id, $admin, $torzsvasarlo) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "UPDATE system.felhasznalok SET admin = :admin, torzsvasarlo = :torzsvasarlo WHERE user_id = :user_id";
    $admine = ($admin === "yes") ? 1 : 0;
    $torzsvasarloe = ($torzsvasarlo === "yes") ? 1 : 0;
    $result = oci_parse($c, $sql);
    oci_bind_by_name($result, ":admin", $admine);
    oci_bind_by_name($result, ":torzsvasarlo", $torzsvasarloe);
    oci_bind_by_name($result, ":user_id", $id);
    oci_execute($result, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($result);
    oci_close($c);
    header("Location: admin_felhasznalo_mod.php");
}

////// ÁRUHÁZAKHOZ TARTOZÓ FÜGGVÉNYEK \\\\\\

// áruház hozzáadása adatbázishoz
function addShop($shopname, $location) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'select * from system.aruhazak');
    oci_execute($stmt, OCI_DEFAULT);


    oci_free_statement($stmt);
    $maxid = oci_parse($c, 'SELECT MAX(aruhaz_id) FROM system.aruhazak');
    oci_execute($maxid, OCI_DEFAULT);
    $maxid_d = oci_fetch_array($maxid);
    oci_free_statement($maxid);
    $aruhaz_id = end($maxid_d)+1;


    $newA = oci_parse($c, 'INSERT INTO system.aruhazak(aruhaz_id, nev, cim) 
                             VALUES(:aruhaz_id_bv, :nev_bv, :cim_bv)');
    oci_bind_by_name($newA, ':aruhaz_id_bv', $aruhaz_id);
    oci_bind_by_name($newA, ':nev_bv', $shopname);
    oci_bind_by_name($newA, ':cim_bv', $location);

    oci_execute($newA, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($newA);

    oci_close($c);
    return "Sikeres áruház felvétel!";
}

// áruház törlése
function deleteAruhaz($aruhaz_id){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'DELETE FROM system.aruhazak WHERE aruhaz_id = :aruhaz_id');
    oci_bind_by_name($stmt, ':aruhaz_id', $aruhaz_id);
    oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($stmt);
    oci_close($c);
}

// áruház szerkesztése
function editShop($shopID, $nev, $cim){
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");

    $stmt = oci_parse($c, 'UPDATE system.aruhazak SET nev = :uj_nev, cim = :uj_cim WHERE aruhaz_id = :aruhaz_id');
    oci_bind_by_name($stmt, ':uj_nev', $nev);
    oci_bind_by_name($stmt, ':uj_cim', $cim);
    oci_bind_by_name($stmt, ':aruhaz_id', $shopID);
    oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($stmt);
    oci_close($c);

    return "Sikeres adat változtatás!";
}

// áruház hozzáadásánál/szerkesztésénél a form validálása
function validateShop($shopname, $location, &$uzenetek) {
    $hibak=0;
    if (!isset($shopname) || trim($shopname) === ""){
        $uzenetek[] = "Az áruház neve nem lehet üres";
        $hibak++;
    }

    if (!isset($location) || trim($location) === ""){
        $uzenetek[] = "Az áruház címe nem lehet üres!";
        $hibak++;
    }
    return $hibak;
}

// áruházak készletének módosítása
function modify_konyvaruhaz_db($konyv_id, $aruhaz_id, $db) {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");

    // Ellenőrizzük, hogy létezik-e az adott rekord az adatbázisban
    $sql = "SELECT COUNT(*) AS row_count FROM system.konyvaruhaz WHERE konyv_id = :book_id_bv AND aruhaz_id = :aruhaz_id_bv";
    $stmt = oci_parse($c, $sql);
    oci_bind_by_name($stmt, ':book_id_bv', $konyv_id);
    oci_bind_by_name($stmt, ':aruhaz_id_bv', $aruhaz_id);
    oci_execute($stmt, OCI_DEFAULT);

    $row = oci_fetch_assoc($stmt);
    $row_count = $row['ROW_COUNT'];
    oci_free_statement($stmt);

    // Ha a rekord létezik, akkor végrehajtjuk a módosítást, egyébként beszúrjuk az új rekordot, ha nem negatív db-ot akarunk megadni
    if ($row_count > 0) {
        //megnézzük a jelenlegi db-ot
        $sql = "SELECT db FROM system.konyvaruhaz WHERE konyv_id = :konyv_id AND aruhaz_id = :aruhaz_id";
        $stmt = oci_parse($c, $sql);
        oci_bind_by_name($stmt, ":konyv_id", $konyv_id);
        oci_bind_by_name($stmt, ":aruhaz_id", $aruhaz_id);
        oci_execute($stmt, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
        $result = oci_fetch_assoc($stmt);
        $old_db = $result["DB"];
        $new_db = $old_db + $db;
        oci_free_statement($stmt);

        //ha az új db pozitív, akkor updateljük az adatbázist
        if($new_db>=0){
            $sql = "UPDATE system.konyvaruhaz SET db = :db WHERE konyv_id = :konyv_id AND aruhaz_id = :aruhaz_id";
            $stmt = oci_parse($c, $sql);
            oci_bind_by_name($stmt, ':db', $new_db);
            oci_bind_by_name($stmt, ':konyv_id', $konyv_id);
            oci_bind_by_name($stmt, ':aruhaz_id', $aruhaz_id);
            oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($stmt);
        }else{
            oci_close($c);
            return "Az új db érték nem lehet negatív.";
        }
    } else if($db > 0){
        $sql = "INSERT INTO system.konyvaruhaz(konyv_id, aruhaz_id, db) VALUES (:konyv_id, :aruhaz_id, :db)";
        $stmt = oci_parse($c, $sql);
        oci_bind_by_name($stmt, ':konyv_id', $konyv_id);
        oci_bind_by_name($stmt, ':aruhaz_id', $aruhaz_id);
        oci_bind_by_name($stmt, ':db', $db);
        oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($stmt);
    }

    oci_close($c);
}


// áruházak listázása
function listShop() {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $stmt = oci_parse($c, 'SELECT * FROM system.aruhazak ORDER BY aruhaz_id ASC');
    oci_execute($stmt);

    $aruhaz_data= [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $aruhaz_data[] = $row;
    }
    oci_free_statement($stmt);
    oci_close($c);
    return $aruhaz_data;
}

/////// EGYÉB FÜGGVÉNYEK \\\\\\\

//műfajok lekérdezése legördülő menühöz
function get_mufajok() {
    $c = oci_pconnect("localuser", "admin123", "//localhost:1521/XEPDB1", "UTF8");
    $sql = "SELECT mufaj FROM system.konyv GROUP BY mufaj ORDER BY mufaj ASC";
    $result = oci_parse($c, $sql);
    oci_execute($result, OCI_DEFAULT) or die ("Sikertelen lekérdezés");
    while($mufaj = oci_fetch_assoc($result)) {
        echo "<option value='" . $mufaj["MUFAJ"] . "'>" . $mufaj["MUFAJ"] . "</option>";
    }
    oci_free_statement($result);
    oci_close($c);
}

// törzsvásárlóvá váláshoz hány könyvet kell még venni
function get_books_remaining($db) {
    $remaining = 30 - $db;
    return "Még " . $remaining . " könyvet kell rendelnie a törzsvásárlóvá váláshoz.";
}

