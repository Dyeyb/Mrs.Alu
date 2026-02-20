<?php
// ══════════════════════════════════════════════
//  archive.php – Action Handler (POST only)
//  Handles all write operations:
//    action=archive        → move user → archive_users (from users.php)
//    action=delete_user    → permanently delete from users (from users.php)
//    action=restore        → move archive_users → users (from archive-users.php)
//    action=delete_archive → permanently delete from archive_users (from archive-users.php)
//  Mrs. Alu Admin Panel
// ══════════════════════════════════════════════

require_once 'db.php';
require_once 'helpers.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('users.php');
}

$action = $_POST['action'] ?? '';

switch ($action) {

    // ──────────────────────────────────────────
    //  ARCHIVE: move user → archive_users
    //  Called from: users.php
    // ──────────────────────────────────────────
    case 'archive':
        $userId = (int)($_POST['user_id'] ?? 0);

        if (!$userId) {
            setFlash('error', 'Invalid user ID.');
            redirect('users.php');
        }

        // Load the user
        $sel = getDB()->prepare("SELECT * FROM users WHERE user_id = :id");
        $sel->execute([':id' => $userId]);
        $user = $sel->fetch();

        if (!$user) {
            setFlash('error', 'User not found.');
            redirect('users.php');
        }

        try {
            getDB()->beginTransaction();

            // Insert into archive_users
            $ins = getDB()->prepare("
                INSERT INTO archive_users
                    (user_id, first_name, last_name, email, password_hash, phone, original_created_at)
                VALUES
                    (:uid, :fn, :ln, :email, :pw, :phone, :created)
            ");
            $ins->execute([
                ':uid'     => $user['user_id'],
                ':fn'      => $user['first_name'],
                ':ln'      => $user['last_name'],
                ':email'   => $user['email'],
                ':pw'      => $user['password_hash'],
                ':phone'   => $user['phone'],
                ':created' => $user['created_at'],
            ]);

            // Remove from users
            $del = getDB()->prepare("DELETE FROM users WHERE user_id = :id");
            $del->execute([':id' => $userId]);

            getDB()->commit();

            setFlash('success', "{$user['first_name']} {$user['last_name']} has been archived and moved to archive_users.");
        } catch (Exception $e) {
            getDB()->rollBack();
            setFlash('error', 'Archive failed: ' . $e->getMessage());
        }

        redirect('users.php');
        break;


    // ──────────────────────────────────────────
    //  DELETE USER: permanently remove from users
    //  Called from: users.php
    // ──────────────────────────────────────────
    case 'delete_user':
        $userId = (int)($_POST['user_id'] ?? 0);

        if (!$userId) {
            setFlash('error', 'Invalid user ID.');
            redirect('users.php');
        }

        // Get name for message
        $sel = getDB()->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id");
        $sel->execute([':id' => $userId]);
        $user = $sel->fetch();

        if (!$user) {
            setFlash('error', 'User not found.');
            redirect('users.php');
        }

        $del = getDB()->prepare("DELETE FROM users WHERE user_id = :id");
        if ($del->execute([':id' => $userId])) {
            setFlash('success', "{$user['first_name']} {$user['last_name']} was permanently deleted from users.");
        } else {
            setFlash('error', 'Delete failed. Please try again.');
        }

        redirect('users.php');
        break;


    // ──────────────────────────────────────────
    //  RESTORE: move archive_users → users
    //  Called from: archive-users.php
    // ──────────────────────────────────────────
    case 'restore':
        $archiveId = (int)($_POST['archive_id'] ?? 0);

        if (!$archiveId) {
            setFlash('error', 'Invalid archive ID.');
            redirect('archive-users.php');
        }

        // Load the archived record
        $sel = getDB()->prepare("SELECT * FROM archive_users WHERE archive_id = :id");
        $sel->execute([':id' => $archiveId]);
        $archived = $sel->fetch();

        if (!$archived) {
            setFlash('error', 'Archived record not found.');
            redirect('archive-users.php');
        }

        // Check if email is already back in users table
        $chk = getDB()->prepare("SELECT COUNT(*) FROM users WHERE email = :e");
        $chk->execute([':e' => $archived['email']]);
        if ((int)$chk->fetchColumn() > 0) {
            setFlash('error', "Cannot restore: the email <strong>{$archived['email']}</strong> is already registered in the active users table.");
            redirect('archive-users.php');
        }

        try {
            getDB()->beginTransaction();

            // Re-insert into users (new user_id will be auto-generated)
            $ins = getDB()->prepare("
                INSERT INTO users
                    (first_name, last_name, email, password_hash, phone, user_type, created_at, updated_at)
                VALUES
                    (:fn, :ln, :email, :pw, :phone, 'user', :created, NOW())
            ");
            $ins->execute([
                ':fn'      => $archived['first_name'],
                ':ln'      => $archived['last_name'],
                ':email'   => $archived['email'],
                ':pw'      => $archived['password_hash'],
                ':phone'   => $archived['phone'],
                ':created' => $archived['original_created_at'] ?? date('Y-m-d H:i:s'),
            ]);

            // Remove from archive
            $del = getDB()->prepare("DELETE FROM archive_users WHERE archive_id = :id");
            $del->execute([':id' => $archiveId]);

            getDB()->commit();

            setFlash('success', "{$archived['first_name']} {$archived['last_name']} has been restored to the active users table.");
        } catch (Exception $e) {
            getDB()->rollBack();
            setFlash('error', 'Restore failed: ' . $e->getMessage());
        }

        redirect('archive-users.php');
        break;


    // ──────────────────────────────────────────
    //  DELETE ARCHIVE: permanently erase from archive_users
    //  Called from: archive-users.php
    // ──────────────────────────────────────────
    case 'delete_archive':
        $archiveId = (int)($_POST['archive_id'] ?? 0);

        if (!$archiveId) {
            setFlash('error', 'Invalid archive ID.');
            redirect('archive-users.php');
        }

        // Get name for message
        $sel = getDB()->prepare("SELECT first_name, last_name FROM archive_users WHERE archive_id = :id");
        $sel->execute([':id' => $archiveId]);
        $archived = $sel->fetch();

        if (!$archived) {
            setFlash('error', 'Archived record not found.');
            redirect('archive-users.php');
        }

        $del = getDB()->prepare("DELETE FROM archive_users WHERE archive_id = :id");
        if ($del->execute([':id' => $archiveId])) {
            setFlash('success', "{$archived['first_name']} {$archived['last_name']} was permanently deleted from archive_users.");
        } else {
            setFlash('error', 'Delete failed. Please try again.');
        }

        redirect('archive-users.php');
        break;


    // ──────────────────────────────────────────
    //  Unknown action
    // ──────────────────────────────────────────
    default:
        setFlash('error', "Unknown action: " . htmlspecialchars($action));
        redirect('users.php');
}