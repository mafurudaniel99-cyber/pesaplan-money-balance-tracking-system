<?php
/** @var array $tx $categories */
$data['title'] = 'Hariri muamala';
$data['subtitle'] = 'Marekebisho yatahifadhiwa kwenye kumbukumbu ya ukaguzi.';
?>
<a href="/transactions/<?= (int) $tx['id'] ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> Rudi kwenye muamala</a>

<form method="post" action="/transactions/<?= (int) $tx['id'] ?>" class="form-card panel" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label for="type">Aina ya muamala</label>
        <select id="type" name="type">
            <option value="expense" <?= $tx['type']==='expense'?'selected':'' ?>>Matumizi</option>
            <option value="other_income" <?= $tx['type']==='other_income'?'selected':'' ?>>Mapato mengine</option>
            <option value="refund" <?= $tx['type']==='refund'?'selected':'' ?>>Rudisha pesa</option>
            <option value="salary" <?= $tx['type']==='salary'?'selected':'' ?>>Mshahara</option>
            <option value="savings_in" <?= $tx['type']==='savings_in'?'selected':'' ?>>Mchango wa akiba</option>
            <option value="savings_out" <?= $tx['type']==='savings_out'?'selected':'' ?>>Utoaji wa akiba</option>
            <option value="debt_repay" <?= $tx['type']==='debt_repay'?'selected':'' ?>>Malipo ya deni</option>
            <option value="adjustment" <?= $tx['type']==='adjustment'?'selected':'' ?>>Marekebisho</option>
        </select>
    </div>
    <div class="grid-2">
        <div class="field">
            <label for="amount">Kiasi (TZS)</label>
            <input type="number" id="amount" name="amount" min="1" step="1" required value="<?= e((string) (float) $tx['amount']) ?>">
        </div>
        <div class="field">
            <label for="transaction_date">Tarehe</label>
            <input type="date" id="transaction_date" name="transaction_date" required value="<?= e($tx['transaction_date']) ?>">
        </div>
    </div>
    <div class="field">
        <label for="description">Maelezo</label>
        <input type="text" id="description" name="description" required maxlength="255" value="<?= e($tx['description']) ?>">
    </div>
    <div class="grid-2">
        <div class="field">
            <label for="category_id">Kategoria</label>
            <select id="category_id" name="category_id">
                <option value="">— chagua —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $tx['category_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="payment_method">Njia ya malipo</label>
            <select id="payment_method" name="payment_method">
                <?php foreach (['cash'=>'Taslimu','mobile_money'=>'Simu','bank'=>'Benki','card'=>'Kadi','cheque'=>'Cheki','other'=>'Nyingine'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= $tx['payment_method']===$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="field">
        <label for="notes">Maelezo ya ziada</label>
        <textarea id="notes" name="notes" rows="2" maxlength="500"><?= e($tx['notes'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi marekebisho</button>
        <a href="/transactions/<?= (int) $tx['id'] ?>" class="btn btn-ghost">Ghairi</a>
    </div>
</form>
