<?php
if (session_status() === PHP_SESSION_NONE)
{
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}
function user_id()
{
    return (int)($_SESSION['user_id'] ?? 0);
}
function require_login()
{
    if (!is_logged_in())
    {
        header('Location: login.php');
        exit;
    }
}
function require_admin()
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin')
    {
        http_response_code(403);
        die('Access denied.');
    }
}
function redirect_if_logged_in()
{
    if (is_logged_in())
    {
        header('Location: home.php');
        exit;
    }
}
function csrf_token()
{
    if (empty($_SESSION['csrf']))
    {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_check()
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function flash($message = null)
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}
function go($url)
{
    header('Location: ' . $url);
    exit;
}
function badge($status)
{
    return '<span class="badge ' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}
function fmt_date($ts)
{
    return date('d M Y', strtotime($ts));
}
function avatar($name)
{
    $i = '';
    foreach (preg_split('/\s+/', trim((string)$name)) as $p) {
        if ($p !== '' && mb_strlen($i) < 2) { $i .= mb_strtoupper(mb_substr($p, 0, 1)); }
    }
    return '<span class="avatar">' . e($i) . '</span>';
}
