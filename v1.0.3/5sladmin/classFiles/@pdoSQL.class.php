<?php
require(__DIR__ . '/pdoSQL.Log.class.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
		$this->logObject  = new PDOLog();
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
			/*
			$dsn = 'mysql:';
			$dsn .= 'host=' . $this->Host . ';';
			$dsn .= 'port=' . $this->DBPort . ';';
			if (!empty($this->DBName)) {
				$dsn .= 'dbname=' . $this->DBName . ';';
			}
			$dsn .= 'charset=utf8;';

			*/
			$dsn = 'sqlsrv:';
			$dsn .= 'server=' . $this->Host . ';';
			//$dsn .= 'port=' . $this->DBPort . ';';
			if (!empty($this->DBName)) {
				$dsn .= 'database=' . $this->DBName . ';';
			}
			$dsn .= 'TrustServerCertificate=1;';//SQL Server 的憑證是自己產生，所以會被視為安全問題。在 DSN 中設定 TrustServerCertificate 參數，可以忽略憑證錯誤。
			////////////////////////////////////////////////
			$this->pdo = new PDO($dsn,
				$this->DBUser,
				$this->DBPassword,
				array(
					//For PHP 5.3.6 or lower
					//PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
					PDO::ATTR_EMULATE_PREPARES => false,
					//长连接
					//PDO::ATTR_PERSISTENT => true,

					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					//PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    //PDO::MYSQL_ATTR_FOUND_ROWS => true

					//PDO::SQLSRV_ATTR_DIRECT_QUERY
		  
				)
			);
			/*
			//For PHP 5.3.6 or lower
			$this->pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//$this->pdo->setAttribute(PDO::ATTR_PERSISTENT, true);//长连接
			$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			*/
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

    /**
     * 結束資料庫PDO連結
     * close pdo connection
     */
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
					$this->sQuery->bindParam($parametersType ? intval($column) : ":" . $column, $this->parameters[$column]); //It would be query after loop end(before 'sQuery->execute()').It is wrong to use $value.
				}
			}

			if (!isset($driverOptions[PDO::ATTR_CURSOR])) {
                $this->sQuery->execute();
            }
			$this->querycount++;
		}
		catch (PDOException $e) {
			$this->ExceptionLog($e, $this->BuildParams($query), 'Init', array('query' => $query, 'parameters' => $parameters));

		}

		$this->parameters = array();
	}

    
    /**
     * 將傳入的陣列重新格式為PDO資料讀寫的格式
     * @return query
     */
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
						// concatenates params as named placeholders
                            $in .= ":".$name_placeholder.", ";
						// adds each single parameter to $params
						$params[$name_placeholder] = $value;
					}
					$in = rtrim($in, ", ");
					$query = preg_replace("/:".$parameter_key."/", $in, $query);
					// removes array form $params
					unset($params[$parameter_key]);
				}
			}

			// updates $this->params if $params and $query have changed
			if ($array_parameter_found) $this->parameters = $params;
		}
		return $query;
	}

    /**
     * 開始資料庫交易，關閉自動提交模式。在自動提交模式關閉時，在通過調用PDO :: commit()結束事務之前，不會提交通過PDO對象實例對數據庫所做的更改。調用PDO :: rollBack()將回滾對數據庫的所有更改並將連接返回到自動提交模式。 
     * @return bool
     */
	public function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}

    /**
     * 提交事務，將數據庫連接返回到自動提交模式，直到下一次調用PDO :: beginTransaction()啓動新的事務。
     * @return bool
     */
	public function commit()
	{
		return $this->pdo->commit();
	}

    /**
     * 回退當前任務，由PDO :: beginTransaction（）啓動。如果數據庫設置為自動提交模式，則此功能在回退事務後將恢復自動提交模式。
     * @return bool
     */
	public function rollBack()
	{
		return $this->pdo->rollBack();
	}

    /**
     * 檢查任務在驅動程序當前是否處於活動狀態。此方法僅適用於支持任務的數據庫驅動程序。
     * @return bool
     */
	public function inTransaction()
	{
		return $this->pdo->inTransaction();
	}

    /**
     * 執行一個sql查詢，在選擇操作中返回一個結果數組，並在其他操作中返回受影響的行數。
     * @param string $query
     * @param null $params
     * @param int $fetchMode
     * @return array|int|null
     */
	public function query($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
	{
		$query        = trim($query);
		$rawStatement = preg_split("/( |\r|\n)/", $query);
		$this->Init($query, $params);
		$statement = strtolower($rawStatement[0]);
		if ($statement === 'select' || $statement === 'show' || $statement === 'call' || $statement === 'describe') {
			return $this->sQuery->fetchAll($fetchMode);
		} elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
			return $this->sQuery->rowCount();
		} else {
			return NULL;
		}
	}

    /**
     * 執行一個sql查詢，在選擇操作中返回一個迭代器，並在其他操作中返回受影響的行數。
     * @param string $query
     * @param null $params
     * @param int $fetchMode
     * @return int|null|PDOIterator
     */
    public function iterator($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
    {
        $query        = trim($query);
        $rawStatement = preg_split("/( |\r|\n)/", $query);
        $this->Init($query, $params, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $statement = strtolower(trim($rawStatement[0]));
        if ($statement === 'select' || $statement === 'show' || $statement === 'call' || $statement === 'describe') {
            return new PDOIterator($this->sQuery, $fetchMode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }

    /**
     * 建立單一資料
     * 
     * @param $tableName
     * @param null $params 單層陣列 [colnameA=>value1, colnameB=>value2]
     * @return bool|string 回傳新建 ID 值或失敗值
     */
	public function insert($tableName, $params = null)
	{
		$keys = array_keys($params);
		//$rowCount = $this->query('INSERT INTO `' . $tableName . '` (`' . implode('`,`', $keys) . '`) VALUES (:' . implode(',:', $keys) . ')', $params);
		$rowCount = $this->query('INSERT INTO ' . $tableName . ' (' . implode(',', $keys) . ') VALUES (:' . implode(',:', $keys) . ')', $params);
		if ($rowCount === 0) {
			return false;
		} else {
			if(empty($this->lastInsertId())) {return $rowCount; }
		}
		return $this->lastInsertId();
    }

    /**
     * 建立多筆資料
     *
     * @param 字串 $tableName 資料表名稱
     * @param array $params 多層陣列 [[colnameA => value1, colnameB => value2], [colnameA => value3, colnameB => value4]]
     * @return boolean 返回建立筆數，0為失敗
     */
    public function insertMulti($tableName, $params = array())
    {
        $rowCount = 0;
        if (!empty($params)) {
            $insParaStr = '';
            $insValueArray = array();

            foreach ($params as $addRow) {
                //$insColStr = implode('`,`', array_keys($addRow));
                $insColStr = implode(',', array_keys($addRow));
                $insParaStr .= '(' . implode(",", array_fill(0, count($addRow), "?")) . '),';
                $insValueArray = array_merge($insValueArray, array_values($addRow));
            }
            $insParaStr = substr($insParaStr, 0, -1);
            //$dbQuery = "INSERT INTO {$tableName} (`$insColStr`) VALUES $insParaStr";
            $dbQuery = "INSERT INTO {$tableName} ($insColStr) VALUES $insParaStr";
            $rowCount = $this->query($dbQuery, $insValueArray);
        }
        return (bool) ($rowCount > 0);
    }

    /**
     * 編輯資料表
     *
     * @param string $tableName
     * @param array $params
     * @param array $where
     * @return int affect rows
     */
    public function update($tableName, $params = array(), $where = array())
    {
        $rowCount = 0;
        if (!empty($params)) {
            $updColStr = '';
            $whereStr = '';
            $updatePara = array();
            // Build update statement
            foreach ($params as $key => $value) {
                $updColStr .= "{$key}=?,";
            }
            $updColStr = substr($updColStr, 0, -1);
            $dbQuery = "UPDATE {$tableName} SET {$updColStr}";
            // where condition
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    // Is there need to add "OR" condition?
                    $whereStr .= " AND {$key}=?";
                }
                $dbQuery .= " WHERE 1=1{$whereStr}";
                $updatePara = array_merge(array_values($params), array_values($where));
            } else {
                $updatePara = array_values($params);
            }
            $rowCount = $this->query($dbQuery, $updatePara);
        }
        return $rowCount;
    }

    /**
     * 取得最新增加的資料ID
     * @return string
     */
	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}


    /**
     * 自資料庫取得一筆指定欄位中的資料，回傳為陣列格式，即使 SELECT 多個欄位，也只會取出第一個欄位資料。
     * @param $query
     * @param null $params
     * @return array
     */
	public function column($query, $params = null)
	{
		$this->Init($query, $params);
		$resultColumn = $this->sQuery->fetchAll(PDO::FETCH_COLUMN);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultColumn;
	}

    /**
     * 自資料庫取得資料，回傳為陣列格式，只取第一順位的一筆資料。
     * @param $query
     * @param null $params
     * @param int $fetchmode
     * @return mixed
     */
	public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
	{
		$this->Init($query, $params);
		$resultRow = $this->sQuery->fetch($fetchmode);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultRow;
	}

    /**
     * 自資料庫取得一筆指定欄位中的資料，直接回傳為內容字串，即使 SELECT 多個欄位，也只會取出第一個欄位資料。
     * @param $query
     * @param null $params
     * @return mixed
     */
	public function single($query, $params = null)
	{
		$this->Init($query, $params);
		return $this->sQuery->fetchColumn();
	}

    /**
     * @param PDOException $e
     * @param string $sql
     * @param string $method
     * @param array $parameters
     */
	private function ExceptionLog(PDOException $e, $sql = "", $method = '', $parameters = array())
	{
		$message = $e->getMessage();
		$exception = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";

		if (!empty($sql)) {
			$message .= "\r\nRaw SQL : " . $sql;
		}
		$this->logObject->write($message, $this->DBName . md5($this->DBPassword));
		if (
			self::AUTO_RECONNECT
			&& $this->retryAttempt < self::RETRY_ATTEMPTS
			&& stripos($message, 'server has gone away') !== false
			&& !empty($method)
			&& !$this->inTransaction()
		) {
			$this->SetFailureFlag();
			$this->retryAttempt ++;
			$this->logObject->write('Retry ' . $this->retryAttempt . ' times', $this->DBName . md5($this->DBPassword));
			call_user_func_array(array($this, $method), $parameters);
		} else {
			if (($this->pdo === null || !$this->inTransaction()) && php_sapi_name() !== "cli") {
				//Prevent search engines to crawl
				//*** 檢查必要性 ***
				//header("HTTP/1.1 500 Internal Server Error");
				//header("Status: 500 Internal Server Error");
				//echo $exception;
				//exit();
			} else {
				throw $e;
			}
		}
	}
    //--------------------------------------------------------------------//
    //--------------------------    自定義功能    ---------------------------//
    //--------------------------------------------------------------------//
    /** ************************************************************************* **/
    /*-----  以代碼直接取得 Configurations 表中的值  -----*/
	public function getConfigVal($codE='',$grouP='')
	{
        if(empty($codE) && empty($grouP)) { return false; }
		if(!empty($codE)){
			$res = $this->row("SELECT config_crypt, config_value FROM fh.configurations WHERE config_code=? ",array($codE));
			$rtnObj = $res;
		} else if(!empty($grouP)){
			$res = $this->query("SELECT config_code, config_crypt, config_value FROM fh.configurations WHERE config_group=? ",array($grouP));
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
    /*-----  以代碼/值直接更新 Configurations 表中的值  -----*/
	public function setConfigVal($vaL,$codE = null)
	{
         if (empty($codE)) { return false; }
        $up = $this->query("UPDATE fh.configurations SET config_value= ? WHERE config_code = ?", array($vaL,$codE));
        return $up;
	}
    /** ************************************************************************* **/
	/**-----  function getAuthArray() -------------------**/
    /**-----  以 ad_id 取得頁面操作權限 Array 陣列回傳  -----**/
    /**-----  取得格式如下：-------------------------------**/
    //    Array
    //    (
    //        [PG_ADHOME] => Array
    //           (
    //               [broswer] => 1
    //               [search] => 1
    //               [addnew] => 1
    //               [edit] => 1
    //               [delete] => 0
    //           ), ......
	public function getAuthArray($iD = null)
	{
        if (empty($iD)) { return false; }
        $ar = array();
        //$res = $this->query("SELECT * FROM fh.admin_page WHERE ap_status=? ORDER BY ap_sort ASC",array('1'));
		$admJS=_fn_getCachedJsonData("jsonFiles/adminpage.json");
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
        //for ($i=0; $i<count($res); $i++){
            $resi = $this->row("SELECT * FROM fh.admin_auth WHERE ad_id=? AND ap_pagecode=?",array($iD, $idX));
            if($resi){//有建立資料時，取自 admin_auth 資料表
				//$resi = $resi[0];
                $ari = array($idX => array(
                    "browse" => intval($resi['auth_browse']),
                    "addnew"=>intval($resi['auth_addnew']),
                    "edit"=>intval($resi['auth_edit']),
                    "delete"=>intval($resi['auth_delete'])
                ));
            } else { //沒有建立資料時，取自 admin_page 資料表預設值
                $ari = array($idX => array(
                    "browse" => intval(substr($gF,0,1)),
                    "addnew"=>intval(substr($gF,1,1)),
                    "edit"=>intval(substr($gF,2,1)),
                    "delete"=>intval(substr($gF,3,1))
                ));
            }
            //合併每頁權限陣列（若使用 array_push 接續陣列時，會產生一個 Index值，而無法
            //順利以頁面代碼直接讀取權限值）。
            $ar = array_merge($ar, $ari);
			$i++;
        }//for
        //輸出結果陣列
        return $ar;
    }
    /** ************************************************************************* **/
 	/**-----  function insSysLogs() ---------------------**/
    /**-----  寫入操作記錄 true/false 回傳  ----------------**/
    /**-----  寫入格式如下：-------------------------------**/
	public function insSysLogs($iD=0, $bY=0, $coDe='', $vaRi='', $frOm='')
	{
        if(!empty($coDe) && !empty($vaRi)){
			$inArr = array(
				'lg_created' => time(),
				'lg_forid' => $iD,
				'lg_for' => $bY,
				'lg_code' => $coDe,
				'lg_params' => $vaRi
			);
			$ins = $this->insert("fh.data_logs", $inArr);
			if($ins) return true;
			else return false;
		} else {
			return false;
		}
    }
    /** ************************************************************************* **/
 	/**-----  function duplicateCheck() ---------------------**/
    /**-----  寫入操作記錄 true/false 回傳  ----------------**/
    /**-----  寫入格式如下：-------------------------------**/
	public function duplicateCheck($tableName, $params = null)
	{
        $rowCount = 0;
        if (!empty($params)) {
            $selectStr = '';
            $whereStr = '';
			foreach ($params as $key => $value) {
				$selectStr .= "{$key},";
				$whereStr .= " AND {$key}=?";
			}
			$selectStr = substr($selectStr, 0, -1);
			$whereStr = substr($whereStr, 0, -4);
			$dbQuery = "SELECT {$selectStr} FROM {$tableName} WHERE 1=1{$whereStr}";
			$wherePara = array_values($params);
            $rowCount = $this->query($dbQuery, $wherePara);
        }
        return $rowCount;
		//'ad_phone'=>'', 'ad_email'=>'','ad_line'=>'','ad_account'=>''
	}
	    /** ************************************************************************* **/
 	/**-----  function _DB_online_active() -------------------**/
    /**-----  上線/延續上線狀態  -------------------------------**/
    /**-----  寫入格式如下：------------------------------------**/
	public function _DB_online_active($mbID = 0)
	{
		$clearAll = $this->query("DELETE FROM fh.mb_online WHERE mbol_actiontime<?",array(time()-300));
		if (empty($mbID)) { return false; }
		//$sel = $this->row("SELECT * FROM fh.mb_online WHERE mb_id=?",array($mbID));
		$activeup = $this->query("UPDATE fh.mb_online SET mbol_actiontime=?, mbol_chattime=?, mbol_onchat=? WHERE mb_id=?",array(time(),0,0,$mbID));
		if($activeup){
			return true;
		} else {
			$activins = $this->insert("fh.mb_online",array('mb_id'=>$mbID,'mbol_actiontime'=>time()));
			if($activins){
				return true;
			} else {
				return false;
			}
		}
	}
 	/**-----  function _DB_onchat_active() -------------------**/
    /**-----  即時訊息上線狀態啟用  -----------------------------**/
    /**-----  寫入格式如下：------------------------------------**/
	public function _DB_onchat_active($mbID = 0)
	{
		$clearAll = $this->query("DELETE FROM fh.mb_online WHERE mbol_actiontime<?",array(time()-300));
		if (empty($mbID)) { return false; }
		$activeup = $this->query("UPDATE fh.mb_online SET mbol_actiontime=getdate(), mbol_chattime=getdate(), mbol_onchat=? WHERE mb_id=?",array(1,$mbID));
		if($activeup){
			return true;
		} else {
			return false;
		}
	}
    /** ************************************************************************* **/
 	/**-----  function getEmailTemplate() ---------------------**/
    /**-----  取得郵件模板內容  ----------------**/
    /**-----  寫入格式如下：-------------------------------**/
	public function getEmailTemplate($codE = null)
	{
		if (empty($codE)) { return false; }
        $res = $this->row("SELECT arti_subscript,arti_body FROM fh.articles WHERE arti_keycode=? AND arti_status=?",array($codE,1));//'OPPNOTIFY'
		if($res){
			return array(
				'subject' => $res['arti_subscript'],
				'content' => $res['arti_body']
			);
		} else {
			return false;
		}
	}

    /** ************************************************************************* **/
 	/**-----  function doSendEmail() ---------------------**/
    /**-----  發送電子郵件 ---------------------------------**/
    /**-----  寫入格式如下：--------------------------------**/
	public function doSendEmail($codE='',$markerARR=[],$replaceARR=[],$subJect='',$htmlBody='',$setTo=[],$setCC=[],$setBCC=[])
	{
		if(empty($codE) && (empty($subJect) || empty($htmlBody))){
			return false;
		}
		if(!empty($codE)){
			$getTemplate = $this->getEmailTemplate($codE);
			if($getTemplate){
				$subJect = $getTemplate['subject'];//自資料庫中取得的郵件主體
				$htmlBody = $getTemplate['content'];//自資料庫中取得的郵標題
			}
		}
		if(!empty($codE) && (empty($subJect) || empty($htmlBody))){
			return false;
		}

		//取得紀錄內容
		$cDateTime = date('Y-m-d H:i:s');
		//---- 設定固定值 ----//
		$getMailInfo = $this->getConfigVal('','mailserver');
		if(empty($setTo)){
			if(!empty($getMailInfo['mail_notifyadd']['config_value'])) { 
				$setTo = [[$getMailInfo['mail_notifyadd']['config_value'],$getMailInfo['mail_notifytItle']['config_value']]];
			}
			if(!empty($getMailInfo['mail_cc1add']['config_value'])) { 
				$setCC[] = [$getMailInfo['mail_cc1add']['config_value'],$getMailInfo['mail_cc1title']['config_value']]; 
				$setCC[] = [$getMailInfo['mail_cc2add']['config_value'],$getMailInfo['mail_cc2title']['config_value']];
			}
			if(!empty($getMailInfo['mail_bcc1add']['config_value'])) { 
				$setBCC[] = [$getMailInfo['mail_bcc1add']['config_value'],$getMailInfo['mail_bcc1title']['config_value']];
				$setBCC[] = [$getMailInfo['mail_bcc2add']['config_value'],$getMailInfo['mail_bcc2title']['config_value']];
			}
		}
		if(empty($setTo)){
			return false;
		}
		$setHost = $getMailInfo['smtp_host']['config_value'];
		$setPort = $getMailInfo['smtp_tlsport']['config_value'];
		if($getMailInfo['smtp_smode']['config_value']=='SSL'){
			$setPort = $getMailInfo['smtp_sslport']['config_value'];
		}
		$setFrom = $getMailInfo['mail_fromadd']['config_value'];
		$setFromTitle = $getMailInfo['mail_fromtitle']['config_value'];
		$setUsername = $getMailInfo['smtp_accuont']['config_value'];
		$setPassword = $getMailInfo['smtp_pass']['config_value'];//uikw gwnp zvcs sbhi (應用程式密碼)

		//---- /設定固定值 ----//    
		$htmlBody = stripslashes($htmlBody);//主文
		$subJect = stripslashes($subJect);
		if(!empty($markerARR) && !empty($replaceARR)) { $subJect = str_replace($markerARR,$replaceARR,$subJect); }//標題
		$subJect = "=?UTF-8?B?".base64_encode($subJect)."?=";//格式化標題(避免亂嗎)

		require '../pluginFiles/phpmailer/src/Exception.php';
		require '../pluginFiles/phpmailer/src/PHPMailer.php';
		require '../pluginFiles/phpmailer/src/SMTP.php';

		$mail = new PHPMailer;
		$mail->CharSet = 'UTF-8';
		$mail->isSMTP();
		$mail->SMTPDebug = 0;//0
		$mail->Host = $setHost;
		//$mail->Port = 465;  //SSL
		$mail->Port = $setPort;  //TLS
		//$mail->SMTPSecure = 'ssl';//ssl
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->SMTPAuth = true;
		$mail->Username = $setUsername;
		$mail->Password = $setPassword;//uikw gwnp zvcs sbhi (應用程式密碼)
		//$mail->From = $setFromMail; //設定寄件人電郵
		//$mail->FromName = $setFromName; //設定寄件人名稱
		$mail->setFrom($setFrom, $setFromTitle);
		for($i=0;$i<count($setTo);$i++){
			$mail->addAddress($setTo[$i][0], $setTo[$i][1]);
		}
		if(count($setCC)>0){
			for($i=0;$i<count($setCC);$i++){
				$mail->addCC($setCC[$i][0], $setCC[$i][1]); //設定收件人電郵及名稱(CC)
			}
		}
		if(count($setBCC)>0){
			for($i=0;$i<count($setBCC);$i++){
				$mail->addBCC($setBCC[$i][0], $setBCC[$i][1]); //設定收件人電郵及名稱(BCC)
			}
		}
		$mail->Subject = $subJect; //信件標題，解決亂碼問題
		$mail->IsHTML(true);
		//$htmlbody=str_replace("<table>",'<table width="100%">',$htmlbody);
		//$htmlImgARR = explode("../uploadFiles/sysimg/",$htmlBody);
		//---- 解析並替換 HTML 主文中的圖檔 ----//
		$htmlImgARR = explode('data-file-name="',$htmlBody);
		for($i=1;$i<count($htmlImgARR);$i++){
		$getPureARR = explode('" data-file-size="',$htmlImgARR[$i]);
		$getPureName = trim($getPureARR[0]);
		$getPureCID = pathinfo($getPureName, PATHINFO_FILENAME);
		$htmlBody=str_replace("../uploadFiles/sysimg/".$getPureName,"cid:".$getPureCID,$htmlBody);
		$mail->AddEmbeddedImage('../uploadFiles/sysimg/'.$getPureName, $getPureCID, $getPureName); 
		//$mail->AddEmbeddedImage('sysimg_20240901024320832.jpg');//cid:fh-logo02//嵌入圖檔
		}
		//---- /解析並替換 HTML 主文中的圖檔 ----//
		//---- 解析並替換 HTML 主文中的文字 ----//
		if(!empty($markerARR) && !empty($replaceARR)) { $htmlBody = str_replace($markerARR,$replaceARR,$htmlBody); }
		//---- /解析並替換 HTML 主文中的文字 ----//
		//---- 送出 HTML 主文 ----//
		$mail->Body = $htmlBody;//'This is the HTML message body in bold!';
		//---- /送出 HTML 主文 ----//
		//傳送 CC/BCC 
		//$mail->addCC("personC@gmail.com", "person C"); //設定收件人電郵及名稱(CC)
		//$mail->addBCC("personD@gmail.com", "person D"); //設定收件人電郵及名稱(BCC)
		//$mail->addAttachment("image1.jpg", "picture.jpg"); //設定附件, 對方會看到附件名稱為 picture.jpg

		return $mail->send();
		//return $mail;

	}
    /** ************************************************************************* **/
 	/**-----  function getTotalPayments( 【member】 ) ---------------------**/
    /**-----  取得指定會員支付點數  ----------------------------------------**/
    /**-----  寫入格式如下：-----------------------------------------------**/
	public function getTotalPayments($mbID = 0)
	{
		if(empty($mbID)){ return false; }
		$totalPays = 0;//總出入金額
		$totalPayTo = 0;//返現總額
		$totalIncome = 0;//支出總額
		$totalPoint = 0;//總購得點數
		$totalFree = 0;//總贈送點數
		$sql="
			SELECT SUM(pay_amount) AS toTal, 
			SUM(CASE WHEN pay_outgo=1 THEN pay_amount ELSE 0 END) AS payTo, 
			SUM(CASE WHEN pay_outgo=0 THEN pay_amount ELSE 0 END) AS inCome,
			SUM(pay_points) AS poTal,
			SUM(pay_freepoints) AS freeTal 
			FROM fh.data_payments WHERE mb_id=? AND pay_status=?
		";
		$res = $this->query($sql,array($mbID,1));
		$totalPays = intval($res[0]['toTal']);// 總進出結帳額，對人生第五季而言正數為結餘，負數為透支；而對會員而言正數為透支，負數為結餘。
		$totalPayTo = intval($res[0]['payTo']);// 總支出金額，對人生第五季而言為支付出去的金額，例如專家收入返現、退款等；而對會員而言為總收入金額。
		$totalIncome = intval($res[0]['inCome']);// 總收入金額，對人生第五季而言為收入的金錢，如儲值收入、合作中介公司匯款登錄等；而對會員而言為總支付金額。
		$totalPoint = intval($res[0]['poTal']);// 對人生第五季而言為已售出的點數，對會員而言為已購得點數（儲值後換得的點數）。
		$totalFree = intval($res[0]['freeTal']);// 對人生第五季而言為已發送的點數；而對會員而言為儲值或活動時獲贈的點數。
	
		return array('totalPays'=>$totalPays, 'totalPayTo'=>$totalPayTo, 'totalIncome'=>$totalIncome, 'totalPoint'=>$totalPoint, 'totalFree'=>$totalFree);
	
	}
    /** ************************************************************************* **/
 	/**-----  function getTotalPoints( 【member】 ) ---------------------**/
    /**-----  取得指定會員剩餘點數  ----------------**/
    /**-----  寫入格式如下：-------------------------------**/
	public function getTotalPoints($mbID = 0)
	{
		if(empty($mbID)){ return false; }
		$totalPoints = 0;	//全部可用點數
		$pointIN = 0;		//全部進項
		$pointOUT = 0;		//全部出項
		$totalRedeem = 0;
		$totalFree = 0;		//全部免費點數
		$redeemIN = 0;
		$redeemOUT = 0;
		//SELECT SUM(mbp_trade) AS toT,
		$sql="
		SELECT 
		SUM(CASE WHEN mbp_income=1 AND mbp_redeem=1 THEN mbp_trade ELSE 0 END) AS toRN, 
		SUM(CASE WHEN mbp_income=0 AND mbp_redeem=1 THEN mbp_trade ELSE 0 END) AS toPD, 
		SUM(CASE WHEN mbp_income=1 THEN mbp_trade ELSE 0 END) AS toIN, 
		SUM(CASE WHEN mbp_income=0 THEN mbp_trade ELSE 0 END) AS toOUT,
		SUM(mbp_freepoints) AS toFREE 
		FROM fh.mb_points WHERE mb_id=? AND mbp_status>?";
		$res = $this->query($sql,array($mbID,0));
		$totalFree = intval($res[0]['toFREE']); // 總獲贈的點數
		$pointIN = intval($res[0]['toIN']) + $totalFree; // 總獲得的點數（包含獲贈的點數），若要取得儲值實際點數時，此值要扣除 totalFree 值。
		$pointOUT = intval($res[0]['toOUT']); // 總已使用的點數，包含網站提供的有償服務、加價購、課程商品換購等所扣除的點數值。
		$totalPoints = $pointIN + $pointOUT; // 總剩餘的點數，此值將會同步寫入會員資料表內的 mb_points 欄位中。
		$redeemIN = intval($res[0]['toRN']); // 所有點數中可以換算現金返現給會員的總點數值，通常這些點數來源為站內有場服務時所賺取而來的，例如專家顧問費用等。
		$redeemOUT = intval($res[0]['toPD']); // 所有已經換現並領取完成的點數值。
		$totalRedeem = $redeemIN + $redeemOUT; // 剩餘可返現的點數值。
		/*
		if($totalPoints > 0 && $totalRedeem > $totalPoints){
		  $totalRedeem = $totalPoints;
		}
		*/
		return array('totalPoints'=>$totalPoints, 'pointIN'=>$pointIN, 'pointOUT'=>$pointOUT, 'totalRedeem'=>$totalRedeem, 'redeemIN'=>$redeemIN, 'redeemOUT'=>$redeemOUT, 'totalFree'=>$totalFree);
	
	}

    /** ************************************************************************* **/
 	/**-----  function getIDFORtitle ---------------------**/
    /**-----  取得紀錄相關的所屬項目抬頭  ----------------**/
    /**-----  寫入格式如下：-------------------------------**/
	public function getIDFORtitle($idforID,$getForInfo=array(),$getFromInfo=array(),$getTypeInfo='')
	{
		$getTypeInfo = !empty($getTypeInfo) ? '【'.$getTypeInfo.'】' : '';
		if(!empty($getForInfo['title'])){
			$idforTitle = $getForInfo['title'];
		} else {
			if(!empty($getForInfo['titlecol'])){
				$sql = "SELECT {$getForInfo['titlecol']} FROM {$getForInfo['table']} WHERE {$getForInfo['idcol']}=?";
				$idforTitle = $this->single($sql,array($idforID));
			} else {
				if(!empty($getFromInfo)){
					$ssql = "SELECT t1.{$getFromInfo['titlecol']} FROM {$getFromInfo['table']} AS t1 INNER JOIN {$getForInfo['table']} AS t2 ON t1.{$getFromInfo['idcol']}=t2.{$getFromInfo['idcol']} WHERE t2.{$getForInfo['idcol']}=?";
					$idforTitle = $this->single($ssql, array($idforID));
				} else {
					$idforTitle = $getForInfo['table'].".".$getForInfo['idcol']."=".$idforID;
				}
			}
		} 
		return $getTypeInfo.$idforTitle;
	}
    /** ************************************************************************* **/
 	/**-----  function _DB_invoice_requestSend -----------------------------------**/
    /**-----  執行發票登錄作業，並取得發票中心回傳訊息 ----------------------------------**/
    /**-----  寫入格式如下：---------------------------------------------------------**/
	public function _DB_invoice_requestSend($sendParam=array(),$param2Obj=array())
	{
		if(empty($sendParam) || empty($param2Obj)){
			return false;
		}
		//-----------------------------------------------------
		$getInvoiceInfo = $this->getConfigVal('','invoice');
		$cNewDate = new DateTime(date('Y-m-d H:i:s'));
		$cDate = $cNewDate->format('Y/m/d');
		$cTime = $cNewDate->format('H:i:s');// $sendParam['xxxx']
		$randCode = str_pad(rand(1,9999),4,'0',STR_PAD_LEFT);//發票用四位隨機碼
		$payAmount = $sendParam['payAmount'];
		$bfTax = round($payAmount / 1.05);
		$tax = round($payAmount - $bfTax);
		if(!empty($sendParam['items'])){
			$items = $sendParam['items'];
		} else {
			$items = array(
				array(
				  "Description" => $sendParam['itemTitle'],  //品名
					 "Quantity" => "1",				//數量
						 "Unit" => "",				//單位(非必填可為空白)
					"UnitPrice" => $bfTax,			//單價
					   "Amount" => $bfTax,			//金額
			   "SequenceNumber" => "1",				//明細排列序號
					   "Remark" => ""				//單一欄位備註(非必填可為空白)
				  )
				);
		}
		$donate = $param2Obj[8];
		$donateTo = $param2Obj[9];
		$carrierType = $param2Obj[1];
		$carrierCode = $param2Obj[2];
		if($param2Obj[0]=='Y'){
			$carrierType = '';
			$carrierCode = '';
			$donate = 0;
			$donateTo = '';
		} else {
			if(!empty($donate) && !empty($donateTo)){
				if($donateTo=='RS'){
					$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
					$npobanObj = $geJS['npoban'];
					unset($npobanObj['RS']);
					$donateTo = array_rand($npobanObj,1);
				}
			}
		}
		$curlData = array(
			"AccessToken" => $getInvoiceInfo['inv_token']['config_value'],			//用戶認證碼
			"InvoiceRoot" => array(
					"Invoice" => array(												//逐筆開立發票資料集合(若有多筆開立發票資料，此 TAG 可重複)
									array(
											"DataNumber" => $sendParam['dataNum'],	//單據號碼**
											"DataDate" => $cDate,					//單據日期
											   "SellerId" => $getInvoiceInfo['inv_taxid']['config_value'],	//賣方‐營業人統一編號
											  "BuyerName" => $param2Obj[3],			//買方‐B2B名稱/B2C個人識別碼
												"BuyerId" => $param2Obj[4],			//買方‐營業人統一編號
								 "CustomsClearanceMark" => "",		  				//通關方式註記
											"InvoiceType" => "07",					//發票類別
											"BuyerMark" => "",			  			//買受人註記
											 "DonateMark" => $donate,				//捐贈註記
											"CarrierType" => $carrierType,			//載具類別號碼//自然人憑證條碼CQ0001
											 "CarrierId1" => $carrierCode,			//載具顯碼id//自然人憑證2位大寫英文字母 + 14位數字
											 "CarrierId2" => $carrierCode,			//載具隱碼id//自然人憑證2位大寫英文字母 + 14位數字
											  "PrintMark" => $param2Obj[0],			//紙本電子發票已列印註記
										   "MainRemark" => "",						//總備註
											   "NPOBAN" => $donateTo,				//發票捐贈對象統一編號
										   "RandomNumber" => $randCode,				//發票防偽隨機碼
											"InvoiceItem" => $items,				//品項列表
											"SalesAmount" => $bfTax,				//應稅銷售額合計(新台幣)
									 "FreeTaxSalesAmount" => "",					//免稅銷售額合計(新台幣)
									 "ZeroTaxSalesAmount" => "",					//零稅率銷售額合計(新台幣)
												"TaxType" => "1",					//課稅別
												"TaxRate" => "0.05",				//稅率
											  "TaxAmount" => $tax,					//營業稅額
											"TotalAmount" => $payAmount,			//總計
												"Contact" => array(
														"Name" => $param2Obj[5],	//收件人
													"Address" => $param2Obj[7],		//地址
														"TEL" => $param2Obj[6],		//電話
													"Email" => $param2Obj[10]		//Email
												 ),
										"CustomerDefined" => array(
													 "ProjectNo" => "",				//專案編號
													"PurchaseNo" => "",				//採購案號
												 "StampDutyFlag" => ""				//顯示印花稅圖章
												)
									)
					)
			)
		);
		$curlData = json_encode($curlData);
	
		$curl = curl_init();
		//--------------------------------------------------------------
		$ifInvTestUrl = $getInvoiceInfo['inv_test']['config_value'];
		$invoiceStatus = 1;
		if(!empty($ifInvTestUrl)){
			$EGUIurl = 'https://eguitest.uxifs.com/eGUICenter/InvoiceService/UploadInvoiceAutoTrackNo';//測試
			$invoiceStatus = 8;
		} else {
			$EGUIurl = 'https://egui.uxifs.com/eivo/Published/eInvoiceService.asmx';//正式
		}
		// 設定curl網址
		curl_setopt($curl, CURLOPT_URL, $EGUIurl);
		// 略過SSL驗證
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		// 設定Header
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($curlData)
		));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlData);//json_data
		curl_setopt($curl, CURLOPT_HEADER,false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	
		$output = curl_exec($curl);
		curl_close($curl);
		//--------------------------------------------------------
		if(!empty($output)){
			$returnOBJ = json_decode($output, true);
			$resultStatus = $returnOBJ['Result']['value'];
			if(empty($resultStatus)){
				//need error descriptions? put it here!
				return array("done"=>1,"invIns"=>0,"payUp"=>0,"ivID"=>0,"ivUP"=>0);
				//------------------------------------
				//return false;
			}
			//$returnOBJ['randCode'] = $randCode;
			//return $returnOBJ;
			$datetime = new DateTime($returnOBJ['Result']['timeStamp']);
			$resultTime = $datetime->format('Y-m-d H:i:s');
			$invoiceStatus = $returnOBJ['Automation'][0]['Status'];
			$invoiceNum = $returnOBJ['Automation'][0]['Invoice']['InvoiceNumber'];
			$dataNum = $returnOBJ['Automation'][0]['Invoice']['DataNumber'];
			$InvoiceDate = $returnOBJ['Automation'][0]['Invoice']['InvoiceDate'];
			$InvoiceDate = date("Y-m-d", strtotime($InvoiceDate));
			$InvoiceTime = $returnOBJ['Automation'][0]['Invoice']['InvoiceTime'];
			$invArr = array(
				"iv_published" => $resultTime,
				"iv_updated" => $resultTime,
				"mb_id" => $sendParam['mbID'],//
				"pay_id" => $sendParam['payID'],//
				"pay_orderno" => $dataNum,//
				"iv_invoiceno" => $invoiceNum,
				"iv_invoicedate" => $InvoiceDate,
				"iv_buyer" => $param2Obj[3],//
				"iv_buyerid" => $param2Obj[4],//
				"iv_mailto" => $param2Obj[5],//
				"iv_mailadd" => $param2Obj[7],
				"iv_phone" => $param2Obj[6],//
				"iv_email" => $param2Obj[10],//
				"iv_beforetax" => $bfTax,
				"iv_tax" => $tax,
				"iv_aftertax" => $payAmount,//
				"iv_donate" => $donate,//
				"iv_donateto" => $donateTo,//
				"iv_carriertype" => $carrierType,//
				"iv_carrier" => $carrierCode,//
				"iv_randcode" => $randCode,//
				"iv_status" => $invoiceStatus
			);
			$invIns = $this->insert('fh.invoices',$invArr);
			if($invIns && $invIns>0){
				$payUp = $this->update('fh.data_payments', array('iv_id'=>$invIns), array('pay_id'=>$sendParam['payID']));//更新 fh.data_payments 資料表，關聯 fh.invoices
				return array("done"=>1,"invIns"=>1,"payUp"=>$payUp,"ivID"=>$invIns,"ivUP"=>0);
			} else {
				
				return array("done"=>1,"invIns"=>0,"payUp"=>0,"ivID"=>0,"ivUP"=>0);
			}
	
		}
		return array("done"=>0,"invIns"=>0,"payUp"=>0,"ivID"=>0,"ivUP"=>0);
	}
    /** ************************************************************************* **/
 	/**-----  function _DB_userlevel_update -----------------------------------**/
    /**-----  會員等級更新操作----------------------------------**/
    /**-----  寫入格式如下：---------------------------------------------------------**/
	public function _DB_userlevel_update($mbID=0,$mbLevel=0)
	{
		if(empty($mbID)){ return false; }
		$pointOBJ = $this->getTotalPoints($mbID);
		$needUpdate = 0;
		if($pointOBJ){
			$getLvlPoints = intval($pointOBJ['pointIN'] - $pointOBJ['redeemIN']);
			$getLvlPoints = $getLvlPoints<0 ? 0 : $getLvlPoints;
			//require_once('../includeFiles/common.lib.php');
			$levelOBJ = _fn_mblevel_status($mbLevel,$getLvlPoints);
			$needUpdate = $levelOBJ['needUpdate'];
		}
		if(!empty($needUpdate)){
			$newLevel = $levelOBJ['newLevel'];
			$up = $this->update('fh.members',  array(
				"mb_updated" => '[EXPR]GETDATE()',
				"mb_level" => $newLevel
			), array('mb_id'=>$mbID));
			if($up){
				return $levelOBJ;
			}
		}
		return false;
	}
    /** ************************************************************************* **/
 	/**-----  function _DB_get_invinfo -----------------------------------**/
    /**-----  取得最近輸入的發票資訊 ----------------------------------**/
    /**-----  寫入格式如下：---------------------------------------------------------**/
	public function _DB_get_ratingresults($mbID,$for,$forID)
	{
		if(empty($mbID)){return false;}
		$sql="SELECT AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5)/5) AS totAVE, AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5 FROM fh.data_rating WHERE mb_id=?";
		if(!empty($for) && !empty($forID)){ $sql.=" AND rat_for={$for} AND rat_forid={$forID}"; }
		$res = $this->query($sql,array($mbID));
		$S1 = !empty($res[0]['S1']) ? floatval($res[0]['S1']) : 0;
		$S2 = !empty($res[0]['S2']) ? floatval($res[0]['S2']) : 0;
		$S3 = !empty($res[0]['S3']) ? floatval($res[0]['S3']) : 0;
		$S4 = !empty($res[0]['S4']) ? floatval($res[0]['S4']) : 0;
		$S5 = !empty($res[0]['S5']) ? floatval($res[0]['S5']) : 0;
		$totAVE = !empty($res[0]['totAVE']) ? floatval($res[0]['totAVE']) : 0;
		//$ratingItem = $geJS['idfor'][$idForItem]['rating']['1'].','.$geJS['idfor'][$idForItem]['rating']['2'].','.$geJS['idfor'][$idForItem]['rating']['3'].','.$geJS['idfor'][$idForItem]['rating']['4'].','.$geJS['idfor'][$idForItem]['rating']['5'];
		//$ratingData = $S1.','.$S2.','.$S3.','.$S4.','.$S5;
		return array('S1'=>$S1,'S2'=>$S2,'S3'=>$S3,'S4'=>$S4,'S5'=>$S5,'totAVE'=>$totAVE);
	}
    /** ************************************************************************* **/
 	/**-----  function _DB_get_invinfo -----------------------------------**/
    /**-----  取得最近輸入的發票資訊 ----------------------------------**/
    /**-----  寫入格式如下：---------------------------------------------------------**/
	public function _DB_get_invinfo($mbID)
	{
		if(empty($mbID)){ return false; }
		$sql="SELECT * FROM fh.invoices WHERE mb_id=? AND iv_status=? ORDER BY iv_published DESC";
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
    //--------------------------    /自定義功能    ---------------------------//
/*
PHP Fatal error:  Uncaught Error: Object of class DateTime could not be converted to string in C:\Project\Published\FH-Php\classFiles\pdoSQL.class.php:141
Stack trace:
#0 C:\Project\Published\FH-Php\classFiles\pdoSQL.class.php(141): PDOStatement->bindParam(':iv_published', Object(DateTime))
#1 C:\Project\Published\FH-Php\classFiles\pdoSQL.class.php(238): DB->Init('INSERT INTO fh....', Array)
#2 C:\Project\Published\FH-Php\classFiles\pdoSQL.class.php(282): DB->query('INSERT INTO fh....', Array)
#3 C:\Project\Published\FH-Php\WebStore\api_qpay_returnURL.php(173): DB->insert('fh.invoices', Array)
#4 {main}
  thrown in C:\Project\Published\FH-Php\classFiles\pdoSQL.class.php on line 141

*/
}
