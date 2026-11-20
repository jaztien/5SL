<?php

header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
session_start();
//if(!isset($_SESSION['SESS_AD_ID']) || empty($_SESSION['SESS_AD_ID'])){
    //header("location: ../?e=9");//遺失 SESSION，可能閒置超時，系統自動登出！
    //exit();
//}
//img_up_func.php

/*$folderPath = '../imageFiles/adphoto//';

$image_parts = explode(";base64,", $_POST['image']);
$image_type_aux = explode("image/", $image_parts[0]);
$image_type = $image_type_aux[1];
$image_base64 = base64_decode($image_parts[1]);
$file = $folderPath . uniqid() . '.png';

file_put_contents($file, $image_base64);

echo $file;*/
if (!isset($_POST['proc']) || !isset($_POST["image"])) {
    $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'圖像上傳失敗！！', 'upTime'=> time());
    echo json_encode($rtn);
    exit();
}
switch ($_POST['proc']) {
    case 'addADphoto':
        if (isset($_POST["image"])) {
            $data = $_POST["image"];
            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);

            $imageName = '../imageFiles/adphoto/' . $_SESSION['SESS_AD_ID'] . '_temp_adphoto.jpg';

            file_put_contents($imageName, $data);
            $_SESSION['SESS_TEMPPHOTO'] = $imageName;
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'圖像上傳成功！！', 'upTime'=> time(), 'imgPATH'=> $imageName.'?'.time());
            echo json_encode($rtn);
        }
    break;
    case 'upADphoto':
        if (isset($_POST["image"])) {
            $data = $_POST["image"];
            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);

            $imageName = '../imageFiles/adphoto/' . intval($_POST['adID']) . '_adphoto.jpg';

            file_put_contents($imageName, $data);
            //---------------- 製作縮圖 ----------------//
            $src = imagecreatefromjpeg($imageName);
            $thumb = imagecreatetruecolor(100, 100);
            imagecopyresized($thumb, $src, 0, 0, 0, 0, 100, 100, 400, 400);
            $thumbName = '../imageFiles/adphoto/' . intval($_POST['adID']) . '_adphoto_thumb.jpg';
            imagejpeg($thumb, $thumbName);
            //---------------- /製作縮圖 ----------------//
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'圖像上傳成功！！', 'upTime'=> time(), 'imgPATH'=> $imageName.'?'.time());
            echo json_encode($rtn);
        }
    break;
}
