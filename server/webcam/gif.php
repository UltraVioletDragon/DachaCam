<?php
include('config.php');
include('GIFEncoder.class.php');

header("Content-Type: image/gif");

if (isset($_GET['c']) && !empty($_GET['c']))
    $cam = $_GET['c'];
else
    $cam = 'cam1';

if (isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == $cookiePass) {
    setcookie("pass", $cookiePass, time() + $cookieTimeout, '/', $_SERVER['HTTP_HOST']); 
} else if ($cam == 'cam2' && !isset($_COOKIE['pass']) && $_COOKIE['pass'] != $cookiePass) {
    $cam = 'cam1';
}

$path = 'img/' . $cam . '/' . date("Y-m-d");

$k = 1;

while (!file_exists($path)) {
    $path = 'img/' . $cam . '/' . date("Y-m-d", time() - 60 * 60 * 24 * $k);
    $k++;

    if ($k > 15)
        break;
}

// revese order
$dir = scandir($path, 1);

//get last img
//$im = @imagecreatefromjpeg($path.'/'.$dir[0]);
$k = 0;

$num = 30;
while ($k < $num) {
    if (preg_match("/.jpg/", $dir[$num - $k]) && filesize($path . '/' . $dir[$num - $k]) > 0) {

        $image = @imagecreatefromjpeg($path . '/' . $dir[$num - $k]);

        // Generate GIF from the $image
        // We want to put the binary GIF data into an array to be used later,
        //  so we use the output buffer.

        if ($image !== false) {
            ob_start();
            imagegif($image);
            $frames[] = ob_get_contents();
            if ($k < $num - 1)
                $framed[] = 50; // Delay in the animation.
            else
                $framed[] = 500;
            ob_end_clean();
        }
    }
    $k++;
}

// Generate the animated gif and output to screen.

$gif = new GIFEncoder($frames, $framed, 0, 2, 0, 0, 0, 'bin');
echo $gif->GetAnimation();
