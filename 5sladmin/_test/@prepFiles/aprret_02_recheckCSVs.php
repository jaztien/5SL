<?php
/////////////////////////////////////////////////////////////////////////////////////////////
///////////////// 實價登錄資料處理——第二步.與原始檔案檢查並比對轉存的CSV檔案，列出遺失的檔案名稱 /////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
header("Content-Type:text/html; charset=utf-8");
$xlsfolder = isset($_POST['xlsfolder']) ? $_POST['xlsfolder'] : '';
$csvfolder = isset($_POST['csvfolder']) ? $_POST['csvfolder'] : '';
$sdata = isset($_POST['sdata']) ? $_POST['sdata'] : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life｜｜實價登錄資料處理——第二步.與原始檔案檢查並比對轉存的CSV檔案，列出遺失的檔案名稱</title>
  </head>
<body>
<h2>實價登錄資料處理——第二步.與原始檔案檢查並比對轉存的CSV檔案，列出遺失的檔案名稱</h2>
<form id="form_xlstocsv" name="form_xlstocsv" action="#" method="post">
    <div style="display:inline-block;vertical-align:top;line-height:32px;">
        來源資料夾: <input type="text" id="xlsfolder" name="xlsfolder" style="width:200px;" value="aprretFiles/Original/20240421"><br>
        目標資料夾: <input type="text" id="csvfolder" name="csvfolder" style="width:200px;" value="aprretFiles/CSV_single">
    </div>
    <div style="display:inline-block;vertical-align:top;margin-left:12px;">
        <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行比對</button>
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_02_recheckCSVs.php';">RESET</button>
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
    </div>
</form>


<hr>
<div style="font-size:16px;line-height:20px;">
    
<?php
$checkLoading = 1;
if(empty($xlsfolder)){ echo " /尚未指定來源資料夾！"; $checkLoading = 0; }
if(empty($csvfolder)){ echo " /尚未指定目標資料夾！"; $checkLoading = 0; }
if(empty($checkLoading)){
    //echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
    exit();
}

$xls_folderpath = $xlsfolder."/";//aprretFilesO
$csv_folderpath = $csvfolder."/";//aprretCSV
$ctotalNum=0;
if (is_dir($csv_folderpath)) {
    $cfiles = opendir($csv_folderpath); {
        if ($cfiles) {
            while (($csv_subfolder = readdir($cfiles)) !== FALSE) {
                if ($csv_subfolder != '.' && $csv_subfolder != '..' && $csv_subfolder != '.DS_Store') {
                    $cdirpath = $csv_folderpath . $csv_subfolder . "/";
                    if (is_dir($cdirpath)) {
                        $cfile = opendir($cdirpath); {
                            if ($cfile) {
                                while (($csv_filename = readdir($cfile)) !== FALSE) {
                                    if ($csv_filename != '.' && $csv_filename != '..' && $csv_filename != '.DS_Store' && $csv_filename != 'Icon?') {
                                        $fileARR[] = basename($csv_filename, '.csv');
                                        $ctotalNum++;
                                    }
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
echo "Total CSV Files Sum: ".$ctotalNum;
sort($fileARR);
//print_r($fileARR);
//----------------------------------------------
$totalNum = 0;
//$fileARR = array();
// CHECKING WHETHER PATH IS A DIRECTORY OR NOT
if (is_dir($xls_folderpath)) {
    // GETTING INTO DIRECTORY
    $files = opendir($xls_folderpath); {
        // CHECKING FOR SMOOTH OPENING OF DIRECTORY
        if ($files) {
            //READING NAMES OF EACH ELEMENT INSIDE THE DIRECTORY 
            while (($xls_subfolder = readdir($files)) !== FALSE) {
                // CHECKING FOR FILENAME ERRORS
                if ($xls_subfolder != '.' && $xls_subfolder != '..' && $xls_subfolder != '.DS_Store' && $xls_subfolder != 'Icon?') {//Icon?
                    //echo "SUBFOLDER--" .$xls_subfolder . "<br>"."Files in ".$xls_subfolder."--<br>";            
                    $dirpath = $xls_folderpath . $xls_subfolder . "/";
                    // GETTING INSIDE EACH SUBFOLDERS
                    if (is_dir($dirpath)) {
                        $dpath = opendir($dirpath); {
                            if($dpath) {
                                while (($ffolder = readdir($dpath)) !== FALSE) {
                                    // CHECKING FOR FILENAME ERRORS
                                    if ($ffolder != '.' && $ffolder != '..' && $ffolder != '.DS_Store') {
                                        $fpath = $dirpath . $ffolder . "/";
                                        $file = opendir($fpath); {
                                            if ($file) {
                                                    //READING NAMES OF EACH FILE INSIDE SUBFOLDERS
                                                while (($xls_filename = readdir($file)) !== FALSE) {
                                                    if ($xls_filename != '.' && $xls_filename != '..' && $xls_filename != '.DS_Store' && $xls_filename != 'Icon?') {
                                                        $filename=substr($xls_filename, 0, strrpos($xls_filename, "."));
                                                        if (!in_array($filename, $fileARR)){
                                                            echo $filename ."<br>";
                                                        }
                                                        $totalNum++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }
    }
}

//echo $fileARR;
echo "<hr>";
echo "Totle FIles Missed: ".($totalNum - $ctotalNum)."<br>";
echo "<hr>";
echo "Total Excel Files Sum: ".$totalNum;
echo "<hr>";

echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
?>
</div>
</body>
</html>

