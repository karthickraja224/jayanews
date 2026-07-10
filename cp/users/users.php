<?php
// users/users.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('users_title');
$breadcrumbs = [__('users_title') => null];

$me       = currentUser();
$is_admin = hasRole(['superadmin', 'admin']);

$roles = [
    'superadmin' => __('role_superadmin'),
    'admin'      => __('role_admin'),
    'editor'     => __('role_editor'),
    'author'     => __('role_author'),
];

// ---------- Handle form submissions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Admin-only actions
    if ($action === 'add' && $is_admin) {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $role  = array_key_exists($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'author';
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($name) || empty($email) || empty($pass)) {
            flashError(__('err_user_fields'));
        } else {
            $exists = db()->fetchOne("SELECT id FROM tbl_users WHERE email=?", 's', $email);
            if ($exists) {
                flashError(__('err_email_exists'));
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                db()->insert(
                    "INSERT INTO tbl_users (name, email, password, role, status) VALUES (?,?,?,?,?)",
                    'ssssi', $name, $email, $hash, $role, $status
                );
                logActivity('create', 'users', __('msg_user_added') . ": $name");
                flashSuccess(__('msg_user_added'));
            }
        }
        header('Location: users.php'); exit();
    }

    if ($action === 'edit') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        // Non-admins may only edit their own record, and cannot change role/status
        $editing_self = ($id === (int)$me['id']);
        if (!$is_admin && !$editing_self) {
            flashError(__('err_no_permission'));
            header('Location: users.php'); exit();
        }

        if (empty($name) || empty($email)) {
            flashError(__('err_name_email'));
            header('Location: users.php' . ($editing_self ? '' : "?edit=$id")); exit();
        }

        $dup = db()->fetchOne("SELECT id FROM tbl_users WHERE email=? AND id!=?", 'si', $email, $id);
        if ($dup) {
            flashError(__('err_email_taken'));
            header('Location: users.php' . ($editing_self ? '' : "?edit=$id")); exit();
        }

        if ($is_admin) {
            $role   = array_key_exists($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'author';
            $status = isset($_POST['status']) ? 1 : 0;

            // Protect against locking out the last superadmin
            if ($role !== 'superadmin' || !$status) {
                $superadmin_count = db()->fetchOne("SELECT COUNT(*) c FROM tbl_users WHERE role='superadmin' AND status=1")['c'];
                $target = db()->fetchOne("SELECT role, status FROM tbl_users WHERE id=?", 'i', $id);
                if ($target && $target['role'] === 'superadmin' && $target['status'] == 1 && $superadmin_count <= 1) {
                    flashError(__('err_last_superadmin'));
                    header('Location: users.php?edit=' . $id); exit();
                }
            }

            if ($pass) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                db()->query("UPDATE tbl_users SET name=?, email=?, password=?, role=?, status=? WHERE id=?", 'ssssii', $name, $email, $hash, $role, $status, $id);
            } else {
                db()->query("UPDATE tbl_users SET name=?, email=?, role=?, status=? WHERE id=?", 'sssii', $name, $email, $role, $status, $id);
            }
        } else {
            // Self-service profile edit — role/status untouched
            if ($pass) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                db()->query("UPDATE tbl_users SET name=?, email=?, password=? WHERE id=?", 'sssi', $name, $email, $hash, $id);
            } else {
                db()->query("UPDATE tbl_users SET name=?, email=? WHERE id=?", 'ssi', $name, $email, $id);
            }
        }

        unset($_SESSION['admin_data']); // refresh cached profile
        logActivity('update', 'users', __('msg_user_updated') . ": $name");
        flashSuccess(__('msg_user_updated'));
        header('Location: users.php'); exit();
    }

    if ($action === 'delete' && $is_admin) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$me['id']) {
            flashError(__('err_delete_self'));
        } else {
            $target = db()->fetchOne("SELECT name, role FROM tbl_users WHERE id=?", 'i', $id);
            $superadmin_count = db()->fetchOne("SELECT COUNT(*) c FROM tbl_users WHERE role='superadmin' AND status=1")['c'];
            if ($target && $target['role'] === 'superadmin' && $superadmin_count <= 1) {
                flashError(__('err_delete_superadmin'));
            } else {
                db()->query("DELETE FROM tbl_users WHERE id=?", 'i', $id);
                logActivity('delete', 'users', __('msg_user_deleted') . ": " . ($target['name'] ?? $id));
                flashSuccess(__('msg_user_deleted'));
            }
        }
        header('Location: users.php'); exit();
    }

    if ($action === 'toggle' && $is_admin) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id !== (int)$me['id']) {
            db()->query("UPDATE tbl_users SET status=IF(status=1,0,1) WHERE id=?", 'i', $id);
            flashSuccess(__('msg_status_changed'));
        } else {
            flashError(__('err_self_status'));
        }
        header('Location: users.php'); exit();
    }
}

// ---------- Data for rendering ----------
$edit_item = null;
if ($is_admin && isset($_GET['edit'])) {
    $edit_item = db()->fetchOne("SELECT * FROM tbl_users WHERE id=?", 'i', (int)$_GET['edit']);
}
$user_list = $is_admin ? db()->fetchAll("SELECT * FROM tbl_users ORDER BY id DESC") : [];

include '../includes/header.php';
?>

<?php if ($is_admin): ?>
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-users"></i> <?= __('users_title') ?></h1>
        <p><?= __('users_subtitle') ?></p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:20px;align-items:start">

    <!-- Add / Edit form -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-<?= $edit_item ? 'user-edit' : 'user-plus' ?>"></i> <?= $edit_item ? __('user edit') : __('Add user') ?></span>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="<?= $edit_item ? 'edit' : 'add' ?>">
                <?php if ($edit_item): ?><input type="hidden" name="id" value="<?= $edit_item['id'] ?>"><?php endif; ?>
                <div class="form-group">
                    <label class="form-label"><?= __('label_name') ?> <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_item['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_email') ?> <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_item['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_password') ?> <?= $edit_item ? '' : '<span class="required">*</span>' ?></label>
                    <input type="password" name="password" class="form-control" placeholder="<?= $edit_item ? __('btn_update') : '••••••••' ?>" <?= $edit_item ? '' : 'required' ?>>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_role') ?></label>
                        <select name="role" class="form-control">
                            <?php foreach ($roles as $rk => $rl): ?>
                            <option value="<?= $rk ?>" <?= ($edit_item['role'] ?? 'author') === $rk ? 'selected' : '' ?>><?= $rl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_status') ?></label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                            <input type="checkbox" name="status" <?= (!$edit_item || $edit_item['status']) ? 'checked' : '' ?>> <?= __('status_active') ?>
                        </label>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $edit_item ? __('btn_update') : __('btn_save') ?></button>
                    <?php if ($edit_item): ?><a href="users.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-list"></i> <?= __('users_title') ?></span></div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th><?= __('label_name') ?></th>
                        <th><?= __('label_role') ?></th>
                        <th><?= __('label_status') ?></th>
                        <th><?= __('col_last_edit') ?></th>
                        <th><?= __('col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($user_list): foreach ($user_list as $i => $u): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--bg-hover);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--accent);flex-shrink:0;">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($u['name']) ?></strong>
                                <?php if ((int)$u['id'] === (int)$me['id']): ?><span class="badge badge-info" style="font-size:9px;margin-left:4px"><?= __('profile') ?></span><?php endif; ?>
                                <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-secondary"><?= $roles[$u['role']] ?? ucfirst($u['role']) ?></span></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="badge <?= $u['status'] ? 'badge-success' : 'badge-danger' ?>" style="border:none;cursor:pointer" <?= (int)$u['id'] === (int)$me['id'] ? 'disabled title="' . __('err_self_status') . '"' : '' ?>>
                                <?= $u['status'] ? __('status_active') : __('status_inactive') ?>
                            </button>
                        </form>
                    </td>
                    <td><span class="text-muted"><?= $u['last_login'] ? timeAgo($u['last_login']) : '—' ?></span></td>
                    <td>
                        <div class="action-btns">
                            <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                            <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('<?= __('confirm_delete') ?>')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6"><div class="empty-state"><i class="fas fa-users"></i><h3><?= __('no_news') ?></h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Non-admin: self profile only -->
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-user"></i> <?= __('profile') ?></h1>
        <p><?= __('profile') ?></p>
    </div>
</div>

<div class="form-layout">
    <div class="form-main" style="max-width:500px">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-id-card"></i> <?= __('profile') ?></span></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $me['id'] ?>">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_name') ?> <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($me['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_email') ?> <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($me['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_password') ?></label>
                        <input type="password" name="password" class="form-control" placeholder="<?= __('btn_update') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_role') ?></label>
                        <input type="text" class="form-control" value="<?= $roles[$me['role']] ?? ucfirst($me['role']) ?>" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>