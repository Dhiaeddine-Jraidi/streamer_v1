<?php

function getLastWatchedEpisode($serie) {
    $lines = file("/var/log/apache2/access.log");
    $lines = array_reverse($lines);
    $nameserieonlogs = str_replace(' ', '%20', $serie);
    $latestEpisode = null;

    foreach ($lines as $line) {
        if (strpos($line, $nameserieonlogs) !== false && (strpos($line, ".mkv") !== false || strpos($line, ".mp4") !== false)) {
            preg_match('/\/' . preg_quote($nameserieonlogs) . '\/(\d+\.(mkv|mp4))/', $line, $matches);
            if (!empty($matches[1])) {
                $episodeNumber = intval($matches[1]);
                if ($latestEpisode === null || $episodeNumber > $latestEpisode) {
                    $latestEpisode = $matches[1];
                }
            }
        }
    }

    return $latestEpisode;
}


function go_next_episode_or_stay_in_the_same($lastWatchedfile, $serie) {
    $logFilePath = '/var/log/apache2/access.log';
    $filePath = "/var/www/html/drive/$serie/$lastWatchedfile";
    $totalBytes = 0;
    $fileSize = filesize($filePath);
    $nameserieonlogs = str_replace(' ', '%20', $serie);
    $handle = fopen($logFilePath, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, "/drive/$nameserieonlogs/$lastWatchedfile") !== false) {
                preg_match('/HTTP\/1\.1"\s(\d+)\s(\d+)/', $line, $matches);
                if (isset($matches[2])) {
                    $bytes = (int)$matches[2];
                    $totalBytes += $bytes;
                }
            }
        }
        fclose($handle);
    }

    $pct = ($totalBytes / $fileSize) * 100;
    
    if ($pct > 65) {
        $directory = "/var/www/html/drive/$serie/";
        $files = scandir($directory);
        $files = array_diff($files, array('.', '..'));
        $files = array_values($files);
        $currentIndex = array_search($lastWatchedfile, $files);
        $nextIndex = $currentIndex + 1;
        if ($nextIndex >= count($files)) {
            $nextIndex = 0;
        }
        $nextFile = $files[$nextIndex];
        return $nextFile;
    } else {
        return $lastWatchedfile ; }


}

?>
