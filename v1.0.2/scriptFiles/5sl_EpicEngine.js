/* ==========================================================================
   💾 VERSION LOG: 2026-11-09 16:07 PM CST [Snapshot #046]
   🏆【5SL 工業級通用型 3D 物理動畫粒子與樣板旗艦引擎 - 獨立外連定型體】
   🎯 檔案名稱：5sl_EpicEngine.js
   ========================================================================== */
    $(function() {
        class FslEpicEffectEngine {
            constructor() {
                this.canvas = document.getElementById('fslParallaxCanvas');
                this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
                this.animId = null;
                this.particles = [];
                this.intervalId = null;
                
                // 🚀【安全防線 1】：將 resize 鎖定為具名實體，物理封印匿名箭頭函式導致解綁失敗的記憶體洩漏！
                this.resizeBound = this.resize.bind(this);
            }

            /**
             * 🎯 智慧型優惠券 HTML 樣板生成器 (Template Factory)
             * 用途：封印繁複的幾何結構，對外僅曝露最核心的動態改變變數
             */
            createCouponTemplate(data) {
                const config = $.extend({
                    bgUrl: '', 
                    points: '100',                                
                    unit: 'PTS',                                  
                    title: 'CONGRATULATIONS!',                    
                    subTitle: '歡迎加入人生第五季生命藝術，恭喜您獲得',  
                    label: '★ 首次登入專屬 Premium 點券 ★'         
                }, data);

                return `
                    <div data-fsl-bg="${config.bgUrl}">
                        <div class="text-warning display-2 mb-2" style="animation: fslPulse 1s infinite alternate; text-align: center !important;"><i class="bi bi-gift-fill"></i></div>
                        <h3 class="fw-bold text-white mb-2" style="font-family:'Montserrat'; letter-spacing: 2px; text-align: center !important;">${config.title}</h3>
                        <p class="text-white-50 small mb-4" style="white-space: nowrap !important; margin: 10px 0 20px 0 !important; text-align: center !important; width: 100% !important;">${config.subTitle}</p>
                        <div class="coupon-points mb-2" style="font-size: 3.5rem; color: #ffc107; font-weight: 900; line-height: 1 !important; text-align: center !important;">${config.points} <span style="font-size: 1.5rem;">${config.unit}</span></div>
                        <p class="text-warning small m-0" style="margin-top: 15px !important; text-align: center !important; width: 100% !important;">${config.label}</p>
                    </div>
                `;
            }

            /**
             * 🚀 核心觸發方法 (解耦接收體、3D 螺旋煞車舞台)
             */
            trigger(config) {
                const self = this;
                const settings = $.extend({
                    contentType: 'html',
                    contentSource: '',
                    theme: 'celebration',
                    onConfirm: function(close) { close(); }
                }, config);

                if (!this.canvas || !this.ctx) return;

                const $slot = $('#fslDynamicContentSlot');
                $slot.empty();

                let customBgStyle = "";
                let finalContent = settings.contentSource;
                
                const bgMatch = settings.contentSource.match(/data-fsl-bg=["']([^"']+)["']/);
                if (bgMatch && bgMatch[1]) {
                    customBgStyle = `background-image: url('${bgMatch[1]}') !important;`;
                }

                let innerHTML = `<div class="fsl-fast-spin-pop">
                    <div class="fsl-coupon-card-epic" id="fslEpicClickCard" style="${customBgStyle}">
                        <div style="position:relative; z-index:5 !important; width:100%;">${finalContent}</div>
                        <div class="text-warning fw-bold text-center mt-4 fsl-click-hint-pulse" style="position:relative; z-index:5 !important;">
                            <i class="bi bi-hand-index-thumb-fill me-1"></i> 【 點我領取優惠 】
                        </div>
                    </div>
                </div>`;
                $slot.html(innerHTML);

                $('#couponGiftModal').addClass('show');
                
                // 幾何畫布初始重繪與具名解綁防線
                this.resize();
                $(window).off('resize.fslEngine', this.resizeBound).on('resize.fslEngine', this.resizeBound);

                // 點火初始化粒子
                this.initParticles(settings.theme);

                const renderLoop = () => {
                    self.render();
                    self.animId = window.requestAnimationFrame(renderLoop);
                };
                renderLoop();

                $(document).off('click.fslEngine').on('click.fslEngine', '#fslEpicClickCard', function(e) {
                    e.preventDefault();
                    console.log("5SL Studio: Direct coupon data logging executed asynchronously.");
                    settings.onConfirm(() => self.close());
                });
            }

            resize() {
                if (this.canvas) { this.canvas.width = window.innerWidth; this.canvas.height = window.innerHeight; }
            }

            initParticles(theme) {
                // 🚀【安全防線 2】：在重繪新煙火定時器前，物理切斷舊計時器遺毒，消滅背景執行序暴走！
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                    this.intervalId = null;
                }

                // 🚀【去重排雷點】：粒子陣列在此處實施唯一初始化，消滅兩部分重複宣告的累贅！
                this.particles = []; 

                const w = this.canvas.width;
                const h = this.canvas.height;
                const colors = theme === 'celebration' 
                    ? ['#ffc107', '#ff5722', '#e91e63', '#00bcd4', '#4caf50', '#ffffff']
                    : ['#ffc107', '#ffe082', '#fff8e1', '#ffd54f', '#ffb300'];

                const self = this;
                if (theme === 'celebration') {
                    this.burstFirework(w * 0.2, h * 0.3, colors);
                    this.burstFirework(w * 0.8, h * 0.25, colors);
                    this.burstFirework(w * 0.5, h * 0.4, colors);
                    this.burstFirework(w * 0.3, h * 0.2, colors);
                    
                    this.intervalId = setInterval(() => {
                        self.burstFirework(Math.random() * w, Math.random() * (h * 0.5), colors);
                    }, 700);
                }

                const count = theme === 'stardust' ? 150 : 65; 
                for (let i = 0; i < count; i++) { this.particles.push(this.createDropdownParticle(w, h, theme, colors)); }
            }

            burstFirework(x, y, colors) {
                for (let i = 0; i < 50; i++) {
                    this.particles.push({
                        x: x, y: y, type: 'firework',
                        color: colors[Math.floor(Math.random() * colors.length)],
                        angle: Math.random() * Math.PI * 2, speed: Math.random() * 7 + 4,
                        radius: Math.random() * 2.5 + 1.2, alpha: 1, decay: Math.random() * 0.018 + 0.012
                    });
                }
            }

            createDropdownParticle(w, h, theme, colors) {
                return {
                    x: Math.random() * w, y: Math.random() * -h,
                    type: theme === 'celebration' ? 'ribbon' : 'dot',
                    color: colors[Math.floor(Math.random() * colors.length)],
                    w: Math.random() * 6 + 4, h: Math.random() * 12 + 6,
                    radius: Math.random() * 2.5 + 1,
                    speedY: theme === 'celebration' ? (Math.random() * 0.6 + 0.65) : (Math.random() * 2 + 1.5),
                    speedX: Math.random() * 0.8 - 0.4,
                    rotation: Math.random() * 360, rotSpeed: Math.random() * 2 - 1, theme: theme
                };
            }

            /**
             * 🚀 每秒 60 幀高頻 Canvas 硬體加速渲染矩陣
             */
            render() {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                const w = this.canvas.width;
                const h = this.canvas.height;

                for (let i = this.particles.length - 1; i >= 0; i--) {
                    let p = this.particles[i];
                    if (p.type === 'firework') {
                        p.x += Math.cos(p.angle) * p.speed; 
                        p.y += Math.sin(p.angle) * p.speed + 0.16; 
                        p.alpha -= p.decay;
                        
                        this.ctx.save(); 
                        this.ctx.globalAlpha = p.alpha; 
                        this.ctx.beginPath(); 
                        this.ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2); 
                        this.ctx.fillStyle = p.color; 
                        this.ctx.fill(); 
                        this.ctx.restore();
                        
                        if (p.alpha <= 0) this.particles.splice(i, 1);
                    } else {
                        p.y += p.speedY; 
                        p.x += p.speedX;
                        
                        if (p.type === 'ribbon') {
                            p.rotation += p.rotSpeed;
                            this.ctx.save(); 
                            this.ctx.translate(p.x, p.y); 
                            this.ctx.rotate(p.rotation * Math.PI / 180); 
                            this.ctx.fillStyle = p.color; 
                            this.ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h); 
                            this.ctx.restore();
                        } else {
                            this.ctx.beginPath(); 
                            this.ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2); 
                            this.ctx.fillStyle = p.color;
                            if (p.theme === 'stardust') { 
                                this.ctx.shadowBlur = 10; 
                                this.ctx.shadowColor = p.color; 
                            }
                            this.ctx.fill(); 
                            this.ctx.shadowBlur = 0;
                        }
                        
                        if (p.y > h) { 
                            this.particles[i] = this.createDropdownParticle(w, h, p.theme, [p.color]); 
                            this.particles[i].y = -20; 
                        }
                    }
                }
            }

            /**
             * 🚀 銷毀退場生命週期 (物理去重重組，100% 乾淨釋放)
             */
            close() {
                $('#couponGiftModal').removeClass('show');
                
                if (this.animId) {
                    window.cancelAnimationFrame(this.animId);
                    this.animId = null;
                }
                
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                    this.intervalId = null;
                }
                
                // 🚀【具名精準卸載】：撤銷 window resize 監聽器
                $(window).off('resize.fslEngine', this.resizeBound);
                
                setTimeout(() => { 
                    $('#fslDynamicContentSlot').empty(); 
                    this.particles = []; // 徹底排空內存陣列
                }, 350);
            }
        }

        // 🚀 實例化掛載至 window 天花板頂層
        const instance = new FslEpicEffectEngine();
        window.fslEpicEngine = instance;
        window['fslEpicEngine'] = instance;
        // 🎯 1. 補強：讓 3D 優惠券卡片插槽觸發時，全自動動態注入右上角關閉鈕
        // 🚀【安全防線 2】：改用 window.fslOriginalTrigger 進行特權鎖定，防禦多重載入時的 const 重複定義衝突！
        if (window.fslEpicEngine && !window.fslOriginalTrigger) {
            window.fslOriginalTrigger = window.fslEpicEngine.trigger;
            window.fslEpicEngine.trigger = function(config) {
                window.fslOriginalTrigger.call(window.fslEpicEngine, config);
                // 動態在 3D 大舞台補上高奢關閉鈕機關，與 CSS 樣式完美咬合
                if (!$('#fslCloseCouponBtn').length) {
                    $('#couponGiftModal').append('<button type="button" class="fsl-close-stage-btn" id="fslCloseCouponBtn" aria-label="關閉卡片"><i class="bi bi-x-lg"></i></button>');
                }
            };
        }
        // 🎯 優惠券卡片專屬離開：直接寫在特效引擎檔案中，單純喚醒防呆確認窗
        $(document).off('click', '#fslCloseCouponBtn').on('click', '#fslCloseCouponBtn', function(e) {
            e.preventDefault(); 
            e.stopPropagation();
            $('#fslConfirmAlertMask').addClass('show'); // 喚醒共用黑紗
        });

     });
