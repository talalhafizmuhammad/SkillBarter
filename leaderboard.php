<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$rows = $pdo->query('SELECT u.id, u.full_name, u.department, ROUND(AVG(r.rating),1) AS rating, COUNT(r.id) AS reviews,
    (SELECT COUNT(*) FROM swaps WHERE status="completed" AND (owner_id=u.id OR requester_id=u.id)) AS done
    FROM users u JOIN reviews r ON r.reviewee_id=u.id GROUP BY u.id ORDER BY rating DESC, reviews DESC, done DESC LIMIT 20')->fetchAll();
$pageTitle = 'Leaderboard';
include 'includes/header.php';
?>
<section class="wide">
    <h1>Top Teachers</h1>
    <p class="muted">Ranked by average review rating.</p>
    <?php if (!$rows): ?><p class="muted">No reviews yet. Complete a swap to appear here.</p><?php endif; ?>
    <div class="table-wrap"><table>
        <tr><th>#</th><th>Teacher</th><th>Rating</th><th>Reviews</th><th>Completed swaps</th></tr>
        <?php foreach ($rows as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= avatar($r['full_name']) ?> <a href="user.php?id=<?= (int)$r['id'] ?>"><?= e($r['full_name']) ?></a>
                <span class="muted small"><?= e($r['department']) ?></span></td>
            <td><strong><?= e($r['rating']) ?>/5</strong></td><td><?= (int)$r['reviews'] ?></td><td><?= (int)$r['done'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table></div>
</section>
<?php include 'includes/footer.php'; ?>
