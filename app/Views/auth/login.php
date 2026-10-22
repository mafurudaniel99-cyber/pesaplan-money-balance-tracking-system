<?php /** Login form */ ?>
<div class="auth-header">
    <h1><i class="fa-solid fa-right-to-bracket"></i> Ingia kwenye akaunti</h1>
    <p>Karibu tena! Ingiza taarifa zako ili kuendelea.</p>
</div>
<form method="post" action="/login" class="form-card" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label for="email">Barua pepe</label>
        <div class="input-wrap">
            <i class="fa-solid fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" required autocomplete="email" placeholder="wewe@mfano.com">
        </div>
    </div>
    <div class="field">
        <label for="password">Nenosiri</label>
        <div class="input-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            <button type="button" class="password-toggle" aria-label="Onesha nenosiri"><i class="fa-solid fa-eye"></i></button>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-right-to-bracket"></i> Ingia</button>
</form>
<p class="auth-switch">Huna akaunti? <a href="/register">Jisajili hapa</a></p>
