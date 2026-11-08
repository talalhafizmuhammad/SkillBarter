<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$uid = user_id();
$cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$catIds = array_map('intval', array_column($cats, 'id'));
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!csrf_check()) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {
        $act = $_POST['action'] ?? '';
        if ($act === 'add_skill') {
            $title = trim($_POST['title'] ?? '');
            $desc  = trim($_POST['description'] ?? '');
            $cat   = (int)($_POST['category_id'] ?? 0);
            $level = $_POST['level'] ?? 'beginner';
            if (!in_array($level, ['beginner', 'intermediate', 'expert'], true)) { $level = 'beginner'; }
            if ($title === '' || mb_strlen($title) > 100) { $errors[] = 'Skill title is required (max 100 characters).'; }
            if (mb_strlen($desc) > 500) { $errors[] = 'Description must be 500 characters or less.'; }
            if (!in_array($cat, $catIds, true)) { $errors[] = 'Please choose a category.'; }
            if (!$errors) {
                $pdo->prepare('INSERT INTO skills (user_id, category_id, title, description, level) VALUES (?,?,?,?,?)')
                    ->execute([$uid, $cat, $title, $desc, $level]);
                flash('Skill added.');
                go('skills.php');
            }
        } elseif ($act === 'delete_skill') {
            $sid = (int)($_POST['id'] ?? 0);
            $chk = $pdo->prepare("SELECT COUNT(*) FROM swaps WHERE status IN ('pending','accepted') AND (requested_skill_id=? OR offered_skill_id=?)");
            $chk->execute([$sid, $sid]);
            if ($chk->fetchColumn() > 0) {
                flash('This skill is part of an open swap and cannot be deleted yet.');
            } else {
                $pdo->prepare('DELETE FROM skills WHERE id=? AND user_id=?')->execute([$sid, $uid]);
                flash('Skill deleted.');
            }
            go('skills.php');
        } elseif ($act === 'add_want') {
            $title = trim($_POST['want_title'] ?? '');
            if ($title === '' || mb_strlen($title) > 100) {
                $errors[] = 'Enter the skill you want to learn (max 100 characters).';
            } else {
                $pdo->prepare('INSERT INTO skill_wants (user_id, title) VALUES (?,?)')->execute([$uid, $title]);
                flash('Added to your wanted skills.');
                go('skills.php');
            }
        } elseif ($act === 'delete_want') {
            $pdo->prepare('DELETE FROM skill_wants WHERE id=? AND user_id=?')->execute([(int)($_POST['id'] ?? 0), $uid]);
            flash('Removed.');
            go('skills.php');
        }
    }
}

$stmt = $pdo->prepare('SELECT s.id, s.title, s.description, s.level, c.name AS category
    FROM skills s JOIN categories c ON c.id=s.category_id WHERE s.user_id=? ORDER BY s.created_at DESC');
$stmt->execute([$uid]);
$mine = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT id, title FROM skill_wants WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$uid]);
$wants = $stmt->fetchAll();

$pageTitle = 'My Skills';
include 'includes/header.php';
?>
<section class="wide">
    <h1>My Skills</h1>
    <?php if ($errors): ?>
        <div class="alert"><?php foreach ($errors as $er): ?><p><?= e($er) ?></p><?php endforeach; ?></div>
    <?php endif; ?>

    <div class="two-col">
        <div class="panel">
            <h2>Skills I can teach</h2>
            <?php if (!$mine): ?><p class="muted">Nothing listed yet.</p><?php endif; ?>
            <?php foreach ($mine as $s): ?>
                <div class="item">
                    <strong><?= e($s['title']) ?></strong>
                    <span class="badge"><?= e($s['category']) ?></span>
                    <span class="badge"><?= e(ucfirst($s['level'])) ?></span>
                    <?php if ($s['description'] !== ''): ?><p class="muted"><?= e($s['description']) ?></p><?php endif; ?>
                    <form method="post" class="inline" data-confirm="Delete this skill?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_skill">
                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <button type="submit" class="link-btn">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <h3>Add a skill</h3>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_skill">
                <label for="title">Skill</label>
                <input type="text" id="title" name="title" placeholder="e.g. Python basics" required>
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Choose...</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="level">Your level</label>
                <select id="level" name="level">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="expert">Expert</option>
                </select>
                <label for="description">What will you teach?</label>
                <textarea id="description" name="description" rows="3" maxlength="500"></textarea>
                <button type="submit" class="btn">Add Skill</button>
            </form>
        </div>

        <div class="panel">
            <h2>Skills I want to learn</h2>
            <?php if (!$wants): ?><p class="muted">Nothing listed yet.</p><?php endif; ?>
            <?php foreach ($wants as $w): ?>
                <div class="item">
                    <strong><?= e($w['title']) ?></strong>
                    <form method="post" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_want">
                        <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
                        <button type="submit" class="link-btn">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <h3>Add a wanted skill</h3>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_want">
                <label for="want_title">Skill</label>
                <input type="text" id="want_title" name="want_title" placeholder="e.g. Graphic design" required>
                <button type="submit" class="btn">Add</button>
            </form>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
