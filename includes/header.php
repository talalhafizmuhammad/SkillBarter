<?php
$flashMsg = flash();
$app = is_logged_in();
$cur = basename($_SERVER['SCRIPT_NAME']);
$alias = ['swap.php' => 'swaps.php', 'skill.php' => 'browse.php', 'user.php' => 'leaderboard.php'];
$cur = $alias[$cur] ?? $cur;
$links = ['home.php' => 'Dashboard', 'browse.php' => 'Browse', 'matches.php' => 'Matches', 'swaps.php' => 'Swaps',
          'leaderboard.php' => 'Leaderboard', 'skills.php' => 'My Skills', 'profile.php' => 'Profile'];
if (($_SESSION['role'] ?? '') === 'admin') { $links['admin.php'] = 'Admin'; }
$links['how.php'] = 'How it works';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'SkillBarter') ?> | SkillBarter</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=DM+Sans:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= $app ? 'is-app' : 'is-guest' ?>">
<?php if ($app): ?>
<div class="shell">
<aside class="sidebar">
    <a class="brand" href="home.php"><span class="dot c"></span><span class="dot m"></span>SkillBarter</a>
    <nav>
        <?php foreach ($links as $file => $label): ?>
            <a href="<?= e($file) ?>" class="<?= $cur === $file ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="me">
        <?= avatar($_SESSION['user_name'] ?? 'U') ?>
        <span class="small"><?= e($_SESSION['user_name'] ?? '') ?></span>
        <form action="logout.php" method="post" class="inline">
            <?= csrf_field() ?>
            <button type="submit" class="link-btn">Log out</button>
        </form>
    </div>
</aside>
<div class="content">
<?php else: ?>
<header class="topbar">
    <a class="brand" href="index.php"><span class="dot c"></span><span class="dot m"></span>SkillBarter</a>
    <nav>
        <a href="how.php">How it works</a>
        <a href="login.php">Log in</a>
        <a href="register.php" class="pill">Sign up</a>
    </nav>
</header>
<?php endif; ?>
<main class="container">
<?php if ($flashMsg): ?>
    <div class="notice"><p><?= e($flashMsg) ?></p></div>
<?php endif; ?>
