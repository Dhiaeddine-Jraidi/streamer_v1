<?php
$directory = '/var/www/html/drive/';
$series = scandir($directory);
echo '<div class="server-directory">';
foreach ($series as $serie) {
    if ($serie != '.' && $serie != '..' && $serie[0] != '.' && is_dir($directory . $serie)) {
        $coverPath = $directory . $serie . '/.cover.jpg';
        $coverUrl = file_exists($coverPath) ? 'drive/' . $serie . '/.cover.jpg' : 'default-cover.jpg';
        $url = 'episodes_listing.php?serie=' . urlencode($serie);
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
