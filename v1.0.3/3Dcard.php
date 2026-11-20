<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>5SL STUDIO - FslPremiumLuxuryCard</title>
  <style>
    /* ==========================================
       1. 基礎黑金環境重設
       ========================================== */
    body {
      background-color: #0A0A0A;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* ==========================================
       2. 3D 尊爵金卡核心樣式
       ========================================== */
    .fsl-card-wrapper {
      perspective: 1200px; /* 3D 空間深度空間鎖死 */
    }

    .fsl-premium-card {
      position: relative;
      width: 380px;
      height: 240px;
      border-radius: 20px;
      /* 奢華極致深黑底層 + 隱約金屬拉絲底紋 */
      background: linear-gradient(135deg, #141414 0%, #070707 100%);
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8),
                  inset 0 1px 0 rgba(255, 215, 0, 0.15);
      border: 1px solid rgba(212, 175, 55, 0.15); /* 1px 尊爵金微光邊框 */
      padding: 28px;
      box-sizing: border-box;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      
      /* 3D 變數注入與平滑回彈 */
      transform: rotateX(var(--rotate-x, 0deg)) rotateY(var(--rotate-y, 0deg)) scale(var(--card-scale, 1));
      transition: transform 0.15s cubic-bezier(0.25, 1, 0.5, 1);
      transform-style: preserve-3d;
      cursor: pointer;
    }

    /* 金屬絲綢物理拉絲紋理層 */
    .fsl-premium-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, rgba(255,255,255,0.01) 0%, rgba(0,0,0,0) 50%, rgba(255,255,255,0.01) 100%),
                  repeating-linear-gradient(0deg, transparent, transparent 1px, rgba(212, 175, 55, 0.03) 1px, rgba(212, 175, 55, 0.03) 2px);
      opacity: 0.6;
      pointer-events: none;
      z-index: 1;
    }

    /* 反向折射率流光層 (Shimmer Layer) */
    .fsl-premium-card-shimmer {
      position: absolute;
      inset: 0;
      background: radial-gradient(
        circle at var(--shimmer-x, 50%) var(--shimmer-y, 50%),
        rgba(212, 175, 55, 0.18) 0%,
        rgba(212, 175, 55, 0.03) 45%,
        transparent 70%
      );
      mix-blend-mode: overlay;
      pointer-events: none;
      z-index: 2;
    }

    /* ==========================================
       3. 卡片內部元件 (全面死鎖視差深度)
       ========================================== */
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      transform: translateZ(40px); /* 向上懸浮立體感 */
      z-index: 3;
    }

    .brand-logo {
      font-weight: 800;
      font-size: 20px;
      letter-spacing: 2px;
      background: linear-gradient(135deg, #FFF 30%, #D4AF37 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .card-type {
      font-size: 11px;
      color: #D4AF37;
      text-transform: uppercase;
      letter-spacing: 3px;
      border: 1px solid rgba(212, 175, 55, 0.4);
      padding: 4px 10px;
      border-radius: 20px;
      background: rgba(212, 175, 55, 0.05);
    }

    .card-body {
      transform: translateZ(50px);
      z-index: 3;
    }

    .chip-icon {
      width: 42px;
      height: 32px;
      background: linear-gradient(135deg, #A48222 0%, #D4AF37 50%, #F3E5AB 100%);
      border-radius: 6px;
      margin-bottom: 16px;
      position: relative;
      box-shadow: inset 0 1px 2px rgba(255,255,255,0.3);
    }

    .card-number {
      font-family: 'Courier New', Courier, monospace;
      font-size: 22px;
      color: #F5F5F5;
      letter-spacing: 3px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      transform: translateZ(30px);
      z-index: 3;
    }

    .member-info .label {
      font-size: 9px;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 4px;
    }

    .member-info .value {
      font-size: 14px;
      color: #E0E0E0;
      letter-spacing: 1px;
      font-weight: 500;
    }

    /* ==========================================
       4. 手機端響應式與優雅降級
       ========================================== */
    @media (max-width: 480px) {
      .fsl-premium-card {
        width: 86vw;
        height: calc(86vw * 0.6315); /* 鎖死標準信用卡黃金比例 */
        padding: 20px;
      }
      .card-number {
        font-size: 18px;
      }
      /* 手機端自動啟動純 CSS 自動流光循環（防止 3D 暈眩） */
      .fsl-premium-card-shimmer {
        animation: mobileShimmer 6s infinite linear;
      }
    }

    @keyframes mobileShimmer {
      0% { --shimmer-x: 10%; --shimmer-y: 10%; }
      50% { --shimmer-x: 90%; --shimmer-y: 90%; }
      100% { --shimmer-x: 10%; --shimmer-y: 10%; }
    }
  </style>
</head>
<body>

  <div class="fsl-card-wrapper">
    <div class="fsl-premium-card">
      <!-- 獨立流光反射層 -->
      <div class="fsl-premium-card-shimmer"></div>
      
      <!-- 頂部商標區 -->
      <div class="card-header">
        <div class="brand-logo">5SL STUDIO</div>
        <div class="card-type">Black Gold</div>
      </div>
      
      <!-- 中部晶片與卡號區 -->
      <div class="card-body">
        <div class="chip-icon"></div>
        <div class="card-number">5566 8899 0011 2026</div>
      </div>
      
      <!-- 底部持卡人與效期區 -->
      <div class="card-footer">
        <div class="member-info">
          <div class="label">Cardholder</div>
          <div class="value">PREMIUM MEMBER</div>
        </div>
        <div class="member-info" style="text-align: right;">
          <div class="label">Expires End</div>
          <div class="value">11/29</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================
       5. 智慧物理動效核心 JavaScript 引擎
       ========================================== */ -->
  <script>
    const card = document.querySelector('.fsl-premium-card');

    if (card) {
      // 僅在非觸控裝置（電腦版）啟動滑鼠 3D 視差與折射監聽
      if (window.matchMedia("(pointer: fine)").matches) {
        
        card.addEventListener('pointermove', (e) => {
          const rect = card.getBoundingClientRect();
          
          // 計算相對卡片中心的座標值 (-1 到 1 之間)
          const x = e.clientX - rect.left - rect.width / 2;
          const y = e.clientY - rect.top - rect.height / 2;
          
          // 限制最大翻轉角度在內斂尊貴的 12 度之內
          const rotateX = -(y / (rect.height / 2)) * 12;
          const rotateY = (x / (rect.width / 2)) * 12;
          
          // 注入 3D 旋轉變數
          card.style.setProperty('--rotate-x', `${rotateX}deg`);
          card.style.setProperty('--rotate-y', `${rotateY}deg`);
          card.style.setProperty('--card-scale', '1.03'); // 微幅放大

          // 核心光學：反向折射率位移（光隨游標流動）
          const shimmerX = (e.clientX - rect.left) / rect.width * 100;
          const shimmerY = (e.clientY - rect.top) / rect.height * 100;
          card.style.setProperty('--shimmer-x', `${shimmerX}%`);
          card.style.setProperty('--shimmer-y', `${shimmerY}%`);
        });

        // 滑鼠離開：回彈平穩歸零
        card.addEventListener('pointerleave', () => {
          card.style.setProperty('--rotate-x', '0deg');
          card.style.setProperty('--rotate-y', '0deg');
          card.style.setProperty('--card-scale', '1');
          card.style.setProperty('--shimmer-x', '50%');
          card.style.setProperty('--shimmer-y', '50%');
        });
      }
    }
  </script>
</body>
</html>
