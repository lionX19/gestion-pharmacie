<?php
session_start();
require_once("../db/db.php");

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_medicament = (int)$_GET['id'];

// Vérifier si le médicament existe
$query = $pdo->prepare("SELECT id_medicament FROM medicaments WHERE id_medicament = ?");
$query->execute([$id_medicament]);
if ($query->rowCount() == 0) {
    header("Location: index.php");
    exit;
}

// Supprimer le médicament
$req = $pdo->prepare("DELETE FROM medicaments WHERE id_medicament = ?");
$req->execute([$id_medicament]);

// Redirection vers la liste
header("Location: index.php");
exit;
