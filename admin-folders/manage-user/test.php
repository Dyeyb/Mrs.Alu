<?php
/**
 * test.php — Diagnostic file for Mrs.Alu User Management
 * Place in: D:\Qasia\xampp\htdocs\Mrs.Alu\admin-folders\manage-user\test.php
 * Open:     http://localhost/Mrs.Alu/admin-folders/manage-user/test.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<title>Mrs.Alu — Diagnostic</title>
<style>
    body { font-family: monospace; padding: 30px; background: #f5f2ee; }
    h2 { font-family: serif; color: #6b4226; }
    .check { background: #fff; border: 1px solid #e5e0d8; border-radius: 8px; padding: 16px 20px; margin-bottom: 12px; }
    .ok   { border-left: 4px solid #2d9e6b; }
    .fail { border-left: 4px solid #e05555; }
    .warn { border-left: 4px solid #e67e22; }
    .label { font-weight: bold; font-size: 13px; margin-bottom: 6px; }
    .val   { font-size: 12px; color: #555; white-space: pre-wrap; word-break: break-all; }
    .ok   .label::before { content: '✅  '; }
    .fail .label::before { content: '❌  '; }
    .warn .label::before { content: '⚠️  '; }
</style>
</head>
<body>
<h2>Mrs.Alu — Server Diagnostic</h2>

<?php

function check($label, $ok, $detail = '', $warn = false) {
    $cls = $ok ? 'ok' : ($warn ? 'warn' : 'fail');
    echo "<div class='check {$cls}'>";
    echo "<div class='label'>{$label}</div>";
    if ($detail) echo "<div class='val'>{$detail}</div>";
    echo "</div>";
}

// 1. PHP version
$phpOk = version_compare(PHP_VERSION, '7.4.0', '>=');
check('PHP Version', $phpOk, 'Running PHP ' . PHP_VERSION . ($phpOk ? '' : ' — needs 7.4+'));

// 2. cURL extension
$curlOk = extension_loaded('curl');
check('cURL Extension', $curlOk, $curlOk ? 'cURL is loaded and available.' : 'cURL is NOT loaded. Enable it in php.ini → uncomment extension=curl');

// 3. Config file exists
$configPath = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'supabase.php';
$configOk   = file_exists($configPath);
check('config/supabase.php exists', $configOk, $configPath);

// 4. API file exists
$apiPath = __DIR__ . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'users.php';
$apiOk   = file_exists($apiPath);
check('api/users.php exists', $apiOk, $apiPath);

// 5. Load config and attempt Supabase connection
if ($configOk && $curlOk) {
    require_once $configPath;

    $url = SUPABASE_URL . '/rest/v1/' . SUPABASE_TABLE . '?select=user_id&limit=1';
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . SUPABASE_ANON_KEY,
            'Authorization: Bearer ' . SUPABASE_ANON_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response   = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    $sslError   = curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
    curl_close($ch);

    if ($curlError) {
        // Retry without SSL verification to detect SSL issues
        $ch2 = curl_init($url);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,  // bypass for test only
        ]);
        $resp2  = curl_exec($ch2);
        $stat2  = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        $err2   = curl_error($ch2);
        curl_close($ch2);

        if ($stat2 === 200) {
            check('Supabase Connection (SSL bypassed)', false,
                "SSL verification failed — this is the root cause.\n" .
                "Fix: Open D:\\Qasia\\xampp\\php\\php.ini\n" .
                "Find:  ;curl.cainfo =\n" .
                "Change to: curl.cainfo = \"D:/Qasia/xampp/php/extras/ssl/cacert.pem\"\n\n" .
                "Then download cacert.pem from https://curl.se/ca/cacert.pem\n" .
                "Save it to: D:\\Qasia\\xampp\\php\\extras\\ssl\\cacert.pem\n" .
                "Then restart Apache.", false);
        } elseif (!$err2) {
            check('Supabase Connection', false, "HTTP {$stat2}\nResponse: " . substr($resp2, 0, 400));
        } else {
            check('Supabase Connection', false,
                "cURL Error: {$curlError}\n\nThis usually means:\n" .
                "1. SSL cert issue (see above)\n" .
                "2. No internet from XAMPP\n" .
                "3. Supabase URL is wrong");
        }
    } elseif ($httpStatus === 200) {
        check('Supabase Connection', true, "HTTP 200 — Connected to Supabase successfully!\nResponse: " . substr($response, 0, 200));
    } elseif ($httpStatus === 401) {
        check('Supabase Connection', false, "HTTP 401 — Unauthorized. Check your anon key in config/supabase.php.");
    } elseif ($httpStatus === 404) {
        check('Supabase Connection', false, "HTTP 404 — Table 'Users' not found.\nCheck the table name is exactly 'Users' in Supabase (case-sensitive).\nResponse: " . substr($response, 0, 300));
    } elseif ($httpStatus === 0) {
        check('Supabase Connection', false, "No response (HTTP 0). XAMPP may be blocking outbound connections.\ncURL error: {$curlError}");
    } else {
        check('Supabase Connection', false, "HTTP {$httpStatus}\nResponse: " . substr($response, 0, 400));
    }
} elseif (!$curlOk) {
    check('Supabase Connection', false, 'Skipped — cURL not available.', true);
} else {
    check('Supabase Connection', false, 'Skipped — config/supabase.php not found.', true);
}

// 6. CORS headers check
check('CORS Headers from api/users.php', true,
    "When the browser calls api/users.php it should receive:\n" .
    "  Access-Control-Allow-Origin: *\n" .
    "If you see CORS errors in browser DevTools (F12 > Console),\n" .
    "add this to D:\\Qasia\\xampp\\apache\\conf\\httpd.conf:\n\n" .
    '  Header set Access-Control-Allow-Origin "*"', true);

// 7. Current directory info
check('Current File Location', true,
    "__DIR__  = " . __DIR__ . "\n" .
    "Expected = D:\\Qasia\\xampp\\htdocs\\Mrs.Alu\\admin-folders\\manage-user");

?>

<div class="check warn">
    <div class="label">Next Steps</div>
    <div class="val">1. Fix any ❌ items above first.
2. If Supabase says HTTP 200 ✅ but admin_user.html still fails, open browser DevTools (F12) → Console tab and share the exact error message.
3. If SSL fix needed: download cacert.pem → https://curl.se/ca/cacert.pem</div>
</div>

</body>
</html>