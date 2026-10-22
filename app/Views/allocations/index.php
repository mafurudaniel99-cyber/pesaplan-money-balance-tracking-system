<?php /** @var array $allocations $totalsByPurpose $incomes $categories */
$data['title'] = 'Ugawaji wa Fedha';
$data['subtitle'] = 'Kila shilingi iwe na kusudi. Hii ni ugawaji wa kimantiki, si hamisho la benki.';
?>
<div class="page-toolbar">
    <button class="btn btn-primary" data-modal-open="newAlloc"><i class="fa-solid fa-plus"></i> Ugawaji mpya</button>
</div>

<div class="alert alert-info">
    <i class="fa-solid fa-circle-info"></i>
    Ugawaji huu ni kimantiki tu — unaonyesha jinsi umepanga kutumia mapato yako. Haisimami hamisho la benki. Unaweza kuufuata au kubadilisha wakati wowote.
</div>

<?php if (!empty($totalsByPurpose)): ?>
<section class="cards-grid cards-3">
    <?php foreach ($totalsByPurpose as $t): 
        $labels = [
            'essential' => ['Mahitaji muhimu', 'fa-basket-shopping', '#3d5a80'],
            'rent_bills' => ['Kodi & bili', 'fa-house', '#e07a5f'],
            'debt' => ['Madeni', 'fa-hand-holding-dollar', '#7f8c8d'],
            'emergency_savings' => ['Akiba ya dharura', 'fa-shield-halved', '#16a085'],
            'other_savings' => ['Akiba nyingine', 'fa-piggy-bank', '#27ae60'],
            'development' => ['Maendeleo', 'fa-seedling', '#2c3e50'],
            'discretionary' => ['Starehe', 'fa-music', '#d35400'],
            'other' => ['Nyingine', 'fa-ellipsis', '#7f8c8d'],
        ];
        $l = $labels[$t['purpose']] ?? ['Nyingine', 'fa-ellipsis', '#7f8c8d'];
    ?>
    <article class="card">
        <div class="card-head">
            <span class="card-icon" style="color: <?= $l[2] ?>"><i class="fa-solid <?= $l[1] ?>"></i></span>
            <span class="card-title"><?= e($l[0]) ?></span>
        </div>
        <p class="card-value"><?= money($t['total']) ?></p>
    </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<div class="panel">
    <?php if (empty($allocations)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-folder-tree empty-icon"></i>
            <h3>Hujagawa mapato yako bado</h3>
            <p>Baada ya kupokea mshahara, gawa kwa kusudi: mahitaji, majukumu, akiba, na starehe.</p>
            <button class="btn btn-primary" data-modal-open="newAlloc"><i class="fa-solid fa-plus"></i> Anza ugawaji</button>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tarehe</th>
                    <th>Chanzo</th>
                    <th>Kusudi</th>
                    <th class="ta-right">Kiasi</th>
                    <th>Maelezo</th>
                    <th class="ta-center">Hatua</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allocations as $a): ?>
                <tr>
                    <td><?= sw_date($a['allocation_date']) ?></td>
                    <td><?= e($a['income_source'] ?? '—') ?></td>
                    <td><span class="badge badge-info"><?= e($a['purpose']) ?></span></td>
                    <td class="ta-right"><?= money($a['allocated_amount']) ?></td>
                    <td><?= e($a['notes'] ?? '—') ?></td>
                    <td class="ta-center">
                        <form method="post" action="/allocations/<?= (int) $a['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Futa?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="icon-action icon-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="modal" id="newAlloc" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true">
        <header class="modal-head"><h3>Ugawaji mpya</h3><button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></header>
        <form method="post" action="/allocations" class="modal-body">
            <?= csrf_field() ?>
            <div class="field">
                <label for="income_id">Mapato (chagua yaliyopokelewa)</label>
                <select id="income_id" name="income_id">
                    <option value="">— hiari —</option>
                    <?php foreach ($incomes as $i): if (!$i['is_received']) continue; ?>
                        <option value="<?= (int) $i['id'] ?>"><?= e($i['source']) ?> — <?= money($i['received_amount']) ?> (<?= sw_date($i['received_date']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <small class="hint">Ugawaji unaweza kufanywa dhidi ya mapato yaliyopokelewa tu.</small>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="purpose">Kusudi</label>
                    <select id="purpose" name="purpose" required>
                        <option value="essential">Mahitaji muhimu</option>
                        <option value="rent_bills">Kodi & bili</option>
                        <option value="debt">Madeni</option>
                        <option value="emergency_savings">Akiba ya dharura</option>
                        <option value="other_savings">Akiba nyingine</option>
                        <option value="development">Maendeleo</option>
                        <option value="discretionary">Starehe</option>
                        <option value="other">Nyingine</option>
                    </select>
                </div>
                <div class="field">
                    <label for="allocated_amount">Kiasi (TZS)</label>
                    <input type="number" id="allocated_amount" name="allocated_amount" min="1" step="1" required>
                </div>
            </div>
            <div class="field">
                <label for="alloc_date">Tarehe</label>
                <input type="date" id="alloc_date" name="allocation_date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="field">
                <label for="alloc_notes">Maelezo</label>
                <textarea id="alloc_notes" name="notes" rows="2"></textarea>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi</button>
            </div>
        </form>
    </div>
</div>
