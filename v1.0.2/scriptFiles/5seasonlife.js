/*
        var avatarUrl = "https://mockmind-api.uifaces.co/content/human/" + randomNumber + ".jpg"; // 生成隨機人臉頭像 URL
        var newHeroBg = "https://picsum.photos/1920/1080/?" + randomNumber;
        var newIntroImg = "https://picsum.photos/600/450/?" + randomNumber;
https://mockmind-api.uifaces.co/content/human/222.jpg
*/
/**
 * ==========================================================================
 * 5SL 專案核心控制邏輯 (5seasonlife.js) - 前端體感極致重構完美版
 * ==========================================================================
 */

$(document).ready(function () {
  /* ==========================================================================
       1. 大底圖自動定時換圖控制 (雙圖層智慧 Cross Fade + 預載入)
       ========================================================================== */
  const $hero = $("#heroSection");
  const $layerA = $("#heroBgLayerA");
  const $layerB = $("#heroBgLayerB");
  const opacityValue = 0.18;
  const bgImages = [];

  // 安全取得原生屬性節點，建立無限換圖陣列
  if ($hero.length) {
    const nativeHero = $hero.get(0);
    if (nativeHero && nativeHero.attributes) {
      $.each(nativeHero.attributes, function () {
        if (this.name.indexOf("data-bg") === 0) {
          bgImages.push(this.value);
        }
      });
    }
  }

  // 幕後圖片預載入
  function preloadImages(urls) {
    $.each(urls, function (index, url) {
      const img = new Image();
      img.src = url;
    });
  }
  if (bgImages.length > 0) {
    preloadImages(bgImages);
  }

  let currentBgIndex = 0;

  function changeSliderBackground() {
    if (bgImages.length === 0) return;

    const nextImageUrl = bgImages[currentBgIndex];
    const nextStyle =
      "linear-gradient(rgba(0,0,0," +
      opacityValue +
      "), rgba(0,0,0," +
      opacityValue +
      ")), url('" +
      nextImageUrl +
      "')";

    if ($layerA.hasClass("active")) {
      $layerB.css("background-image", nextStyle).addClass("active");
      $layerA.removeClass("active");
    } else {
      $layerA.css("background-image", nextStyle).addClass("active");
      $layerB.removeClass("active");
    }

    currentBgIndex = (currentBgIndex + 1) % bgImages.length;
  }

  // 初始載入第一張
  if (bgImages.length > 0) {
    $layerA.css(
      "background-image",
      "linear-gradient(rgba(0,0,0," +
        opacityValue +
        "), rgba(0,0,0," +
        opacityValue +
        ")), url('" +
        bgImages[0] +
        "')",
    );
    currentBgIndex = 1 % bgImages.length;
  }

  // 每 6 秒交叉換圖一次
  setInterval(changeSliderBackground, 6000);

  /* ==========================================================================
       2. 導航列通用操作優化 (手機版點擊連結自動收合＋同步解除 Mask 遮罩)
       ========================================================================== */
  $(".navbar-nav .nav-link:not(.dropdown-toggle), .dropdown-item").on(
    "click",
    function () {
      if ($(window).width() < 992) {
        const $navCollapse = $("#mainNavbar");
        const $navToggler = $('[data-bs-target="#mainNavbar"]');

        if ($navCollapse.hasClass("show") && $navToggler.length > 0) {
          // 模擬點擊漢堡按鈕，讓 Bootstrap Collapse 實例與 Mask 遮罩同步正常關閉
          $navToggler.trigger("click");
        }
      }
    },
  );
  /* ==========================================================================
       3. 手機版手勢擴充：原生 Swipe 手勢監聽 (防輪播誤觸 + 邊緣精準感應版)
       ========================================================================== */
  const $sidebar = $("#mainNavbar");
  const $toggleBtn = $('[data-bs-target="#mainNavbar"]');

  if ($sidebar.length > 0 && $toggleBtn.length > 0) {
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;
    let ignoreNavSwipe = false;

    const minSwipeDistanceX = 45; // 左右滑動超過 45px 才觸發
    const maxSwipeDistanceY = 50; // 上下滑動限制在 50px 內，避免與頁面滾動衝突

    window.addEventListener(
      "touchstart",
      function (e) {
        // 防誤觸保護：若手指按在「課程&活動花絮」或「價格方案」水平滑動區塊內，不觸發導航選單
        if (
          e.target &&
          e.target.closest(".fsl-highlights-viewport, .fsl-pricing-viewport")
        ) {
          ignoreNavSwipe = true;
          return;
        }
        ignoreNavSwipe = false;

        let touch =
          (e.touches && e.touches.item(0)) ||
          (e.changedTouches && e.changedTouches.item(0)) ||
          e;
        touchStartX = touch.clientX || touch.pageX || 0;
        touchStartY = touch.clientY || touch.pageY || 0;
      },
      { passive: true },
    );

    window.addEventListener(
      "touchend",
      function (e) {
        if (ignoreNavSwipe) {
          ignoreNavSwipe = false;
          return;
        }

        let touch =
          (e.changedTouches && e.changedTouches.item(0)) ||
          (e.touches && e.touches.item(0)) ||
          e;
        touchEndX = touch.clientX || touch.pageX || 0;
        touchEndY = touch.clientY || touch.pageY || 0;
        handleSwipeGesture();
      },
      { passive: true },
    );

    function handleSwipeGesture() {
      const distanceX = touchEndX - touchStartX;
      const distanceY = Math.abs(touchEndY - touchStartY);

      if (distanceY > maxSwipeDistanceY) return;

      const isOpen = $sidebar.hasClass("show");

      // 狀況 A：右滑 (拉出選單) -> 僅限從螢幕最左側邊緣 35px 內劃入才有效，徹底避免日常滑動誤觸
      if (distanceX > minSwipeDistanceX && !isOpen) {
        if (touchStartX <= 35) {
          if ($toggleBtn.length > 0) {
            $toggleBtn.click();
          }
        }
      }
      // 狀況 B：左滑 (收回選單)
      else if (distanceX < -minSwipeDistanceX && isOpen) {
        if ($toggleBtn.length > 0) {
          $toggleBtn.click();
        }
      }
    }
  }

  /* ==========================================================================
       4. 測試懸浮鈕與正式登入鈕：真·雙向狀態切換中控引擎 (100% 整合隨機 Badge 與手機連結)
       ========================================================================== */
  $(document).on(
    "click",
    "#btnToggleAuthStatus, .btn-guest-login",
    function (e) {
      e.preventDefault();

      // 智慧判斷：檢查目前電腦版登入按鈕是否被隱藏，來得知當前是登入還是登出
      var isCurrentlyLoggedOut = !$("#pcLoginBtn").hasClass("d-none");

      if (isCurrentlyLoggedOut) {
        console.log("【中控系統】執行模擬登入 ➔ 狀態切換至會員中心");

        var randomNumber = Math.floor(Math.random() * 220) + 1;

        // 100% 固化保留你親自調校完美的傳統拼接網址與精確解析度規格
        var avatarUrl = "https://mockmind-api.uifaces.co/content/human/" + randomNumber + ".jpg";
        var newHeroBg = "https://picsum.photos/1920/1080/?" + randomNumber;
        var newIntroImg = "https://picsum.photos/600/450/?" + randomNumber;

        // 精確塞入各版面結構節點
        $("#pcUserDropdown img").attr("src", avatarUrl);
        $("#mobileMemberState img").attr("src", avatarUrl);
        $(".hero-bg").attr("src", newHeroBg);
        $(".intro-img").attr("src", newIntroImg);

        // 未完成事件 Badge 動態演算處理
        var eventCount = Math.floor(Math.random() * 9) + 1;
        var isAlarm = Math.floor(Math.random() * 2);
        var $badges = $("#pcAvatarBadge, #mobileAvatarBadge");

        $badges.removeClass(
          "bg-danger bg-warning bg-info bg-success badge-alarm-pulse d-none",
        );
        $badges.text(eventCount);

        if (isAlarm === 1) {
          $badges.addClass("bg-danger badge-alarm-pulse"); // 緊急紅色呼吸燈
        } else {
          $badges.addClass("bg-info"); // 日常藍色提示
        }

        // 手機版關鍵修復：亮起手機選單底部的「會員中心」黃色粗體連結
        $(".mobile-member-link").removeClass("d-none");

        // 執行 RWD 結構切換：關閉訪客按鈕，亮起對應會員組件
        $("#pcLoginBtn").addClass("d-none");
        $("#pcUserDropdown").removeClass("d-none");
        $("#mobileGuestState").addClass("d-none");
        $("#mobileMemberState").removeClass("d-none");
      } else {
        console.log("【中控系統】執行模擬登出 ➔ 狀態還原至訪客身分");

        // 強制隱藏所有通知 Badge 小圈圈
        $("#pcAvatarBadge, #mobileAvatarBadge").addClass("d-none");

        // 手機版關鍵修復：隱藏手機選單底部的「會員中心」連結
        $(".mobile-member-link").addClass("d-none");

        // 執行 RWD 結構還原：退回訪客模式
        $("#pcUserDropdown").addClass("d-none");
        $("#pcLoginBtn").removeClass("d-none");
        $("#mobileMemberState").addClass("d-none");
        $("#mobileGuestState").removeClass("d-none");
      }
    },
  );

  // 額外綁定導航列內部下拉選單的「安全登出」連結，直接調用中控引擎，不寫重疊死碼
  $(document).on("click", "#pcLogoutBtn, #mobileLogoutBtn", function (e) {
    e.preventDefault();
    if ($("#pcLoginBtn").hasClass("d-none")) {
      $("#btnToggleAuthStatus").click();
    }
  });

  /* ==========================================================================
       5. 區塊動畫：1.8 秒超長滑行、緩入不急停智慧監聽 (IntersectionObserver)
       ========================================================================== */
  if ("IntersectionObserver" in window) {
    const observerOptions = {
      root: null,
      rootMargin: "0px 0px -50px 0px",
      threshold: 0.35, // 區塊露出 35% 準時啟動動畫
    };

    const scrollObserver = new IntersectionObserver(function (
      entries,
      observer,
    ) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          $(entry.target).addClass("active");
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    $(".reveal-on-scroll, .reveal-from-left, .reveal-from-right").each(
      function () {
        scrollObserver.observe(this);
      },
    );
  } else {
    $(".reveal-on-scroll, .reveal-from-left, .reveal-from-right").addClass(
      "active",
    );
  }

  /* ==========================================================================
       6. 數據動效：營運數據數字滾動飆升計數器
       ========================================================================== */
  var $counters = $(".stat-number");

  if ($counters.length > 0 && "IntersectionObserver" in window) {
    var counterObserver = new IntersectionObserver(
      function (entries, observer) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var $this = $(entry.target);
            var targetValue = parseInt($this.attr("data-target"), 10) || 0;

            // 利用 jQuery 動態增量控制，達到 1.8 秒與卡片進場同步的緩入效果
            $({ countNum: 0 }).animate(
              { countNum: targetValue },
              {
                duration: 1800,
                easing: "swing",
                step: function () {
                  $this.text(Math.floor(this.countNum).toLocaleString());
                },
                complete: function () {
                  $this.text(targetValue.toLocaleString());
                },
              },
            );
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 },
    );

    $counters.each(function () {
      counterObserver.observe(this);
    });
  }

  /* ==========================================================================
       7. 圖庫特效：作品集滑鼠方位追蹤 3D 空間傾斜引擎
       ========================================================================== */
  if (window.innerWidth > 992) {
    $(".portfolio-card")
      .on("mousemove", function (e) {
        var $card = $(this);
        var w = $card.width();
        var h = $card.height();
        var offsets = $card.offset();
        var x = e.pageX - offsets.left - w / 2;
        var y = e.pageY - offsets.top - h / 2;

        // 計算傾斜角度，最大安全範疇限制在 24 度內，防範畫面劇烈抖動
        var rotateX = (-y / h) * 24;
        var rotateY = (x / w) * 24;

        $card.css(
          "transform",
          "rotateX(" + rotateX + "deg) rotateY(" + rotateY + "deg)",
        );
      })
      .on("mouseleave", function () {
        // 游標滑出時，溫和地歸位歸零
        $(this).css("transform", "rotateX(0deg) rotateY(0deg)");
      });
  }

  /* ==========================================================================
       8. 頂級景深：原生滾動視差景深引擎 (requestAnimationFrame 幀率節流)
       ========================================================================== */
  var $parallaxZones = $(".parallax-bg-zone");

  if ($parallaxZones.length > 0) {
    var ticked = false;

    function updateParallax() {
      var scrollTop = $(window).scrollTop();
      var windowHeight = $(window).height();

      $parallaxZones.each(function () {
        var $zone = $(this);
        var $bg = $zone.find(".parallax-bg");
        if ($bg.length === 0) return;

        var zoneTop = $zone.offset().top;
        var zoneHeight = $zone.outerHeight();

        // 智慧性能防線：只有當該區塊進入螢幕視野時，才啟動圖層位移計算
        if (
          scrollTop + windowHeight > zoneTop &&
          scrollTop < zoneTop + zoneHeight
        ) {
          var relativeScroll = scrollTop - zoneTop;
          var translateY = relativeScroll * 0.55; // 從 0.3 調高至 0.55，大幅提升移動速度與範圍
          $bg.css("transform", "translate3d(0, " + translateY + "px, 0)");
        }
      });
      ticked = false;
    }

    // 監聽全網頁滾動事件，並透過 requestAnimationFrame 進行顯示卡渲染節流，防止掉幀卡頓
    $(window).on("scroll", function () {
      if (!ticked) {
        window.requestAnimationFrame(updateParallax);
        ticked = true;
      }
    });
  }

  /* ==========================================================================
       9. 影音展示中控：自適應 Lightbox 智慧讀取與銷毀機制 (無障礙焦點修正 + YouTube 播放修正版)
       ========================================================================== */
  var $videoModal = $("#videoLightboxModal");
  var $videoIframe = $("#lightboxIframe");

  if ($videoModal.length > 0 && $videoIframe.length > 0) {
    $(document).on("click", ".video-play-btn", function () {
      var rawUrl = $(this).attr("data-video-url") || "";
      var videoId = "";

      // 解析標準網址 (v=...) 與短網址 (youtu.be/...)
      if (rawUrl.indexOf("v=") !== -1) {
        videoId = rawUrl.split("v=").pop().split("&").shift();
      } else if (rawUrl.indexOf("youtu.be/") !== -1) {
        videoId = rawUrl.split("youtu.be/").pop().split("?").shift();
      }

      if (videoId !== "") {
        var embedPath = "em" + "bed";
        var finalEmbedUrl =
          "https://youtube.com" +
          "/" +
          embedPath +
          "/" +
          videoId +
          "?autoplay=1&rel=0&playsinline=1";

        $videoIframe.attr("src", finalEmbedUrl);
      }
    });

    // 1. 視窗「準備關閉」瞬間：強制移除內部關閉鈕焦點，徹底消除 Blocked aria-hidden 警告
    $videoModal.on("hide.bs.modal", function () {
      if (document.activeElement && this.contains(document.activeElement)) {
        document.activeElement.blur();
      }
    });

    // 2. 視窗「完全關閉」後：清空 iframe 網址，確保背景停止播放聲音
    $videoModal.on("hidden.bs.modal", function () {
      $videoIframe.attr("src", "");
    });
  }

  /* ==========================================================================
       10. 價格方案無限多階可擴充景深輪播引擎（支援 hottest 置中 + 任意數量擴充）
       ========================================================================== */
  const $pricingStage = $("#fslPricingStage");
  const $pricingViewport = $("#fslPricingViewport");

  if ($pricingStage.length > 0) {
    const $realSlides = $pricingStage.children(".fsl-plan-slide");
    const realCount = $realSlides.length;

    // 偵測哪一張卡片擁有 .hottest 類別，作為初始置中目標；若無則預設取正中間項目
    let activeRealIndex = Math.floor(realCount / 2);
    $realSlides.each(function (idx) {
      if ($(this).hasClass("hottest") || $(this).find(".hottest").length > 0) {
        activeRealIndex = idx;
        return false;
      }
    });

    // 頭尾各自動補上 3 張空白平衡卡片，確保任何多階視窗下左右兩側皆能對稱延伸
    const padCount = 3;
    const spacerHtml =
      '<div class="fsl-plan-slide fsl-plan-spacer" aria-hidden="true">' +
      '<div class="card h-100 fsl-plan-card"><div class="card-body"></div></div>' +
      "</div>";
    $pricingStage.prepend(spacerHtml.repeat(padCount));
    $pricingStage.append(spacerHtml.repeat(padCount));

    const $allSlides = $pricingStage.children(".fsl-plan-slide");

    // 動態建立對應數量的底部指示點
    const $dotsContainer = $("#fslPricingDots");
    if ($dotsContainer.length > 0) {
      $dotsContainer.empty();
      for (let i = 0; i < realCount; i++) {
        $dotsContainer.append(
          '<button type="button" class="fsl-pricing-dot" data-target-idx="' +
            i +
            '" aria-label="切換至第 ' +
            (i + 1) +
            ' 個方案"></button>',
        );
      }
    }

    // 依據當前螢幕寬度回傳多階景深參數（提高最兩側透明度，讓邊界空白卡片與側翼方案清晰可見）
    function getDepthConfig() {
      const w = $(window).width();
      if (w >= 1400) {
        return {
          maxVisibleTier: 3,
          offsets: new Array(0, 300, 520, 680),
          scales: [1, 0.85, 0.66, 0.48],
          opacities: [1, 0.82, 0.58, 0.36],
        };
      } else if (w >= 992) {
        return {
          maxVisibleTier: 2,
          offsets: new Array(0, 295, 510),
          scales: [1, 0.84, 0.65],
          opacities: [1, 0.8, 0.56],
        };
      } else if (w >= 576) {
        return {
          maxVisibleTier: 2,
          offsets: new Array(0, 220, 360),
          scales: [1, 0.8, 0.52],
          opacities: [1, 0.72, 0.42],
        };
      } else {
        return {
          maxVisibleTier: 1,
          offsets: new Array(0, 155),
          scales: [1, 0.76],
          opacities: [1, 0.5],
        };
      }
    }

    function renderPricingCarousel() {
      const centerDomIndex = activeRealIndex + padCount;
      const cfg = getDepthConfig();

      $allSlides.each(function (domIdx) {
        const diff = domIdx - centerDomIndex;
        const absDiff = Math.abs(diff);
        const dir = diff === 0 ? 0 : diff > 0 ? 1 : -1;
        const $slide = $(this);
        const isSpacer = $slide.hasClass("fsl-plan-spacer");

        $slide
          .removeClass("is-center is-side-clickable is-far-blur")
          .attr("data-diff", diff);

        if (absDiff === 0) {
          $slide.addClass("is-center").css({
            transform: "translate3d(0px, 0, 0) scale(1)",
            opacity: 1,
            zIndex: 50,
            pointerEvents: "auto",
          });
        } else if (absDiff <= cfg.maxVisibleTier) {
          const tx = dir * cfg.offsets[absDiff];
          const sc = cfg.scales[absDiff];
          const op = cfg.opacities[absDiff];

          // 所有可見範圍內的真實方案卡片皆可點擊置中；空白卡片則作為清晰邊界提示
          $slide.addClass(isSpacer ? "is-far-blur" : "is-side-clickable").css({
            transform: "translate3d(" + tx + "px, 0, 0) scale(" + sc + ")",
            opacity: op,
            zIndex: 50 - absDiff * 10,
            pointerEvents: !isSpacer ? "auto" : "none",
          });
        } else {
          const maxOffset = cfg.offsets[cfg.offsets.length - 1] + 140;
          $slide.addClass("is-far-blur").css({
            transform:
              "translate3d(" + dir * maxOffset + "px, 0, 0) scale(0.25)",
            opacity: 0,
            zIndex: 1,
            pointerEvents: "none",
          });
        }
      });

      $("#fslPricingPrev").prop("disabled", activeRealIndex <= 0);
      $("#fslPricingNext").prop("disabled", activeRealIndex >= realCount - 1);

      $dotsContainer.find(".fsl-pricing-dot").removeClass("active");
      $dotsContainer
        .find('.fsl-pricing-dot[data-target-idx="' + activeRealIndex + '"]')
        .addClass("active");
    }


    function goToPlan(newIdx) {
      if (newIdx < 0) newIdx = 0;
      if (newIdx >= realCount) newIdx = realCount - 1;
      activeRealIndex = newIdx;
      renderPricingCarousel();
    }

    renderPricingCarousel();

    // 視窗寬度改變時自動重算多階景深座標
    $(window)
      .off("resize.fsl_pricing")
      .on("resize.fsl_pricing", function () {
        renderPricingCarousel();
      });

    $("#fslPricingPrev").on("click", function () {
      goToPlan(activeRealIndex - 1);
    });

    $("#fslPricingNext").on("click", function () {
      goToPlan(activeRealIndex + 1);
    });

    // 點擊任意可見側翼卡片，自動計算差值並平滑切換至正中央
    $pricingStage.on("click", ".fsl-plan-slide.is-side-clickable", function () {
      const diff = parseInt($(this).attr("data-diff"), 10) || 0;
      if (diff !== 0) {
        goToPlan(activeRealIndex + diff);
      }
    });

    $dotsContainer.on("click", ".fsl-pricing-dot", function () {
      const targetIdx = parseInt($(this).attr("data-target-idx"), 10);
      goToPlan(targetIdx);
    });

    // 滑鼠拖曳與觸控左右滑動支援
    let dragStartX = 0;
    let isDragging = false;
    const swipeThreshold = 45;

    $pricingViewport.on("mousedown touchstart", function (e) {
      if ($(e.target).closest("a, button").length) return;
      isDragging = true;
      const point = e.type === "touchstart" ? e.originalEvent.touches.item(0) : e;
      dragStartX = point.clientX;
    });

    $(window).on("mouseup touchend", function (e) {
      if (!isDragging) return;
      isDragging = false;
      const point =
        e.type === "touchend" ? e.originalEvent.changedTouches.item(0) : e;
      const deltaX = point.clientX - dragStartX;

      if (deltaX > swipeThreshold) {
        goToPlan(activeRealIndex - 1);
      } else if (deltaX < -swipeThreshold) {
        goToPlan(activeRealIndex + 1);
      }
    });
  }

  /* ==========================================================================
       11. 課程&活動花絮水平滑動藝廊引擎（GPU 物理慣性滑動 + 大圖預覽 + 影片彈窗）
       ========================================================================== */
  const $highlightsViewport = $("#fslHighlightsViewport");
  const $highlightsTrack = $("#fslHighlightsTrack");

  if ($highlightsViewport.length > 0 && $highlightsTrack.length > 0) {
    const viewportEl = $highlightsViewport.get(0);
    const trackEl = $highlightsTrack.get(0);
    const $prevBtn = $("#fslHighlightsPrev");
    const $nextBtn = $("#fslHighlightsNext");

    let currentX = 0;
    let minX = 0;
    let isDown = false;
    let startPointerX = 0;
    let startTrackX = 0;
    let lastPointerX = 0;
    let lastTime = 0;
    let velocity = 0;
    let rafId = null;
    let totalDragDistance = 0; // 每次按下滑鼠時歸零，精準記錄本次是否為拖曳行為

    function recalcBounds() {
      const viewW = viewportEl.clientWidth;
      const trackW = trackEl.scrollWidth;
      minX = Math.min(0, viewW - trackW);
      if (currentX < minX) currentX = minX;
      if (currentX > 0) currentX = 0;
      applyTransform(currentX);
      updateNavButtons();
    }

    function applyTransform(x) {
      trackEl.style.transform = "translate3d(" + x.toFixed(2) + "px, 0, 0)";
    }

    function updateNavButtons() {
      $prevBtn.prop("disabled", currentX >= -2);
      $nextBtn.prop("disabled", currentX <= minX + 2);
    }

    function stopAnimation() {
      if (rafId !== null) {
        cancelAnimationFrame(rafId);
        rafId = null;
      }
      $highlightsViewport.removeClass("is-gliding");
    }

    function getScrollStep() {
      const $firstItem = $highlightsTrack.find(".fsl-highlight-item").first();
      const cardW = $firstItem.length > 0 ? $firstItem.outerWidth(true) : 314;
      return window.innerWidth < 768 ? cardW : cardW * 2;
    }

    function smoothGlideBy(distance) {
      stopAnimation();
      const startPos = currentX;
      const targetPos = Math.max(minX, Math.min(0, startPos + distance));
      if (Math.abs(targetPos - startPos) < 1) return;

      const duration = 520;
      const startTime = performance.now();
      $highlightsViewport.addClass("is-gliding");

      function step(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeOut = 1 - Math.pow(1 - progress, 3);
        currentX = startPos + (targetPos - startPos) * easeOut;
        applyTransform(currentX);

        if (progress < 1) {
          rafId = requestAnimationFrame(step);
        } else {
          rafId = null;
          $highlightsViewport.removeClass("is-gliding");
          updateNavButtons();
        }
      }

      rafId = requestAnimationFrame(step);
    }

    $prevBtn.on("click", function () {
      smoothGlideBy(getScrollStep());
    });

    $nextBtn.on("click", function () {
      smoothGlideBy(-getScrollStep());
    });

    function getClientX(e) {
      if (e.type.indexOf("touch") === 0) {
        const touchList = e.originalEvent.touches.length
          ? e.originalEvent.touches
          : e.originalEvent.changedTouches;
        return touchList.item(0).clientX;
      }
      return e.clientX;
    }

    $highlightsViewport.on("mousedown touchstart", function (e) {
      if (e.type === "mousedown" && e.button !== 0) return;
      stopAnimation();
      isDown = true;
      totalDragDistance = 0; // 每次按下皆重置拖曳距離
      const px = getClientX(e);
      startPointerX = px;
      lastPointerX = px;
      startTrackX = currentX;
      lastTime = performance.now();
      velocity = 0;
      // 💡 關鍵修正：按下瞬間不立即加 .is-dragging，避免 pointer-events: none 吃掉正常點擊事件
    });

    $(window).on("mousemove.fsl_highlights touchmove.fsl_highlights", function (e) {
      if (!isDown) return;
      if (e.type === "mousemove") e.preventDefault();

      const now = performance.now();
      const px = getClientX(e);
      const dt = now - lastTime;
      const dx = px - lastPointerX;

      totalDragDistance = Math.max(
        totalDragDistance,
        Math.abs(px - startPointerX),
      );

      // 只有真正移動超過 6px 確認為「拖曳行為」時，才啟用 .is-dragging 暫停卡片 3D 重繪
      if (totalDragDistance > 6 && !$highlightsViewport.hasClass("is-dragging")) {
        $highlightsViewport.addClass("is-dragging");
      }

      if (dt > 0) {
        const instantVel = (dx / dt) * 16.67;
        velocity = velocity * 0.35 + instantVel * 0.65;
      }

      let nextX = startTrackX + (px - startPointerX);
      if (nextX > 0) nextX = nextX * 0.25;
      if (nextX < minX) nextX = minX + (nextX - minX) * 0.25;

      currentX = nextX;
      applyTransform(currentX);

      lastPointerX = px;
      lastTime = now;
    });

    $(window).on("mouseup.fsl_highlights touchend.fsl_highlights", function () {
      if (!isDown) return;
      isDown = false;
      $highlightsViewport.removeClass("is-dragging");

      if (performance.now() - lastTime > 80) {
        velocity = 0;
      }

      if (currentX > 0) {
        smoothGlideBy(-currentX);
        return;
      }
      if (currentX < minX) {
        smoothGlideBy(minX - currentX);
        return;
      }

      if (Math.abs(velocity) >= 0.8) {
        $highlightsViewport.addClass("is-gliding");

        function inertiaLoop() {
          currentX += velocity;
          velocity *= 0.94;

          if (currentX >= 0) {
            currentX = 0;
            applyTransform(currentX);
            stopAnimation();
            updateNavButtons();
            return;
          }
          if (currentX <= minX) {
            currentX = minX;
            applyTransform(currentX);
            stopAnimation();
            updateNavButtons();
            return;
          }

          applyTransform(currentX);

          if (Math.abs(velocity) < 0.3) {
            stopAnimation();
            updateNavButtons();
            return;
          }

          rafId = requestAnimationFrame(inertiaLoop);
        }

        rafId = requestAnimationFrame(inertiaLoop);
      } else {
        updateNavButtons();
      }
    });

    // 僅當本次滑鼠拖曳距離超過 8px 時，才攔截一次點擊事件
    viewportEl.addEventListener(
      "click",
      function (e) {
        if (totalDragDistance > 8) {
          e.preventDefault();
          e.stopPropagation();
          totalDragDistance = 0;
        }
      },
      true,
    );

    // 點擊花絮卡片：純圖片開啟大圖 Preview 燈箱（影片卡片交由 .video-play-btn 開啟影片燈箱）
    $highlightsTrack.on("click", ".fsl-highlight-card", function (e) {
      if (totalDragDistance > 8) return;
      if ($(e.target).closest(".video-play-btn").length > 0) return;

      // 若整張卡片是影片卡片且使用者點在圖片空白處，一樣自動觸發它的影片播放鈕
      const $playBtn = $(this).find(".video-play-btn");
      if ($playBtn.length > 0) {
        $playBtn.trigger("click");
        return;
      }

      const imgSrc = $(this).find("img").attr("src") || "";
      const titleText = $(this).find(".portfolio-overlay h6").text() || "";
      const descText = $(this).find(".portfolio-overlay p").text() || "";

      if (imgSrc !== "") {
        $("#previewLightboxImg").attr("src", imgSrc);
        $("#previewLightboxTitle").text(titleText);
        $("#previewLightboxDesc").text(descText);

        const previewModalEl = document.getElementById("imagePreviewModal");
        if (previewModalEl && window.bootstrap) {
          bootstrap.Modal.getOrCreateInstance(previewModalEl).show();
        }
      }
    });

    $(window)
      .off("resize.fsl_highlights")
      .on("resize.fsl_highlights", recalcBounds);

    recalcBounds();
    setTimeout(recalcBounds, 300);
  }

  /* ==========================================================================
       12. 常見問題 FAQ 分類標籤快速篩選控制
       ========================================================================== */
  const $faqFilterBar = $("#fslFaqFilterBar");

  if ($faqFilterBar.length > 0) {
    $faqFilterBar.on("click", ".fsl-faq-filter-btn", function () {
      const $btn = $(this);
      const filterCat = $btn.attr("data-faq-filter");

      $faqFilterBar.find(".fsl-faq-filter-btn").removeClass("active");
      $btn.addClass("active");

      const $items = $("#faqAccordion .accordion-item");
      if (filterCat === "all") {
        $items.stop(true, true).fadeIn(250);
      } else {
        $items.each(function () {
          const $item = $(this);
          if ($item.attr("data-faq-cat") === filterCat) {
            $item.stop(true, true).fadeIn(250);
          } else {
            $item.stop(true, true).hide();
          }
        });
      }
    });
  }

  /* ==========================================================================
       14. 更多花絮頁面 (5sl_highlights.php)：支援圖檔 / YouTube視訊 / 聲音檔三種 Lightbox
       ========================================================================== */
  const $highlightsGrid = $("#fslHighlightsGrid");

  if ($highlightsGrid.length > 0) {
    $highlightsGrid.on("click", ".fsl-grid-highlight-card", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $card    = $(this);
      const mType    = parseInt($card.attr("data-media-type"), 10) || 1;
      const mainImg  = $card.attr("data-main-img") || $card.find("img").attr("src") || "";
      const mediaUrl = ($card.attr("data-media-url") || "").trim();
      const title    = $card.attr("data-title") || $card.find(".portfolio-overlay h6").text() || "";
      const desc     = $card.attr("data-desc") || $card.find(".portfolio-overlay p").text() || "";
      const dateStr  = $card.attr("data-date") || "";

      if (mType === 2 && mediaUrl !== "") {
        let ytId = "";
        if (mediaUrl.indexOf("v=") !== -1) {
          ytId = mediaUrl.split("v=").pop().split("&").shift();
        } else if (mediaUrl.indexOf("youtu.be/") !== -1) {
          ytId = mediaUrl.split("youtu.be/").pop().split("?").shift();
        } else if (mediaUrl.indexOf("/embed/") !== -1) {
          ytId = mediaUrl.split("/embed/").pop().split("?").shift();
        }

        if (ytId !== "") {
          const embedSrc = "https://" + "youtube.com" + "/em" + "bed/" + ytId + "?autoplay=1&rel=0&playsinline=1";
          $("#hlVideoLightboxTitle").text((dateStr ? "[" + dateStr + "] " : "") + title);
          $("#hlLightboxIframe").attr("src", embedSrc);
          const vModalEl = document.getElementById("hlVideoLightboxModal") || document.getElementById("videoLightboxModal");
          if (vModalEl) bootstrap.Modal.getOrCreateInstance(vModalEl).show();
        }
      } else if (mType === 3 && mediaUrl !== "") {
        $("#audioLightboxCover").attr("src", mainImg);
        $("#audioLightboxDate").text(dateStr);
        $("#audioLightboxTitle").text(title);
        $("#audioLightboxDesc").text(desc);
        const player = document.getElementById("audioLightboxPlayer");
        if (player) {
          player.src = mediaUrl;
          bootstrap.Modal.getOrCreateInstance(document.getElementById("audioLightboxModal")).show();
          player.play().catch(function () {});
        }
      } else if (mainImg !== "") {
        $("#previewLightboxImg").attr("src", mainImg);
        $("#previewLightboxTitle").text(title);
        $("#previewLightboxDesc").text(desc);
        const pModalEl = document.getElementById("imagePreviewModal");
        if (pModalEl) bootstrap.Modal.getOrCreateInstance(pModalEl).show();
      }
    });

    $("#fslLoadMoreHighlightsBtn").on("click", function () {
      const $hiddenCards = $highlightsGrid.find(".fsl-extra-card.d-none");
      $hiddenCards.slice(0, 8).removeClass("d-none").hide().fadeIn(350);
      if ($highlightsGrid.find(".fsl-extra-card.d-none").length === 0) {
        $(this).fadeOut(250);
      }
    });

    $("#fslGalleryFilterBar").on("click", ".fsl-faq-filter-btn", function () {
      const $btn = $(this);
      const filter = $btn.attr("data-gallery-filter");

      $("#fslGalleryFilterBar .fsl-faq-filter-btn").removeClass("active");
      $btn.addClass("active");

      const $allCols = $highlightsGrid.find(".fsl-grid-card-col");

      if (filter === "all") {
        $allCols.each(function () {
          const $col = $(this);
          if ($col.hasClass("fsl-extra-card") && $col.hasClass("d-none")) {
            $col.hide();
          } else {
            $col.stop(true, true).fadeIn(250);
          }
        });
        if ($highlightsGrid.find(".fsl-extra-card.d-none").length > 0) {
          $("#fslLoadMoreHighlightsBtn").fadeIn(250);
        }
      } else {
        $("#fslLoadMoreHighlightsBtn").hide();
        $allCols.each(function () {
          const $col = $(this);
          let isMatch = false;
          if (filter === "video") isMatch = $col.attr("data-has-video") === "1";
          else if (filter === "audio") isMatch = $col.attr("data-has-audio") === "1";
          else isMatch = $col.attr("data-cat") === filter;

          if (isMatch) {
            $col.removeClass("d-none").stop(true, true).fadeIn(250);
          } else {
            $col.stop(true, true).hide();
          }
        });
      }
    });
  }

  /* ==========================================================================
    全站通用：一鍵加入購物車 / 立即預約觸發器 (.btn-fsl-add-cart)
    ========================================================================== */
  $(document).on('click', '.btn-fsl-add-cart', function(e) {
      e.preventDefault();
      const $btn      = $(this);
      const itemType  = $btn.attr('data-type') || '1';
      const courseId  = $btn.attr('data-course-id') || 0;
      const sessId    = $btn.attr('data-sess-id') || 0;
      const merchId   = $btn.attr('data-merch-id') || 0;
      const qty       = $btn.attr('data-qty') || 1;
      const redirect  = $btn.attr('data-redirect') === '1';

      if (window.fslLoader) window.fslLoader.show();

      $.post('./funcFiles/5sl_act_member.php', {
          action: 'act_cart_add',
          item_type: itemType,
          course_id: courseId,
          sess_id: sessId,
          merch_id: merchId,
          qty: qty
      }, function(res) {
          if (window.fslLoader) window.fslLoader.hide();

          if (res.success === 1) {
              // 同步更新頂部導覽列與側邊欄購物車數量徽章
              if (res.cart_count > 0) {
                  $('#navCartBadge, #subnaviCartBadge').text(res.cart_count).removeClass('d-none');
              } else {
                  $('#navCartBadge, #subnaviCartBadge').text('0').addClass('d-none');
              }              
              // 更新頂部導覽列購物車數量徽章（若有 #navCartBadge 元素）
              /*
              if ($('#navCartBadge').length) {
                  $('#navCartBadge').text(res.cart_count).removeClass('d-none');
              }
              */
              if (redirect) {
                  window.location.href = '5sl_myCart.php';
              } else {
                  if (confirm(`「${res.item_title}」已加入購物車（目前共 ${res.cart_count} 項）！\n\n是否立即前往結帳？`)) {
                      window.location.href = '5sl_myCart.php';
                  }
              }
          } else if (res.need_login === 1) {
              alert('請先登入會員帳號，即可立即預約與結帳！');
              window.location.href = '5sl_login.php';
          } else {
              alert(res.message || '加入購物車失敗，請稍後再試。');
          }
      }, 'json').fail(function() {
          if (window.fslLoader) window.fslLoader.hide();
          alert('網路連線異常，請稍後再試。');
      });
  });

/* ==========================================================================
   全站通用：一鍵加入/取消「收藏與追蹤」 (.btn-fsl-fav) 負責「加入購物車／前往報名／立即購買」（
   例如 5sl_myConsole.php 收藏卡片上的「前往報名」、「立即購買」按鈕，以及首頁課程卡片的購買按
   鈕，都會靠它把項目寫入 fsl_cart_items 並跳轉 5sl_myCart.php）。
   ========================================================================== */
$(document).on('click', '.btn-fsl-fav', function(e) {
    e.preventDefault();
    const $btn       = $(this);
    const targetType = $btn.attr('data-type') || '1';
    const targetId   = $btn.attr('data-id') || '0';
    const op         = $btn.attr('data-op') || 'toggle'; // toggle 或 remove

    $.post('./funcFiles/5sl_act_member.php', {
        action: 'act_toggle_favorite',
        target_type: targetType,
        target_id: targetId,
        op: op
    }, function(res) {
        if (res.success === 1) {
            if (op === 'remove') {
                // 在「收藏與追蹤」列表頁直接淡出該張卡片並更新總數徽章
                $btn.closest('.fav-card-item').fadeOut(220, function() {
                    $(this).remove();
                    $('#favCountBadge').text(res.total_fav);
                    if ($('.fav-card-item').length === 0) {
                        $('#emptyFavAlert').removeClass('d-none');
                    }
                });
            } else {
                // 在商品/課程卡片頁即時切換實心/空心愛心圖示
                const $icon = $btn.find('i');
                if (res.is_favorite === 1) {
                    $icon.removeClass('bi-heart').addClass('bi-heart-fill text-danger');
                } else {
                    $icon.removeClass('bi-heart-fill text-danger').addClass('bi-heart');
                }
            }
        } else if (res.need_login === 1) {
            alert('請先登入會員帳號，即可將喜愛的課程與商品加入收藏！');
            window.location.href = '5sl_login.php';
        } else {
            alert(res.message || '操作失敗，請稍後再試。');
        }
    }, 'json');
});
  /* ==========================================================================
   全站通用：負責「加入／取消愛心收藏」（只會影響愛心圖示與 fsl_wishlists 資料表）。
    ========================================================================== */
$(document).on('click', '.btn-fsl-wishlist', function(e) {
    e.preventDefault();
    const $btn       = $(this);
    const targetType = $btn.attr('data-type') || '1';
    const targetId   = $btn.attr('data-id') || '0';

    $.post('./funcFiles/5sl_act_member.php', {
        action: 'act_toggle_wishlist',
        target_type: targetType,
        target_id: targetId,
        op: 'toggle'
    }, function(res) {
        if (res.success === 1) {
            const $icon = $btn.find('i');
            if (res.is_wishlist === 1) {
                $icon.removeClass('bi-heart').addClass('bi-heart-fill text-danger');
            } else {
                $icon.removeClass('bi-heart-fill text-danger').addClass('bi-heart');
            }
        } else if (res.need_login === 1) {
            alert('請先登入會員帳號即可加入收藏！');
            window.location.href = '5sl_login.php';
        }
    }, 'json');
});










});
