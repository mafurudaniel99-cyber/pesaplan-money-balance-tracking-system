<?php
/** @var string $today $weekLabel $monthLabel */
/** @var array $dailyChecklist $weeklyChecklist $monthlyChecklist */
/** @var ?array $todayReview $weekReview $monthReview */
/** @var array $past */
function renderChecklist(string $type, array $checklist, ?array $existing, string $dateVal, string $periodLabel, string $title, string $icon): void {
    $items = $existing ? json_decode($existing['checklist'], true) : [];
    $isComplete = $existing && $existing['is_completed'];
    ?>
    <article class="panel review-card <?= $isComplete ? 'review-done' : '' ?>">
        <header class="panel-head">
            <h2><i class="fa-solid <?= $icon ?>"></i> <?= e($title) ?></h2>
            <?php if ($isComplete): ?>
                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Umekamilika</span>
            <?php endif; ?>
        </header>
        <form method="post" action="/reviews" class="review-form">
            <?= csrf_field() ?>
            <input type="hidden" name="review_type" value="<?= e($type) ?>">
            <input type="hidden" name="review_date" value="<?= e($dateVal) ?>">
            <input type="hidden" name="period_label" value="<?= e($periodLabel) ?>">
            <ul class="checklist">
                <?php foreach ($checklist as $i => $label):
                    $done = $items[$i]['done'] ?? false;
                ?>
                <li>
                    <label class="checkbox-line">
                        <input type="checkbox" name="items[<?= $i ?>]" value="1" <?= $done ? 'checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="field">
                <label for="notes_<?= e($type) ?>">Maelezo yako</label>
                <textarea id="notes_<?= e($type) ?>" name="notes" rows="2" placeholder="Mambo ya kumbukumbu..."><?= e($existing['notes'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Hifadhi</button>
                <?php if ($existing && !$isComplete): ?>
                    <button type="submit" formaction="/reviews/<?= (int) $existing['id'] ?>/complete" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Weka kama umekamilika</button>
                <?php endif; ?>
            </div>
        </form>
    </article>
    <?php
}
$data['title'] = 'Ukaguzi wa Fedha';
$data['subtitle'] = 'Mazoea ya kila siku, wiki, na mwezi hujenga nidhamu ya muda mrefu.';
?>
<div class="alert alert-info">
    <i class="fa-solid fa-circle-info"></i>
    Ukaguzi wa kila siku: ~5 dakika · kila wiki: ~15-20 dakika · kila mwezi: ~30-60 dakika. Ukaguzi sio adhabu — ni kujifunza.
</div>

<div class="reviews-grid">
    <?php renderChecklist('daily', $dailyChecklist, $todayReview, $today, $today, 'Ukaguzi wa leo (kila siku)', 'fa-calendar-day'); ?>
    <?php renderChecklist('weekly', $weeklyChecklist, $weekReview, $today, $weekLabel, 'Ukaguzi wa wiki (kila wiki)', 'fa-calendar-week'); ?>
    <?php renderChecklist('monthly', $monthlyChecklist, $monthReview, $today, $monthLabel, 'Ukaguzi wa mwezi (kila mwezi)', 'fa-calendar'); ?>
</div>

<?php if (!empty($past)): ?>
<section class="panel">
    <header class="panel-head"><h2><i class="fa-solid fa-clock-rotate-left"></i> Ukaguzi wa nyuma</h2></header>
    <table class="data-table">
        <thead><tr><th>Tarehe</th><th>Aina</th><th>Kipindi</th><th>Hali</th></tr></thead>
        <tbody>
            <?php foreach ($past as $r): ?>
            <tr>
                <td><?= sw_date($r['review_date']) ?></td>
                <td><span class="badge badge-info"><?= e($r['review_type']) ?></span></td>
                <td><?= e($r['period_label']) ?></td>
                <td><?= $r['is_completed'] ? '<span class="badge badge-success">Umekamilika</span>' : '<span class="badge badge-warning">Haujakamilika</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>
