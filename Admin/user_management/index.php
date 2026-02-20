<?php
// ══════════════════════════════════════════════
//  index.php – Dashboard
//  Mrs. Alu Admin Panel
// ══════════════════════════════════════════════

require_once 'db.php';
require_once 'helpers.php';

// ── Stats ─────────────────────────────────────
$db = getDB();

// User counts
$typeRows = $db->query("SELECT user_type, COUNT(*) as cnt FROM users GROUP BY user_type")->fetchAll();
$counts   = ['admin' => 0, 'user' => 0, 'total' => 0];
foreach ($typeRows as $row) {
    $counts[$row['user_type']] = (int)$row['cnt'];
    $counts['total']          += (int)$row['cnt'];
}

// New this month
$newThisMonth = (int)$db->query("
    SELECT COUNT(*) FROM users
    WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())
")->fetchColumn();

// Archived count
$archivedCount = (int)$db->query("SELECT COUNT(*) FROM archive_users")->fetchColumn();

// ── Layout vars ───────────────────────────────
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
$breadcrumb = [];   // no extra breadcrumb on dashboard

require_once 'layout.php';
?>

<style>
    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;}
    .stat-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px;box-shadow:var(--shadow);position:relative;overflow:hidden;transition:var(--ease);}
    .stat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),var(--gold-light));transform:scaleX(0);transform-origin:left;transition:transform .35s ease;}
    .stat-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.1);}
    .stat-card:hover::after{transform:scaleX(1);}
    .sc-label{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#aaa;margin-bottom:10px;}
    .sc-value{font-family:'Cormorant Garamond',serif;font-size:40px;font-weight:700;color:var(--dark);line-height:1;margin-bottom:6px;}
    .sc-meta{font-size:12px;color:#aaa;}
    .sc-icon{position:absolute;top:20px;right:20px;width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#f8f3e8,#f0e8d0);display:flex;align-items:center;justify-content:center;}
    .sc-icon svg{width:20px;height:20px;color:var(--gold);}

    .section-head{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:600;color:var(--dark);margin-bottom:18px;}
    .section-head span{color:var(--gold);font-style:italic;}

    .quick-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:36px;}
    .qa-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:22px 20px;box-shadow:var(--shadow);display:flex;align-items:center;gap:16px;text-decoration:none;transition:var(--ease);}
    .qa-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.1);border-color:var(--gold);}
    .qa-ico{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .qa-ico svg{width:22px;height:22px;}
    .qa-gold{background:linear-gradient(135deg,#f8f3e8,#f0e8d0);color:var(--gold);}
    .qa-blue{background:#eff6ff;color:#2563eb;}
    .qa-amber{background:#fffbeb;color:#92400e;}
    .qa-green{background:#f0fdf4;color:#166534;}
    .qa-lbl{font-size:15px;font-weight:600;color:var(--dark);}
    .qa-sub{font-size:12px;color:#aaa;margin-top:3px;}

    .recent-card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden;}
    .rc-head{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border);background:#fefcf8;}
    .rc-title{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:600;color:var(--dark);}
    .rc-title span{color:var(--gold);font-style:italic;}
    .rc-empty{padding:60px 24px;text-align:center;}
    .rc-empty-ico{width:56px;height:56px;background:linear-gradient(135deg,#f8f3e8,#f0e8d0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;}
    .rc-empty-ico svg{width:24px;height:24px;color:var(--gold);}
    .rc-empty p{font-size:14px;color:#aaa;}
</style>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card anim d1">
        <div class="sc-label">Total Users</div>
        <div class="sc-value"><?= $counts['total'] ?></div>
        <div class="sc-meta">Registered accounts</div>
        <div class="sc-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
    </div>
    <div class="stat-card anim d2">
        <div class="sc-label">Admins</div>
        <div class="sc-value"><?= $counts['admin'] ?></div>
        <div class="sc-meta">Admin accounts</div>
        <div class="sc-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
    </div>
    <div class="stat-card anim d3">
        <div class="sc-label">Regular Users</div>
        <div class="sc-value"><?= $counts['user'] ?></div>
        <div class="sc-meta">Standard accounts</div>
        <div class="sc-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
    </div>
    <div class="stat-card anim d4">
        <div class="sc-label">New This Month</div>
        <div class="sc-value"><?= $newThisMonth ?></div>
        <div class="sc-meta"><?= date('F Y') ?></div>
        <div class="sc-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="section-head anim d4">Quick <span>Actions</span></div>
<div class="quick-grid anim d5">
    <a href="users.php" class="qa-card">
        <div class="qa-ico qa-gold">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <div class="qa-lbl">All Users</div>
            <div class="qa-sub"><?= $counts['total'] ?> total accounts</div>
        </div>
    </a>

    <a href="add-users.php" class="qa-card">
        <div class="qa-ico qa-blue">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
        </div>
        <div>
            <div class="qa-lbl">Add New User</div>
            <div class="qa-sub">Create a new account</div>
        </div>
    </a>

    <a href="archive-users.php" class="qa-card">
        <div class="qa-ico qa-amber">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <polyline points="21 8 21 21 3 21 3 8"/>
                <rect x="1" y="3" width="22" height="5"/>
                <line x1="10" y1="12" x2="14" y2="12"/>
            </svg>
        </div>
        <div>
            <div class="qa-lbl">Archived Users</div>
            <div class="qa-sub"><?= $archivedCount ?> archived records</div>
        </div>
    </a>
</div>

<!-- Recent activity placeholder -->
<div class="recent-card anim d5">
    <div class="rc-head">
        <div class="rc-title">Recent <span>Activity</span></div>
        <a href="users.php" class="btn btn-secondary btn-sm">View All Users</a>
    </div>
    <div class="rc-empty">
        <div class="rc-empty-ico">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <p>Activity log will appear here as users are added and modified.</p>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>