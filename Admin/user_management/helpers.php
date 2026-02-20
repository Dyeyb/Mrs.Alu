<?php
// ─────────────────────────────────────────────
//  helpers.php – Shared Utility Functions
//  Mrs. Alu Admin Panel
// ─────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Store a flash message for the next page load.
 * @param string $type    'success' | 'error' | 'warning'
 * @param string $message Human-readable message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear the flash message.
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect to a URL and stop execution.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Safely output a value as HTML-escaped string.
 */
function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format a PostgreSQL timestamp for human display.
 */
function formatDate(?string $ts): string {
    if (!$ts) return '—';
    return date('Y-m-d H:i', strtotime($ts));
}

/**
 * Zero-pad a numeric ID for display, e.g. #0042
 */
function fmtId(int $id, int $pad = 4): string {
    return '#' . str_pad($id, $pad, '0', STR_PAD_LEFT);
}