/** #########################################################################
########################## 外框主頁面 JS 功能匯總  #############################
###################### toaster + iFrame + treeview ##########################
######################## 擷取自 5sladmin.js ############################
######################################################################### **/ 
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('jquery')) :
  typeof define === 'function' && define.amd ? define(['exports', 'jquery'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.5seasonlife = {}, global.jQuery));
})(this, (function (exports, $) { 'use strict';

  function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

  var $__default = /*#__PURE__*/_interopDefaultLegacy($);
  /**
   * --------------------------------------------
   * Fullscreen.js
   * License MIT
   * --------------------------------------------
   */
  /**
   * Constants
   * ====================================================
   */

  var NAME$8 = 'Fullscreen';
  var DATA_KEY$8 = 'lte.fullscreen';
  var JQUERY_NO_CONFLICT$8 = $__default["default"].fn[NAME$8];
  var SELECTOR_DATA_WIDGET$2 = '[data-widget="fullscreen"]';
  var SELECTOR_ICON = SELECTOR_DATA_WIDGET$2 + " i";
  var EVENT_FULLSCREEN_CHANGE = 'webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange';
  var Default$8 = {
    minimizeIcon: 'fa-compress-arrows-alt',
    maximizeIcon: 'fa-expand-arrows-alt'
  };
  /**
   * Class Definition
   * ====================================================
   */

  var Fullscreen = /*#__PURE__*/function () {
    function Fullscreen(_element, _options) {
      this.element = _element;
      this.options = $__default["default"].extend({}, Default$8, _options);
    } // Public


    var _proto = Fullscreen.prototype;

    _proto.toggle = function toggle() {
      if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
        this.windowed();
      } else {
        this.fullscreen();
      }
    };

    _proto.toggleIcon = function toggleIcon() {
      if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
        $__default["default"](SELECTOR_ICON).removeClass(this.options.maximizeIcon).addClass(this.options.minimizeIcon);
      } else {
        $__default["default"](SELECTOR_ICON).removeClass(this.options.minimizeIcon).addClass(this.options.maximizeIcon);
      }
    };

    _proto.fullscreen = function fullscreen() {
      if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen();
      } else if (document.documentElement.webkitRequestFullscreen) {
        document.documentElement.webkitRequestFullscreen();
      } else if (document.documentElement.msRequestFullscreen) {
        document.documentElement.msRequestFullscreen();
      }
    };

    _proto.windowed = function windowed() {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
      } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
      }
    } // Static
    ;

    Fullscreen._jQueryInterface = function _jQueryInterface(config) {
      var data = $__default["default"](this).data(DATA_KEY$8);

      if (!data) {
        data = $__default["default"](this).data();
      }

      var _options = $__default["default"].extend({}, Default$8, typeof config === 'object' ? config : data);

      var plugin = new Fullscreen($__default["default"](this), _options);
      $__default["default"](this).data(DATA_KEY$8, typeof config === 'object' ? config : data);

      if (typeof config === 'string' && /toggle|toggleIcon|fullscreen|windowed/.test(config)) {
        plugin[config]();
      } else {
        plugin.init();
      }
    };

    return Fullscreen;
  }();
  /**
    * Data API
    * ====================================================
    */


  $__default["default"](document).on('click', SELECTOR_DATA_WIDGET$2, function () {
    Fullscreen._jQueryInterface.call($__default["default"](this), 'toggle');
  });
  $__default["default"](document).on(EVENT_FULLSCREEN_CHANGE, function () {
    Fullscreen._jQueryInterface.call($__default["default"](SELECTOR_DATA_WIDGET$2), 'toggleIcon');
  });
  /**
   * jQuery API
   * ====================================================
   */

  $__default["default"].fn[NAME$8] = Fullscreen._jQueryInterface;
  $__default["default"].fn[NAME$8].Constructor = Fullscreen;

  $__default["default"].fn[NAME$8].noConflict = function () {
    $__default["default"].fn[NAME$8] = JQUERY_NO_CONFLICT$8;
    return Fullscreen._jQueryInterface;
  };
  /**
   * --------------------------------------------
   * IFrame.js
   * License MIT
   * --------------------------------------------
   */
//----------------------------------------------
  $('.content-wrapper.iframe-mode .tab-content iframe')
  var NAME$7 = 'IFrame';
  var DATA_KEY$7 = 'lte.iframe';
  var JQUERY_NO_CONFLICT$7 = $__default["default"].fn[NAME$7];
  var SELECTOR_DATA_TOGGLE$1 = '[data-widget="iframe"]';
  /* JazFIX:暫刪 */ //var SELECTOR_DATA_TOGGLE_CLOSE = '[data-widget="iframe-close"]';
  /* JazFIX:暫刪 */ //var SELECTOR_DATA_TOGGLE_SCROLL_LEFT = '[data-widget="iframe-scrollleft"]';
  /* JazFIX:暫刪 */ //var SELECTOR_DATA_TOGGLE_SCROLL_RIGHT = '[data-widget="iframe-scrollright"]';
  var SELECTOR_DATA_TOGGLE_FULLSCREEN = '[data-widget="iframe-fullscreen"]';
  var SELECTOR_CONTENT_WRAPPER = '.content-wrapper';
  var SELECTOR_CONTENT_IFRAME = SELECTOR_CONTENT_WRAPPER + " iframe";
  var SELECTOR_TAB_NAV = SELECTOR_CONTENT_WRAPPER + ".iframe-mode .nav";
  /* JazFIX:暫刪 */ //var SELECTOR_TAB_NAVBAR_NAV = SELECTOR_CONTENT_WRAPPER + ".iframe-mode .navbar-nav";
  /* JazFIX:暫刪 */ //var SELECTOR_TAB_NAVBAR_NAV_ITEM = SELECTOR_TAB_NAVBAR_NAV + " .nav-item";
  /* JazFIX:暫刪 */ //var SELECTOR_TAB_NAVBAR_NAV_LINK = SELECTOR_TAB_NAVBAR_NAV + " .nav-link";
  var SELECTOR_NAV_SIDEBARL = "nav#sidebarL";
  var SELECTOR_TAB_CONTENT = SELECTOR_CONTENT_WRAPPER + ".iframe-mode .tab-content";
  var SELECTOR_TAB_EMPTY = SELECTOR_TAB_CONTENT + " .tab-empty";
  var SELECTOR_TAB_LOADING = SELECTOR_TAB_CONTENT + " .tab-loading";
  var SELECTOR_TAB_PANE = SELECTOR_TAB_CONTENT + " .tab-pane";
  var SELECTOR_SIDEBAR_MENU_ITEM = '.main-sidebar .nav-item > a.nav-link';
  var SELECTOR_SIDEBAR_SEARCH_ITEM = '.sidebar-search-results .list-group-item';
  var SELECTOR_HEADER_MENU_ITEM = '.main-header .nav-item a.nav-link';
  var SELECTOR_HEADER_DROPDOWN_ITEM = '.main-header a.dropdown-item';
  var CLASS_NAME_IFRAME_MODE$1 = 'iframe-mode';
  var CLASS_NAME_FULLSCREEN_MODE = 'iframe-mode-fullscreen';
  var Default$7 = {
    /* JazFIX: 自此阻擋之後的程式不會執行
    onTabClick: function onTabClick(item) {
      return item;
    },
    onTabChanged: function onTabChanged(item) {
      return item;
    },
    onTabCreated: function onTabCreated(item) {
      return item;
    },
    //--jazfix end ----------------------------------------------------------- */
    autoIframeMode: true,
    autoItemActive: true,
    autoShowNewTab: true,
    autoDarkMode: false,
    allowDuplicates: false,
    allowReload: true,
    loadingScreen: true,
    useNavbarItems: true,
    scrollOffset: 40,
    scrollBehaviorSwap: false,
    iconMaximize: 'fa-expand',
    iconMinimize: 'fa-compress'
  };
  /**
   * Class Definition
   * ====================================================
   */

  var IFrame = /*#__PURE__*/function () {
    function IFrame(element, config) {
      this._config = config;
      this._element = element;

      this._init();
    } // Public

    var _proto = IFrame.prototype;

  //JazFIX:(以下保留，為其後程式碼的簡化版)
    _proto.openTabSidebar = function openTabSidebar(item, autoOpen) {
      var currItem = $__default["default"](item);
      var sideBarL = $__default["default"](SELECTOR_NAV_SIDEBARL);
      
      //var $item = currItem.parent('a').clone();
      //var link = $item.attr('href');
      
      var link = currItem.attr('href');//JazFIX
      if (link === '#' || link === '' || link === undefined) {
        //$__default["default"](SELECTOR_CONTENT_IFRAME).attr('src','5sladmin_startpage.html');//JazFIX
        $('#iFrame_main')[0].contentWindow.location.replace('5sladmin_startpage.html');
        this._fixHeight();//JazFIX
        /*
        console.log(currItem.parent('a').html());
        if(sideBarL.find('ul>li>ul>li>a.active').hasClass('active')) {
          sideBarL.find('ul>li>ul>li>a.active').removeClass('active');
          $__default["default"](SELECTOR_CONTENT_IFRAME).attr('src','5sladmin_startpage.html');
          this._fixHeight();
        } else {
          sideBarL.find('ul>li>ul>li>a.active').removeClass('active');
          currItem.parent('a').addClass('active');
        }
        */
        return;
      }
      //JazFIX
      if(currItem.hasClass('active')) {
        return;
      }
      
      //$('.content-wrapper.iframe-mode .tab-content').html('<iframe id="iFrame_main" name="iFrame_main" src="'+link+'"></iframe>');
      //$__default["default"](SELECTOR_CONTENT_IFRAME).attr('src',link);
      $('#iFrame_main')[0].contentWindow.location.replace(link);
      //$('.nav-item.nav-authpanel i.active').removeClass('active');
      
      this._fixHeight();
    };
    //NOTE: 全螢幕操作
    _proto.toggleFullscreen = function toggleFullscreen() {
      if ($__default["default"]('body').hasClass(CLASS_NAME_FULLSCREEN_MODE)) {
        $__default["default"](SELECTOR_DATA_TOGGLE_FULLSCREEN + " i").removeClass(this._config.iconMinimize).addClass(this._config.iconMaximize);
        $__default["default"]('body').removeClass(CLASS_NAME_FULLSCREEN_MODE);
        $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).height('100%');
        $__default["default"](SELECTOR_CONTENT_WRAPPER).height('100%');
        $__default["default"](SELECTOR_CONTENT_IFRAME).height('100%');
      } else {
        $__default["default"](SELECTOR_DATA_TOGGLE_FULLSCREEN + " i").removeClass(this._config.iconMaximize).addClass(this._config.iconMinimize);
        $__default["default"]('body').addClass(CLASS_NAME_FULLSCREEN_MODE);
      }

      $__default["default"](window).trigger('resize');

      this._fixHeight(true);
    }; // Private
  //--jazfix end -----------------------------------------------------------

  /* JazFIX: 自此阻擋之後的程式不會執行
    _proto.onTabClick = function onTabClick(item) {
      this._config.onTabClick(item);
    };

    _proto.onTabChanged = function onTabChanged(item) {
      this._config.onTabChanged(item);
    };
    _proto.onTabCreated = function onTabCreated(item) {
      this._config.onTabCreated(item);
    };
    //NOTE:創建新的導航軌跡
    _proto.createTab = function createTab(title, link, uniqueName, autoOpen) {
      var _this = this;

      var tabId = "panel-" + uniqueName;
      var navId = "tab-" + uniqueName;

      if (this._config.allowDuplicates) {
        tabId += "-" + Math.floor(Math.random() * 1000);
        navId += "-" + Math.floor(Math.random() * 1000);
      }
      var newNavItem = "<li class=\"nav-item\" role=\"presentation\"><a href=../scriptFiles/\"#\" class=\"btn-iframe-close\" data-widget=\"iframe-close\" data-type=\"only-this\"><i class=\"fas fa-times\"></i></a><a class=\"nav-link\" data-toggle=\"row\" id=\"" + navId + "\" href=\"#" + tabId + "\" role=\"tab\" aria-controls=\"" + tabId + "\" aria-selected=\"false\">" + title + "</a></li>";
      $__default["default"](SELECTOR_TAB_NAVBAR_NAV).append(unescape(escape(newNavItem)));
      var newTabItem = "<div class=\"tab-pane fade\" id=\"" + tabId + "\" role=\"tabpanel\" aria-labelledby=\"" + navId + "\"><iframe src=\"" + link + "\"></iframe></div>";
      $__default["default"](SELECTOR_TAB_CONTENT).append(unescape(escape(newTabItem)));

      if (autoOpen) {
        if (this._config.loadingScreen) {
          var $loadingScreen = $__default["default"](SELECTOR_TAB_LOADING);
          $loadingScreen.fadeIn();

          $__default["default"](tabId + " iframe").ready(function () {
            if (typeof _this._config.loadingScreen === 'number') {
              _this.switchTab("#" + navId);

              setTimeout(function () {
                $loadingScreen.fadeOut();
              }, _this._config.loadingScreen);
            } else {
              _this.switchTab("#" + navId);

              $loadingScreen.fadeOut();
            }
          });
          
        } else {
          this.switchTab("#" + navId);
        }
        
      }

      this.onTabCreated($__default["default"]("#" + navId));
    };
    //NOTE：點擊打開左側樹狀導航項目(最後一個 IF 指令之前皆為樹狀導航項目)
    _proto.openTabSidebar = function openTabSidebar(item, autoOpen) {
      if (autoOpen === void 0) {
        autoOpen = this._config.autoShowNewTab;
      }
      var $item = $__default["default"](item).clone();
      if ($item.attr('href') === undefined) {
        $item = $__default["default"](item).parent('a').clone();
      }
      $item.find('.right, .search-path').remove();
      var title = $item.find('p').text();

      if (title === '') {
        title = $item.text();
      }

      var link = $item.attr('href');

      if (link === '#' || link === '' || link === undefined) {
        return;
      }

      var uniqueName = unescape(link).replace('./', '').replace(/["#&'./:=?[\]]/gi, '-').replace(/(--)/gi, '');
      
      var navId = "tab-" + uniqueName;
      if (!this._config.allowDuplicates && $__default["default"]("#" + navId).length > 0) {
        return this.switchTab("#" + navId, this._config.allowReload);
      }
      
      //NOTE:外部執行創建新的導航軌跡
      //當左側第一層樹狀導航項目被點擊時到此結束（此時尚無網址導向），以下為具有導向網址的樹狀導航項目
      if (!this._config.allowDuplicates && $__default["default"]("#" + navId).length === 0 || this._config.allowDuplicates) {
        this.createTab(title, link, uniqueName, autoOpen);
      }

    };
    //JazFIX: 取代 openTabSidebar，更改為略過上方導航列直接顯示主頁面
    _proto.openTabSidebar = function openTabSidebar(item, autoOpen) {
      var $item = $__default["default"](item).clone();
      $item = $__default["default"](item).parent('a').clone();
      var link = $item.attr('href');
      if (link === '#' || link === '' || link === undefined) {
        return;
      }
      //$('.content-wrapper.iframe-mode .tab-content').html('<iframe id="iFrame_main" name="iFrame_main" src="'+link+'"></iframe>');
      $('.content-wrapper.iframe-mode .tab-content iframe').attr('src',link);
      
      this._fixHeight();
      if (tabEmpty === void 0) {
        var tabEmpty = false;
      }
      if ($__default["default"]('body').hasClass(CLASS_NAME_FULLSCREEN_MODE)) {
        var windowHeight = $__default["default"](window).height();
        //var navbarHeight = $__default["default"](SELECTOR_TAB_NAV).outerHeight();
        //$__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING + ", " + SELECTOR_CONTENT_IFRAME).height(windowHeight - navbarHeight);
        $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING + ", " + SELECTOR_CONTENT_IFRAME).height(windowHeight);
        $__default["default"](SELECTOR_CONTENT_WRAPPER).height(windowHeight);
      } else {
        var contentWrapperHeight = parseFloat($__default["default"](SELECTOR_CONTENT_WRAPPER).css('height'));
       // var _navbarHeight = $__default["default"](SELECTOR_TAB_NAV).outerHeight();//無需上方導航列
        if (tabEmpty == true) {
          setTimeout(function () {
            $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).height(contentWrapperHeight);//無需減除上方導航列的高度
          }, 50);
        } else {
          $__default["default"](SELECTOR_CONTENT_IFRAME).height(contentWrapperHeight);//無需減除上方導航列的高度
        }
      }

    };
    //NOTE: 點擊、移動導航軌跡欄項目
    _proto.switchTab = function switchTab(item, reload) {
      alert('switchTab'); 
      var _this2 = this;

      if (reload === void 0) {
        reload = false;
      }

      var $item = $__default["default"](item);
      var tabId = $item.attr('href');
      $__default["default"](SELECTOR_TAB_EMPTY).hide();

      if (reload) {
        var $loadingScreen = $__default["default"](SELECTOR_TAB_LOADING);

        if (this._config.loadingScreen) {
          $loadingScreen.show(0, function () {
            $__default["default"](tabId + " iframe").attr('src', $__default["default"](tabId + " iframe").attr('src')).ready(function () {
              if (_this2._config.loadingScreen) {
                if (typeof _this2._config.loadingScreen === 'number') {
                  setTimeout(function () {
                    $loadingScreen.fadeOut();
                  }, _this2._config.loadingScreen);
                } else {
                  $loadingScreen.fadeOut();
                }
              }
            });
          });
        } else {
          $__default["default"](tabId + " iframe").attr('src', $__default["default"](tabId + " iframe").attr('src'));
        }
      }

      $__default["default"](SELECTOR_TAB_NAVBAR_NAV + " .active").tab('dispose').removeClass('active');

      this._fixHeight();

      $item.tab('show');
      $item.parents('li').addClass('active');
      this.onTabChanged($item);

      if (this._config.autoItemActive) {
        this._setItemActive($__default["default"](tabId + " iframe").attr('src'));
      }
    };
    //NOTE: 移除導航軌跡欄項目
    _proto.removeActiveTab = function removeActiveTab(type, element) {
      if (type == 'all') {
        $__default["default"](SELECTOR_TAB_NAVBAR_NAV_ITEM).remove();
        $__default["default"](SELECTOR_TAB_PANE).remove();
        $__default["default"](SELECTOR_TAB_EMPTY).show();
      } else if (type == 'all-other') {
        $__default["default"](SELECTOR_TAB_NAVBAR_NAV_ITEM + ":not(.active)").remov();
        $__default["default"](SELECTOR_TAB_PANE + ":not(.active)").remove();
      } else if (type == 'only-this') {
        var $navClose = $__default["default"](element);
        var $navItem = $navClose.parent('.nav-item');
        var $navItemParent = $navItem.parent();
        var navItemIndex = $navItem.index();
        var tabId = $navClose.siblings('.nav-link').attr('aria-controls');
        $navItem.remove();
        $__default["default"]("#" + tabId).remove();

        if ($__default["default"](SELECTOR_TAB_CONTENT).children().length == $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).length) {
          $__default["default"](SELECTOR_TAB_EMPTY).show();
        } else {
          var prevNavItemIndex = navItemIndex - 1;
          this.switchTab($navItemParent.children().eq(prevNavItemIndex).find('a.nav-link'));
        }
      } else {
        var _$navItem = $__default["default"](SELECTOR_TAB_NAVBAR_NAV_ITEM + ".active");

        var _$navItemParent = _$navItem.parent();

        var _navItemIndex = _$navItem.index();

        _$navItem.remove();

        $__default["default"](SELECTOR_TAB_PANE + ".active").remove();

        if ($__default["default"](SELECTOR_TAB_CONTENT).children().length == $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).length) {
          $__default["default"](SELECTOR_TAB_EMPTY).show();
        } else {
          var _prevNavItemIndex = _navItemIndex - 1;

          this.switchTab(_$navItemParent.children().eq(_prevNavItemIndex).find('a.nav-link'));
        }
      }
    };

    //NOTE: 全螢幕操作
    _proto.toggleFullscreen = function toggleFullscreen() {
      if ($__default["default"]('body').hasClass(CLASS_NAME_FULLSCREEN_MODE)) {
        $__default["default"](SELECTOR_DATA_TOGGLE_FULLSCREEN + " i").removeClass(this._config.iconMinimize).addClass(this._config.iconMaximize);
        $__default["default"]('body').removeClass(CLASS_NAME_FULLSCREEN_MODE);
        $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).height('100%');
        $__default["default"](SELECTOR_CONTENT_WRAPPER).height('100%');
        $__default["default"](SELECTOR_CONTENT_IFRAME).height('100%');
      } else {
        $__default["default"](SELECTOR_DATA_TOGGLE_FULLSCREEN + " i").removeClass(this._config.iconMaximize).addClass(this._config.iconMinimize);
        $__default["default"]('body').addClass(CLASS_NAME_FULLSCREEN_MODE);
      }

      $__default["default"](window).trigger('resize');

      this._fixHeight(true);
    }; // Private
  //--jazfix end ----------------------------------------------------------- */

    _proto._init = function _init() {
      var usingDefTab = $__default["default"](SELECTOR_TAB_CONTENT).children().length > 2;

      this._setupListeners();

      this._fixHeight(true);

      if (usingDefTab) {
        var $el = $__default["default"]("" + SELECTOR_TAB_PANE).first(); // eslint-disable-next-line no-console
        var uniqueName = $el.attr('id').replace('panel-', '');
        var navId = "#tab-" + uniqueName;
        this.switchTab(navId, true);
      }
    };

    _proto._initFrameElement = function _initFrameElement() {
      if (window.frameElement && this._config.autoIframeMode) {
        var $body = $__default["default"]('body');
        $body.addClass(CLASS_NAME_IFRAME_MODE$1);

        if (this._config.autoDarkMode) {
          $body.addClass('dark-mode');
        }
      }
    };
    
    /*
    _proto._navScroll = function _navScroll(offset) {
      var leftPos = $__default["default"](SELECTOR_TAB_NAVBAR_NAV).scrollLeft();
      $__default["default"](SELECTOR_TAB_NAVBAR_NAV).animate({
        scrollLeft: leftPos + offset
      }, 250, 'linear');
    };
    */
   
  //JazFIX:(以下保留，為其後程式碼替代的升級版)
    _proto._setupListeners = function _setupListeners() {
      var _this3 = this;
      $__default["default"](window).on('resize', function () {
        setTimeout(function () {
          _this3._fixHeight();
        }, 1);
      });

      if ($__default["default"](SELECTOR_CONTENT_WRAPPER).hasClass(CLASS_NAME_IFRAME_MODE$1)) {

        $__default["default"](document).on('click', SELECTOR_SIDEBAR_MENU_ITEM + ", " + SELECTOR_SIDEBAR_SEARCH_ITEM, function (e) {
          e.preventDefault();
          _this3.openTabSidebar(e.target);
        });

        if (this._config.useNavbarItems) {
          $__default["default"](document).on('click', SELECTOR_HEADER_MENU_ITEM + ", " + SELECTOR_HEADER_DROPDOWN_ITEM, function (e) {
            e.preventDefault();

            _this3.openTabSidebar(e.target);
          });
        }
      }
      //NOTE: 導航軌跡欄全螢幕按鈕
      $__default["default"](document).on('click', SELECTOR_DATA_TOGGLE_FULLSCREEN, function (e) {
        e.preventDefault();

        _this3.toggleFullscreen();
      });
    };
  //--jazfix end -----------------------------------------------------------

  /* JazFIX: 自此阻擋之後的程式不會執行
    _proto._setupListeners = function _setupListeners() {
      var _this3 = this;
      $__default["default"](window).on('resize', function () {
        setTimeout(function () {
          _this3._fixHeight();
        }, 1);
      });

      if ($__default["default"](SELECTOR_CONTENT_WRAPPER).hasClass(CLASS_NAME_IFRAME_MODE$1)) {

        $__default["default"](document).on('click', SELECTOR_SIDEBAR_MENU_ITEM + ", " + SELECTOR_SIDEBAR_SEARCH_ITEM, function (e) {
          e.preventDefault();

          _this3.openTabSidebar(e.target);
        });

        if (this._config.useNavbarItems) {
          $__default["default"](document).on('click', SELECTOR_HEADER_MENU_ITEM + ", " + SELECTOR_HEADER_DROPDOWN_ITEM, function (e) {
            e.preventDefault();

            _this3.openTabSidebar(e.target);
          });
        }
      }
      //NOTE: 點擊上方導航軌跡列表
      $__default["default"](document).on('click', SELECTOR_TAB_NAVBAR_NAV_LINK, function (e) {
        e.preventDefault();

        _this3.onTabClick(e.target);

        _this3.switchTab(e.target);
      });
      //NOTE: 移除上方導航軌跡
      $__default["default"](document).on('click', SELECTOR_DATA_TOGGLE_CLOSE, function (e) {
        e.preventDefault();
        var target = e.target;

        if (target.nodeName == 'I') {
          target = e.target.offsetParent;
        }

        _this3.removeActiveTab(target.attributes['data-type'] ? target.attributes['data-type'].nodeValue : null, target);
      });


      //NOTE: 導航軌跡欄全螢幕按鈕
      $__default["default"](document).on('click', SELECTOR_DATA_TOGGLE_FULLSCREEN, function (e) {
        e.preventDefault();

        _this3.toggleFullscreen();
      });

      var mousedown = false;
      var mousedownInterval = null;
      $__default["default"](document).on('mousedown', SELECTOR_DATA_TOGGLE_SCROLL_LEFT, function (e) {
        e.preventDefault();
        clearInterval(mousedownInterval);
        var scrollOffset = _this3._config.scrollOffset;

        if (!_this3._config.scrollBehaviorSwap) {
          scrollOffset = -scrollOffset;
        }

        mousedown = true;

        _this3._navScroll(scrollOffset);

        mousedownInterval = setInterval(function () {
          _this3._navScroll(scrollOffset);
        }, 250);
      });
      $__default["default"](document).on('mousedown', SELECTOR_DATA_TOGGLE_SCROLL_RIGHT, function (e) {
        e.preventDefault();
        clearInterval(mousedownInterval);
        var scrollOffset = _this3._config.scrollOffset;

        if (_this3._config.scrollBehaviorSwap) {
          scrollOffset = -scrollOffset;
        }

        mousedown = true;

        _this3._navScroll(scrollOffset);

        mousedownInterval = setInterval(function () {
          _this3._navScroll(scrollOffset);
        }, 250);
      });
      $__default["default"](document).on('mouseup', function () {
        if (mousedown) {
          mousedown = false;
          clearInterval(mousedownInterval);
          mousedownInterval = null;
        }
      });

    };
    _proto._setItemActive = function _setItemActive(href) {
      $__default["default"](SELECTOR_SIDEBAR_MENU_ITEM + ", " + SELECTOR_HEADER_DROPDOWN_ITEM).removeClass('active');
      $__default["default"](SELECTOR_HEADER_MENU_ITEM).parent().removeClass('active');
      var $headerMenuItem = $__default["default"](SELECTOR_HEADER_MENU_ITEM + "[href$=\"" + href + "\"]");
      var $headerDropdownItem = $__default["default"](SELECTOR_HEADER_DROPDOWN_ITEM + "[href$=\"" + href + "\"]");
      var $sidebarMenuItem = $__default["default"](SELECTOR_SIDEBAR_MENU_ITEM + "[href$=\"" + href + "\"]");
      $headerMenuItem.each(function (i, e) {
        $__default["default"](e).parent().addClass('active');
      });
      $headerDropdownItem.each(function (i, e) {
        $__default["default"](e).addClass('active');
      });
      $sidebarMenuItem.each(function (i, e) {
        $__default["default"](e).addClass('active');
        $__default["default"](e).parents('.nav-treeview').prevAll('.nav-link').addClass('active');
      });
    };
  //--jazfix end ----------------------------------------------------------- */


    _proto._fixHeight = function _fixHeight(tabEmpty) {
      if (tabEmpty === void 0) {
        tabEmpty = false;
      }

      if ($__default["default"]('body').hasClass(CLASS_NAME_FULLSCREEN_MODE)) {
        var windowHeight = $__default["default"](window).height();
        var navbarHeight = $__default["default"](SELECTOR_TAB_NAV).outerHeight();
        $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING + ", " + SELECTOR_CONTENT_IFRAME).height(windowHeight - navbarHeight);
        $__default["default"](SELECTOR_CONTENT_WRAPPER).height(windowHeight);
      } else {
        var contentWrapperHeight = parseFloat($__default["default"](SELECTOR_CONTENT_WRAPPER).css('height'));
        /* JazFIX: 去除了上方導航列，所以導航列高度為 0 */
        //var _navbarHeight = $__default["default"](SELECTOR_TAB_NAV).outerHeight();
        var _navbarHeight = 0;

        if (tabEmpty == true) {
          setTimeout(function () {
            $__default["default"](SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).height(contentWrapperHeight - _navbarHeight);
          }, 50);
        } else {
          $__default["default"](SELECTOR_CONTENT_IFRAME).height(contentWrapperHeight - _navbarHeight);
        }
      }
    } // Static
    ;

    IFrame._jQueryInterface = function _jQueryInterface(config) {
      if ($__default["default"](SELECTOR_DATA_TOGGLE$1).length > 0) {
        var data = $__default["default"](this).data(DATA_KEY$7);

        if (!data) {
          data = $__default["default"](this).data();
        }

        var _options = $__default["default"].extend({}, Default$7, typeof config === 'object' ? config : data);

        localStorage.setItem('5SeasonLife:IFrame:Options', JSON.stringify(_options));
        var plugin = new IFrame($__default["default"](this), _options);
        $__default["default"](this).data(DATA_KEY$7, typeof config === 'object' ? config : data);

        if (typeof config === 'string' && /createTab|openTabSidebar|switchTab|removeActiveTab/.test(config)) {
          plugin[config]();
        }
      } else {
        new IFrame($__default["default"](this), JSON.parse(localStorage.getItem('5SeasonLife:IFrame:Options')))._initFrameElement();
      }
    };

    return IFrame;
  }();
  /**
   * Data API
   * ====================================================
   */

  $__default["default"](window).on('load', function () {
    IFrame._jQueryInterface.call($__default["default"](SELECTOR_DATA_TOGGLE$1));
  });
  /**
   * jQuery API
   * ====================================================
   */

  $__default["default"].fn[NAME$7] = IFrame._jQueryInterface;
  $__default["default"].fn[NAME$7].Constructor = IFrame;

  $__default["default"].fn[NAME$7].noConflict = function () {
    $__default["default"].fn[NAME$7] = JQUERY_NO_CONFLICT$7;
    return IFrame._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * Layout.js
   * License MIT
   * --------------------------------------------
   * Constants
   * ====================================================
   */
/*
  var NAME$6 = 'Layout';
  var DATA_KEY$6 = 'lte.layout';
  var JQUERY_NO_CONFLICT$6 = $__default["default"].fn[NAME$6];
  var SELECTOR_HEADER = '.main-header';
  var SELECTOR_MAIN_SIDEBAR = '.main-sidebar';
  var SELECTOR_SIDEBAR$1 = '.main-sidebar .sidebar';
  var SELECTOR_CONTENT = '.content-wrapper';
  var SELECTOR_CONTROL_SIDEBAR_CONTENT = '.control-sidebar-content';
  var SELECTOR_CONTROL_SIDEBAR_BTN = '[data-widget="control-sidebar"]';
  var SELECTOR_FOOTER = '.main-footer';
  var SELECTOR_PUSHMENU_BTN = '[data-widget="pushmenu"]';
  var SELECTOR_LOGIN_BOX = '.login-box';
  var SELECTOR_REGISTER_BOX = '.register-box';
  var SELECTOR_PRELOADER = '.preloader';
  var CLASS_NAME_SIDEBAR_COLLAPSED$1 = 'sidebar-collapse';
  var CLASS_NAME_SIDEBAR_FOCUSED = 'sidebar-focused';
  var CLASS_NAME_LAYOUT_FIXED = 'layout-fixed';
  var CLASS_NAME_CONTROL_SIDEBAR_SLIDE_OPEN = 'control-sidebar-slide-open';
  var CLASS_NAME_CONTROL_SIDEBAR_OPEN = 'control-sidebar-open';
  var CLASS_NAME_IFRAME_MODE = 'iframe-mode';
  var Default$6 = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'l',
    panelAutoHeight: true,
    panelAutoHeightMode: 'min-height',
    preloadDuration: 200,
    loginRegisterAutoHeight: true
  };

  //Class Definition
  //====================================================
  //var Layout = #__PURE__function () {
  var Layout = function () {
    function Layout(element, config) {
      this._config = config;
      this._element = element;
    } // Public


    var _proto = Layout.prototype;

    _proto.fixLayoutHeight = function fixLayoutHeight(extra) {
      
      if (extra === void 0) {
        extra = null;
      }

      var $body = $__default["default"]('body');
      var controlSidebar = 0;

      if ($body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE_OPEN) || $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN) || extra === 'control_sidebar') {
        controlSidebar = $__default["default"](SELECTOR_CONTROL_SIDEBAR_CONTENT).outerHeight();
      }

      var heights = {
        window: $__default["default"](window).height(),
        header: $__default["default"](SELECTOR_HEADER).length > 0 ? $__default["default"](SELECTOR_HEADER).outerHeight() : 0,
        footer: $__default["default"](SELECTOR_FOOTER).length > 0 ? $__default["default"](SELECTOR_FOOTER).outerHeight() : 0,
        sidebar: $__default["default"](SELECTOR_SIDEBAR$1).length > 0 ? $__default["default"](SELECTOR_SIDEBAR$1).height() : 0,
        controlSidebar: controlSidebar
      };

      var max = this._max(heights);

      var offset = this._config.panelAutoHeight;

      if (offset === true) {
        offset = 0;
      }

      var $contentSelector = $__default["default"](SELECTOR_CONTENT);

      if (offset !== false) {
        if (max === heights.controlSidebar) {
          $contentSelector.css(this._config.panelAutoHeightMode, max + offset);
        } else if (max === heights.window) {
          $contentSelector.css(this._config.panelAutoHeightMode, max + offset - heights.header - heights.footer);
        } else {
          $contentSelector.css(this._config.panelAutoHeightMode, max + offset - heights.header);
        }

        if (this._isFooterFixed()) {
          $contentSelector.css(this._config.panelAutoHeightMode, parseFloat($contentSelector.css(this._config.panelAutoHeightMode)) + heights.footer);
        }
      }

      if (!$body.hasClass(CLASS_NAME_LAYOUT_FIXED)) {
        return;
      }

      if (typeof $__default["default"].fn.overlayScrollbars !== 'undefined') {
        $__default["default"](SELECTOR_SIDEBAR$1).overlayScrollbars({
          className: this._config.scrollbarTheme,
          sizeAutoCapable: true,
          scrollbars: {
            autoHide: this._config.scrollbarAutoHide,
            clickScrolling: true
          }
        });
      } else {
        $__default["default"](SELECTOR_SIDEBAR$1).css('overflow-y', 'auto');
      }
    };

    _proto.fixLoginRegisterHeight = function fixLoginRegisterHeight() {
      var $body = $__default["default"]('body');
      var $selector = $__default["default"](SELECTOR_LOGIN_BOX + ", " + SELECTOR_REGISTER_BOX);

      if ($body.hasClass(CLASS_NAME_IFRAME_MODE)) {
        $body.css('height', '100%');
        $__default["default"]('.wrapper').css('height', '100%');
        $__default["default"]('html').css('height', '100%');
      } else if ($selector.length === 0) {
        $body.css('height', 'auto');
        $__default["default"]('html').css('height', 'auto');
      } else {
        var boxHeight = $selector.height();

        if ($body.css(this._config.panelAutoHeightMode) !== boxHeight) {
          $body.css(this._config.panelAutoHeightMode, boxHeight);
        }
      }
    } // Private
    ;

    _proto._init = function _init() {
      var _this = this;

      // Activate layout height watcher
      this.fixLayoutHeight();

      if (this._config.loginRegisterAutoHeight === true) {
        this.fixLoginRegisterHeight();
      } else if (this._config.loginRegisterAutoHeight === parseInt(this._config.loginRegisterAutoHeight, 10)) {
        setInterval(this.fixLoginRegisterHeight, this._config.loginRegisterAutoHeight);
      }

      $__default["default"](SELECTOR_SIDEBAR$1).on('collapsed.lte.treeview expanded.lte.treeview', function () {
        _this.fixLayoutHeight();
      });
      $__default["default"](SELECTOR_MAIN_SIDEBAR).on('mouseenter mouseleave', function () {
        if ($__default["default"]('body').hasClass(CLASS_NAME_SIDEBAR_COLLAPSED$1)) {
          _this.fixLayoutHeight();
        }
      });
      $__default["default"](SELECTOR_PUSHMENU_BTN).on('collapsed.lte.pushmenu shown.lte.pushmenu', function () {
        setTimeout(function () {
          _this.fixLayoutHeight();
        }, 300);
      });
      $__default["default"](SELECTOR_CONTROL_SIDEBAR_BTN).on('collapsed.lte.controlsidebar', function () {
        _this.fixLayoutHeight();
      }).on('expanded.lte.controlsidebar', function () {
        _this.fixLayoutHeight('control_sidebar');
      });
      $__default["default"](window).resize(function () {
        _this.fixLayoutHeight();
      });
      setTimeout(function () {
        $__default["default"]('body.hold-transition').removeClass('hold-transition');
      }, 50);
      setTimeout(function () {
        var $preloader = $__default["default"](SELECTOR_PRELOADER);

        if ($preloader) {
          $preloader.css('height', 0);
          setTimeout(function () {
            $preloader.children().hide();
          }, 200);
        }
      }, this._config.preloadDuration);
    };

    _proto._max = function _max(numbers) {
      // Calculate the maximum number in a list
      var max = 0;
      Object.keys(numbers).forEach(function (key) {
        if (numbers[key] > max) {
          max = numbers[key];
        }
      });
      return max;
    };

    _proto._isFooterFixed = function _isFooterFixed() {
      return $__default["default"](SELECTOR_FOOTER).css('position') === 'fixed';
    } // Static
    ;

    Layout._jQueryInterface = function _jQueryInterface(config) {
      if (config === void 0) {
        config = '';
      }

      return this.each(function () {
        var data = $__default["default"](this).data(DATA_KEY$6);

        var _options = $__default["default"].extend({}, Default$6, $__default["default"](this).data());

        if (!data) {
          data = new Layout($__default["default"](this), _options);
          $__default["default"](this).data(DATA_KEY$6, data);
        }

        if (config === 'init' || config === '') {
          data._init();
        } else if (config === 'fixLayoutHeight' || config === 'fixLoginRegisterHeight') {
          data[config]();
        }
      });
    };

    return Layout;
  }();
  
  //Data API
  //====================================================
  $__default["default"](window).on('load', function () {
    Layout._jQueryInterface.call($__default["default"]('body'));
  });
  $__default["default"](SELECTOR_SIDEBAR$1 + " a").on('focusin', function () {
    $__default["default"](SELECTOR_MAIN_SIDEBAR).addClass(CLASS_NAME_SIDEBAR_FOCUSED);
  }).on('focusout', function () {
    $__default["default"](SELECTOR_MAIN_SIDEBAR).removeClass(CLASS_NAME_SIDEBAR_FOCUSED);
  });
  //jQuery API
  //====================================================
  $__default["default"].fn[NAME$6] = Layout._jQueryInterface;
  $__default["default"].fn[NAME$6].Constructor = Layout;

  $__default["default"].fn[NAME$6].noConflict = function () {
    $__default["default"].fn[NAME$6] = JQUERY_NO_CONFLICT$6;
    return Layout._jQueryInterface;
  };
*/

  /**
   * --------------------------------------------
   * PushMenu.js
   * License MIT
   * --------------------------------------------
   */
  /**
   * Constants
   * ====================================================
   */

  var NAME$5 = 'PushMenu';
  var DATA_KEY$5 = 'lte.pushmenu';
  var EVENT_KEY$2 = "." + DATA_KEY$5;
  var JQUERY_NO_CONFLICT$5 = $__default["default"].fn[NAME$5];
  var EVENT_COLLAPSED$1 = "collapsed" + EVENT_KEY$2;
  var EVENT_COLLAPSED_DONE = "collapsed-done" + EVENT_KEY$2;
  var EVENT_SHOWN = "shown" + EVENT_KEY$2;
  var SELECTOR_TOGGLE_BUTTON$1 = '[data-widget="pushmenu"]';
  var SELECTOR_BODY = 'body';
  var SELECTOR_OVERLAY = '#sidebar-overlay';
  var SELECTOR_WRAPPER = '.wrapper';
  var CLASS_NAME_COLLAPSED = 'sidebar-collapse';
  var CLASS_NAME_OPEN$3 = 'sidebar-open';
  var CLASS_NAME_IS_OPENING$1 = 'sidebar-is-opening';
  var CLASS_NAME_CLOSED = 'sidebar-closed';
  var Default$5 = {
    autoCollapseSize: 992,
    enableRemember: false,
    noTransitionAfterReload: true,
    animationSpeed: 300
  };
  /**
   * Class Definition
   * ====================================================
   */

  var PushMenu = /*#__PURE__*/function () {
    function PushMenu(element, options) {
      this._element = element;
      this._options = $__default["default"].extend({}, Default$5, options);

      if ($__default["default"](SELECTOR_OVERLAY).length === 0) {
        this._addOverlay();
      }

      this._init();
    } // Public


    var _proto = PushMenu.prototype;

    _proto.expand = function expand() {
      var $bodySelector = $__default["default"](SELECTOR_BODY);

      if (this._options.autoCollapseSize && $__default["default"](window).width() <= this._options.autoCollapseSize) {
        $bodySelector.addClass(CLASS_NAME_OPEN$3);
      }

      $bodySelector.addClass(CLASS_NAME_IS_OPENING$1).removeClass(CLASS_NAME_COLLAPSED + " " + CLASS_NAME_CLOSED).delay(50).queue(function () {
        $bodySelector.removeClass(CLASS_NAME_IS_OPENING$1);
        $__default["default"](this).dequeue();
      });

      if (this._options.enableRemember) {
        localStorage.setItem("remember" + EVENT_KEY$2, CLASS_NAME_OPEN$3);
      }

      $__default["default"](this._element).trigger($__default["default"].Event(EVENT_SHOWN));
    };

    _proto.collapse = function collapse() {
      var _this = this;

      var $bodySelector = $__default["default"](SELECTOR_BODY);

      if (this._options.autoCollapseSize && $__default["default"](window).width() <= this._options.autoCollapseSize) {
        $bodySelector.removeClass(CLASS_NAME_OPEN$3).addClass(CLASS_NAME_CLOSED);
      }

      $bodySelector.addClass(CLASS_NAME_COLLAPSED);

      if (this._options.enableRemember) {
        localStorage.setItem("remember" + EVENT_KEY$2, CLASS_NAME_COLLAPSED);
      }

      $__default["default"](this._element).trigger($__default["default"].Event(EVENT_COLLAPSED$1));
      setTimeout(function () {
        $__default["default"](_this._element).trigger($__default["default"].Event(EVENT_COLLAPSED_DONE));
      }, this._options.animationSpeed);
    };

    _proto.toggle = function toggle() {
      if ($__default["default"](SELECTOR_BODY).hasClass(CLASS_NAME_COLLAPSED)) {
        this.expand();
      } else {
        this.collapse();
      }
    };

    _proto.autoCollapse = function autoCollapse(resize) {
      if (resize === void 0) {
        resize = false;
      }

      if (!this._options.autoCollapseSize) {
        return;
      }

      var $bodySelector = $__default["default"](SELECTOR_BODY);

      if ($__default["default"](window).width() <= this._options.autoCollapseSize) {
        if (!$bodySelector.hasClass(CLASS_NAME_OPEN$3)) {
          this.collapse();
        }
      } else if (resize === true) {
        if ($bodySelector.hasClass(CLASS_NAME_OPEN$3)) {
          $bodySelector.removeClass(CLASS_NAME_OPEN$3);
        } else if ($bodySelector.hasClass(CLASS_NAME_CLOSED)) {
          this.expand();
        }
      }
    };

    _proto.remember = function remember() {
      if (!this._options.enableRemember) {
        return;
      }

      var $body = $__default["default"]('body');
      var toggleState = localStorage.getItem("remember" + EVENT_KEY$2);

      if (toggleState === CLASS_NAME_COLLAPSED) {
        if (this._options.noTransitionAfterReload) {
          $body.addClass('hold-transition').addClass(CLASS_NAME_COLLAPSED).delay(50).queue(function () {
            $__default["default"](this).removeClass('hold-transition');
            $__default["default"](this).dequeue();
          });
        } else {
          $body.addClass(CLASS_NAME_COLLAPSED);
        }
      } else if (this._options.noTransitionAfterReload) {
        $body.addClass('hold-transition').removeClass(CLASS_NAME_COLLAPSED).delay(50).queue(function () {
          $__default["default"](this).removeClass('hold-transition');
          $__default["default"](this).dequeue();
        });
      } else {
        $body.removeClass(CLASS_NAME_COLLAPSED);
      }
    } // Private
    ;

    _proto._init = function _init() {
      var _this2 = this;

      this.remember();
      this.autoCollapse();
      $__default["default"](window).resize(function () {
        _this2.autoCollapse(true);
      });
    };

    _proto._addOverlay = function _addOverlay() {
      var _this3 = this;

      var overlay = $__default["default"]('<div />', {
        id: 'sidebar-overlay'
      });
      overlay.on('click', function () {
        _this3.collapse();
      });
      $__default["default"](SELECTOR_WRAPPER).append(overlay);
    } // Static
    ;

    PushMenu._jQueryInterface = function _jQueryInterface(operation) {
      return this.each(function () {
        var data = $__default["default"](this).data(DATA_KEY$5);

        var _options = $__default["default"].extend({}, Default$5, $__default["default"](this).data());

        if (!data) {
          data = new PushMenu(this, _options);
          $__default["default"](this).data(DATA_KEY$5, data);
        }

        if (typeof operation === 'string' && /collapse|expand|toggle/.test(operation)) {
          data[operation]();
        }
      });
    };

    return PushMenu;
  }();
  /**
   * Data API
   * ====================================================
   */


  $__default["default"](document).on('click', SELECTOR_TOGGLE_BUTTON$1, function (event) {
    event.preventDefault();
    var button = event.currentTarget;

    if ($__default["default"](button).data('widget') !== 'pushmenu') {
      button = $__default["default"](button).closest(SELECTOR_TOGGLE_BUTTON$1);
    }

    PushMenu._jQueryInterface.call($__default["default"](button), 'toggle');
  });
  $__default["default"](window).on('load', function () {
    PushMenu._jQueryInterface.call($__default["default"](SELECTOR_TOGGLE_BUTTON$1));
  });
  /**
   * jQuery API
   * ====================================================
   */

  $__default["default"].fn[NAME$5] = PushMenu._jQueryInterface;
  $__default["default"].fn[NAME$5].Constructor = PushMenu;

  $__default["default"].fn[NAME$5].noConflict = function () {
    $__default["default"].fn[NAME$5] = JQUERY_NO_CONFLICT$5;
    return PushMenu._jQueryInterface;
  };

//----------------------------------------------

  /**
   * --------------------------------------------
   * Toasts.js
   * License MIT
   * --------------------------------------------
   */
/*[*]*/
  var NAME$2 = 'Toasts';
  var DATA_KEY$2 = 'lte.toasts';
  var EVENT_KEY$1 = "." + DATA_KEY$2;
  var JQUERY_NO_CONFLICT$2 = $__default["default"].fn[NAME$2];
  var EVENT_INIT = "init" + EVENT_KEY$1;
  var EVENT_CREATED = "created" + EVENT_KEY$1;
  var EVENT_REMOVED = "removed" + EVENT_KEY$1;
  var SELECTOR_CONTAINER_TOP_RIGHT = '#toastsContainerTopRight';
  var SELECTOR_CONTAINER_TOP_LEFT = '#toastsContainerTopLeft';
  var SELECTOR_CONTAINER_BOTTOM_RIGHT = '#toastsContainerBottomRight';
  var SELECTOR_CONTAINER_BOTTOM_LEFT = '#toastsContainerBottomLeft';
  var CLASS_NAME_TOP_RIGHT = 'toasts-top-right';
  var CLASS_NAME_TOP_LEFT = 'toasts-top-left';
  var CLASS_NAME_BOTTOM_RIGHT = 'toasts-bottom-right';
  var CLASS_NAME_BOTTOM_LEFT = 'toasts-bottom-left';
  var POSITION_TOP_RIGHT = 'topRight';
  var POSITION_TOP_LEFT = 'topLeft';
  var POSITION_BOTTOM_RIGHT = 'bottomRight';
  var POSITION_BOTTOM_LEFT = 'bottomLeft';
  var Default$2 = {
    position: POSITION_TOP_RIGHT,
    fixed: true,
    autohide: false,
    autoremove: true,
    delay: 1000,
    fade: true,
    icon: null,
    image: null,
    imageAlt: null,
    imageHeight: '25px',
    title: null,
    subtitle: null,
    close: true,
    body: null,
    class: null
  };
  /**
   * Class Definition
   * ====================================================
   */

  var Toasts = /*#__PURE__*/function () {
    function Toasts(element, config) {
      this._config = config;

      this._prepareContainer();

      $__default["default"]('body').trigger($__default["default"].Event(EVENT_INIT));
    } // Public

    var _proto = Toasts.prototype;

    _proto.create = function create() {
      var toast = $__default["default"]('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true"/>');
      toast.data('autohide', this._config.autohide);
      toast.data('animation', this._config.fade);

      if (this._config.class) {
        toast.addClass(this._config.class);
      }

      if (this._config.delay && this._config.delay != 500) {
        toast.data('delay', this._config.delay);
      }

      var toastHeader = $__default["default"]('<div class="toast-header">');

      if (this._config.image != null) {
        var toastImage = $__default["default"]('<img />').addClass('rounded mr-2').attr('src', this._config.image).attr('alt', this._config.imageAlt);

        if (this._config.imageHeight != null) {
          toastImage.height(this._config.imageHeight).width('auto');
        }

        toastHeader.append(toastImage);
      }

      if (this._config.icon != null) {
        toastHeader.append($__default["default"]('<i />').addClass('mr-2').addClass(this._config.icon));
      }

      if (this._config.title != null) {
        toastHeader.append($__default["default"]('<strong />').addClass('mr-auto').html(this._config.title));
      }

      if (this._config.subtitle != null) {
        toastHeader.append($__default["default"]('<small />').html(this._config.subtitle));
      }

      if (this._config.close == true) {
        var toastClose = $__default["default"]('<button data-dismiss="toast" />').attr('type', 'button').addClass('ml-2 mb-1 close').attr('aria-label', 'Close').append('<span aria-hidden="true">×</span>');

        if (this._config.title == null) {
          toastClose.toggleClass('ml-2 ml-auto');
        }

        toastHeader.append(toastClose);
      }

      toast.append(toastHeader);

      if (this._config.body != null) {
        toast.append($__default["default"]('<div class="toast-body" />').html(this._config.body));
      }

      $__default["default"](this._getContainerId()).prepend(toast);
      var $body = $__default["default"]('body');
      $body.trigger($__default["default"].Event(EVENT_CREATED));
      toast.toast('show');

      if (this._config.autoremove) {
        toast.on('hidden.bs.toast', function () {
          $__default["default"](this).delay(200).remove();
          $body.trigger($__default["default"].Event(EVENT_REMOVED));
        });
      }
    } // Static
    ;

    _proto._getContainerId = function _getContainerId() {
      if (this._config.position == POSITION_TOP_RIGHT) {
        return SELECTOR_CONTAINER_TOP_RIGHT;
      }

      if (this._config.position == POSITION_TOP_LEFT) {
        return SELECTOR_CONTAINER_TOP_LEFT;
      }

      if (this._config.position == POSITION_BOTTOM_RIGHT) {
        return SELECTOR_CONTAINER_BOTTOM_RIGHT;
      }

      if (this._config.position == POSITION_BOTTOM_LEFT) {
        return SELECTOR_CONTAINER_BOTTOM_LEFT;
      }
    };

    _proto._prepareContainer = function _prepareContainer() {
      if ($__default["default"](this._getContainerId()).length === 0) {
        var container = $__default["default"]('<div />').attr('id', this._getContainerId().replace('#', ''));

        if (this._config.position == POSITION_TOP_RIGHT) {
          container.addClass(CLASS_NAME_TOP_RIGHT);
        } else if (this._config.position == POSITION_TOP_LEFT) {
          container.addClass(CLASS_NAME_TOP_LEFT);
        } else if (this._config.position == POSITION_BOTTOM_RIGHT) {
          container.addClass(CLASS_NAME_BOTTOM_RIGHT);
        } else if (this._config.position == POSITION_BOTTOM_LEFT) {
          container.addClass(CLASS_NAME_BOTTOM_LEFT);
        }

        $__default["default"]('body').append(container);
      }

      if (this._config.fixed) {
        $__default["default"](this._getContainerId()).addClass('fixed');
      } else {
        $__default["default"](this._getContainerId()).removeClass('fixed');
      }
    } // Static
    ;

    Toasts._jQueryInterface = function _jQueryInterface(option, config) {
      return this.each(function () {
        var _options = $__default["default"].extend({}, Default$2, config);

        var toast = new Toasts($__default["default"](this), _options);

        if (option === 'create') {
          toast[option]();
        }
      });
    };

    return Toasts;
  }();
  /**
   * jQuery API
   * ====================================================
   */

  $__default["default"].fn[NAME$2] = Toasts._jQueryInterface;
  $__default["default"].fn[NAME$2].Constructor = Toasts;

  $__default["default"].fn[NAME$2].noConflict = function () {
    $__default["default"].fn[NAME$2] = JQUERY_NO_CONFLICT$2;
    return Toasts._jQueryInterface;
  };
/* ******** */

  /* JazFIX: */
  /** 
   * --------------------------------------------
   *Treeview.js
   * License MIT
   * --------------------------------------------
   */
/* [*] */
  var NAME = 'Treeview';
  var DATA_KEY = 'lte.treeview';
  var EVENT_KEY = "." + DATA_KEY;
  var JQUERY_NO_CONFLICT = $__default["default"].fn[NAME];
  var EVENT_EXPANDED = "expanded" + EVENT_KEY;
  var EVENT_COLLAPSED = "collapsed" + EVENT_KEY;
  var EVENT_LOAD_DATA_API = "load" + EVENT_KEY;
  var SELECTOR_LI = '.nav-item';
  var SELECTOR_LINK = '.nav-link';
  var SELECTOR_TREEVIEW_MENU = '.nav-treeview';
  var SELECTOR_OPEN = '.menu-open';
  var SELECTOR_DATA_WIDGET = '[data-widget="treeview"]';
  var CLASS_NAME_OPEN = 'menu-open';
  var CLASS_NAME_IS_OPENING = 'menu-is-opening';
  var CLASS_NAME_SIDEBAR_COLLAPSED = 'sidebar-collapse';
  var Default = {
    trigger: SELECTOR_DATA_WIDGET + " " + SELECTOR_LINK,
    animationSpeed: 300,
    accordion: true,
    expandSidebar: false,
    sidebarButtonSelector: '[data-widget="pushmenu"]'
  };
  /**
   * Class Definition
   * ====================================================
   */

  var Treeview = /*#__PURE__*/function () {
    function Treeview(element, config) {
      this._config = config;
      this._element = element;
    } // Public


    var _proto = Treeview.prototype;

    _proto.init = function init() {
      $__default["default"]("" + SELECTOR_LI + SELECTOR_OPEN + " " + SELECTOR_TREEVIEW_MENU + SELECTOR_OPEN).css('display', 'block');

      this._setupListeners();
    };

    _proto.expand = function expand(treeviewMenu, parentLi) {
      var _this = this;

      var expandedEvent = $__default["default"].Event(EVENT_EXPANDED);

      if (this._config.accordion) {
        var openMenuLi = parentLi.siblings(SELECTOR_OPEN).first();
        var openTreeview = openMenuLi.find(SELECTOR_TREEVIEW_MENU).first();
        this.collapse(openTreeview, openMenuLi);
      }

      parentLi.addClass(CLASS_NAME_IS_OPENING);
      treeviewMenu.stop().slideDown(this._config.animationSpeed, function () {
        parentLi.addClass(CLASS_NAME_OPEN);
        $__default["default"](_this._element).trigger(expandedEvent);
      });

      if (this._config.expandSidebar) {
        this._expandSidebar();
      }
    };

    _proto.collapse = function collapse(treeviewMenu, parentLi) {
      var _this2 = this;

      var collapsedEvent = $__default["default"].Event(EVENT_COLLAPSED);
      parentLi.find('a.active').removeClass('active');//JazFIX
      parentLi.removeClass(CLASS_NAME_IS_OPENING + " " + CLASS_NAME_OPEN);
      treeviewMenu.stop().slideUp(this._config.animationSpeed, function () {
        $__default["default"](_this2._element).trigger(collapsedEvent);
        treeviewMenu.find(SELECTOR_OPEN + " > " + SELECTOR_TREEVIEW_MENU).slideUp();
        treeviewMenu.find(SELECTOR_OPEN).removeClass(CLASS_NAME_IS_OPENING + " " + CLASS_NAME_OPEN);
      });
    };

    _proto.toggle = function toggle(event) {
      var $relativeTarget = $__default["default"](event.currentTarget);
      var $parent = $relativeTarget.parent();
      var treeviewMenu = $parent.find("> " + SELECTOR_TREEVIEW_MENU);

      if (!treeviewMenu.is(SELECTOR_TREEVIEW_MENU)) {
        if (!$parent.is(SELECTOR_LI)) {
          treeviewMenu = $parent.parent().find("> " + SELECTOR_TREEVIEW_MENU);
        }

        if (!treeviewMenu.is(SELECTOR_TREEVIEW_MENU)) {
          $('#sidebarL .nav-treeview a.active').removeClass('active');//JazFIX
          $relativeTarget.addClass('active');//JazFIX
          return;
        }
      }

      event.preventDefault();
      var parentLi = $relativeTarget.parents(SELECTOR_LI).first();
      var isOpen = parentLi.hasClass(CLASS_NAME_OPEN);

      if (isOpen) {
        this.collapse($__default["default"](treeviewMenu), parentLi);
      } else {
        this.expand($__default["default"](treeviewMenu), parentLi);
      }
    } // Private
    ;

    _proto._setupListeners = function _setupListeners() {
      var _this3 = this;

      var elementId = this._element.attr('id') !== undefined ? "#" + this._element.attr('id') : '';
      $__default["default"](document).on('click', "" + elementId + this._config.trigger, function (event) {
        _this3.toggle(event);
      });
    };

    _proto._expandSidebar = function _expandSidebar() {
      if ($__default["default"]('body').hasClass(CLASS_NAME_SIDEBAR_COLLAPSED)) {
        $__default["default"](this._config.sidebarButtonSelector).PushMenu('expand');
      }
    } // Static
    ;

    Treeview._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $__default["default"](this).data(DATA_KEY);

        var _options = $__default["default"].extend({}, Default, $__default["default"](this).data());

        if (!data) {
          data = new Treeview($__default["default"](this), _options);
          $__default["default"](this).data(DATA_KEY, data);
        }

        if (config === 'init') {
          data[config]();
        }
      });
    };

    return Treeview;
  }();
  /**
   * Data API
   * ====================================================
   */


  $__default["default"](window).on(EVENT_LOAD_DATA_API, function () {
    $__default["default"](SELECTOR_DATA_WIDGET).each(function () {
      Treeview._jQueryInterface.call($__default["default"](this), 'init');
    });
  });
  /**
   * jQuery API
   * ====================================================
   */

  $__default["default"].fn[NAME] = Treeview._jQueryInterface;
  $__default["default"].fn[NAME].Constructor = Treeview;

  $__default["default"].fn[NAME].noConflict = function () {
    $__default["default"].fn[NAME] = JQUERY_NO_CONFLICT;
    return Treeview._jQueryInterface;
  };
  
/* ******** */

  //exports.CardRefresh = CardRefresh;
  //exports.CardWidget = CardWidget;
  //exports.ControlSidebar = ControlSidebar;
  //exports.DirectChat = DirectChat;
  //exports.Dropdown = Dropdown;
  //exports.ExpandableTable = ExpandableTable;
  exports.Fullscreen = Fullscreen;
  exports.IFrame = IFrame;
  //exports.Layout = Layout;
  //exports.NavbarSearch = NavbarSearch;
  exports.PushMenu = PushMenu;
  //exports.SidebarSearch = SidebarSearch;
  exports.Toasts = Toasts;
  //exports.TodoList = TodoList;
  exports.Treeview = Treeview;

  Object.defineProperty(exports, '__esModule', { value: true });

}));

//# sourceMappingURL=5SeasonLifeAdmin.js.map
