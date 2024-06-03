<?php
include 'parse_logs.php';
$directory = '/var/www/html/drive/';
$series = scandir($directory);
echo '<div class="server-directory">';
foreach ($series as $serie) {
    if ($serie != '.' && $serie != '..' && $serie[0] != '.' && is_dir($directory . $serie)) {
        $coverPath = $directory . $serie . '/.cover.jpg';
        $coverUrl = file_exists($coverPath) ? 'drive/' . $serie . '/.cover.jpg' : 'default-cover.jpg';
        $lastWatchedfile = getLastWatchedEpisode($serie);
        if ($lastWatchedfile === null) {
            $files = scandir($directory . $serie);
            $files = array_filter($files, function($file) {
                return $file[0] !== '.';
            });
            $files = array_values($files);
            $firstFile = reset($files);
            $newWatchedfile = $firstFile;
        } else {
            $newWatchedfile = go_next_episode_or_stay_in_the_same($lastWatchedfile, $serie);
        }
        $url = 'drive/' . $serie . '/' . $newWatchedfile;
        echo '<div class="folder-block">
                <a href="' . $url . '">
                    <img src="' . $coverUrl . '" alt="' . $serie . ' cover" class="cover-image">
                    <div class="folder-name">' . $serie . '</div>
                </a>
              </div>';
    }
}
echo '</div>';
?>
