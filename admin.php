<?php
require 'config/db.php';
require 'includes/auth.php';
require_admin();
$uid = user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!csrf_check()) {
        flash('Invalid request. Please try again.');
    } else {
        $act = $_POST['action'] ?? '';
        if ($act === 'delete_user') {
            $target = (int)($_POST['id'] ?? 0);
            if ($target === $uid) { flash('You cannot delete your own account here.'); }
            else { $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$target]); flash('User deleted.'); }
        } elseif ($act === 'delete_skill') {
            $pdo->prepare('DELETE FROM skills WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
            flash('Skill deleted.');
        } elseif ($act === 'add_category') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '' || mb_strlen($name) > 60) { flash('Category name is required (max 60 characters).'); }
            else {
                try { $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute([$name]); flash('Category added.'); }
                catch (PDOException $ex) { flash('That category already exists.'); }
            }
        }
    }
    go('admin.php');
}

$totals = [
    'Users'      => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'Skills'     => $pdo->query('SELECT COUNT(*) FROM skills')->fetchColumn(),
    'Swaps'      => $pdo->query('SELECT COUNT(*) FROM swaps')->fetchColumn(),
    'Completed'  => $pdo->query("SELECT COUNT(*) FROM swaps WHERE status='completed'")->fetchColumn(),
];
$users  = $pdo->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC LIMIT 100')->fetchAll();
$skills = $pdo->query('SELECT s.id, s.title, u.full_name FROM skills s JOIN users u ON u.id=s.user_id ORDER BY s.id DESC LIMIT 100')->fetchAll();

$pageTitle = 'Admin';
include 'includes/header.php';
?>
<section class="wide">
    <h1>Admin Panel</h1>
    <div class="stats">
        <?php foreach ($totals as $label => $n): ?>
            <div class="stat"><span class="num"><?= (int)$n ?></span><span><?= e($label) ?></span></div>
        <?php endforeach; ?>
    </div>

    <h2>Users</h2>
    <div class="table-wrap"><table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr>
        <?php foreach ($users as $r): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td><td><?= e($r['full_name']) ?></td><td><?= e($r['email']) ?></td>
            <td><?= e($r['role']) ?></td><td><?= e(fmt_date($r['created_at'])) ?></td>
            <td>
                <?php if ((int)$r['id'] !== $uid): ?>
                <form method="post" class="inline" data-confirm="Delete this user and all their data?">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="link-btn">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table></div>

    <h2>Skills</h2>
    <div class="table-wrap"><table>
        <tr><th>ID</th><th>Skill</th><th>Owner</th><th></th></tr>
        <?php foreach ($skills as $r): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td><td><?= e($r['title']) ?></td><td><?= e($r['full_name']) ?></td>
            <td>
                <form method="post" class="inline" data-confirm="Delete this skill?">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete_skill"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="link-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table></div>

    <h2>Add category</h2>
    <form method="post" class="filters">
        <?= csrf_field() ?><input type="hidden" name="action" value="add_category">
        <input type="text" name="name" placeholder="Category name" required>
        <button type="submit" class="btn">Add</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>
