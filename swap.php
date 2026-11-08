<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$uid = user_id();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT w.*, rs.title AS requested_title, os.title AS offered_title,
    ru.full_name AS requester_name, ou.full_name AS owner_name
    FROM swaps w
    JOIN skills rs ON rs.id=w.requested_skill_id
    JOIN skills os ON os.id=w.offered_skill_id
    JOIN users ru ON ru.id=w.requester_id
    JOIN users ou ON ou.id=w.owner_id WHERE w.id=?');
$stmt->execute([$id]);
$w = $stmt->fetch();
if (!$w || ((int)$w['requester_id'] !== $uid && (int)$w['owner_id'] !== $uid)) {
    http_response_code(404);
    die('Swap not found.');
}
$isOwner  = ((int)$w['owner_id'] === $uid);
$otherId  = $isOwner ? (int)$w['requester_id'] : (int)$w['owner_id'];
$otherName = $isOwner ? $w['requester_name'] : $w['owner_name'];
$teach = $isOwner ? $w['requested_title'] : $w['offered_title'];
$learn = $isOwner ? $w['offered_title'] : $w['requested_title'];
$status = $w['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!csrf_check()) {
        flash('Invalid request. Please try again.');
        go('swap.php?id=' . $id);
    }
    $act = $_POST['action'] ?? '';
    if ($act === 'accept' && $isOwner && $status === 'pending') {
        $pdo->prepare("UPDATE swaps SET status='accepted' WHERE id=?")->execute([$id]);
        flash('Swap accepted. Use the messages below to arrange a time.');
    } elseif ($act === 'reject' && $isOwner && $status === 'pending') {
        $pdo->prepare("UPDATE swaps SET status='rejected' WHERE id=?")->execute([$id]);
        flash('Swap rejected.');
    } elseif ($act === 'cancel' && !$isOwner && $status === 'pending') {
        $pdo->prepare("UPDATE swaps SET status='cancelled' WHERE id=?")->execute([$id]);
        flash('Request cancelled.');
    } elseif ($act === 'complete' && $status === 'accepted') {
        $col = $isOwner ? 'owner_done' : 'requester_done';
        $pdo->prepare("UPDATE swaps SET $col=1, status=IF(owner_done=1 AND requester_done=1,'completed',status) WHERE id=?")->execute([$id]);
        flash('Marked as done on your side. The swap completes when both of you confirm.');
    } elseif ($act === 'message' && in_array($status, ['pending', 'accepted', 'completed'], true)) {
        $body = trim($_POST['body'] ?? '');
        if ($body === '' || mb_strlen($body) > 1000) {
            flash('Message must be between 1 and 1000 characters.');
        } else {
            $pdo->prepare('INSERT INTO messages (swap_id, sender_id, body) VALUES (?,?,?)')->execute([$id, $uid, $body]);
        }
    } elseif ($act === 'review' && $status === 'completed') {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5 || mb_strlen($comment) > 500) {
            flash('Choose a rating from 1 to 5 (comment max 500 characters).');
        } else {
            try {
                $pdo->prepare('INSERT INTO reviews (swap_id, reviewer_id, reviewee_id, rating, comment) VALUES (?,?,?,?,?)')
                    ->execute([$id, $uid, $otherId, $rating, $comment]);
                flash('Review submitted. Thank you.');
            } catch (PDOException $ex) {
                flash('You have already reviewed this swap.');
            }
        }
    }
    go('swap.php?id=' . $id);
}

$stmt = $pdo->prepare('SELECT m.body, m.created_at, m.sender_id, u.full_name FROM messages m
    JOIN users u ON u.id=m.sender_id WHERE m.swap_id=? ORDER BY m.id');
$stmt->execute([$id]);
$msgs = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE swap_id=? AND reviewer_id=?');
$stmt->execute([$id, $uid]);
$reviewed = $stmt->fetchColumn() > 0;
$myDone = $isOwner ? (int)$w['owner_done'] : (int)$w['requester_done'];

$pageTitle = 'Swap #' . $id;
include 'includes/header.php';
?>
<section class="wide">
    <h1>Swap with <a href="user.php?id=<?= $otherId ?>"><?= e($otherName) ?></a></h1>
    <p><?= badge($status) ?></p>
    <div class="stats">
        <div class="stat teach"><span class="num small"><?= e($teach) ?></span><span>You teach</span></div>
        <div class="stat learn"><span class="num small"><?= e($learn) ?></span><span>You learn</span></div>
    </div>
    <?php if ($w['message'] !== ''): ?>
        <p class="muted">Request message: <?= e($w['message']) ?></p>
    <?php endif; ?>

    <div class="actions">
    <?php if ($status === 'pending' && $isOwner): ?>
        <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="accept"><button class="btn-link" type="submit">Accept</button></form>
        <form method="post" class="inline" data-confirm="Reject this request?"><?= csrf_field() ?><input type="hidden" name="action" value="reject"><button class="btn-link alt" type="submit">Reject</button></form>
    <?php elseif ($status === 'pending'): ?>
        <form method="post" class="inline" data-confirm="Cancel this request?"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><button class="btn-link alt" type="submit">Cancel request</button></form>
    <?php elseif ($status === 'accepted'): ?>
        <?php if ($myDone): ?>
            <p class="muted">You confirmed completion. Waiting for <?= e($otherName) ?> to confirm.</p>
        <?php else: ?>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="complete"><button class="btn-link" type="submit">Mark my part as done</button></form>
        <?php endif; ?>
    <?php endif; ?>
    </div>

    <div class="two-col">
        <div class="panel">
            <h2>Messages</h2>
            <?php if (!$msgs): ?><p class="muted">No messages yet.</p><?php endif; ?>
            <?php foreach ($msgs as $m): ?>
                <div class="item <?= (int)$m['sender_id'] === $uid ? 'me' : '' ?>">
                    <strong><?= (int)$m['sender_id'] === $uid ? 'You' : e($m['full_name']) ?></strong>
                    <span class="small muted"><?= e(date('d M, H:i', strtotime($m['created_at']))) ?></span>
                    <p><?= nl2br(e($m['body'])) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (in_array($status, ['pending', 'accepted', 'completed'], true)): ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="message">
                <textarea name="body" rows="2" maxlength="1000" placeholder="Write a message" required></textarea>
                <button type="submit" class="btn">Send</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="panel">
            <h2>Review</h2>
            <?php if ($status !== 'completed'): ?>
                <p class="muted">You can leave a review once both of you mark the swap as done.</p>
            <?php elseif ($reviewed): ?>
                <p class="muted">You have already reviewed this swap.</p>
            <?php else: ?>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="review">
                    <label for="rating">How was <?= e($otherName) ?> as a teacher?</label>
                    <select id="rating" name="rating" required>
                        <option value="">Choose...</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Okay</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Bad</option>
                    </select>
                    <label for="comment">Comment (optional)</label>
                    <textarea id="comment" name="comment" rows="3" maxlength="500"></textarea>
                    <button type="submit" class="btn">Submit Review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
