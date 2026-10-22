<?php /** @var array $rows */ ?>
<h3>Mapato ya mwezi</h3>
<?php if (empty($rows)): ?>
    <div class="empty-state"><p>Hakuna mapato kwa kipindi hiki.</p></div>
<?php else: ?>
    <table class="data-table">
        <thead><tr><th>Mwezi</th><th class="ta-right">Mshahara</th><th class="ta-right">Mapato mengine</th><th class="ta-right">Jumla</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['month']) ?></td>
                <td class="ta-right"><?= money($r['salary']) ?></td>
                <td class="ta-right"><?= money($r['other']) ?></td>
                <td class="ta-right"><strong><?= money($r['total']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
