<?php /** @var array $notifications */
$data['title'] = 'Arifa';
$data['subtitle'] = 'Arifa zinazotokana na hali halisi ya fedha zako.';
?>
<div class="page-toolbar">
    <form method="post" action="/notifications/read-all">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-check-double"></i> Weka zote kuwa zimesomwa</button>
    </form>
</div>

<?php if (empty($notifications)): ?>
    <div class="panel"><div class="empty-state">
        <i class="fa-solid fa-bell-slash empty-icon"></i>
        <h3>Hakuna arifa</h3>
        <p>Arifa zitatokea kiotomatiki inapohitajika — kama bili inakaribia au bajeti inakaribia kikomo.</p>
    </div></div>
<?php else: ?>
    <ul class="notif-list">
        <?php foreach ($notifications as $n): ?>
        <li class="notif-item notif-<?= e($n['severity']) ?> <?= $n['is_read'] ? 'is-read' : 'is-unread' ?>">
            <span class="notif-icon"><i class="fa-solid <?= e($n['icon']) ?>"></i></span>
            <span class="notif-body">
                <strong><?= e($n['title']) ?></strong>
                <span class="notif-text"><?= e($n['body']) ?></span>
                <span class="notif-time"><?= sw_date($n['created_at'], 'j F Y H:i') ?></span>
            </span>
            <span class="notif-actions">
                <?php if ($n['action_url']): ?>
                    <a href="<?= e($n['action_url']) ?>" class="icon-action" title="Fungua"><i class="fa-solid fa-arrow-right"></i></a>
                <?php endif; ?>
                <?php if (!$n['is_read']): ?>
                    <form method="post" action="/notifications/<?= (int) $n['id'] ?>/read" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="icon-action" title="Weka kama umesoma"><i class="fa-solid fa-check"></i></button>
                    </form>
                <?php endif; ?>
            </span>
        </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
