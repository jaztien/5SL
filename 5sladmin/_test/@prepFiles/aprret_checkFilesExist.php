<?php
/////////////////////////////////////////////////////////////////////////////////////////////
//////////////////// 實價登錄資料處理———檢查檔案的時間段連貫性 ////////////////////
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



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life｜實價登錄資料處理———檢查檔案的時間段連貫性 </title>
</head>
<body>
    <h2>實價登錄資料處理——檢查檔案的時間段連貫性</h2>
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
                    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行查驗</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_checkFilesExist.php';">RESET</button>
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
            $cityfolder = $city;
            $xls_folderpath = "aprretFiles/Original/20240421/".$cityfolder;
            $subFolderArr =array();
            $count = 0;
            $totalFiles=0;
            if (is_dir($xls_folderpath)) {
                $cfiles = opendir($xls_folderpath); {
                    if ($cfiles) {
                        while (($xls_subfolder = readdir($cfiles)) !== FALSE) {
                            if ($xls_subfolder != '.' && $xls_subfolder != '..' && $xls_subfolder != '.DS_Store' && $xls_subfolder != 'Icon?') {//Icon?
                                $subFolderArr[] = $xls_subfolder;
                            }
                        }
                        
                        sort($subFolderArr);
                        foreach($subFolderArr as $dFolder){
                            $folderDist = $xls_folderpath .'/'.$dFolder;
                            if (is_dir($folderDist)) {
                                $cfile = opendir($folderDist); {
                                    if ($cfile) {
                                        //$ctotalNum = 0;
                                        $fNameArr=array();
                                        while (($xls_filename = readdir($cfile)) !== FALSE) {
                                            if ($xls_filename != '.' && $xls_filename != '..' && $xls_filename != '.DS_Store') {
                                                $fNameArr[]=$xls_filename;
                                                $totalFiles++;
                                            }
                                        }
                                        
                                        sort($fNameArr);
                                        $yearString = '';
                                        $monthString = '';
                                        $prevYear = '';
                                        foreach($fNameArr as $xls_filename){
                                            $pureName = str_replace('.xls','',$xls_filename);
                                            $splitName = explode('_',$pureName);
                                            //$splitFile = substr(strrchr($pureName, '_'), 1);
                                            $getCity = trim($splitName[0]);
                                            $getDist = trim($splitName[1]);
                                            if($getCity!=$cityfolder || $getDist!=$dFolder){
                                                echo "Error 0 @@ 地區不匹配 @@ ".$pureName."<br>";
                                                $count++;
                                            }

                                            $getFullPeriod = $splitName[2];
                                            $splitFullPeriod = explode('-',$getFullPeriod);
                                            $getFrom = $splitFullPeriod[0];
                                            $getTo = $splitFullPeriod[1];
                                            $getYear = strchr($getFrom,".",true);
                                            
                                            $getFromMonth = (int)substr(strrchr($getFrom,'.'),1);
                                            $getToMonth = (int)substr(strrchr($getTo,'.'),1);
                                            if($getYear != $prevYear){
                                                if($monthString!='' && $monthString!=":1:2:3:4:5:6:7:8:9:10:11:12"){
                                                    $monthLoString = str_replace(":",":@:",$monthString);
                                                    $monthArr = explode('@',substr($monthLoString,2));
                                                    $monthLost = str_replace($monthArr,'',":1::2::3::4::5::6::7::8::9::10::11::12:");
                                                    echo "Error 1 @@ 時間段缺失 @@ ".$getCity." @@ ".$getDist." @@ ".$prevYear." @@ ".$monthLost."<br>";
                                                    $count++;
                                                }
                                                $yearString = $yearString.":".$getYear;
                                                $monthString = '';
                                                $prevYear = $getYear;
                                            }
                                            for($i=$getFromMonth;$i<=$getToMonth;$i++){
                                                $monthString = $monthString.":".$i;
                                            }
                                            

                                            //echo $getCity."@@".$getDist."@@".$getYear."@@".$getFromMonth."@@".$getToMonth."<br>";
        

                                        }
                                        if($yearString!="" && $yearString!=":101:102:103:104:105:106:107:108:109:110:111:112"){
                                            $yearArr = explode(':',substr($yearString,1));
                                            $yearLost = str_replace($yearArr,'',":101:102:103:104:105:106:107:108:109:110:111:112");
                                            echo "Error 2 @@ 年度段缺失 @@ ".$getCity." @@ ".$getDist." @@ ".$yearLost."<br>";
                                            $count++;
                                        }
                                        
                                    }
                                }
                            }
                        }
                        
                    }
                }
            }
            echo "<hr>";
            if($count==0) echo "城市碼 ".$cityfolder." 地區檔案無任何缺失的時間段！！";
            echo "<hr>";
            echo $cityfolder." 總檔案數: ".$totalFiles;
            echo "<hr>";

        ?>
    </div>


</body>
</html>

