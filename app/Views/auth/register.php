<?php /** Register form */ ?>
<div class="auth-header">
    <h1><i class="fa-solid fa-user-plus"></i> Unda akaunti yako</h1>
    <p>PesaPlan ni bure kwa watumiaji binafsi.</p>
</div>
<form method="post" action="/register" class="form-card" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label for="name">Jina lako</label>
        <div class="input-wrap">
            <i class="fa-solid fa-user input-icon"></i>
            <input type="text" id="name" name="name" required minlength="2" autocomplete="name" placeholder="Mfano: Amani Juma">
        </div>
    </div>
    <div class="field">
        <label for="email">Barua pepe</label>
        <div class="input-wrap">
            <i class="fa-solid fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" required autocomplete="email" placeholder="wewe@mfano.com">
        </div>
    </div>
    <div class="field">
        <label for="password">Nenosiri (herufi 8+)</label>
        <div class="input-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="••••••••">
            <button type="button" class="password-toggle" aria-label="Onesha nenosiri"><i class="fa-solid fa-eye"></i></button>
        </div>
    </div>
    <div class="field">
        <label for="password_confirm">Thibitisha nenosiri</label>
        <div class="input-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="8" autocomplete="new-password" placeholder="••••••••">
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-user-plus"></i> Jisajili</button>
</form>
<p class="auth-switch">Tayari una akaunti? <a href="/login">Ingia hapa</a></p>
