<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$uid = user_id();
$stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.level, u.id AS uid, u.full_name,
    EXISTS(SELECT 1 FROM skill_wants w2 JOIN skills m ON m.user_id=?
           WHERE w2.user_id=u.id AND (m.title LIKE CONCAT('%',w2.title,'%') OR w2.title LIKE CONCAT('%',m.title,'%'))) AS mutual
    FROM skills s JOIN users u ON u.id=s.user_id
    JOIN skill_wants w ON w.user_id=? AND (s.title LIKE CONCAT('%',w.title,'%') OR w.title LIKE CONCAT('%',s.title,'%'))
    WHERE s.user_id<>? ORDER BY mutual DESC, s.id DESC");
$stmt->execute([$uid, $uid, $uid]);
$rows = $stmt->fetchAll();
$pageTitle = 'Matches';
include 'includes/header.php';
?>
<section class="wide">
    <h1>Your Matches</h1>
    <p class="muted">Skills that match what you want to learn. A mutual match means they also want something you teach, so a swap is easy to agree on.</p>
    <?php if (!$rows): ?><p class="muted">No matches yet. <a href="skills.php">Add skills you want to learn</a>.</p><?php endif; ?>
    <div class="grid">
    <?php foreach ($rows as $m): ?>
        <div class="skill-card">
            <h3><a href="skill.php?id=<?= (int)$m['id'] ?>"><?= e($m['title']) ?></a></h3>
            <p><span class="badge"><?= e(ucfirst($m['level'])) ?></span>
               <?php if ($m['mutual']): ?><span class="badge accepted">Mutual match</span><?php endif; ?></p>
            <p class="small"><?= avatar($m['full_name']) ?> <a href="user.php?id=<?= (int)$m['uid'] ?>"><?= e($m['full_name']) ?></a></p>
            <a class="btn-link" href="skill.php?id=<?= (int)$m['id'] ?>">Propose swap</a>
        </div>
    <?php endforeach; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
