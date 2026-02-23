<?php
// ── All config is inline — no require_once needed ──────────────────────────
define('SB_URL', 'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('SB_TABLE', 'Users');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Supabase request ────────────────────────────────────────────────────────
function sb(string $method, string $endpoint, ?array $body = null): array {
    $ch = curl_init(SB_URL . '/rest/v1/' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: '           . SB_KEY,
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

// ── JSON output ─────────────────────────────────────────────────────────────
function out(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$userId = trim($_GET['id'] ?? '');

// ── GET ──────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $r = sb('GET', SB_TABLE . '?select=user_id,first_name,last_name,email,phone,user_type,status,created_at,updated_at&order=created_at.desc');
    if ($r['cerr'])          out(false, 'cURL error: ' . $r['cerr'], null, 500);
    if ($r['status'] === 200) out(true, 'OK', $r['body']);
    out(false, $r['body']['message'] ?? ('Supabase error HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

// ── POST ─────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty(trim($in['first_name'] ?? ''))) out(false, 'First name is required.', null, 422);
    if (empty(trim($in['last_name']  ?? ''))) out(false, 'Last name is required.',  null, 422);
    if (empty(trim($in['email']      ?? ''))) out(false, 'Email is required.',       null, 422);
    if (!filter_var($in['email'] ?? '', FILTER_VALIDATE_EMAIL)) out(false, 'Invalid email address.', null, 422);
    $pw = trim($in['password'] ?? '');
    if (empty($pw)) out(false, 'Password is required.', null, 422);

    $r = sb('POST', SB_TABLE, [
        'first_name'    => trim($in['first_name']),
        'last_name'     => trim($in['last_name']),
        'email'         => strtolower(trim($in['email'])),
        'password_hash' => password_hash($pw, PASSWORD_BCRYPT),
        'phone'         => trim($in['phone'] ?? ''),
        'created_at'    => date('c'),
        'updated_at'    => date('c'),
    ]);

    if ($r['cerr']) out(false, 'cURL error: ' . $r['cerr'], null, 500);

    if (in_array($r['status'], [200, 201])) {
        $row = is_array($r['body']) ? ($r['body'][0] ?? $r['body']) : $r['body'];
        out(true, 'User created successfully.', $row, 201);
    }

    $raw = strtolower($r['raw'] ?? '');
    if (str_contains($raw, 'unique') || str_contains($raw, 'duplicate') || $r['status'] === 409)
        out(false, 'Email address already exists.', null, 409);
    if (str_contains($raw, 'rls') || str_contains($raw, 'security policy') || in_array($r['status'], [401, 403]))
        out(false, 'RLS is blocking this action. Run in Supabase SQL Editor: ALTER TABLE "Users" DISABLE ROW LEVEL SECURITY;', ['http' => $r['status'], 'raw' => $r['raw']], 403);

    out(false, $r['body']['message'] ?? ('Insert failed. HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

// ── PUT ──────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!$userId) out(false, 'User ID is required.', null, 400);
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    $payload = [];
    if (!empty($in['first_name'])) $payload['first_name'] = trim($in['first_name']);
    if (!empty($in['last_name']))  $payload['last_name']  = trim($in['last_name']);
    if (!empty($in['email'])) {
        if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) out(false, 'Invalid email.', null, 422);
        $payload['email'] = strtolower(trim($in['email']));
    }
    if (!empty($in['password'])) $payload['password_hash'] = password_hash($in['password'], PASSWORD_BCRYPT);
    if (isset($in['phone']))     $payload['phone'] = trim($in['phone']);
    if (empty($payload))         out(false, 'No fields to update.', null, 422);
    $payload['updated_at'] = date('c');

    $r = sb('PATCH', SB_TABLE . '?user_id=eq.' . urlencode($userId), $payload);
    if ($r['cerr']) out(false, 'cURL error: ' . $r['cerr'], null, 500);
    if (in_array($r['status'], [200, 204])) {
        $row = is_array($r['body']) ? ($r['body'][0] ?? $r['body']) : $r['body'];
        out(true, 'User updated successfully.', $row);
    }
    out(false, $r['body']['message'] ?? ('Update failed. HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

// ── DELETE ───────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$userId) out(false, 'User ID is required.', null, 400);
    $r = sb('DELETE', SB_TABLE . '?user_id=eq.' . urlencode($userId));
    if ($r['cerr']) out(false, 'cURL error: ' . $r['cerr'], null, 500);
    if (in_array($r['status'], [200, 204])) out(true, 'User deleted successfully.');
    out(false, $r['body']['message'] ?? ('Delete failed. HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

out(false, 'Method not allowed.', null, 405);