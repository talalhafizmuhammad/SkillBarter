<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, full_name, bio, department, semester, created_at,
    (SELECT ROUND(AVG(rating),1) FROM reviews WHERE reviewee_id=users.id) AS rating,
    (SELECT COUNT(*) FROM swaps WHERE status="completed" AND (owner_id=users.id OR requester_id=users.id)) AS done
    FROM users WHERE id=?');
$stmt->execute([$id]);
$u = $stmt->fetch();
if (!$u) { http_response_code(404); die('User not found.'); }

$stmt = $pdo->prepare('SELECT s.id, s.title, s.level, c.name AS category FROM skills s JOIN categories c ON c.id=s.category_id WHERE s.user_id=? ORDER BY s.title');
$stmt->execute([$id]);
$skills = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT title FROM skill_wants WHERE user_id=? ORDER BY title');
$stmt->execute([$id]);
$wants = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT r.rating, r.comment, r.created_at, u.full_name FROM reviews r JOIN users u ON u.id=r.reviewer_id WHERE r.reviewee_id=? ORDER BY r.created_at DESC LIMIT 10');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$pageTitle = $u['full_name'];
include 'includes/header.php';
?>
<section class="wide">
    <h1><?= avatar($u['full_name']) ?> <?= e($u['full_name']) ?></h1>
    <p class="muted">
        <?= $u['department'] !== '' ? e($u['department']) . ' | ' : '' ?>
        <?= $u['semester'] ? 'Semester ' . (int)$u['semester'] . ' | ' : '' ?>
        Member since <?= e(fmt_date($u['created_at'])) ?>
    </p>
    <?php if ($u['bio'] !== ''): ?><p><?= nl2br(e($u['bio'])) ?></p><?php endif; ?>
    <div class="stats">
        <div class="stat"><span class="num"><?= $u['rating'] !== null ? e($u['rating']) : '-' ?></span><span>Avg rating</span></div>
        <div class="stat"><span class="num"><?= (int)$u['done'] ?></span><span>Completed swaps</span></div>
        <div class="stat"><span class="num"><?= count($skills) ?></span><span>Skills offered</span></div>
    </div>
    <div class="two-col">
        <div class="panel">
            <h2>Teaches</h2>
            <?php if (!$skills): ?><p class="muted">Nothing listed.</p><?php endif; ?>
            <?php foreach ($skills as $s): ?>
                <div class="item">
                    <a href="skill.php?id=<?= (int)$s['id'] ?>"><?= e($s['title']) ?></a>
                    <span class="badge"><?= e($s['category']) ?></span>
                    <span class="badge"><?= e(ucfirst($s['level'])) ?></span>
                </div>
            <?php endforeach; ?>
            <h3>Wants to learn</h3>
            <?php if (!$wants): ?><p class="muted">Nothing listed.</p><?php endif; ?>
            <?php foreach ($wants as $w): ?><span class="badge"><?= e($w['title']) ?></span> <?php endforeach; ?>
        </div>
        <div class="panel">
            <h2>Reviews</h2>
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
