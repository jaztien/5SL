<%@ Page Language="C#" AutoEventWireup="true" CodeFile="QueryAddrGoogleMap.aspx.cs" Inherits="Address_Sample01" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
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
    <!-- 
         上述程式碼引用Jquery
         googlemap引用api,未申請api Key 情況下,每日限定搜尋2500次。

         未設定sensor,會發生執行錯誤,這裡的sensor 指的是感應器，例如GPS定位器
         如果你希望在行動裝置去判斷使用者的位置,那請將sensor設定為true。

         來自於 google網站說明: 請注意，即使您的應用程式沒有使用位置感應器，您仍必須設定
         sensor 參數 (在此情況下，請設定為 false)。
    
    -->
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">

        window.onload = function () {
            $('#LblResult').hide(); //隱藏

            var aJson = $.parseJSON($('#LblResult').text());
            if (aJson.AddressList.length == 0) {
                alert("輸入查詢地址錯誤,請重新輸入,查詢格式為:縣市,區,路名");
                location.reload(); //頁面刷新
            }

            var X = aJson.AddressList[0].X; //預設X坐標
            var Y = aJson.AddressList[0].Y; //預設Y坐標

            var latlng = new google.maps.LatLng(Y, X); //地圖中心經度,緯度
            //變數是小寫latlng， new後面的LatLng 的L是大寫!!!

            var myOptions = {//放入變數
                zoom: 18, //縮放等級
                center: latlng, //地圖中心
                mapTypeId: google.maps.MapTypeId.ROADMAP//地圖類型

            };
            //建立google.maps.Map - 基本課程商品，定義網頁上的單一地圖
            var map = new google.maps.Map(document.getElementById("mymap"), myOptions); //地圖課程商品


            var marker = new google.maps.Marker({//標記點
                position: latlng, //經緯度
                map: map //指定要放置的地圖對象
            });

            infowindow = new google.maps.InfoWindow(); //创建一个提示框，变量必须全局变量，否则函数无法使用
            infowindow.setContent("" + address + "</br>" + "坐標X:" + X + "</br>" + "坐標Y:" + Y); //设置提示框内容
            infowindow.open(map, marker); //地图上展现



            $("#ItemsData").append("<label >" + '總共有' + aJson.AddressList.length + '筆' + "</label>");  //插入label
            $("#AddressData").append("<label >" + aJson.AddressList[0].FULL_ADDR + "</label>"); //插入label


            if (aJson) {//如果aJson存在
                for (var i = 0; i < aJson.AddressList.length; i++) {
                    var address = aJson.AddressList[i].FULL_ADDR; //地址
                    var X = aJson.AddressList[i].X; //x坐標
                    var Y = aJson.AddressList[i].Y; //y坐標

                    $("#AddrList").append("<span class=\"rowStyle\" onclick=\"CallGoogleMap(" + X + "," + Y + ",'" + address + "',this);return false;\">" + address + "</span></br>");
                    //插入span
                }
                var DefaultAddress = $('.rowStyle');
                if (DefaultAddress.length > 0) {
                    $(DefaultAddress.get(0)).click();
                }
            }

        };

        function CallGoogleMap(X, Y, address, oSpan) {

            var latlng = new google.maps.LatLng(Y, X); //地圖中心經度,緯度
            //變數是小寫latlng， new後面的LatLng 的L是大寫!!!

            var myOptions = {//放入變數
                zoom: 18, //縮放等級
                center: latlng, //地圖中心
                mapTypeId: google.maps.MapTypeId.ROADMAP//地圖類型

            };
            //建立google.maps.Map - 基本課程商品，定義網頁上的單一地圖
            var map = new google.maps.Map(document.getElementById("mymap"), myOptions); //地圖課程商品


            var marker = new google.maps.Marker({//標記點
                position: latlng, //經緯度
                map: map //指定要放置的地圖對象
            });


            infowindow = new google.maps.InfoWindow(); //创建一个提示框，变量必须全局变量，否则函数无法使用
            infowindow.setContent("" + address + "</br>" + "坐標X:" + X + "</br>" + "坐標Y:" + Y); //设置提示框内容
            infowindow.open(map, marker); //地图上展现



            $("#AddressData").empty(); //地址存在label清空


            $("#AddressData").append("<label >" + '目前查詢地址:' + address + "</label>");

            $(oSpan).css({
                "background-color": "rgb(112, 207, 236)",
                "color": "White"
            }); //點擊之後改變的css

            $(oSpan).siblings().css({
                "background-color": "White",
                "color": "#0097C6"
            }); //其他元素css


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
                                        <asp:TextBox ID="TxtAddress" runat="server" OnTextChanged="TxtAddress_TextChanged" Width="250px">臺北市中山區松江路469巷4號</asp:TextBox>
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
                        <div id="AddrList" align="left" style="width: 357px; height: 636px; border-width: thin; border-color: rgb(185, 185, 185); border-style: solid; overflow: auto;">
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
