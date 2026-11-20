		/*
		1.宣告 API 服務網址、APPID與APIKEY
        2.動態載入 TGOS MAP API
		*/
	   
		/**********************************************************
		* TGOS MAP API :  動態載入引用、APPID 與 APIKey
		***********************************************************/
		
		//TGOS MAP API 應用程式識別碼(APPId)
        var MAP_AppID = "VIdCqgTZ/EWEwKH0dtfV6LKCaKnod/DQ1Z6ytl+/xnT2Xkm9st4KDw==";

		//TGOS MAP API 應用程式介接驗證碼(APIKey)
        var MAP_APIKey = "NvUe+tN1tqN2i8o4G4vhvpAiLYsCnrOCKMdZKi+AAovUZMhDtynM3bgmvwEe1JB3m06+PO2KlH86AyrCeZTEUNxJS6fBvqPtpShtBE9JkNnrXW8AR8vaKawVTExVOPbMZxOXDHRyoJSpfXDKttkz0zsEo2UrmVEZvobsRZ4IEDujpfLSG/plc0eA/zJEH32gRmIQmyvJBzs1xs637Jb7+jJ7ixeyUlkpNRA6LaozSZ/dY/t41wV3GAjIVV4Ra7Te/WUlhl42tPyBlhU1S1ZrnN9PPuBt1FANt6cVTzPdz3PbjADvmAWi2HxjgpL1Ixd5T0jaRs7FvgwNkNbpMCmUIIZRzEf6skHAYsCINJa/UPiHrT6PGpjhTuosVcbf9woPifT+80KTq8ZDBGgQ+SDMaA==";

		//TGOS MAP API 的 js 檔網址，並須加入 APPId 與 APIKey 參數        
        var jsUrl_tgosMap = "https://api.tgos.tw/TGOS_API/tgos?ver=2&AppID=" + MAP_AppID + "&APIKey=" + MAP_APIKey; 
		
		//執行 TGOS MAP API 的 js檔載入
        document.write(unescape("%3Cscript type='text/javascript' src='" + jsUrl_tgosMap + "' %3E%3C/script%3E")); 
				
		/**********************************************************
		* 全國門牌位置比對服務 :  WebService 網址、APPID 與 APIKey
		***********************************************************/		
        //全國門牌位置比對服務 WebService 網址   
        var wsUrl = "https://addr.tgos.tw/addrws/v40/QueryAddr.asmx/QueryAddr";
		
		//門牌應用程式識別碼(APPId)
		var ADDR_AppID = "";  

		//門牌應用程式介接驗證碼(APIKey)
		var ADDR_APIKey = "";		
		
      $(function () {//網頁元素準備好時呼叫(可以選取網頁中的元素，並對操作它們)

            initMap('Map'); //先初始化地圖

            $('#btQuery').click(function () {//當按下查詢鈕時呼叫
                var Address = $('#txtAddress').val(); //取出使用者所輸入的地址
                executeLocate(Address); //呼叫地址比對功能
            }).click();

        });

        /*==========================================
        執行地址比對並輸出結果至畫面 
        ============================================*/
        function executeLocate(address) {
            var $ResultList = $('#ResultList');
            var $ResultInfo = $('#ResultInfo');
            $.ajax({
                url: wsUrl, ////API 服務網址
                type: 'GET', //請求模式使用 GET 模式
                data: {/****************** 定位參數可依所需自行設定，以下設定最常用的，說明可查 https://addr.tgos.tw/addrws/v40/QueryAddr.asmx?op=QueryAddr ***********************/
                    oAPPId: ADDR_AppID, //應用程式識別碼(APPId)
                    oAPIKey: ADDR_APIKey, // 應用程式介接驗證碼(APIKey)
                    oAddress: address, //所要查詢的門牌位置
                    oSRS: 'EPSG:3826', //回傳的坐標系統
                    oFuzzyType: "2", //模糊比對的代碼
                    oResultDataType: 'json', //回傳的資料格式
                    oFuzzyBuffer: "0", //模糊比對回傳門牌號的許可誤差範圍
                    oIsOnlyFullMatch: "false", //是否只進行完全比對
                    oIsSupportPast: "true", //是否查詢舊門牌
                    oIsShowCodeBase: "true", //是否顯示統計區資訊
                    oIsLockCounty: "false", //是否鎖定縣市
                    oIsLockTown: "false", //是否鎖定鄉鎮市區
                    oIsLockVillage: "false", //是否鎖定村里
                    oIsLockRoadSection: "false", //是否鎖定路段
                    oIsLockLane: "false", //是否鎖定巷
                    oIsLockAlley: "false", //是否鎖定弄
                    oIsLockArea: "false", //是否鎖定地區
                    oIsSameNumber_SubNumber: "true", //號之、之號是否視為相同
                    oCanIgnoreVillage: "true", //找不時是否可忽略村里
                    oCanIgnoreNeighborhood: "true", //找不時是否可忽略鄰
                    oReturnMaxCount: "0"//如為多筆時，限制回傳最大筆數
                },
                beforeSend: function () {
                    /*===== 執行前會進來 =====*/
                    $ResultInfo.text(""); //清除上次結果筆數訊息
                    $ResultList.empty().append('<span style="padding:8px;color:#aaa;font-size:13px;">比對中，請稍後...</span>'); //清除上次結果清單並顯示比對中
                }
            }).done(function (data) {
                /*===== 成功時會進來 =====*/

                //console.log($(data).text());
                var jsonObj = $.parseJSON($(data).text()); //將查詢回來的結果轉成 JSON 課程商品
                //console.log(jsonObj);

                var Info = jsonObj.Info; // 取出結果說明課程商品，是一個 Array
                var AddressList = jsonObj.AddressList; //取出地址清單課程商品，是一個 Array

                $ResultInfo.text("共 " + Info[0].OutTotal + " 筆"); //設定本次查詢結果總筆數
                $ResultList.empty();
                for (var i = 0, max = AddressList.length; i < max; i++) {//將地址結果清單寫到畫面中
                    var address = AddressList[i];
                    $("<a class='addressItem' x='" + address.X + "' y='" + address.Y + "'>" + address.FULL_ADDR + "</a>").appendTo($ResultList);
                }

                //---

                $('.addressItem').click(function () {//當按下地址清單中的某一列時呼叫
                    var $this = $(this);
                    var x = $this.attr("x"); //取出X坐標
                    var y = $this.attr("y"); //取出Y坐標
                    var address = $this.text(); //取出地址

                    setMarker(x, y); //設定標記至指定的位置，並移置於地圖中心
                    showMessageBox(address, x, y); //顯示本次訊息視窗

                    $this.addClass('addressItem_Select').siblings().removeClass('addressItem_Select'); //設定被點選的地址列樣式

                });

            }).fail(function (jqXHR, textStatus, errorThrown) {
                /*===== 失敗時會進入 =====*/
                $ResultList.empty();
            }).always(function (data, textStatus, errorThrown) {
                /*===== 不管失敗或成功，最後都會進入 =====*/
            });

        }
