<?php 


//依代碼取得設定值 _FUN_GET_CONFIG(代碼)
function _FUN_GET_CONFIG(){
    //$DB = new DB(DBHost, DBPort, DBName, DBUser, DBPassword);
    //$res = $DB->row("SELECT config_crypt, config_value FROM fh.configurations WHERE config_code=? ",array($codE));
    //$DB->closeConnection();
    //return $_SESSION['SESS_PAGE_AUTH']['PG_ADHOME']['delete'];
}
/*
function _FUN_LATEST_SN($tableNAME='',$snFIELD='',$preFIX='',$datE='',$groupFIELD='',$grouP=''){
	if (!empty($datE)) $IDnum_Head=date("Ymd",$datE);
	else $IDnum_Head=date("Ymd",CONFIG_NOWTIME);
	$CLS = new cSQLquery('table_data_spform',"SUBSTRING(`".$snFIELD."`,-11,8)='".$IDnum_Head."'".(!empty($groupFIELD)&&!empty($grouP) ? " AND `".$groupFIELD."`='".$grouP."'" : ""));
	$row=$CLS -> _GET_ROW($snFIELD,$snFIELD,'DESC',1);
	if ($row) $TD_foot=sprintf("%03d", intval(substr($row[$snFIELD],-3))+1);
	else $TD_foot="001";
	$FullIDnum=$preFIX.trim($IDnum_Head).trim($TD_foot);
	return $FullIDnum;
}

*/

?>