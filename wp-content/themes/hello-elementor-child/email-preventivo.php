<?php
// $dati contiene tutti i campi del form
?>
<html>
  <body>
    <h2>Nuova richiesta preventivo</h2>
    <p><strong>Tipo veicolo:</strong> <?= esc_html($dati['tipoSelezionato'] ?? ''); ?></p>
    <p><strong>Veicolo:</strong> <?= esc_html($dati['veicoloSelezionato']['acf']['nome'] ?? ''); ?></p>
    <p><strong>Ritiro:</strong> <?= esc_html($dati['ritiro'] ?? ''); ?></p>
    <p><strong>Riconsegna:</strong> <?= esc_html($dati['riconsegna'] ?? ''); ?></p>
    <p><strong>Data/ora ritiro:</strong> <?= esc_html(($dati['dataRitiro'] ?? '') . ' ' . ($dati['oraRitiro'] ?? '')); ?></p>
    <p><strong>Data/ora riconsegna:</strong> <?= esc_html(($dati['dataRiconsegna'] ?? '') . ' ' . ($dati['oraRiconsegna'] ?? '')); ?></p>
    <p><strong>Telefono:</strong> <?= esc_html($dati['telefono'] ?? ''); ?></p>
    <p><strong>Email:</strong> <?= esc_html($dati['email'] ?? ''); ?></p>
  </body>
</html>
