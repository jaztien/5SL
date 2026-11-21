<?php
//require(__DIR__ . '/pdoSQL.Log.class.php');
//require(__DIR__ . '/pdoSQL.Iterator.class.php');
/** Class DB
 * @property PDO pdo PDO object
 * @property PDOStatement sQuery PDOStatement
 * @property PDOLog PDOLog logObject
 */
class DB
{
	private $Host;
	private $DBPort;
	private $DBName;
	private $DBUser;
	private $DBPassword;
	private $pdo;
	private $sQuery;
	private $connectionStatus = false;
	private $logObject;
	private $parameters;
	public $rowCount   = 0;
	public $columnCount   = 0;
	public $querycount = 0;
    // 在 DB 類別內部新增這兩個屬性與方法 for SecurityGuard
    private $securityGuardInstance = null;

	private $retryAttempt = 0; // 失败重试次数
	const AUTO_RECONNECT = true;
	const RETRY_ATTEMPTS = 3; // 最大失败重试次数

    /**
     * DB constructor.
     * @param $Host
     * @param $DBPort
     * @param $DBName
     * @param $DBUser
     * @param $DBPassword
     */
	public function __construct($Host, $DBPort, $DBName, $DBUser, $DBPassword)
	{
		//$this->logObject  = new PDOLog();
		$this->Host       = $Host;
		$this->DBPort     = $DBPort;
		$this->DBName     = $DBName;
		$this->DBUser     = $DBUser;
		$this->DBPassword = $DBPassword;
		$this->parameters = array();
		$this->Connect();
	}

	private function Connect()
	{
		try {
			// 修正為 MySQL 專用 DSN 結構，並指定 utf8mb4 編碼
			$dsn = "mysql:host=" . $this->Host . ";port=" . $this->DBPort . ";dbname=" . $this->DBName . ";charset=utf8mb4";
			
			$this->pdo = new PDO($dsn,
				$this->DBUser,
				$this->DBPassword,
				array(
					PDO::ATTR_EMULATE_PREPARES => false, // 禁用模擬預處理，嚴格防禦注入
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
				)
			);
			$this->connectionStatus = true;
		}
		catch (PDOException $e) {
			$this->ExceptionLog($e, '', 'Connect');
		}
	}
	private function SetFailureFlag()
	{
		$this->pdo = null;
		$this->connectionStatus = false;
	}
	public function closeConnection()
	{
		$this->pdo = null;
	}
	private function Init($query, $parameters = null, $driverOptions = array())
	{
		if (!$this->connectionStatus) {
			$this->Connect();
		}
		try {
			$this->parameters = $parameters;
			$this->sQuery     = $this->pdo->prepare($this->BuildParams($query, $this->parameters), $driverOptions);

			if (!empty($this->parameters)) {
				if (array_key_exists(0, $parameters)) {
					$parametersType = true;
					array_unshift($this->parameters, "");
					unset($this->parameters[0]);
				} else {
					$parametersType = false;
				}
				foreach ($this->parameters as $column => $value) {
					// 規範 1：具名佔位符安全清洗，嚴禁添加或帶有冒號（:）
					$cleanColumn = is_string($column) ? ltrim($column, ':') : $column;
					
					$this->sQuery->bindParam($parametersType ? intval($cleanColumn) : ":" . $cleanColumn, $this->parameters[$column]);
				}
			}

			if (!isset($driverOptions[PDO::ATTR_CURSOR])) {
                $this->sQuery->execute();
            }
			$this->querycount++;
		}
		catch (PDOException $e) {
			$this->ExceptionLog($e, $this->BuildParams($query), 'Init', array('query' => $query, 'parameters' => $parameters));
			throw $e;
		}

		$this->parameters = array();
	}
	private function BuildParams($query, $params = null)
	{
		if (!empty($params)) {
			$array_parameter_found = false;
			foreach ($params as $parameter_key => $parameter) {
				if (is_array($parameter)){
					$array_parameter_found = true;
					$in = "";
					foreach ($parameter as $key => $value){
						$name_placeholder = $parameter_key."_".$key;
                        $in .= ":".$name_placeholder.", ";
						$params[$name_placeholder] = $value;
					}
					$in = rtrim($in, ", ");
					$query = preg_replace("/:".$parameter_key."/", $in, $query);
					unset($params[$parameter_key]);
				}
			}
			if ($array_parameter_found) $this->parameters = $params;
		}
		return $query;
	}
	public function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}
	public function commit()
	{
		return $this->pdo->commit();
	}
	public function rollBack()
	{
		return $this->pdo->rollBack();
	}
	public function inTransaction()
	{
		return $this->pdo->inTransaction();
	}
	public function query($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
	{
		$query        = trim($query);
		$rawStatement = preg_split("/( |\r|\n)/", $query);
		$this->Init($query, $params);
		if (!$this->sQuery) {
			return false;
		}
		$statement = strtolower($rawStatement[0]);
		if ($statement === 'select' || $statement === 'with' || $statement === 'show' || $statement === 'call' || $statement === 'describe') {
			return $this->sQuery->fetchAll($fetchMode);
		} 
		else {
			return $this->sQuery->rowCount();
		}
	}
    public function iterator($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
    {
        $query        = trim($query);
        $rawStatement = preg_split("/( |\r|\n)/", $query);
        $this->Init($query, $params, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        
        if (!$this->sQuery) {
            return false;
        }

        $statement = strtolower(trim($rawStatement[0]));
        if ($statement === 'select' || $statement === 'show' || $statement === 'call' || $statement === 'describe') {
            return new PDOIterator($this->sQuery, $fetchMode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }
    public function insert($tableName, $params = array())
    {
        if (!empty($params)) {
            $fields = array();
            $placeholders = array(); 
            $insertPara = array();   
            foreach ($params as $key => $value) {
                // 規範 5：動態欄位安全防禦，MySQL 使用反單引號包覆欄位名
                $safeKey = "`" . str_replace("`", "``", $key) . "`";
                $fields[] = $safeKey;
                
                if (is_string($value) && strpos($value, '[EXPR]') === 0) {
                    $expression = substr($value, 6); 
                    $placeholders[] = $expression;   
                } else {
                    $placeholders[] = '?';
                    $insertPara[] = $value;
                }
            }
            $fieldStr = implode(',', $fields);
            $placeholderStr = implode(',', $placeholders);
            
            // 資料表名稱同步增加反單引號安全保護
            $dbQuery = "INSERT INTO `{$tableName}` ({$fieldStr}) VALUES ({$placeholderStr})";
            return $this->query($dbQuery, $insertPara);
        }
        return false;
    }
    public function insertMulti($tableName, $params = array())
    {
        $rowCount = 0;
        if (!empty($params)) {
            $insParaStr = '';
            $insValueArray = array();
            $firstRow = current($params);
            
            // 規範 5：批次寫入欄位名稱加反單引號保護
            $safeCols = array_map(function($col) {
                return "`" . str_replace("`", "``", $col) . "`";
            }, array_keys($firstRow));
            $insColStr = implode(',', $safeCols);
            
            foreach ($params as $addRow) {
                $insParaStr .= '(' . implode(",", array_fill(0, count($addRow), "?")) . '),';
                $insValueArray = array_merge($insValueArray, array_values($addRow));
            }
            $insParaStr = rtrim($insParaStr, ',');
            
            $dbQuery = "INSERT INTO `{$tableName}` ($insColStr) VALUES $insParaStr";
            $rowCount = $this->query($dbQuery, $insValueArray);
        }
        return (bool) ($rowCount > 0);
    }
    public function update($tableName, $params = array(), $where = array())
    {
        $rowCount = 0;
        if (!empty($params)) {
            $updColStr = '';
            $whereStr = '';
            $setPara = array();
            $wherePara = array();
            foreach ($params as $key => $value) {
                // 規範 5：動態欄位防禦，MySQL 使用反單引號包覆
                $safeKey = "`" . str_replace("`", "``", $key) . "`";
                
                if (is_string($value) && strpos($value, '[EXPR]') === 0) {
                    $expression = substr($value, 6); 
                    $updColStr .= "{$safeKey}={$expression},"; 
                } else {
                    $updColStr .= "{$safeKey}=?,";
                    $setPara[] = $value; 
                }
            }
            $updColStr = rtrim($updColStr, ',');
            $dbQuery = "UPDATE `{$tableName}` SET {$updColStr}";
            
            if (!empty($where) && is_array($where)) {
                foreach ($where as $key => $value) {
                    $safeWhereKey = "`" . str_replace("`", "``", $key) . "`";
                    $whereStr .= " AND {$safeWhereKey}=?";
                    $wherePara[] = $value; 
                }
                $dbQuery .= " WHERE 1=1{$whereStr}";
            }
            // 規範 1 雙軌優化：嚴格遵循順序，完美合併問號參數陣列，徹底消除 HY093 錯誤
            $finalParams = array_merge($setPara, $wherePara);
            $rowCount = $this->query($dbQuery, $finalParams);
        }
        return $rowCount;
    }
	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}
	public function column($query, $params = null)
	{
		$this->Init($query, $params);
		if (!$this->sQuery) {
			return array();
		}
		$resultColumn = $this->sQuery->fetchAll(PDO::FETCH_COLUMN);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultColumn;
	}
	public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
	{
		$this->Init($query, $params);
		if (!$this->sQuery) {
			return array();
		}
		$resultRow = $this->sQuery->fetch($fetchmode);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultRow ? $resultRow : array();
	}
	public function single($query, $params = null)
	{
		$this->Init($query, $params);
		if (!$this->sQuery) {
			return null;
		}
		return $this->sQuery->fetchColumn();
	}
	private function ExceptionLog(PDOException $e, $sql = "", $method = '', $parameters = array())
	{
		$message = $e->getMessage();
		$exception = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "You can find the error back in the log.";
		if (!empty($sql)) {
			$message .= "\r\nRaw SQL : " . $sql;
		}
		$this->logObject->write($message, $this->DBName . md5($this->DBPassword));
		// 修正：同步捕捉 MySQL 的斷線特徵：'server has gone away' 或 'Lost connection'
		if (
		self::AUTO_RECONNECT
		&& $this->retryAttempt < self::RETRY_ATTEMPTS
		&& (stripos($message, 'server has gone away') !== false || stripos($message, 'Lost connection') !== false)
		&& !empty($method)
		&& !$this->inTransaction()
		) {
			$this->SetFailureFlag();
			$this->retryAttempt ++;
			$this->logObject->write('Retry ' . $this->retryAttempt . ' times', $this->DBName . md5($this->DBPassword));
			call_user_func_array(array($this, $method), $parameters);
		} else {
		throw $e;
		}
	}




//--------------------------------------------------------------------//
//--------------------------    自定義功能    ---------------------------//
//--------------------------------------------------------------------//
	public function guard()
	{
		if (!class_exists('SecurityGuard')) {
			$guardFile = DIR . '/SecurityGuard.class.php';
			if (file_exists($guardFile)) {
				require_once $guardFile;
			} else {
				throw new Exception('找不到 SecurityGuard.class.php 檔案');
			}
		}
		if ($this->securityGuardInstance === null) {
			$this->securityGuardInstance = new SecurityGuard($this);
		}
		return $this->securityGuardInstance;
	}
	public function checkSecurity($eventType, $mbID, $thumbmark, $extraParams = array())
	{
	return $this->guard()->checkAction($eventType, $mbID, $thumbmark, $extraParams);
	}
	//-----  以代碼直接取得 Configurations 表中的值  -----//
	public function getConfigVal($codE='',$grouP='')
	{
		if(empty($codE) && empty($grouP)) { return false; }
		$rtnObj = array();
		$rtnObj['config_crypt']=0;
		$rtnObj['config_value']='';
		if(!empty($codE)){
			$res = $this->row("SELECT config_crypt, config_value FROM fsl_configurations WHERE config_code=? ",array($codE));
			if($res) {
				$rtnObj = $res;
			}
		}
		else if(!empty($grouP)){
			$res = $this->query("SELECT config_code, config_crypt, config_value FROM fsl_configurations WHERE config_group=? ",array($grouP));
			$i = 0;
			$rtnArr = array();
			while($i<count($res)){
				$rtnArr[$res[$i]['config_code']]['config_crypt']=$res[$i]['config_crypt'];
				$rtnArr[$res[$i]['config_code']]['config_value']=$res[$i]['config_value'];
				$i++;
			}
			$rtnObj = $rtnArr;
		}
		return $rtnObj;
	}
	//-----  以代碼/值直接更新 Configurations 表中的值  -----//
	public function setConfigVal($vaL,$codE = null)
	{
		if (empty($codE)) { return false; }
		$up = $this->query("UPDATE fsl_configurations SET config_value= ? WHERE config_code = ?", array($vaL,$codE));
		return $up;
	}
	//-----  以 ad_id 取得頁面操作權限 Array 陣列回傳  -----//
	public function getAuthArray($iD = null)
	{
		if (empty($iD)) { return false; }
		$ar = array();
		$admJS=_fn_getCachedJsonData("../5sladmin/jsonFiles/adminpage.json");
		$authpage = $admJS['sidebar'];
		$optARR = array();
		$i=0;
		while($i < count($authpage)){
			$authpage2 = isset($authpage[$i]['subgroup']) ? $authpage[$i]['subgroup'] : array();
			$si=0;
			while($si < count($authpage2)){
				$authpage3 = isset($authpage2[$si]['subgroup']) ? $authpage2[$si]['subgroup'] : array();
				if(count($authpage3) <= 0) { array_push($optARR, $authpage2[$si]); }
				$ti=0;
				while($ti < count($authpage3)){
					array_push($optARR, $authpage3[$ti]);
					$ti++;
				}
				$si++;
			}
			$i++;
		}
		$i=0;
		while($i<count($optARR)){
			$idX=$optARR[$i]['pagecode'];
			$gF=$optARR[$i]['default'];
			$resi = $this->row("SELECT * FROM fsl_admin_auth WHERE ad_id=? AND ap_pagecode=?",array($iD, $idX));
			if($resi){
				$ari = array($idX => array(
					"browse" => intval($resi['auth_browse']),
					"addnew"=>intval($resi['auth_addnew']),
					"edit"=>intval($resi['auth_edit']),
					"delete"=>intval($resi['auth_delete'])
				));
			} else {
				$ari = array($idX => array(
					"browse" => intval(substr($gF,0,1)),
					"addnew"=>intval(substr($gF,1,1)),
					"edit"=>intval(substr($gF,2,1)),
					"delete"=>intval(substr($gF,3,1))
				));
			}
			$ar = array_merge($ar, $ari);
			$i++;
		}
		return $ar;
	}
	/*-----  寫入操作記錄 true/false 回傳  ----------------**/
	public function insSysLogs($for=0, $forid=0, $logCode='', $logParams='')
	{
		if(!empty($logCode) && !empty($logParams)){
			$inArr = array(
			'lg_created' => '[EXPR]NOW()',
			'lg_for' => $for,
			'lg_forid' => $forid,
			'lg_code' => $logCode,
			'lg_params' => $logParams
			);
			$ins = $this->insert("data_logs", $inArr);
			if($ins) return true;
			else return false;
		} else {
			return false;
		}
	}
	/*-----  檢查重複 true/false 回傳  ----------------**/
	public function duplicateCheck($tableName, $params = null)
	{
		$count = 0;
		if (!empty($params)) {
			$selectParts = [];
			$whereParts = [];
			foreach ($params as $key => $value) {
				$safeKey = "" . str_replace("", "``", $key) . "`";
				$selectParts[] = $safeKey;
				$whereParts[] = "{$safeKey}=?";
			}
			$selectStr = implode(',', $selectParts);
			$whereStr = implode(' AND ', $whereParts);
			$dbQuery = "SELECT {$selectStr} FROM {$tableName} WHERE {$whereStr}";
			$wherePara = array_values($params);
			$res = $this->query($dbQuery, $wherePara);
			$count = is_array($res) ? count($res) : 0;
		}
		return $count;
	}
	/*-----  上線/延續上線狀態  -------------------------------**/
	public function _DB_online_active($mbID = 0)
	{
		$this->query("DELETE FROM mb_online WHERE mbol_actiontime <= (NOW() - INTERVAL 300 SECOND)");
		if (empty($mbID)) { return false; }
		$activeup = $this->query("UPDATE mb_online SET mbol_actiontime=NOW(), mbol_chattime=?, mbol_onchat=? WHERE mb_id=?", array(null, 0, $mbID));
		if($activeup){
			return true;
		} else {
			$activins = $this->insert("mb_online", array('mb_id' => $mbID, 'mbol_actiontime' => '[EXPR]NOW()'));
			if($activins){
				return true;
			} else {
				return false;
			}
		}
	}
	/*-----  聊天狀態更新功能  -------------------------------**/
	public function _DB_onchat_active($mbID = 0)
	{
		$this->query("DELETE FROM mb_online WHERE mbol_actiontime <= (NOW() - INTERVAL 300 SECOND)");
		if (empty($mbID)) { return false; }
		$activeup = $this->query("UPDATE mb_online SET mbol_actiontime=NOW(), mbol_chattime=NOW(), mbol_onchat=? WHERE mb_id=?", array(1, $mbID));
		if($activeup){
			return true;
		} else {
			return false;
		}
	}
	/*-----  取得郵件模板內容  ----------------**/
	public function getEmailTemplate($codE = null)
	{
		if (empty($codE)) { return false; }
		$res = $this->row("SELECT arti_subscript, arti_body FROM articles WHERE arti_keycode=? AND arti_status=?", array($codE, 1));
		if($res){
			return array(
				'subject' => $res['arti_subscript'],
				'content' => $res['arti_body']
			);
		} else {
			return false;
		}
	}
	/*-----  發送電子郵件 ---------------------------------**/
	public function doSendEmail($codE='', $markerARR=[], $replaceARR=[], $subJect='', $htmlBody='', $setTo=[], $setCC=[], $setBCC=[])
	{
		if(empty($codE) && (empty($subJect) || empty($htmlBody))){
			return false;
		}
		if(!empty($codE)){
			$getTemplate = $this->getEmailTemplate($codE);
			if($getTemplate){
				$subJect = $getTemplate['subject'];
				$htmlBody = $getTemplate['content'];
			}
		}
		if(!empty($codE) && (empty($subJect) || empty($htmlBody))){
			return false;
		}
		$cDateTime = date('Y-m-d H:i:s');
		$getMailInfo = $this->getConfigVal('','mailserver');
		if (!is_array($getMailInfo)) { $getMailInfo = array(); }
		if(empty($setTo)){
			if(!empty($getMailInfo['mail_notifyadd']['config_value'])) {
				$setTo = [[$getMailInfo['mail_notifyadd']['config_value'], $getMailInfo['mail_notifytItle']['config_value']]];
			}
			if(!empty($getMailInfo['mail_cc1add']['config_value'])) {
				$setCC[] = [$getMailInfo['mail_cc1add']['config_value'], $getMailInfo['mail_cc1title']['config_value']];
				$setCC[] = [$getMailInfo['mail_cc2add']['config_value'], $getMailInfo['mail_cc2title']['config_value']];
			}
			if(!empty($getMailInfo['mail_bcc1add']['config_value'])) {
				$setBCC[] = [$getMailInfo['mail_bcc1add']['config_value'], $getMailInfo['mail_bcc1title']['config_value']];
				$setBCC[] = [$getMailInfo['mail_bcc2add']['config_value'], $getMailInfo['mail_bcc2title']['config_value']];
			}
		}
		if(empty($setTo)){
			return false;
		}
		$setHost = isset($getMailInfo['smtp_host']['config_value']) ? $getMailInfo['smtp_host']['config_value'] : '';
		$setPort = isset($getMailInfo['smtp_tlsport']['config_value']) ? $getMailInfo['smtp_tlsport']['config_value'] : '';
		if(isset($getMailInfo['smtp_smode']['config_value']) && $getMailInfo['smtp_smode']['config_value']=='SSL'){
			$setPort = isset($getMailInfo['smtp_sslport']['config_value']) ? $getMailInfo['smtp_sslport']['config_value'] : '';
		}
		$setFrom = isset($getMailInfo['mail_fromadd']['config_value']) ? $getMailInfo['mail_fromadd']['config_value'] : '';
		$setFromTitle = isset($getMailInfo['mail_fromtitle']['config_value']) ? $getMailInfo['mail_fromtitle']['config_value'] : '';
		$setUsername = isset($getMailInfo['smtp_accuont']['config_value']) ? $getMailInfo['smtp_accuont']['config_value'] : '';
		$setPassword = isset($getMailInfo['smtp_pass']['config_value']) ? $getMailInfo['smtp_pass']['config_value'] : '';
		$htmlBody = stripslashes($htmlBody);
		$subJect = stripslashes($subJect);
		if(!empty($markerARR) && !empty($replaceARR)) {
			$subJect = str_replace($markerARR, $replaceARR, $subJect);
		}
		$subJect = "=?UTF-8?B?".base64_encode($subJect)."?=";
		require_once '../pluginFiles/phpmailer/src/Exception.php';
		require_once '../pluginFiles/phpmailer/src/PHPMailer.php';
		require_once '../pluginFiles/phpmailer/src/SMTP.php';
		$mail = new PHPMailer\PHPMailer\PHPMailer;
		$mail->CharSet = 'UTF-8';
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->Host = $setHost;
		$mail->Port = $setPort;
		$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
		$mail->SMTPAuth = true;
		$mail->Username = $setUsername;
		$mail->Password = $setPassword;
		$mail->setFrom($setFrom, $setFromTitle);
		for($i=0;$i<count($setTo);$i++){
			if(!empty($setTo[$i])) {
				$mail->addAddress($setTo[$i][0], isset($setTo[$i][1]) ? $setTo[$i][1] : '');
			}
		}
		if(count($setCC)>0){
			for($i=0;$i<count($setCC);$i++){
				if(!empty($setCC[$i])) {
					$mail->addCC($setCC[$i][0], isset($setCC[$i][1]) ? $setCC[$i][1] : '');
				}
			}
		}
		if(count($setBCC)>0){
			for($i=0;$i<count($setBCC);$i++){
				if(!empty($setBCC[$i])) {
					$mail->addBCC($setBCC[$i][0], isset($setBCC[$i][1]) ? $setBCC[$i][1] : '');
				}
			}
		}
		$mail->Subject = $subJect;
		$mail->IsHTML(true);
		$htmlImgARR = explode('data-file-name="',$htmlBody);
		for($i=1;$i<count($htmlImgARR);$i++){
			$getPureARR = explode('" data-file-size="',$htmlImgARR[$i]);
			$getPureName = trim($getPureARR[0]);
			$getPureCID = pathinfo($getPureName, PATHINFO_FILENAME);
			$htmlBody=str_replace("../uploadFiles/sysimg/".$getPureName,"cid:".$getPureCID,$htmlBody);
			$mail->AddEmbeddedImage('../uploadFiles/sysimg/'.$getPureName, $getPureCID, $getPureName);
		}
		if(!empty($markerARR) && !empty($replaceARR)) { $htmlBody = str_replace($markerARR,$replaceARR,$htmlBody); }
		$mail->Body = $htmlBody;
		return $mail->send();
	}
	//-----  會員財務總額計算  -----//
	public function getTotalPayments($mbID = 0)
	{
		if(empty($mbID)){ return false; }
		$totalPays = 0;
		$totalPayTo = 0;
		$totalIncome = 0;
		$totalPoint = 0;
		$totalFree = 0;
		$sql="
			SELECT
			COALESCE(SUM(pay_amount), 0) AS toTal,
			COALESCE(SUM(CASE WHEN pay_outgo=1 THEN pay_amount ELSE 0 END), 0) AS payTo,
			COALESCE(SUM(CASE WHEN pay_outgo=0 THEN pay_amount ELSE 0 END), 0) AS inCome,
			COALESCE(SUM(pay_points), 0) AS poTal,
			COALESCE(SUM(pay_freepoints), 0) AS freeTal
			FROM data_payments WHERE mb_id=? AND pay_status=?
		";
		$res = $this->query($sql, array($mbID, 1));
		if (!empty($res)) {
			$totalPays = intval($res[0]['toTal']);
			$totalPayTo = intval($res[0]['payTo']);
			$totalIncome = intval($res[0]['inCome']);
			$totalPoint = intval($res[0]['poTal']);
			$totalFree = intval($res[0]['freeTal']);
		}
		return array('totalPays'=>$totalPays, 'totalPayTo'=>$totalPayTo, 'totalIncome'=>$totalIncome, 'totalPoint'=>$totalPoint, 'totalFree'=>$totalFree);
	}
	//-----  取得指定會員剩餘點數  -----//
	public function getTotalPoints($mbID = 0)
	{
		if(empty($mbID)){ return false; }
		$totalPoints = 0;
		$pointIN = 0;
		$pointOUT = 0;
		$totalRedeem = 0;
		$totalFree = 0;
		$redeemIN = 0;
		$redeemOUT = 0;
		$sql="
			SELECT
			COALESCE(SUM(CASE WHEN pt_income=1 AND pt_redeem=1 THEN pt_trade ELSE 0 END), 0) AS toRN,
			COALESCE(SUM(CASE WHEN pt_income=0 AND pt_redeem=1 THEN pt_trade ELSE 0 END), 0) AS toPD,
			COALESCE(SUM(CASE WHEN pt_income=1 THEN pt_trade ELSE 0 END), 0) AS toIN,
			COALESCE(SUM(CASE WHEN pt_income=0 THEN pt_trade ELSE 0 END), 0) AS toOUT,
			COALESCE(SUM(pt_freepoints), 0) AS toFREE
			FROM fsl_points WHERE mb_id=? AND pt_status>?
		";
		$res = $this->query($sql, array($mbID, 0));
		if (!empty($res)) {
			$totalFree = intval($res[0]['toFREE']);
			$pointIN = intval($res[0]['toIN']) + $totalFree;
			$pointOUT = intval($res[0]['toOUT']);
			$totalPoints = $pointIN + $pointOUT;
			$redeemIN = intval($res[0]['toRN']);
			$redeemOUT = intval($res[0]['toPD']);
			$totalRedeem = $redeemIN + $redeemOUT;
		}
		return array('totalPoints'=>$totalPoints, 'pointIN'=>$pointIN, 'pointOUT'=>$pointOUT, 'totalRedeem'=>$totalRedeem, 'redeemIN'=>$redeemIN, 'redeemOUT'=>$redeemOUT, 'totalFree'=>$totalFree);
	}
	//-----  取得紀錄相關的所屬項目抬頭  -----//
	public function getIDFORtitle($idforID, $getForInfo=array(), $getFromInfo=array(), $getTypeInfo='')
	{
		$getTypeInfo = !empty($getTypeInfo) ? '【'.$getTypeInfo.'】' : '';
		if(!empty($getForInfo['title'])){
			$idforTitle = $getForInfo['title'];
		} else {
			if(!empty($getForInfo['titlecol'])){
				$safeTitleCol = "" . preg_replace('/[^a-zA-Z0-9_]/', '', $getForInfo['titlecol']) . "";
				$safeTable = "" . preg_replace('/[^a-zA-Z0-9_.]/', '', $getForInfo['table']) . "";
				$safeIdCol = "" . preg_replace('/[^a-zA-Z0-9_]/', '', $getForInfo['idcol']) . "";
				$safeTable = str_replace('.', '.', $safeTable);
				$sql = "SELECT {$safeTitleCol} FROM {$safeTable} WHERE {$safeIdCol}=?";
				$idforTitle = $this->single($sql, array($idforID));
			} else {
				if(!empty($getFromInfo)){
					$safeFromTitleCol = "" . preg_replace('/[^a-zA-Z0-9_]/', '', $getFromInfo['titlecol']) . "";
					$safeFromTable = "" . preg_replace('/[^a-zA-Z0-9_.]/', '', $getFromInfo['table']) . "";
					$safeFromTable = str_replace('.', '.', $safeFromTable);
					$safeFromIdCol = "" . preg_replace('/[^a-zA-Z0-9_]/', '', $getFromInfo['idcol']) . "";
					$safeForTable = "" . preg_replace('/[^a-zA-Z0-9_.]/', '', $getForInfo['table']) . "";
					$safeForTable = str_replace('.', '.', $safeForTable);
					$safeForIdCol = "" . preg_replace('/[^a-zA-Z0-9_]/', '', $getForInfo['idcol']) . "";
					$ssql = "SELECT t1.{$safeFromTitleCol} FROM {$safeFromTable} AS t1 INNER JOIN {$safeForTable} AS t2 ON t1.{$safeFromIdCol}=t2.{$safeFromIdCol} WHERE t2.{$safeForIdCol}=?";
					$idforTitle = $this->single($ssql, array($idforID));
				} else {
					$idforTitle = !empty($getForInfo) ? $getForInfo['table'].".".$getForInfo['idcol']."=".$idforID  : '';
				}
			}
		}
		return $getTypeInfo.$idforTitle;
	}
	//-----  執行發票登錄作業，並取得發票中心回傳訊息  -----//
	public function _DB_invoice_requestSend($sendParam=array(),$param2Obj=array())
	{

		if (empty($sendParam) || empty($param2Obj)) {
			return false;
		}
		$getInvoiceInfo = $this->getConfigVal('', 'invoice');
		if (!is_array($getInvoiceInfo)) {
			$getInvoiceInfo = array();
		}
		$cNewDate = new DateTime(date('Y-m-d H:i:s'));
		$cDate = $cNewDate->format('Y/m/d');
		$cTime = $cNewDate->format('H:i:s');
		$randCode = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
		$payAmount = isset($sendParam['payAmount']) ? $sendParam['payAmount'] : 0;
		$bfTax = round($payAmount / 1.05);
		$tax = round($payAmount - $bfTax);
		if (!empty($sendParam['items'])) {
			$items = $sendParam['items'];
		} else {
			$items = array(
				array(
					"Description" => isset($sendParam['itemTitle']) ? $sendParam['itemTitle'] : '',
					"Quantity" => "1",
					"Unit" => "",
					"UnitPrice" => $bfTax,
					"Amount" => $bfTax,
					"SequenceNumber" => "1",
					"Remark" => ""
				)
			);
		}
		$donate = isset($param2Obj[8]) ? $param2Obj[8] : 0;
		$donateTo = isset($param2Obj[9]) ? $param2Obj[9] : '';
		$carrierType = isset($param2Obj[1]) ? $param2Obj[1] : '';
		$carrierCode = isset($param2Obj[2]) ? $param2Obj[2] : '';
		if (isset($param2Obj[0]) && $param2Obj[0] == 'Y') {
			$carrierType = '';
			$carrierCode = '';
			$donate = 0;
			$donateTo = '';
		} else {
			if (!empty($donate) && !empty($donateTo)) {
				if ($donateTo == 'RS') {
					$geJS = _fn_getCachedJsonData("../jsonFiles/general.json");
					$npobanObj = isset($geJS['npoban']) ? $geJS['npoban'] : [];
					unset($npobanObj['RS']);
					if (!empty($npobanObj)) {
						$donateTo = array_rand($npobanObj, 1);
					}
				}
			}
		}
		$curlData = array(
			"AccessToken" => isset($getInvoiceInfo['inv_token']['config_value']) ? $getInvoiceInfo['inv_token']['config_value'] : '',
			"InvoiceRoot" => array(
				"Invoice" => array(
					array(
						"DataNumber" => isset($sendParam['dataNum']) ? $sendParam['dataNum'] : '',
						"DataDate" => $cDate,
						"SellerId" => isset($getInvoiceInfo['inv_taxid']['config_value']) ? $getInvoiceInfo['inv_taxid']['config_value'] : '',
						"BuyerName" => isset($param2Obj[3]) ? $param2Obj[3] : '',
						"BuyerId" => isset($param2Obj[4]) ? $param2Obj[4] : '',
						"CustomsClearanceMark" => "",
						"InvoiceType" => "07",
						"BuyerMark" => "",
						"DonateMark" => $donate,
						"CarrierType" => $carrierType,
						"CarrierId1" => $carrierCode,
						"CarrierId2" => $carrierCode,
						"PrintMark" => isset($param2Obj[0]) ? $param2Obj[0] : 'N',
						"MainRemark" => "",
						"NPOBAN" => $donateTo,
						"RandomNumber" => $randCode,
						"InvoiceItem" => $items,
						"SalesAmount" => $bfTax,
						"FreeTaxSalesAmount" => "",
						"ZeroTaxSalesAmount" => "",
						"TaxType" => "1",
						"TaxRate" => "0.05",
						"TaxAmount" => $tax,
						"TotalAmount" => $payAmount,
						"Contact" => array(
							"Name" => isset($param2Obj[5]) ? $param2Obj[5] : '',
							"Address" => isset($param2Obj[7]) ? $param2Obj[7] : '',
							"TEL" => isset($param2Obj[6]) ? $param2Obj[6] : '',
							"Email" => isset($param2Obj[10]) ? $param2Obj[10] : ''
						),
						"CustomerDefined" => array(
							"ProjectNo" => "",
							"PurchaseNo" => "",
							"StampDutyFlag" => ""
						)
					)
				)
			)
		);
		$curlData = json_encode($curlData, JSON_UNESCAPED_UNICODE);
		$curl = curl_init();
		$ifInvTestUrl = isset($getInvoiceInfo['inv_test']['config_value']) ? $getInvoiceInfo['inv_test']['config_value'] : '';
		$invoiceStatus = 1;
		if (!empty($ifInvTestUrl)) {
			$EGUIurl = 'uxifs.com';
			$invoiceStatus = 8;
		} else {
			$EGUIurl = 'uxifs.com';
		}
		curl_setopt($curl, CURLOPT_URL, $EGUIurl);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($curlData)
		));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlData);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($curl);
		curl_close($curl);
		//--------------------------------------------------------
		if (!empty($output)) {
			$returnOBJ = json_decode($output, true);
			$resultStatus = isset($returnOBJ['Result']['value']) ? $returnOBJ['Result']['value'] : null;
			if (empty($resultStatus)) {
				return array("done" => 1,"invIns" => 0,"payUp" => 0,"ivID" => 0,"ivUP" => 0);
			}
			$datetime = new DateTime($returnOBJ['Result']['timeStamp']);
			$resultTime = $datetime->format('Y-m-d H:i:s');
			$invoiceStatus = $returnOBJ['Automation'][0]['Status'];
			$invoiceNum = $returnOBJ['Automation'][0]['Invoice']['InvoiceNumber'];
			$dataNum = $returnOBJ['Automation'][0]['Invoice']['DataNumber'];
			$InvoiceDate = $returnOBJ['Automation'][0]['Invoice']['InvoiceDate'];
			$InvoiceDate = date("Y-m-d", strtotime($InvoiceDate));
			$InvoiceTime = $returnOBJ['Automation'][0]['Invoice']['InvoiceTime'];
			$invArr = array(
			"mb_id" => $sendParam['mbID'],
			"pay_id" => $sendParam['payID'],
			"pay_orderno" => $dataNum,
			"iv_invoiceno" => $invoiceNum,
			"iv_invoicedate" => $InvoiceDate,
			"iv_buyer" => $param2Obj[3],
			"iv_buyerid" => $param2Obj[4],
			"iv_mailto" => $param2Obj[5],
			"iv_mailadd" => $param2Obj[7],
			"iv_phone" => $param2Obj[6],
			"iv_email" => $param2Obj[10],
			"iv_beforetax" => $bfTax,
			"iv_tax" => $tax,
			"iv_aftertax" => $payAmount,
			"iv_donate" => $donate,
			"iv_donateto" => $donateTo,
			"iv_carriertype" => $carrierType,
			"iv_carrier" => $carrierCode,
			"iv_randcode" => $randCode,
			"iv_status" => $invoiceStatus
			);
			$invIns = $this->insert('fsl_invoices', $invArr);
			if ($invIns && $invIns > 0) {
				$payUp = $this->update('data_payments', array('pay_updated' => '[EXPR]NOW()','iv_id' => $invIns), array('pay_id' => $sendParam['payID']));
				return array("done" => 1,"invIns" => 1,"payUp" => $payUp,"ivID" => $invIns,"ivUP" => 0);
			} else {
				return array("done" => 1,"invIns" => 0,"payUp" => 0,"ivID" => 0,"ivUP" => 0);
			}
		}
		return array("done" => 0,"invIns" => 0,"payUp" => 0,"ivID" => 0,"ivUP" => 0);
	}
	//-----  會員等級更新操作  -----//
	public function _DB_userlevel_update($mbID=0,$mbLevel=0)
	{
		if(empty($mbID)){ return false; }
		$pointOBJ = $this->getTotalPoints($mbID);
		$needUpdate = 0;
		if($pointOBJ){
			$getLvlPoints = intval($pointOBJ['pointIN'] - $pointOBJ['redeemIN']);
			$getLvlPoints = $getLvlPoints<0 ? 0 : $getLvlPoints;
			$levelOBJ = _fn_mblevel_status($mbLevel,$getLvlPoints);
			$needUpdate = $levelOBJ['needUpdate'];
		}
		if(!empty($needUpdate)){
		$newLevel = $levelOBJ['newLevel'];
		$up = $this->update('fsl_members',  array(
			'mb_updated' => '[EXPR]NOW()',
			'mb_level' => $newLevel
		), array('mb_id'=>$mbID));
			if($up){
				return $levelOBJ;
			}
		}
		return false;
	}
	//-----  星級評分累加與平均數計算  -----//
	public function _DB_get_ratingresults($mbID,$for,$forID)
	{
		if(empty($mbID)){return false;}
		$sql="SELECT
		AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5) / 5.0) AS totAVE,
		AVG(rat_s1) AS S1,
		AVG(rat_s2) AS S2,
		AVG(rat_s3) AS S3,
		AVG(rat_s4) AS S4,
		AVG(rat_s5) AS S5
		FROM fsl_rating
		WHERE mb_id=?";
		$params = array($mbID);
		if(!empty($for) && !empty($forID)){
			$sql .= " AND rat_for=? AND rat_forid=?";
			$params[] = $for;
			$params[] = $forID;
		}
		$res = $this->query($sql,$params);
		$S1 = !empty($res[0]['S1']) ? floatval($res[0]['S1']) : 0;
		$S2 = !empty($res[0]['S2']) ? floatval($res[0]['S2']) : 0;
		$S3 = !empty($res[0]['S3']) ? floatval($res[0]['S3']) : 0;
		$S4 = !empty($res[0]['S4']) ? floatval($res[0]['S4']) : 0;
		$S5 = !empty($res[0]['S5']) ? floatval($res[0]['S5']) : 0;
		$totAVE = !empty($res[0]['totAVE']) ? floatval($res[0]['totAVE']) : 0;
		return array('S1'=>$S1,'S2'=>$S2,'S3'=>$S3,'S4'=>$S4,'S5'=>$S5,'totAVE'=>$totAVE);
	}
	//-----  取得最近輸入的發票資訊  -----//
	public function _DB_get_invinfo($mbID)
	{
		if(empty($mbID)){ return false; }
		$sql="SELECT * FROM fsl_invoices WHERE mb_id=? AND iv_status=? ORDER BY iv_published DESC";
		$res = $this->row($sql,array($mbID,1));
		if($res){
			$rtnARR = array(
				'iv_carrier' => $res['iv_carrier'],
				'iv_buyer' => $res['iv_buyer'],
				'iv_buyerid' => $res['iv_buyerid'],
				'iv_mailto' => $res['iv_mailto'],
				'iv_mailadd' => $res['iv_mailadd'],
				'iv_phone' => $res['iv_phone'],
				'iv_email' => $res['iv_email'],
				'iv_donate' => $res['iv_donate'],
				'iv_donateto' => $res['iv_donateto']
			);
			return array('invInfoOBJ'=>$rtnARR);
		}
		return false;
	}
	//-----  寫入執行歷程  -----/
	public function _DB_write_execution($mdid,$opdid,$mbid,$type,$mode)
	{
		if(empty($mdid) || empty($opdid) || empty($mbid) || empty($type) || empty($mode)){ return false; }
		$insArray = array(
			'eop_created'=>'[EXPR]NOW()',
			'merch_id'=>$mdid,
			'opd_id'=>$opdid,
			'mb_id'=>$mbid,
			'eop_type'=>$type,
			'eop_mode'=>$mode
		);
		$ins = $this->insert("data_exec_opp",$insArray);
		if($ins && $ins > 0){
			return true;
		} else {
			return false;
		}
	}
	//-----  取得新建的課程商品序號  -----/
	public function _DB_get_merchSN($rsoption='',$nowDate='')
	{
		if(empty($rsoption)) { return ''; }
		$preFix = 'M'.$rsoption;
		if(empty($nowDate)) { $nowDate = date('Ymd', time()); }
		$sql = "SELECT max(cast(right(merch_sn, 4) as SIGNED)) FROM fsl_merchandise WHERE merch_sn LIKE ?";
		$getmaxSN = $this->single($sql, array($preFix.$nowDate."%"));
		if(!empty($getmaxSN)) {
			$getNewSN = $preFix.$nowDate.str_pad($getmaxSN+1, 4, '0', STR_PAD_LEFT);
		} else {
			$getNewSN = $preFix.$nowDate.'0001';
		}
		return $getNewSN;
	}
	//-----  檢查時間軸重複疊加  -----/
	public function _DB_dupcheck_timeline($dateStart='',$dateEnd='',$timeStart=0,$timeEnd=0,$merchID=0,$moDe=0,$tyPe=0)
	{
		if(empty($dateStart) || empty($dateEnd) || empty($timeEnd) || $timeStart >= $timeEnd){
			return array('return'=>false,'errorType'=>1);
		}
		$timestampS = strtotime($dateStart);
		$timestampE = strtotime($dateEnd);
		$totalDays = (($timestampE-$timestampS) / 86400) + 1;
		$dateObj = array();
		$sql = "SELECT count(*) FROM data_sch_timeline
		WHERE stl_year=? AND stl_month=? AND stl_day=?
		AND ((stl_timestart>=? AND stl_timestart<=?)
		OR (stl_timeend>=? AND stl_timeend<=?)
		OR (stl_timestart<=? AND stl_timeend>=?))
		AND merch_id=? AND stl_mode=? AND stl_type=?";
		for ($i = 0; $i < $totalDays; $i++) {
			$nowStamp = $timestampS + (86400 * $i);
			$currYear = date('Y', $nowStamp);
			$currMonth = date('m', $nowStamp);
			$currDay = date('d', $nowStamp);
			$dupcheck = $this->single($sql, array($currYear,$currMonth,$currDay,$timeStart,$timeEnd,$timeStart,$timeEnd,$timeStart,$timeEnd,$merchID,$moDe,$tyPe));
			if($dupcheck && $dupcheck > 0){
				return array('return'=>false,'errorType'=>2);
				break;
			}
			$dateObj[] = array(
				'year' => date('Y', $nowStamp),
				'month' => date('m', $nowStamp),
				'day' => date('d', $nowStamp),
				'week' => date('w', $nowStamp)
			);
		}
		return array('return'=>true,'errorType'=>0,'dateObj'=>$dateObj);
	}
}




?>



		
