<?php
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// 實價登錄資料處理——第三步.建立工作檔案資料表  ///////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
    $imgfolder = isset($_POST['imgfolder']) ? $_POST['imgfolder'] : '';
    
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
        //echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life||實價登錄資料處理——第三步.建立工作檔案資料表 </title>
  </head>
    <body style="margin:10px;">
        <script>
            function doSubmit(){
                window.document.getElementById('loading').style.display = 'block';
                document.getElementById('form_imgoption').submit();
            }
        </script>
        <div id="loading" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;;background-image: url('loading.gif');background-color:#2A2826;background-repeat: no-repeat;background-position: 50% 0;background-size:contain;opacity: 0.75;margin:0;text-align: center; z-index:99;">
            <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
                <div id="loadingtext" style="font-size:24px;">正在進行檔案轉換......</div>
                <div><button id="loopbut" style="height:32px;width:120px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_03_filelistToDB.php';">終止操作</button></div>
            </div>
        </div>
        <h2>檔案系統批次處理- 社區建案圖檔轉移</h2>
        <form id="form_imgoption" name="form_imgoption" action="#" method="post">
            <div style="display:inline-block;vertical-align:top;line-height:32px;">
                圖檔資料夾: <input type="text" id="imgfolder" name="imgfolder" style="width:200px;" value="uploadFiles/bc">
            </div>
            <div style="display:inline-block;vertical-align:top;margin-left:12px;">
                <button type="button" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;" onClick="doSubmit();">SEND</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/imagefilesOptions.php';">RESET</button>
                <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
            </div>
        </form>

        <hr>
        <div style="font-size:16px;line-height:20px;">
            
        <?php
        if(empty($imgfolder)){
            echo "/尚未設定檔案資料夾！";
            echo "<script>window.document.getElementById('loading').style.display = 'none';</script>";
            exit();
        }
        $cImgNum = 0;
        $img_folderpath = $imgfolder."/";

        $sql = "SELECT * FROM `data_buildcase`";
        //$sql .= " WHERE dt_distcode='".$di."' AND dt_year='".$yi."'";
        $sql .= " ORDER BY bc_id ASC";
        $result = mysqli_query($link, $sql);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
        while($bcrow = mysqli_fetch_assoc($result)){
            $oldPath =  $imgfolder."/".$bcrow['bc_city']."/bcimg_".$bcrow['bc_id']."_0_.jpg";
            $newPath = $imgfolder."/".$bcrow['bc_city']."/".$bcrow['bc_district']."/bcimg_".$bcrow['bc_id']."_0_.jpg";
            if(file_exists($oldPath)){

                if( rename($oldPath, $newPath) ) {  
                    $cImgNum++;
                    echo "File has been moved!<br>";  
                }  
                else {
                    echo $newPath."<br>";  
                }
                
            }

        }
        echo '<br><br><hr><br>';
        echo 'Total Images: '.$cImgNum;
        echo '<br><br>';
        echo "<script>window.document.getElementById('loading').style.display = 'none';</script>";
        mysqli_close($link);

        ?>
        </div>
    </body>
</html>

