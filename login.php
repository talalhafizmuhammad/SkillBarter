<?php
require 'config/db.php';
require 'includes/auth.php';
redirect_if_logged_in();
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!csrf_check()) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true); // prevents session fixation

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            header('Location: home.php');
            exit;
        }
        $error = 'Invalid email or password.';
    }
}
$pageTitle = 'Login';
include 'includes/header.php';
?>
<section class="card">
    <h1>Welcome back</h1>
    <?php if ($error): ?>
        <div class="alert"><p><?= e($error) ?></p></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn">Log In</button>
    </form>

    <p class="muted">No account yet? <a href="register.php">Sign up</a></p>
</section>
<?php include 'includes/footer.php'; ?>