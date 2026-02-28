<?php include("init.php");
$user = $_GET['user'];
?>
<html><head><title>Channel <?=htmlspecialchars($user)?></title>
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
  <b>Videos From<?=htmlspecialchars($user)?>:</b>
  <?php
  $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
  $per_page = 5;
  $offset = ($page - 1) * $per_page;
  
  $stmt = $db->prepare("SELECT COUNT(*) FROM videos WHERE user = ?");
  $stmt->execute([$user]);
  $total = $stmt->fetchColumn();
  $total_pages = ceil($total / $per_page);
  
  $stmt = $db->prepare("SELECT id, title, preview FROM videos WHERE user = ? ORDER BY id DESC LIMIT $offset, $per_page");
  $stmt->execute([$user]);
  while ($row = $stmt->fetch()) {
    echo '<table border="0" cellpadding="3"><tr>';
    echo '<td><a href="video.php?id='.$row['id'].'"><img src="'.$row['preview'].'" width="120" height="90" border="2"></a></td>';
    echo '<td><b><a href="video.php?id='.$row['id'].'">'.htmlspecialchars($row['title']).'</a></b><br>';
    echo 'Автор: <a href="channel.php?user='.htmlspecialchars($user).'">'.$user.'</a></td>';
    echo '</tr></table>';
  }
  
  if ($total_pages > 1) {
    echo '<tr><td colspan="2" align="center">';
    
    // Первая страница
    if ($page > 1) {
        echo '<a href="?user=' . htmlspecialchars($user) . '&page=1">&lt;&lt;</a>&nbsp;&nbsp;';
    } else {
        echo '&lt;&lt;&nbsp;&nbsp;';
    }

    if ($page > 1) {
        echo '<a href="?user=' . htmlspecialchars($user) . '&page=' . ($page - 1) . '">&lt;</a>&nbsp;&nbsp;';
    } else {
        echo '&lt;&nbsp;&nbsp;';
    }

    echo 'Стр. ' . $page . ' из ' . $total_pages;

    if ($page < $total_pages) {
        echo '&nbsp;&nbsp;<a href="?user=' . htmlspecialchars($user) . '&page=' . ($page + 1) . '">&gt;</a>';
    } else {
        echo '&nbsp;&nbsp;&gt;';
    }

    if ($page < $total_pages) {
        echo '&nbsp;&nbsp;<a href="?user=' . htmlspecialchars($user) . '&page=' . $total_pages . '">&gt;&gt;</a>';
    } else {
        echo '&nbsp;&nbsp;&gt;&gt;';
    }

    echo '</td></tr>';
  }
  ?>
</td></tr>
</table>

</body></html>
