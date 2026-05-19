<?php
$files = [
    'template/smartadmin/daftar.php',
    'template/smartadmin/publik.php',
    'template/smartadmin/publik2.php',
    'template/smartadmin/login.php',
    'template/smartadmin/lock.html',
    'template/smartadmin/index.html',
    'template/smartadmin/forgotpassword.html',
    'template/smartadmin/dashboard.php',
    'template/smartadmin/dashboard.html',
    'dashboard.html'
];

foreach ($files as $file) {
    $path = "c:/laragon/www/magang/" . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $search = '<meta name="apple-mobile-web-app-capable" content="yes">';
        $replace = '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n\t\t" . '<meta name="mobile-web-app-capable" content="yes">';
        
        // Prevent double insertion
        if (strpos($content, '<meta name="mobile-web-app-capable"') === false) {
            $content = str_replace($search, $replace, $content);
            file_put_contents($path, $content);
            echo "Updated: $file\n";
        } else {
            echo "Already updated: $file\n";
        }
    }
}
?>
