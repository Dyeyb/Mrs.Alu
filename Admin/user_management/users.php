<?php
// ══════════════════════════════════════════════
//  users.php – All Users List
//  Mrs. Alu Admin Panel
// ══════════════════════════════════════════════

require_once 'db.php';
require_once 'helpers.php';

// ── Query params ──────────────────────────────
$filterType   = $_GET['type']   ?? '';
$filterSort   = $_GET['sort']   ?? 'created_at_desc';
$filterLimit  = (int)($_GET['limit']  ?? 10);
$filterSearch = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$offset       = ($page - 1) * $filterLimit;

// ── Build query ───────────────────────────────
$where  = ['1=1'];
$params = [];

if ($filterType && in_array($filterType, ['admin','user'], true)) {
    $where[]         = 'user_type = :type';
    $params[':type'] = $filterType;
}
if ($filterSearch) {
    $where[]      = "(first_name ILIKE :s OR last_name ILIKE :s OR email ILIKE :s OR phone ILIKE :s)";
    $params[':s'] = "%{$filterSearch}%";
}

$orderMap = [
    'created_at_desc' => 'created_at DESC',
    'created_at_asc'  => 'created_at ASC',
    'name_az'         => 'first_name ASC, last_name ASC',
    'name_za'         => 'first_name DESC, last_name DESC',
];
$order = $orderMap[$filterSort] ?? 'created_at DESC';
$whereSQL = implode(' AND ', $where);

// Total count
$countStmt = getDB()->prepare("SELECT COUNT(*) FROM users WHERE $whereSQL");
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($totalCount / $filterLimit);

// Fetch page
$stmt = getDB()->prepare("SELECT * FROM users WHERE $whereSQL ORDER BY $order LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $filterLimit, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,      PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// ── Layout vars ───────────────────────────────
$pageTitle  = 'User Management';
$activePage = 'users';
$breadcrumb = ['Users' => ''];

require_once 'layout.php';
?>

<style>
    .card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden;}
    .card-head{display:flex;align-items:center;justify-content:space-between;padding:22px 28px;border-bottom:1px solid var(--border);background:#fefcf8;}
    .card-title{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:600;color:var(--dark);}
    .card-title span{color:var(--gold);font-style:italic;}
    .card-sub{font-size:13px;color:#aaa;margin-top:2px;}

    .filter-bar{display:flex;align-items:center;gap:10px;padding:14px 28px;border-bottom:1px solid var(--border);background:#fafaf8;flex-wrap:wrap;}
    .f-select{padding:7px 28px 7px 11px;border:1px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;font-size:13px;background:#fff;color:var(--text);outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%23aaa' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 9px center;transition:var(--ease);}
    .f-select:focus{border-color:var(--gold);}
    .f-search{position:relative;display:flex;align-items:center;}
    .f-search input{padding:7px 34px 7px 11px;border:1px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;font-size:13px;width:190px;outline:none;background:#fff;transition:var(--ease);}
    .f-search input:focus{border-color:var(--gold);width:220px;}
    .f-search input::placeholder{color:#ccc;}
    .f-search button{position:absolute;right:4px;top:50%;transform:translateY(-50%);width:26px;height:26px;background:var(--gold);border:none;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
    .f-search button svg{width:12px;height:12px;color:#fff;}
    .f-spacer{flex:1;}
    .f-count{font-size:13px;color:#aaa;white-space:nowrap;}
    .f-count strong{color:var(--text);}

    .tbl-wrap{overflow-x:auto;}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:#fafaf8;border-bottom:2px solid var(--border);}
    th{padding:13px 14px;text-align:left;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#aaa;white-space:nowrap;}
    th:first-child{padding-left:28px;}
    th:last-child{padding-right:28px;text-align:center;}
    tbody tr{border-bottom:1px solid #f5f2ec;transition:background .15s;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:#faf8f4;}
    td{padding:14px 14px;font-size:14px;color:var(--text);vertical-align:middle;}
    td:first-child{padding-left:28px;}
    td:last-child{padding-right:28px;text-align:center;}

    .user-cell{display:flex;align-items:center;gap:10px;}
    .u-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#c9a561,#e8c97a);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:15px;font-weight:700;color:#1a0e05;flex-shrink:0;}
    .u-name{font-weight:600;color:var(--dark);}

    .action-cell{display:flex;gap:5px;justify-content:center;}

    .tbl-foot{display:flex;align-items:center;justify-content:space-between;padding:14px 28px;border-top:1px solid var(--border);background:#fafaf8;}
    .pg-info{font-size:13px;color:#aaa;}
    .pagination{display:flex;align-items:center;gap:5px;}
    .pg-btn{width:32px;height:32px;border-radius:7px;border:1px solid var(--border);background:#fff;font-family:'Outfit',sans-serif;font-size:13px;color:#888;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:var(--ease);}
    .pg-btn:hover{border-color:var(--gold);color:var(--gold);}
    .pg-btn.on{background:var(--gold);border-color:var(--gold);color:#1a0e05;font-weight:700;}
    .pg-btn.off{opacity:.4;pointer-events:none;}

    .empty{padding:80px 28px;text-align:center;}
    .empty-ico{width:70px;height:70px;background:linear-gradient(135deg,#f8f3e8,#f0e8d0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;}
    .empty-ico svg{width:32px;height:32px;color:var(--gold);}
    .empty-t{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:600;color:var(--dark);margin-bottom:8px;}
    .empty-d{font-size:14px;color:#aaa;margin-bottom:22px;}
</style>

<?php
$baseUrl = '?' . http_build_query(array_filter([
    'type'   => $filterType,
    'sort'   => $filterSort,
    'limit'  => $filterLimit,
    'search' => $filterSearch,
]));
$startEntry = $totalCount > 0 ? $offset + 1 : 0;
$endEntry   = min($offset + $filterLimit, $totalCount);
?>

<div class="card anim d1">

    <!-- Header -->
    <div class="card-head">
        <div>
            <div class="card-title">All <span>Users</span></div>
            <div class="card-sub">Manage registered users and administrators</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="add-users.php" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add User
            </a>
        </div>
    </div>

    <!-- Filter bar -->
    <form method="GET" class="filter-bar">
        <select name="type" class="f-select" onchange="this.form.submit()">
            <option value=""     <?= $filterType===''     ?'selected':'' ?>>All Types</option>
            <option value="admin"<?= $filterType==='admin'?'selected':'' ?>>Admin</option>
            <option value="user" <?= $filterType==='user' ?'selected':'' ?>>User</option>
        </select>

        <select name="sort" class="f-select" onchange="this.form.submit()">
            <option value="created_at_desc"<?= $filterSort==='created_at_desc'?'selected':'' ?>>Newest First</option>
            <option value="created_at_asc" <?= $filterSort==='created_at_asc' ?'selected':'' ?>>Oldest First</option>
            <option value="name_az"        <?= $filterSort==='name_az'        ?'selected':'' ?>>Name A–Z</option>
            <option value="name_za"        <?= $filterSort==='name_za'        ?'selected':'' ?>>Name Z–A</option>
        </select>

        <select name="limit" class="f-select" onchange="this.form.submit()">
            <option value="10"<?= $filterLimit===10?'selected':'' ?>>Show 10</option>
            <option value="25"<?= $filterLimit===25?'selected':'' ?>>Show 25</option>
            <option value="50"<?= $filterLimit===50?'selected':'' ?>>Show 50</option>
        </select>

        <div class="f-search">
            <input type="text" name="search" placeholder="Search users…" value="<?= e($filterSearch) ?>">
            <button type="submit">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
            </button>
        </div>

        <?php if ($filterSearch): ?>
            <a href="users.php" class="btn btn-secondary btn-sm">✕ Clear</a>
        <?php endif; ?>

        <div class="f-spacer"></div>
        <div class="f-count"><strong><?= $totalCount ?></strong> record<?= $totalCount!==1?'s':'' ?></div>
    </form>

    <!-- Table -->
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>#&nbsp;ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="8">
                    <div class="empty">
                        <div class="empty-ico">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="empty-t">No Users Found</div>
                        <div class="empty-d"><?= $filterSearch ? 'No results for your search. Try different keywords.' : 'No users yet. Add your first one.' ?></div>
                        <a href="add-users.php" class="btn btn-primary">
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Add First User
                        </a>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td style="font-family:'Cormorant Garamond',serif;font-size:16px;color:#bbb;"><?= fmtId($u['user_id']) ?></td>
                    <td>
                        <div class="user-cell">
                            <div class="u-avatar"><?= strtoupper(substr($u['first_name'] ?? 'U', 0, 1)) ?></div>
                            <div class="u-name"><?= e($u['first_name']) ?> <?= e($u['last_name']) ?></div>
                        </div>
                    </td>
                    <td style="color:#666;"><?= e($u['email']) ?></td>
                    <td style="color:#999;"><?= e($u['phone'] ?? '—') ?></td>
                    <td><span class="tag tag-<?= e($u['user_type']) ?>"><?= ucfirst(e($u['user_type'])) ?></span></td>
                    <td style="color:#999;font-size:13px;"><?= formatDate($u['created_at']) ?></td>
                    <td style="color:#999;font-size:13px;"><?= formatDate($u['updated_at']) ?></td>
                    <td>
                        <div class="action-cell">
                            <!-- Edit → goes to edit-users.php -->
                            <a href="edit-users.php?id=<?= $u['user_id'] ?>" class="btn btn-edit btn-sm">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </a>
                            <!-- Archive → confirm modal -->
                            <button class="btn btn-archive btn-sm"
                                onclick="confirmArchive(<?= $u['user_id'] ?>, '<?= e($u['first_name']) ?> <?= e($u['last_name']) ?>')">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="21 8 21 21 3 21 3 8"/>
                                    <rect x="1" y="3" width="22" height="5"/>
                                    <line x1="10" y1="12" x2="14" y2="12"/>
                                </svg>
                                Archive
                            </button>
                            <!-- Delete → goes to archive-users.php (permanent delete) -->
                            <button class="btn btn-danger btn-sm"
                                onclick="confirmDelete(<?= $u['user_id'] ?>, '<?= e($u['first_name']) ?> <?= e($u['last_name']) ?>')">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="tbl-foot">
        <div class="pg-info">Showing <?= $startEntry ?> – <?= $endEntry ?> of <?= $totalCount ?></div>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <a href="<?= $baseUrl ?>&page=<?= $page-1 ?>" class="pg-btn <?= $page<=1?'off':'' ?>">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15,18 9,12 15,6"/></svg>
            </a>
            <?php for ($p=1;$p<=$totalPages;$p++): ?>
                <?php if ($p===1||$p===$totalPages||abs($p-$page)<=1): ?>
                    <a href="<?= $baseUrl ?>&page=<?= $p ?>" class="pg-btn <?= $p===$page?'on':'' ?>"><?= $p ?></a>
                <?php elseif(abs($p-$page)===2): ?>
                    <span class="pg-btn" style="cursor:default;">…</span>
                <?php endif; ?>
            <?php endfor; ?>
            <a href="<?= $baseUrl ?>&page=<?= $page+1 ?>" class="pg-btn <?= $page>=$totalPages?'off':'' ?>">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9,18 15,12 9,6"/></svg>
            </a>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /card -->


<!-- ══ MODAL: ARCHIVE CONFIRM ══ -->
<div class="modal-overlay" id="archiveModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">Archive <span>User</span></div>
            <div class="modal-close" onclick="closeModal('archiveModal')">×</div>
        </div>
        <div class="confirm-body">
            <div class="confirm-icon ci-archive">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <polyline points="21 8 21 21 3 21 3 8"/>
                    <rect x="1" y="3" width="22" height="5"/>
                    <line x1="10" y1="12" x2="14" y2="12"/>
                </svg>
            </div>
            <div class="confirm-title">Archive this user?</div>
            <div class="confirm-desc">
                <strong id="archiveName"></strong> will be moved to
                <em>archive_users</em>. You can restore them any time from
                <a href="archive-users.php">Archived Users</a>.
            </div>
        </div>
        <form method="POST" action="archive.php">
            <input type="hidden" name="action"  value="archive">
            <input type="hidden" name="user_id" id="archiveId">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('archiveModal')">Cancel</button>
                <button type="submit" class="btn btn-archive">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="21 8 21 21 3 21 3 8"/>
                        <rect x="1" y="3" width="22" height="5"/>
                    </svg>
                    Yes, Archive
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL: DELETE CONFIRM ══ -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">Delete <span>User</span></div>
            <div class="modal-close" onclick="closeModal('deleteModal')">×</div>
        </div>
        <div class="confirm-body">
            <div class="confirm-icon ci-delete">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </div>
            <div class="confirm-title">Delete permanently?</div>
            <div class="confirm-desc">
                <strong id="deleteName"></strong> will be <strong>permanently deleted</strong>
                and cannot be recovered. Consider archiving instead.
            </div>
        </div>
        <form method="POST" action="archive.php">
            <input type="hidden" name="action"  value="delete_user">
            <input type="hidden" name="user_id" id="deleteId">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    </svg>
                    Yes, Delete Forever
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmArchive(id, name) {
    document.getElementById('archiveId').value   = id;
    document.getElementById('archiveName').textContent = name;
    openModal('archiveModal');
}
function confirmDelete(id, name) {
    document.getElementById('deleteId').value    = id;
    document.getElementById('deleteName').textContent  = name;
    openModal('deleteModal');
}
</script>

<?php require_once 'layout-end.php'; ?>