<?php /** @var array $indicators */ ?>
<h3>Vielelezo vya fedha — <?= sw_date($from) ?> hadi <?= sw_date($to) ?></h3>
<div class="cards-grid cards-3">
    <article class="card card-income"><p class="card-title">Mapato halisi</p><p class="card-value"><?= money($indicators['total_income']) ?></p></article>
    <article class="card card-expense"><p class="card-title">Matumizi halisi</p><p class="card-value"><?= money($indicators['total_expense']) ?></p></article>
    <article class="card card-savings"><p class="card-title">Net cash</p><p class="card-value <?= $indicators['net_cash']<0?'amt-expense':'amt-income' ?>"><?= money($indicators['net_cash']) ?></p></article>
    <article class="card"><p class="card-title">Michango ya akiba</p><p class="card-value"><?= money($indicators['savings_contrib']) ?></p></article>
    <article class="card"><p class="card-title">Malipo ya deni</p><p class="card-value"><?= money($indicators['debt_repay']) ?></p></article>
    <article class="card"><p class="card-title">Rudisha pesa</p><p class="card-value"><?= money($indicators['refunds']) ?></p></article>
</div>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Kategoria</th><th class="ta-right">Kiasi</th><th class="ta-right">% ya mapato</th></tr></thead>
        <tbody>
            <tr><td>Matumizi muhimu</td><td class="ta-right"><?= money($indicators['essential_expense']) ?></td><td class="ta-right"><?= round($indicators['essential_pct']) ?>%</td></tr>
            <tr><td>Matumizi ya starehe</td><td class="ta-right"><?= money($indicators['discretionary_expense']) ?></td><td class="ta-right"><?= round($indicators['discretionary_pct']) ?>%</td></tr>
        </tbody>
    </table>
</div>
<p class="muted small">💡 "Net cash" si sawa na "mapato" au "faida". Hapa ina maana ya pesa iliyobaki baada ya matumizi. Kwa mtu wa mshahara, muhimu kufuatilia: mshahara halisi, matumizi halisi, majukumu, fedha zinazopatikana, na maendeleo ya akiba.</p>
