<?php
include ('config.php');

$post = file_get_contents('php://input');
if (!empty($post)) {
    $json = json_decode($post);
    $clientJ = $json->client;
    $channel = $json->channels[0];
}

if (isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == $cookiePass) {
    setcookie("pass", $cookiePass, time() + $cookieTimeout, '/', $_SERVER['HTTP_HOST']); //172800 = 2 day

    include ('../centrifugo/Client.php');
    $client = new Client();
    $info = null;

    $sign = $client->setSecret($centrifugoSecret)->generateChannelSign($clientJ, $channel, $info);

    echo json_encode(array($channel => array('sign' => $sign, 'info' => $info)));
    exit();
} else { //if (!isset($_COOKIE['pass']) && $_COOKIE['pass'] != 'lfxf') {
    header('WWW-Authenticate: Basic realm="Camera password"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(array('status' => 403));
}
