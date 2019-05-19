package ru.dachacam;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;

import java.text.SimpleDateFormat;

import java.util.Calendar;
//import java.util.concurrent.ExecutionException;

//import org.bytedeco.javacpp.BytePointer;
import org.bytedeco.javacv.CanvasFrame;
import org.bytedeco.javacv.FrameGrabber;
//import org.bytedeco.javacv.JavaCV;
import org.bytedeco.javacpp.opencv_core.*;
//import org.bytedeco.javacpp.opencv_core.*;
//import org.bytedeco.javacv.FrameRecorder.Exception;
import org.bytedeco.javacv.OpenCVFrameGrabber;
//import static org.bytedeco.javacpp.opencv_core.cvFlip;
import static org.bytedeco.javacpp.opencv_core.CvPoint;
import static org.bytedeco.javacpp.opencv_core.cvPutText;
import static org.bytedeco.javacpp.opencv_core.CvScalar;
import static org.bytedeco.javacpp.opencv_core.cvAbsDiff;
import static org.bytedeco.javacpp.opencv_core.cvCreateImage;
import static org.bytedeco.javacpp.opencv_core.cvCloneImage;

import static org.bytedeco.javacpp.opencv_core.cvSize;
import static org.bytedeco.javacpp.opencv_core.cvSub;
import static org.bytedeco.javacpp.opencv_highgui.*;

//import static org.bytedeco.javacpp.opencv_highgui.addText;
import org.bytedeco.javacv.FFmpegFrameRecorder;
//import org.bytedeco.javacv.VideoInputFrameGrabber;
import static org.bytedeco.javacpp.avcodec.AV_CODEC_ID_H264;
import static org.bytedeco.javacpp.opencv_imgproc.CV_RGB2GRAY;
import static org.bytedeco.javacpp.opencv_imgproc.CV_THRESH_BINARY;
import static org.bytedeco.javacpp.opencv_imgproc.CV_BGR2HSV;
import static org.bytedeco.javacpp.opencv_imgproc.cvCvtColor;
import static org.bytedeco.javacpp.opencv_core.cvCountNonZero;
//import static org.bytedeco.javacpp.opencv_core.cvReleaseImage;
import static org.bytedeco.javacpp.opencv_core.cvCopy;

import static org.bytedeco.javacpp.opencv_imgproc.cvThreshold;
import static org.bytedeco.javacpp.opencv_imgproc.CV_BGR2YCrCb;

//import com.googlecode.javacv.cpp.*;

import java.util.ResourceBundle;


//import sun.util.ResourceBundleEnumeration;

//import java.io.File;
//import java.io.FileNotFoundException;

//import java.io.IOException;

//import java.util.concurrent.ExecutionException;
import java.util.concurrent.Future;

import org.apache.http.HttpResponse;
//import org.apache.http.HttpStatus;
//import org.apache.http.client.ClientProtocolException;
import org.apache.http.entity.ContentType;
import org.apache.http.impl.nio.client.CloseableHttpAsyncClient;
import org.apache.http.impl.nio.client.HttpAsyncClients;
//import org.apache.http.message.BasicHttpResponse;
//import org.apache.http.nio.client.methods.ZeroCopyConsumer;
import org.apache.http.nio.client.methods.ZeroCopyPost;
import org.apache.http.nio.protocol.BasicAsyncResponseConsumer;
import org.apache.http.nio.protocol.HttpAsyncResponseConsumer;

import org.bytedeco.javacv.FFmpegFrameGrabber;


/*enum
{
http://choorucode.com/2015/03/11/fonts-in-opencv/
    FONT_HERSHEY_SIMPLEX = 0,
    FONT_HERSHEY_PLAIN = 1,
    FONT_HERSHEY_DUPLEX = 2,
    FONT_HERSHEY_COMPLEX = 3,
    FONT_HERSHEY_TRIPLEX = 4,
    FONT_HERSHEY_COMPLEX_SMALL = 5,
    FONT_HERSHEY_SCRIPT_SIMPLEX = 6,
    FONT_HERSHEY_SCRIPT_COMPLEX = 7,
    FONT_ITALIC = 16
};
*/

public class Cam1 {

    static int JpegQuality = 80;
    static int[] p = { CV_IMWRITE_JPEG_QUALITY, JpegQuality, 0 };
    //abstract void storeImage(IplImage img);
    static String Cam;
    static String FILENAME;
    static long ForceNextTime = System.currentTimeMillis() + 300000;

    static HttpAsyncResponseConsumer consumer;
    static IplImage prevImg = null;
    static IplImage img = null;
    static long nextTime = 0;
    static long nextTimeVideo = System.currentTimeMillis() + 300000;
    static long nextTimeImg = 0;
    static CvPoint pos = null;
    static CvPoint posS = null;
    static CvFont font = null;

    static IplImage image = null;
    static IplImage cv_diff = null;
    static boolean ShowTimestamp = false;
    static boolean ShowImage = false;
    static int ThresholdVal = 32;
    static int ThresholdMax = 255;
    static int DiffValue = 300;
    static int UpdateInterval = 30 * 1000;
    static int FrameRate;
    static int DarkHourFrom = 20;
    static int DarkHourTo = 5;
    static FFmpegFrameRecorder recorder;

    static int width;
    static int height;
    static ZeroCopyPost httpost;
    static Future<HttpResponse> future;
    static CanvasFrame canvas;

    static SimpleDateFormat fileTimeStamp = new SimpleDateFormat("yyyyMMdd.HHmmss");
    static SimpleDateFormat imageTimeStamp = new SimpleDateFormat("dd.MM.yyyy HH:mm:ss");
    static SimpleDateFormat HourSDF = new SimpleDateFormat("HH");


    public static void main(String[] args) throws InterruptedException {

        if (args[0] != "")
            Cam = args[0];
        else
            Cam = "cam1";

        FILENAME = "_video/" + Cam + "/output.flv";

        ResourceBundle r = ResourceBundle.getBundle(Cam);

        FrameRate = Integer.parseInt(r.getString("Framerate"));
        canvas = new CanvasFrame("Webcam " + Cam);
        //canvas.setDefaultCloseOperation(javax.swing.JFrame.EXIT_ON_CLOSE);

        if (r.containsKey("ShowTimestamp") && Integer.parseInt(r.getString("ShowTimestamp")) == 1)
            ShowTimestamp = true;
        if (r.containsKey("ShowImage") && Integer.parseInt(r.getString("ShowImage")) == 1)
            ShowImage = true;

        if (r.containsKey("ThresholdVal"))
            ThresholdVal = Integer.parseInt(r.getString("ThresholdVal"));
        if (r.containsKey("ThresholdMax"))
            ThresholdMax = Integer.parseInt(r.getString("ThresholdMax"));

        if (r.containsKey("DiffValue"))
            DiffValue = Integer.parseInt(r.getString("DiffValue"));

        if (r.containsKey("JpegQuality")) {
            JpegQuality = Integer.parseInt(r.getString("JpegQuality"));
            p[1] = JpegQuality;
        }

        if (r.containsKey("UpdateTime"))
            UpdateInterval = Integer.parseInt(r.getString("UpdateTime")) * 1000;

        System.out.println("------ " + Cam + " ------");
        System.out.println("Source: " + r.getString("Source"));
        System.out.println("ShowTimestamp:" + ShowTimestamp);
        System.out.println("ShowImage:" + ShowImage);
        System.out.println("ThresholdVal:" + ThresholdVal);
        System.out.println("ThresholdMax:" + ThresholdMax);
        System.out.println("DiffValue:" + DiffValue);
        System.out.println("UpdateInterval:" + UpdateInterval + "ms");
        System.out.println("JpegQuality:" + p[1]);
        System.out.println("------ ------ ------");

        FrameGrabber grabber;
        if (r.getString("Source").matches("rtsp(.*)")) {
            grabber = new FFmpegFrameGrabber(r.getString("Source"));
        } else {
            grabber = new OpenCVFrameGrabber(0);
        }
        //r.getString("Source") //OpenCVFrameGrabber

        try {
            grabber.start();

            grabber.setFrameRate(FrameRate);

            width = grabber.getImageWidth();
            height = grabber.getImageHeight();

            recorder = new FFmpegFrameRecorder(FILENAME, width, height);

            recorder.setVideoCodec(AV_CODEC_ID_H264); //13
            recorder.setVideoOption("preset", "ultrafast");

            recorder.setFormat("flv");

            //recorder.setPixelFormat(0); //PIX_FMT_YUV420P

            recorder.setFrameRate(FrameRate);
            recorder.setVideoBitrate(2 * width * height);
            recorder.start();

            if (ShowTimestamp) {
                pos = new CvPoint();
                posS = new CvPoint();
                font = new CvFont();

                //pos.x(width - 200).y(height - 30);
                //posS.x(width - 200 + 1).y(height - 30 + 1); // shadow
                
                pos.x(60).y(40);
                posS.x(61).y(41); // shadow

                font.font_face(1); // FONT_HERSHEY_DUPLEX
                font.vscale(1);
                font.hscale(1);
            }

            // grabber.setTimestamp(System.currentTimeMillis());
            canvas.setCanvasSize(width, height);

            try {
                consumer = new BasicAsyncResponseConsumer();
            } catch (Exception e) {
                consumer = null;
            }
            img = grabber.grab();
            while (canvas.isVisible() && (img) != null) {

                // запись старого файла и инициализация нового
                if (System.currentTimeMillis() > nextTimeVideo) {
                    splitVideo();
                }

                if (System.currentTimeMillis() > nextTimeImg) {
                    //      cvFlip(img, img, 1);
                    if (ShowTimestamp)
                        placeTimeStamp();

                    if (ShowImage) {
                        canvas.showImage(img);
                    }

                    recorder.record(img);


                    if (System.currentTimeMillis() > ForceNextTime) {
                        System.out.println("5 min always");
                        storeImage();
                        prevImg = cvCloneImage(img);
                        nextTime = System.currentTimeMillis() + UpdateInterval;

                    }

                    if (System.currentTimeMillis() > nextTime) {
                        checkDiff();
                    }

                    nextTimeImg = System.currentTimeMillis() + 200;
                }
                //img.release();
                img = grabber.grab();
            }
            recorder.stop();

            File file = new File(FILENAME);
            String timeStamp = fileTimeStamp.format(Calendar.getInstance().getTime());

            file.renameTo(new File("_video/" + Cam + "/" + timeStamp + ".flv"));

            grabber.stop();
            canvas.dispose();

        } catch (Exception e) {
            System.out.println(e.getMessage());
        }

        System.out.println("end");
    }

    private static void placeTimeStamp() {
        String timeStamp = imageTimeStamp.format(Calendar.getInstance().getTime());
        cvPutText(img, timeStamp, posS, font, CvScalar.BLACK); //shadow
        cvPutText(img, timeStamp, pos, font, CvScalar.WHITE);
    }

    private static void splitVideo() {
        try {
            recorder.stop();
            recorder.release();

            File file = new File(FILENAME);
            String timeStamp = fileTimeStamp.format(Calendar.getInstance().getTime());

            file.renameTo(new File("_video/" + Cam + "/" + timeStamp + ".flv"));

            recorder.start();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
        nextTimeVideo = System.currentTimeMillis() + 300000;
    }

    private static void checkDiff() {
        //  String timeStamp2 =
        //      new SimpleDateFormat("yyyyMMdd.HHmmss").format(Calendar.getInstance().getTime());

        if (prevImg != null) {

            //     image = IplImage.create(width, height, 8, 1);
            if (cv_diff == null)
                cv_diff = IplImage.create(width, height, img.depth(), img.nChannels());
            //cv_diff = cvCreateImage(cvSize(width, height), );
            if (image == null)
                image = IplImage.create(width, height, img.depth(), 1);

            cvSub(prevImg, img, cv_diff, null); //определяем разницу между изображениями

            cvThreshold(cv_diff, cv_diff, ThresholdVal, ThresholdMax, CV_THRESH_BINARY);

            cvCvtColor(cv_diff, image, CV_RGB2GRAY);
            int cc = cvCountNonZero(image);
            System.out.println("diff: " + cc + " limit: " + DiffValue);

            //  cvCvtColor( cv_diff, image, CV_BGR2YCrCb );

            //   calcBackProject(cv_diff,0,,image,255,1);

            //System.out.println(cv_diff.nSize());
            //cvSaveImage("_photo/cam1/" + timeStamp2 + "_.jpg", cv_diff);
            // cvSaveImage("_photo/cam1/" + timeStamp2 + "__.jpg", image);
            if (cc > DiffValue)
                storeImage();
            //cvSaveImage("_photo/cam1/" + timeStamp2 + ".jpg", img);

            // image.release();
            // cv_diff.release();

        } else
            storeImage();
        //cvSaveImage("_photo/cam1/" + timeStamp2 + ".jpg", img);

        nextTime = System.currentTimeMillis() + UpdateInterval;
    }

    private static void storeImage() {

        String timeStamp2 = fileTimeStamp.format(Calendar.getInstance().getTime());

        try {

            //  System.out.println("store Image");
            cvSaveImage("_photo/" + Cam + "/" + timeStamp2 + ".jpg", img, p);

            if (prevImg == null)
                prevImg = cvCreateImage(cvSize(width, height), img.depth(), img.nChannels());

            cvCopy(img, prevImg, null);
            //    prevImg = cvCloneImage(img);

            // cvReleaseImage(img);

            int Hour = Integer.parseInt(HourSDF.format(Calendar.getInstance().getTime()));

            if (Hour >= DarkHourTo && Hour < DarkHourFrom)
                ForceNextTime = System.currentTimeMillis() + 300000; // 5 min
            else
                ForceNextTime = System.currentTimeMillis() + 3600000; // 1 hour


            try {
                CloseableHttpAsyncClient httpclient = HttpAsyncClients.createDefault();
                try {
                    httpclient.start();

                    httpost =
                            new ZeroCopyPost("http://localhost/webcam/save.php?c=" + Cam, new File("_photo/" +
                                                                                                           Cam + "/" +
                                                                                                           timeStamp2 +
                                                                                                           ".jpg"),
                                             ContentType.create("image/jpeg")); //ContentType.create("image/jpeg")

                    future = httpclient.execute(httpost, consumer, null);
                    //HttpResponse response =
                    future.get();


                    //System.out.println(httpclient..toString());
                    //File result;
                    //       result = future.get();
                    //BasicHttpResponse result = future.get();
                    // System.out.println("Response file length: " + result);

                    httpost.close();
                    System.out.println("Post image. . .");
                } finally {
                    httpclient.close();
                }
                System.out.println("Done");
            } catch (FileNotFoundException fnfe) {
                // TODO: Add catch code
                System.out.println("FileNotFoundException");
                fnfe.printStackTrace();
            } catch (IOException ioe) {
                // TODO: Add catch code
                System.out.println("IOException");
                ioe.printStackTrace();
            }

        } catch (Exception e) {
            // TODO: Add catch code
            System.out.println("storeImage");
            e.printStackTrace();
        }


    }

}
