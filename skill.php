<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$uid = user_id();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT s.*, c.name AS category, u.full_name, u.id AS uid, u.department,
    (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.reviewee_id=u.id) AS rating
    FROM skills s JOIN users u ON u.id=s.user_id JOIN categories c ON c.id=s.category_id WHERE s.id=?');
$stmt->execute([$id]);
$skill = $stmt->fetch();
if (!$skill) { http_response_code(404); die('Skill not found.'); }
$isMine = ((int)$skill['user_id'] === $uid);

$stmt = $pdo->prepare('SELECT id, title FROM skills WHERE user_id=? ORDER BY title');
$stmt->execute([$uid]);
$mine = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!csrf_check()) {
        flash('Invalid request. Please try again.');
    } elseif ($isMine) {
        flash('You cannot swap with yourself.');
    } else {
        $offered = (int)($_POST['offered_skill_id'] ?? 0);
        $msg = trim($_POST['message'] ?? '');
        $own = $pdo->prepare('SELECT COUNT(*) FROM skills WHERE id=? AND user_id=?');
        $own->execute([$offered, $uid]);
        $dup = $pdo->prepare("SELECT COUNT(*) FROM swaps WHERE requester_id=? AND requested_skill_id=? AND offered_skill_id=? AND status IN ('pending','accepted')");
        $dup->execute([$uid, $id, $offered]);
        if (!$own->fetchColumn()) {
            flash('Choose one of your own skills to offer in return.');
        } elseif (mb_strlen($msg) > 500) {
            flash('Message must be 500 characters or less.');
        } elseif ($dup->fetchColumn() > 0) {
            flash('You already have an open request for this swap.');
        } else {
            $pdo->prepare('INSERT INTO swaps (requester_id, owner_id, requested_skill_id, offered_skill_id, message) VALUES (?,?,?,?,?)')
                ->execute([$uid, $skill['user_id'], $id, $offered, $msg]);
            flash('Swap request sent.');
            go('swap.php?id=' . $pdo->lastInsertId());
        }
    }
    go('skill.php?id=' . $id);
}

$stmt = $pdo->prepare('SELECT r.rating, r.comment, r.created_at, u.full_name FROM reviews r
    JOIN users u ON u.id=r.reviewer_id WHERE r.reviewee_id=? ORDER BY r.created_at DESC LIMIT 5');
$stmt->execute([$skill['uid']]);
$reviews = $stmt->fetchAll();

$pageTitle = $skill['title'];
include 'includes/header.php';
?>
<section class="wide">
    <h1><?= e($skill['title']) ?></h1>
    <p><span class="badge"><?= e($skill['category']) ?></span> <span class="badge"><?= e(ucfirst($skill['level'])) ?></span></p>
    <p><?= $skill['description'] !== '' ? nl2br(e($skill['description'])) : '<span class="muted">No description provided.</span>' ?></p>
    <p class="muted">Taught by <a href="user.php?id=<?= (int)$skill['uid'] ?>"><?= e($skill['full_name']) ?></a>
        <?= $skill['department'] !== '' ? '(' . e($skill['department']) . ')' : '' ?>
        | Rating: <?= $skill['rating'] !== null ? e($skill['rating']) . '/5' : 'no reviews yet' ?></p>

    <div class="two-col">
        <div class="panel">
            <h2>Propose a swap</h2>
            <?php if ($isMine): ?>
                <p class="muted">This is your own skill.</p>
            <?php elseif (!$mine): ?>
                <p>You need something to offer. <a href="skills.php">Add a skill</a> first.</p>
            <?php else: ?>
                <p class="muted">You get <strong><?= e($skill['title']) ?></strong>. You give one of your skills in return.</p>
                <form method="post">
                    <?= csrf_field() ?>
                    <label for="offered_skill_id">I will teach</label>
                    <select id="offered_skill_id" name="offered_skill_id" required>
                        <?php foreach ($mine as $m): ?>
                            <option value="<?= (int)$m['id'] ?>"><?= e($m['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="message">Message (optional)</label>
                    <textarea id="message" name="message" rows="3" maxlength="500" placeholder="Say hello and suggest a time"></textarea>
                    <button type="submit" class="btn">Send Request</button>
                </form>
            <?php endif; ?>
        </div>
        <div class="panel">
            <h2>Reviews of <?= e($skill['full_name']) ?></h2>
            <?php if (!$reviews): ?><p class="muted">No reviews yet.</p><?php endif; ?>
            <?php foreach ($reviews as $r): ?>
                <div class="item">
                    <strong><?= (int)$r['rating'] ?>/5</strong> by <?= e($r['full_name']) ?>
                    <p class="muted"><?= e($r['comment']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
