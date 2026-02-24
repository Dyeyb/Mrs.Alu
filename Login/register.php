<?php
define('SB_URL',   'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY',   'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('SB_TABLE', 'Users');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Supabase request ─────────────────────────────────────────────────────────
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

// ── JSON response ────────────────────────────────────────────────────────────
function out(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

// ── Only accept POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(false, 'Method not allowed.', null, 405);
}

// ── Parse input ───────────────────────────────────────────────────────────────
$in = json_decode(file_get_contents('php://input'), true) ?? [];

$firstName = trim($in['first_name'] ?? '');
$lastName  = trim($in['last_name']  ?? '');
$email     = strtolower(trim($in['email']    ?? ''));
$phone     = trim($in['phone']    ?? '');
$password  = $in['password']  ?? '';
$confirm   = $in['confirm']   ?? '';

// ── Validate required fields ──────────────────────────────────────────────────
if (empty($firstName))  out(false, 'First name is required.',    null, 422);
if (empty($lastName))   out(false, 'Last name is required.',     null, 422);
if (empty($email))      out(false, 'Email is required.',         null, 422);
if (empty($password))   out(false, 'Password is required.',      null, 422);
if (empty($confirm))    out(false, 'Please confirm your password.', null, 422);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(false, 'Invalid email format.', null, 422);
}

if (strlen($password) < 8) {
    out(false, 'Password must be at least 8 characters.', null, 422);
}

if ($password !== $confirm) {
    out(false, 'Passwords do not match.', null, 422);
}

// ── Check if email already exists ────────────────────────────────────────────
$check = sb('GET', SB_TABLE . '?select=user_id&email=eq.' . urlencode($email) . '&limit=1');

if ($check['cerr']) out(false, 'Connection error: ' . $check['cerr'], null, 500);
if ($check['status'] !== 200) out(false, 'Database error. HTTP ' . $check['status'], null, 500);

if (!empty($check['body'])) {
    out(false, 'An account with this email already exists.', null, 409);
}

// ── Insert new user — always type "user", status "active" ────────────────────
$r = sb('POST', SB_TABLE, [
    'first_name'    => $firstName,
    'last_name'     => $lastName,
    'email'         => $email,
    'phone'         => $phone,
    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
    'user_type'     => 'user',      // always registered as user
    'status'        => 'active',    // active by default
    'is_archived'   => false,
    'prev_status'   => null,
    'archived_at'   => null,
]);

if ($r['cerr']) out(false, 'Connection error: ' . $r['cerr'], null, 500);

if (in_array($r['status'], [200, 201])) {
    $row = is_array($r['body']) ? ($r['body'][0] ?? $r['body']) : $r['body'];
    // Return safe data only — never expose password_hash
    out(true, 'Account created successfully.', [
        'user_id'    => $row['user_id']    ?? null,
        'first_name' => $row['first_name'] ?? $firstName,
        'last_name'  => $row['last_name']  ?? $lastName,
        'email'      => $row['email']      ?? $email,
        'user_type'  => $row['user_type']  ?? 'user',
        'status'     => $row['status']     ?? 'active',
    ], 201);
}

// ── Error handling ────────────────────────────────────────────────────────────
$raw = strtolower($r['raw'] ?? '');
if (str_contains($raw, 'unique') || str_contains($raw, 'duplicate') || $r['status'] === 409)
    out(false, 'An account with this email already exists.', null, 409);
if (str_contains($raw, 'rls') || str_contains($raw, 'security policy') || in_array($r['status'], [401, 403]))
    out(false, 'Registration is currently unavailable. Please try again later.', null, 403);

out(false, $r['body']['message'] ?? ('Registration failed. HTTP ' . $r['status']), ['raw' => $r['raw']], 500);