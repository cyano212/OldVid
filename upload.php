<?php 
ini_set('upload_max_filesize', '1000M');
ini_set('post_max_size', '1000M');
ini_set('memory_limit', '1000M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include("init.php");
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_POST) {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  
  if (empty($title)) {
      $error = "Введите название видео.";
  } elseif (strlen($description) > 5000) {
      $error = "Описание не должно превышать 5000 символов.";
  } elseif (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
      $error = "Ошибка при загрузке видео. Убедитесь, что файл выбран и не превышает лимит.";
  } else {
      $stmt = $db->query("SELECT MAX(id) + 1 as next_id FROM videos");
      $next_id = $stmt->fetch()['next_id'] ?? 1;
      
      $video_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
      $preview_ext = 'jpg'; 
      
      $temp_video = 'uploads/temp_' . $next_id . '.' . $video_ext;
      $final_video = 'uploads/' . $next_id . '.mp4';
      $preview_file = 'uploads/' . $next_id . '_preview.' . $preview_ext;
      
      if (!move_uploaded_file($_FILES['video']['tmp_name'], $temp_video)) {
          $error = "Ошибка при сохранении видео. Убедитесь, что папка uploads существует и имеет права на запись.";
      } else {
          if ($video_ext != 'mp4') {
              $ffprobe = "ffprobe -v error -select_streams v:0 -show_entries stream=codec_type -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($temp_video);
              $has_video = trim(shell_exec($ffprobe)) === 'video';
              
              $log_file = 'uploads/ffmpeg_' . $next_id . '.log';
              
              if (!$has_video) {
                  $ffmpeg = "ffmpeg -i " . escapeshellarg($temp_video) . 
                           " -f lavfi -i color=c=black:s=1280x720 -shortest " .
                           " -c:v libx264 -profile:v main -level 3.0 " .
                           " -c:a aac -b:a 171k -ar 48000 -ac 1 " .
                           " -movflags +faststart " .
                           " -brand mp42 " .
                           " -y " . 
                           " -loglevel debug " . 
                           escapeshellarg($final_video) . 
                           " 2>" . escapeshellarg($log_file);
              } else {
                  $ffmpeg = "ffmpeg -i " . escapeshellarg($temp_video) . 
                           " -c:v libx264 -profile:v main -level 3.0 " .
                           " -c:a aac -b:a 171k -ar 48000 -ac 1 " .
                           " -vf \"scale=trunc(iw/2)*2:trunc(ih/2)*2,format=yuv420p\" " .
                           " -movflags +faststart " .
                           " -brand mp42 " .
                           " -y " .
                           " -loglevel debug " .
                           escapeshellarg($final_video) . 
                           " 2>" . escapeshellarg($log_file);
              }
              
              exec($ffmpeg, $output, $return_var);
              
              if ($return_var !== 0) {
                  $error_log = file_exists($log_file) ? file_get_contents($log_file) : 'Лог недоступен';
                  unlink($temp_video);
                  if (file_exists($log_file)) unlink($log_file);
                  $error = "Ошибка при конвертации в MP4. Код ошибки: " . $return_var . 
                          "<br>Детали ошибки: <pre>" . htmlspecialchars($error_log) . "</pre>";
              } else {
                  unlink($temp_video);
                  if (file_exists($log_file)) unlink($log_file);
              }
          } else {
              $log_file = 'uploads/ffmpeg_' . $next_id . '.log';
              
              $ffmpeg = "ffmpeg -i " . escapeshellarg($temp_video) . 
                       " -c:v libx264 -profile:v main -level 3.0 " .
                       " -c:a aac -b:a 171k -ar 48000 -ac 1 " .
                       " -vf \"scale=trunc(iw/2)*2:trunc(ih/2)*2,format=yuv420p\" " .
                       " -movflags +faststart " .
                       " -brand mp42 " .
                       " -y " .
                       " -loglevel debug " .
                       escapeshellarg($final_video) . 
                       " 2>" . escapeshellarg($log_file);
              
              exec($ffmpeg, $output, $return_var);
              
              if ($return_var !== 0) {
                  $error_log = file_exists($log_file) ? file_get_contents($log_file) : 'Лог недоступен';
                  unlink($temp_video);
                  if (file_exists($log_file)) unlink($log_file);
                  $error = "Ошибка при конвертации в MP4. Код ошибки: " . $return_var . 
                          "<br>Детали ошибки: <pre>" . htmlspecialchars($error_log) . "</pre>";
              } else {
                  unlink($temp_video);
                  if (file_exists($log_file)) unlink($log_file);
              }
          }

          if (empty($error)) {
              if (isset($_FILES['preview']) && $_FILES['preview']['size'] > 0) {
                  if (!move_uploaded_file($_FILES['preview']['tmp_name'], $preview_file)) {
                      unlink($final_video);
                      $error = "Ошибка при сохранении превью. Убедитесь, что папка uploads существует и имеет права на запись.";
                  }
              } else {
                  $ffmpeg = "ffmpeg -i " . escapeshellarg($final_video) . " -ss 00:00:01 -vframes 1 -y " . escapeshellarg($preview_file);
                  exec($ffmpeg, $output, $return_var);
                  
                  if ($return_var !== 0) {
                      $im = imagecreatetruecolor(120, 90);
                      $bg = imagecolorallocate($im, 0, 0, 0);
                      imagefill($im, 0, 0, $bg);
                      imagejpeg($im, $preview_file);
                      imagedestroy($im);
                  }
              }

              if (empty($error)) {
                  $time = date("d.m.Y, H:i", strtotime("+1 hour"));
                  $stmt = $db->prepare("INSERT INTO videos (title, description, file, preview, user, time) VALUES (?, ?, ?, ?, ?, ?)");
                  $stmt->execute([$title, $description, $final_video, $preview_file, $_SESSION['user'], $time]);
                  $success = "Your Video has been sucesfully uploaded! <a href=\"index.php\">Go to Homepage</a>";
              }
          }
      }
  }
}
?>
<html><head><title>Upload a Video</title>
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
  <b>Upload your Video:</b>
  <?php if ($error): ?>
  <br>
  <em><?=$error?></em>
  <br>
  <br>
  <?php endif; ?>
  <?php if ($success): ?>
  <br>
  <em><?=$success?></em>
  <br>
  <br>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
  <table align="center" border="1" cellpadding="5">
  <tr><td>Title:</td><td><input type="text" name="title" value="<?=htmlspecialchars($_POST['title'] ?? '')?>" /></td></tr>
  <tr><td>Description:</td><td><textarea name="description" rows="5" cols="50"><?=htmlspecialchars($_POST['description'] ?? '')?></textarea><br>
    <small>Maximum of 5,000 symbols</small></td></tr>
  <tr><td>Video:</td><td><input type="file" name="video" accept="video/*,audio/*" /><br>
    <small>It will automatically convert to MP4 format.</small></td></tr>
  <tr><td>Thumbnail (jpg):</td><td><input type="file" name="preview" /><br><small>Optional - will be created automatically</small></td></tr>
  <tr><td colspan="2" align="center"><input type="submit" value="Share to the World"></td></tr>
  </table>
  </form>
</td></tr>
</table>

</body></html>
