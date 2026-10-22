<?php /** @var array $debts $obligations $categories */
$data['title'] = 'Madeni & Majukumu';
$data['subtitle'] = 'Fuata deni na bili zinazorudia.';
?>
<div class="page-toolbar">
    <button class="btn btn-primary" data-modal-open="newDebt"><i class="fa-solid fa-plus"></i> Deni jipya</button>
    <button class="btn btn-ghost" data-modal-open="newObligation"><i class="fa-solid fa-plus"></i> Mjukumu mpya</button>
</div>

<section>
    <h2 class="section-title"><i class="fa-solid fa-hand-holding-dollar"></i> Madeni</h2>
    <?php if (empty($debts)): ?>
        <div class="panel"><div class="empty-state-inline">
            <p>Huna madeni yaliyoandikwa. <button class="btn btn-link" data-modal-open="newDebt">Ongeza deni</button></p>
        </div></div>
    <?php else: ?>
        <div class="debts-grid">
            <?php foreach ($debts as $d):
                $pctPaid = $d['original_amount'] > 0 ? (1 - $d['outstanding_balance'] / $d['original_amount']) * 100 : 0;
            ?>
            <article class="panel debt-card">
                <header class="debt-head">
                    <div>
                        <h3><?= e($d['name']) ?></h3>
                        <?php if ($d['is_paid_off']): ?><span class="badge badge-success">Limekwisha</span><?php endif; ?>
                        <?php if ($d['interest_rate_pct'] > 0): ?><span class="badge badge-warning">Riba <?= e($d['interest_rate_pct']) ?>%</span><?php endif; ?>
                    </div>
                    <form method="post" action="/debts/<?= (int) $d['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa deni?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="icon-action icon-danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </header>
                <dl class="debt-stats">
                    <div><dt>Salio</dt><dd><?= money($d['outstanding_balance']) ?></dd></div>
                    <div><dt>Asili</dt><dd><?= money($d['original_amount']) ?></dd></div>
                    <div><dt>Malipo ya chini</dt><dd><?= money($d['minimum_payment']) ?></dd></div>
                    <?php if ($d['next_due_date']): ?><div><dt>Tarehe ijayo</dt><dd><?= sw_date($d['next_due_date']) ?></dd></div><?php endif; ?>
                </dl>
                <div class="progress">
                    <div class="progress-bar bar-success" style="width: <?= $pctPaid ?>%"></div>
                </div>
                <p class="muted">Limekwisha: <?= round($pctPaid) ?>%</p>
                <?php if (!$d['is_paid_off']): ?>
                    <button class="btn btn-primary btn-sm btn-block" data-modal-open="pay<?= (int) $d['id'] ?>"><i class="fa-solid fa-credit-card"></i> Lipa</button>
                <?php endif; ?>
                <?php if (!empty($d['payments'])): ?>
                    <details class="contrib-details">
                        <summary>Historia ya malipo (<?= count($d['payments']) ?>)</summary>
                        <ul class="contrib-list">
                            <?php foreach (array_slice($d['payments'], 0, 10) as $p): ?>
                                <li>
                                    <span><?= sw_date($p['payment_date']) ?></span>
                                    <span class="amt-expense">−<?= money($p['amount']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <h2 class="section-title"><i class="fa-solid fa-calendar-day"></i> Majukumu (bili zinazorudia)</h2>
    <?php if (empty($obligations)): ?>
        <div class="panel"><div class="empty-state-inline">
            <p>Huna majukumu yaliyoandikwa. <button class="btn btn-link" data-modal-open="newObligation">Ongeza mjukumu</button></p>
        </div></div>
    <?php else: ?>
        <div class="panel">
            <table class="data-table">
                <thead>
                    <tr><th>Jina</th><th class="ta-right">Kiasi</th><th>Tarehe ijayo</th><th>Hali</th><th class="ta-center">Hatua</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($obligations as $o): ?>
                    <tr>
                        <td><?= e($o['name']) ?> <?= $o['is_recurring'] ? '<span class="badge badge-info">Inarudia</span>' : '' ?></td>
                        <td class="ta-right"><?= money($o['amount']) ?></td>
                        <td><?= $o['next_due_date'] ? sw_date($o['next_due_date']) : '<span class="muted">—</span>' ?></td>
                        <td><?= $o['is_paid'] ? '<span class="badge badge-success">Limekwisha</span>' : '<span class="badge badge-warning">Bado</span>' ?></td>
                        <td class="ta-center">
                            <?php if (!$o['is_paid']): ?>
                                <form method="post" action="/obligations/<?= (int) $o['id'] ?>/mark-paid" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="icon-action" title="Weka kama umelipwa"><i class="fa-solid fa-check"></i></button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="/obligations/<?= (int) $o['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="icon-action icon-danger" title="Futa"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Modals -->
<div class="modal" id="newDebt" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Deni jipya</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/debts" class="modal-body">
            <?= csrf_field() ?>
            <div class="field"><label for="dname">Jina la deni</label><input type="text" id="dname" name="name" required placeholder="Mkopo wa SOSL"></div>
            <div class="grid-2">
                <div class="field"><label for="obalance">Salio la sasa (TZS)</label><input type="number" id="obalance" name="outstanding_balance" min="0" step="1" required></div>
                <div class="field"><label for="orig">Asili (TZS, hiari)</label><input type="number" id="orig" name="original_amount" min="0" step="1"></div>
            </div>
            <div class="grid-2">
                <div class="field"><label for="minpay">Malipo ya chini (TZS)</label><input type="number" id="minpay" name="minimum_payment" min="0" step="1"></div>
                <div class="field"><label for="rate">Riba kwa mwaka (%)</label><input type="number" id="rate" name="interest_rate_pct" min="0" step="0.1" value="0"></div>
            </div>
            <div class="grid-2">
                <div class="field"><label for="ddue">Siku ya malipo (1-28)</label><input type="number" id="ddue" name="due_day" min="1" max="28"></div>
                <div class="field"><label for="ndue">Tarehe ijayo</label><input type="date" id="ndue" name="next_due_date"></div>
            </div>
            <div class="field"><label for="dnotes">Maelezo</label><textarea id="dnotes" name="notes" rows="2"></textarea></div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="newObligation" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Mjukumu mpya (bili)</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/obligations" class="modal-body">
            <?= csrf_field() ?>
            <div class="field"><label for="oname">Jina</label><input type="text" id="oname" name="name" required placeholder="Kodi ya nyumba"></div>
            <div class="grid-2">
                <div class="field"><label for="oamt">Kiasi (TZS)</label><input type="number" id="oamt" name="amount" min="1" step="1" required></div>
                <div class="field"><label for="oddue">Siku ya mwisho (1-28)</label><input type="number" id="oddue" name="due_day" min="1" max="28"></div>
            </div>
            <div class="field"><label for="onext">Tarehe ijayo</label><input type="date" id="onext" name="next_due_date"></div>
            <div class="field">
                <label class="checkbox-line"><input type="checkbox" name="is_recurring" value="1" checked><span>Inarudia kila mwezi</span></label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($debts as $d): if ($d['is_paid_off']) continue; ?>
<div class="modal" id="pay<?= (int) $d['id'] ?>" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Lipa deni — <?= e($d['name']) ?></h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/debts/<?= (int) $d['id'] ?>/pay" class="modal-body">
            <?= csrf_field() ?>
            <p class="muted">Salio: <?= money($d['outstanding_balance']) ?> · Malipo ya chini: <?= money($d['minimum_payment']) ?></p>
            <div class="field"><label for="pamt_<?= (int) $d['id'] ?>">Kiasi (TZS)</label><input type="number" id="pamt_<?= (int) $d['id'] ?>" name="amount" min="1" step="1" required></div>
            <div class="grid-2">
                <div class="field"><label for="pdate_<?= (int) $d['id'] ?>">Tarehe</label><input type="date" id="pdate_<?= (int) $d['id'] ?>" name="payment_date" required value="<?= date('Y-m-d') ?>"></div>
                <div class="field"><label for="pmethod_<?= (int) $d['id'] ?>">Njia</label><select id="pmethod_<?= (int) $d['id'] ?>" name="payment_method"><option value="bank">Benki</option><option value="mobile_money">Simu</option><option value="cash">Taslimu</option></select></div>
            </div>
            <div class="field"><label for="pint_<?= (int) $d['id'] ?>">Sehemu ya riba (TZS, hiari)</label><input type="number" id="pint_<?= (int) $d['id'] ?>" name="interest_portion" min="0" step="1"></div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Lipa</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
