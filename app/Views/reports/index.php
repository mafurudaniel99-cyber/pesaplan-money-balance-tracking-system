<?php
/** @var string $from $to $report */
/** @var array $rows $incomeRows $expenseRows $indicators $budgetData */
/** @var string $view */
$data['title'] = 'Ripoti za Fedha';
$data['subtitle'] = 'Chambua mapato, matumizi, na bajeti kwa kipindi.';
?>
<form method="get" class="filter-bar">
    <div class="filter-grid">
        <div class="field"><label for="from">Kutoka</label><input type="date" id="from" name="from" value="<?= e($from) ?>"></div>
        <div class="field"><label for="to">Hadi</label><input type="date" id="to" name="to" value="<?= e($to) ?>"></div>
        <div class="field">
            <label for="report">Aina ya ripoti</label>
            <select id="report" name="report" onchange="this.form.submit()">
                <option value="monthly_summary" <?= $report==='monthly_summary'?'selected':'' ?>>Muhtasari wa mwezi</option>
                <option value="income" <?= $report==='income'?'selected':'' ?>>Mapato ya mwezi</option>
                <option value="expense" <?= $report==='expense'?'selected':'' ?>>Matumizi ya mwezi</option>
                <option value="category" <?= $report==='category'?'selected':'' ?>>Matumizi kwa kategoria</option>
                <option value="transactions" <?= $report==='transactions'?'selected':'' ?>>Historia ya miamala</option>
                <option value="indicators" <?= $report==='indicators'?'selected':'' ?>>Vielelezo vya fedha</option>
                <option value="budget_actual" <?= $report==='budget_actual'?'selected':'' ?>>Bajeti vs Halisi</option>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Onyesha</button>
        <a href="/reports/export?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&report=<?= urlencode($report) ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-csv"></i> Pakua CSV</a>
        <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> Chapisha</button>
    </div>
</form>

<div class="panel report-panel">
    <?php
    if (!empty($view) && file_exists(__DIR__ . '/partials/' . basename($view) . '.php')) {
        include __DIR__ . '/partials/' . basename($view) . '.php';
    } else {
        echo '<div class="empty-state"><p>Chagua aina ya ripoti.</p></div>';
    }
    ?>
</div>

<p class="muted small">💡 Maelezo: "Mapato halisi" = jumla ya mshahara na mapato mengine yaliyopokelewa. "Matumizi halisi" = miamala ya kawaida ya matumizi tu. "Akiba" ni mchango, sio matumizi. "Mapato halisi" tofauti na "Mapato yaliyotarajiwa".</p>
