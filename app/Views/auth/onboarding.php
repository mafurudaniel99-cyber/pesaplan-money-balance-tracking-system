<?php /** Onboarding wizard (single-page, all steps) */
/** @var array $settings */
?>
<div class="onboarding-wrap">
    <div class="onboarding-head">
        <h1><i class="fa-solid fa-compass"></i> Karibu! Hebu tukamilishe usanidi wako</h1>
        <p>Taarifa hizi zitatusaidia kukupa picha ya kwanza ya fedha zako. Unaweza kurudi baadaye kubadilisha chochote.</p>
    </div>
    <form method="post" action="/onboarding" class="onboarding-form">
        <?= csrf_field() ?>

        <fieldset class="step">
            <legend><span class="step-num">1</span> Mshahara wako wa kila mwezi</legend>
            <p class="step-help">Andika kiasi cha mshahara unaotarajia kupokea kila mwezi. Mfumo hauhitaji kiasi mahususi — weka halisi yako.</p>
            <div class="grid-2">
                <div class="field">
                    <label for="expected_salary">Mshahara unayotarajia (TZS)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-money-bill-trend-up input-icon"></i>
                        <input type="number" id="expected_salary" name="expected_salary" min="0" step="1000" value="<?= e((string) ($settings['expected_salary'] ?? 0)) ?>" placeholder="500000">
                    </div>
                </div>
                <div class="field">
                    <label for="expected_salary_day">Siku ya kawaida ya kupokea mshahara</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-calendar-day input-icon"></i>
                        <input type="number" id="expected_salary_day" name="expected_salary_day" min="1" max="28" value="<?= e((string) ($settings['expected_salary_day'] ?? 28)) ?>">
                    </div>
                    <small class="hint">Tarehe 1-28 (tumia 28 kwa mwisho wa mwezi)</small>
                </div>
            </div>
        </fieldset>

        <fieldset class="step">
            <legend><span class="step-num">2</span> Matumizi muhimu yanayorudia (hiari)</legend>
            <p class="step-help">Andika kila mstari mmoja: jina | kiasi. Mfano: <code>Kodi ya nyumba | 150000</code></p>
            <div class="field">
                <label for="recurring">Matumizi yanayorudia kila mwezi</label>
                <textarea id="recurring" name="recurring" rows="4" placeholder="Kodi ya nyumba | 150000&#10;Umeme | 40000&#10;Mawasiliano | 25000"></textarea>
            </div>
        </fieldset>

        <fieldset class="step">
            <legend><span class="step-num">3</span> Madeni (hiari)</legend>
            <p class="step-help">Kwa kila deni: jina | salio | malipo ya chini. Mfano: <code>Mkopo wa SOSL | 200000 | 25000</code></p>
            <div class="field">
                <label for="debts">Madeni yako (kama yapo)</label>
                <textarea id="debts" name="debts" rows="3" placeholder="Mkopo wa chama | 300000 | 30000"></textarea>
            </div>
        </fieldset>

        <fieldset class="step">
            <legend><span class="step-num">4</span> Lengo la akiba (hiari)</legend>
            <p class="step-help">Kama una lengo la kwanza la akiba, andika kiasi hapa. Mfumo utaunda lengo la "Akiba ya kuanzia".</p>
            <div class="field">
                <label for="savings_target">Lengo la akiba (TZS)</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-piggy-bank input-icon"></i>
                    <input type="number" id="savings_target" name="savings_target" min="0" step="1000" placeholder="500000">
                </div>
            </div>
        </fieldset>

        <div class="onboarding-actions">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-check"></i> Maliza usanidi na anza</button>
            <p class="onboarding-note">Unaweza kurudi kwenye Mipangilio wakati wowote kubadilisha taarifa hizi.</p>
        </div>
    </form>
</div>
