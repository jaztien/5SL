<?php
//require_once('5sladmin/includeFiles/init.inc.php');
//require_once('includeFiles/db.inc.php');
//require_once('includeFiles/common.lib.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>	
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="imageFiles/favicon.ico" type="image/ico" />
        <title>人生第五季 Fifth Season Life | Zoom API TEST</title>
    <!-- -------------------------- CSS Link -------------------------- -->
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="./pluginFiles/fontawesome-free/css/all.css">
        <link href="./pluginFiles/font-gis/css/font-gis.css" rel="stylesheet" />
        <!-- iCheck for checkboxes and radio inputs -->
        <link rel="stylesheet" href="./pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
        <!-- DataTables -->
        <link rel="stylesheet" href="./pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
        <link rel="stylesheet" href="./pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
        <link rel="stylesheet" href="./pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="./cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
        <!-- Custom JS style -->
        <link rel="stylesheet" href="./cssFiles/5sl.formaccess.css">
    <!-- -------------------------- /CSS Link -------------------------- -->

    </head>

    <body>
     <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Zoom 開課 API 測試</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form id="transform" name="transform" action="#" method="post">

                <div class="card-body">


                  <div class="form-group row">
                    <label for="md_title" class="col-sm-2 col-form-label">課程名稱</label>
                    <input class="col-sm-10 form-control" type="text" id="md_title" name="md_title" value="" placeholder="請輸入課程名稱">
                  </div>
                  <div class="form-group row">
                    <label for="md_type" class="col-sm-2 col-form-label">課程種類</label>
                    <select id="md_type" name="md_type" class="col-sm-10 form-control">
                        <option value="">請選擇課程種類</option>
                        <option value="1">即時課程</option>
                        <option value="2">排定課程</option>
                        <option value="3">重複課程(無固定時間)</option>
                        <option value="8">重複課程(固定時間)</option>
                    </select>
                  </div>
`                 <div class="form-group row">
                    <label for="md_min" class="col-sm-2 col-form-label">最低開課人數</label>
                    <input type="text" id="md_min" name="md_min" class="col-sm-4 form-control" value="" placeholder="請輸入最低開課人數">
                    <label for="md_max" class="col-sm-2 col-form-label">學員人數上限</label>
                    <input type="text" id="md_max" name="md_max" class="col-sm-4 form-control" value="" placeholder="請輸入學員人數上限">
                  </div>
`                 <div class="form-group row">
                    <label for="md_starttime" class="col-sm-2 col-form-label">預計開課時間</label>
                    <input type="text" id="md_starttime" name="md_starttime" class="col-sm-4 form-control" value="" placeholder="請輸入預計開課時間">
                    <label for="md_duration" class="col-sm-2 col-form-label">課程時長</label>
                    <input type="text" id="md_duration" name="md_duration" class="col-sm-4 form-control" value="" placeholder="請輸入課程時長">
                  </div>
`                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                  </div>


                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <input type="hidden" id="pass" name="pass" value="pass" style="width:400px;height:24px;">
                    <button type="button" onClick="window.location.replace('zoomAPItest.php');" style="width:60px;height:40px;border-radius:5px;cursor:pointer;">重置</button>&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="submit" style="width:80px;height:40px;background-color:#cd5757;color:#FFFFFF;border-radius:5px;cursor:pointer;">執行</button>
                </div>
                
              </form>
            </div>
            <!-- /.card -->
          </div>
          <!--/.col (left) -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->       

<br>
<!-- <input type="text" id="transTo" name="transTo" value="<?php //echo $transTo; ?>" style="width:400px;height:24px;"> -->
<br>


<?php


if(!isset($_POST['pass'])){
    return false;
}





require_once 'classFiles/ZoomManager.class.php';
/*
Account ID：
aSV8ksGWQsmT3_0XcLTrng

Client ID：
6ufGliFST1WCtd7NbvZUGQ

Client Secret：
7CINeUoSvb6iLMhUJgUMFI4MBK36f4ul

Secret Token:
FTXxNhqQSu67dbOMKn7t0w




Scopes: meeting:write:admin,meeting:write
Scopes: report:read:admin
Scopes: recording:read,phone_recording:read:admin


*/


// ==========================================================
// 步驟一：動態向 Zoom 領取熱騰騰、數百碼長的真實 Access Token
// ==========================================================
// ⚠️ 請精準填入您的三組後台憑證，千萬不要對調位置
echo "<h2>🚀 學院系統：最終極 IPv4 全管道大通車面板</h2>";

// ⚠️ 請在此處填入您 Zoom 後台的真實憑證
$accountId    = 'aSV8ksGWQsmT3_0XcLTrng';
$clientId     = '6ufGliFST1WCtd7NbvZUGQ'; // 🎯 這是您的 Client ID，不是 Token 喔！
$clientSecret = '7CINeUoSvb6iLMhUJgUMFI4MBK36f4ul';


try {
    echo "<h2>--- 正在透過網域 https://ngrok-free.dev 進行 API 學院對接測試 ---</h2>";
    
    // 初始化系統（此步驟會自動觸發私有方法 getAccessToken 獲取 Token）
    $zoom = new ZoomUltimateCourseManager($clientId, $clientSecret, $accountId);
    echo "✅ [第一階段] 成功獲取 Zoom Access Token 認證！<br><br>";

    // 2. 測試方法 2：建立一個安全的預約課程（安全機制開啟）
    $courseTopic = "五季生活美學 - 線上茶藝精修班";
    $options = [
        'admin_user_id' => 'me', // 對應第二階段：'https://zoom.us'
        'start_time'    => '2026-11-02T19:00:00',
        'duration'      => 40,
        'timezone'      => 'Asia/Taipei'
    ];
    
    $newCourse = $zoom->createCourse($courseTopic, 2, $options, true);
    
    if (!isset($newCourse['id'])) {
        throw new Exception("開課失敗，未取得 Meeting ID。回應：" . json_encode($newCourse));
    }
    
    $meetingId = $newCourse['id'];
    echo "✅ [方法 2] 成功建立新課程教室！<br>";
    echo "・課程主題: " . $newCourse['topic'] . "<br>";
    echo "・Zoom 會議 ID: " . $meetingId . "<br>";
    echo "・等候室狀態: " . ($newCourse['settings']['waiting_room'] ? '已防禦開啟' : '關閉') . "<br><br>";

    // 3. 測試方法 4：為課程加入學員並透傳本地 student_id
    $studentEmail = "student123@5seasonlife.com";
    $firstName    = "小明";
    $lastName     = "張";
    $localId      = 9527; // 本地資料庫學員編號（會員編號 mb_id）
    
    $registration = $zoom->addStudentToCourseWithCustomId($meetingId, $studentEmail, $firstName, $lastName, $localId);
    
    if (isset($registration['join_url'])) {
        echo "✅ [方法 4] 學員白名單成功綁定（含自訂 custom_questions 透傳值）！<br>";
        echo "・學員姓名: " . $lastName . $firstName . " (本地ID: " . $localId . ")<br>";
        echo "・學員專屬無密碼上課連結: <a href='{$registration['join_url']}' target='_blank'>點擊進入教室</a><br><br>";
    }

    // 4. 課後數據報表撈取示範 (方法 5 ~ 8)
    echo "<h3>--- 課後實時數據撈取演練 ---</h3>";
    
    // 方法 5：出席與聽課時長
    $attendance = $zoom->getAttendanceReport($meetingId);
    echo "📊 [方法 5] 已撈取出席名單，目前實時報到人數: " . (isset($attendance['participants']) ? count($attendance['participants']) : 0) . " 人。<br>";
    
    // 方法 6：問答數據
    $qaReport = $zoom->getQAReport($meetingId);
    echo "📊 [方法 6] 課堂問答數據撈取完成。<br>";
    
    // 方法 7：投票結果
    $pollReport = $zoom->getPollReport($meetingId);
    echo "📊 [方法 7] 課堂投票數據撈取完成。<br>";
    
    // 方法 8：雲端錄影與白板
    $recordings = $zoom->getCloudRecordings($meetingId);
    echo "📊 [方法 8] 雲端錄影檔案與 Whiteboard 板書快照網址擷取完成。<br>";

} catch (Exception $e) {
    // 完美的異常捕獲（包含 VPN 斷訊警報提示）
    echo "<div style='padding:15px; background-color:#fff0f0; border-left:5px solid #ff0000; color:#aa0000;'>";
    echo "<strong>運作期間發生錯誤：</strong><br>" . nl2br(htmlspecialchars($e->getMessage()));
    echo "</div>";
}

?>
      <!-- jQuery -->
      <script src="../pluginFiles/jquery/jquery.min.js"></script>
      <!-- ------------------------ Scripts Link ------------------------ -->
      <!-- jQuery is in front of footer file. -->
      <!-- 5SeasonLife App -->
      <script src="../scriptFiles/5sladmin.js<?php echo '?t='.time(); ?>"></script>
      <!-- ------------------------ /Scripts Link ------------------------ -->

    </body>
</html>



