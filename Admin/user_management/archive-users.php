<?php
// ══════════════════════════════════════════════
//  archive-users.php – Archived Users List
//  View archived users, restore, or permanently delete
//  Mrs. Alu Admin Panel
// ══════════════════════════════════════════════

require_once 'db.php';
require_once 'helpers.php';

// ── Query params ──────────────────────────────
$filterSearch = trim($_GET['search'] ?? '');
$filterLimit  = (int)($_GET['limit']  ?? 10);
$page         = max(1, (int)($_GET['page'] ?? 1));
$offset       = ($page - 1) * $filterLimit;

// ── Build query ───────────────────────────────
$where  = ['1=1'];
$params = [];
if ($filterSearch) {
    $where[]      = "(first_name ILIKE :s OR last_name ILIKE :s OR email ILIKE :s)";
    $params[':s'] = "%{$filterSearch}%";
}
$whereSQL = implode(' AND ', $where);

$countStmt = getDB()->prepare("SELECT COUNT(*) FROM archive_users WHERE $whereSQL");
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($totalCount / $filterLimit);

$stmt = getDB()->prepare("SELECT * FROM archive_users WHERE $whereSQL ORDER BY archived_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $filterLimit, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,      PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll();

// ── Layout vars ───────────────────────────────
$pageTitle  = 'Archived Users';
$activePage = 'archive-users';
$breadcrumb = ['Archived Users' => ''];

require_once 'layout.php';
?>

<style>
    .info-banner{display:flex;align-items:center;gap:12px;padding:14px 20px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;color:#92400e;font-size:13px;margin-bottom:24px;}
    .info-banner svg{width:18px;height:18px;flex-shrink:0;}

    .card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden;}
    .card-head{display:flex;align-items:center;justify-content:space-between;padding:22px 28px;border-bottom:1px solid var(--border);background:#fefcf8;}
    .card-title{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:600;color:var(--dark);}
    .card-title span{color:var(--gold);font-style:italic;}
    .card-sub{font-size:13px;color:#aaa;margin-top:2px;}

    .filter-bar{display:flex;align-items:center;gap:10px;padding:14px 28px;border-bottom:1px solid var(--border);background:#fafaf8;flex-wrap:wrap;}
    .f-search{position:relative;display:flex;align-items:center;}
    .f-search input{padding:7px 34px 7px 11px;border:1px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;font-size:13px;width:200px;outline:none;background:#fff;transition:var(--ease);}
    .f-search input:focus{border-color:var(--gold);width:240px;}
    .f-search input::placeholder{color:#ccc;}
    .f-search button{position:absolute;right:4px;top:50%;transform:translateY(-50%);width:26px;height:26px;background:var(--gold);border:none;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
    .f-search button svg{width:12px;height:12px;color:#fff;}
    .f-select{padding:7px 28px 7px 11px;border:1px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;font-size:13px;background:#fff;color:var(--text);outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%23aaa' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 9px center;transition:var(--ease);}
    .f-select:focus{border-color:var(--gold);}
    .f-spacer{flex:1;}
    .f-count{font-size:13px;color:#aaa;}
    .f-count strong{color:var(--text);}

    .tbl-wrap{overflow-x:auto;}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:#fafaf8;border-bottom:2px solid var(--border);}
    th{padding:13px 14px;text-align:left;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#aaa;white-space:nowrap;}
    th:first-child{padding-left:28px;}
    th:last-child{padding-right:28px;text-align:center;}
    tbody tr{border-bottom:1px solid #f5f2ec;transition:background .15s;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:#fdf9f4;}
    td{padding:14px 14px;font-size:14px;color:var(--text);vertical-align:middle;}
    td:first-child{padding-left:28px;}
    td:last-child{padding-right:28px;text-align:center;}

    .user-cell{display:flex;align-items:center;gap:10px;}
    .u-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#bbb,#d0d0d0);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:15px;font-weight:700;color:#fff;flex-shrink:0;}
    .u-name{font-weight:600;color:#666;}

    .archived-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#fef3c7;color:#92400e;border:1px solid #fde68a;}
    .archived-badge svg{width:11px;height:11px;}

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
    .empty-d{font-size:14px;color:#aaa;}
</style>

<!-- Info banner -->
<div class="info-banner">
    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <polyline points="21 8 21 21 3 21 3 8"/>
        <rect x="1" y="3" width="22" height="5"/>
        <line x1="10" y1="12" x2="14" y2="12"/>
    </svg>
    These users were moved from <strong>&nbsp;users&nbsp;</strong> to <strong>&nbsp;archive_users</strong>.
    Restore them to make them active again, or permanently delete them.
</div>

<?php
$baseUrl = '?' . http_build_query(array_filter(['search' => $filterSearch, 'limit' => $filterLimit]));
$startEntry = $totalCount > 0 ? $offset + 1 : 0;
$endEntry   = min($offset + $filterLimit, $totalCount);
?>

<div class="card anim d1">

    <!-- Header -->
    <div class="card-head">
        <div>
            <div class="card-title">Archived <span>Users</span></div>
            <div class="card-sub">Moved to archive_users table — restore or permanently delete</div>
        </div>
        <a href="users.php" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            View Active Users
        </a>
    </div>

    <!-- Filter bar -->
    <form method="GET" class="filter-bar">
        <div class="f-search">
            <input type="text" name="search" placeholder="Search archive…" value="<?= e($filterSearch) ?>">
            <button type="submit">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
            </button>
        </div>
        <select name="limit" class="f-select" onchange="this.form.submit()">
            <option value="10"<?= $filterLimit===10?'selected':'' ?>>Show 10</option>
            <option value="25"<?= $filterLimit===25?'selected':'' ?>>Show 25</option>
            <option value="50"<?= $filterLimit===50?'selected':'' ?>>Show 50</option>
        </select>
        <?php if ($filterSearch): ?>
            <a href="archive-users.php" class="btn btn-secondary btn-sm">✕ Clear</a>
        <?php endif; ?>
        <div class="f-spacer"></div>
        <div class="f-count"><strong><?= $totalCount ?></strong> archived record<?= $totalCount!==1?'s':'' ?></div>
    </form>

    <!-- Table -->
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Archive&nbsp;#</th>
                    <th>Orig&nbsp;ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Originally Created</th>
                    <th>Archived At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="8">
                    <div class="empty">
                        <div class="empty-ico">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <polyline points="21 8 21 21 3 21 3 8"/>
                                <rect x="1" y="3" width="22" height="5"/>
                                <line x1="10" y1="12" x2="14" y2="12"/>
                            </svg>
                        </div>
                        <div class="empty-t">No Archived Users</div>
                        <div class="empty-d"><?= $filterSearch ? 'No archived records match your search.' : 'Archive is empty. Archived users from the Users page will appear here.' ?></div>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td style="font-family:'Cormorant Garamond',serif;font-size:16px;color:#bbb;"><?= fmtId($r['archive_id']) ?></td>
                    <td style="color:#bbb;font-size:13px;">U<?= fmtId((int)$r['user_id']) ?></td>
                    <td>
                        <div class="user-cell">
                            <div class="u-avatar"><?= strtoupper(substr($r['first_name'] ?? 'U', 0, 1)) ?></div>
                            <div>
                                <div class="u-name"><?= e($r['first_name']) ?> <?= e($r['last_name']) ?></div>
                                <div class="archived-badge" style="margin-top:4px;">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="21 8 21 21 3 21 3 8"/>
                                        <rect x="1" y="3" width="22" height="5"/>
                                    </svg>
                                    Archived
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="color:#666;"><?= e($r['email']) ?></td>
                    <td style="color:#999;"><?= e($r['phone'] ?? '—') ?></td>
                    <td style="color:#999;font-size:13px;"><?= formatDate($r['original_created_at']) ?></td>
                    <td style="color:#92400e;font-size:13px;font-weight:500;"><?= formatDate($r['archived_at']) ?></td>
                    <td>
                        <div class="action-cell">
                            <!-- Restore -->
                            <button class="btn btn-restore btn-sm"
                                onclick="confirmRestore(<?= $r['archive_id'] ?>, '<?= e($r['first_name']) ?> <?= e($r['last_name']) ?>')">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="1 4 1 10 7 10"/>
                                    <path d="M3.51 15a9 9 0 1 0 .49-3.67"/>
                                </svg>
                                Restore
                            </button>
                            <!-- Permanent delete -->
                            <button class="btn btn-danger btn-sm"
                                onclick="confirmDelete(<?= $r['archive_id'] ?>, '<?= e($r['first_name']) ?> <?= e($r['last_name']) ?>')">
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


<!-- ══ MODAL: RESTORE CONFIRM ══ -->
<div class="modal-overlay" id="restoreModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">Restore <span>User</span></div>
            <div class="modal-close" onclick="closeModal('restoreModal')">×</div>
        </div>
        <div class="confirm-body">
            <div class="confirm-icon ci-restore">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 .49-3.67"/>
                </svg>
            </div>
            <div class="confirm-title">Restore this user?</div>
            <div class="confirm-desc">
                <strong id="restoreName"></strong> will be moved back to the
                active <em>users</em> table and removed from the archive.
            </div>
        </div>
        <form method="POST" action="archive.php">
            <input type="hidden" name="action"     value="restore">
            <input type="hidden" name="archive_id" id="restoreId">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('restoreModal')">Cancel</button>
                <button type="submit" class="btn btn-restore">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="1 4 1 10 7 10"/>
                        <path d="M3.51 15a9 9 0 1 0 .49-3.67"/>
                    </svg>
                    Yes, Restore
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL: DELETE CONFIRM ══ -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">Delete <span>Permanently</span></div>
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
            <div class="confirm-title">Delete forever?</div>
            <div class="confirm-desc">
                <strong id="deleteName"></strong> will be
                <strong>permanently erased</strong> from the archive.
                This cannot be undone.
            </div>
        </div>
        <form method="POST" action="archive.php">
            <input type="hidden" name="action"     value="delete_archive">
            <input type="hidden" name="archive_id" id="deleteId">
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
function confirmRestore(id, name) {
    document.getElementById('restoreId').value          = id;
    document.getElementById('restoreName').textContent  = name;
    openModal('restoreModal');
}
function confirmDelete(id, name) {
    document.getElementById('deleteId').value           = id;
    document.getElementById('deleteName').textContent   = name;
    openModal('deleteModal');
}
</script>

<?php require_once 'layout-end.php'; ?>