<?php
require 'includes/auth.php';
if (is_logged_in()) { go('home.php'); }
$pageTitle = 'Pay for lessons with a lesson';
include 'includes/header.php';
?>
<section class="hero">
    <div class="hero-text">
        <h1>Pay for a lesson with a lesson.</h1>
        <p>SkillBarter lets students trade what they know for what they want to learn. You teach Python, they teach you design, and no money changes hands.</p>
        <p>
            <a class="btn-link" href="register.php">Create free account</a>
            <a class="btn-link alt" href="login.php">Log in</a>
        </p>
    </div>
    <svg class="hero-art" viewBox="0 0 380 260" role="img" aria-label="Two skills being swapped">
        <circle class="orb c" cx="140" cy="130" r="95"/>
        <circle class="orb m" cx="240" cy="130" r="95"/>
        <text x="105" y="125" class="t">Python</text>
        <text x="105" y="145" class="s">you teach</text>
        <text x="215" y="125" class="t">Design</text>
        <text x="215" y="145" class="s">you learn</text>
        <path d="M170 38 h40 m-8 -8 l8 8 l-8 8" class="arrow"/>
        <path d="M210 222 h-40 m8 -8 l-8 8 l8 8" class="arrow"/>
    </svg>
</section>
<section class="steps">
    <div class="step"><span class="num">1</span><h3>List your skills</h3><p>Add what you can teach and what you want to learn.</p></div>
    <div class="step"><span class="num">2</span><h3>Find a match</h3><p>Search, or let Matches show who teaches what you want.</p></div>
    <div class="step"><span class="num">3</span><h3>Swap and learn</h3><p>Agree on a trade, chat, finish, then rate each other.</p></div>
</section>
<?php include 'includes/footer.php'; ?>
