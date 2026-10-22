<?php
/** @var \App\Services\FinancialSummaryService $summary */
/** @var array $recent $alerts $breakdown $trend $budgetTotals $categories */
$month = $summary->month();
$salaryReceived = $summary->salaryReceived();
$expenses = $summary->totalExpenses();
$cash = $summary->recordedCashBalance();
$obligations = $summary->unpaidObligations();
$projected = $summary->projectedAvailableCash();
$savings = $summary->totalSavings();
?>
<?php $data['title'] = 'Muhtasari wa Fedha'; ?>
<?php $data['subtitle'] = sw_date(date('Y-m-d')) . ' — mwezi ' . sw_date(date('Y-m-01'), 'F Y'); ?>

<div class="dashboard">
    <!-- Summary cards -->
    <section class="cards-grid" aria-label="Muhtasari wa mwezi">
        <article class="card card-income">
            <div class="card-head">
                <span class="card-icon"><i class="fa-solid fa-money-bill-trend-up"></i></span>
                <span class="card-title">Mshahara uliopokelewa</span>
            </div>
            <p class="card-value"><?= money($salaryReceived) ?></p>
            <p class="card-foot">Mwezi <?= sw_date(date('Y-m-01'), 'F Y') ?></p>
        </article>
        <article class="card card-expense">
            <div class="card-head">
                <span class="card-icon"><i class="fa-solid fa-arrow-trend-down"></i></span>
                <span class="card-title">Matumizi halisi</span>
            </div>
            <p class="card-value"><?= money($expenses) ?></p>
            <p class="card-foot">Jumla ya miamala ya mwezi</p>
        </article>
        <article class="card card-budget">
            <div class="card-head">
                <span class="card-icon"><i class="fa-solid fa-table-list"></i></span>
                <span class="card-title">Bajeti iliyobaki</span>
            </div>
            <p class="card-value"><?= money($budgetTotals['remaining']) ?></p>
            <p class="card-foot">Pangwa: <?= money($budgetTotals['planned']) ?> · Halisi: <?= money($budgetTotals['actual']) ?></p>
        </article>
        <article class="card card-savings">
            <div class="card-head">
                <span class="card-icon"><i class="fa-solid fa-piggy-bank"></i></span>
                <span class="card-title">Akiba iliyokusanywa</span>
            </div>
            <p class="card-value"><?= money($savings) ?></p>
            <p class="card-foot">Jumla ya malengo yote ya akiba</p>
        </article>
        <article class="card card-obligations">
            <div class="card-head">
                <span class="card-icon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                <span class="card-title">Majukumu yasiyolipwa</span>
            </div>
            <p class="card-value"><?= money($obligations) ?></p>
            <p class="card-foot">Bili na majukumu bado hayajalipwa</p>
        </article>
        <article class="card card-cash">
            <div class="card-head">
                <span class="card-icon"><i class="fa-solid fa-wallet"></i></span>
                <span class="card-title">Fedha zinazopatikana</span>
            </div>
            <p class="card-value"><?= money($projected) ?></p>
            <p class="card-foot">
                <span class="hint-line">
                    <i class="fa-solid fa-circle-info"></i>
                    Hesabu: fedha zilizorekodiwa (<?= money($cash) ?>) − majukumu (<?= money($obligations) ?>). Hesabu hii si salio la benki lililothibitishwa.
                </span>
            </p>
        </article>
    </section>

    <!-- Alerts -->
    <?php if (!empty($alerts)): ?>
        <section class="alerts" aria-label="Arifa za fedha">
            <h2 class="section-title"><i class="fa-solid fa-bell"></i> Arifa za fedha</h2>
            <div class="alert-grid">
                <?php foreach ($alerts as $a): ?>
                    <a class="alert-item alert-<?= e($a['severity']) ?>" href="<?= e($a['url'] ?? '#') ?>">
                        <span class="alert-icon"><i class="fa-solid <?= e($a['icon']) ?>"></i></span>
                        <span class="alert-body">
                            <strong><?= e($a['title']) ?></strong>
                            <span class="alert-text"><?= e($a['body']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Charts -->
    <section class="charts-grid">
        <article class="panel">
            <header class="panel-head">
                <h2><i class="fa-solid fa-chart-pie"></i> Matumizi kwa kategoria</h2>
                <span class="panel-sub">Mwezi <?= sw_date(date('Y-m-01'), 'F Y') ?></span>
            </header>
            <div class="panel-body">
                <?php if (empty($breakdown)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-chart-pie empty-icon"></i>
                        <p>Hakuna matumizi yaliyorekodiwa mwezi huu.</p>
                        <a href="/transactions/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Anza kuandika</a>
                    </div>
                <?php else: ?>
                    <div class="donut-chart" data-donut='<?= json_encode(array_map(fn($b) => [
                        'label' => $b['name'],
                        'value' => (float) $b['total'],
                        'color' => $b['color'],
                    ], $breakdown), JSON_UNESCAPED_UNICODE) ?>'></div>
                    <ul class="legend">
                        <?php foreach ($breakdown as $b): ?>
                            <li>
                                <span class="legend-dot" style="background: <?= e($b['color']) ?>"></span>
                                <span class="legend-label"><?= e($b['name']) ?></span>
                                <span class="legend-value"><?= money($b['total']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </article>

        <article class="panel">
            <header class="panel-head">
                <h2><i class="fa-solid fa-chart-column"></i> Mapato vs Matumizi</h2>
                <span class="panel-sub">Miezi 6 iliyopita</span>
            </header>
            <div class="panel-body">
                <?php if (empty($trend)): ?>
                    <div class="empty-state"><p>Hakuna data ya kutosha kwa chati hii.</p></div>
                <?php else: ?>
                    <div class="bar-chart" data-bars='<?= json_encode(array_map(fn($t) => [
                        'label' => $t['month'],
                        'income' => (float) $t['income'],
                        'expense' => (float) $t['expense'],
                    ], $trend), JSON_UNESCAPED_UNICODE) ?>'></div>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <!-- Budget progress + Recent activity -->
    <section class="two-col">
        <article class="panel">
            <header class="panel-head">
                <h2><i class="fa-solid fa-gauge-high"></i> Maendeleo ya bajeti</h2>
                <a href="/budgets" class="panel-link">Bajeti kamili <i class="fa-solid fa-arrow-right"></i></a>
            </header>
            <div class="panel-body">
                <?php
                $items = $summary->budgetItemsWithActuals();
                if (empty($items)): ?>
                    <div class="empty-state">
                        <p>Hujatengeneza bajeti kwa mwezi huu bado.</p>
                        <a href="/budgets" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Tengeneza bajeti</a>
                    </div>
                <?php else: ?>
                    <ul class="budget-progress">
                        <?php foreach (array_slice($items, 0, 6) as $it): ?>
                            <li>
                                <div class="bp-head">
                                    <span class="bp-name"><i class="fa-solid <?= e($it['category_icon']) ?>"></i> <?= e($it['category_name']) ?></span>
                                    <span class="bp-amount"><?= money($it['actual_amount']) ?> / <?= money($it['planned_amount']) ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar <?= percent_bar_class((float) $it['percent']) ?>" style="width: <?= min(100, (float) $it['percent']) ?>%"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </article>

        <article class="panel">
            <header class="panel-head">
                <h2><i class="fa-solid fa-clock-rotate-left"></i> Miamala ya hivi karibuni</h2>
                <a href="/transactions" class="panel-link">Yote <i class="fa-solid fa-arrow-right"></i></a>
            </header>
            <div class="panel-body">
                <?php if (empty($recent)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-receipt empty-icon"></i>
                        <p>Hakuna miamala bado. Anza kwa kuandika matumizi ya leo.</p>
                        <a href="/transactions/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Ongeza muamala</a>
                    </div>
                <?php else: ?>
                    <ul class="tx-list">
                        <?php foreach ($recent as $tx): ?>
                            <?php
                                $isIncome = in_array($tx['type'], ['salary','other_income','refund','savings_out','transfer_in'], true);
                                $isExpense = $tx['type'] === 'expense' || $tx['type'] === 'debt_repay' || $tx['type'] === 'transfer_out';
                            ?>
                            <li class="tx-item">
                                <span class="tx-icon" style="background: <?= e($tx['category_color'] ?? '#4a6fa5') ?>20; color: <?= e($tx['category_color'] ?? '#4a6fa5') ?>">
                                    <i class="fa-solid <?= e($tx['category_icon'] ?? 'fa-tag') ?>"></i>
                                </span>
                                <span class="tx-body">
                                    <span class="tx-desc"><?= e($tx['description']) ?></span>
                                    <span class="tx-meta"><?= sw_date($tx['transaction_date']) ?> · <?= e($tx['category_name'] ?? '—') ?></span>
                                </span>
                                <span class="tx-amount <?= $isIncome ? 'amt-income' : ($isExpense ? 'amt-expense' : 'amt-neutral') ?>">
                                    <?= $isIncome ? '+' : ($isExpense ? '−' : '') ?><?= money($tx['amount']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </article>
    </section>
</div>
<script src="/assets/js/charts.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/charts.js') ?>"></script>
<script src="/assets/js/dashboard.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/dashboard.js') ?>"></script>
