<?php
require 'config/db.php';
require 'includes/auth.php';
require_login();
$q   = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$sql = 'SELECT s.id, s.title, s.description, s.level, c.name AS category, u.id AS uid, u.full_name,
        (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.reviewee_id=u.id) AS rating
        FROM skills s JOIN users u ON u.id=s.user_id JOIN categories c ON c.id=s.category_id
        WHERE s.user_id<>?';
$params = [user_id()];
if ($q !== '') {
    $sql .= ' AND (s.title LIKE ? OR s.description LIKE ? OR u.full_name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($cat > 0) { $sql .= ' AND s.category_id=?'; $params[] = $cat; }
$sql .= ' ORDER BY s.created_at DESC LIMIT 60';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pageTitle = 'Browse Skills';
include 'includes/header.php';
?>
<section class="wide">
    <h1>Browse Skills</h1>
    <form method="get" class="filters">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search skills or people">
        <select name="cat">
            <option value="0">All categories</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Search</button>
    </form>

    <?php if (!$rows): ?>
        <p class="muted">No skills found.</p>
    <?php endif; ?>
    <div class="grid">
    <?php foreach ($rows as $r): ?>
        <div class="skill-card">
            <h3><a href="skill.php?id=<?= (int)$r['id'] ?>"><?= e($r['title']) ?></a></h3>
            <p><span class="badge"><?= e($r['category']) ?></span> <span class="badge"><?= e(ucfirst($r['level'])) ?></span></p>
            <p class="muted"><?= e(mb_strimwidth($r['description'], 0, 110, '...')) ?></p>
            <p class="small">
                <a href="user.php?id=<?= (int)$r['uid'] ?>"><?= e($r['full_name']) ?></a>
                <?= $r['rating'] !== null ? ' | ' . e($r['rating']) . '/5' : ' | New' ?>
            </p>
            <a class="btn-link" href="skill.php?id=<?= (int)$r['id'] ?>">View and swap</a>
        </div>
    <?php endforeach; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
