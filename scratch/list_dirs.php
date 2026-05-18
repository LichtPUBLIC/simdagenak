<?php
$dir = "d:\\laragon\\www";
$contents = scandir($dir);
echo "Directories in $dir:\n";
foreach($contents as $item) {
    if(is_dir($dir . DIRECTORY_SEPARATOR . $item) && $item != '.' && $item != '..') {
        echo "- " . $item . "\n";
    }
}
