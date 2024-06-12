<?php
$directory = '/var/www/html/drive/';
$serie = isset($_GET['serie']) ? $_GET['serie'] : '';

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
    <title><?php echo $serie;?></title>
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
            text-decoration: none;
            color: #ffffff;
        }
        .episode-block:hover {
            transform: scale(1.05);
        }
        .episode-block.clicked {
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
    echo '<a href="' . $episodeUrl . '" class="episode-block' . ($watched ? ' clicked' : '') . '">
            ' . htmlspecialchars($episodeName) . '
          </a>';
}
?>
    </div>
</body>
</html>
