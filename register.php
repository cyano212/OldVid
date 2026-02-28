<?php include("init.php"); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$error = '';
if ($_POST) {
    $login = trim($_POST['login']);
    $pass = trim($_POST['pass']);

    if ($login == '' || $pass == '') {
        $error = "Пожалуйста, заполните все поля.";
    } else {
        $stmt = $db->prepare("SELECT login FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = "Пользователь с таким логином уже существует.";
        } else {
            $stmt = $db->prepare("INSERT INTO users (login, pass) VALUES (?, ?)");
            $stmt->execute([$login, $pass]); 
            $_SESSION['user'] = $login;
            header("Location: index.php");
            exit;
        }
    }
}
?>

<html><head><title>Create Account</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
</head><body link="#0000FF" alink="#0000FF" vlink="#0000FF">

<table width="700" align="center" cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="2" bgcolor="#CCCCCC">
  <b>OldVid</b> — Share Yourself
  <div align="right">
    <a href="index.php">Home</a> |
    <?php if (!isset($_SESSION['user'])): ?>
      <a href="register.php">Register</a> |
      <a href="login.php">Login</a>
    <?php else: ?>
      <a href="channel.php?user=<?=htmlspecialchars($_SESSION['user'])?>">My Channel</a> |
      <a href="upload.php">Upload Video</a> |
      <a href="logout.php">Logout</a>
    <?php endif; ?>
  </div>
</td></tr>

<tr><td colspan="2">
  <b>Register:</b>
  <?php if ($error): ?>
  <br>
  <em><?=htmlspecialchars($error)?></em>
  <br>
  <?php endif; ?>
  <form method="post">
  <table border="1" cellpadding="5" align="center">
  <tr><td>Username:</td><td><input type="text" name="login" /></td></tr>
  <tr><td>Passowrd:</td><td><input type="password" name="pass" /></td></tr>
  <tr><td colspan="2" align="center"><input type="submit" value="Create your Account" /></td></tr>
  </table>
  </form>
</td></tr>
</table>

</body></html>
