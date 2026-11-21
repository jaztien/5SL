<?php
/////////////////////////////////////////////////////////////////////////////////////////////
//////////////////// 實價登錄資料處理——第一步.轉為CSV檔案並依年份轉存於年份資料夾 ////////////////////
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
$locJS=_fn_getCachedJsonData("wejoin.json");
$city = isset($_POST['city']) ? $_POST['city'] : '';
$dist = isset($_POST['dist']) ? $_POST['dist'] : '';

$osql = "SELECT SUM(cfd_countnum) AS cNum,SUM(cfd_filenum) AS fNum FROM check_filenum_district";
//$osql .= " WHERE cfd_dist='1101'";
$oresult = mysqli_query($link, $osql);
//$orow = mysql_fetch_array($oresult);
$orow = mysqli_fetch_array($oresult);
//echo $orow['ss'].'/'.$orow_cnt.'<hr>';
echo $orow['cNum'].' / '.$orow['fNum'].'<hr>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life｜實價登錄資料處理——第一步.轉為CSV檔案並依年份轉存於年份資料夾 </title>
</head>
<body>
    <h2>實價登錄資料處理——第一步.轉為CSV檔案並依年份轉存於年份資料夾</h2>
    <form id="form_xlsfolder" name="form_xlsfolder" action="#" method="post">
    <table>
            <tr>
                <td style="padding-right:20px;border-right:#333333 1px solid;">
                    城市 <select id="city" name="city" onchange="localSelect(this);" style="width:120px;height:24px;">
                        <option value="">--選擇城市--</option>
                        <?PHP
                            foreach($locJS as $key => $value){
                            echo "<option value='".$value['value']."'";
                            if($city==$value['value']) { echo " selected"; }
                            echo ">".$value['text']."</option>";
                            }
                        ?>
                    </select><span id="citynum" style="padding-left:8px;font-weight:bold;"><?php echo $city;?></span><br><br>
                    地區 <select id="dist" name="dist" onchange="distSelect(this)" style="width:120px;height:24px;" disabled>
                        <option value=''>--選擇地區--</option>
                        <?PHP
                            if(!empty($city)) {
                                foreach($locJS[$city]['district'] as $key => $value){
                                    echo "<option value='".$key."'";
                                    if($dist==$key) { echo " selected"; }
                                    echo ">".$value['text']."</option>";
                                }
                            }
                        ?>
                    </select><span id="distnum" style="padding-left:8px;font-weight:bold;"><?php echo $dist;?></span>
                </td>
                <td style="padding-left:40px;">
                    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行轉存</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_01_XLStoCSV.php';">RESET</button>
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
                </td>
            </tr>
        </table>
    </form>
    <script> 
    var locobj = JSON.parse('<?PHP echo preg_replace("/\s+/", "", $locGet); ?>'); //若要給JAVA使用，必須去除 whitespace
    function localSelect(){
        //------------- 改變行政區劃選單
        var cityID = document.getElementById('city').value;
        if(cityID==0) {
            cityID='';
            document.getElementById('distnum').innerHTML = '';
        }
        document.getElementById('citynum').innerHTML = cityID;
        const distElm = document.getElementById('dist');
        distElm.innerHTML = '<option value="">--選擇地區--</option>'; 
        const disT = locobj[cityID]['district'];
        for (const [key, value] of Object.entries(disT)) {
            distElm.insertAdjacentHTML('beforeend', `<option value="${key}">${value['text']}</option>`)
        }
    }
    function distSelect(){
        var distyID = document.getElementById('dist').value;
        document.getElementById('distnum').innerHTML = distyID;
    }
    function CopytoClipboard() {
        var copyText = document.getElementById("prevstr");
        copyText.select();
        document.execCommand("copy");
        alert("Copied!!");
    }
</script>
    <hr>
    <div style="font-size:12px;line-height:16px;">
        
        <?php
            $checkLoading = 1;
            if(empty($city)){ echo " /尚未指定資料夾！(資料夾名稱為二位數城市代碼)"; $checkLoading = 0; }
            if(empty($checkLoading)){
                //echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
                exit();
            }

            
            $numCountARR = array();
            $cityfolder = $city;
            $xls_folderpath = "aprretFiles/Original/20240421/".$cityfolder;
            $csv_folderpath = "aprretFiles/CSV_single/";
            $count = 0;
            if (is_dir($xls_folderpath)) {
                $cfiles = opendir($xls_folderpath); {
                    if ($cfiles) {
                        while (($xls_subfolder = readdir($cfiles)) !== FALSE) {
                            if ($xls_subfolder != '.' && $xls_subfolder != '..' && $xls_subfolder != '.DS_Store' && $xls_subfolder != 'Icon?') {//Icon?
                                $cdirpath = $xls_folderpath .'/'. $xls_subfolder;
                                $numCountARR[$xls_subfolder] = 0;                                
                                //if (!is_dir($cdirpath)) { mkdir($cdirpath, 0777, true); }
                                if (is_dir($cdirpath)) {
                                    $cfile = opendir($cdirpath); {
                                        if ($cfile) {
                                            $totalNum = 0;
                                            $ctotalNum = 0;
                                            while (($xls_filename = readdir($cfile)) !== FALSE) {
                                                if ($xls_filename != '.' && $xls_filename != '..' && $xls_filename != '.DS_Store') {
                                                    $ext=substr(strrchr($xls_filename, '.'), 1);
                                                    $pureName = str_replace('.'.$ext,'',$xls_filename);
                                                    $splitFile = substr(strrchr($pureName, '_'), 1);
                                                    $newFolder = $csv_folderpath.strchr($splitFile,".",true);
                                                    if (!is_dir($newFolder)) { mkdir($newFolder, 0777, true); }
                                                    if(_fn_getCsv($cdirpath,$newFolder,$pureName)){
                                                        $count++;
                                                        echo $count.'. '.$newFolder.'/'.$pureName.'.csv<br>';
                                                        $numCountARR[$xls_subfolder]++;
                                                        $ctotalNum++;
                                                    }
                                                    $totalNum++;
                                                }
                                            }
                                            $difference = $totalNum - $ctotalNum;
                                            $checkneed = $difference > 0 ? 1 : 0;
                                            $sql = "SELECT * FROM `check_filenum_district` WHERE cfd_city='".$cityfolder."' AND cfd_dist='".$xls_subfolder."'";
                                            $result = mysqli_query($link, $sql);
                                            $row_cnt = mysqli_num_rows($result);
                                            $row = mysqli_fetch_array($result);
                                            if($row_cnt && $row_cnt > 0){ 
                                                $upsql = "UPDATE `check_filenum_district` SET cfd_countnum='".$totalNum."', cfd_filenum='".$ctotalNum."', cfd_difference='".$difference."', cfd_checkneed='".$checkneed."' WHERE cfd_id='".$row['cfd_id']."'";
                                                $up = mysqli_query($link, $upsql);
                                            } else {
                                                $ins = "INSERT INTO `check_filenum_district` VALUES(null, '".$cityfolder."', '".$xls_subfolder."', '".$totalNum."', '".$ctotalNum."', '".$difference."', '".$checkneed."')";
                                                $result = mysqli_query($link, $ins);
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
            print_r($numCountARR);
            echo "<hr><br><br><br><br>";
            function _fn_getCsv($foldER='',$nfoldER='',$f=''){
                $filename = $foldER."/".$f.".xls";
                $newname = $nfoldER."/".$f.".csv";
                $excel= PHPExcel_IOFactory::load($filename);  
                $writer = PHPExcel_IOFactory::createWriter($excel, 'CSV');
                $writer->setUseBOM(true);
                $writer->save($newname);
                return true;
            }

        ?>
    </div>
    <audio autoplay='true' style='display:none;'><source src='sounds/theetone.wav'></audio>
</body>
</html>

