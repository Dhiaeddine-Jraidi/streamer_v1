<?php
$directory = '/var/www/html/drive/';
$serie = isset($_GET['serie']) ? $_GET['serie'] : '';

if (!$serie || !is_dir($directory . $serie)) {
    echo 'Invalid series specified.';
    exit;
}

$episodes = scandir($directory . $serie);
$episodes = array_filter($episodes, function($file) use ($directory, $serie) {
    $allowedExtensions = ['mp4', 'mkv'];
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    return $file[0] !== '.' && !is_dir($directory . $serie . '/' . $file) && in_array($extension, $allowedExtensions);
});
$episodes = array_values($episodes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Series Episodes</title>
    <style>
        body {
            background-color: #131722;
            color: #ffffff;
            font-family: Arial, sans-serif;
        }
        .series-episodes {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
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
            width: 250px;
            height: 50px;
            transition: transform 0.2s;
        }      
        .episode-block:hover {
            transform: scale(1.05);
        }
        .cover-image {
            border-bottom: 1px solid #0059b3;
            width: 100%;
            height: auto;
        }
        .episode-block a {
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
        }
        .episode-block a:visited {
            color: #ffffff;
            text-decoration: none;
        }
        .episode-block a:hover {
            color: #ffffff;
            text-decoration: none;
        }
        .episode-block a:active {
            color: #ffffff;
            text-decoration: none;
        }
        .episode-block a:focus {
            color: #ffffff;
            text-decoration: none;
        }
        .clicked {
            background-color: #001a33;
        }
    </style>
</head>
<body>
    <div class="series-episodes">
<?php
foreach ($episodes as $episode) {
    $episodeUrl = 'player.php?serie=' . urlencode($serie) . '&episode=' . urlencode($episode);
    $episodeName = pathinfo($episode, PATHINFO_FILENAME);
    $watched = isset($_COOKIE['watched_episodes']) && in_array($episode, explode(',', $_COOKIE['watched_episodes']));
    echo '<div class="episode-block' . ($watched ? ' clicked' : '') . '">
            <a href="' . $episodeUrl . '" onclick="markClicked(this)">' . htmlspecialchars($episodeName) . '</a>
          </div>';
}
?>
</div>

<script>
    function markClicked(element) {
        var episodeBlocks = document.querySelectorAll('.episode-block');
        var watchedEpisodes = {};

        episodeBlocks.forEach(function(block) {
            if (block.querySelector('a') === element) {
                block.classList.toggle('clicked');
            }
            if (block.classList.contains('clicked')) {
                var episodeName = block.querySelector('a').innerHTML;
                watchedEpisodes[episodeName] = true;
            }
        });

        var serie = "<?php echo $serie; ?>";
        localStorage.setItem('watched_episodes_' + serie, JSON.stringify(watchedEpisodes));
    }

    document.addEventListener('DOMContentLoaded', function() {
        var serie = "<?php echo $serie; ?>";
        var watchedEpisodes = localStorage.getItem('watched_episodes_' + serie);
        if (watchedEpisodes) {
            watchedEpisodes = JSON.parse(watchedEpisodes);
            var episodeBlocks = document.querySelectorAll('.episode-block');
            episodeBlocks.forEach(function(block) {
                var episodeName = block.querySelector('a').innerHTML;
                if (watchedEpisodes[episodeName]) {
                    block.classList.add('clicked');
                }
            });
        }
    });
</script>
</body>
</html>
