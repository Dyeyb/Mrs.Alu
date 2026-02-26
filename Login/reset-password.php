<?php
/**
 * reset-password.php
 * Receives email + new password → hashes it → updates users table.
 * Returns JSON: { success: bool, message: string }
 *
 * Security note: In production, add a short-lived server-side token
 * (stored in DB after OTP verification) instead of relying only on
 * sessionStorage. This version is functional for small/internal apps.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Config ────────────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');    // ← change this
define('DB_USER', 'your_db_user');     // ← change this
define('DB_PASS', 'your_db_password'); // ← change this

define('MIN_PASSWORD_LENGTH', 8);

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

function respond($success, $message = '') {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// ── Only allow POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.');
}

// ── Read JSON body ────────────────────────────────────────────────────────────
$body     = json_decode(file_get_contents('php://input'), true);
$email    = strtolower(trim($body['email']    ?? ''));
$user_id  = (int)($body['user_id']  ?? 0);
$password = $body['password'] ?? '';

// ── Validate ──────────────────────────────────────────────────────────────────
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Invalid email address.');
}

if ($user_id <= 0) {
    respond(false, 'Invalid user.');
}

if (!$password || strlen($password) < MIN_PASSWORD_LENGTH) {
    respond(false, 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.');
}

// ── Connect ───────────────────────────────────────────────────────────────────
$pdo = getDB();

// ── Verify the email + user_id match (extra safety check) ────────────────────
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND email = ? LIMIT 1");
$stmt->execute([$user_id, $email]);
$user = $stmt->fetch();

if (!$user) {
    respond(false, 'Account not found. Please restart the reset process.');
}

// ── Hash password ─────────────────────────────────────────────────────────────
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// ── Update password ───────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    UPDATE users
    SET    password    = :password,
           updated_at  = NOW()
    WHERE  user_id = :user_id
      AND  email   = :email
");
$stmt->execute([
    ':password' => $hashed,
    ':user_id'  => $user_id,
    ':email'    => $email,
]);

if ($stmt->rowCount() === 0) {
    respond(false, 'Failed to update password. Please try again.');
}

// ── Optionally invalidate all existing OTPs for this user ────────────────────
$pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?")->execute([$user_id]);

respond(true, 'Password reset successfully.');
