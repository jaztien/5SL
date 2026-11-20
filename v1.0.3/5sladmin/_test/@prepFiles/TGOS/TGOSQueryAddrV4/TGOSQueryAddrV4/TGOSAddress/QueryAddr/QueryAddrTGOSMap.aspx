<%@ Page Language="C#" AutoEventWireup="true" CodeFile="QueryAddrTGOSMap.aspx.cs" Inherits="Sample_Codes_TGOSQueryAddr_Sample02" %>

<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml">
<head id="Head1" runat="server">
    <title>ASP.NET呼叫應用範例</title>
    <style type="text/css">
        .rowStyle
        {
            padding: 6px 20px;
            line-height: 24px;
            white-space: nowrap;
            letter-spacing: 3px;
            cursor: pointer;
            color: #0097C6;
            font-size: 11pt;
            text-align: left;
        }
        .rowStyle:link
        {
            text-decoration: none;
        }
        .rowStyle:hover
        {
            background-color: rgb(112, 207, 236);
            color: White;
        }
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
    <!-- 宣告 API 服務網址、APPID與APIKEY -->
    <script type="text/javascript">
        //TGOS MAP API 的 js 檔網址，並須加入 APPId 與 APIKey 參數        
        var AppIDforTGOSMAPAPI = "";
        var APIkeyforTGOSMAPAPI = "";
        var jsUrl_tgosMap = "https://api.tgos.tw/TGOS_API/tgos?ver=2&AppID=" + AppIDforTGOSMAPAPI + "&APIKey=" + APIkeyforTGOSMAPAPI;
        document.write(unescape("%3Cscript type='text/javascript' src='" + jsUrl_tgosMap + "' %3E%3C/script%3E")); //執行 TGOS MAP API 的 js檔載入
    </script>
    <script type="text/javascript">
        var setPosition;
        var marker;
        var pMap = null;
        var messageBox; //訊息視窗課程商品
        var messageBoxArray = new Array();

        window.onload = function () {

            InitMap();

            $('#LblResult').hide(); //隱藏

            var aJson = $.parseJSON($('#LblResult').text());
            if (aJson && aJson.AddressList.length == 0) {
                alert("輸入查詢地址錯誤,請重新輸入,查詢格式為:縣市,區,路名");
                location.reload(); //頁面刷新
            }

            $("#ItemsData").append("<label >" + '總共有' + aJson.AddressList.length + '筆' + "</label>");  //插入label



            for (var i = 0; i < aJson.AddressList.length; i++) {
                var address = aJson.AddressList[i].FULL_ADDR; //地址
                var X = aJson.AddressList[i].X; //x坐標
                var Y = aJson.AddressList[i].Y; //y坐標
                $("#AddrList").append("<span class=\"rowStyle\" onclick=\"setCoordinate(" + X + "," + Y + ",'" + address + "',this);return false;\">" + address + "</span></br>");


            }

            if ($("#AddrList .rowStyle").length > 0) {
                $("#AddrList .rowStyle")[0].click();

            }


        };

        function InitMap() {

            var pOMap = document.getElementById("mymap");
            pMap = new TGOS.TGOnlineMap(pOMap, TGOS.TGCoordSys.EPSG3826); //宣告TGOnlineMap地圖課程商品並設定坐標系統
            marker = new TGOS.TGMarker(pMap, new TGOS.TGPoint(0, 0));
            marker.setVisible(false);
            setPosition = new TGOS.TGLocateService(); //宣告一個TGLocateService課程商品，準備做為定位工具使用

        }
        function closeInfoWindow() {

            for (var i = 0; i < messageBoxArray.length - 1; i++) {
                messageBoxArray[i].close(); //關閉訊息視窗
            }
        }



        function setCoordinate(pointX, pointY, address, oSpan) {

            var pt = new TGOS.TGPoint(pointX, pointY);
            setPosition.setCenter(pMap, pt); //移動查詢目標至地圖中心
            marker.setVisible(true); //顯示marker
            marker.setPosition(pt); //設定顯示marker至指定的坐標
            pMap.setZoom(12); //設定範圍到適當比例尺到

            messageBox = new TGOS.TGInfoWindow("" + address + "</br>" + "坐標X:" + pointX + "</br>" + "坐標Y:" + pointY, new TGOS.TGPoint(pointX, pointY));
            var opts = {
                maxWidth: Number(250), //訊息視窗的最大寬度(介於100px~250px之間)
                pixelOffset: new TGOS.TGSize(5, -15),  //訊息視窗的位移量(pixel)
                zIndex: 0//視窗堆疊順序
            }
            messageBox.setOptions(opts);

            messageBox.open(pMap);
            messageBoxArray.push(messageBox);
            closeInfoWindow();
            $("#AddressData").empty().append("<label >" + '目前查詢地址:' + address + "</label>");

            $(oSpan).css({
                "background-color": "rgb(112, 207, 236)",
                "color": "White"
            }).siblings().css({
                "background-color": "White",
                "color": "#0097C6"
            });
        }

    </script>
</head>
<body style="font-size: small; font-family: 'Microsoft JhengHei',Tahoma,微軟正黑體,'Trebuchet MS' ,Arial ,san-serif; margin-top: 0px; margin-left: 0px; margin-right: 0px;">
    <form id="form1" runat="server">
    <div>
        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="padding-bottom: 50px">
                    <table border="0" cellpadding="2" cellspacing="2" style="border-bottom: #003300 1px solid; background-color: aliceblue; width: 100%">
                        <tr>
                            <td>
                                <table border="0" cellpadding="0" cellspacing="0" style="width: 100%">
                                    <tr>
                                        <td align="left">
                                            <table border="0" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="padding-left: 20px; padding-bottom: 5px; padding-top: 5px">
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td>
                                                                    <asp:Label ID="Label8" runat="server" Font-Bold="True" Font-Size="14pt" ForeColor="Maroon" Text="TGOS WEB API服務資訊"></asp:Label>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-left: 25px; padding-bottom: 5px; padding-top: 5px">
                                                        <asp:Label ID="lbCSWTitle" runat="server" Font-Size="11pt">方法名稱：QueryAddr</asp:Label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-left: 25px; padding-bottom: 5px; padding-top: 5px">
                                                        <asp:Label ID="lbCSWAbstract" runat="server" Font-Size="Small"></asp:Label>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td align="right" style="padding-right: 15px">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div>
        <div align="left" style="padding: 0px 0px 0px 20px">
            <asp:Label ID="LblResult" runat="server"></asp:Label>
            <table border="0" cellspacing="0" cellpadding="0" style="position: absolute;">
                <tr>
                    <td>
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="width: 357px; border-width: thin; border-color: rgb(185, 185, 185); border-bottom: 0px; border-style: solid;">
                                    <div align="left" style="padding: 10px 10px">
                                        <asp:Label ID="Label1" runat="server" Text="門牌"></asp:Label>
                                        <asp:TextBox ID="TxtAddress" runat="server" Width="250px">臺北市中山區松江路469巷4號</asp:TextBox>
                                        <asp:Button ID="BtnQuery" runat="server" Text="查詢" OnClick="BtnQuery_Click" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 357px; border-width: thin; border-color: rgb(185, 185, 185); border-bottom: 0px; border-style: solid; background-color: rgb(230, 206, 206);">
                                    <div id="ItemsData" style="padding: 6px 0px 6px 5px; line-height: 24px; letter-spacing: 3px;">
                                        查詢結果:&nbsp;&nbsp;</div>
                                </td>
                            </tr>
                        </table>
                        <div id="AddrList" align="left" style="width: 357px; height: 635px; border-width: thin; border-color: rgb(185, 185, 185); border-style: solid; overflow: auto;">
                        </div>
                    </td>
                    <td>
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="width: 600px; border-width: thin; border-color: rgb(185, 185, 185); border-bottom: 0px; border-style: solid; background-color: rgb(230, 206, 206);">
                                    <div id="AddressData" style="padding: 7px 0px 7px 5px; line-height: 24px; letter-spacing: 3px;">
                                        目前查詢地址:</div>
                                </td>
                            </tr>
                        </table>
                        <div id="mymap" align="left" style="width: 600px; height: 681px; border-width: thin; border-color: rgb(206, 206, 206); border-style: solid;">
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    </form>
</body>
</html>
