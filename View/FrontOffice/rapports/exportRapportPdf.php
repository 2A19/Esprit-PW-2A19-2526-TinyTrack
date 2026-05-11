<?php
// Vue passive — données injectées par RapportController::exportPdf()
// Variables : $rapport
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Rapport PDF #<?= htmlspecialchars($rapport['id_rapport']) ?></title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 30px; color: #333; }
    .pdf-container { max-width: 800px; margin: auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
    .header { text-align: center; border-bottom: 3px solid #4CAF50; padding-bottom: 20px; margin-bottom: 30px; }
    .header h1 { margin: 0; color: #4CAF50; }
    .info-box { margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-left: 5px solid #4CAF50; border-radius: 8px; }
    .info-box p { margin: 8px 0; font-size: 15px; }
    .content-box { margin-top: 25px; }
    .content-box h3 { color: #4CAF50; margin-bottom: 10px; }
    .content { padding: 15px; border: 1px solid #ddd; border-radius: 8px; min-height: 120px; line-height: 1.6; }
    .footer { margin-top: 40px; text-align: center; font-size: 13px; color: #777; border-top: 1px solid #ddd; padding-top: 15px; }
    .buttons { text-align: center; margin-bottom: 20px; }
    .btn { display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 25px; text-decoration: none; color: white; font-weight: bold; border: none; cursor: pointer; }
    .btn-print { background: #4CAF50; }
    .btn-back { background: #6c757d; }
    @media print {
      body { background: white; padding: 0; }
      .buttons { display: none; }
      .pdf-container { box-shadow: none; border-radius: 0; max-width: 100%; }
    }
  </style>
</head>
<body>

<div class="buttons">
  <button onclick="window.print()" class="btn btn-print">Exporter / Enregistrer en PDF</button>
  <a href="/TinyTrack/rapports/parent" class="btn btn-back">Retour</a>
</div>

<div class="pdf-container">
  <div class="header">
    <h1>TinyTrack</h1>
    <p>Rapport Journalier de l'Enfant</p>
  </div>

  <div class="info-box">
    <p><strong>Numéro du rapport :</strong> #<?= htmlspecialchars($rapport['id_rapport']) ?></p>
    <p><strong>Activité :</strong> <?= htmlspecialchars($rapport['nom_activite'] ?? '—') ?></p>
    <p><strong>Date :</strong> <?= htmlspecialchars($rapport['date_rapport']) ?></p>
    <p><strong>Éducateur ID :</strong> <?= htmlspecialchars($rapport['id_educateur'] ?? '—') ?></p>
  </div>

  <div class="content-box">
    <h3>Contenu du rapport</h3>
    <div class="content">
      <?= nl2br(htmlspecialchars($rapport['contenu_rapport'])) ?>
    </div>
  </div>

  <div class="footer">
    <p>Document généré par TinyTrack</p>
    <p>Date d'exportation : <?= date('Y-m-d H:i') ?></p>
  </div>
</div>

</body>
</html>
