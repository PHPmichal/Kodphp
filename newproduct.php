<?php


class newproduct
{

    function __construct()
    {   session_start();
        require_once 'bazaDanych.php';
        if(isset($_POST['addcena'])){
            if(getimagesize($_FILES['addfoto']['tmp_name']) == FALSE){
                echo "Pleace select any foto";
            }else{
                $addfoto = addslashes($_FILES['addfoto']['tmp_name']);
                $name = addslashes($_FILES['addfoto']['name']);
                $addfoto = file_get_contents($addfoto);
                $addfoto = base64_encode($addfoto);
               // saveimage($name,$addfoto);
            }

            $addprodukt = $_POST['addprodukt'];
            $addproducent = $_POST['addproducent'];
            $addsztuk = $_POST['addsztuk'];
            $addrabat = $_POST['addrabat'];
            $addcena = $_POST['addcena'];
            $addwaga = $_POST['addwaga']; //add to html
            $kod = $_POST['kod'];
            $addopis = $_POST['addopis'];
            $cenapierwotna = $addcena %- $addrabat;

            if($polaczenie->query("INSERT INTO equip VALUE(NULL,'$addprodukt','$addcena','$addwaga','$addsztuk','$addproducent','$addrabat','$name','$addfoto','$cenapierwotna','$kod')")){

                if($rezultat = $polaczenie->query("SELECT idequip FROM equip ORDER BY idequip desc ")){

                    $wiersz = $rezultat->fetch_assoc();
                    $idequip = $wiersz['idequip'];
                    $addtxt = "opis/up$idequip.html";
                    $fb = fopen($addtxt,'a');
                    fwrite($fb,$addopis);
                    fclose($fb);
                    $_SESSION['refresh'] = true;
                }
            }
        }
        mysqli_close($polaczenie);
    }
}
$oop = new newproduct();
