<?php
session_start();
require_once("../db/db.php");

// Configuration de la pagination
$medicaments_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $medicaments_per_page;

// Gestion du filtre de recherche
$search = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'])) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = " WHERE nom LIKE ? OR dosage LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
}

// Compter le nombre total de médicaments pour la pagination
$count_query = "SELECT COUNT(*) as total FROM medicaments" . $where_clause;
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_medicaments = $count_stmt->fetch()->total;
$total_pages = ceil($total_medicaments / $medicaments_per_page);

// Récupérer les médicaments pour la page actuelle
$query = "SELECT * FROM medicaments" . $where_clause . " ORDER BY id_medicament DESC LIMIT :limit OFFSET :offset";
$req = $pdo->prepare($query);

// Bind parameters explicitly
if (!empty($search)) {
    $req->bindValue(1, $search_param, PDO::PARAM_STR);
    $req->bindValue(2, $search_param, PDO::PARAM_STR);
    $req->bindValue(':limit', $medicaments_per_page, PDO::PARAM_INT);
    $req->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
    $req->bindValue(':limit', $medicaments_per_page, PDO::PARAM_INT);
    $req->bindValue(':offset', $offset, PDO::PARAM_INT);
}
$req->execute();

$i = ($page - 1) * $medicaments_per_page + 1;

// Préparer les alertes pour stock critique et expiration proche
$alerts = [];
$today = new DateTime();
$threshold_date = (new DateTime())->modify('+30 days');

$req_alerts = $pdo->prepare("SELECT * FROM medicaments" . $where_clause);
$req_alerts->execute($params);
while ($medicament = $req_alerts->fetch()) {
    if ($medicament->quantite <= $medicament->seuil_minimum) {
        $alerts['seuil'][] = $medicament->nom . " (Dosage: " . $medicament->dosage . ")";
    }
    $expiration_date = new DateTime($medicament->date_expiration);
    if ($expiration_date <= $threshold_date) {
        $alerts['expiration'][] = $medicament->nom . " (Dosage: " . $medicament->dosage . ")";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Médicaments</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <!-- Contenu Principal -->
    <main>
        <div class="container">
            <!-- Section des alertes globales -->
            <div class="alert-section">
                <?php if (!empty($alerts['seuil'])): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Stock critique :</strong>
                        <?= implode(", ", $alerts['seuil']) ?> ont une quantité inférieure ou égale au seuil minimum.
                    </div>
                <?php endif; ?>
                <?php if (!empty($alerts['expiration'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-clock"></i> <strong>Expiration proche :</strong>
                        <?= implode(", ", $alerts['expiration']) ?> expirent dans moins de 30 jours.
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center my-4">
                <h1 class="text-start">Liste des Médicaments</h1>
                <form class="search-form" method="get" action="">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou dosage"
                            value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <a href="create.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Ajouter</a>
            </div>

            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Dosage</th>
                        <th scope="col">Quantité</th>
                        <th scope="col">Seuil</th>
                        <th scope="col">Date d'expiration</th>
                        <th scope="col">Créé le</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($medicament = $req->fetch()) : ?>
                        <?php
                        $is_low_stock = $medicament->quantite <= $medicament->seuil_minimum;
                        $expiration_date = new DateTime($medicament->date_expiration);
                        $is_expiring_soon = $expiration_date <= $threshold_date;
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($medicament->nom) ?></td>
                            <td><?= htmlspecialchars($medicament->dosage) ?></td>
                            <td>
                                <?= htmlspecialchars($medicament->quantite) ?>
                                <?php if ($is_low_stock): ?>
                                    <span class="badge bg-warning ms-2">Stock critique</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($medicament->seuil_minimum) ?></td>
                            <td>
                                <?= htmlspecialchars($medicament->date_expiration) ?>
                                <?php if ($is_expiring_soon): ?>
                                    <span class="badge bg-danger ms-2">Expiration proche</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($medicament->created_at) ?></td>
                            <td>
                                <a class="btn btn-outline-primary btn-sm" href="edit.php?id=<?= $medicament->id_medicament ?>">
                                    <i class="fas fa-pencil"></i> Éditer
                                </a>
                                <a class="btn btn-outline-danger btn-sm" href="delete.php?id=<?= $medicament->id_medicament ?>"
                                    onclick="return confirm('Voulez-vous vraiment supprimer ce médicament ?');">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Dosage</th>
                        <th scope="col">Quantité</th>
                        <th scope="col">Seuil</th>
                        <th scope="col">Date d'expiration</th>
                        <th scope="col">Créé le</th>
                        <th scope="col">Actions</th>
                    </tr>
                </tfoot>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Pagination">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Précédent</a>
                        </li>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>


        <!-- Boîte de dialogue personnalisée -->
        <div id="confirmDialog" class="dialog hidden">
            <div class="dialog-content">
                <h2>Êtes-vous sûr ?</h2>
                <p>Voulez-vous vraiment supprimer ce médicament ? Cette action est irréversible.</p>
                <div class="dialog-buttons">
                    <button onclick="confirmAction()">Supprimer</button>
                    <button onclick="cancelAction()">Annuler</button>
                </div>
            </div>
        </div>
        </div>
    </main>
    <!-- Fin Contenu Principal -->

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>

</html>