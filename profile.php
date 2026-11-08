<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$uid = user_id();
$errors = [];

$stmt = $pdo->prepare('SELECT full_name, email, bio, department, semester, password_hash FROM users WHERE id=?');
$stmt->execute([$uid]);
$u = $stmt->fetch();
if (!$u) { session_destroy(); go('login.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!csrf_check()) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } elseif (($_POST['action'] ?? '') === 'profile') {
        $name = trim($_POST['full_name'] ?? '');
        $bio  = trim($_POST['bio'] ?? '');
        $dept = trim($_POST['department'] ?? '');
        $sem  = trim($_POST['semester'] ?? '');
        if ($name === '' || mb_strlen($name) > 100) { $errors[] = 'Full name is required (max 100 characters).'; }
        if (mb_strlen($bio) > 500) { $errors[] = 'Bio must be 500 characters or less.'; }
        if (mb_strlen($dept) > 100) { $errors[] = 'Department must be 100 characters or less.'; }
        if ($sem !== '' && (!ctype_digit($sem) || (int)$sem < 1 || (int)$sem > 12)) { $errors[] = 'Semester must be a number from 1 to 12.'; }
        if (!$errors) {
            $pdo->prepare('UPDATE users SET full_name=?, bio=?, department=?, semester=? WHERE id=?')
                ->execute([$name, $bio, $dept, $sem === '' ? null : (int)$sem, $uid]);
            $_SESSION['user_name'] = $name;
            flash('Profile updated.');
            go('profile.php');
        }
        $u['full_name'] = $name; $u['bio'] = $bio; $u['department'] = $dept; $u['semester'] = $sem;
    } elseif (($_POST['action'] ?? '') === 'password') {
        $cur = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($cur, $u['password_hash'])) { $errors[] = 'Current password is incorrect.'; }
        elseif (strlen($new) < 8) { $errors[] = 'New password must be at least 8 characters.'; }
        else {
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
            session_regenerate_id(true);
            flash('Password changed.');
            go('profile.php');
        }
    }
}

$pageTitle = 'Profile';
include 'includes/header.php';
?>
<section class="wide">
    <h1>My Profile</h1>
    <p class="muted"><?= e($u['email']) ?> | <a href="user.php?id=<?= $uid ?>">View public profile</a></p>
    <?php if ($errors): ?>
        <div class="alert"><?php foreach ($errors as $er): ?><p><?= e($er) ?></p><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="two-col">
        <div class="panel">
            <h2>Details</h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="profile">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required>
                <label for="department">Department</label>
                <input type="text" id="department" name="department" value="<?= e($u['department']) ?>" placeholder="e.g. Computer Science">
                <label for="semester">Semester</label>
                <input type="number" id="semester" name="semester" min="1" max="12" value="<?= e($u['semester']) ?>">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4" maxlength="500"><?= e($u['bio']) ?></textarea>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
        <div class="panel">
            <h2>Change password</h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <label for="current_password">Current password</label>
                <input type="password" id="current_password" name="current_password" required>
                <label for="new_password">New password (min 8 characters)</label>
                <input type="password" id="new_password" name="new_password" required>
                <button type="submit" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
