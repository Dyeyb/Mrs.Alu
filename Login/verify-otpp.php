<?php
/**
 * verify-otp.php
 * Validates OTP for a given email.
 * Returns JSON: { success: bool, message: string }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Config ────────────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');     // ← change this
define('DB_USER', 'your_db_user');      // ← change this
define('DB_PASS', 'your_db_password'); // ← change this

// ── Helpers ───────────────────────────────────────────────────────────────────
function getDB() {
    try {
        return new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        respond(false, 'Database connection failed.');
    }
}

function respond($success, $message = '', $data = null) {
    $payload = ['success' => $success, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload);
    exit;
}

// ── Only allow POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.');
}

// ── Read JSON body ────────────────────────────────────────────────────────────
$body  = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($body['email'] ?? ''));
$otp   = trim($body['otp'] ?? '');
$flow  = $body['flow'] ?? ''; // 'forgot-password' or 'register'

// ── Validate input ────────────────────────────────────────────────────────────
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Invalid email address.');
}

if (!$otp || !preg_match('/^\d{6}$/', $otp)) {
    respond(false, 'Invalid OTP format.');
}

// ── Connect ───────────────────────────────────────────────────────────────────
$pdo = getDB();

// ── Get user ──────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    respond(false, 'No account found with that email.');
}

$user_id = $user['user_id'];

// ── Fetch OTP record ──────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT otp, expires_at
    FROM   otp_codes
    WHERE  user_id = ?
    LIMIT  1
");
$stmt->execute([$user_id]);
$record = $stmt->fetch();

if (!$record) {
    respond(false, 'No verification code found. Please request a new one.');
}

// ── Check expiry ──────────────────────────────────────────────────────────────
if (strtotime($record['expires_at']) < time()) {
    respond(false, 'Verification code has expired. Please request a new one.');
}

// ── Check OTP value ───────────────────────────────────────────────────────────
if (!hash_equals($record['otp'], $otp)) {
    respond(false, 'Incorrect code. Please try again.');
}

// ── OTP is valid — delete it (one-time use) ───────────────────────────────────
$stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?");
$stmt->execute([$user_id]);

// ── If this is a registration flow, mark user as verified ────────────────────
if ($flow === 'register') {
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
}

// ── If this is forgot-password flow, return success so frontend can redirect
// to reset-password page. No DB change needed here; reset-password.php will
// handle the actual password update. ─────────────────────────────────────────

respond(true, 'Code verified successfully.', ['user_id' => $user_id]);
