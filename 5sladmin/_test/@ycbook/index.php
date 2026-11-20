<!doctype html>
<html lang="en">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="0">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta charset='utf-8'>
<head>
<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<!-- META ===================================================== -->
    <title></title>
    <meta name="description" content="">

<!-- Favicon  ========================================== -->
	<link rel="icon" href="images/yc2025.ico" type="image/ico" />

<!-- CSS ======================================================
	<link rel="stylesheet" href="css/responsivetables.css">-->
	<!-- Demo CSS (don't use) -->
	<!-- <link href="https://fonts.googleapis.com/css?family=Yanone+Kaffeesatz" rel="stylesheet" type="text/css"> -->
	<style type="text/css">
		body, html {
			padding:0px;
			margin:0px;
			background: url('images/yc2025.jpg') no-repeat left top fixed;
			background-size: auto;
			/*background-attachment: fixed;*/
			text-align:center;
			color:#fff;
			line-height: 1.4em;
			font-family: "Trebuchet MS", Helvetica, sans-serif;
		}
		body {
			/*padding:10vh 0;*/
			padding: 0;
		}
		h1 {
			font-family: 'Yanone Kaffeesatz', sans-serif;
			text-align: center;
			font-size: 77px;
			text-shadow: 0 0px 30px rgba(0, 0, 0, 0.2);
		}
		h2 {
			font-family: 'Yanone Kaffeesatz', sans-serif;
			font-size:30px;
			text-shadow: 0 0px 20px rgba(0, 0, 0, 0.3);
			color:#fff;
		}
		.monthly {
			box-shadow: 0 13px 40px rgba(0, 0, 0, 0.65);
			font-size: 0.8em;
		}
		.mainCal {
			opacity: 0.9;
			width:100%;
			max-width:1024px;
			display:inline-block;
			padding:0 24px 0  24px;
		}

		.button {
			display: inline-block;
			padding: 15px 25px;
			margin: 25px 0 75px 0;
			border-radius: 3px;
			color: #fff;
			background: #000;
			letter-spacing: .4em;
			text-decoration: none;
			font-size: 13px;
		}
		.button:hover {
			background: #3b587a;
		}
		.desc {
			width: 100%;
			text-align: left;
			font-size:1em;
			/*padding-top:30px;*/
			padding: 8px;
			margin: 0;
			line-height: 1.6em;
			background-color: #555555;
			color: #fff;
		}
		.desc_g_mark {
			background-color: #009933;
		}
		.desc_y_mark {
			background-color: #cca300;
		}
		.desc_r_mark {
			background-color: #cc0000;
		}
		.resize {
			background: #222;
			display: inline-block;
			padding: 6px 15px;
			border-radius: 22px;
			font-size: 13px;
		}
		@media (max-height: 700px) {
			.mainCal {
				padding: 0px;
			}
			.sticky {
				position: relative;
			}
		}
		@media (max-width: 1036px) {
			.mainCal {
				padding: 0px;
			}
			.resize {
				display: none;
			}
		}
	</style>
	<link rel="stylesheet" href="css/monthly.css<?php echo '?t='.time(); ?>">
</head>
<body>
<div class="page">
		<h2>2025原創音樂大賽 - <span style="font-size:0.85em">後製錄音空檔時程表</span></h2>
		<!--<h4>各組錄音時段選擇用，採用先選先得制</h4>-->
		<div class="mainCal">
			<div class="monthly" id="mycalendar"></div>
			
		</div>
		<!--
		<br><br>
		<h2>Example Two - Date Picker</h2>
		<div style="display:inline-block; width:250px;">
			<input type="text" id="mytarget" value="Select Date">
			<div class="monthly" id="mycalendar2"></div>
		</div>
		<br>
		<a href="https://github.com/kthornbloom/Monthly" class="button">DOWNLOAD / DOCS</a>
		-->
</div>
<!-- JS ======================================================= -->
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/monthly.js<?php echo '?t='.time(); ?>"></script>
<script type="text/javascript">
	$(window).load( function() {

		$('#mycalendar').monthly({
			weekStart: 'Mon',
			mode: 'event',
			eventList: true,
			jsonUrl: 'events.json<?php echo '?t='.time(); ?>',
			dataType: 'json'
			//xmlUrl: 'events.xml'
		});
		/*
		$('#mycalendar2').monthly({
			mode: 'picker',
			target: '#mytarget',
			setWidth: '250px',
			startHidden: true,
			showTrigger: '#mytarget',
			stylePast: true,
			disablePast: true
		});

		switch(window.location.protocol) {
			case 'http:':
			case 'https:':
			// running on a server, should be good.
			break;
			case 'file:':
			alert('Just a heads-up, events will not work when run locally.');
		}
		*/
			// JAZFIX:新增一個說明欄在月曆日期區域下方
			$(".monthly-day").parent().parent().parent().append("<div"
				+ " class=\"desc\">"
				+ "<span class=\"desc_g_mark\">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;綠色時段為優先選擇時段。&nbsp;&nbsp;"
				+ "<span class=\"desc_y_mark\">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;黃色時段為優先時段無法選擇時的次要選擇時段。&nbsp;&nbsp;"
				+ "<span class=\"desc_r_mark\">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;紅色時段為已被選擇時段。"
				+ "<span></span>請選擇好時段後以 Line 群組告知田老師。"
				+ "<br><span></span>** 請注意，以上的空檔時段並非為已預定的錄音間時段，預定錄音間在選擇時段之後後始得進行，所以有可能會出現選擇的時段無錄音間可使用，此情形下當重新選擇及協調。"
				+ "</div>");


		
	});
</script>
</body>
</html>
