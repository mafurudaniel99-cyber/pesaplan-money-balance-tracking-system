<?php /** @var array $incomes */
$data['title'] = 'Mapato';
$data['subtitle'] = 'Dhibiti mshahara na mapato mengine.';
?>
<div class="page-toolbar">
    <button class="btn btn-primary" data-modal-open="newIncome"><i class="fa-solid fa-plus"></i> Ongeza mapato</button>
</div>

<div class="panel">
    <?php if (empty($incomes)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-money-bill-trend-up empty-icon"></i>
            <h3>Hakuna mapato yaliyoandikwa</h3>
            <p>Anza kwa kuongeza mshahara wako unaotarajiwa kwa mwezi huu.</p>
            <button class="btn btn-primary" data-modal-open="newIncome"><i class="fa-solid fa-plus"></i> Ongeza mapato</button>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Chanzo</th>
                    <th>Tarehe iliyotarajiwa</th>
                    <th class="ta-right">Iliyotarajiwa</th>
                    <th>Tarehe iliyopokelewa</th>
                    <th class="ta-right">Iliyopokelewa</th>
                    <th>Hali</th>
                    <th class="ta-center">Hatua</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incomes as $inc): ?>
                    <tr>
                        <td>
                            <?= e($inc['source']) ?>
                            <?php if ($inc['is_salary']): ?><span class="badge badge-info">Mshahara</span><?php endif; ?>
                        </td>
                        <td><?= sw_date($inc['expected_date']) ?></td>
                        <td class="ta-right"><?= money($inc['expected_amount']) ?></td>
                        <td><?= $inc['received_date'] ? sw_date($inc['received_date']) : '<span class="muted">—</span>' ?></td>
                        <td class="ta-right"><?= $inc['is_received'] ? money($inc['received_amount']) : '<span class="muted">—</span>' ?></td>
                        <td>
                            <?php if ($inc['is_received']): ?>
                                <span class="badge badge-success">Imepokelewa</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Inasubiri</span>
                            <?php endif; ?>
                        </td>
                        <td class="ta-center">
                            <?php if (!$inc['is_received']): ?>
                                <button class="icon-action" data-modal-open="receive<?= (int) $inc['id'] ?>" title="Weka kama imepokelewa"><i class="fa-solid fa-check"></i></button>
                            <?php endif; ?>
                            <form method="post" action="/income/<?= (int) $inc['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="icon-action icon-danger" title="Futa"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal: new income -->
<div class="modal" id="newIncome" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Ongeza mapato</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/income" class="modal-body">
            <?= csrf_field() ?>
            <div class="field">
                <label for="source">Chanzo</label>
                <input type="text" id="source" name="source" required maxlength="120" value="Mshahara">
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="expected_amount">Kiasi kinachotarajiwa (TZS)</label>
                    <input type="number" id="expected_amount" name="expected_amount" min="0" step="1" required>
                </div>
                <div class="field">
                    <label for="expected_date">Tarehe inayotarajiwa</label>
                    <input type="date" id="expected_date" name="expected_date" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="field">
                <label class="checkbox-line">
                    <input type="checkbox" name="is_salary" value="1" checked>
                    <span>Hii ni mshahara wangu wa kila mwezi</span>
                </label>
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

<?php foreach ($incomes as $inc): if ($inc['is_received']) continue; ?>
<div class="modal" id="receive<?= (int) $inc['id'] ?>" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Weka mapato kama yamepokelewa</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/income/<?= (int) $inc['id'] ?>/receive" class="modal-body">
            <?= csrf_field() ?>
            <div class="field">
                <label for="received_amount_<?= (int) $inc['id'] ?>">Kiasi kilichopokelewa (TZS)</label>
                <input type="number" id="received_amount_<?= (int) $inc['id'] ?>" name="received_amount" min="0" step="1" required value="<?= e((string) (float) $inc['expected_amount']) ?>">
            </div>
            <div class="field">
                <label for="received_date_<?= (int) $inc['id'] ?>">Tarehe iliyopokelewa</label>
                <input type="date" id="received_date_<?= (int) $inc['id'] ?>" name="received_date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="field">
                <label for="description_<?= (int) $inc['id'] ?>">Maelezo (hiari)</label>
                <input type="text" id="description_<?= (int) $inc['id'] ?>" name="description" value="<?= e($inc['source']) ?> umepokelewa">
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Thibitisha kupokelewa</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
