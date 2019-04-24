<?php


class saveProduct
{
   
    function __construct()
    {
        session_start();
            if (getimagesize($_FILES['changefoto']['tmp_name']) == FALSE) {
                echo "Pleace change image";
            } else {
                $changefoto = addslashes($_FILES['changefoto']['tmp_name']);
                $name = addslashes($_FILES['changefoto']['name']);
                $changefoto = file_get_contents($changefoto);
                $changefoto = base64_encode($changefoto);
            }
        $product= $_POST['changeprodukt'];
        $producent = $_POST['changeproducent'];
        $szt = $_POST['changesztuk'];
        $rabat = $_POST['changerabat'];
        $waga = $_POST['changewaga'];
        $cena = $_POST['changecena'];
        $id = $_POST['id'];
        $opis = $_POST['changeopis'];
        require_once 'bazaDanych.php';
                if ($polaczenie->query("UPDATE equip SET producent='$producent', produkt='$product',cena='$cena',szt='$szt',waga='$waga',rabat='$rabat',zdjecie='$name',image='$changefoto' WHERE idequip='$id' ")){

                    $txt = "opis/up$id.html";
                    $fb = fopen($txt,'a');
                    file_put_contents($txt,'');
                    fwrite($fb,$opis);
                    fclose($fb);
                    $_SESSION['refresh'] = true;
                }
        mysqli_close($polaczenie);
    }

}
$oopSave = new saveProduct();
