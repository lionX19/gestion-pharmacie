<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=pharmacy_stock;charset=UTF8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    exit("Oups, un problème s'est produit !");
}
