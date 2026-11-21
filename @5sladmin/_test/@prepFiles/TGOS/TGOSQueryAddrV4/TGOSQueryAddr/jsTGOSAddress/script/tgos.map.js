var Locate; //定位課程商品 TGOS.TGLocateService
var marker; //標記課程商品 TGOS.TGMarker
var tgosMap = null; //地圖課程商品 TGOS.TGOnlineMap
var messageBox; //訊息視窗課程商品 TGOS.TGInfoWindow
var clickEvent; //點擊地圖的事件課程商品
var drawing; //繪圖管理課程商品


/*==========================================
地圖初始化
============================================*/
function initMap(elementId) {

    var pOMap = document.getElementById(elementId); //找出展示地圖的 div 元素
    tgosMap = new TGOS.TGOnlineMap(pOMap, TGOS.TGCoordSys.EPSG3826); //宣告 TGOnlineMap 地圖課程商品並設定坐標系統
    marker = new TGOS.TGMarker(tgosMap, new TGOS.TGPoint(0, 0)); //宣告標記課程商品，準備作為顯示訊息使用
    marker.setVisible(false); //先隱藏標記
    Locate = new TGOS.TGLocateService(); //宣告一個TGLocateService課程商品，準備作為定位工具使用

}

/*==========================================
標註標記
============================================*/
function setMarker(x, y) {

    var pt = new TGOS.TGPoint(x, y); //依據所傳入的 x,y 宣告一個點坐標課程商品
    marker.setPosition(pt); //設定標記的坐標
    marker.setVisible(true); //顯示標記
    Locate.setCenter(tgosMap, pt); //移動標記至地圖中心            
    tgosMap.setZoom(12); //設定範圍到適當比例尺

}

/*==========================================
顯示訊息視窗
============================================*/
function showMessageBox(address, x, y) {
    if (messageBox) {
        messageBox.close();
    }
    messageBox = new TGOS.TGInfoWindow("" + address + "</br>" + "坐標X:" + x + "</br>" + "坐標Y:" + y, new TGOS.TGPoint(x, y)); //宣各一個訊息視窗課程商品，並指定顯示位置
    messageBox.setOptions({
        maxWidth: Number(250), //訊息視窗的最大寬度(介於100px~250px之間)
        pixelOffset: new TGOS.TGSize(5, -15),  //訊息視窗的位移量(pixel)
        zIndex: 0//視窗堆疊順序   
    }); //設定訊息視窗顯示屬性
    messageBox.open(tgosMap); //將訊息視窗加入地圖中      

}

/*==========================================
設定點坐標值到指定 INPUT type='text' 元素中
============================================*/
function setPoint(elementIdX, elementIdY) {
    clickEvent = TGOS.TGEvent.addListener(tgosMap, "click", function (e) {//將點擊事件加入地圖事件集合中
        //console.log(e);
        $('#' + elementIdX).val(e.point.x); //取出點的X坐標
        $('#' + elementIdY).val(e.point.y); //取出點的Y坐標
        TGOS.TGEvent.removeListener(clickEvent); //移除上次點擊地圖的事件課程商品
    });

}

/*==========================================
繪圖管理初始化
============================================*/
function initDrawing() {

    drawing = new TGOS.TGDrawing();
    drawing.setMap(tgosMap);

    drawing.setOptions({
        drawingControl: false,
        drawingControlOptions: {
            position: TGOS.TGControlPosition.BOTTOM_RIGHT,
            drawingModes: [TGOS.TGOverlayType.LINESTRING]
        },
        polylineOptions: {
            strokeWeight: 2,
            strokeColor: '#00ff00',
            strokeOpacity: 1
        },
        polygonOptions: {
            fillColor: '#55ff55',
            fillOpacity: 0.5,
            strokeWeight: 2,
            strokeColor: '#008844',
            strokeOpacity: 1
        }
    });
}

/*==========================================
讓使用者進行繪製線的動作，並將坐標集合設定到指定 textArea 元素中
============================================*/
function setLine(elementId) {
    $("path").remove();//移除上一次繪製結果
    TGOS.TGEvent.addListener(drawing, 'linestring_complete', function (e) {
        var aPaths = e.overlay.getPath().getPath(); //取出線的路徑，路徑是由點組成，所以是一個坐標集合的陣列
        var aPathStr = "";//宣告一個存放點集合資料的變數
        if (aPaths.length > 0) {
            for (var i = 0; i < aPaths.length; i++) {//一個一個取出點位資料，並設定到變數 aPathStr
                aPathStr += (aPaths[i].x.toFixed(3) + " " + aPaths[i].y.toFixed(3) + ",");
            }
            aPathStr += (aPaths[0].x.toFixed(3) + " " + aPaths[0].y.toFixed(3));
        }
        $('#' + elementId).val(aPathStr);//設定點集合字串到指定的元素中
        drawing.removeLEvent();//移除本身事件
    }); //宣告一個地圖繪製事件，就是當線繪製完成後要作什麼事
    drawing.enterLineStrMode();//啟動繪製線的功能

}

/*==========================================
讓使用者進行繪製面的動作，並將坐標集合設定到指定 textArea 元素中
============================================*/
function setPolygon(elementId) {

    $("path").remove();//移除上一次繪製結果
    TGOS.TGEvent.addListener(drawing, 'polygon_complete', function (e) {
        var aPaths = e.overlay.getPath().getExteriorRing().getPath().getPath(); //取出面的路徑，路徑是由點組成，所以是一個坐標集合的陣列
        var aPathStr = ""; //宣告一個存放點集合資料的變數
        if (aPaths.length > 0) {            
            for (var i = 0; i < aPaths.length; i++) {//一個一個取出點位資料，並設定到變數 aPathStr
                aPathStr += (aPaths[i].x.toFixed(3) + " " + aPaths[i].y.toFixed(3) + ",");
            }
            aPathStr += (aPaths[0].x.toFixed(3) + " " + aPaths[0].y.toFixed(3));
        }
        $('#' + elementId).val(aPathStr); //設定點集合字串到指定的元素中
        drawing.removePEvent(); //移除本身事件
    }); //宣告一個地圖繪製事件，就是當面繪製完成後要作什麼事
    drawing.enterPolyMode(); //啟動繪製面的功能

}