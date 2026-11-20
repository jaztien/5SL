<?php
/////////////////////////////////////////////////////////////////////////////////////////////
//////////////////// 實價登錄資料處理——第二步.比對原始檔案與資料庫所載檔案數量是否一致 ////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
error_reporting(E_ALL); 
header("Content-Type:text/html; charset=utf-8");
include 'PHPExcel/Classes/PHPExcel.php';
$host = 'localhost';
$dbuser ='root';
$dbpassword = '1111';
$dbname = 'jazhome';
$link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
if($link){
    mysqli_query($link,'SET NAMES uff8');
    //echo "正確連接資料庫";
}
else {
   // echo "不正確連接資料庫</br>" . mysqli_connect_error();
}
$doact = isset($_POST['doact']) ? $_POST['doact'] : '';

$osql = "SELECT SUM(f_rows - f_startrow) AS CC FROM filelist";
$oresult = mysqli_query($link, $osql);
$orow = mysqli_fetch_array($oresult);
echo "總紀錄數： ".$orow['CC'].'<hr>';


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life｜實價登錄資料處理——第二步.比對原始檔案與資料庫所載檔案數量是否一致 </title>
</head>
<body>
    <h2>實價登錄資料處理——第二步.比對原始檔案與資料庫所載檔案數量是否一致</h2>
    <form id="form_xlsfolder" name="form_xlsfolder" action="#" method="post">
    <table>
            <tr>
                <td style="padding-left:40px;">
                    <input type="hidden" id="doact" name="doact" value="1">
                    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行比對</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_02_ComparisonFileNum.php';">RESET</button>
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
                </td>
            </tr>
        </table>
    </form>
    <hr>
    <div style="font-size:12px;line-height:16px;">
        
        <?php
            $checkLoading = 1;
            if(empty($doact)){ echo " /準備執行檔案數量比較作業"; $checkLoading = 0; }
            if(empty($checkLoading)){
                //echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
                exit();
            }

            
            $xls_folderpath = "aprretFiles/Original/20240421";
            $dirFiles = array();
            // opens images folder
            if ($handle = opendir($xls_folderpath)) {
                while (false !== ($citydir = readdir($handle))) {
                    if ($citydir != '.' && $citydir != '..' && $citydir != '.DS_Store') {
                        $dirFiles[] = $citydir;
                    }
                }
                closedir($handle);
            }
            sort($dirFiles);
            $totalNum = 0;
            foreach($dirFiles as $cFolder){
                $csubpath = $xls_folderpath .'/'. $cFolder;
                if (is_dir($csubpath)) {
                    $cdist = opendir($csubpath); {
                        if ($cdist) {
                            $distDir = array();
                            while (($xls_dsubfolder = readdir($cdist)) !== FALSE) {
                                if ($xls_dsubfolder != '.' && $xls_dsubfolder != '..' && $xls_dsubfolder != '.DS_Store') {
                                    $distDir[] = $xls_dsubfolder;
                                    //$dsubpath = $csubpath .'/'. $xls_dsubfolder;
                                    //echo $dsubpath."<br>";
                                }
                            }
                            sort($distDir);
                            foreach($distDir as $dFolder){
                                $dsubpath = $csubpath .'/'.$dFolder;
                                if (is_dir($dsubpath)) {
                                    $cfile = opendir($dsubpath); {
                                        if ($cfile) {
                                            $ctotalNum = 0;
                                            while (($xls_filename = readdir($cfile)) !== FALSE) {
                                                if ($xls_filename != '.' && $xls_filename != '..' && $xls_filename != '.DS_Store') {
                                                    $ctotalNum++;
                                                }
                                            }
                                            $totalNum += $ctotalNum;
                                            $osql = "SELECT SUM(cfd_countnum) AS CC FROM `check_filenum_district`";
                                            $osql .= " WHERE cfd_dist='".$dFolder."'";
                                            $oresult = mysqli_query($link, $osql);
                                            $orow = mysqli_fetch_array($oresult);
                                            if($ctotalNum == $orow['CC']){ 
                                                echo $dFolder."區域內檔案數量比對相符，共 ".$ctotalNum." 筆。<br>";
                                            } else {
                                                echo "<span style='color:red;'>".$dFolder."區域內檔案數量比對不符，原檔數量共 ".$ctotalNum." 筆。實際數量共 ".$orow['CC']." 筆</span><br>";
                                            }


                                        }
                                    }
                                }
                            }
                        }

                    }
                }
                
            }

            echo "<hr>";
            echo "總檔案數: ".$totalNum;
            echo "<hr>";
        ?>
    </div>
</body>
</html>

