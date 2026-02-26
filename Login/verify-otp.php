<?php
/**
 * verify-otp.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Validates the 6-digit OTP against Supabase and marks the user as verified.
 *
 * Expects POST JSON: { "user_id": "...", "email": "...", "otp": "123456" }
 */

define('SB_URL',      'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY',      'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('OTP_TABLE',   'OTP_Verifications');
define('USERS_TABLE', 'Users');

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
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($raw, true)];
}

function out(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Method not allowed.', null, 405);

// ── Parse & validate input ────────────────────────────────────────────────────
$in     = json_decode(file_get_contents('php://input'), true) ?? [];
$userId = trim($in['user_id'] ?? '');
$email  = strtolower(trim($in['email'] ?? ''));
$otp    = trim($in['otp'] ?? '');

if (!$userId || !$email || strlen($otp) !== 6 || !ctype_digit($otp)) {
    out(false, 'user_id, email, and a 6-digit numeric OTP are required.', null, 422);
}

// ── Fetch latest valid (unused, unexpired) OTP record ─────────────────────────
$now = gmdate('Y-m-d\TH:i:s') . '+00:00';
$r   = sb('GET', OTP_TABLE
    . '?user_id=eq.'     . urlencode($userId)
    . '&email=eq.'       . urlencode($email)
    . '&used=eq.false'
    . '&expires_at=gte.' . urlencode($now)
    . '&order=created_at.desc&limit=1'
);

if (empty($r['body']) || !is_array($r['body']) || count($r['body']) === 0) {
    out(false, 'Code has expired or was already used. Please request a new one.', null, 422);
}

$record = $r['body'][0];

// ── Verify the OTP against the stored hash ────────────────────────────────────
if (!password_verify($otp, $record['otp_hash'])) {
    out(false, 'Incorrect verification code. Please try again.', null, 422);
}

// ── Mark this OTP record as used ──────────────────────────────────────────────
sb('PATCH', OTP_TABLE . '?id=eq.' . urlencode($record['id']), ['used' => true]);

// ── Mark the user as verified in the Users table ─────────────────────────────
// Requires: ALTER TABLE "Users" ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT false;
$update = sb('PATCH', USERS_TABLE . '?user_id=eq.' . urlencode($userId), [
    'is_verified' => true,
]);

if (!in_array($update['status'], [200, 204])) {
    out(false, 'Code verified but failed to activate account. Please contact support.', null, 500);
}

// ── Cleanup: delete all OTP records for this user ────────────────────────────
sb('DELETE', OTP_TABLE . '?user_id=eq.' . urlencode($userId));

out(true, 'Email verified successfully! Your account is now active.');