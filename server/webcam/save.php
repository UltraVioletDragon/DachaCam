<?php
ini_set('max_execution_time', 120);

$file = file_get_contents('php://input');
if (!empty($file)) {

    if (isset($_GET['c']))
        $cam = str_replace(array('%', '\\', '/'), array('', '', ''), $_GET['c']);

    $path = 'img/' . $cam . '/' . date("Y-m-d");

    if (!file_exists($path))
        mkdir($path);

    $hour = date('G');

    if ($hour > 7 && $hour < 19)
        $timeout = 30; //10 ������
    else if ($hour == 7 || ($hour >= 18 && $hour < 21))
        $timeout = 60; // ������
    else
        $timeout = 3600;
    //$timeout=1800; // �������
//$timeout=3600;		
//$timeout=20;		

    echo $timeout;
    $time = time();

    file_put_contents($path . '/' . $time . '.jpg', $file);

    if (filesize($path . '/' . $time . '.jpg') < 3000) {
        return;
    }

    include('config.php');
    include('../centrifugo/Client.php');
    include('../centrifugo/ITransport.php');
    include('../centrifugo/TransportException.php');
    include('../centrifugo/Transport.php');

    $client = new Client();
    $client->setSecret($centrifugoSecret);

    if ($cam == 'cam2') {
        $ccam = "\$cam_home:" . $cam;
    } else {
        $ccam = "cam:" . $cam;
    }
    $client->publish($ccam, ["time" => $time, 'img' => base64_encode($file)]);
}
