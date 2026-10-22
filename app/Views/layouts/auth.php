<?php /** Auth layout — no sidebar. */ ?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <title><?= e($data['title'] ?? 'PesaPlan') ?> — PesaPlan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/style.css') ?>">
</head>
<body class="auth-body">
<div class="auth-shell">
    <div class="auth-aside">
        <div class="auth-brand">
            <span class="brand-mark"><i class="fa-solid fa-coins"></i></span>
            <span class="brand-name">PesaPlan</span>
        </div>
        <div class="auth-tagline">
            <h2>Kila shilingi iwe na kusudi.</h2>
            <p>PesaPlan ni mfumo wa kusaidia watu binafsi kusimamia mshahara wao, kudhibiti matumizi, kujenga akiba, na kufanya maamuzi mazuri ya fedha kila mwezi.</p>
        </div>
        <ul class="auth-features">
            <li><i class="fa-solid fa-circle-check"></i> Andika mshahara na matumizi yako halisi</li>
            <li><i class="fa-solid fa-circle-check"></i> Tengeneza bajeti na uilinganishe na halisi</li>
            <li><i class="fa-solid fa-circle-check"></i> Jenga akiba na ufuatilie malengo</li>
            <li><i class="fa-solid fa-circle-check"></i> Jifunze kusimamia fedha kwa Kiswahili</li>
        </ul>
    </div>
    <div class="auth-main">
        <div class="auth-card">
            <?= flash_messages($flash) ?>
            <?= $content ?>
        </div>
        <p class="auth-foot">&copy; <?= date('Y') ?> PesaPlan. Haki zote zimehifadhiwa.</p>
    </div>
</div>
<script src="/assets/js/app.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/app.js') ?>"></script>
</body>
</html>
