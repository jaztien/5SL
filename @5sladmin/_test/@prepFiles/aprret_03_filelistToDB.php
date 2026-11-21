<?php
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// 實價登錄資料處理——第三步.建立工作檔案資料表  ///////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $csvfolder = isset($_POST['csvfolder']) ? $_POST['csvfolder'] : '';
    
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
                document.getElementById('form_filetodb').submit();
            }


        </script>
        <div id="loading" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;;background-image: url('loading.gif');background-color:#2A2826;background-repeat: no-repeat;background-position: 50% 0;background-size:contain;opacity: 0.75;margin:0;text-align: center; z-index:99;">
            <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
                <div id="loadingtext" style="font-size:24px;">正在生成檔案資料表......</div>
                <div><button id="loopbut" style="height:32px;width:120px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_03_filelistToDB.php';">終止操作</button></div>
            </div>
        </div>
        <h2>實價登錄資料處理——第三步.建立工作檔案資料表</h2>
        <form id="form_filetodb" name="form_filetodb" action="#" method="post">
            <div style="display:inline-block;vertical-align:top;line-height:32px;">
                年份: <input type="text" id="csvfolder" name="csvfolder" style="width:200px;" value="aprretFiles/CSV_single"><br>
                年份: <input type="text" id="year" name="year" style="width:200px;" value="<?php echo $year; ?>">
            </div>
            <div style="display:inline-block;vertical-align:top;margin-left:12px;">
                <button type="button" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;" onClick="doSubmit();">SEND</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_03_filelistToDB.php';">RESET</button>
                <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
            </div>
        </form>

        <hr>
        <div style="font-size:16px;line-height:20px;">
            
        <?php
        if(empty($year) || empty($csvfolder)){
            echo "/尚未設定年份或檔案資料夾！";
            echo "<script>window.document.getElementById('loading').style.display = 'none';</script>";
            exit();
        }
        $cFolderNum = 0;
        $csv_folderpath = $csvfolder."/";//aprretCSV
        //--取得並排序子目錄陣列------------------
        /*
        if(is_dir($csv_folderpath)) {
            if($cfiles = opendir($csv_folderpath)) {
                while(($csv_subfolder = readdir($cfiles)) !== FALSE){
                    if($csv_subfolder != '.' && $csv_subfolder != '..' && $csv_subfolder != '.DS_Store' && $csv_subfolder != 'Icon?'){//Icon?
                        $arr_subfolder[] = $csv_subfolder;
                    }
                }

            }
        }
        sort($arr_subfolder);
        */
        //----------------------------------
        $arr_subfolder[0]=$year;//僅處理單一資料夾
        foreach($arr_subfolder as $sf){
            $cTotalRowNum = 0;
            $cTotalFileNum = 0;
            $cdirpath = $csv_folderpath . $sf . "/";
            if(is_dir($cdirpath)) {
                if($cfiles = opendir($cdirpath)) {
                    //--取得並排序子目錄下的檔案陣列--------------------
                    $arr_subfile=array();
                    $cFileNum=0;
                    while(($csv_filename = readdir($cfiles)) !== FALSE) {
                        if ($csv_filename != '.' && $csv_filename != '..' && $csv_filename != '.DS_Store'){
                            $arr_subfile[] = $csv_filename;
                            $cFileNum++;
                            $cTotalFileNum++;
                        }
                    }
                    sort($arr_subfile);
                    //--直接執行檔案操作-------------------
                    $doneFiles = 0;
                    foreach($arr_subfile as $f){
                        //--------------------------------

                        $excel= PHPExcel_IOFactory::load($cdirpath.$f); 
                        $sheet=$excel->getSheet(0);
                        $highestRow = $sheet->getHighestRow(); // 取得總列數
                        $startRow = $sheet->getCell("E2")=='單價'? 3 : 2;
                        $totalRows = $highestRow - $startRow;
                        //echo $totalRows.'<br>';
                        $cTotalRowNum += $totalRows;
                        //--------------------------------
                        $nameSplit=@explode("_",$f);
                        $getCity = $nameSplit[0];
                        $getDist = $nameSplit[1];
                        //--------------------------------
                        $sql = "INSERT INTO filelist VALUES(null, $sf, $getCity, $getDist, '".$f."', $startRow, $highestRow, $totalRows, 0, '')";
                        $result = mysqli_query($link, $sql);
                        //$result = true;
                        if($result){ 
                            echo $sf.'@@ '.$getCity.'@@ '.$getDist.'@@ '.$f.'@@ '.$totalRows.'<br>';
                            $doneFiles++; 
                        }
                        else { 
                            echo '<span style="color:red;">'.$sf.'@@ '.$getCity.'@@ '.$getDist.'@@ '.$f.'@@ '.$totalRows.'</span><br>';
                            //break; 
                        }
                    }
                    //----------------------------------
                    echo "<hr>File Number: ".$cFileNum.'<br><br><hr>';
                }

            }
        
            $fsql = "INSERT INTO ffcount VALUES(null, $sf, $cTotalFileNum, $cTotalRowNum)";
            $fresult = mysqli_query($link, $fsql);
            echo "Total Files: ".$doneFiles."/".$cTotalFileNum;
            echo "<br><br><hr>Total Data Rows: ".$cTotalRowNum;
            echo "<br><br>";
        
        }

        echo "<script>window.document.getElementById('loading').style.display = 'none';</script>";
        mysqli_close($link);

        ?>
        </div>
    </body>
</html>

