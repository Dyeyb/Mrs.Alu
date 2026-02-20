<?php
// ─────────────────────────────────────────────
//  db.php – Supabase PostgreSQL Connection
//  Mrs. Alu Admin Panel (POOLER + TENANT FIXED)
// ─────────────────────────────────────────────

// Supabase REST credentials
define('SUPABASE_URL', 'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');

// PostgreSQL pooler credentials
define('DB_HOST', 'aws-0-ap-southeast-1.pooler.supabase.com');
define('DB_PORT', '6543');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres.pdqhbxtxvxrwtkvymjlm'); // pooler requires project ref
define('DB_PASSWORD', 'CloningofMrs.ALu');

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "pgsql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";sslmode=require";

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true
            ]);
        } catch (PDOException $e) {
            die("<h2>⚠️ Supabase Connection Failed</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>");
        }
    }

    return $pdo;
}

function supabaseRequest($method, $table, $data = [], $query = '') {

    $url = SUPABASE_URL . '/rest/v1/' . $table . ($query ? '?' . $query : '');

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . SUPABASE_KEY,
            'Authorization: Bearer ' . SUPABASE_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => $data ? json_encode($data) : null
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
