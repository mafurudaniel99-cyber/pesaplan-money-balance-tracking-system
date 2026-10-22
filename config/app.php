<?php
/**
 * PesaPlan — Application configuration.
 * Copy database.example.php -> database.php and adjust credentials.
 * Never commit database.php to a public repository.
 */

return [
    'app_name' => 'PesaPlan',
    'app_tagline' => 'Mfumo wa Nidhamu ya Fedha',

    // Display errors only in development. Production must use logs.
    'env' => 'development',
    'debug' => true,

    // Default currency and locale
    'currency' => 'TZS',
    'locale' => 'sw_TZ',
    'timezone' => 'Africa/Dar_es_Salaam',

    // Session
    'session_name' => 'pesaplan_session',
    'session_lifetime' => 7200, // 2 hours of inactivity
    'session_secure' => false,  // set true in production under HTTPS
    'session_httponly' => true,
    'session_samesite' => 'Lax',

    // Auth
    'login_max_attempts' => 5,
    'login_lock_minutes' => 15,

    // Default categories (used by seed.sql)
    'default_categories_seed' => true,

    // Storage paths (relative to project root)
    'storage_dir' => dirname(__DIR__) . '/storage',
    'exports_dir' => dirname(__DIR__) . '/storage/exports',
    'logs_dir'    => dirname(__DIR__) . '/storage/logs',

    // CSV export safety: prefix cells that start with =, +, -, @, tab, CR
    'csv_injection_prefix' => "'",
];
