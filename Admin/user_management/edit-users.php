<?php
// ══════════════════════════════════════════════
//  edit-users.php – Edit Existing User
//  Mrs. Alu Admin Panel
// ══════════════════════════════════════════════

require_once 'db.php';
require_once 'helpers.php';

// ── Load user ─────────────────────────────────
$userId = (int)($_GET['id'] ?? $_POST['user_id'] ?? 0);

if (!$userId) {
    setFlash('error', 'No user ID provided.');
    redirect('users.php');
}

$stmt = getDB()->prepare("SELECT * FROM users WHERE user_id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User not found.');
    redirect('users.php');
}

// ── Handle POST submission ────────────────────
$formError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors    = [];
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['email']      ?? '');
    $password  = $_POST['password']        ?? '';
    $phone     = trim($_POST['phone']      ?? '');
    $userType  = $_POST['user_type']       ?? 'user';

    // Validate
    if (!$firstName)                                    $errors[] = 'First name is required.';
    if (!$lastName)                                     $errors[] = 'Last name is required.';
    if (!$email)                                        $errors[] = 'Email address is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email address is not valid.';
    if ($password && strlen($password) < 8)             $errors[] = 'New password must be at least 8 characters.';
    if (!in_array($userType, ['admin','user']))          $errors[] = 'Invalid user type.';

    // Email unique (exclude self)
    if (!$errors) {
        $chk = getDB()->prepare("SELECT COUNT(*) FROM users WHERE email = :e AND user_id != :id");
        $chk->execute([':e' => $email, ':id' => $userId]);
        if ((int)$chk->fetchColumn() > 0) $errors[] = 'That email is already used by another user.';
    }

    if ($errors) {
        $formError = implode(' ', $errors);
        // Reflect new values in $user so form keeps what was typed
        $user = array_merge($user, [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone,
            'user_type'  => $userType,
        ]);
    } else {
        // Build UPDATE
        $fields = [
            'first_name = :fn',
            'last_name  = :ln',
            'email      = :email',
            'phone      = :phone',
            'user_type  = :utype',
            'updated_at = NOW()',
        ];
        $params = [
            ':fn'    => $firstName,
            ':ln'    => $lastName,
            ':email' => $email,
            ':phone' => $phone ?: null,
            ':utype' => $userType,
            ':id'    => $userId,
        ];

        if ($password) {
            $fields[]   = 'password_hash = :pw';
            $params[':pw'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $upd = getDB()->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :id");
        $upd->execute($params);

        setFlash('success', "User {$firstName} {$lastName} was updated successfully.");
        redirect('users.php');
    }
}

// ── Layout vars ───────────────────────────────
$pageTitle  = 'Edit User';
$activePage = 'users';
$breadcrumb = ['Users' => 'users.php', 'Edit User' => ''];

require_once 'layout.php';
?>

<style>
    .form-card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden;max-width:760px;margin:0 auto;}
    .form-card-head{padding:24px 32px;border-bottom:1px solid var(--border);background:#fefcf8;display:flex;align-items:center;justify-content:space-between;}
    .form-card-title{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:600;color:var(--dark);}
    .form-card-title span{color:var(--gold);font-style:italic;}
    .form-card-sub{font-size:13px;color:#aaa;margin-top:3px;}
    .form-body{padding:32px;}
    .form-footer{padding:20px 32px;border-top:1px solid var(--border);background:#fafaf8;display:flex;align-items:center;justify-content:space-between;}

    .section-divider{grid-column:1/-1;border:none;border-top:1px solid var(--border);margin:8px 0;}
    .section-heading{grid-column:1/-1;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);padding-bottom:4px;border-bottom:1px solid #f0e8d0;}

    .inline-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:8px;padding:12px 16px;font-size:13px;margin-bottom:0;display:flex;align-items:flex-start;gap:10px;}
    .inline-error svg{width:16px;height:16px;flex-shrink:0;margin-top:1px;}

    .user-info-bar{display:flex;align-items:center;gap:14px;padding:18px 32px;background:linear-gradient(135deg,#fefcf8,#fdf5e4);border-bottom:1px solid var(--border);}
    .uib-avatar{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#c9a561,#e8c97a);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:700;color:#1a0e05;flex-shrink:0;}
    .uib-name{font-size:16px;font-weight:600;color:var(--dark);}
    .uib-meta{font-size:12px;color:#aaa;margin-top:3px;}
    .uib-id{margin-left:auto;font-family:'Cormorant Garamond',serif;font-size:22px;color:#ddd;font-weight:700;}

    .pw-wrap{position:relative;}
    .pw-wrap .form-input{padding-right:42px;width:100%;}
    .pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;}
    .pw-toggle:hover{color:var(--gold);}
    .pw-toggle svg{width:18px;height:18px;}

    .type-cards{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .type-card{border:2px solid var(--border);border-radius:10px;padding:14px 16px;cursor:pointer;transition:var(--ease);display:flex;align-items:center;gap:12px;}
    .type-card:hover{border-color:var(--gold);background:#fefcf8;}
    .type-card input[type=radio]{display:none;}
    .type-card.selected{border-color:var(--gold);background:linear-gradient(135deg,#fefcf8,#fdf5e4);}
    .tc-icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .tc-icon svg{width:18px;height:18px;}
    .tc-admin .tc-icon{background:#fef3c7;color:#92400e;}
    .tc-user  .tc-icon{background:#f0fdf4;color:#166534;}
    .tc-label{font-size:13px;font-weight:600;color:var(--dark);}
    .tc-desc{font-size:11px;color:#aaa;margin-top:2px;}
</style>

<div class="form-card anim d1">

    <!-- Card head -->
    <div class="form-card-head">
        <div>
            <div class="form-card-title">Edit <span>User</span></div>
            <div class="form-card-sub">Update the account details below</div>
        </div>
        <a href="users.php" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15,18 9,12 15,6"/>
            </svg>
            Back to Users
        </a>
    </div>

    <!-- User info bar -->
    <div class="user-info-bar">
        <div class="uib-avatar"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?></div>
        <div>
            <div class="uib-name"><?= e($user['first_name']) ?> <?= e($user['last_name']) ?></div>
            <div class="uib-meta">
                <?= e($user['email']) ?> &nbsp;·&nbsp;
                <span class="tag tag-<?= e($user['user_type']) ?>"><?= ucfirst(e($user['user_type'])) ?></span>
                &nbsp;·&nbsp; Joined <?= formatDate($user['created_at']) ?>
            </div>
        </div>
        <div class="uib-id"><?= fmtId($user['user_id']) ?></div>
    </div>

    <!-- Inline error -->
    <?php if ($formError): ?>
    <div class="inline-error" style="margin:24px 32px 0;">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= e($formError) ?>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="edit-users.php" novalidate>
        <input type="hidden" name="user_id" value="<?= $userId ?>">

        <div class="form-body">
            <div class="form-grid">

                <div class="section-heading">Personal Information</div>

                <div class="form-group">
                    <label class="form-label">First Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="first_name" class="form-input"
                           value="<?= e($user['first_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Last Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="last_name" class="form-input"
                           value="<?= e($user['last_name']) ?>" required>
                </div>

                <div class="form-group full">
                    <label class="form-label">Email Address <span style="color:#dc2626;">*</span></label>
                    <input type="email" name="email" class="form-input"
                           value="<?= e($user['email']) ?>" required>
                </div>

                <div class="form-group full">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input"
                           placeholder="+1 555-0100"
                           value="<?= e($user['phone'] ?? '') ?>">
                </div>

                <hr class="section-divider">
                <div class="section-heading">Change Password</div>

                <div class="form-group full">
                    <label class="form-label">New Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="pwField" class="form-input"
                               placeholder="Leave blank to keep current password" minlength="8">
                        <button type="button" class="pw-toggle" onclick="togglePw()">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="form-hint">Only fill in if you want to change the password (min. 8 characters)</div>
                </div>

                <hr class="section-divider">
                <div class="section-heading">Role & Permissions</div>

                <div class="form-group full">
                    <label class="form-label">User Type <span style="color:#dc2626;">*</span></label>
                    <div class="type-cards">
                        <label class="type-card tc-user <?= $user['user_type']==='user'?'selected':'' ?>"
                               onclick="selectType(this,'user')">
                            <input type="radio" name="user_type" value="user"
                                   <?= $user['user_type']==='user'?'checked':'' ?>>
                            <div class="tc-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="tc-label">Regular User</div>
                                <div class="tc-desc">Standard access only</div>
                            </div>
                        </label>
                        <label class="type-card tc-admin <?= $user['user_type']==='admin'?'selected':'' ?>"
                               onclick="selectType(this,'admin')">
                            <input type="radio" name="user_type" value="admin"
                                   <?= $user['user_type']==='admin'?'checked':'' ?>>
                            <div class="tc-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="tc-label">Administrator</div>
                                <div class="tc-desc">Full admin privileges</div>
                            </div>
                        </label>
                    </div>
                </div>

            </div><!-- /form-grid -->
        </div><!-- /form-body -->

        <div class="form-footer">
            <a href="users.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17,21 17,13 7,13 7,21"/>
                    <polyline points="7,3 7,8 15,8"/>
                </svg>
                Save Changes
            </button>
        </div>
    </form>

</div><!-- /form-card -->

<script>
function togglePw() {
    const f = document.getElementById('pwField');
    f.type = f.type === 'password' ? 'text' : 'password';
}
function selectType(label, value) {
    document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input[type=radio]').checked = true;
}
</script>

<?php require_once 'layout-end.php'; ?>