<?php
/** @var string $month */
/** @var ?array $budget */
/** @var array $items $totals $recentMonths $categories $suggestion */
/** @var float $expectedSalary $salaryReceived */
$data['title'] = 'Bajeti — ' . sw_date($month . '-01', 'F Y');
$data['subtitle'] = 'Panga matumizi yako KABLA ya kuyafanya. Linganisha na halisi.';
?>
<div class="page-toolbar">
    <form method="get" class="inline-form">
        <label for="month">Mwezi:</label>
        <input type="month" id="month" name="month" value="<?= e($month) ?>" onchange="this.form.submit()">
    </form>
    <button class="btn btn-primary" data-modal-open="newBudget"><i class="fa-solid fa-pen"></i> Weka bajeti ya mwezi</button>
</div>

<?php if (!$budget): ?>
    <div class="panel">
        <div class="empty-state">
            <i class="fa-solid fa-table-list empty-icon"></i>
            <h3>Hujatengeneza bajeti ya <?= sw_date($month . '-01', 'F Y') ?></h3>
            <p>Bajeti ni mpango wa jinsi pesa itakavyotumika. Anza na mapato yako halisi, kisha gawa kwenye kategoria.</p>
            <button class="btn btn-primary" data-modal-open="newBudget"><i class="fa-solid fa-pen"></i> Weka bajeti ya mwezi</button>
        </div>
    </div>
<?php else: ?>
    <!-- Budget summary -->
    <section class="cards-grid cards-4">
        <article class="card card-income">
            <p class="card-title">Mapato halisi</p>
            <p class="card-value"><?= money($salaryReceived) ?></p>
            <p class="card-foot">Umetaraji: <?= money($expectedSalary) ?></p>
        </article>
        <article class="card card-budget">
            <p class="card-title">Jumla iliyopangwa</p>
            <p class="card-value"><?= money($totals['planned']) ?></p>
            <p class="card-foot">Kategoria: <?= count($items) ?></p>
        </article>
        <article class="card card-expense">
            <p class="card-title">Halisi</p>
            <p class="card-value"><?= money($totals['actual']) ?></p>
            <p class="card-foot">Asilimia: <?= round($totals['percent']) ?>%</p>
        </article>
        <article class="card <?= $totals['remaining'] >= 0 ? 'card-savings' : 'card-obligations' ?>">
            <p class="card-title">Iliyobaki</p>
            <p class="card-value"><?= money($totals['remaining']) ?></p>
            <p class="card-foot">Tofauti: <?= money($totals['variance']) ?></p>
        </article>
    </section>

    <?php if ($totals['planned'] > $salaryReceived && $salaryReceived > 0): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Bajeti yako inazidi mapato halisi ya mwezi (<?= money($salaryReceived) ?>) kwa <?= money($totals['planned'] - $salaryReceived) ?>. Fikiria kupunguza baadhi ya kategoria.
        </div>
    <?php endif; ?>

    <div class="panel">
        <header class="panel-head">
            <h2><i class="fa-solid fa-list-check"></i> Kategoria za bajeti</h2>
            <button class="btn btn-ghost btn-sm" data-modal-open="addItem"><i class="fa-solid fa-plus"></i> Ongeza kategoria</button>
        </header>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kategoria</th>
                    <th class="ta-right">Pangwa</th>
                    <th class="ta-right">Halisi</th>
                    <th class="ta-right">Iliyobaki</th>
                    <th>Asilimia</th>
                    <th class="ta-center">Hatua</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td>
                            <span class="cat-chip" style="background: <?= e($it['category_color']) ?>20; color: <?= e($it['category_color']) ?>">
                                <i class="fa-solid <?= e($it['category_icon']) ?>"></i> <?= e($it['category_name']) ?>
                            </span>
                            <?php if ($it['is_essential']): ?><span class="badge badge-info">Muhimu</span><?php endif; ?>
                        </td>
                        <td class="ta-right"><?= money($it['planned_amount']) ?></td>
                        <td class="ta-right"><?= money($it['actual_amount']) ?></td>
                        <td class="ta-right <?= $it['remaining'] < 0 ? 'amt-expense' : '' ?>"><?= money($it['remaining']) ?></td>
                        <td>
                            <div class="progress-cell">
                                <div class="progress">
                                    <div class="progress-bar <?= percent_bar_class((float) $it['percent']) ?>" style="width: <?= min(100, (float) $it['percent']) ?>%"></div>
                                </div>
                                <span class="progress-text"><?= round($it['percent']) ?>%</span>
                            </div>
                        </td>
                        <td class="ta-center">
                            <form method="post" action="/budgets/items/<?= (int) $it['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa kipengele hiki?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="icon-action icon-danger" title="Futa"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="ta-center muted">Hakuna kategoria. Ongeza moja.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Jumla</th>
                    <th class="ta-right"><?= money($totals['planned']) ?></th>
                    <th class="ta-right"><?= money($totals['actual']) ?></th>
                    <th class="ta-right"><?= money($totals['remaining']) ?></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if (!empty($suggestion['items'])): ?>
    <div class="panel panel-info">
        <header class="panel-head">
            <h2><i class="fa-solid fa-lightbulb"></i> Mapendekezo ya bajeti (hiari)</h2>
        </header>
        <div class="panel-body">
            <p class="muted">Mapendekezo haya yanatokana na wastani wa matumizi yako ya miezi 3 iliyopita. Yanaweza kubadilishwa. Asilimia za kawaida si sheria — zingatia hali yako halisi.</p>
            <ul class="suggestion-list">
                <?php foreach ($suggestion['items'] as $s): ?>
                    <li>
                        <span class="cat-chip" style="background: <?= e($s['color']) ?>20; color: <?= e($s['color']) ?>"><i class="fa-solid <?= e($s['icon']) ?>"></i> <?= e($s['category_name']) ?></span>
                        <span class="suggestion-amount"><?= money($s['planned_amount']) ?></span>
                        <span class="muted"><?= e($s['reason']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal: create/edit budget -->
<div class="modal" id="newBudget" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Bajeti ya <?= sw_date($month . '-01', 'F Y') ?></h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/budgets" class="modal-body">
            <?= csrf_field() ?>
            <input type="hidden" name="period_month" value="<?= e($month) ?>">
            <div class="field">
                <label for="total_income_planned">Mapato yaliyopangwa (TZS)</label>
                <input type="number" id="total_income_planned" name="total_income_planned" min="0" step="1" value="<?= e((string) (float) ($budget['total_income_planned'] ?? $expectedSalary)) ?>">
            </div>
            <div class="field">
                <label for="notes">Maelezo (hiari)</label>
                <textarea id="notes" name="notes" rows="2"><?= e($budget['notes'] ?? '') ?></textarea>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi bajeti</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: add item -->
<div class="modal" id="addItem" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Ongeza kategoria kwenye bajeti</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/budgets/<?= (int) ($budget['id'] ?? 0) ?>/items" class="modal-body">
            <?= csrf_field() ?>
            <div class="field">
                <label for="category_id">Kategoria</label>
                <select id="category_id" name="category_id" required>
                    <option value="">— chagua —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="planned_amount">Kiasi kilichopangwa (TZS)</label>
                    <input type="number" id="planned_amount" name="planned_amount" min="0" step="1" required>
                </div>
                <div class="field">
                    <label for="priority">Kipaumbele (1-10)</label>
                    <input type="number" id="priority" name="priority" min="1" max="10" value="5">
                </div>
            </div>
            <div class="field">
                <label class="checkbox-line">
                    <input type="checkbox" name="is_essential" value="1">
                    <span>Hii ni kategoria muhimu (hitaji la msingi)</span>
                </label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Ongeza</button>
            </div>
        </form>
    </div>
</div>
