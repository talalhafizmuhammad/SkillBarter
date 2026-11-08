<?php
require 'config/db.php';
require 'includes/auth.php';
redirect_if_logged_in();
$errors = [];
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    if (!csrf_check()) 
    {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    }
    $name     = trim($_POST['full_name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100)
    {
        $errors[] = 'Full name is required (max 100 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150)
    {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) 
    {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) 
    {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) 
    {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) 
        {
            $errors[] = 'An account with this email already exists.';
        } else 
    {
            try 
            {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)'
                );
                $stmt->execute([$name, $email, $hash]);

                flash('Account created successfully. Please log in.');
                header('Location: login.php');
                exit;
            } catch(PDOException $ex) 
            {
                $errors[] = 'Could not create the account. Please try again.';
            }
        }
    }
}
$pageTitle = 'Sign Up';
include 'includes/header.php';
?>
<section class="card">
    <h1>Create your account</h1>

    <?php if ($errors): ?>
        <div class="alert">
            <?php foreach ($errors as $err): ?>
                <p><?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" id="registerForm" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= e($name) ?>" required>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        <label for="password">Password (min 8 characters)</label>
        <input type="password" id="password" name="password" required>
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <p class="error-text" id="clientError"></p>
        <button type="submit" class="btn">Sign Up</button>
    </form>

    <p class="muted">Already have an account? <a href="login.php">Log in</a></p>
</section>
<?php include 'includes/footer.php'; ?>