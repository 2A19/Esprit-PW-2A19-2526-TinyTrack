<!DOCTYPE html>
<html lang="fr">
<head>
	<title>TinyTrack — Liste des Événements</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="format-detection" content="telephone=no">
	<meta name="apple-mobile-web-app-capable" content="yes">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

	<link rel="stylesheet" type="text/css" href="assets/css/normalize.css">
	<link rel="stylesheet" type="text/css" href="assets/icomoon/icomoon.css">
	<link rel="stylesheet" type="text/css" href="assets/css/vendor.css">
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<style> 
.event-card {
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    transition: 0.3s;
}

.event-card:hover {
    transform: translateY(-5px);
}

.event-image {
    position: relative;
}

.event-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.event-type {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #365CF5;
    color: white;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
}

.event-content {
    padding: 15px;
}

.event-content h5 {
    font-weight: bold;
}

.desc {
    font-size: 13px;
    color: #6b7280;
}

.event-info {
    font-size: 13px;
    margin-top: 10px;
}

.event-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    font-weight: bold;
}

.price {
    color: #16a34a;
}

.status {
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 12px;
}

.status.planifie { background:#fff3cd; color:#856404; }
.status.en_cours { background:#d1e7dd; color:#0f5132; }
.status.termine { background:#f8d7da; color:#842029; }
.status.annule { background:#e2e3e5; color:#41464b; }
</style>
<body>

<?php
include(__DIR__ . '/../../controller/evenementController.php');
$controller = new EvenementController();
$events = $controller->afficher();
?>

<div id="header-wrap">
	<header id="header">
		<div class="container-fluid">
			<div class="row">

				<div class="col-md-2">
					<div class="main-logo">
						<a href="index.php"><img src="images/x.png" alt="logo" width="40%" height="50%"></a>
					</div>
				</div>

				<div class="col-md-10">
					<nav id="navbar">
						<div class="main-menu stellarnav">
							<ul class="menu-list">
								<li class="menu-item"><a href="index.php">Home</a></li>
								<li class="menu-item active"><a href="evenementList.php">Events</a></li>
							</ul>
							<div class="hamburger">
								<span class="bar"></span>
								<span class="bar"></span>
								<span class="bar"></span>
							</div>
						</div>
					</nav>
				</div>

			</div>
		</div>
	</header>
</div>

<br/>

<section id="library" class="bookshelf py-5 my-3">
	<div class="container">
		<div class="row">
			<div class="col-md-12">

				<div class="section-header align-center mb-4">
					<div class="title">
						<span>Agenda TinyTrack</span>
					</div>
					<h2 class="section-title">Liste des Événements</h2>
				</div>

				<?php if ($events && $events->rowCount() > 0): ?>
				<div class="table-responsive">
	<div class="row">

<?php while ($row = $events->fetch()): ?>

<div class="col-md-4 mb-4">

    <div class="event-card">

        <!-- Image -->
        <div class="event-image">
            <img src="images/image1.jfif" alt="event">
            <span class="event-type"><?= htmlspecialchars($row['type']) ?></span>
        </div>

        <!-- Content -->
        <div class="event-content">

            <h5><?= htmlspecialchars($row['titre']) ?></h5>

            <p class="desc">
                <?= htmlspecialchars(substr($row['description'], 0, 80)) ?>...
            </p>

            <div class="event-info">
                <span>📅 <?= htmlspecialchars($row['date']) ?></span><br>
                <span>📍 <?= htmlspecialchars($row['lieu']) ?></span>
            </div>

            <div class="event-footer">
                <span class="price">
                    <?= $row['prix'] > 0 ? number_format($row['prix'],2).' TND' : 'Gratuit' ?>
                </span>

                <span class="status <?= $row['statut'] ?>">
                    <?= htmlspecialchars($row['statut']) ?>
                </span>
            </div>
        </div>

    </div>

</div>

<?php endwhile; ?>

</div>
				</div>

				<?php else: ?>
				<div class="alert alert-info text-center" role="alert">
					Aucun événement disponible pour le moment.
				</div>
				<?php endif; ?>

			</div>
		</div>
	</div>
</section>

<div id="footer-bottom">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="copyright">
					<div class="row">
						<div class="col-md-6">
							<p>© 2025 TinyTrack — Tous droits réservés</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="js/jquery-1.11.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
	integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
	crossorigin="anonymous"></script>
<script src="assets/js/plugins.js"></script>
<script src="assets/js/script.js"></script>

</body>
</html>