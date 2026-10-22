<?php /** @var array $purchases $categories */
$data['title'] = 'Kagua Kabla ya Kutumia';
$data['subtitle'] = 'Fikiria kabla ya ununuzi. Kila uamuzi wa fedha uwe na sababu.';
?>
<div class="page-toolbar">
    <button class="btn btn-primary" data-modal-open="newPurchase"><i class="fa-solid fa-plus"></i> Ongeza ununuzi wa kufikiri</button>
</div>

<div class="alert alert-info">
    <i class="fa-solid fa-circle-info"></i>
    Kipengele hiki kinakusaidia kufikiria mara mbili kabla ya ununuzi wa starehe. Mfumo utaonyesha kama ununuzi unakubaliana na bajeti na fedha ulizonazo. Haukuzu ununuzi — unakupa taarifa.
</div>

<?php if (empty($purchases)): ?>
    <div class="panel"><div class="empty-state">
        <i class="fa-solid fa-magnifying-glass-chart empty-icon"></i>
        <h3>Hujaweka ununuzi wowote wa kufikiri</h3>
        <p>Kabla ya nunua kitu cha starehe, kiandike hapa kwanza. Mfumo utakupa mapendekezo.</p>
        <button class="btn btn-primary" data-modal-open="newPurchase"><i class="fa-solid fa-plus"></i> Ongeza</button>
    </div></div>
<?php else: ?>
    <div class="purchases-grid">
        <?php foreach ($purchases as $p):
            $statusLabels = [
                'cooling' => ['Kipindi cha kufikiri', 'badge-warning', 'fa-hourglass-half'],
                'pending' => ['Inasubiri', 'badge-info', 'fa-pause'],
                'approved' => ['Imeidhinishwa', 'badge-success', 'fa-check'],
                'rejected' => ['Imekataliwa', 'badge-muted', 'fa-xmark'],
                'completed' => ['Imekamilika', 'badge-info', 'fa-check-double'],
            ];
            $sl = $statusLabels[$p['status']] ?? ['?', 'badge-muted', 'fa-question'];
        ?>
        <article class="panel purchase-card">
            <header class="purchase-head">
                <div>
                    <h3><?= e($p['item_name']) ?></h3>
                    <span class="badge <?= $sl[1] ?>"><i class="fa-solid <?= $sl[2] ?>"></i> <?= e($sl[0]) ?></span>
                </div>
                <form method="post" action="/planned-purchases/<?= (int) $p['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="icon-action icon-danger"><i class="fa-solid fa-trash"></i></button>
                </form>
            </header>
            <p class="purchase-cost"><?= money($p['proposed_cost']) ?></p>
            <dl class="purchase-meta">
                <div><dt>Sababu</dt><dd><?= e($p['reason']) ?></dd></div>
                <div><dt>Aina</dt><dd><?= e($p['classification']) ?><?= $p['can_postpone'] ? ' (inasubiriwa)' : '' ?></dd></div>
                <div><dt>Tarehe iliyopendekezwa</dt><dd><?= sw_date($p['proposed_date']) ?></dd></div>
                <?php if ($p['category_name']): ?><div><dt>Kategoria</dt><dd><?= e($p['category_name']) ?></dd></div><?php endif; ?>
            </dl>
            <?php if ($p['decision_result']): ?>
                <div class="alert alert-info inline-alert">
                    <strong><?= e($p['decision_result']) ?></strong>
                    <?php if ($p['decision_explanation']): ?><p><?= e($p['decision_explanation']) ?></p><?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="purchase-actions">
                <?php if ($p['status'] !== 'approved' && $p['status'] !== 'completed'): ?>
                    <form method="post" action="/planned-purchases/<?= (int) $p['id'] ?>/approve" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Idhinisha</button>
                    </form>
                <?php endif; ?>
                <?php if ($p['status'] !== 'rejected' && $p['status'] !== 'completed'): ?>
                    <form method="post" action="/planned-purchases/<?= (int) $p['id'] ?>/reject" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-xmark"></i> Kataa</button>
                    </form>
                <?php endif; ?>
                <?php if ($p['status'] === 'approved'): ?>
                    <form method="post" action="/planned-purchases/<?= (int) $p['id'] ?>/complete" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-check-double"></i> Imekamilika</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal" id="newPurchase" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Ongeza ununuzi wa kufikiri</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/planned-purchases" class="modal-body">
            <?= csrf_field() ?>
            <div class="field"><label for="item_name">Bidhaa au huduma</label><input type="text" id="item_name" name="item_name" required placeholder="Mfano: Kiatu kipya"></div>
            <div class="grid-2">
                <div class="field"><label for="proposed_cost">Bei inayopendekezwa (TZS)</label><input type="number" id="proposed_cost" name="proposed_cost" min="1" step="1" required></div>
                <div class="field"><label for="proposed_date">Tarehe inayopendekezwa</label><input type="date" id="proposed_date" name="proposed_date" required value="<?= date('Y-m-d') ?>"></div>
            </div>
            <div class="field"><label for="reason">Sababu ya ununuzi</label><textarea id="reason" name="reason" rows="2" required placeholder="Mfano: Viatu vya zamani vimechanika kabisa"></textarea></div>
            <div class="grid-2">
                <div class="field">
                    <label for="classification">Uainishaji</label>
                    <select id="classification" name="classification">
                        <option value="essential">Muhimu (hitaji)</option>
                        <option value="useful">Muhimu (manufaa)</option>
                        <option value="discretionary" selected>Starehe (tamani)</option>
                    </select>
                </div>
                <div class="field">
                    <label for="category_id">Kategoria</label>
                    <select id="category_id" name="category_id">
                        <option value="">— hiari —</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="field">
                <label class="checkbox-line"><input type="checkbox" name="can_postpone" value="1" checked><span>Ununuzi huu unaweza kusubiri</span></label>
            </div>
            <div class="field"><label for="ppnotes">Maelezo</label><textarea id="ppnotes" name="notes" rows="2"></textarea></div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi kwa ajili ya kufikiri</button>
            </div>
        </form>
    </div>
</div>
