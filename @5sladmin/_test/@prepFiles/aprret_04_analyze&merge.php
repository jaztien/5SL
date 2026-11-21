<?php
    error_reporting(E_ALL); 
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
    header("Content-Type:text/html; charset=utf-8");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life </title>
  <style>
    .readonly{
        height:18px;
        background-color: #EEEEEE;
        color: #999999;
        border: #666666 1px solid;
    }
    #timer{
        font-size: 75px;
        font-weight: bold;
    }
    #control>#startbtn{
        background-color:green;
        color:white;
    }
    #control>#pausebtn{
        background-color:blue;
        color:white;
    }
    #control>#resumebtn{
        background-color:orange;
        color:white;
    }
    #control>#resetbtn{
        background-color:red;
        color:white;
    }
  </style>
  </head>
    <body style="margin:10px;">
        <div id="loading" style="right:24px;position:absolute;display:block;width:100%;height:100%;;background-image: url('loading.gif');background-color:#2A2826;background-repeat: no-repeat;background-position: 50% 0;background-size:contain;opacity: 0.75;margin:-10px;text-align: center; z-index:99;">
            <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
                <div id="loadingtext" style="font-size:24px;">進行中......</div>
                <div id="timer"><span id="minutes">00</span>:<span id="seconds">00</span></div>
                <div><button id="loopbut" style="height:32px;width:120px;cursor:pointer;" onClick="doStopLoop()">Start Loop</button></div>
            </div>
        </div>
        <div style="font-size:16px;padding:0 24px 0 24px;">
            <hr>
        <form id="form_anamerge" name="form_anamerge" action="aprret_04_iframe.php" target="bcIframe" method="post">
            <table>
                <tr>
                    <td style="padding-right:20px;border-right:#333333 1px solid;">
                        起始ID: <input type="text" id="fromID" name="fromID" style="width:80px;" value="" autocomplete="off"><br><br>
                        終止ID: <input type="text" id="toID" name="toID" style="width:80px;" value="" autocomplete="off">
                    </td>
                    <td style="padding-left:20px;padding-right:20px;border-right:#333333 1px solid;">
                        年份<br><br> <input type="text" id="folderr" name="folderr" style="width:80px;" value="" autocomplete="off">&nbsp;年
                    </td>
                    <td style="padding-left:20px;padding-right:20px;border-right:#333333 1px solid;">
                        <input type="checkbox" id="oktobox" name="oktobox" value="1">寫入<br><br><input type="checkbox" id="lockrowbox" name="lockrowbox" value="locked" onclick="lockrow();" checked>鎖定
                    </td>
                    <td style="padding-left:20px;">
                        <input type="checkbox" id="doloop" name="doloop" onClick="doLoopCheck()" value="1" disabled>循環&nbsp;&nbsp;
                        <input type="checkbox" id="donealert" name="donealert" value="1" checked>聲音<br>
                        <button type="button" id="loopbutTop" onClick="doStopLoop()" style="height:36px;width:120px;cursor:pointer;">STOP LOOP</button>
                    </td>
                    <td style="padding-left:40px;">
                        <button type="button" onClick="doAction();" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">ACTION</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_04_analyze&merge.php';">RESET</button>
                        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
                    </td>
                </tr>
            </table>
        </form>
        <hr>
        </div>
        <iframe id="bcIframe" name="bcIframe" src="aprret_04_iframe.php" style="position:fixed; top:106px; left:0; bottom:0; right:0; width:100%; margin:0; padding:0; z-index:9;border:none;overflow:visible;"></iframe>
        <script> 
            window.onload = function() {
                var iframe = document.getElementById("bcIframe");
            // iframe.style.width = iframe.contentWindow.document.body.scrollWidth + 'px';
                iframe.style.height = (iframe.contentWindow.document.body.scrollHeight+580 )+ 'px';
            }
            //Clock.startTimer();Clock.pauseTimer();Clock.resumeTimer();Clock.resetTimer();
            var Clock = {
                totalSeconds: 0,
                startTimer: function() {
                    if (!this.interval) {
                    var self = this;
                    function pad(val) {
                        return val > 9 ? val : '0' + val;
                    }
                    this.interval = setInterval(function() {
                        self.totalSeconds += 1;
                        document.getElementById('minutes').innerHTML =
                            pad(Math.floor(self.totalSeconds / 60 % 60));
                        document.getElementById('seconds').innerHTML =
                            pad(parseInt(self.totalSeconds % 60));
                    }, 1000);
                    }
                },
                resetTimer: function() {
                    Clock.totalSeconds = null;
                    clearInterval(this.interval);
                    document.getElementById('minutes').innerHTML = '00';
                    document.getElementById('seconds').innerHTML = '00';
                    delete this.interval;
                },
                pauseTimer: function() {
                    clearInterval(this.interval);
                    delete this.interval;
                },
                resumeTimer: function() {
                    this.startTimer();
                },
            };

            function doAction(){
                var loopAlert='';
                if(document.getElementById("doloop").checked){
                    loopAlert+='請注意！這個操作包含自動循環截取，';
                }
                if(document.getElementById("oktobox").checked){
                    loopAlert+='以及資料庫寫入，';
                }
                if(!confirm(loopAlert+'確定執行這個操作？！')){ return false; }
                document.getElementById('loading').style.display = "block";
                Clock.startTimer();
                document.getElementById("form_anamerge").submit();
            }
            function doNext(){
                //var currPageS = parseInt(document.getElementById('fromID').value);
                //var currPageE = parseInt(document.getElementById('toID').value);
                var nextPageS = currPageE + 1;
                var nextPageE = currPageE - currPageS + nextPageS;
                //document.getElementById('spage').value = nextPageS;
                //document.getElementById('epage').value = nextPageE;
                doNextAction();

            }
            function doNextAction(){
                document.getElementById('loadingtext').innerHTML = "進行中......";
                document.getElementById('loadingtext').style.color = "#FFFFFF";
                document.getElementById('loading').style.display = "block";
                Clock.startTimer();
                document.getElementById("form_anamerge").submit();
            }
            function doStopLoop(){
                var delm=document.getElementById("doloop");
                if(delm.checked == true){
                    document.getElementById("doloop").checked = false;
                    document.getElementById("loopbutTop").innerHTML = 'START LOOP';
                    document.getElementById("loopbut").innerHTML = 'Start Loop';
                    document.getElementById('bcIframe').contentWindow.stopNext();
                } else {
                    document.getElementById("doloop").checked = true;
                    document.getElementById("loopbutTop").innerHTML = 'STOP LOOP';
                    document.getElementById("loopbut").innerHTML = 'Stop Loop';
                }
            }
            function doLoopCheck(){
                var elm = document.getElementById("doloop");
                if(elm.checked==true){
                    document.getElementById("loopbutTop").innerHTML = 'STOP LOOP';
                    document.getElementById("loopbut").innerHTML = 'Stop Loop';
                } else {
                    document.getElementById("loopbutTop").innerHTML = 'START LOOP';
                    document.getElementById("loopbut").innerHTML = 'Start Loop';
                }

            }
        </script>
    </body>
</html>

