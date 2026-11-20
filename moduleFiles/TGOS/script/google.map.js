var marker; //標記課程商品 TGOS.TGMarker
var tgosMap = null; //地圖課程商品 TGOS.TGOnlineMap
var messageBox; //訊息視窗課程商品 TGOS.TGInfoWindow

/*==========================================
地圖初始化
============================================*/
function initMap(elementId) {

    var pOMap = document.getElementById(elementId); //找出展示地圖的 div 元素
    tgosMap = new google.maps.Map(pOMap, {//地圖初始化變數
        zoom: 10, //縮放等級
        center: new google.maps.LatLng(25.0662921772, 121.5341788032), //地圖中心
        mapTypeId: google.maps.MapTypeId.ROADMAP//地圖類型
    }); //宣告一個地圖課程商品

}

/*==========================================
標註標記
============================================*/
function setMarker(x, y) {

    if (marker) {
        marker.setMap(null); //先清除上一次標記
    }
    marker = new google.maps.Marker({//標記點
        position: new google.maps.LatLng(y, x), //經緯度， x=Longitude(經度、東經)、y=Latitude(緯度、北緯)
        map: tgosMap //指定要放置的地圖對象
    });

    tgosMap.setCenter(new google.maps.LatLng(y, x)); //移動標記至地圖中心 
    tgosMap.setZoom(18); //設定範圍到適當比例尺

}

/*==========================================
顯示訊息視窗
============================================*/
function showMessageBox(address, x, y) {

    messageBox = new google.maps.InfoWindow(); //宣各一個訊息視窗課程商品，並指定顯示位置
    messageBox.setContent("" + address + "</br>" + "經度:" + x + "</br>" + "緯度:" + y);
    messageBox.open(tgosMap, marker); //將訊息視窗加入地圖中

}