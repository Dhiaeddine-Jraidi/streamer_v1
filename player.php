<?php
$serie = isset($_GET['serie']) ? $_GET['serie'] : '';
$episode = isset($_GET['episode']) ? $_GET['episode'] : '';

if ($serie && $episode) {
    $cookie_name = 'watched_episodes';
    $current_watched = isset($_COOKIE[$cookie_name]) ? explode(',', $_COOKIE[$cookie_name]) : [];
    
    if (!in_array($episode, $current_watched)) {
        $current_watched[] = $episode;
        setcookie($cookie_name, implode(',', $current_watched), time() + (86400 * 365 * 100), "/"); // 100 years expiration XD
    }
}

$input_file = "drive/$serie/$episode";
$output_file_vtt = "drive/$serie/" . pathinfo($episode, PATHINFO_FILENAME) . ".vtt";

function generateSubtitle($input_file, $output_file_vtt) {
    $command = "ffmpeg -i \"$input_file\" -map 0:s:0 -c:s webvtt \"$output_file_vtt\"";
    exec($command);
    exec("python3 vtt_cleaner.py \"$output_file_vtt\"");
    return file_exists($output_file_vtt);
}

$subtitle_generated = !file_exists($output_file_vtt) ? generateSubtitle($input_file, $output_file_vtt) : false;

function getEpisodes($directory) {
    $episodes = array_diff(scandir($directory), array('..', '.'));
    $episodes = array_filter($episodes, function($file) {
        return preg_match('/\.(mp4|mkv)$/i', $file);
    });
    $episodes = array_combine(range(1, count($episodes)), array_values($episodes));
    return $episodes;
}

$episodes = getEpisodes("drive/$serie");
$current_index = array_search($episode, $episodes);

$next_episode = 'none';
$prev_episode = 'none';

if ($current_index !== false) {
    if (isset($episodes[$current_index + 1])) {
        $next_episode = $episodes[$current_index + 1];
    }
    if (isset($episodes[$current_index - 1])) {
        $prev_episode = $episodes[$current_index - 1];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $serie . ' - ' . pathinfo($episode, PATHINFO_FILENAME); ?></title>
<link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet">
<style>
    body {
        background-color: #131722;
        color: #ffffff;
        font-family: Arial, sans-serif;
        margin: 0;
    }
    .video-container {
        position: relative;
        width: 70%;
        padding-top: 39.375%;
        overflow: hidden;
        margin: 0 auto;
    }
    .video-js {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .episode-navigation {
        display: flex;
        justify-content: space-between;
        margin: 20px auto;
        width: 70%;
    }
    .episode-block {
        background-color: #004080;
        border: 1px solid #0059b3;
        border-radius: 10px;
        margin: 10px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 150px;
        height: 50px;
        transition: transform 0.2s;
        text-decoration: none;
        color: #ffffff;
    }
    .episode-block:hover {
        transform: scale(1.05);
    }
</style>
<script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
</head>
<body>

<div class="video-container">
    <video id="my-video" class="video-js" controls preload="auto" autoplay data-setup="{}">
        <source src="<?php echo $input_file; ?>" type="video/mp4">
        <?php
            if (file_exists($output_file_vtt) || $subtitle_generated) {
                echo "<track src='$output_file_vtt' kind='captions' srclang='ar' label='Arabic' default>";
            }
        ?>
    </video>
</div>

<div class="episode-navigation">
    <?php if ($prev_episode !== 'none'): ?>
        <a href="?serie=<?php echo htmlspecialchars($serie); ?>&episode=<?php echo htmlspecialchars($prev_episode); ?>" class="episode-block">Previous</a>
    <?php endif; ?>
    <?php if ($next_episode !== 'none'): ?>
        <a href="?serie=<?php echo htmlspecialchars($serie); ?>&episode=<?php echo htmlspecialchars($next_episode); ?>" class="episode-block">Next</a>
    <?php endif; ?>
</div>

<script>
    var player = videojs('my-video');
    
    window.addEventListener('beforeunload', function() {
        var currentTime = player.currentTime();
        localStorage.setItem('playbackTime_<?php echo $serie . '_' . $episode; ?>', currentTime);
    });

    window.addEventListener('load', function() {
        var savedTime = localStorage.getItem('playbackTime_<?php echo $serie . '_' . $episode; ?>');
        if (savedTime !== null) {
            player.currentTime(savedTime);
        }
    });
</script>
</body>
</html>
