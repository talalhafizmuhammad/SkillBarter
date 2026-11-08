<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$uid = user_id();

$stmt = $pdo->prepare('SELECT w.id, w.status, w.requester_id, w.updated_at,
    rs.title AS requested_title, os.title AS offered_title,
    ru.full_name AS requester_name, ou.full_name AS owner_name
    FROM swaps w
    JOIN skills rs ON rs.id=w.requested_skill_id
    JOIN skills os ON os.id=w.offered_skill_id
    JOIN users ru ON ru.id=w.requester_id
    JOIN users ou ON ou.id=w.owner_id
    WHERE w.requester_id=? OR w.owner_id=? ORDER BY w.updated_at DESC');
$stmt->execute([$uid, $uid]);
$rows = $stmt->fetchAll();

$pageTitle = 'Swaps';
include 'includes/header.php';
?>
<section class="wide">
    <h1>My Swaps</h1>
    <?php if (!$rows): ?>
        <p class="muted">No swaps yet. <a href="browse.php">Browse skills</a> and propose one.</p>
    <?php else: ?>
    <div class="table-wrap">
    <table>
        <tr><th>With</th><th>You teach</th><th>You learn</th><th>Type</th><th>Status</th><th>Updated</th><th></th></tr>
        <?php foreach ($rows as $r):
            $mineReq = ((int)$r['requester_id'] === $uid);
            $other = $mineReq ? $r['owner_name'] : $r['requester_name'];
            $teach = $mineReq ? $r['offered_title'] : $r['requested_title'];
            $learn = $mineReq ? $r['requested_title'] : $r['offered_title'];
        ?>
        <tr>
            <td><?= e($other) ?></td>
            <td><?= e($teach) ?></td>
            <td><?= e($learn) ?></td>
            <td><?= $mineReq ? 'Outgoing' : 'Incoming' ?></td>
            <td><?= badge($r['status']) ?></td>
            <td><?= e(fmt_date($r['updated_at'])) ?></td>
            <td><a href="swap.php?id=<?= (int)$r['id'] ?>">Open</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>
