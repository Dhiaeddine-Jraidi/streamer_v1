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
    exec("ffmpeg -i \"$input_file\" -map 0:s:0 -c:s webvtt \"$output_file_vtt\"");
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
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <style>
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Stack elements vertically */
            height: 100vh;
            background-color: #131722; /* Updated background color */
        }
        .video-container {
            width: 70%;
            aspect-ratio: 16 / 9;
            display: flex;
            justify-content: center;
            margin-bottom: 20px; /* Space between video and navigation */
        }
        #player {
            width: 100%;
            height: 100%;
        }
        .episode-navigation {
            display: flex;
            justify-content: space-between;
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
</head>
<body>
    <div class="video-container">
        <video id="player" playsinline controls data-poster="drive/<?php echo $serie; ?>/.cover.jpg">
            <source src="<?php echo $input_file; ?>" type="video/mp4" />
            <?php
            if (file_exists($output_file_vtt) || $subtitle_generated) {
                echo "<track kind='captions' label='Arabic captions' src='$output_file_vtt' srclang='ar' default />";
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
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const player = new Plyr('#player');

            const playbackKey = 'playbackTime_<?php echo $serie . '_' . $episode; ?>';
            const savedTime = localStorage.getItem(playbackKey);
            if (savedTime !== null) {
                player.once('canplay', () => {
                    player.currentTime = parseFloat(savedTime);
                });
            }
            window.addEventListener('beforeunload', function() {
                localStorage.setItem(playbackKey, player.currentTime);
            });
            setInterval(function() {
                localStorage.setItem(playbackKey, player.currentTime);
            }, 5000);
        });

    </script>
</body>
</html>
