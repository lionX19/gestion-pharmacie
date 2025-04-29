<?php
session_start();
require_once("../db/db.php");

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_medicament = (int)$_GET['id'];

// Récupérer les données du médicament
$query = $pdo->prepare("SELECT * FROM medicaments WHERE id_medicament = ?");
$query->execute([$id_medicament]);
$medicament = $query->fetch();

if (!$medicament) {
    header("Location: index.php");
    exit;
}

// Traitement du formulaire
if (isset($_POST["btnSubmit"])) {
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
                        $today = date('Y-m-d');
                        if ($date_expiration >= $today) {
                            // Vérifier l'unicité (nom + dosage, sauf pour le médicament actuel)
                            $query = $pdo->prepare("SELECT id_medicament FROM medicaments WHERE nom = ? AND dosage = ? AND id_medicament != ?");
                            $query->execute([$nom, $dosage, $id_medicament]);
                            if ($query->rowCount() == 0) {
                                // Mise à jour dans la base de données
                                $req = $pdo->prepare("UPDATE medicaments SET nom = ?, dosage = ?, quantite = ?, seuil_minimum = ?, date_expiration = ? WHERE id_medicament = ?");
                                $req->execute([$nom, $dosage, $quantite, $seuil_minimum, $date_expiration, $id_medicament]);

                                // Redirection vers la liste
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
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Médicament</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <div class="container">
        <h1 class="text-center my-4">Modifier un Médicament</h1>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="" method="post" class="col-md-6 mx-auto">
            <div class="form-group">
                <label for="nom" class="form-label required">Nom</label>
                <input type="text" name="nom" class="form-control" id="nom" required
                    value="<?= htmlspecialchars($medicament->nom) ?>">
            </div>
            <div class="form-group">
                <label for="dosage" class="form-label required">Dosage</label>
                <input type="text" name="dosage" class="form-control" id="dosage" required
                    value="<?= htmlspecialchars($medicament->dosage) ?>">
            </div>
            <div class="form-group">
                <label for="quantite" class="form-label required">Quantité</label>
                <input type="number" name="quantite" class="form-control" id="quantite" min="0" required
                    value="<?= htmlspecialchars($medicament->quantite) ?>">
            </div>
            <div class="form-group">
                <label for="seuil_minimum" class="form-label required">Seuil Minimum</label>
                <input type="number" name="seuil_minimum" class="form-control" id="seuil_minimum" min="0" required
                    value="<?= htmlspecialchars($medicament->seuil_minimum) ?>">
            </div>
            <div class="form-group">
                <label for="date_expiration" class="form-label required">Date d'Expiration</label>
                <input type="date" name="date_expiration" class="form-control" id="date_expiration" required
                    value="<?= htmlspecialchars($medicament->date_expiration) ?>">
            </div>
            <div class="d-flex gap-2 my-4">
                <button class="btn btn-primary" type="submit" name="btnSubmit">
                    <i class="bi bi-save"></i> Enregistrer
                </button>
                <a class="btn btn-danger" href="index.php">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>