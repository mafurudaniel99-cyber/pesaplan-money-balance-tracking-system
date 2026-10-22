<?php /** @var array $rows */ ?>
<h3>Matumizi kwa kategoria</h3>
<?php if (empty($rows)): ?>
    <div class="empty-state"><p>Hakuna matumizi kwa kipindi hiki.</p></div>
<?php else: ?>
    <table class="data-table">
        <thead><tr><th>Kategoria</th><th>Aina</th><th class="ta-right">Idadi</th><th class="ta-right">Jumla</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><i class="fa-solid <?= e($r['icon']) ?>" style="color: <?= e($r['color']) ?>"></i> <?= e($r['name']) ?></td>
                <td><span class="badge"><?= e($r['kind']) ?></span></td>
                <td class="ta-right"><?= (int) $r['tx_count'] ?></td>
                <td class="ta-right"><strong><?= money($r['total']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
