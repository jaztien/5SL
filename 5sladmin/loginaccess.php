<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
session_start();
if(isset($_POST['token'])){
    require_once('./includeFiles/common.lib.php');
    //$token = _fn_FHMD5_encrypt(strtolower($_SERVER['HTTP_USER_AGENT']).'PHPSESSID='.session_id());
    $token = _fn_FHMD5_encrypt(strtolower($_SERVER['HTTP_USER_AGENT']).'PHPSESSID='.session_id());
    if($token == $_POST['token']){
        session_regenerate_id(TRUE);
        //更新註冊過後的 TOKEN ，降低被盜取的機率
        $_SESSION['token'] = _fn_FHMD5_encrypt(strtolower($_SERVER['HTTP_USER_AGENT']).'PHPSESSID='.session_id());
        if(($_POST['loginUser']&&$_POST['loginUser']!='')&&($_POST['loginPass']&&$_POST['loginPass']!='')){
            //---------------- get session data here ---------------//
            define('DOC_PATH_LAYER', './');
            require_once('./includeFiles/db.inc.php');
            //require('funcFiles/mssql.func.php');
            $r = $DB->row("SELECT * FROM fsl_administrator WHERE ad_account=?",array($_POST['loginUser']));
            if($r && password_verify($_POST['loginPass'], $r['ad_password'])){//** 通過登入條件，取出資料寫入 SESSION **//
                $getData=$r;
                if(intval($getData['ad_status'])==1){
                    $_SESSION['SESS_AD_ID']        = intval($getData['ad_id']);
                    $_SESSION['SESS_AD_NAME']      = $getData['ad_name'];
                    $_SESSION['SESS_AD_NICK']      = $getData['ad_title'];
                    $_SESSION['SESS_AD_AUTH']      = intval($getData['ad_authlevel']);
                    $_SESSION['SESS_AD_EMAIL']     = $getData['ad_email'];
                    $_SESSION['SESS_AD_PHONE']     = $getData['ad_phone'];
                    $_SESSION['SESS_AD_LINE']      = $getData['ad_line'];
                    $_SESSION['SESS_AD_STATUS']    = intval($getData['ad_status']);
                //**操作者縮圖**//
                    if(file_exists( './uploadFiles/ad/adphoto_'.$_SESSION['SESS_AD_ID'].'_thumb.jpg')){
                        $_SESSION['SESS_AD_THUMB']     = './uploadFiles/ad/adphoto_'.$_SESSION['SESS_AD_ID'].'_thumb.jpg';
                    } else {
                        $_SESSION['SESS_AD_THUMB']     = 'imageFiles/nopic_user_32x32.jpg';
                    }
                    if(file_exists( './uploadFiles/ad/adphoto_'.$_SESSION['SESS_AD_ID'].'_.jpg')){
                        $_SESSION['SESS_AD_PHOTO']     = './uploadFiles/ad/adphoto_'.$_SESSION['SESS_AD_ID'].'_.jpg';
                    } else {
                        $_SESSION['SESS_AD_PHOTO']     = 'imageFiles/nopic_user_256x256.jpg';
                    }
                    //** 操作權限陣列生成 **//
                    $getAuthArray = $DB->getAuthArray($_SESSION['SESS_AD_ID']);
                    $_SESSION['SESS_PAGE_AUTH']     = $getAuthArray;
                    //** 寫入最近登入時間 **//
                    $doOnboard = $DB->update('fsl_administrator',array('ad_onboard'=>'[EXPR]NOW()'),array('ad_id'=>$_SESSION['SESS_AD_ID']));
                    //** 寫入操作記錄 **//
                    $ilog = $DB->insSysLogs(51, $_SESSION['SESS_AD_ID'], 2001, '["'.$_SESSION['SESS_AD_ID'].'","'.$_SESSION['SESS_AD_NICK'].'"]');//寫入操作記錄
                    //** 中斷資料庫連接 **//
                    //
                    //** 進入管理首頁 **//
                    header("location: ./pageFiles/5sladmin_iframe.php");
                } else {//** 管理者已遭停權！ **//
                    $ilog = $DB->insSysLogs(51, $_SESSION['SESS_AD_ID'], 2000, '["'.$_SESSION['SESS_AD_ID'].'","'.$_SESSION['SESS_AD_NICK'].'"]');//寫入操作記錄
                    session_destroy();
                    header("location: ./5sladmin.php?e=5");
                }
            } else { //** 帳號密碼比對無相符條件，取出資料失敗！ **//
                session_destroy();
                header("location: ./5sladmin.php?e=4");
            }
            //----------------------------------------------------//
            $DB->closeConnection();
        } else {//** 帳號密碼傳送錯誤！ **//
            session_destroy();
            header("location: ./5sladmin.php?e=3");
        }
    } else {//** TOKEN比對不符！ **//
        session_destroy();
        header("location: ./5sladmin.php?e=2");
    }
} else {//** 沒有必要的TOKEN資料傳送！ **//
    session_destroy();
    header("location: ./5sladmin.php?e=1");
}
?>
