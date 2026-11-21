<?php
/**------------------------------------------------------------------------**/
/**-------------------------- 頁面基本功能檔案 -------------------------------**/
/**------------------- 此檔案必須放置於 PAGE_MARK 定義之後 --------------------**/
/**------------------------------------------------------------------------**/
//定義必要數值
define('currID',isset($_REQUEST['currID']) ? intval($_REQUEST['currID']) : 0);
if (!defined('PAGE_OPANEL')) {
    define('PAGE_OPANEL', currID > 0 ? 4 : 5);
}
if (!defined('SEARCH_OPANEL')) {
    define('SEARCH_OPANEL', 0);
}
if (!defined('EXPORT_OPANEL')) {
    define('EXPORT_OPANEL', 0);
}
if (!defined('bkRedir')) {
    define('bkRedir', 0);
}
//define('bkRedir',isset($_REQUEST['bkRedir']) ? base64_decode($_REQUEST['bkRedir']) : '');
// ----------------- 頁面操作權限處理 ---------------- //
//取得登入時的授權（依當下頁面的授權條件）。
$getPageAuth=@$_SESSION['SESS_PAGE_AUTH'][PAGE_MARK];
if(defined('PAGE_TOPAUTH') && $_SESSION['SESS_AD_AUTH'] >= 6){
    $getPageAuth['browse'] = 1;
    $getPageAuth['addnew'] = 1;
    $getPageAuth['edit'] = 1;
    $getPageAuth['delete'] = $_SESSION['SESS_AD_AUTH']==9 ? 1 : 0;
    $topEditAuth = true;
}
//當在管理者本人的資料頁面時，若有「本人通關」註記時，忽略系統設定的權限，重新賦予「瀏覽」以及「編輯」權限，「新增」以及「刪除」權限如常。
if(defined('PAGE_ADPASS') && $_SESSION['SESS_AD_ID'] == currID && $getPageAuth['edit'] == 0){
    $getPageAuth['browse'] = 1;
    $getPageAuth['edit'] = 1;
}
//當沒有最高管理權以及「管理者本人」時，若無瀏覽權限時，系統強制退回前一頁。
if(!isset($getPageAuth['browse']) || $getPageAuth['browse'] == 0){
    echo "<script>";
    echo "alert('您沒有這個頁面的瀏覽授權，請停止操作或聯絡管理人員提高這個頁面的操作權限！！');";
    echo "window.location.replace('5sladmin_startpage.html');";
    echo "</script>";
    exit();
}
//------------------ 最終的權限定義 --------------------//
define('auBROWSE',       $getPageAuth['browse']?:0);
define('auADDNEW',       $getPageAuth['addnew']?:0);
define('auEDIT',         $getPageAuth['edit']?:0);
define('auDELETE',       $getPageAuth['delete']?:0);

echo "<script>";

echo "var authPanelELM = window.parent.$('.nav-item.nav-authpanel');";
echo "var topPanelELM = window.parent.$('.nav-item.nav-toppanel');";
echo "var topSearchELM = window.parent.$('.nav-item.nav-topsearch');";
echo "var topExportELM = window.parent.$('.nav-item.nav-topexport');";
echo "var topBKredirELM = window.parent.$('.nav-item.nav-topbkredir');";
//先關閉權限顯示框
echo "authPanelELM.find('i').removeClass('active');";
//先關閉操作按鈕框
echo "topPanelELM.find('i').removeClass('active').hide();";
//先關閉搜尋按鈕框
echo "topSearchELM.find('i').removeClass('active').hide();";
//先關閉匯出按鈕框
echo "topExportELM.find('i').removeClass('active').hide();";
//先關閉返回按鈕框
echo "topBKredirELM.find('i').removeClass('active').hide();";
//打開權限顯示框
echo "authPanelELM.show();";
//當權限顯示框有內容時，依據四大權限授權與否打開權限顯示圖標
echo "if(authPanelELM.length>0){";
    if (auBROWSE > 0) { echo "authPanelELM.find('i#topAuth_browser').addClass('active');"; }
    if (auADDNEW > 0) { echo "authPanelELM.find('i#topAuth_addnew').addClass('active');"; }
    if (auEDIT > 0) { echo "authPanelELM.find('i#topAuth_edit').addClass('active');"; }
    if (auDELETE > 0) { echo "authPanelELM.find('i#topAuth_delete').addClass('active');"; }
echo "}";

//當內框網頁中定義的 PAGE_OPANEL 大於 0 時，且至少有一個權限被授權時，開啟操作按鈕框
if(PAGE_OPANEL>0 && (auBROWSE > 0 || auADDNEW > 0 || auEDIT > 0 || auDELETE > 0)){
    echo "topPanelELM.show();";
    //當操作按鈕框有內容時，依據操作授權與否開啟(顯示)操作按鈕
    echo "if(topPanelELM.length>0){";
    //if(auADDNEW==1 || auEDIT==1 || auEDIT==1){ 
        if(PAGE_OPANEL==1){//PAGE_OPANEL為1時，顯示新增按鈕
            echo "topPanelELM.find('i#topBut_add').show().addClass('active');";
        } else if(PAGE_OPANEL==2){//PAGE_OPANEL為2時，顯示儲存按鈕
            echo "topPanelELM.find('i#topBut_save').show().removeClass('active');";
        } else if(PAGE_OPANEL==3){//PAGE_OPANEL為3時，顯示上傳以及刪除按鈕
            echo "topPanelELM.find('i#topBut_upload').show().addClass('active');";
            echo "topPanelELM.find('i#topBut_delete').show().addClass('active');";
        } else if(PAGE_OPANEL==4){//PAGE_OPANEL為4時，顯示儲存以及刪除按鈕
            echo "topPanelELM.find('i#topBut_save').show().removeClass('active');";
            echo "topPanelELM.find('i#topBut_delete').show().addClass('active');";
        } else if(PAGE_OPANEL==5){//PAGE_OPANEL為5時，顯示儲存按鈕
            echo "topPanelELM.find('i#topBut_save').show().removeClass('active');";
        }
        //無論 PAGE_OPANEL為何，皆同時顯示重置按鈕
        echo "topPanelELM.find('i#topBut_reset').show().addClass('active');";
    //}
    //if(pcSave>=2 && auDELETE==1){
    //    echo "topPanelELM.find('i#topBut_delete').show().addClass('active');";
    //}
    echo "}";
}
//當內框網頁中定義的 PAGE_OPANEL 為 0 時，主頁框上方的權限顯示以及操作按鈕框都隱藏關閉
else {
    echo "topPanelELM.find('i.active').removeClass('active').hide();";
    echo "topPanelELM.hide();";
}
//當內框網頁中定義的 SEARCH_OPANEL 大於 0 時，開啟搜尋按鈕框
if(SEARCH_OPANEL>0){
    echo "topSearchELM.show();";
    if(SEARCH_OPANEL==1){//當內框網頁中定義的 SEARCH_OPANEL 為 1 時隱藏搜尋按鈕
        echo "topSearchELM.find('i#topBut_search').find('span').hide();";
    } else {//當內框網頁中定義的 SEARCH_OPANEL 不為 1 時顯示搜尋按鈕
        echo "topSearchELM.find('i#topBut_search').find('span').show();";
    }
    echo "topSearchELM.find('i#topBut_search').show().addClass('active');";
}
//當內框網頁中定義的 SEARCH_OPANEL 為 0 時，搜尋按鈕框隱藏
else {
    echo "if(topSearchELM.length>0){";
    echo "topSearchELM.find('i#topBut_search').hide().removeClass('active');";
    echo "}";
    echo "topSearchELM.hide();";
}
//當內框網頁中定義的 EXPORT_OPANEL 大於 0 時，顯示匯出按鈕框
if(EXPORT_OPANEL>0){
    echo "topExportELM.show();";
    if(EXPORT_OPANEL==1){//當內框網頁中定義的 EXPORT_OPANEL 為 1 時，顯示匯出EXCEL以及隱藏顯示器列印按鈕
        echo "topExportELM.find('i#topBut_excel').show().addClass('active');";
        echo "topExportELM.find('i#topBut_print').hide().removeClass('active');";
    } else if(EXPORT_OPANEL==2){//當內框網頁中定義的 EXPORT_OPANEL 為 2 時，隱藏匯出EXCEL以及顯示顯示器列印按鈕
        echo "topExportELM.find('i#topBut_print').show().addClass('active');";
        echo "topExportELM.find('i#topBut_excel').hide().removeClass('active');";
    } else {//當內框網頁中定義的 EXPORT_OPANEL 大於 2 時，顯示匯出EXCEL以及顯示顯示器列印按鈕
        echo "topExportELM.find('i#topBut_excel').show().addClass('active');";
        echo "topExportELM.find('i#topBut_print').show().addClass('active');";
    }
}
//當內框網頁中定義的 EXPORT_OPANEL 為 0 時，匯出按鈕框隱藏
else {
    echo "if(topExportELM.length>0){";
    echo "topExportELM.find('i#topBut_excel').hide().removeClass('active');";
    echo "topExportELM.find('i#topBut_print').hide().removeClass('active');";
    echo "}";
    echo "topExportELM.hide();";
}
//當內框網頁中定義的 bkRedir 大於 0 時，顯示返回按鈕框
if(bkRedir > 0){
    echo "topBKredirELM.show();";
    echo "if(topBKredirELM.length>0){";
    echo "topBKredirELM.find('i#topBut_bkurl').show().addClass('active');";
    echo "}";
 }
 //當內框網頁中定義的 bkRedir 為 0 時，返回按鈕框隱藏
 else {
    echo "if(topBKredirELM.length>0){";
    echo "topBKredirELM.find('i#topBut_bkurl').hide().removeClass('active');";
    echo "}";
    echo "topBKredirELM.hide();";
}
echo "</script>";

///////////////////////////////////////////////////////////////////////////
//頁面讀寫權限佈局 //auBROWSE,auADDNEW,auEDIT,auDELETE
//依據四大權限結構，設定表單中元件的讀寫權限
define('NreadOnly',      ((auADDNEW==0 && currID==0) || (auEDIT==0 && currID>0)) ? " readonly" : "");//一般管理權
define('NdisAble',       ((auADDNEW==0 && currID==0) || (auEDIT==0 && currID>0)) ? " disabled" : "");//一般管理權
//若是當下頁面有最高授權註記時，忽略系統設定的權限，重新賦予新的且完全的權限。
//$topEditAuth = false;
if(defined('FORMEDIT_TOPAUTH') && $_SESSION['SESS_AD_AUTH'] >= 6){
    define('TreadOnly',      "");//最高管理權
    define('TdisAble',       "");//最高管理權
} else {
    define('TreadOnly',      " readonly");//最高管理權
    define('TdisAble',       " disabled");//最高管理權
}
//---------------- 取得並定義上一頁網頁檔案名稱 ---------------------//
/*
if (isset($_SERVER['HTTP_REFERER'])) {
    $referrer_url = $_SERVER['HTTP_REFERER']; // 例如: http://example.com
    // 2. 擷取網址中的路徑部分 (移除 http、網域及參數)
    $path = parse_url($referrer_url, PHP_URL_PATH); // 得到: /folder/index.php 
    // 3. 取得最後的檔案名稱
    $prev_filename = basename($path); // 得到: index.php
    $prev_pagepath = pathinfo($prev_filename, PATHINFO_FILENAME);// 得到: index
    define('prevPAGE', $prev_filename);
    define('prevCleanPAGE', $prev_pagepath);
} else {
    define('prevPAGE', '');
    define('prevCleanPAGE', '');
}
    */
///////////////////////////////////////////////////////////////////////////

/*
if(!empty($_GET['msg'])&&intval($_GET['msg'])!=0){
    switch (intval($_GET['msg'])) {
        case 1:
            $type="success";
            $message="這是測試1";
            break;
        case 2:
            $type="info";
            $message="這是測試2";
            break;
        case 3:
            $type="warning";
            $message="這是測試3";
            break;
        case 4:
            $type="error";
            $message="這是測試4";
            break;
        case 5:
            $type="success";
            $message="成功刪除管理者".(!empty($_GET['doneid']) ? '【ID: '.$_GET['doneid'].'】':'')."資料！！";
            break;
        default:
            $type="error";
            $message="這是測試Others";
    }
    define('msgTYPE', $type);
    define('msgMSG', $message);    
} else {
    define('msgTYPE', '');
    define('msgMSG', '');    
}
*/
?>