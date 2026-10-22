<?php
/** @var array $transactions $filters $categories $types */
/** @var int $page $totalPages $total */
$data['title'] = 'Miamala';
$data['subtitle'] = $total . ' muamala — ukurasa ' . $page . ' wa ' . $totalPages;
?>
<div class="page-toolbar">
    <a href="/transactions/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ongeza muamala</a>
</div>

<form method="get" class="filter-bar" aria-label="Chuja miamala">
    <div class="filter-grid">
        <div class="field">
            <label for="q">Tafuta</label>
            <input type="text" id="q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Maelezo...">
        </div>
        <div class="field">
            <label for="from">Kutoka</label>
            <input type="date" id="from" name="from" value="<?= e($filters['from'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="to">Hadi</label>
            <input type="date" id="to" name="to" value="<?= e($filters['to'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="type">Aina</label>
            <select id="type" name="type">
                <option value="">— zote —</option>
                <?php foreach ($types as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= ($filters['type'] ?? '') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="category_id">Kategoria</label>
            <select id="category_id" name="category_id">
                <option value="">— zote —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) ($filters['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="sort">Panga</label>
            <select id="sort" name="sort">
                <option value="">Tarehe (mpya)</option>
                <option value="date_asc" <?= ($filters['sort'] ?? '') === 'date_asc' ? 'selected' : '' ?>>Tarehe (zamani)</option>
                <option value="amount_desc" <?= ($filters['sort'] ?? '') === 'amount_desc' ? 'selected' : '' ?>>Kiasi (kubwa)</option>
                <option value="amount_asc" <?= ($filters['sort'] ?? '') === 'amount_asc' ? 'selected' : '' ?>>Kiasi (ndogo)</option>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Chuja</button>
        <a href="/transactions" class="btn btn-ghost btn-sm">Futa</a>
    </div>
</form>

<?php if (empty($transactions)): ?>
    <div class="empty-state panel">
        <i class="fa-solid fa-receipt empty-icon"></i>
        <h3>Hakuna miamala iliyoonekana</h3>
        <p>Anza kwa kuandika matumizi ya leo ili kuona historia hapa.</p>
        <a href="/transactions/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ongeza muamala wa kwanza</a>
    </div>
<?php else: ?>
    <div class="table-wrap panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tarehe</th>
                    <th>Aina</th>
                    <th>Maelezo</th>
                    <th>Kategoria</th>
                    <th class="ta-right">Kiasi</th>
                    <th>Njia</th>
                    <th class="ta-center">Hatua</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <?php $isIncome = in_array($tx['type'], ['salary','other_income','refund','savings_out','transfer_in'], true); ?>
                    <tr>
                        <td><?= sw_date($tx['transaction_date']) ?></td>
                        <td><span class="pill pill-<?= e($tx['type']) ?>"><?= e($types[$tx['type']] ?? $tx['type']) ?></span></td>
                        <td><?= e($tx['description']) ?></td>
                        <td>
                            <?php if (!empty($tx['category_name'])): ?>
                                <span class="cat-chip" style="background: <?= e($tx['category_color']) ?>20; color: <?= e($tx['category_color']) ?>">
                                    <i class="fa-solid <?= e($tx['category_icon']) ?>"></i> <?= e($tx['category_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="ta-right <?= $isIncome ? 'amt-income' : 'amt-expense' ?>">
                            <?= $isIncome ? '+' : '−' ?><?= money($tx['amount']) ?>
                        </td>
                        <td><?= e($tx['payment_method']) ?></td>
                        <td class="ta-center">
                            <a href="/transactions/<?= (int) $tx['id'] ?>" class="icon-action" title="Tazama"><i class="fa-solid fa-eye"></i></a>
                            <a href="/transactions/<?= (int) $tx['id'] ?>/edit" class="icon-action" title="Hariri"><i class="fa-solid fa-pen"></i></a>
                            <form method="post" action="/transactions/<?= (int) $tx['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa muamala huu?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="icon-action icon-danger" title="Futa"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Ukurasa">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
