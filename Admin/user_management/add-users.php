<?php
// ══════════════════════════════════════════════
//  add-users.php – Add New User
//  Mrs. Alu Admin Panel
// ══════════════════════════════════════════════

require_once 'db.php';
require_once 'helpers.php';

// ── Handle POST submission ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['email']      ?? '');
    $password  = $_POST['password']        ?? '';
    $phone     = trim($_POST['phone']      ?? '');
    $userType  = $_POST['user_type']       ?? 'user';

    // Validate
    if (!$firstName)                              $errors[] = 'First name is required.';
    if (!$lastName)                               $errors[] = 'Last name is required.';
    if (!$email)                                  $errors[] = 'Email address is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email address is not valid.';
    if (!$password)                               $errors[] = 'Password is required.';
    elseif (strlen($password) < 8)               $errors[] = 'Password must be at least 8 characters.';
    if (!in_array($userType, ['admin','user']))   $errors[] = 'Invalid user type.';

    // Check email unique
    if (!$errors) {
        $chk = getDB()->prepare("SELECT COUNT(*) FROM users WHERE email = :e");
        $chk->execute([':e' => $email]);
        if ((int)$chk->fetchColumn() > 0) {
            $errors[] = 'That email address is already registered.';
        }
    }

    if ($errors) {
        // Store in session so they survive redirect; or just keep in $errors for inline display
        $formError = implode(' ', $errors);
    } else {
        $stmt = getDB()->prepare("
            INSERT INTO users (first_name, last_name, email, password_hash, phone, user_type)
            VALUES (:fn, :ln, :email, :pw, :phone, :utype)
        ");
        $stmt->execute([
            ':fn'    => $firstName,
            ':ln'    => $lastName,
            ':email' => $email,
            ':pw'    => password_hash($password, PASSWORD_BCRYPT),
            ':phone' => $phone ?: null,
            ':utype' => $userType,
        ]);
        setFlash('success', "User {$firstName} {$lastName} was created successfully.");
        redirect('users.php');
    }
}

// ── Layout vars ───────────────────────────────
$pageTitle  = 'Add User';
$activePage = 'add-users';
$breadcrumb = ['Users' => 'users.php', 'Add User' => ''];

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

    .inline-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:8px;padding:12px 16px;font-size:13px;margin-bottom:24px;display:flex;align-items:flex-start;gap:10px;}
    .inline-error svg{width:16px;height:16px;flex-shrink:0;margin-top:1px;}

    .pw-wrap{position:relative;}
    .pw-wrap .form-input{padding-right:42px;width:100%;}
    .pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;display:flex;align-items:center;}
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
            <div class="form-card-title">Add New <span>User</span></div>
            <div class="form-card-sub">Fill in the details to create a new account</div>
        </div>
        <a href="users.php" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15,18 9,12 15,6"/>
            </svg>
            Back to Users
        </a>
    </div>

    <!-- Inline error -->
    <?php if (!empty($formError)): ?>
    <div class="inline-error" style="margin:24px 32px 0;">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= e($formError) ?>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="add-users.php" novalidate>
        <div class="form-body">
            <div class="form-grid">

                <!-- Personal info section -->
                <div class="section-heading">Personal Information</div>

                <div class="form-group">
                    <label class="form-label">First Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="first_name" class="form-input"
                           placeholder="Jane"
                           value="<?= e($_POST['first_name'] ?? '') ?>"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Last Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="last_name" class="form-input"
                           placeholder="Doe"
                           value="<?= e($_POST['last_name'] ?? '') ?>"
                           required>
                </div>

                <div class="form-group full">
                    <label class="form-label">Email Address <span style="color:#dc2626;">*</span></label>
                    <input type="email" name="email" class="form-input"
                           placeholder="jane.doe@example.com"
                           value="<?= e($_POST['email'] ?? '') ?>"
                           required>
                </div>

                <div class="form-group full">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input"
                           placeholder="+1 555-0100"
                           value="<?= e($_POST['phone'] ?? '') ?>">
                    <div class="form-hint">Optional – include country code</div>
                </div>

                <!-- Divider -->
                <hr class="section-divider">
                <div class="section-heading">Security</div>

                <div class="form-group full">
                    <label class="form-label">Password <span style="color:#dc2626;">*</span></label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="pwField" class="form-input"
                               placeholder="Minimum 8 characters" minlength="8" required>
                        <button type="button" class="pw-toggle" onclick="togglePw()">
                            <svg id="pwEye" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="form-hint">Must be at least 8 characters long</div>
                </div>

                <!-- Divider -->
                <hr class="section-divider">
                <div class="section-heading">Role & Permissions</div>

                <div class="form-group full">
                    <label class="form-label">User Type <span style="color:#dc2626;">*</span></label>
                    <div class="type-cards">
                        <label class="type-card tc-user <?= ($_POST['user_type']??'user')==='user'?'selected':'' ?>"
                               onclick="selectType(this,'user')">
                            <input type="radio" name="user_type" value="user"
                                   <?= ($_POST['user_type']??'user')==='user'?'checked':'' ?>>
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

                        <label class="type-card tc-admin <?= ($_POST['user_type']??'user')==='admin'?'selected':'' ?>"
                               onclick="selectType(this,'admin')">
                            <input type="radio" name="user_type" value="admin"
                                   <?= ($_POST['user_type']??'')==='admin'?'checked':'' ?>>
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
            <a href="users.php" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="15,18 9,12 15,6"/>
                </svg>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create User
            </button>
        </div>
    </form>

</div><!-- /form-card -->

<script>
// Password visibility toggle
function togglePw() {
    const f = document.getElementById('pwField');
    f.type = f.type === 'password' ? 'text' : 'password';
}

// Radio card selector
function selectType(label, value) {
    document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input[type=radio]').checked = true;
}
</script>

<?php require_once 'layout-end.php'; ?>