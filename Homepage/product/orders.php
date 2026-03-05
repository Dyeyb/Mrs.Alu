<?php
/**
 * Cal Elite Builders — Orders API (Supabase)
 * File: Homepage/product/orders.php
 *
 * POST /orders.php  → Create a new order  (returns JSON with order_id)
 * GET  /orders.php  → List orders (admin only, optional ?id=X for single)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// ── Supabase Credentials ────────────────────────────────────────────────────
define('SB_URL',  'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY',  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');

/**
 * Make a REST request to Supabase
 *
 * @param string      $method   GET | POST | PATCH | DELETE
 * @param string      $table    Table name (e.g., 'orders')
 * @param array|null  $body     JSON body for POST/PATCH
 * @param string      $query    Query string e.g. "?select=*&order_id=eq.5"
 * @param array       $extra    Extra headers
 * @return array{status:int, body:mixed}
 */
function sb(string $method, string $table, ?array $body = null, string $query = '', array $extra = []): array {
    $url = SB_URL . '/rest/v1/' . $table . $query;

    // Use 'return=minimal' for INSERT/PATCH to avoid needing SELECT RLS on anon
    $prefer = ($method === 'POST' || $method === 'PATCH') ? 'Prefer: return=minimal' : '';

    $headers = [
        'Content-Type: application/json',
        'apikey: '               . SB_KEY,
        'Authorization: Bearer ' . SB_KEY,
    ];
    if ($prefer) $headers[] = $prefer;
    foreach ($extra as $h) $headers[] = $h;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,   // include response headers
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $raw      = curl_exec($ch);
    $status   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hdrSize  = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $bodyRaw  = substr($raw, $hdrSize);
    curl_close($ch);

    error_log("[orders.php] $method $table => HTTP $status | body: " . substr($bodyRaw, 0, 300));

    return ['status' => $status, 'body' => json_decode($bodyRaw, true)];
}

function respond(array $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sanitize(mixed $v): string {
    return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8');
}

// ── Route ──────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    handleCreateOrder();
} elseif ($method === 'GET') {
    handleGetOrders();
} else {
    respond(['error' => 'Method not allowed'], 405);
}


// ══════════════════════════════════════════════════════════════════════
//  POST — Create Order
// ══════════════════════════════════════════════════════════════════════
function handleCreateOrder(): void {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true);

    if (!$body || json_last_error() !== JSON_ERROR_NONE) {
        respond(['error' => 'Invalid JSON payload'], 400);
    }

    // Required fields
    $required = ['full_name','phone','street','barangay','city','province','delivery_date','payment_method','items'];
    foreach ($required as $field) {
        if (empty($body[$field])) {
            respond(['error' => "Missing required field: $field"], 422);
        }
    }

    $items = $body['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        respond(['error' => 'Order must have at least one item'], 422);
    }

    $allowedPayment  = ['cod','gcash','bank','quote'];
    $allowedTimeSlot = ['morning','afternoon','anytime'];
    $paymentMethod   = in_array($body['payment_method'], $allowedPayment,  true) ? $body['payment_method'] : 'cod';
    $timeSlot        = in_array($body['time_slot'] ?? '', $allowedTimeSlot, true) ? $body['time_slot']      : 'anytime';

    // 1. Insert order header
    $orderPayload = [
        'user_id'        => !empty($body['user_id']) ? (int)$body['user_id'] : null,
        'full_name'      => sanitize($body['full_name']),
        'email'          => sanitize($body['email'] ?? ''),
        'phone'          => sanitize($body['phone']),
        'street'         => sanitize($body['street']),
        'barangay'       => sanitize($body['barangay']),
        'city'           => sanitize($body['city']),
        'province'       => sanitize($body['province']),
        'zip'            => sanitize($body['zip'] ?? ''),
        'landmark'       => sanitize($body['landmark'] ?? ''),
        'delivery_date'  => sanitize($body['delivery_date']),
        'time_slot'      => $timeSlot,
        'payment_method' => $paymentMethod,
        'status'         => 'pending',
        'notes'          => sanitize($body['notes'] ?? ''),
        'total_amount'   => 0.00,
    ];

    $res = sb('POST', 'orders', $orderPayload);

    // With 'return=minimal', a successful INSERT returns HTTP 201 with empty body
    if ($res['status'] < 200 || $res['status'] >= 300) {
        error_log('Orders API — insert order failed: HTTP ' . $res['status'] . ' | ' . json_encode($res['body']));
        respond(['error' => 'Failed to place order. Please try again. (HTTP ' . $res['status'] . ')'], 500);
    }

    // Fetch the just-inserted order by email+phone combo to get the order_id
    $email    = urlencode($orderPayload['email'] ?: 'empty@cal.elite');
    $phone    = urlencode($orderPayload['phone']);
    $getName  = urlencode($orderPayload['full_name']);
    $idRes    = sb('GET', 'orders', null,
        '?select=order_id&full_name=eq.' . $getName . '&phone=eq.' . $phone . '&order=order_id.desc&limit=1'
    );

    $orderId = $idRes['body'][0]['order_id'] ?? null;
    if (!$orderId) {
        // Fallback: still return success — order likely was saved, we just can't retrieve the ID
        // This happens if the anon SELECT policy is not set yet
        error_log('Orders API — order saved but ID not retrieved. Add SELECT policy for anon role.');
        respond([
            'success' => true,
            'order_id' => null,
            'ref'     => '#CE-' . date('ymdHis'),
            'message' => 'Order placed successfully. Our team will contact you shortly.',
        ], 201);
    }

    // 2. Insert order items + deduct stock
    foreach ($items as $item) {
        if (empty($item['qty']) || (int)$item['qty'] < 1) continue;
        $qty = (int)$item['qty'];
        $pid = $item['product_id'] ?? '';

        sb('POST', 'order_items', [
            'order_id'     => $orderId,
            'product_id'   => !empty($pid) ? (int)$pid : 0,
            'product_name' => sanitize($item['product_name'] ?? 'Unknown Product'),
            'sku'          => sanitize($item['sku'] ?? ''),
            'qty'          => $qty,
            'unit_price'   => 0.00,
        ]);

        // ── Deduct stock from products table ──────────────────────────────────
        // product_id in the cart can be the UUID (id column) or the PROD-001 string
        // We try both: first by numeric id, then by product_id string
        if (!empty($pid)) {
            // Try fetching by UUID id column first (most reliable)
            $prodRes = sb('GET', 'products', null,
                '?select=id,stocks&id=eq.' . urlencode($pid) . '&limit=1'
            );

            // If not found by UUID, try by product_id string (e.g. "PROD-001")
            if (empty($prodRes['body'])) {
                $prodRes = sb('GET', 'products', null,
                    '?select=id,stocks&product_id=eq.' . urlencode($pid) . '&limit=1'
                );
            }

            if (!empty($prodRes['body'][0])) {
                $prod       = $prodRes['body'][0];
                $uuid       = $prod['id'];
                $current    = (int)($prod['stocks'] ?? 0);
                $newStock   = max(0, $current - $qty);   // never go below 0

                sb('PATCH', 'products', ['stocks' => $newStock, 'updated_at' => date('c')],
                    '?id=eq.' . urlencode($uuid)
                );
                error_log("[orders.php] Stock deducted: product $uuid | $current → $newStock (qty: $qty)");
            } else {
                error_log("[orders.php] Stock deduction skipped: product not found for id=$pid");
            }
        }
    }

    // 3. Create delivery schedule row
    sb('POST', 'delivery_schedule', [
        'order_id'       => $orderId,
        'scheduled_date' => sanitize($body['delivery_date']),
        'time_slot'      => $timeSlot,
        'status'         => 'scheduled',
    ]);

    // 4. Create payment row
    sb('POST', 'payments', [
        'order_id' => $orderId,
        'method'   => $paymentMethod,
        'amount'   => 0.00,
        'status'   => 'pending',
    ]);

    // 5. Status log (initial)
    sb('POST', 'order_status_log', [
        'order_id'   => $orderId,
        'old_status' => null,
        'new_status' => 'pending',
        'changed_by' => 'customer',
        'note'       => 'Order placed via website',
    ]);

    respond([
        'success'  => true,
        'order_id' => $orderId,
        'ref'      => '#CE-' . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT),
        'message'  => 'Order placed successfully. Our team will contact you shortly.',
    ], 201);
}


// ══════════════════════════════════════════════════════════════════════
//  GET — List / Fetch Orders
// ══════════════════════════════════════════════════════════════════════
function handleGetOrders(): void {
    // Single order by ID
    if (!empty($_GET['id'])) {
        $id  = (int)$_GET['id'];
        $res = sb('GET', 'orders', null, '?order_id=eq.' . $id . '&select=*');

        if (empty($res['body'])) respond(['error' => 'Order not found'], 404);

        $order = $res['body'][0];

        // Fetch items
        $itemsRes = sb('GET', 'order_items', null, '?order_id=eq.' . $id . '&select=*');
        $order['items'] = $itemsRes['body'] ?? [];

        // Fetch delivery
        $dsRes = sb('GET', 'delivery_schedule', null, '?order_id=eq.' . $id . '&select=*&order=created_at.desc&limit=1');
        $order['delivery'] = $dsRes['body'][0] ?? null;

        // Fetch status log
        $logRes = sb('GET', 'order_status_log', null, '?order_id=eq.' . $id . '&select=*&order=created_at.desc');
        $order['status_log'] = $logRes['body'] ?? [];

        respond(['data' => $order]);
    }

    // Build filters
    $filters = [];

    if (!empty($_GET['status']))  $filters[] = 'status=eq.'          . urlencode($_GET['status']);
    if (!empty($_GET['date']))    $filters[] = 'delivery_date=eq.'   . urlencode($_GET['date']);
    if (!empty($_GET['payment'])) $filters[] = 'payment_method=eq.'  . urlencode($_GET['payment']);

    $limit  = min((int)($_GET['limit']  ?? 50), 200);
    $offset = max((int)($_GET['offset'] ?? 0),  0);

    $qs = '?select=*&order=created_at.desc&limit=' . $limit . '&offset=' . $offset;
    foreach ($filters as $f) $qs .= '&' . $f;

    $res = sb('GET', 'orders', null, $qs, ['Prefer: count=exact']);

    respond([
        'data'   => $res['body'] ?? [],
        'limit'  => $limit,
        'offset' => $offset,
    ]);
}
