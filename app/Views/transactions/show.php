<?php /** @var array $tx */
$data['title'] = 'Maelezo ya muamala';
$data['subtitle'] = 'Muamala #' . (int) $tx['id'];
?>
<a href="/transactions" class="back-link"><i class="fa-solid fa-arrow-left"></i> Rudi kwenye miamala</a>

<div class="detail-card panel">
    <div class="detail-head">
        <span class="cat-chip" style="background: <?= e($tx['category_color'] ?? '#4a6fa5') ?>20; color: <?= e($tx['category_color'] ?? '#4a6fa5') ?>">
            <i class="fa-solid <?= e($tx['category_icon'] ?? 'fa-tag') ?>"></i> <?= e($tx['category_name'] ?? '—') ?>
        </span>
        <span class="pill pill-<?= e($tx['type']) ?>"><?= e($tx['type']) ?></span>
    </div>
    <h2 class="detail-title"><?= e($tx['description']) ?></h2>
    <p class="detail-amount <?= in_array($tx['type'], ['salary','other_income','refund','savings_out','transfer_in'], true) ? 'amt-income' : 'amt-expense' ?>">
        <?= in_array($tx['type'], ['salary','other_income','refund','savings_out','transfer_in'], true) ? '+' : '−' ?><?= money($tx['amount']) ?>
    </p>
    <dl class="detail-grid">
        <div><dt>Tarehe</dt><dd><?= sw_date($tx['transaction_date']) ?></dd></div>
        <div><dt>Njia ya malipo</dt><dd><?= e($tx['payment_method']) ?></dd></div>
        <div><dt>Imeundwa</dt><dd><?= sw_date($tx['created_at'], 'j F Y H:i') ?></dd></div>
        <div><dt>Imerekebishwa</dt><dd><?= sw_date($tx['updated_at'], 'j F Y H:i') ?></dd></div>
    </dl>
    <?php if (!empty($tx['notes'])): ?>
        <div class="detail-notes">
            <h4>Maelezo ya ziada</h4>
            <p><?= e($tx['notes']) ?></p>
        </div>
    <?php endif; ?>
    <div class="detail-actions">
        <a href="/transactions/<?= (int) $tx['id'] ?>/edit" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Hariri</a>
        <form method="post" action="/transactions/<?= (int) $tx['id'] ?>/delete" onsubmit="return confirm('Futa muamala huu?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Futa</button>
        </form>
    </div>
</div>
