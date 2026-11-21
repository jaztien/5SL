<?PHP
phpinfo();
exit();
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>TimeSheet example</title>

    <style>
        body{
            font-size:12px;
            color:#777777;
        }
        label{
            width:80px;
            display:inline-block;
        }
        .diagonal-line {
            background-image: linear-gradient(to top right,
            white 49%,
            black,
            white 51%);
        }
    </style>
  <!-- Theme style -->
    <link rel="stylesheet" href="../../cssFiles/5sladmin.light.css">
    <link rel="stylesheet" href="./css/TimeSheet.css?<?php echo time(); ?>" type="text/css" media="screen">
</head>
<body>

<div style="padding:15px 0 10px;">
    <button class="J_sheetControl" id="J_timingDisable">Disable</button>
    <button class="J_sheetControl" id="J_timingEnable">Enable</button>
    <button class="J_sheetControl" id="J_timingClean">Clean</button>
    <button class="J_sheetControl" id="J_timingSubmit">Submit</button>
    <button class="J_sheetControl" id="J_timingIsFull">IsFull</button>
<!--
    <div style="padding:15px 0 5px;">
        <label>單元格索引:</label><input type="text" placeholder="0,0" id="J_cellIndex" value="0,0"/> <button class="J_sheetControl" id="J_timingGetCell">GetCell</button>
    </div>
    <div style="padding:10px 0;">
        <label>行索引:</label><input type="number" placeholder="0" id="J_rowIndex" value="0"/>  <button class="J_sheetControl" id="J_timingGetRow">GetRow</button>
    </div>
-->
</div>
<div id="J_calenderWrapper">
    <table>
        <thead></thead>
        <tbody id="J_timedSheet">

<!--
            <tr>
                <td class="TimeSheet-head" style="color:chocolate;">日期\\時間</td>
                <td title="07:00-08:00" data-col="0" class="TimeSheet-colHead" style="">07</td>
                <td title="07:00-08:00" data-col="1" class="TimeSheet-colHead" style="">08</td>
                <td title="07:00-08:00" data-col="2" class="TimeSheet-colHead" style="">09</td>
                <td title="07:00-08:00" data-col="3" class="TimeSheet-colHead" style="">10</td>
                <td title="07:00-08:00" data-col="4" class="TimeSheet-colHead" style="">11</td>
                <td title="07:00-08:00" data-col="5" class="TimeSheet-colHead" style="">12</td>
                <td title="07:00-08:00" data-col="6" class="TimeSheet-colHead" style="">13</td>
                <td title="07:00-08:00" data-col="7" class="TimeSheet-colHead" style="">14</td>
                <td title="07:00-08:00" data-col="8" class="TimeSheet-colHead" style="">15</td>
                <td title="07:00-08:00" data-col="9" class="TimeSheet-colHead" style="">16</td>
                <td title="07:00-08:00" data-col="10" class="TimeSheet-colHead" style="">17</td>
                <td title="07:00-08:00" data-col="11" class="TimeSheet-colHead" style="">18</td>
                <td title="07:00-08:00" data-col="12" class="TimeSheet-colHead" style="">19</td>
                <td title="07:00-08:00" data-col="13" class="TimeSheet-colHead rightMost" style="">20</td>
                <td class="TimeSheet-remarkHead">說明</td>
            </tr>

            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-01</td>
                <td class="TimeSheet-cell" data-row="0" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="0" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="0" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>

            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-02</td>
                <td class="TimeSheet-cell" data-row="1" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="1" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="1" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-03</td>
                <td class="TimeSheet-cell" data-row="2" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="2" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="2" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-04</td>
                <td class="TimeSheet-cell" data-row="3" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="3" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="3" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-05</td>
                <td class="TimeSheet-cell" data-row="4" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="4" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="4" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-06</td>
                <td class="TimeSheet-cell" data-row="5" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="5" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="5" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-07</td>
                <td class="TimeSheet-cell" data-row="6" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="6" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="6" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-08</td>
                <td class="TimeSheet-cell" data-row="7" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="7" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="7" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-09</td>
                <td class="TimeSheet-cell" data-row="8" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="8" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="8" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-10</td>
                <td class="TimeSheet-cell" data-row="9" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="9" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="9" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-11</td>
                <td class="TimeSheet-cell" data-row="10" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="10" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="10" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-12</td>
                <td class="TimeSheet-cell" data-row="11" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="11" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="11" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead" style="">2015-09-13</td>
                <td class="TimeSheet-cell" data-row="12" data-col="0"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="1"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="2"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="3"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="4"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="5"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="6"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="7"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="8"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="9"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="10"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="11"></td>
                <td class="TimeSheet-cell" data-row="12" data-col="12"></td>
                <td class="TimeSheet-cell rightMost" data-row="12" data-col="13"></td>
                <td class="TimeSheet-remark">未設置</td>
            </tr>
            <tr class="TimeSheet-row">
                <td title="" class="TimeSheet-rowHead bottomMost" style="">2015-09-14</td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="0"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="1"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="2"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="3"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="4"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="5"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="6"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="7"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="8"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="9"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="10"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="11"></td>
                <td class="TimeSheet-cell bottomMost" data-row="13" data-col="12"></td>
                <td class="TimeSheet-cell bottomMost rightMost" data-row="13" data-col="13"></td>
                <td class="TimeSheet-remark bottomMost">未設置</td>
            </tr>

-->

<!--<tr class="TimeSheet-row">
    <td title=""class="TimeSheet-rowHead bottomMost " style="">2015-09-14</td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="0"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="1"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="2"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="3"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="4"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="5"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="6"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="7"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="8"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="9"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="10"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="11"></td>
    <td class="TimeSheet-cell bottomMost " data-row="13" data-col="12"></td>
    <td class="TimeSheet-cell bottomMost rightMost" data-row="13" data-col="13"></td>
    <td class="TimeSheet-remark bottomMost ">未設置</td>
</tr>>
-->

        </tbody>
    </table>
    <p style="color:#757a7d;font-size:12px;padding:10px 0;">Hint：以左鍵選擇為選定，以右鍵選擇為取消選定，單元格與列首可通過右鍵點擊或框選來取消選擇。</p>
</div>

<p id="J_dataDisplay" style="color:#aaaaaa;font-family: 'Arial';">

</p>

<script src="../../pluginFiles/jquery/jquery.min.js"></script>
<!-- <script type="text/javascript" src="./js/jquery-1.11.3.min.js"></script>> -->
<script type="text/javascript" src="./js/TimeSheet.js?<?php echo time(); ?>"></script>
<script type="text/javascript">
    //var currTimeStamp = Math.floor(Date.now() / 1000);
    //var currTimeStamp = Date.now();
    //alert(currTimeStamp);
    /*
    const weekDay = ["星期日","星期一","星期二","星期三","星期四","星期五","星期六"];
    const weekDayS = ["(日)","(一)","(二)","(三)","(四)","(五)","(六)"];
    const currDay = new Date();
    let nowDay = weekDay[currDay.getDay()];
    */
    var testData = {
        /*
            "2024-01-31": {
              "value": [ 0,0,0,0,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0,0 ]
            },
            "2024-02-01": {
              "value": [ 1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0 ]
            },
            */
            "2024-02-02": {
              "value": [
                // [row, col, check], 行, 列, 是否選取(會在上面的應該都是已勾選，可以考慮不用帶)
                [[0, 0], [3, 3],1],
                [[0, 0], [20, 28],4],
              ]
            },
            "2024-02-06": {
              "value": [
                [[1, 1], [4, 6],2],
                [[1, 1], [12, 12],58],
              ]
            },
            "2024-02-08": {
              "value": [
                [[2, 2], [15, 19],0],
              ]
            },

          }


    function getMonday(d) {
        d = new Date(d);
        var day = d.getDay(),
            diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
            //return new Date(d.setDate(diff));
            return dateFormater(new Date(d.setDate(diff)));
    }
/*
    function dateFormater(date) {
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        if (day < 10) { day = '0' + day; }
        if (month < 10) { month = '0' + month; }
        return year + '-' + month + '-' + day;
    }
*/

    function getDayList(modE='p',sdate='',edate='') { // ex. "m"== 當月整月，"w"==
        var date = new Date();
        var month = date.getMonth();
        var all_days = [];

        if(modE==='p'){



        } else if(modE==='m'){
            date.setDate(1);
            while (date.getMonth() == month) {
                var currdate = date.getFullYear() + '-' + (date.getMonth()+1).toString().padStart(2, '0') + '-' + date.getDate().toString().padStart(2, '0');
                all_days.push({name: currdate, weeknum: date.getDay(currdate),title: currdate});
                date.setDate(date.getDate() + 1);
            }
        } else if(modE.substr(-1)==='w'){
            var getPeriod = parseInt(modE.slice(0, 1))*7;//以第一個數值決定排程天數，例如：“3w”為3週共21天
            var day = date.getDay();
            var prevSun = date.getDate() - day + (day == 0 ? -6 : 1) -1; // 今日之前的星期日
            //var startDate = new Date(date.setDate(prevSun));
            const twoWeeksLater = new Date(date.getTime() + 1000 * 60 * 60 * 24 * getPeriod);
            for (let d = date; d < twoWeeksLater; d.setDate(d.getDate() + 1)) {
                var currdate = d.getFullYear() + '-' + (d.getMonth()+1).toString().padStart(2, '0') + '-' + d.getDate().toString().padStart(2, '0');
                all_days.push({name: currdate, weeknum: d.getDay(currdate),title: currdate});
            }
        }
        return all_days;
    }

    function getHourList(hL){
        var hList=[];
        for(var i=hL[0]; i<hL[1]; i++){
            var currHr = i.toString().padStart(2, '0');
            var currNHr = (i+1).toString().padStart(2, '0'); 
            hList.push({name: currHr+':00', title: currHr+':00~'+currHr+':30'});
            hList.push({name: currHr+':30', title: currHr+':30~'+currNHr+':00'});
        }
        return hList;
    }

    var dayList = getDayList('4w');
    //var dimensions = [14,28];//[日期,時間]
    var hourrange = [7,21];//[【起始時數】,【終止時數】]
    var dimensions = [dayList.length,(hourrange[1]-hourrange[0])*2];//[日期,時間]
    var hourList = getHourList(hourrange);

    /*
    var dayList = [
        {name:"2024-02-01",weeknum:1},
        {name:"2024-02-02",weeknum:2},...
    ];

    var hourList = [
    {name:"07:00",title:"07:00-07:30"},
    {name:"07:30",title:"07:30-08:00"},...
    ];
    */



   // var sheetData = [];

    var sheetData = [
        [],
        [ 0,0,0,0,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0,0 ],
        [ 0,0,0,0,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0,0 ],
        [],
        [],
        [ 0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0 ],
        [ 0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0 ],
/*
        [ 0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0 ],
        [ 0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0 ],
        [ 0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0 ],
        [ 1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0 ],
*/
        [ 1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0 ],
        [ 0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0 ],
        [ 0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0 ],
        [ 0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0 ],
        [ 0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0 ],
        [ 0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0 ],
        [ 0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0 ],
        []
    ];

    var updateRemark = function(sheet){

        var sheetStates = sheet.getSheetStates();
        var rowsCount = dimensions[0];
        var colsCount = dimensions[1];
        var rowRemark = [];
        var rowRemarkLen = 0;
        var remarkHTML = '';

        for(var row= 0, rowStates=[]; row<rowsCount; ++row){
            rowRemark = [];
            rowStates = sheetStates[row];
            for(var col=0; col<colsCount; ++col){
                if(rowStates[col]===0 && rowStates[col-1]===1){
                    rowRemark[rowRemarkLen-1] += ((col+7)<=10?'0':'')+(col+7)+':00';
                }else if(rowStates[col]===1 && (rowStates[col-1]===0 || rowStates[col-1]===undefined)){
                    rowRemarkLen = rowRemark.push(((col+7)<=10?'0':'')+(col+7)+':00-');
                }
                if(rowStates[col]===1 && col===colsCount-1){
                    rowRemark[rowRemarkLen-1] += '24:00';
                }
            }
            remarkHTML = rowRemark.join("，");
            sheet.setRemark(row,remarkHTML==='' ? sheet.getDefaultRemark() : remarkHTML);
        }
    };

    $(document).ready(function(){

        var sheet = $("#J_timedSheet").TimeSheet({
            data: {
                dimensions : dimensions,
                hourrange : hourrange,
                colHead : hourList,
                rowHead : dayList,
                dayList : dayList,
                sheetHead : {uppername:"時間",lowername:"日期"},
                sheetData : sheetData,
                testData : testData,
                mbid : '1'
            },
            remarks : {
                title : "時間段",
                default : ""
            },
            end : function(ev,selectedArea){
                updateRemark(sheet);
            }
        });

        updateRemark(sheet);

        $("#J_timingDisable").click(function(ev){
            sheet.disable();
        });

        $("#J_timingEnable").click(function(ev){
            sheet.enable();
        });

        $("#J_timingClean").click(function(ev){
            sheet.clean();
        });

        $("#J_timingSubmit").click(function(ev){

            var sheetStates = sheet.getSheetStates();
            var rowsCount = dimensions[0];
            var $submitDataDisplay = $("#J_dataDisplay") ;
            
            $submitDataDisplay.html("<b>Raw Data Submitted:</b><br/>"+JSON.stringify(sheetStates));

            /*
            $submitDataDisplay.html("<b>Raw Data Submitted:</b><br/>[<br/>");

            for(var row= 0, rowStates=[]; row<rowsCount; ++row){
                rowStates = sheetStates[row];
                $submitDataDisplay.append('&nbsp;&nbsp;[ '+rowStates+' ]'+(row==rowsCount-1?'':',')+'<br/>');
            }

            $submitDataDisplay.append(']');
            */
        });

        $("#J_timingIsFull").click(function(ev){
            alert(sheet.isFull());
        });

        $("#J_timingGetCell").click(function(ev){

            var cellIndex = $("#J_cellIndex").val().split(',');
            var cellData = sheet.getCellState(cellIndex);
            var $dataDisplay = $("#J_dataDisplay") ;

            $dataDisplay.html("<b>Cell Data At ["+cellIndex+"] : </b>"+cellData);
        });

        $("#J_timingGetRow").click(function(ev){
            var rowIndex = $("#J_rowIndex").val();
            var rowData = sheet.getRowStates(rowIndex);
            var $dataDisplay = $("#J_dataDisplay") ;

            $dataDisplay.html("<b>Row Data At "+rowIndex+" : </b>[ "+rowData+" ]");
        });
    });
</script>

</body>
</html>