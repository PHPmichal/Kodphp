<?php


class saveProduct
{
    function __construct() // update foto and data 
    {
        require_once 'bazaDanych.php';
        session_start();
        error_reporting(0);
            if(getimagesize($_FILES['changefoto']['tmp_name']) == FALSE) { // checks if somefthing is
            } else {
                $changefoto = addslashes($_FILES['changefoto']['tmp_name']); 
                $name = addslashes($_FILES['changefoto']['name']);
                $changefoto = file_get_contents($changefoto);
                $changefoto = base64_encode($changefoto);
                $id = $_POST['id'];
                $polaczenie->query("UPDATE equip SET zdjecie='$name',image='$changefoto' WHERE idequip='$id' "); //save only foto if isset
            }
        $product= $_POST['changeprodukt']; // download data from form 
        $producent = $_POST['changeproducent'];
        $szt = $_POST['changesztuk'];
        $rabat = $_POST['changerabat'];
        $waga = $_POST['changewaga'];
        $cena = $_POST['changecena'];
        $id = $_POST['id'];
        $opis = $_POST['changeopis'];
                if ($polaczenie->query("UPDATE equip SET producent='$producent', produkt='$product',cena='$cena',szt='$szt',waga='$waga',rabat='$rabat' WHERE idequip='$id' ")){
                   // save all data like:price , discount ,name and so on
                    $txt = "opis/up$id.html"; 
                    $fb = fopen($txt,'a'); // opet txt document
                    file_put_contents($txt,''); // removal old text from document
                    fwrite($fb,$opis); // creating new data to text document
                    fclose($fb); //closed creating txt document
                    $_SESSION['refresh'] = true;
                }
        mysqli_close($polaczenie);
    }
}
$oopSave = new saveProduct();

