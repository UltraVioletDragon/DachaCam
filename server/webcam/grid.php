<?php
include ('config.php');
include ('../centrifugo/Client.php');
header('Content-type: text/html; charset=utf-8');

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
                width:600px;
                height: 372px;
                padding:0px;
                margin:0px;
                /*width:928px;*/
                /*height:576px;*/
            }
            table,tr,td {
                padding:0px;
            }

        </style>
        <script src="/webcam/centrifuge.min.js"></script>
    </head>
    <body style="background-color:#000000">
        <table>
            <tr><td><img id="img1"></td><td><img id="img5"></td></tr>
            <tr><td><img id="img3"></td><td><img id="img4"></td></tr>
            <tr><td><img id="img2"></td><td></td></tr>
        </table>

        <?php
        $client = new Client();

        $time = time();
        $token = $client->setSecret($centrifugoSecret)->generateClientToken($centrifugoUser, $time);

        ?>

        <script type="text/javascript">
            var timer = [null, 0, 0, 0, 0];
            var img = [null, document.getElementById("img1"), document.getElementById("img2"), document.getElementById("img3"), document.getElementById("img4"), document.getElementById("img5")];

            var imgArray = [null, [], [], [], [], []];

            var imgIndex = [null, 0, 0, 0, 0, 0];
            var imgDrawI = [null, 0, 0, 0, 0, 0];

            var centrifuge = new Centrifuge({
                url: 'http://<?= urlencode($_SERVER['SERVER_NAME']) ?>/centrifugo/',
                user: "<?= $centrifugoUser ?>",
                timestamp: "<?= $time ?>",
                token: "<?= $token ?>",
                transports: ["websocket", "xhr-streaming"],
                authEndpoint: '/webcam/auth.php'
                        //debug: true
            });

            var subscription = centrifuge.subscribe("cam:cam1", function (message) {
                var cam = 1;
//                console.log(message);
                fillImages(message.data.img, cam);

                // clearTimeout(timer[cam]);
                //imgDrawI[cam] = imgIndex[cam] - 1;
                //drawImages(cam);
            });
            subscription.history().then(function (message) {
                var cam = 1;
                if (message.data.length > 0) {
                    //console.log(message);
                    for (i = message.data.length - 1; i >= 0; i--) {
                        fillImages(message.data[i].data['img'], cam);
                    }
                    imgDrawI[cam] = imgArray[cam].length - 1;
                    drawImages(cam);
                } else
                    getLast(cam);
            }, function (err) {

            });

            var subscription = centrifuge.subscribe("$cam_home:cam2", function (message) {
                var cam = 2;
//                console.log(message);
                fillImages(message.data.img, cam);

                clearTimeout(timer[cam]);
                imgDrawI[cam] = imgIndex[cam] - 1;
                drawImages(cam);
            });
            subscription.history().then(function (message) {
                var cam = 2;
                if (message.data.length > 0) {
                    //console.log(message);
                    for (i = message.data.length - 1; i >= 0; i--) {
                        fillImages(message.data[i].data['img'], cam);
                    }
                    imgDrawI[cam] = imgArray[cam].length - 1;
                    drawImages(cam);
                } else
                    getLast(cam);
            }, function (err) {

            });

            var subscription = centrifuge.subscribe("cam:cam3", function (message) {
                var cam = 3;
//                console.log(message);
                fillImages(message.data.img, cam);

                clearTimeout(timer[cam]);
                imgDrawI[cam] = imgIndex[cam] - 1;
                drawImages(cam);
            });
            subscription.history().then(function (message) {
                var cam = 3;
                if (message.data.length > 0) {
                    //console.log(message);
                    for (i = message.data.length - 1; i >= 0; i--) {
                        fillImages(message.data[i].data['img'], cam);
                    }
                    imgDrawI[cam] = imgArray[cam].length - 1;
                    drawImages(cam);
                } else
                    getLast(cam);
            }, function (err) {

            });
            var subscription = centrifuge.subscribe("cam:cam4", function (message) {
                var cam = 4;
//                console.log(message);
                fillImages(message.data.img, cam);

                clearTimeout(timer[cam]);
                imgDrawI[cam] = imgIndex[cam] - 1;
                drawImages(cam);
            });
            subscription.history().then(function (message) {
                var cam = 4;
                if (message.data.length > 0) {
                    //console.log(message);
                    for (i = message.data.length - 1; i >= 0; i--) {
                        fillImages(message.data[i].data['img'], cam);
                    }
                    imgDrawI[cam] = imgArray[cam].length - 1;
                    drawImages(cam);
                } else
                    getLast(cam);
            }, function (err) {

            });


            var subscription = centrifuge.subscribe("cam:cam5", function (message) {
                var cam = 5;
//                console.log(message);
                fillImages(message.data.img, cam);

                clearTimeout(timer[cam]);
                imgDrawI[cam] = imgIndex[cam] - 1;
                drawImages(cam);
            });
            subscription.history().then(function (message) {
                var cam = 5;
                if (message.data.length > 0) {
                    //console.log(message);
                    for (i = message.data.length - 1; i >= 0; i--) {
                        fillImages(message.data[i].data['img'], cam);
                    }
                    imgDrawI[cam] = imgArray[cam].length - 1;
                    drawImages(cam);
                } else
                    getLast(cam);

            }, function (err) {

            });


            centrifuge.connect();

            /* * * * * * * */
            function getIndex(i, cam) {
                if (i > imgArray[cam].length - 1) {
                    imgDrawI[cam] = 0;
                    return 0;
                } else
                    return i;
            }

            function drawImages(cam) {
                var i = imgArray[cam][getIndex(imgDrawI[cam], cam)];
                if (i != undefined) {
                    img[cam].src = "data:image/jpeg;base64," + i;
                }
                imgDrawI[cam]++;
                clearTimeout(timer[cam]);
                timer[cam] = setTimeout(function () {
                    drawImages(cam)
                }, 2000);
            }

            function fillImages(im, cam) {
                imgArray[cam][imgIndex[cam]] = im;

                if (imgIndex[cam] < 4)
                    imgIndex[cam]++;
                else
                    imgIndex[cam] = 0;

            }

            function getLast(cam) {
                //console.log(cam);
                var xhr = new XMLHttpRequest();
                var body = 'cam=cam:cam' + encodeURIComponent(cam);
                xhr.open("POST", '/webcam/', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                //  xhr.onreadystatechange = ...;
                xhr.send(body);
            }

        </script>
    </body>
</html>

