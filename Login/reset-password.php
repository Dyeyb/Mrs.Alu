<?php
/**
 * reset-password.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Receives email + user_id + new password → hashes it → updates password_hash
 * in the Supabase Users table.
 *
 * Expects POST JSON: { "email": "...", "user_id": "...", "password": "..." }
 * Returns  JSON:     { success: bool, message: string }
 */

// ── CONFIG ────────────────────────────────────────────────────────────────────
define('SB_URL',   'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY',   'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('USERS_TABLE', 'Users');
define('OTP_TABLE',   'OTP_Verifications');

define('MIN_PASSWORD_LENGTH', 8);

// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Helpers ───────────────────────────────────────────────────────────────────
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
    return ['status' => $status, 'body' => json_decode($raw, true), 'cerr' => $cerr];
}

function out(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Method not allowed.', null, 405);

// ── Parse & validate input ────────────────────────────────────────────────────
$in       = json_decode(file_get_contents('php://input'), true) ?? [];
$email    = strtolower(trim($in['email']    ?? ''));
$userId   = trim($in['user_id']  ?? '');
$password = $in['password'] ?? '';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(false, 'Invalid email address.', null, 422);
}
if (!$userId) {
    out(false, 'Invalid user. Please restart the reset process.', null, 422);
}
if (!$password || strlen($password) < MIN_PASSWORD_LENGTH) {
    out(false, 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.', null, 422);
}

// ── Verify the email + user_id exist in Supabase ──────────────────────────────
$r = sb('GET', USERS_TABLE
    . '?select=user_id,status,is_archived'
    . '&user_id=eq.' . urlencode($userId)
    . '&email=eq.'   . urlencode($email)
    . '&limit=1');

if ($r['cerr'])           out(false, 'Connection error. Please try again.',   null, 500);
if ($r['status'] !== 200) out(false, 'Database error. Please try again.',     null, 500);

if (empty($r['body']) || !is_array($r['body'])) {
    out(false, 'Account not found. Please restart the reset process.', null, 404);
}

$user = $r['body'][0];

if ((bool)($user['is_archived'] ?? false)) {
    out(false, 'This account has been archived. Please contact support.', null, 403);
}
if (($user['status'] ?? '') === 'suspended') {
    out(false, 'Your account has been suspended. Please contact support.', null, 403);
}

// ── Hash the new password ─────────────────────────────────────────────────────
// Uses bcrypt — consistent with how login.php verifies via password_verify()
$newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// ── Update password_hash in Supabase (PATCH) ─────────────────────────────────
$update = sb('PATCH', USERS_TABLE
    . '?user_id=eq.' . urlencode($userId)
    . '&email=eq.'   . urlencode($email),
    ['password_hash' => $newHash]
);

if ($update['cerr']) out(false, 'Connection error. Please try again.',  null, 500);

if (!in_array($update['status'], [200, 204])) {
    out(false, 'Failed to update password. HTTP ' . $update['status'] . '. Please try again.', null, 500);
}

// ── Clean up any leftover OTPs for this user ──────────────────────────────────
sb('DELETE', OTP_TABLE . '?user_id=eq.' . urlencode($userId));

out(true, 'Password reset successfully. You can now log in with your new password.');
