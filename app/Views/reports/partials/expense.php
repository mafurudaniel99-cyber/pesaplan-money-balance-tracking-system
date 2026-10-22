<?php /** @var array $rows */ ?>
<h3>Matumizi ya mwezi</h3>
<?php if (empty($rows)): ?>
    <div class="empty-state"><p>Hakuna matumizi kwa kipindi hiki.</p></div>
<?php else: ?>
    <table class="data-table">
        <thead><tr><th>Mwezi</th><th class="ta-right">Jumla ya matumizi</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['month']) ?></td>
                <td class="ta-right"><strong><?= money($r['total']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
