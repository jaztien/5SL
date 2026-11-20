<!-- ######################################################### -->
<!-- #################### 左側導航區嵌入檔案 #################### -->
<!-- ######################################################### -->
<?php
$admJS=_fn_getCachedJsonData("../jsonFiles/adminpage.json");
$sidebar = $admJS['sidebar'];
$sideBarIDX = array();
$ii = 0;
while($ii<count($sidebar)){
  $sideBarIDX[$sidebar[$ii]['pagecode']]=$ii;
  $ii++;
}
//--------------- 圖文模版相關待辦案件數量 index: 4 -------------//
$sIDX = $sideBarIDX['ITEM_ARTI'];
$getNum = $DB->single("SELECT COUNT(arti_id) FROM fh.articles WHERE arti_status=?",array(6));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='badge-warning';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-warning';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
//--------------- 未讀訊息數量 index: 5 -------------//

$sIDX = $sideBarIDX['ITEM_MESS'];
$getNum = $DB->single("SELECT COUNT(chat_id) FROM fh.data_chats WHERE chat_type = ? AND chat_posttype=? AND chat_published > ? AND chat_status=?",array(1,2,(time()-(86400*180)),2));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='badge-danger';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-danger';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;

//--------------- 服務相關待辦案件數量 index: 6 -------------//
$sIDX = $sideBarIDX['ITEM_SERV'];
$getNum = $DB->single("SELECT COUNT(sv_id) FROM fh.sv_service WHERE sv_status IN (?,?,?)",array(6,7,8));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='badge-danger';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-danger';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
//--------------- 廣告相關待辦案件數量 index: 7 -------------//
$sIDX = $sideBarIDX['ITEM_AD'];
$getNum = $DB->single("SELECT COUNT(advp_id) FROM fh.advi_pack WHERE advp_status=?",array(6));
$getNum1 = $DB->single("SELECT COUNT(advi_id) FROM fh.advitising WHERE advi_status=?",array(6));
$getSum = $getNum + $getNum1;
$sidebar[$sIDX]['badgeClass']='badge-warning';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-warning';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
$sidebar[$sIDX]['subgroup'][1]['badgeClass']='badge-warning';
$sidebar[$sIDX]['subgroup'][1]['badgeNum']=$getNum1;

?>

<!-- 左側導覽區內容 -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- 左上方主圖標示 Logo -->
    <a href="<?php echo DOC_PATH_LAYER; ?>5sladmin.php" class="brand-link" style="height: 53px;">
      <img src="../imageFiles/5SeasonLifeLOGO.png" alt="5SeasonLife Logo" class="brand-image elevation-3"  style="vertical-align:baseline;margin-top:-4px;">
      <div class="brand-text font-weight-bold" style="margin-top:7px;margin-bottom:-12px; ">Fifth Season Life<span class="brand-text font-weight-light"> ADMIN</span></div>
    </a>
  <!-- /左上方主圖標示 Logo -->

  <!-- 功能區塊 -->
    <div class="sidebar">
    <!-- 安全停滯倒數 -->
      <div class="user-panel mt-3 pb-3 mb-3" style="height:50px;background-color:#30363b;">
        <div class="container">
          <div class='timer-block'>
            <span id='timer_on_exhausted' class='fliptimer'></span>
          </div>
        </div>
      </div>
    <!-- /安全停滯倒數 -->
    <!-- 樹狀導航列 -->
      <nav id="sidebarL" name="sidebarL" class="mt-2">
        <!-- ----- nav-child-indent 子層向右縮排 ---------------------------------------------- -->
        <!-- ----- nav-collapse-hide-child 左側導覽收起時子層隱藏（會導致 Treeview 改變時閃動） ---- -->
        <!-- ----- nav-compact 左側導覽行距縮減 ------------------------------------------------ -->
        <!-- ----- data-accordion="true" 開新子層時同時收起先前開啟的子層 ------------------------ -->
        <ul class="nav nav-pills nav-sidebar text-sm nav-child-indent nav-collapse-hide-child flex-column nav-compact" data-widget="treeview" role="menu" data-accordion="true">

          <?php
            $i = 0;
            while($i<count($sidebar)){
                $sidebar2 = isset($sidebar[$i]['subgroup']) ? $sidebar[$i]['subgroup'] : array();
                $subnum2 = count($sidebar2);
                //$topOpen = '';
                //$topActive = '';
                /*
                if(PAGE_ITEM == $sidebar[$i]['pagecode']){
                    $topOpen = ' menu-open';
                    $topActive = ' active';
                }
                */
                $topIcon = '';
                if(null != $sidebar[$i]['icon']){ $topIcon = " ".$sidebar[$i]['icon']; }
                echo '<li class="nav-item">';
                echo '<a href="'.$sidebar[$i]['url'].'" class="nav-link">';
                echo '<i class="nav-icon fas'.$topIcon.'"></i>';
                echo '<p>'.$sidebar[$i]['title'];
                echo '<i class="right fas fa-angle-left"></i>';
                echo '<span id="badge_'.$sidebar[$i]['pagecode'].'" name="badge_'.$sidebar[$i]['pagecode'].'" class="badge right';
                if(!empty($sidebar[$i]['badgeClass'])) { echo ' '.$sidebar[$i]['badgeClass']; }
                //echo ($topOpen == '') ? " badgeShow" : " badgeHide";
                echo '">';
                if(!empty($sidebar[$i]['badgeNum'])) { echo $sidebar[$i]['badgeNum']; }
                echo '</span>';
                echo '</p>';
                echo '</a>';
                if($subnum2 > 0){
                    echo '<ul class="nav nav-treeview">';
                    $si = 0;
                    while($si<$subnum2){
                        $sidebar3 = isset($sidebar2[$si]['subgroup']) ? $sidebar2[$si]['subgroup'] : array();
                        $subnum3 = count($sidebar3);
                        //$subOpen2 = '';
                        //$subActive2 = '';
                        $subIcon2 = ' far fa-dot-circle';
                        if($subnum3 > 0){
                            /*
                            if(PAGE_ITEM == $sidebar[$i]['pagecode'] && PAGE_ITEM2 == $sidebar2[$si]['pagecode']){
                                $subOpen2 = ' menu-open';
                                $subActive2 = ' active nav-layer2';
                            }
                            */
                            if(null != $sidebar2[$si]['icon']){ $subIcon2 = " fas ".$sidebar2[$si]['icon']; }
                        }
                        /*
                        else {
                            if(PAGE_MARK == $sidebar2[$si]['pagecode']){
                                $subOpen2 = ' menu-open';
                                $subActive2 = ' active';
                            }
                        }
                        */
                        echo '<li class="nav-item">';
                        echo '<a href="'.$sidebar2[$si]['url'].'" class="nav-link">';
                        echo '<i class="nav-icon'.$subIcon2.'"></i>';
                        echo '<p>'.$sidebar2[$si]['title'];
                        if($subnum3 > 0) { echo '<i class="fas fa-angle-left right"></i>'; }
                        else { echo '<i class="fas activeicon right"></i>'; }
                        echo '<span id="badge_'.$sidebar2[$si]['pagecode'].'" name="badge_'.$sidebar2[$si]['pagecode'].'" class="badge right';
                        if(!empty($sidebar2[$si]['badgeClass'])) { echo ' '.$sidebar2[$si]['badgeClass']; }
                        //echo ($subOpen2 == '') ? " badgeShow" : " badgeHide";
                        echo '">';
                        if(!empty($sidebar2[$si]['badgeNum'])) { echo $sidebar2[$si]['badgeNum']; }
                        echo '</span>';
                        echo '</p>';
                        echo '</a>';
                        if($subnum3 > 0){
                            echo '<ul class="nav nav-treeview">';
                            $ti = 0;
                            while($ti<$subnum3){
                                //$subOpen3 = '';
                                //$subActive3 = '';
                                /*
                                if(PAGE_MARK == $sidebar3[$ti]['pagecode']){
                                    $subOpen3 = ' menu-open';
                                    $subActive3 = ' active';
                                }
                                */
                                echo '<li class="nav-item">';
                                echo '<a href="'.$sidebar3[$ti]['url'].'" class="nav-link">';
                                echo '<i class="far fa-circle nav-icon"></i>';
                                echo '<p>'.$sidebar3[$ti]['title'];
                                echo '<i class="fas activeicon right"></i>';
                                echo '<span id="badge_'.$sidebar3[$ti]['pagecode'].'" class="badge right';
                                if(!empty($sidebar3[$ti]['badgeClass'])) { echo ' '.$sidebar3[$ti]['badgeClass']; }
                                //echo ($subOpen3 == '') ? " badgeShow" : " badgeHide";
                                echo '">';
                                if(!empty($sidebar3[$ti]['badgeNum'])) { echo $sidebar3[$ti]['badgeNum']; }
                                echo '</span>';        
                                echo '</p>';
                                echo '</a>';
                                echo '</li>';
                                $ti++;
                            }
                            echo '</ul>';
                        }
                        echo '</li>';
                        $si++;
                    }
                    echo '</ul>';
                }
                echo '</li>';
                $i++;
            }
          ?>
          <?php if(CF_AD_ID==1){ ?>
          <!-- --------------- GROUP 工作測試暫存區 --------------- -->
          <li class="nav-item<?php echo (PAGE_ITEM == 'ITEM_TEMP') ? ' menu-open' : '' ; ?>">
            <a href="workingtemp.php" class="nav-link<?php echo (PAGE_MARK == 'PG_TEMP') ? ' active' : '' ; ?>">
              <i class="nav-icon fas fa-flask"></i>
              <p>工作測試暫存區</p>
            </a>
          </li>
          <!-- --------------- /GROUP --------------- -->

          <!-- --------------- OLD ITEMS --------------- -->
            <li class="nav-item<?php echo (PAGE_ITEM == 'ITEM_CHART') ? ' menu-open' : '' ; ?>">
            <a href="chartjs.php" class="nav-link<?php echo (PAGE_MARK == 'PG_CHART') ? ' active' : '' ; ?>">
              <i class="nav-icon fas fa-th"></i>
              <p>Chart.JS</p>
            </a>
          </li>
          <li class="nav-item<?php echo (PAGE_ITEM == 'ITEM_WIDGET') ? ' menu-open' : '' ; ?>">
            <a href="widgets.php" class="nav-link<?php echo (PAGE_MARK == 'PG_WIDGET') ? ' active' : '' ; ?>">
              <i class="nav-icon fas fa-th"></i>
              <p>Widgets</p>
            </a>
          </li>
          <li class="nav-item<?php echo (PAGE_ITEM == 'ITEM_WEBDEMO') ? ' menu-open' : '' ; ?>">
            <a href="../_demo/demo_myaccount.php" class="nav-link<?php echo (PAGE_MARK == 'PG_WEBDEMO') ? ' active' : '' ; ?>">
              <i class="nav-icon fas fa-th"></i>
              <p id="testtt">客戶端測試區</p>
            </a>
          </li>
        </ul>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <?php } ?>

      </nav>
    <!-- /樹狀導航列 -->
      </div>
  <!-- /功能區塊 -->
  </aside>
<!-- /左側導覽區內容 -->
