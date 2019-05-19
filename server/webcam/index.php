<?php
include('config.php');
include('../centrifugo/Client.php');
header('Content-type: text/html; charset=utf-8');

if (isset($_POST['cam'])) {
    if (!preg_match('@\:(.*?)$@', filter_input(INPUT_POST, 'cam', FILTER_SANITIZE_STRING), $cc)) {
        exit();
    }
    $cam = $cc[1];

    $path = 'img/' . $cam . '/' . date("Y-m-d");
    $k = 1;

    while (!file_exists($path)) {
        $path = 'img/' . $cam . '/' . date("Y-m-d", time() - 60 * 60 * 24 * $k);
        $k++;
        if ($k > 15)
            break;
    }

    if (!file_exists($path))
        die();

    // revese order
    $dir = scandir($path, 1);
    //get last img
    $k = 0;

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

    while ($k < 3) {
        if (preg_match("/.jpg/", $dir[$k]) && filesize($path . '/' . $dir[$k]) > 0) {
            $client->publish($ccam, ["time" => '"' . str_replace('.jpg', '', $dir[$k]) . '"', 'img' => base64_encode(file_get_contents($path . '/' . $dir[$k]))]);
            exit();
        }
        $k++;
    }
    exit();
}

?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            body {
                padding: 0;
                margin:0;
            }
            a { color:gray; text-decoration:none;}
            a:hover {color:white;}

            img {
                width:900px;
                height:720px;
            }

        </style>
        <script src="/webcam/centrifuge.min.js"></script>
    </head>
    <body style="background-color:#000000">
        <div id="kk" style='color:white'>
            <img id="img">
        </div>

        <br />
        <a href="/webcam/gif.php?c=<?= isset($_GET['c']) ? stripslashes($_GET['c']) : 'cam1' ?>" target="_blank">Последние анимированые кадры</a> / 
        <a href="/webcam/?c=cam1">Cam1 - Река</a> / 
        <a href="/webcam/?c=cam2">Cam2 - Дом</a> /
        <a href="/webcam/?c=cam3">Cam3 - Участок</a> /
        <a href="/webcam/?c=cam4">Cam4 - Дорога</a> /
        <a href="/webcam/?c=cam5">Cam5 - Двор</a> /
        <a href="/webcam/grid.php">Сетка</a> /

        <?php
        $client = new Client();

        $time = time();
        $token = $client->setSecret($centrifugoSecret)->generateClientToken($centrifugoUser, $time);
        $auth = false;

        if (isset($_GET['c']) && !empty($_GET['c'])) {
            $cam = stripslashes($_GET['c']);

            if ($cam == 'cam1') {
                $cam = "cam:cam1";
            } else if ($cam == 'cam2') {
                $auth = true;
                $cam = "\$cam_home:cam2";
            } else if ($cam == 'cam3') {
                $cam = "cam:cam3";
            } else if ($cam == 'cam4') {
                $cam = "cam:cam4";
            } else if ($cam == 'cam5') {
                $cam = "cam:cam5";
            }
        } else
            $cam = 'cam:cam1';

        ?>

        <script type="text/javascript">
            var timer2;
            var IMG = document.getElementById("img");
            var imgArray = new Array();
            var imgIndex = 0;
            var imgDrawI = 0;
            var centrifuge = new Centrifuge({
                url: 'http://<?= urlencode($_SERVER['SERVER_NAME']) ?>/centrifugo/',
                user: "<?= $centrifugoUser ?>",
                timestamp: "<?= $time ?>",
                token: "<?= $token ?>",
                transports: ["websocket", "xhr-streaming"]
<?= $auth ? ", authEndpoint: '/webcam/auth.php'" : '' ?>
                //debug: true
            });
            var subscription = centrifuge.subscribe("<?= $cam ?>", function (message) {
                console.log(message);
                fillImages(message.data.img);
                clearTimeout(timer2);
                imgDrawI = imgIndex - 1;
                drawImages();
            });
            subscription.history().then(function (message) {
                console.log(message);
                if (message.data.length > 0) {
                    for (i = message.data.length - 1; i >= 0; i--) {
                        fillImages(message.data[i].data['img']);
                    }
                    imgDrawI = imgArray.length - 1;
                    drawImages();
                } else
                    getLast("<?= $cam ?>");
            }, function (err) {

            });
            centrifuge.connect();
            /* * * * * * * */
            function getIndex(i) {
                if (i > imgArray.length - 1) {
                    imgDrawI = 0;
                    return 0;
                } else
                    return i;
            }

            function drawImages() {
                var i = imgArray[getIndex(imgDrawI)];
                if (i != undefined) {
                    img.src = "data:image/jpeg;base64," + i;
                }
                imgDrawI++;
                clearTimeout(timer2);
                timer2 = setTimeout(drawImages, 1500);
            }

            function fillImages(im) {
                imgArray[imgIndex] = im;
                if (imgIndex < 4)
                    imgIndex++;
                else
                    imgIndex = 0;
            }

            function getLast(cam) {
                //console.log(cam);
                var xhr = new XMLHttpRequest();
                var body = 'cam=' + encodeURIComponent(cam);
                xhr.open("POST", '/webcam/', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                //  xhr.onreadystatechange = ...;
                xhr.send(body);
            }
        </script>
    </body>
</html>


