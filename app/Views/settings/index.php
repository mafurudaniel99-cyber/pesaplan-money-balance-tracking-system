<?php /** @var array $user $settings */
$data['title'] = 'Mipangilio';
$data['subtitle'] = 'Badilisha taarifa zako na mipangilio ya mfumo.';
?>
<div class="settings-grid">
    <form method="post" action="/settings" class="panel form-card">
        <header class="panel-head"><h2><i class="fa-solid fa-user"></i> Taarifa za akaunti</h2></header>
        <?= csrf_field() ?>
        <div class="field">
            <label for="name">Jina</label>
            <input type="text" id="name" name="name" required value="<?= e($user['name']) ?>">
        </div>
        <div class="field">
            <label for="email">Barua pepe (haiwezi kubadilishwa)</label>
            <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>
        </div>
        <div class="field">
            <label for="phone">Simu (hiari)</label>
            <input type="tel" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi</button>
    </form>

    <form method="post" action="/settings" class="panel form-card">
        <header class="panel-head"><h2><i class="fa-solid fa-coins"></i> Mipangilio ya fedha</h2></header>
        <?= csrf_field() ?>
        <div class="grid-2">
            <div class="field">
                <label for="expected_salary">Mshahara unayotarajiwa (TZS)</label>
                <input type="number" id="expected_salary" name="expected_salary" min="0" step="1000" value="<?= e((string) (float) $settings['expected_salary']) ?>">
            </div>
            <div class="field">
                <label for="expected_salary_day">Siku ya mshahara (1-28)</label>
                <input type="number" id="expected_salary_day" name="expected_salary_day" min="1" max="28" value="<?= e((string) $settings['expected_salary_day']) ?>">
            </div>
        </div>
        <div class="grid-2">
            <div class="field">
                <label for="cooling_off_hours">Kipindi cha kufikiri (saa)</label>
                <input type="number" id="cooling_off_hours" name="cooling_off_hours" min="0" max="168" value="<?= e((string) $settings['cooling_off_hours']) ?>">
                <small class="hint">Kwa ununuzi wa starehe — kipindi cha kusubiri kabla ya kuamua.</small>
            </div>
            <div class="field">
                <label for="budget_threshold_pct">Asilimia ya onyo la bajeti (%)</label>
                <input type="number" id="budget_threshold_pct" name="budget_threshold_pct" min="50" max="100" value="<?= e((string) $settings['budget_threshold_pct']) ?>">
            </div>
        </div>
        <fieldset class="checklist">
            <legend>Arifa</legend>
            <label class="checkbox-line"><input type="checkbox" name="notify_budget_threshold" value="1" <?= !empty($settings['notify_budget_threshold'])?'checked':'' ?>><span>Onyo la bajeti inakaribia kikomo</span></label>
            <label class="checkbox-line"><input type="checkbox" name="notify_upcoming_bills" value="1" <?= !empty($settings['notify_upcoming_bills'])?'checked':'' ?>><span>Bili na majukumu yanakaribia</span></label>
            <label class="checkbox-line"><input type="checkbox" name="notify_review_reminders" value="1" <?= !empty($settings['notify_review_reminders'])?'checked':'' ?>><span>Kumbusho la ukaguzi</span></label>
        </fieldset>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Hifadhi mipangilio</button>
    </form>

    <form method="post" action="/settings/password" class="panel form-card">
        <header class="panel-head"><h2><i class="fa-solid fa-key"></i> Badilisha nenosiri</h2></header>
        <?= csrf_field() ?>
        <div class="field">
            <label for="current_password">Nenosiri la sasa</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="field">
            <label for="new_password">Nenosiri jipya (8+)</label>
            <input type="password" id="new_password" name="new_password" required minlength="8">
        </div>
        <div class="field">
            <label for="new_password_confirm">Thibitisha nenosiri jipya</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Badilisha</button>
    </form>

    <form method="post" action="/settings/delete-account" class="panel form-card danger-zone" onsubmit="return confirm('Una uhakika unataka kufuta akaunti yako? Taarifa zote zitapotea.')">
        <header class="panel-head"><h2><i class="fa-solid fa-triangle-exclamation"></i> Futa akaunti</h2></header>
        <?= csrf_field() ?>
        <p class="muted">Ufutaji wa akaunti ni wa kudumu. Taarifa zote zitafutika. Hakuna uwezekano wa kurudisha.</p>
        <div class="field">
            <label for="confirm">Andika "FUTA" kuthibitisha</label>
            <input type="text" id="confirm" name="confirm" required pattern="FUTA" title="Andika FUTA">
        </div>
        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Futa akaunti yangu</button>
    </form>
</div>
