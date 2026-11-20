        // ==========================================================================
        // 💾 VERSION LOG: 2026-11-17 09:30 CST 
        // 🚀 工業級幸運大轉盤核心類別引擎 (FslPremiumLotteryWheel - 資料庫動態串接版)
        // $('#fslGlobalWheelPanel').fadeIn(300);
        // ==========================================================================
    $(function() {
        class FslPremiumLotteryWheel {
            constructor() {
                this.canvas = document.getElementById('fslLotteryCanvas');
                this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
                this.isSpinning = false;
                
                const cfg = window.fslWheelConfig || {};
                this.keycode = cfg.keycode || 'LUCKY_WHEEL_2026';
                this.isLoggedIn = !!cfg.isLoggedIn;

                // 優先讀取資料庫 fsl_lottery_items 動態格位，若無則使用預設備援陣列
                this.awards = (Array.isArray(cfg.awards) && cfg.awards.length > 0) ? cfg.awards : [
                    { slot: 1, name: '100 PTS 點券', color: '#1e2125', text: '#ffc107' },
                    { slot: 2, name: '免費專屬域名', color: '#2b3035', text: '#ffffff' },
                    { slot: 3, name: '50 PTS 點券', color: '#1e2125', text: '#ffc107' },
                    { slot: 4, name: 'LOGO 商標設計', color: '#2b3035', text: '#ffffff' },
                    { slot: 5, name: '500 元折價券', color: '#1e2125', text: '#ffc107' },
                    { slot: 6, name: 'VIP 體驗資格', color: '#2b3035', text: '#ffffff' }
                ];
                
                if (this.canvas) this.initWheel();
            }

            /**
             * 2D 高密度黃金扇區自動化幾何繪製
             */
            initWheel() {
                this.canvas.width = 400;
                this.canvas.height = 400;
                const numSectors = this.awards.length;
                const arcAngle = (Math.PI * 2) / numSectors;
                const radius = 200;

                this.ctx.clearRect(0, 0, 400, 400);

                for (let i = 0; i < numSectors; i++) {
                    const angle = i * arcAngle;
                    
                    // 1. 繪製精緻的暗黑黑金分割扇形
                    this.ctx.beginPath();
                    this.ctx.arc(200, 200, radius, angle, angle + arcAngle);
                    this.ctx.lineTo(200, 200);
                    this.ctx.fillStyle = this.awards[i].color;
                    this.ctx.fill();

                    // 2. 注入精緻的黃金分割防線邊框
                    this.ctx.lineWidth = 2;
                    this.ctx.strokeStyle = 'rgba(255, 193, 7, 0.25)';
                    this.ctx.stroke();

                    // 3. 🚀 弧形多語字報幾何高階運算：將文字旋轉打入各個扇區中央
                    this.ctx.save();
                    this.ctx.translate(200, 200);
                    this.ctx.rotate(angle + arcAngle / 2);
                    this.ctx.textAlign = 'right';
                    this.ctx.textBaseline = 'middle';
                    this.ctx.fillStyle = this.awards[i].text;
                    this.ctx.font = "bold 14px 'Montserrat', 'Noto Sans TC', sans-serif";
                    
                    // 文字向外推至距離邊緣 35 像素處，視覺比例最完美
                    this.ctx.fillText(this.awards[i].name, radius - 35, 0);
                    this.ctx.restore();
                }
            }

            /**
             * 🚀 前後端防作弊安全觸發核心 (串接 5sl_act_member.php -> act_draw_lottery)
             */
            spin() {
                if (this.isSpinning) return;

                // 檢查登入狀態
                if (!this.isLoggedIn) {
                    alert('請先登入 Fifth Season Life 會員帳號，即可轉動幸運大轉盤！');
                    window.location.href = '5sl_login.php';
                    return;
                }

                this.isSpinning = true;
                const self = this;
                const $canvas = $('#fslLotteryCanvas');

                // 🛡️ 防作弊防線：呼叫安全等待遮罩，發送 AJAX 由後端 MySQL 權重決定命運
                if (window.fslLoader) fslLoader.show();

                $.post('./funcFiles/5sl_act_member.php', {
                    action: 'act_draw_lottery',
                    lot_keycode: self.keycode,
                    auto_claim: 0 // 先寫入待領取，讓會員在 3D 慶祝彈窗點擊「確認收下」時正式領收入帳
                }, function(res) {
                    if (window.fslLoader) fslLoader.hide();

                    if (res.success !== 1) {
                        self.isSpinning = false;
                        alert(res.message || '目前無法參加抽獎，請稍後再試。');
                        return;
                    }

                    // 比對後端算出的中獎格位 (slot_order)
                    const wonSlot = parseInt(res.slot_order, 10);
                    let awardIndex = self.awards.findIndex(a => a.slot === wonSlot);
                    if (awardIndex < 0) awardIndex = 0;

                    const numSectors = self.awards.length;
                    const sectorDegrees = 360 / numSectors;

                    /* 🚀【電影級阻尼煞車與 Q 彈倒鉤幾何運算式】：
                       1. 讓轉盤基礎旋轉整整 8 圈（8 * 360 = 2880 度）
                       2. 逆向扣除中獎扇區的角度中心點
                       3. 指針頂部指向正上方（270度），進行物理角度偏移修正 */
                    const targetDegrees = 2880 + (270 - (awardIndex * sectorDegrees + sectorDegrees / 2));

                    // 4. 啟動 5.2 秒高級阻尼減速與指針倒鉤重繪曲線
                    $canvas.css({
                        'transition': 'transform 5.2s cubic-bezier(0.25, 1, 0.3, 1.1)',
                        'transform': `rotate(${targetDegrees}deg)`
                    });

                    // 5. 5.2 秒後轉盤停穩，引爆 fslEpicEngine 慢速彩帶煙火秀
                    setTimeout(() => {
                        $canvas.css({ 'transition': 'transform 0s', 'transform': `rotate(${targetDegrees % 360}deg)` });
                        self.isSpinning = false;
                        $('#fslGlobalWheelPanel').fadeOut(300);

                        fslEpicEngine.trigger({
                            contentType: 'html',
                            contentSource: fslEpicEngine.createCouponTemplate({
                                bgUrl: '',
                                points: res.loti_label,
                                unit: 'LUCKY PRIZE',
                                title: 'LUCKY DRAW WINNER!',
                                subTitle: `恭喜抽中「${res.gift_title}」`,
                                label: '★ 點擊下方按鈕立即領收並存入您的帳戶 ★'
                            }),
                            theme: 'celebration',
                            onConfirm: function(close) {
                                // 點擊收下時，立即呼叫 act_claim_gift 完成點數入帳與 fsl_points 流水帳寫入
                                if (res.grec_id && res.grec_id > 0) {
                                    $.post('./funcFiles/5sl_act_member.php', {
                                        action: 'act_claim_gift',
                                        grec_id: res.grec_id
                                    }, function(claimRes) {
                                        close();
                                        if (claimRes.success === 1) {
                                            alert(claimRes.message);
                                            // 若目前正好在禮物與點券頁面，自動重新整理同步餘額與禮品櫃
                                            if (window.location.pathname.indexOf('5sl_myGifts.php') !== -1) {
                                                window.location.reload();
                                            }
                                        }
                                    }, 'json');
                                } else {
                                    close();
                                }
                            }
                        });

                    }, 5250);

                }, 'json').fail(function() {
                    if (window.fslLoader) fslLoader.hide();
                    self.isSpinning = false;
                    alert('網路連線異常，請稍後再試。');
                });
            }
/*
        }, 'json').fail(function(xhr) {
            if (window.fslLoader) window.fslLoader.hide();
            console.error('Server Response:', xhr.status, xhr.responseText);
            showGiftsAlert('danger', '錯誤狀態：' + xhr.status + '｜詳情：' + xhr.responseText.substring(0, 150));
        });
                }, 'json').fail(function() {
                    if (window.fslLoader) fslLoader.hide();
                    self.isSpinning = false;
                    alert('網路連線異常，請稍後再試。');
                });

*/

        }

        const lotteryWheelInstance = new FslPremiumLotteryWheel();
        window.fslLotteryWheel = lotteryWheelInstance;
        window['fslLotteryWheel'] = lotteryWheelInstance;

        if (window.fslEpicEngine && !window.fslOriginalTrigger) {
            window.fslOriginalTrigger = window.fslEpicEngine.trigger;
            window.fslEpicEngine.trigger = function(config) {
                window.fslOriginalTrigger.call(window.fslEpicEngine, config);
                if (!$('#fslCloseCouponBtn').length) {
                    $('#couponGiftModal').append('<button type="button" class="fsl-close-stage-btn" id="fslCloseCouponBtn" aria-label="關閉卡片"><i class="bi bi-x-lg"></i></button>');
                }
            };
        }

        // 🎯 穩固綁定中央黃金指針的點擊抽獎事件
        $(document).off('click.fslWheel').on('click.fslWheel', '#fslWheelStartBtn', function(e) {
            e.preventDefault();
            if (window.fslLotteryWheel) {
                window.fslLotteryWheel.spin();
            }
        });

        // 🎯 大轉盤專屬離開：呼叫共用確認窗
        $(document).off('click', '#fslCloseWheelBtn').on('click', '#fslCloseWheelBtn', function(e) {
            e.preventDefault(); 
            e.stopPropagation();
            $('#fslConfirmAlertMask').addClass('show');
        });

    });
