<?php
require 'includes/auth.php';
$pageTitle = 'How it works';
include 'includes/header.php';
?>
<section class="wide">
    <h1>How SkillBarter works</h1>
    <p>Buying a course normally costs money. On SkillBarter the price is another skill. You teach something you know, and the person you teach teaches you something in return.</p>
    <div class="steps">
        <div class="step"><span class="num">1</span><h3>List</h3><p>Add skills you can teach and skills you want to learn.</p></div>
        <div class="step"><span class="num">2</span><h3>Match</h3><p>Browse, search, or open Matches to see people who teach what you want.</p></div>
        <div class="step"><span class="num">3</span><h3>Propose</h3><p>Pick one of your skills to offer in return and send a request.</p></div>
        <div class="step"><span class="num">4</span><h3>Accept</h3><p>The other person accepts or rejects. Then you chat and agree on a time.</p></div>
        <div class="step"><span class="num">5</span><h3>Complete</h3><p>Both of you mark your part done. The swap becomes completed.</p></div>
        <div class="step"><span class="num">6</span><h3>Review</h3><p>Rate each other. Good ratings put you on the leaderboard.</p></div>
    </div>
    <h2>Swap statuses</h2>
    <p><?= badge('pending') ?> waiting for the owner &nbsp; <?= badge('accepted') ?> in progress &nbsp; <?= badge('completed') ?> both done &nbsp; <?= badge('rejected') ?> declined &nbsp; <?= badge('cancelled') ?> withdrawn</p>
</section>
<?php include 'includes/footer.php'; ?>
