<?php include("init.php");
$id = intval($_GET['id']);
$stmt = $db->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$id]);
$video = $stmt->fetch();
if (!$video) { die("Видео не найдено"); }

if (isset($_GET['download']) && $_GET['download'] == 'avi') {
    $temp_avi = 'uploads/temp_' . $id . '.avi';
    
    $ffmpeg = "ffmpeg -i " . escapeshellarg($video['file']) . 
             " -c:v msmpeg4v2 " . 
             " -c:a pcm_s16le " .
             " -vf \"scale=320:240\" " .
             " -r 15 " .
             " -b:v 800k " .
             escapeshellarg($temp_avi);
    
    exec($ffmpeg, $output, $return_var);
    
    if ($return_var !== 0) {
        die("Ошибка при конвертации в AVI. Убедитесь, что FFmpeg установлен.");
    }
    
    header('Content-Type: video/x-msvideo');
    header('Content-Disposition: attachment; filename="video_' . $id . '.avi"');
    header('Content-Length: ' . filesize($temp_avi));
    readfile($temp_avi);
    
    unlink($temp_avi);
    exit;
}
?>
<html><head><title><?=htmlspecialchars($video['title'])?></title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<script type="text/javascript" src="jwplayer/jwplayer.js"></script>
</head><body link="#0000FF" alink="#0000FF" vlink="#0000FF">

<table width="700" align="center" cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="2" bgcolor="#CCCCCC">
  <b>OldVid</b> — Share Yourself
  <div align="right">
    <a href="index.php">Home</a> |
    <?php if (!isset($_SESSION['user'])): ?>
      <a href="register.php">Register</a> |
      <a href="login.php">Вход</a>
    <?php else: ?>
      <a href="channel.php?user=<?=htmlspecialchars($_SESSION['user'])?>">My Channel</a> |
      <a href="upload.php">Upload Video</a> |
      <a href="logout.php">Logout</a>
    <?php endif; ?>
  </div>
</td></tr>

<tr><td colspan="2">
  <table align="center" width="100%" border="1" cellpadding="5">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td>
            <b><?=htmlspecialchars($video['title'])?></b> — Creator:
            <a href="channel.php?user=<?=htmlspecialchars($video['user'])?>"><?=htmlspecialchars($video['user'])?></a>
          </td>
          <td align="right">
            <em>
              <?=$video['time'];?>
            </em>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr><td>
    <div id="mediaplayer">JW Player goes here</div>
    <script type="text/javascript">
    jwplayer("mediaplayer").setup({
      'controlbar.position':'bottom',
      'logo.hide': 'true',
      file: "<?=htmlspecialchars($video['file'])?>",
      image: "<?=htmlspecialchars($video['preview'])?>",
      height: 344, width: 425,
      modes: [
        { type: "html5" },
        { type: "flash", src: "jwplayer/player.swf" },
        { type: "download" }
      ]
    });
    </script>
    <br>
    <a href="?id=<?=$id?>&download=avi">Download This Video(AVI)</a>
  </td></tr>
  <?php if (!empty($video['description'])): ?>
  <tr><td colspan="2" bgcolor="#F5F5F5">
    <b>Description:</b><br>
    <?=nl2br(htmlspecialchars($video['description']))?>
  </td></tr>
  <?php endif; ?>
  </table>
</td></tr>
</table>

</body></html>
