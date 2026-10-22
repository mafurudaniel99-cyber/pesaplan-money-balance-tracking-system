<?php /** @var array $goals */
$data['title'] = 'Akiba';
$data['subtitle'] = 'Malengo mengi ya akiba — pamoja na akiba ya dharura.';
?>
<div class="page-toolbar">
    <button class="btn btn-primary" data-modal-open="newGoal"><i class="fa-solid fa-plus"></i> Lengo jipya</button>
</div>

<?php if (empty($goals)): ?>
    <div class="panel"><div class="empty-state">
        <i class="fa-solid fa-piggy-bank empty-icon"></i>
        <h3>Huna malengo ya akiba bado</h3>
        <p>Anza na akiba ya dharura — hiyo ni ulinzo wako wa kwanza dhidi ya deni.</p>
        <button class="btn btn-primary" data-modal-open="newGoal"><i class="fa-solid fa-plus"></i> Ongeza lengo la kwanza</button>
    </div></div>
<?php else: ?>
    <div class="goals-grid">
        <?php foreach ($goals as $g):
            $pct = $g['target_amount'] > 0 ? min(100, ($g['current_amount'] / $g['target_amount']) * 100) : 0;
            $remaining = max(0, (float) $g['target_amount'] - (float) $g['current_amount']);
        ?>
        <article class="panel goal-card">
            <header class="goal-head">
                <div>
                    <h3><?= e($g['name']) ?></h3>
                    <?php if ($g['is_emergency_fund']): ?><span class="badge badge-warning">Akiba ya dharura</span><?php endif; ?>
                </div>
                <div class="goal-actions">
                    <button class="icon-action" data-modal-open="contrib<?= (int) $g['id'] ?>" title="Weka mchango"><i class="fa-solid fa-plus"></i></button>
                    <button class="icon-action" data-modal-open="withdraw<?= (int) $g['id'] ?>" title="Toa akiba"><i class="fa-solid fa-arrow-up-from-bracket"></i></button>
                    <form method="post" action="/savings/<?= (int) $g['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa lengo?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="icon-action icon-danger" title="Futa"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </header>
            <div class="goal-progress">
                <div class="progress">
                    <div class="progress-bar bar-success" style="width: <?= $pct ?>%"></div>
                </div>
                <span class="progress-text"><?= round($pct) ?>%</span>
            </div>
            <dl class="goal-stats">
                <div><dt>Kimefikiwa</dt><dd><?= money($g['current_amount']) ?></dd></div>
                <div><dt>Lengo</dt><dd><?= money($g['target_amount']) ?></dd></div>
                <div><dt>Kilichobaki</dt><dd><?= money($remaining) ?></dd></div>
                <?php if ($g['target_date']): ?>
                <div><dt>Tarehe ya lengo</dt><dd><?= sw_date($g['target_date']) ?></dd></div>
                <?php endif; ?>
            </dl>
            <?php if (!empty($g['contributions'])): ?>
                <details class="contrib-details">
                    <summary>Historia ya michango (<?= count($g['contributions']) ?>)</summary>
                    <ul class="contrib-list">
                        <?php foreach (array_slice($g['contributions'], 0, 10) as $c): ?>
                            <li>
                                <span><?= sw_date($c['contribution_date']) ?></span>
                                <span class="<?= $c['direction']==='in' ? 'amt-income' : 'amt-expense' ?>">
                                    <?= $c['direction']==='in' ? '+' : '−' ?><?= money($c['amount']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal: new goal -->
<div class="modal" id="newGoal" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Lengo jipya la akiba</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/savings" class="modal-body">
            <?= csrf_field() ?>
            <div class="field">
                <label for="name">Jina la lengo</label>
                <input type="text" id="name" name="name" required maxlength="120" placeholder="Mfano: Akiba ya dharura">
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="target_amount">Lengo (TZS)</label>
                    <input type="number" id="target_amount" name="target_amount" min="1" step="1" required>
                </div>
                <div class="field">
                    <label for="target_date">Tarehe ya lengo (hiari)</label>
                    <input type="date" id="target_date" name="target_date">
                </div>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="priority">Kipaumbele (1-10)</label>
                    <input type="number" id="priority" name="priority" min="1" max="10" value="5">
                </div>
                <div class="field">
                    <label class="checkbox-line">
                        <input type="checkbox" name="is_emergency_fund" value="1">
                        <span>Hii ni akiba ya dharura</span>
                    </label>
                </div>
            </div>
            <div class="field">
                <label for="notes">Maelezo (hiari)</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($goals as $g): ?>
<div class="modal" id="contrib<?= (int) $g['id'] ?>" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Mchango wa akiba — <?= e($g['name']) ?></h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/savings/<?= (int) $g['id'] ?>/contribute" class="modal-body">
            <?= csrf_field() ?>
            <div class="field">
                <label for="amount_<?= (int) $g['id'] ?>">Kiasi (TZS)</label>
                <input type="number" id="amount_<?= (int) $g['id'] ?>" name="amount" min="1" step="1" required>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="cdate_<?= (int) $g['id'] ?>">Tarehe</label>
                    <input type="date" id="cdate_<?= (int) $g['id'] ?>" name="contribution_date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="field">
                    <label for="cmethod_<?= (int) $g['id'] ?>">Njia</label>
                    <select id="cmethod_<?= (int) $g['id'] ?>" name="payment_method">
                        <option value="mobile_money">Simu</option>
                        <option value="bank">Benki</option>
                        <option value="cash">Taslimu</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="cdesc_<?= (int) $g['id'] ?>">Maelezo</label>
                <input type="text" id="cdesc_<?= (int) $g['id'] ?>" name="description" value="Mchango wa akiba">
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Weka mchango</button>
            </div>
        </form>
    </div>
</div>
<div class="modal" id="withdraw<?= (int) $g['id'] ?>" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Toa akiba — <?= e($g['name']) ?></h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/savings/<?= (int) $g['id'] ?>/withdraw" class="modal-body">
            <?= csrf_field() ?>
            <p class="muted">Salio la sasa: <?= money($g['current_amount']) ?></p>
            <div class="field">
                <label for="wamount_<?= (int) $g['id'] ?>">Kiasi (TZS)</label>
                <input type="number" id="wamount_<?= (int) $g['id'] ?>" name="amount" min="1" max="<?= (float) $g['current_amount'] ?>" step="1" required>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="wdate_<?= (int) $g['id'] ?>">Tarehe</label>
                    <input type="date" id="wdate_<?= (int) $g['id'] ?>" name="withdrawal_date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="field">
                    <label for="wmethod_<?= (int) $g['id'] ?>">Njia</label>
                    <select id="wmethod_<?= (int) $g['id'] ?>" name="payment_method">
                        <option value="mobile_money">Simu</option>
                        <option value="bank">Benki</option>
                        <option value="cash">Taslimu</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="wdesc_<?= (int) $g['id'] ?>">Maelezo</label>
                <input type="text" id="wdesc_<?= (int) $g['id'] ?>" name="description" value="Kutoa akiba">
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-warning"><i class="fa-solid fa-arrow-up-from-bracket"></i> Toa</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
