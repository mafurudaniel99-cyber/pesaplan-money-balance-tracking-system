<?php /** @var array $lesson $lessons */
$data['title'] = $lesson['title'];
$data['subtitle'] = 'Somo la kujifunza fedha';
?>
<a href="/education" class="back-link"><i class="fa-solid fa-arrow-left"></i> Rudi kwenye somo zote</a>

<article class="panel lesson-detail">
    <header class="lesson-detail-head">
        <span class="lesson-icon-lg"><i class="fa-solid <?= e($lesson['icon']) ?>"></i></span>
        <h1><?= e($lesson['title']) ?></h1>
        <p class="lead"><?= e($lesson['summary']) ?></p>
    </header>

    <section class="lesson-section">
        <h2><i class="fa-solid fa-book-open"></i> Maelezo</h2>
        <p><?= nl2br(e($lesson['explanation'])) ?></p>
    </section>

    <section class="lesson-section lesson-example">
        <h2><i class="fa-solid fa-lightbulb"></i> Mfano wa TZS</h2>
        <p><?= nl2br(e($lesson['example'])) ?></p>
    </section>

    <section class="lesson-section lesson-mistake">
        <h2><i class="fa-solid fa-triangle-exclamation"></i> Kosa la kawaida</h2>
        <p><?= nl2br(e($lesson['mistake'])) ?></p>
    </section>

    <section class="lesson-section lesson-action">
        <h2><i class="fa-solid fa-circle-check"></i> Hatua ya kufanya</h2>
        <p><?= nl2br(e($lesson['action'])) ?></p>
    </section>

    <section class="lesson-section lesson-exercise">
        <h2><i class="fa-solid fa-pen-to-square"></i> Zoezi</h2>
        <p><?= nl2br(e($lesson['exercise'])) ?></p>
    </section>

    <footer class="lesson-nav">
        <?php
        $keys = array_keys($lessons);
        $currentIdx = array_search($lesson, $lessons, true);
        $prev = $currentIdx > 0 ? $lessons[$keys[$currentIdx - 1]] : null;
        $next = $currentIdx < count($keys) - 1 ? $lessons[$keys[$currentIdx + 1]] : null;
        ?>
        <?php if ($prev): ?>
            <a class="btn btn-ghost" href="/education/<?= e($prev['slug']) ?>"><i class="fa-solid fa-arrow-left"></i> <?= e($prev['title']) ?></a>
        <?php endif; ?>
        <?php if ($next): ?>
            <a class="btn btn-primary" href="/education/<?= e($next['slug']) ?>"><?= e($next['title']) ?> <i class="fa-solid fa-arrow-right"></i></a>
        <?php endif; ?>
    </footer>
</article>
