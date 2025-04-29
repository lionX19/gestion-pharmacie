<?php
session_start();
include("traitement.php");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Médicament</title>
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
        <h1 class="text-center my-4">Ajouter un Médicament</h1>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="" method="post" class="col-md-6 mx-auto">
            <div class="form-group">
                <label for="nom" class="form-label required">Nom</label>
                <input type="text" name="nom" class="form-control" id="nom" required
                    value="<?php if (isset($_POST['nom'])) echo htmlspecialchars($_POST['nom']); ?>">
            </div>
            <div class="form-group">
                <label for="dosage" class="form-label required">Dosage</label>
                <input type="text" name="dosage" class="form-control" id="dosage" required
                    value="<?php if (isset($_POST['dosage'])) echo htmlspecialchars($_POST['dosage']); ?>">
            </div>
            <div class="form-group">
                <label for="quantite" class="form-label required">Quantité</label>
                <input type="number" name="quantite" class="form-control" id="quantite" min="0" required
                    value="<?php if (isset($_POST['quantite'])) echo htmlspecialchars($_POST['quantite']); ?>">
            </div>
            <div class="form-group">
                <label for="seuil_minimum" class="form-label required">Seuil Minimum</label>
                <input type="number" name="seuil_minimum" class="form-control" id="seuil_minimum" min="0" required
                    value="<?php if (isset($_POST['seuil_minimum'])) echo htmlspecialchars($_POST['seuil_minimum']); ?>">
            </div>
            <div class="form-group">
                <label for="date_expiration" class="form-label required">Date d'Expiration</label>
                <input type="date" name="date_expiration" class="form-control" id="date_expiration" required
                    value="<?php if (isset($_POST['date_expiration'])) echo htmlspecialchars($_POST['date_expiration']); ?>">
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