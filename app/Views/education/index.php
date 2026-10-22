<?php /** @var array $lessons */
use App\Controllers\EducationController;
$lessons = $lessons ?? EducationController::lessons();
$data['title'] = 'Jifunze Kusimamia Fedha';
$data['subtitle'] = 'Somo 15 la kujifunza fedha kwa Kiswahili — kwa mifano ya TZS.';
?>
<div class="alert alert-info">
    <i class="fa-solid fa-graduation-cap"></i>
    Nidhamu ya fedha hujengwa kupitia mazoea madogo ya kila siku. Anza na somo la kwanza. Kila somo lina maelezo, mfano wa TZS, kosa la kawaida, hatua ya kufanya, na zoezi.
</div>

<div class="lessons-grid">
    <?php foreach ($lessons as $l): ?>
    <a class="panel lesson-card" href="/education/<?= e($l['slug']) ?>">
        <span class="lesson-icon"><i class="fa-solid <?= e($l['icon']) ?>"></i></span>
        <h3><?= e($l['title']) ?></h3>
        <p class="lesson-summary"><?= e($l['summary']) ?></p>
        <span class="lesson-cta">Soma zaidi <i class="fa-solid fa-arrow-right"></i></span>
    </a>
    <?php endforeach; ?>
</div>
