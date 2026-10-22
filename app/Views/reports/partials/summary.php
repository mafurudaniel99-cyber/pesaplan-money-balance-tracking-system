<?php /** @var array $rows */ ?>
<h3>Muhtasari wa kipindi — <?= sw_date($from) ?> hadi <?= sw_date($to) ?></h3>

<?php if (!empty($indicators)): ?>
<section class="cards-grid cards-4">
    <article class="card card-income"><p class="card-title">Mapato halisi</p><p class="card-value"><?= money($indicators['total_income']) ?></p></article>
    <article class="card card-expense"><p class="card-title">Matumizi halisi</p><p class="card-value"><?= money($indicators['total_expense']) ?></p></article>
    <article class="card card-savings"><p class="card-title">Net cash</p><p class="card-value <?= $indicators['net_cash'] < 0 ? 'amt-expense' : 'amt-income' ?>"><?= money($indicators['net_cash']) ?></p></article>
    <article class="card card-budget"><p class="card-title">Michango ya akiba</p><p class="card-value"><?= money($indicators['savings_contrib']) ?></p></article>
</section>

<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Kielelezo</th><th class="ta-right">Thamani</th><th>Maelezo</th></tr></thead>
        <tbody>
            <tr><td>Mapato halisi</td><td class="ta-right"><?= money($indicators['total_income']) ?></td><td>Mshahara + mapato mengine yaliyopokelewa</td></tr>
            <tr><td>Matumizi halisi</td><td class="ta-right"><?= money($indicators['total_expense']) ?></td><td>Miamala ya kawaida ya matumizi tu</td></tr>
            <tr><td>Net cash (mapato − matumizi)</td><td class="ta-right <?= $indicators['net_cash']<0?'amt-expense':'amt-income' ?>"><?= money($indicators['net_cash']) ?></td><td>Tofauti ya pesa iliyobaki</td></tr>
            <tr><td>Michango ya akiba</td><td class="ta-right"><?= money($indicators['savings_contrib']) ?></td><td>Hesabuwa kando — sio matumizi</td></tr>
            <tr><td>Malipo ya deni</td><td class="ta-right"><?= money($indicators['debt_repay']) ?></td><td>Haizingatiwi kama matumizi ya kawaida</td></tr>
            <tr><td>Rudisha pesa</td><td class="ta-right"><?= money($indicators['refunds']) ?></td><td>Haizingatiwi kama mapato mapya</td></tr>
            <tr><td>Matumizi muhimu kama % ya mapato</td><td class="ta-right"><?= round($indicators['essential_pct']) ?>%</td><td><?= money($indicators['essential_expense']) ?> / <?= money($indicators['total_income']) ?></td></tr>
            <tr><td>Matumizi ya starehe kama % ya mapato</td><td class="ta-right"><?= round($indicators['discretionary_pct']) ?>%</td><td><?= money($indicators['discretionary_expense']) ?> / <?= money($indicators['total_income']) ?></td></tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
