/**
 * ==========================================================================
 * 5SL 專案全站共用核心控制邏輯 (scriptFiles/5seasonlife.js) - 瘦身精簡版
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

  if (bgImages.length > 0) {
    $.each(bgImages, function (index, url) {
      const img = new Image();
      img.src = url;
    });
  }

  let currentBgIndex = 0;

  function changeSliderBackground() {
    if (bgImages.length === 0) return;

    const nextImageUrl = bgImages[currentBgIndex];
    const nextStyle =
      "linear-gradient(rgba(0,0,0," + opacityValue + "), rgba(0,0,0," + opacityValue + ")), url('" + nextImageUrl + "')";

    if ($layerA.hasClass("active")) {
      $layerB.css("background-image", nextStyle).addClass("active");
      $layerA.removeClass("active");
    } else {
      $layerA.css("background-image", nextStyle).addClass("active");
      $layerB.removeClass("active");
    }

    currentBgIndex = (currentBgIndex + 1) % bgImages.length;
  }

  if (bgImages.length > 0) {
    $layerA.css(
      "background-image",
      "linear-gradient(rgba(0,0,0," + opacityValue + "), rgba(0,0,0," + opacityValue + ")), url('" + bgImages[0] + "')"
    );
    currentBgIndex = 1 % bgImages.length;
    setInterval(changeSliderBackground, 6000);
  }

  /* ==========================================================================
     2. 導航列通用操作優化 (手機版點擊連結自動收合)
     ========================================================================== */
  $(".navbar-nav .nav-link:not(.dropdown-toggle), .dropdown-item").on("click", function () {
    if ($(window).width() < 992) {
      const $navCollapse = $("#mainNavbar");
      const $navToggler = $('[data-bs-target="#mainNavbar"]');
      if ($navCollapse.hasClass("show") && $navToggler.length > 0) {
        $navToggler.trigger("click");
      }
    }
  });

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

    const minSwipeDistanceX = 45;
    const maxSwipeDistanceY = 50;

    window.addEventListener(
      "touchstart",
      function (e) {
        if (e.target && e.target.closest(".fsl-highlights-viewport, .fsl-pricing-viewport")) {
          ignoreNavSwipe = true;
          return;
        }
        ignoreNavSwipe = false;
        const touch = (e.touches && e.touches.item(0)) || (e.changedTouches && e.changedTouches.item(0)) || e;
        touchStartX = touch.clientX || touch.pageX || 0;
        touchStartY = touch.clientY || touch.pageY || 0;
      },
      { passive: true }
    );

    window.addEventListener(
      "touchend",
      function (e) {
        if (ignoreNavSwipe) {
          ignoreNavSwipe = false;
          return;
        }
        const touch = (e.changedTouches && e.changedTouches.item(0)) || (e.touches && e.touches.item(0)) || e;
        touchEndX = touch.clientX || touch.pageX || 0;
        touchEndY = touch.clientY || touch.pageY || 0;

        const distanceX = touchEndX - touchStartX;
        const distanceY = Math.abs(touchEndY - touchStartY);
        if (distanceY > maxSwipeDistanceY) return;

        const isOpen = $sidebar.hasClass("show");
        if (distanceX > minSwipeDistanceX && !isOpen && touchStartX <= 35) {
          $toggleBtn.click();
        } else if (distanceX < -minSwipeDistanceX && isOpen) {
          $toggleBtn.click();
        }
      },
      { passive: true }
    );
  }

  /* ==========================================================================
     4. 圖庫特效：作品集滑鼠方位追蹤 3D 空間傾斜引擎 (首頁與花絮頁共用)
     ========================================================================== */
  if (window.innerWidth > 992) {
    $(document)
      .on("mousemove", ".portfolio-card", function (e) {
        const $card = $(this);
        const w = $card.width();
        const h = $card.height();
        const offsets = $card.offset();
        const x = e.pageX - offsets.left - w / 2;
        const y = e.pageY - offsets.top - h / 2;
        const rotateX = (-y / h) * 24;
        const rotateY = (x / w) * 24;
        $card.css("transform", "rotateX(" + rotateX + "deg) rotateY(" + rotateY + "deg)");
      })
      .on("mouseleave", ".portfolio-card", function () {
        $(this).css("transform", "rotateX(0deg) rotateY(0deg)");
      });
  }

  /* ==========================================================================
     5. 全站通用：一鍵加入購物車 / 立即預約觸發器 (.btn-fsl-add-cart)
     ========================================================================== */
  $(document).on("click", ".btn-fsl-add-cart", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const itemType = $btn.attr("data-type") || "1";
    const courseId = $btn.attr("data-course-id") || 0;
    const sessId = $btn.attr("data-sess-id") || 0;
    const merchId = $btn.attr("data-merch-id") || 0;
    const qty = $btn.attr("data-qty") || 1;
    const redirect = $btn.attr("data-redirect") === "1";

    if (window.fslLoader) window.fslLoader.show();

    $.post(
      "./funcFiles/5sl_act_member.php",
      {
        action: "act_cart_add",
        item_type: itemType,
        course_id: courseId,
        sess_id: sessId,
        merch_id: merchId,
        qty: qty,
      },
      function (res) {
        if (window.fslLoader) window.fslLoader.hide();

        if (res.success === 1) {
          if (res.cart_count > 0) {
            $(".nav-cart-badge, #navCartBadge, #subnaviCartBadge").text(res.cart_count).removeClass("d-none");
          } else {
            $(".nav-cart-badge, #navCartBadge, #subnaviCartBadge").text("0").addClass("d-none");
          }

          if (redirect) {
            window.location.href = "5sl_myCart.php";
          } else {
            if (confirm(`「${res.item_title}」已加入購物車（目前共 ${res.cart_count} 項）！\n\n是否立即前往結帳？`)) {
              window.location.href = "5sl_myCart.php";
            }
          }
        } else if (res.need_login === 1) {
          alert("請先登入會員帳號，即可立即預約與結帳！");
          window.location.href = "5sl_login.php";
        } else {
          alert(res.message || "加入購物車失敗，請稍後再試。");
        }
      },
      "json"
    ).fail(function () {
      if (window.fslLoader) window.fslLoader.hide();
      alert("網路連線異常，請稍後再試。");
    });
  });

  /* ==========================================================================
     6. 全站通用：一鍵加入 / 取消愛心收藏 (.btn-fsl-wishlist)
     ========================================================================== */
  $(document).on("click", ".btn-fsl-wishlist", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const targetType = $btn.attr("data-type") || "1";
    const targetId = $btn.attr("data-id") || "0";

    $.post(
      "./funcFiles/5sl_act_member.php",
      {
        action: "act_toggle_wishlist",
        target_type: targetType,
        target_id: targetId,
        op: "toggle",
      },
      function (res) {
        if (res.success === 1) {
          const $icon = $btn.find("i");
          if (res.is_wishlist === 1) {
            $icon.removeClass("bi-heart").addClass("bi-heart-fill text-danger");
          } else {
            $icon.removeClass("bi-heart-fill text-danger").addClass("bi-heart");
          }
        } else if (res.need_login === 1) {
          alert("請先登入會員帳號即可加入收藏！");
          window.location.href = "5sl_login.php";
        }
      },
      "json"
    );
  });
});
