<?php
/** @var array $data — extracted already; $content holds the page body */
use App\Core\Session;
use App\Models\Notification;
$currentUser = $currentUser ?? null;
$activePage = $activePage ?? '';
$unreadNotifs = $currentUser ? Notification::unreadCount((int) $currentUser['id']) : 0;
$nav = [
    ['dashboard',         '/dashboard',         'fa-gauge-high',       'Muhtasari'],
    ['transactions',      '/transactions',      'fa-arrow-right-arrow-left', 'Miamala'],
    ['income',            '/income',            'fa-money-bill-trend-up', 'Mapato'],
    ['budgets',           '/budgets',           'fa-table-list',       'Bajeti'],
    ['savings',           '/savings',           'fa-piggy-bank',       'Akiba'],
    ['debts',             '/debts',             'fa-hand-holding-dollar', 'Madeni & Majukumu'],
    ['allocations',       '/allocations',       'fa-folder-tree',      'Ugawaji wa Fedha'],
    ['planned_purchases', '/planned-purchases', 'fa-magnifying-glass-chart', 'Kagua Kabla ya Kutumia'],
    ['reviews',           '/reviews',           'fa-calendar-check',   'Ukaguzi'],
    ['reports',           '/reports',           'fa-chart-pie',        'Ripoti'],
    ['education',         '/education',         'fa-graduation-cap',   'Jifunze'],
    ['categories',        '/categories',        'fa-tags',             'Kategoria'],
    ['notifications',     '/notifications',     'fa-bell',             'Arifa'],
    ['settings',          '/settings',          'fa-gear',             'Mipangilio'],
];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <title><?= e($data['title'] ?? ($activePage ? ucfirst($activePage) : 'PesaPlan')) ?> — PesaPlan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/style.css') ?>">
</head>
<body class="app-body" data-page="<?= e($activePage) ?>">
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" aria-label="Menyu ya kando">
        <div class="sidebar-head">
            <a href="/dashboard" class="brand">
                <span class="brand-mark"><i class="fa-solid fa-coins"></i></span>
                <span class="brand-name">PesaPlan</span>
            </a>
            <button class="sidebar-close" id="sidebarClose" aria-label="Funga menyu"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <nav class="nav" aria-label="Uendeshaji">
            <?php foreach ($nav as [$key, $url, $icon, $label]): ?>
                <a href="<?= $url ?>" class="nav-link <?= $activePage === $key ? 'active' : '' ?>"
                   <?= $key === 'notifications' && $unreadNotifs > 0 ? 'data-badge="' . (int) $unreadNotifs . '"' : '' ?>>
                    <i class="fa-solid <?= $icon ?>" aria-hidden="true"></i>
                    <span><?= e($label) ?></span>
                    <?php if ($key === 'notifications' && $unreadNotifs > 0): ?>
                        <span class="nav-badge"><?= (int) $unreadNotifs ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-foot">
            <div class="user-card">
                <span class="user-avatar"><?= e(strtoupper(substr($currentUser['name'] ?? '?', 0, 1))) ?></span>
                <div class="user-meta">
                    <span class="user-name"><?= e($currentUser['name'] ?? '') ?></span>
                    <span class="user-email"><?= e($currentUser['email'] ?? '') ?></span>
                </div>
            </div>
            <form method="post" action="/logout" class="logout-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Toka</button>
            </form>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay" hidden></div>

    <!-- Main -->
    <div class="main">
        <header class="topbar">
            <button class="hamburger" id="hamburger" aria-label="Fungua menyu"><i class="fa-solid fa-bars"></i></button>
            <div class="topbar-title">
                <h1><?= e($data['title'] ?? ucfirst($activePage)) ?></h1>
                <p class="topbar-sub"><?= e($data['subtitle'] ?? 'PesaPlan — Kila shilingi iwe na kusudi') ?></p>
            </div>
            <div class="topbar-actions">
                <a href="/transactions/create" class="btn btn-primary btn-sm" id="quickAdd">
                    <i class="fa-solid fa-plus"></i> Muamala
                </a>
                <a href="/notifications" class="icon-btn" aria-label="Arifa">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($unreadNotifs > 0): ?><span class="dot"><?= (int) $unreadNotifs ?></span><?php endif; ?>
                </a>
            </div>
        </header>

        <main class="content" id="content">
            <?= flash_messages($flash) ?>
            <?= $content ?>
        </main>

        <footer class="app-foot">
            <span>PesaPlan — Mfumo wa Nidhamu ya Fedha</span>
            <span class="foot-quote">"Kila shilingi iwe na kusudi, kila matumizi yawe na kumbukumbu."</span>
        </footer>
    </div>
</div>
<script src="/assets/js/app.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/app.js') ?>"></script>
<?= $data['scripts'] ?? '' ?>
</body>
</html>
