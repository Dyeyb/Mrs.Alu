<?php
define('SB_URL',          'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY',          'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('SB_TABLE',        'Users');
define('GOOGLE_CLIENT_ID','941556744306-38tjae02q06kgjktslh5hts3lkuvjg1t.apps.googleusercontent.com');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Supabase helper ───────────────────────────────────────────────────────────
function sb(string $method, string $endpoint, ?array $body = null): array {
    $ch = curl_init(SB_URL . '/rest/v1/' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: '               . SB_KEY,
            'Authorization: Bearer ' . SB_KEY,
            'Prefer: return=representation',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $raw    = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr   = curl_error($ch);
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($raw, true), 'raw' => $raw, 'cerr' => $cerr];
}

// ── JSON response helper ──────────────────────────────────────────────────────
function out(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

// ── Only POST ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(false, 'Method not allowed.', null, 405);
}

// ── Parse input ───────────────────────────────────────────────────────────────
$in      = json_decode(file_get_contents('php://input'), true) ?? [];
$idToken = trim($in['id_token'] ?? '');

if (empty($idToken)) out(false, 'ID token is required.', null, 422);

// ── Verify token with Google ──────────────────────────────────────────────────
$ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);
$raw    = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status !== 200) {
    out(false, 'Google token verification failed.', null, 401);
}

$google = json_decode($raw, true);

// Confirm the token was issued for YOUR client
if (($google['aud'] ?? '') !== GOOGLE_CLIENT_ID) {
    out(false, 'Token audience mismatch.', null, 401);
}

$email     = strtolower(trim($google['email']        ?? ''));
$firstName = trim($google['given_name']  ?? 'Google');
$lastName  = trim($google['family_name'] ?? 'User');

if (empty($email)) out(false, 'Could not retrieve email from Google.', null, 401);

// ── Look up existing user ─────────────────────────────────────────────────────
$r = sb('GET', SB_TABLE
    . '?select=user_id,first_name,last_name,email,user_type,status,is_archived'
    . '&email=eq.' . urlencode($email)
    . '&limit=1');

if ($r['cerr']) out(false, 'Connection error: ' . $r['cerr'], null, 500);
if ($r['status'] !== 200) out(false, 'Database error. HTTP ' . $r['status'], null, 500);

// ── No account found → tell the frontend to redirect to signup ────────────────
if (empty($r['body']) || !is_array($r['body'])) {
    out(false, 'no_account', [
        'email'      => $email,
        'first_name' => $firstName,
        'last_name'  => $lastName,
    ], 404);
}

// ── Existing user ─────────────────────────────────────────────────────────────
$user = $r['body'][0];

if ((bool)$user['is_archived'])      out(false, 'This account has been archived. Please contact support.',  null, 403);
if ($user['status'] === 'inactive')  out(false, 'Your account is inactive. Please contact support.',        null, 403);
if ($user['status'] === 'suspended') out(false, 'Your account has been suspended. Please contact support.', null, 403);

// ── Redirect target by role ───────────────────────────────────────────────────
$redirect = ($user['user_type'] === 'admin')
    ? '../admin-folders/admin_index.html'
    : '../Homepage/index.html';

// ── Success ───────────────────────────────────────────────────────────────────
out(true, 'Login successful.', [
    'user_id'    => $user['user_id'],
    'first_name' => $user['first_name'],
    'last_name'  => $user['last_name'],
    'email'      => $user['email'],
    'user_type'  => $user['user_type'],
    'status'     => $user['status'],
    'redirect'   => $redirect,
]);