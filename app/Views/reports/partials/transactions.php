<?php /** @var array $rows */ ?>
<h3>Historia ya miamala</h3>
<?php if (empty($rows)): ?>
    <div class="empty-state"><p>Hakuna miamala kwa kipindi hiki.</p></div>
<?php else: ?>
    <table class="data-table">
        <thead><tr><th>Tarehe</th><th>Aina</th><th>Maelezo</th><th>Kategoria</th><th>Njia</th><th class="ta-right">Kiasi</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= sw_date($r['transaction_date']) ?></td>
                <td><span class="pill pill-<?= e($r['type']) ?>"><?= e($r['type']) ?></span></td>
                <td><?= e($r['description']) ?></td>
                <td><?= e($r['category_name'] ?? '—') ?></td>
                <td><?= e($r['payment_method']) ?></td>
                <td class="ta-right <?= in_array($r['type'], ['salary','other_income','refund']) ? 'amt-income' : 'amt-expense' ?>"><?= money($r['amount']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
