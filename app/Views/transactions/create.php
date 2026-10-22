<?php
/** @var array $categories $incomeCategories */
$data['title'] = 'Ongeza muamala';
$data['subtitle'] = 'Andika muamala wa kifedha — mapato, matumizi, au rudisha pesa.';
?>
<a href="/transactions" class="back-link"><i class="fa-solid fa-arrow-left"></i> Rudi kwenye miamala</a>

<form method="post" action="/transactions" class="form-card panel" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label>Aina ya muamala</label>
        <div class="radio-grid">
            <label class="radio-card">
                <input type="radio" name="type" value="expense" checked>
                <span class="radio-body">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                    <strong>Matumizi</strong>
                    <small>Pesa iliyotoka</small>
                </span>
            </label>
            <label class="radio-card">
                <input type="radio" name="type" value="other_income">
                <span class="radio-body">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    <strong>Mapato mengine</strong>
                    <small>Isiyo mshahara</small>
                </span>
            </label>
            <label class="radio-card">
                <input type="radio" name="type" value="refund">
                <span class="radio-body">
                    <i class="fa-solid fa-rotate-left"></i>
                    <strong>Rudisha pesa</strong>
                    <small>Rudi ya pesa uliyotumia</small>
                </span>
            </label>
        </div>
    </div>
    <div class="grid-2">
        <div class="field">
            <label for="amount">Kiasi (TZS) <span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fa-solid fa-money-bill input-icon"></i>
                <input type="number" id="amount" name="amount" min="1" step="1" required placeholder="5000">
            </div>
        </div>
        <div class="field">
            <label for="transaction_date">Tarehe <span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fa-solid fa-calendar input-icon"></i>
                <input type="date" id="transaction_date" name="transaction_date" required value="<?= date('Y-m-d') ?>">
            </div>
        </div>
    </div>
    <div class="field">
        <label for="description">Maelezo <span class="req">*</span></label>
        <input type="text" id="description" name="description" required maxlength="255" placeholder="Mfano: Chakula cha mchana">
    </div>
    <div class="grid-2">
        <div class="field">
            <label for="category_id">Kategoria</label>
            <select id="category_id" name="category_id">
                <option value="">— chagua —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" data-kind="<?= e($c['kind']) ?>">
                        <i class="fa-solid <?= e($c['icon']) ?>"></i> <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="payment_method">Njia ya malipo</label>
            <select id="payment_method" name="payment_method">
                <option value="cash">Taslimu</option>
                <option value="mobile_money">Simu (M-Pesa, Tigo Pesa, etc.)</option>
                <option value="bank">Benki</option>
                <option value="card">Kadi</option>
                <option value="cheque">Cheki</option>
                <option value="other">Nyingine</option>
            </select>
        </div>
    </div>
    <div class="field">
        <label for="notes">Maelezo ya ziada (hiari)</label>
        <textarea id="notes" name="notes" rows="2" maxlength="500" placeholder="Maelezo yoyote ya ziada..."></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi muamala</button>
        <a href="/transactions" class="btn btn-ghost">Ghairi</a>
    </div>
</form>

<script src="/assets/js/transactions.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/transactions.js') ?>"></script>
