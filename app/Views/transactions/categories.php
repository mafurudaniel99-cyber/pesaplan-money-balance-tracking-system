<?php /** @var array $categories */
$data['title'] = 'Kategoria';
$data['subtitle'] = 'Dhibiti kategoria za mapato na matumizi.';
?>
<div class="page-toolbar">
    <button class="btn btn-primary" data-modal-open="newCategory"><i class="fa-solid fa-plus"></i> Kategoria mpya</button>
</div>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>Aina</th>
                <th>Jina</th>
                <th>Ikoni</th>
                <th>Rangi</th>
                <th>Kind</th>
                <th>Hali</th>
                <th class="ta-center">Hatua</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><span class="pill"><?= e($c['type']) ?></span></td>
                    <td><?= e($c['name']) ?> <?= $c['user_id'] === null ? '<span class="muted">(chaguo-msingi)</span>' : '' ?></td>
                    <td><i class="fa-solid <?= e($c['icon']) ?>"></i></td>
                    <td><span class="color-dot" style="background: <?= e($c['color']) ?>"></span> <?= e($c['color']) ?></td>
                    <td><?= e($c['kind']) ?></td>
                    <td>
                        <?php if ($c['is_active']): ?>
                            <span class="badge badge-success">hai</span>
                        <?php else: ?>
                            <span class="badge badge-muted">imezimwa</span>
                        <?php endif; ?>
                    </td>
                    <td class="ta-center">
                        <?php if ($c['user_id'] !== null): ?>
                            <form method="post" action="/categories/<?= (int) $c['id'] ?>/toggle" class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="icon-action" title="Badilisha hali">
                                    <i class="fa-solid <?= $c['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal: New category -->
<div class="modal" id="newCategory" hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="newCatTitle">
        <header class="modal-head">
            <h3 id="newCatTitle">Kategoria mpya</h3>
            <button class="modal-close" data-modal-close aria-label="Funga"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <form method="post" action="/categories" class="modal-body">
            <?= csrf_field() ?>
            <div class="field">
                <label for="name">Jina</label>
                <input type="text" id="name" name="name" required maxlength="80" placeholder="Mfano: Usafiri wa bodaboda">
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="type">Aina</label>
                    <select id="type" name="type">
                        <option value="expense">Matumizi</option>
                        <option value="income">Mapato</option>
                        <option value="savings">Akiba</option>
                        <option value="debt">Deni</option>
                    </select>
                </div>
                <div class="field">
                    <label for="kind">Kind</label>
                    <select id="kind" name="kind">
                        <option value="essential">Muhimu</option>
                        <option value="discretionary">Starehe</option>
                        <option value="obligation">Mjukumu</option>
                        <option value="savings">Akiba</option>
                        <option value="other">Nyingine</option>
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="icon">Ikoni (Font Awesome)</label>
                    <input type="text" id="icon" name="icon" value="fa-tag" placeholder="fa-tag">
                </div>
                <div class="field">
                    <label for="color">Rangi</label>
                    <input type="color" id="color" name="color" value="#4a6fa5">
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Ghairi</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi</button>
            </div>
        </form>
    </div>
</div>
