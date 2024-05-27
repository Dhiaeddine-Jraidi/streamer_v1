<?php
$directory = '/var/www/html/drive/'; // Update the directory path accordingly
$files = scandir($directory);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $fileSize = filesize($directory . '/' . $file);
        echo '<li class="file">
                <a href="drive/' . $file . '">' . $file . '</a> <span>(' . formatSizeUnits($fileSize) . ')</span>
              </li>';
    }
}

function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}
?>