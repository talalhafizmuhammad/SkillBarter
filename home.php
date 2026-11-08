<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$uid = user_id();

$stmt = $pdo->prepare('SELECT full_name, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); go('login.php'); }

function one($pdo, $sql, $params) { $s = $pdo->prepare($sql); $s->execute($params); return $s->fetchColumn(); }
$offered   = one($pdo, 'SELECT COUNT(*) FROM skills WHERE user_id=?', [$uid]);
$wanted    = one($pdo, 'SELECT COUNT(*) FROM skill_wants WHERE user_id=?', [$uid]);
$incoming  = one($pdo, "SELECT COUNT(*) FROM swaps WHERE owner_id=? AND status='pending'", [$uid]);
$active    = one($pdo, "SELECT COUNT(*) FROM swaps WHERE (owner_id=? OR requester_id=?) AND status='accepted'", [$uid, $uid]);
$completed = one($pdo, "SELECT COUNT(*) FROM swaps WHERE (owner_id=? OR requester_id=?) AND status='completed'", [$uid, $uid]);
$rating    = one($pdo, 'SELECT ROUND(AVG(rating),1) FROM reviews WHERE reviewee_id=?', [$uid]);

$stmt = $pdo->prepare("SELECT w.id, u.full_name, s.title FROM swaps w
    JOIN users u ON u.id=w.requester_id JOIN skills s ON s.id=w.requested_skill_id
    WHERE w.owner_id=? AND w.status='pending' ORDER BY w.created_at DESC LIMIT 5");
$stmt->execute([$uid]);
$requests = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.level, u.id AS uid, u.full_name,
    EXISTS(SELECT 1 FROM skill_wants w2 JOIN skills m ON m.user_id=?
           WHERE w2.user_id=u.id AND (m.title LIKE CONCAT('%',w2.title,'%') OR w2.title LIKE CONCAT('%',m.title,'%'))) AS mutual
    FROM skills s JOIN users u ON u.id=s.user_id
    JOIN skill_wants w ON w.user_id=? AND (s.title LIKE CONCAT('%',w.title,'%') OR w.title LIKE CONCAT('%',s.title,'%'))
    WHERE s.user_id<>? ORDER BY mutual DESC, s.id DESC LIMIT 6");
$stmt->execute([$uid, $uid, $uid]);
$matches = $stmt->fetchAll();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>
<section class="wide">
    <h1>Hello, <?= e($user['full_name']) ?></h1>
    <p class="muted">Member since <?= e(fmt_date($user['created_at'])) ?>. Every skill you teach can pay for a skill you want to learn.</p>

    <div class="stats">
        <div class="stat"><span class="num"><?= (int)$offered ?></span><span>Skills offered</span></div>
        <div class="stat"><span class="num"><?= (int)$wanted ?></span><span>Skills wanted</span></div>
        <div class="stat"><span class="num"><?= (int)$active ?></span><span>Active swaps</span></div>
        <div class="stat"><span class="num"><?= (int)$completed ?></span><span>Completed</span></div>
        <div class="stat"><span class="num"><?= $rating !== null ? e($rating) : '-' ?></span><span>Avg rating</span></div>
    </div>

    <?php if ($offered == 0): ?>
        <div class="alert"><p>You have not listed any skill yet. <a href="skills.php">Add your first skill</a> so others can swap with you.</p></div>
    <?php endif; ?>

    <div class="two-col">
        <div class="panel">
            <h2>Incoming requests (<?= (int)$incoming ?>)</h2>
            <?php if (!$requests): ?>
                <p class="muted">No pending requests.</p>
            <?php else: ?>
                <ul class="list">
                <?php foreach ($requests as $r): ?>
                    <li><a href="swap.php?id=<?= (int)$r['id'] ?>"><?= e($r['full_name']) ?> wants to learn <?= e($r['title']) ?></a></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="panel">
            <h2>Matches for you</h2>
            <?php if (!$matches): ?>
                <p class="muted">Add skills you want to learn and matches will appear here.</p>
            <?php else: ?>
                <ul class="list">
                <?php foreach ($matches as $m): ?>
                    <li>
                        <a href="skill.php?id=<?= (int)$m['id'] ?>"><?= e($m['title']) ?></a>
                        by <a href="user.php?id=<?= (int)$m['uid'] ?>"><?= e($m['full_name']) ?></a>
                        <?php if ($m['mutual']): ?><span class="badge accepted">Mutual match</span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
