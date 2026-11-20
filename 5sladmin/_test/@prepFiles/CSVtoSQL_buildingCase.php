<?php
error_reporting(E_ALL);
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//require_once('../includeFiles/QPay.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>	
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
    <title>CSV 檔案讀取寫入 SQL | Building Case 社區建案</title>
    <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo "?t=".time(); ?>">
</head>

<body>
    <div class="wrapper">
            <!-- 頁頭導覽條 -->
            <section class="content-header">
                <div class="container-fluid">

                            <h4> 社區建案 CSV 檔案讀取寫入 SQL</h4>
                            <form id="transform" name="transform" action="#" method="post" style="width:400px;">
                                <select class="form-control" name="currFile" id="currFile">
                                    <option value="">-- 請選擇執行檔案 --</option>
                                    <option value="buildingCase_53396-53440_20240629.csv">buildingCase_53396-53440_20240629.csv</option>
                                    <option value="buildingCase_53396-53440_20240629.csv" disabled>buildingCase_53440_20240629.csv</option>
                                </select>
                                <br>
                                <input type="hidden" id="pass" name="pass" value="pass" style="width:120px;height:40px;">
                                <button type="submit" class="btn btn-danger" style="width:80px;height:40px;">執行</button>&nbsp;&nbsp;
                                <button type="button" class="btn btn-secondary" onClick="window.location.replace('CSVtoSQL_buildingCase.php');" style="width:60px;height:40px;">重置</button>
                                <button type="button" class="btn btn-warning" onClick="window.location.replace('index.php');" style="width:60px;height:40px;">首頁</button>
                            </form>
                            <hr>
                            <?php
                            if(!isset($_POST['pass'])){
                                return false;
                            }
                            if(empty($_POST['currFile'])){
                                alert('請選擇需要執行的檔案！');
                                return false;
                            }

                            $csv = fopen($_POST['currFile'],'rb');
                            //列出檔案內容
                            $i = 0;
                            while ((! feof($csv)) && ($line = fgetcsv($csv))){
                                if($i <2){
                                   $i++;
                                   continue;
                                }
                                printf($line[0]);
                                printf($line[1]);
                                printf($line[2]);
                                printf("<br>");



                                $i++;
                            }

                            //關閉檔案
                            fclose($csv);


                        ?>
                </div>
            </section>
            <!-- /頁頭導覽條 -->
    </div>

</body>
</html>