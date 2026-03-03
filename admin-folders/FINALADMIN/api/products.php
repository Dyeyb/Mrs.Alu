<?php
// ─── Supabase config ─────────────────────────────────────────────────────────
define('SB_URL', 'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('SB_TABLE', 'products');
define('LOW_STOCK_THRESHOLD', 10);

// ─── CORS + JSON headers ──────────────────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Validation constants ─────────────────────────────────────────────────────
const VALID_STATUSES = ['active', 'inactive', 'draft', 'archived'];

// ─── Supabase cURL helper ─────────────────────────────────────────────────────
function sb(string $method, string $endpoint, ?array $body = null): array
{
    $ch = curl_init(SB_URL . '/rest/v1/' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'apikey: ' . SB_KEY,
            'Authorization: Bearer ' . SB_KEY,
            'Prefer: return=representation',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15,
    ]);
    if ($body !== null)
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($raw, true), 'raw' => $raw, 'cerr' => $cerr];
}

// ─── JSON output + exit ───────────────────────────────────────────────────────
function out(bool $ok, string $msg, $data = null, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$productId = trim($_GET['id'] ?? '');   // uuid `id` column

// ─────────────────────────────────────────────────────────────────────────────
// GET — fetch all products ordered newest first (includes stocks)
// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $r = sb('GET', SB_TABLE
        . '?select=id,product_id,name,category,description,sku,status,stocks,image_url,created_at,updated_at'
        . '&order=created_at.desc');

    if ($r['cerr'])
        out(false, 'cURL error: ' . $r['cerr'], null, 500);

    if ($r['status'] === 200)
        out(true, 'OK', $r['body']);

    out(false, $r['body']['message'] ?? ('Supabase error HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

// ─────────────────────────────────────────────────────────────────────────────
// POST — insert a new product  (product_id & sku are AUTO-GENERATED)
// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];

    // Required fields
    if (empty(trim($in['name'] ?? '')))
        out(false, 'Product name is required.', null, 422);
    if (empty(trim($in['category'] ?? '')))
        out(false, 'Category is required.', null, 422);
    if (empty(trim($in['status'] ?? '')))
        out(false, 'Status is required.', null, 422);
    if (!in_array(trim($in['status']), VALID_STATUSES))
        out(false, 'Invalid status. Allowed: ' . implode(', ', VALID_STATUSES), null, 422);

    // ── Auto-generate product_id and sku ──────────────────────────────────────
    $countR = sb('GET', SB_TABLE . '?select=id');
    $nextNum = 1;
    if ($countR['status'] === 200 && is_array($countR['body']))
        $nextNum = count($countR['body']) + 1;
    $padded = str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    $autoId = 'PROD-' . $padded;
    $autoSku = 'ALW-' . $padded;
    // ─────────────────────────────────────────────────────────────────────────

    $stocks = isset($in['stocks']) ? (int) $in['stocks'] : 0;

    $payload = [
        'name' => trim($in['name']),
        'sku' => $autoSku,
        'product_id' => $autoId,
        'category' => trim($in['category']),
        'description' => trim($in['description'] ?? ''),
        'status' => trim($in['status']),
        'image_url' => trim($in['image_url'] ?? ''),
        'stocks' => $stocks,
    ];

    $r = sb('POST', SB_TABLE, $payload);

    if ($r['cerr'])
        out(false, 'cURL error: ' . $r['cerr'], null, 500);

    if (in_array($r['status'], [200, 201])) {
        $row = is_array($r['body']) ? ($r['body'][0] ?? $r['body']) : $r['body'];
        out(true, 'Product created successfully.', $row, 201);
    }

    $raw = strtolower($r['raw'] ?? '');
    if (str_contains($raw, 'unique') || str_contains($raw, 'duplicate') || $r['status'] === 409)
        out(false, 'Duplicate product_id or SKU — please try again.', null, 409);
    if (str_contains($raw, 'rls') || str_contains($raw, 'security policy') || in_array($r['status'], [401, 403]))
        out(false, 'RLS is blocking this action. Run in Supabase SQL Editor: ALTER TABLE "products" DISABLE ROW LEVEL SECURITY;', ['http' => $r['status'], 'raw' => $r['raw']], 403);

    out(false, $r['body']['message'] ?? ('Insert failed. HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

// ─────────────────────────────────────────────────────────────────────────────
// PUT — update an existing product (identified by uuid `id`)
// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!$productId)
        out(false, 'Product ID (uuid) is required.', null, 400);

    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    $payload = [];

    if (isset($in['name']) && $in['name'] !== '')
        $payload['name'] = trim($in['name']);
    if (isset($in['category']))
        $payload['category'] = trim($in['category']);
    if (isset($in['description']))
        $payload['description'] = trim($in['description']);
    if (isset($in['image_url']))
        $payload['image_url'] = trim($in['image_url']);
    if (isset($in['stocks']))
        $payload['stocks'] = (int) $in['stocks'];
    if (isset($in['status'])) {
        if (!in_array(trim($in['status']), VALID_STATUSES))
            out(false, 'Invalid status.', null, 422);
        $payload['status'] = trim($in['status']);
    }

    if (empty($payload))
        out(false, 'No fields to update.', null, 422);

    $payload['updated_at'] = date('c');

    $r = sb('PATCH', SB_TABLE . '?id=eq.' . urlencode($productId), $payload);

    if ($r['cerr'])
        out(false, 'cURL error: ' . $r['cerr'], null, 500);

    if (in_array($r['status'], [200, 204])) {
        $row = is_array($r['body']) ? ($r['body'][0] ?? $r['body']) : $r['body'];
        out(true, 'Product updated successfully.', $row);
    }

    out(false, $r['body']['message'] ?? ('Update failed. HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

// ─────────────────────────────────────────────────────────────────────────────
// DELETE — permanently remove a product
// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$productId)
        out(false, 'Product ID (uuid) is required.', null, 400);

    $r = sb('DELETE', SB_TABLE . '?id=eq.' . urlencode($productId));

    if ($r['cerr'])
        out(false, 'cURL error: ' . $r['cerr'], null, 500);

    if (in_array($r['status'], [200, 204]))
        out(true, 'Product permanently deleted.');

    out(false, $r['body']['message'] ?? ('Delete failed. HTTP ' . $r['status']), ['raw' => $r['raw']], 500);
}

out(false, 'Method not allowed.', null, 405);
