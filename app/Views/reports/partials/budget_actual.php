<?php /** @var array $budgetData */ 
/** @var string $from */
$month = substr($from, 0, 7);
?>
<h3>Bajeti vs Halisi — <?= sw_date($month . '-01', 'F Y') ?></h3>
<?php if (empty($budgetData['items'])): ?>
    <div class="empty-state"><p>Hakuna bajeti kwa mwezi huu.</p></div>
<?php else: ?>
    <div class="cards-grid cards-3">
        <article class="card card-budget"><p class="card-title">Pangwa</p><p class="card-value"><?= money($budgetData['totals']['planned']) ?></p></article>
        <article class="card card-expense"><p class="card-title">Halisi</p><p class="card-value"><?= money($budgetData['totals']['actual']) ?></p></article>
        <article class="card card-savings"><p class="card-title">Tofauti</p><p class="card-value"><?= money($budgetData['totals']['variance']) ?></p></article>
    </div>
    <table class="data-table">
        <thead><tr><th>Kategoria</th><th class="ta-right">Pangwa</th><th class="ta-right">Halisi</th><th class="ta-right">Tofauti</th><th class="ta-right">Asilimia</th></tr></thead>
        <tbody>
            <?php foreach ($budgetData['items'] as $it): ?>
            <tr>
                <td><?= e($it['category_name']) ?></td>
                <td class="ta-right"><?= money($it['planned_amount']) ?></td>
                <td class="ta-right"><?= money($it['actual_amount']) ?></td>
                <td class="ta-right <?= $it['variance']>0?'amt-expense':'amt-income' ?>"><?= money($it['variance']) ?></td>
                <td class="ta-right"><?= round($it['percent']) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
