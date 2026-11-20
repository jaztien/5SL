<?php
	//require_once "../classFiles/QPay.class.php";
	require_once('../includeFiles/db.inc.php');
	$getQpayInfo = $DB->getConfigVal('','qpay');
	//API 位置
	
	$ifQpayTestUrl = $getQpayInfo['qpay_test']['config_value'];
	if(!empty($ifQpayTestUrl) && $ifQpayTestUrl=="1"){
		$targetUrl = $getQpayInfo['qpay_testurl']['config_value'];
	} else {
		$targetUrl = $getQpayInfo['qpay_url']['config_value'];
	}
	//$targetUrl= "https://apisbx.sinopac.com/funBIZ-Sbx/QPay.WebAPI/api/";
	//$targetUrl= "https://sandbox.sinopac.com/QPay.WebAPI/api/";	  //備援網址
	//商店代號
	$ShopNo = $getQpayInfo['qpay_shopno']['config_value'];
	//$ShopNo = "NA0443_001";
	//雜湊值
	$Hash = Array(	
		
		"A1" => $getQpayInfo['qpay_hashA1']['config_value'],
		"A2" => $getQpayInfo['qpay_hashA2']['config_value'],
		"B1" => $getQpayInfo['qpay_hashB1']['config_value'],
		"B2" => $getQpayInfo['qpay_hashB2']['config_value']
		/*
		"A1" => "1558DD36081C4187",
		"A2" => "B826496DB4F04B98",
		"B1" => "F64A91AF213742EB",
		"B2" => "9EA0FED661C344C4"
		*/
	);
	//API 授權認證金鑰
	$QPayApimAuth = $getQpayInfo['qpay_authcode']['config_value'];
	//$QPayApimAuth = "b5e6986d-8636-4aa0-8c93-441ad14b2098";
	$QPayReturnURL = $getQpayInfo['qpay_returnurl']['config_value'];
	//$QPayReturnURL = 'https://www.5seasonlife.com/api_qpay_returnURL.php';
	
	$QPayBackendURL = $getQpayInfo['qpay_backendurl']['config_value'];
	//$QPayBackendURL = 'https://www.5seasonlife.com/api_qpay_backendURL.php';

	AllotQuery();

	//建立人生第五季線上支付訂單
	function _fn_OrderCreate($typE,$amounT,$parA1,$parA2,$parA3)
	{
		global $ShopNo,$QPayReturnURL,$QPayBackendURL;
		$Service = new OrderCreate;
		$Service->ShopNo = $ShopNo;
		$Service->Amount = $amounT*100;
		$Service->OrderNo = $typE . date("YmdHis");
		$Service->PayType = $typE;
		//$Service->PrdtName = '虛擬帳號訂單';
		$Service->Param1 = $parA1;
		$Service->Param2 = $parA2;
		$Service->Param3 = $parA3;
		$Service->ReturnURL = $QPayReturnURL;
		$Service->BackendURL = $QPayBackendURL;

		switch($typE){
            case 'A':
				$date = new DateTime(date('Y-m-d H:i:s'));
				$date->add(new DateInterval('P10D'));//PT5M//P10D
				//$date->modify("+3 minutes");
				$ExpireDate = $date->format('Ymd');
				$ExpireTime = $date->format('Hi');
				$Service->ATMParam['ExpireDate'] = $ExpireDate;//A
				$Service->ATMParam['ExpireTime'] = $ExpireTime;//A
				$Service->PrdtName = '虛擬帳號訂單';
			break;
			case 'C':
				$Service->CardParam['AutoBilling'] = 'Y';//C
				$Service->PrdtName = '信用卡訂單';
			break;
			case 'M':
				$Service->MobileParam['ExpMinutes'] = '10';//M
				$Service->PrdtName = '行動支付訂單';
			break;
			case 'L':
				$Service->PrdtName = 'Line Pay 訂單';
			break;
			case 'P':
				$Service->PrdtName = '信用卡價金訂單';
			break;
			case 'F':
				$Service->PrdtName = '不明訂單';
			break;
		}
		$rtn = APIService("OrderCreate", $Service);
		return $rtn;
		//return $Service;

	}
	//建立信用卡訂單
	/*
	function OrderCreate4Card()
	{
		global $ShopNo;
	
		$Service = new OrderCreate;
		$Service->ShopNo = $ShopNo;
		$Service->Amount = '50000';
		$Service->OrderNo = 'C' . date("YmdHis");
		$Service->PayType = 'C';
		$Service->PrdtName = '信用卡訂單';
		$Service->Param1 = 'Param1';
		$Service->Param2 = 'Param2';
		$Service->Param3 = 'Param3';
		$Service->ReturnURL = 'http://10.11.22.113:8803/QPay.ApiClient-Sandbox/Store/Return';
		$Service->BackendURL = 'https://sandbox.sinopac.com/funBIZ.ApiClient/AutoPush/PushSuccess';
		$Service->CardParam['AutoBilling'] = 'Y';
		//return $Service;
		return APIService("OrderCreate", $Service);
	}
	
	//建立虛擬帳號訂單
	function OrderCreate4ATM()
	{
		global $ShopNo;
	
		$date = new DateTime(date('Y-m-d'));
		$date->add(new DateInterval('P10D'));
		$ExpireDate = $date->format('Ymd');
	
		$Service = new OrderCreate;
		$Service->ShopNo = $ShopNo;
		$Service->Amount = '50000';
		$Service->OrderNo = 'A' . date("YmdHis");
		$Service->PayType = 'A';
		$Service->PrdtName = '虛擬帳號訂單';
		$Service->ReturnURL = 'http://10.11.22.113:8803/QPay.ApiClient-Sandbox/Store/Return';
		$Service->BackendURL = 'https://sandbox.sinopac.com/funBIZ.ApiClient/AutoPush/PushSuccess';
		$Service->ATMParam['ExpireDate'] = $ExpireDate;
		
		return APIService("OrderCreate", $Service);
	}
  
	//建立行動支付訂單
	function OrderCreate4Mobile()
	{
		global $ShopNo;
    
		$Service = new OrderCreate;
		$Service->ShopNo = $ShopNo;
		$Service->Amount = '50000';
		$Service->OrderNo = 'M' . date("YmdHis");
		$Service->PayType = 'M';
		$Service->PrdtName = '行動支付訂單';
		$Service->ReturnURL = 'http://10.11.22.113:8803/QPay.ApiClient-Sandbox/Store/Return';
		$Service->BackendURL = 'https://sandbox.sinopac.com/funBIZ.ApiClient/AutoPush/PushSuccess';
		$Service->MobileParam['ExpMinutes'] = '10';
		
		return APIService("OrderCreate", $Service);
	}
	*/
	//信用卡 - 待請款訂單查詢
	function OrderUnCapturedQuery()
	{
		global $ShopNo;
		
		$Service = new OrderUnCapturedQuery;
		$Service->ShopNo = $ShopNo;
		
		return APIService("OrderUnCapturedQuery", $Service);
	}
	
	//信用卡 – 訂單維護服務
	function OrderMaintain()
	{
		global $ShopNo;
		
		$Service = new OrderMaintain;
		$Service->ShopNo = $ShopNo;
		$Service->OrderNo = 'C201805250005';
		$Service->Command = 'R';
		
		return APIService("OrderMaintain", $Service);
	}
	/*
	//虛擬帳號退款 – 首次發動API
	function OrderMaintain()
	{
		global $ShopNo;
		
		$Service = new OrderMaintain;
		$Service->ShopNo = $ShopNo;
		$Service->OrderNo = 'A202209250001';
		$Service->Command = 'R';
		
		return APIService("OrderMaintain", $Service);
	}
	
	//虛擬帳號退款 – 第二次發動API
	function OrderMaintain()
	{
		global $ShopNo;
		
		$Service = new OrderMaintain;
		$Service->ShopNo = $ShopNo;
		$Service->OrderNo = 'A202209250001';
		$Service->Command = 'R';
		$Service->RefundToken = '68b58e2a4c1bd52be54f8d602d7bb3fefccb38c02d1519eb987507b6d1622567';
		
		return APIService("OrderMaintain", $Service);
	}
	*/
	//訂單交易查詢
	function OrderQuery()
	{
		global $ShopNo;
		
		$Service = new OrderQuery;
		$Service->ShopNo = $ShopNo;
		$Service->OrderNo = 'A20180706100451';
		
		return APIService("OrderQuery", $Service);
	}
	
	//訊息查詢服務
	function OrderPayQuery()
	{
		global $ShopNo;
		
		$Service = new OrderPayQuery;
		$Service->ShopNo = $ShopNo;
		$Service->PayToken = 'c7b0d2b994251d730f4a2feb24e584c509264e92829c9194d1952da08898e44f';
		
		return APIService("OrderPayQuery", $Service);
	}
	
	//交易對帳檔查詢服務
	function BillQuery()
	{
		global $ShopNo;
		
		$Service = new BillQuery;
		$Service->ShopNo = $ShopNo;
		$Service->BillDate = '20180525';
		
		return APIService("BillQuery", $Service);
	}
	
	//撥款檔查詢服務
	function AllotQuery()
	{
		global $ShopNo;
		
		$Service = new AllotQuery;
		$Service->ShopNo = $ShopNo;
		$Service->AllotDateS = '20180625';
		$Service->AllotDateE = '20180625';
		$Service->PayType = 'A';
		
		return APIService("AllotQuery", $Service);
	}

	//信用卡價金 撥款檔查詢服務
	function AllotTrustQuery()
	{
		global $ShopNo;
		
		$Service = new AllotTrustQuery;
		$Service->ShopNo = $ShopNo;
		$Service->AllotDateS = '20180625';
		$Service->AllotDateE = '20180625';
		
		return APIService("AllotTrustQuery", $Service);
	}

	//信用卡綁卡授權交易
	function CardBindCreate()
	{
		global $ShopNo;
		
		$Service = new CardBindCreate;
		$Service->ShopNo = $ShopNo;
		$Service->BindNo = date("YmdHis");
		$Service->ReturnURL = 'http://10.11.22.113:8803/QPay.ApiClient-Sandbox/Store/Return';
				
		return APIService("CardBindCreate", $Service);
	}



	//------------------------------------------
/*

	//API 位置
	$targetUrl= "https://apisbx.sinopac.com/funBIZ-Sbx/QPay.WebAPI/api/";
	//$targetUrl= "https://sandbox.sinopac.com/QPay.WebAPI/api/";	  //備援網址
	//商店代號
	$ShopNo = "NA0443_001";
	//雜湊值
	$Hash = Array(	
					"A1" => "1558DD36081C4187",
					"A2" => "B826496DB4F04B98",
					"B1" => "F64A91AF213742EB",
					"B2" => "9EA0FED661C344C4"
				);
	//API 授權認證金鑰
	$QPayApimAuth = "b5e6986d-8636-4aa0-8c93-441ad14b2098";
	$QPayReturnURL = 'https://www.5seasonlife.com/api_qpay_returnURL.php';
	$QPayBackendURL = 'https://www.5seasonlife.com/api_qpay_backendURL.php';

	*/


	//執行 API 服務方法
	function APIService($service, $objService){
		global $ShopNo, $Hash, $targetUrl;
		
		$url = $targetUrl . "Order";
		
		//取得 Nonce 值
		$Nonce = getNonce($ShopNo, $targetUrl);
		//取得 HashID
		$HashID = getHashID($Hash);
		//取得 IV
		$IV = getIV($Nonce);
		//取得 Sign
		$Sign = getSign($objService, $Nonce, $HashID);
		//訊息內文 E2E 加密(先將變數為 null 移除)
		$objService = array_filter((array) ($objService));
		
		//echo json_encode($objService)."<hr>".$HashID."<hr>".$IV."<hr>";
		
		$Message = EncryptAesCBC(json_encode($objService), $HashID, $IV);
		
		//建立 Request Class
		$Request = new API;
		$Request->ShopNo = $ShopNo;
		$Request->APIService = $service;
		$Request->Sign = $Sign;
		$Request->Nonce = $Nonce;
		$Request->Message = $Message;

		//建立 Response Class
		$Response = new API;
		
		//取得永豐銀行回覆訊息(未解密)
		$Response = json_decode(WebAPI($url, json_encode($Request)));
		//return json_encode($Response);
		//取得 Response Nonce
		$ResNonce = $Response->Nonce;
		//取得 Response IV
		$ResIV = getIV($ResNonce);
		//取得永豐銀行訊息內文(已解密)
		$result = DecryptAesCBC($Response->Message, $HashID, $ResIV);
		
		return $result;
	}
	
	//取得 Nonce 方法
	function getNonce($shopno, $targeturl){
		$url = $targeturl . "Nonce";
	
		$post_data = json_encode(array('ShopNo' => $shopno));
		$result = WebAPI($url, $post_data);
		$result = json_decode($result);
		
		return $result->Nonce;
	}
	
	//取得 Hash ID 計算方法
	function getHashID($hash){
	
		//var_dump($hash);
	
		$Byte_A1 = strToHexBytes($hash["A1"]);
		$Byte_A2 = strToHexBytes($hash["A2"]);
		$Byte_B1 = strToHexBytes($hash["B1"]);
		$Byte_B2 = strToHexBytes($hash["B2"]);
		
		$XOR1 = setXOR($Byte_A1, $Byte_A2);
		$XOR2 = setXOR($Byte_B1, $Byte_B2);

		$result = hexBytesToString($XOR1).hexBytesToString($XOR2);

		return $result;
	}
	
	//取得 IV 計算方法
	function getIV($nonce){
		$data = SHA256($nonce);
		return substr($data, strlen($data) - 16, 16);
	}
	
	//取得 Sign 計算方法
	function getSign($data, $nonce, $hashid){
		$result = '';
		$content = '';
		
		//移除 null
		$data = array_filter((array) ($data));
	
		//對欄位升序排序
		ksort($data);
	
		//訊息排序組合
		while ($fruit_name = current($data)) {
		
			//僅抓取第一層變數作組合
			if (is_array($data[key($data)]) == false){
				$content .= key($data) . '=' . $data[key($data)] . '&';
			}
			
			next($data);
		}
		$content = substr($content , 0, strlen($content) -1);
	
		//字串雜湊
		$content .=  $nonce . $hashid;
		
		//SHA256
		$result = SHA256($content);
	
		return $result;
	}
	
	//字串轉 Hex 方法
	function strToHexBytes($string){
		$hex = array();
		$j = 0;
		
		for ($i=0; $i<strlen($string); $i+=2){
			$hex[$j] = (int)base_convert(substr($string, $i, 2), 16, 10);
			
			$j+=1;
		}
		
		return $hex;
	}
	
	//Hex 轉字串方法
	function hexBytesToString($hex){
		$result = '';
		$str = '';

		for ($i=0; $i<sizeof($hex); $i++){
			$str = (string)base_convert($hex[$i], 10, 16);
			
			if (strlen($str) < 2){
				$str = '0' . $str;
			}
		
			$result .= $str;
		}

		return strtoupper($result);
	}
	
	//XOR 計算方法  
	function setXOR($byte1, $byte2){

		$result = array();

		for ( $i=0 ; $i<sizeof($byte1) ; $i++ ) {
			$result[$i] = ($byte1[$i] ^ $byte2[$i]);		
		}

		return $result;
	}
	
	//AES CBC 加密
	function EncryptAesCBC($data, $key, $iv) {
		$result = '';

		$padding = 16 - (strlen($data) % 16);
		$data .= str_repeat(chr($padding), $padding);
		$encrypt = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

		$result = strtoupper(bin2hex($encrypt));

		return $result;
	}

	//AES CBC 解密
	function DecryptAesCBC($data, $key, $iv) {
		$result = '';

		$encrypt = hex2bin($data);

		$decrypt = openssl_decrypt($encrypt, 'AES-256-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

		$padding = ord($decrypt[strlen($decrypt) - 1]);
		$result = substr($decrypt, 0, -$padding);

		return $result;
	}
	
	//SHA256 後字串轉大寫
	function SHA256($data){
		return strtoupper(hash('sha256', $data));
	}
	
	//WebAPI 串接方法
	function WebAPI($url, $post_data) {
		global $QPayApimAuth;
		
		$ch=curl_init($url);
		$options=Array(
			CURLOPT_HEADER => 0,
			CURLOPT_NOBODY => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => array(
				"Content-type: application/json; charset=utf-8",
				"X-KeyID:{$QPayApimAuth}"
			),
			CURLOPT_POST=> 1,
			CURLOPT_POSTFIELDS=> $post_data ,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSLVERSION => 6					//(1:TLSv1 / 6:TLSv1_2)  使用 TLS 1.2 Protocol
		);
		curl_setopt_array($ch, $options);
		$result=curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}
	
	//Request、Response 呼叫用
	class API
	{
		//API 版本
		public $Version = '1.0.0';
		//商店代號
		public $ShopNo;
		//API服務
		public $APIService;
		//安全簽章
		public $Sign;
		//時間戳記
		public $Nonce;
		//交易訊息內文
		public $Message;
	}
	
	//建立訂單交易(OrderCreate)
	class OrderCreate
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
		//訂單總金額
		public $Amount;
		//幣別
		public $CurrencyID = 'TWD';
		//收款名稱
		public $PrdtName;
		//備註
		public $Memo;
		//自訂參數一
		public $Param1;
		//自訂參數二
		public $Param2;
		//自訂參數三
		public $Param3;
		//導回頁面
		public $ReturnURL;
		//背端通知
		public $BackendURL;
		//收款方式
		public $PayType;
		//虛擬帳號用 Param
		public $ATMParam  = array(
								//'ExpireDate'=> null
							);
		//信用卡用 Param
		public $CardParam = array(
								//'AutoBilling'=> null,
								//'ExpBillingDays'=> null,
								//'ExpMinutes'=> null,
								//'PayTypeSub'=> null,
								//'Staging'=> null
							);
		//行動支付用 Param
		public $MobileParam  = array(
								//'ExpMinutes'=> null
							);
	}
	
	//信用卡 - 待請款訂單查詢(OrderUnCapturedQuery)
	class OrderUnCapturedQuery
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
	}
	
	//信用卡、虛擬帳號 – 訂單維護服務(OrderMaintain)
	class OrderMaintain
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
		//發送要求
		public $Command;
		//申請退款金額
		public $Amount;
		//退款原因
		public $Remark;
		//虛擬帳號退款Token
		public $RefundToken;
	}

	//訂單交易查詢(OrderQuery)
	class OrderQuery
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
		//收款方式
		public $PayType;
		//交易日期(起)
		public $OrderDateTimeS;
		//交易日期(迄)
		public $OrderDateTimeE;
		//付款日期(起)
		public $PayDateTimeS;
		//付款日期(迄)
		public $PayDateTimeE;
		//付款狀態為條件查詢
		public $PayFlag;
	}
	
	//訊息查詢服務(OrderPayQuery)
	class OrderPayQuery
	{
		//商店代號
		public $ShopNo;
		//Token 值
		public $PayToken;
	}
	
	//交易對帳檔查詢服務(BillQuery)
	class BillQuery
	{
		//商店代號
		public $ShopNo;
		//交易對帳日期
		public $BillDate;
	}
	
	//撥款檔查詢服務(AllotQuery)
	class AllotQuery
	{
		//商店代號
		public $ShopNo;
		//撥款日期(起)
		public $AllotDateS;
		//撥款日期(迄)
		public $AllotDateE;
		//收款方式
		public $PayType;
	}

	//信用卡價金 撥款檔查詢服務(AllotTrustQuery)
	class AllotTrustQuery
	{
		//商店代號
		public $ShopNo;
		//撥款日期(起)
		public $AllotDateS;
		//撥款日期(迄)
		public $AllotDateE;
	}

	//信用卡綁卡授權交易(CardBindCreate)
	class CardBindCreate
	{
		//商店代號
		public $ShopNo;
		//信用卡綁定編號
		public $BindNo;
		//授權完成轉入URL
		public $ReturnURL;
	}


?>

