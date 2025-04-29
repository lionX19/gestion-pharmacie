<?php
require_once('../db/db.php');

if (isset($_POST["btnSubmit"])) {
    // Vérification des champs obligatoires
    if (
        !empty($_POST["nom"]) && !empty($_POST["dosage"]) && !empty($_POST["quantite"]) &&
        !empty($_POST["seuil_minimum"]) && !empty($_POST["date_expiration"])
    ) {

        // Récupération et nettoyage des données
        $nom = trim(htmlspecialchars($_POST["nom"]));
        $dosage = trim(htmlspecialchars($_POST["dosage"]));
        $quantite = (int)$_POST["quantite"];
        $seuil_minimum = (int)$_POST["seuil_minimum"];
        $date_expiration = trim(htmlspecialchars($_POST["date_expiration"]));

        // Validation des champs
        if (strlen($nom) >= 3 && strlen($nom) <= 255) {
            if (strlen($dosage) >= 1 && strlen($dosage) <= 100) {
                if ($quantite >= 0) {
                    if ($seuil_minimum >= 0) {
                        // Validation de la date d'expiration (doit être future ou aujourd'hui)
                        $today = date('Y-m-d');
                        if ($date_expiration >= $today) {
                            // Vérification si le médicament existe déjà (nom + dosage unique)
                            $query = $pdo->prepare("SELECT id_medicament FROM medicaments WHERE nom = ? AND dosage = ?");
                            $query->execute([$nom, $dosage]);
                            if ($query->rowCount() == 0) {
                                // Insertion dans la base de données
                                $req = $pdo->prepare("INSERT INTO medicaments (nom, dosage, quantite, seuil_minimum, date_expiration, created_at) 
                                                      VALUES (?, ?, ?, ?, ?, NOW())");
                                $req->execute([$nom, $dosage, $quantite, $seuil_minimum, $date_expiration]);

                                // Redirection vers la liste des médicaments
                                header("Location: index.php");
                                exit;
                            } else {
                                $error = "Ce médicament (nom et dosage) existe déjà.";
                            }
                        } else {
                            $error = "La date d'expiration doit être aujourd'hui ou dans le futur.";
                        }
                    } else {
                        $error = "Le seuil minimum doit être supérieur ou égal à 0.";
                    }
                } else {
                    $error = "La quantité doit être supérieure ou égale à 0.";
                }
            } else {
                $error = "Le dosage doit contenir entre 1 et 100 caractères.";
            }
        } else {
            $error = "Le nom doit contenir entre 3 et 255 caractères.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
