<?php

/**
 * Class ArticleRankingSystem
 * 
 * 負責文章平台的前台星級平滑換算，以及後台推廣熱度排序計算。
 * 
 * 系統運行邏輯：
 * 1. 前台顯示：採用 LSR (Laplace Smoothed Rating) 平滑星級，預設新文為健康的 4.2 星。
 * 2. 後台推廣：採用 TDS (Time-Decayed Score) 結合 Hacker News 重力公式進行排程計算。
 */
class ArticleRankingSystem {
    
    /** @var float 後台演算法參數：重力加速度（值越大，舊文章退燒越快） */
    private static float $GRAVITY = 1.5; 
    
    /** @var float 後台演算法參數：倒讚的懲罰權重（1 個倒讚需要 1.5 個讚才能抵消） */
    private static float $DISLIKE_WEIGHT = 1.5; 

    /**
     * 【前台】計算平滑星級評分 (1.0 - 5.0 星)
     * 
     * 採用拉普拉斯平滑，解決新文章冷啟動與極端值問題。
     *
     * 💡 程式碼調用簡單範例：
     * <code>
     * $stars = ArticleRankingSystem::calculateFrontendStars($likes = 10, $dislikes = 1);
     * // 回傳結果：(float) 4.3
     * </code>
     * 
     * @param int $likes 文章累積的按讚總數
     * @param int $dislikes 文章累積的倒讚總數
     * @param int $precision 小數點四捨五入位數（預設為 1 位）
     * @return float 經拉普拉斯平滑後的真實星級分數 (1.0 到 5.0)
     */
    public static function calculateFrontendStars(int $likes, int $dislikes, int $precision = 1): float {
        $likes = max(0, $likes);
        $dislikes = max(0, $dislikes);

        // 拉普拉斯平滑（基礎值 1.6，確保無數據新文預設為健康的 4.2 星）
        $smoothedScore = ($likes + 1.6) / ($likes + $dislikes + 2.0);
        $stars = 1.0 + 4.0 * $smoothedScore;

        return round(min(5.0, max(1.0, $stars)), $precision);
    }

    /**
     * 【前台】將分數轉換為星級圖樣（支援 0.5 星精細度）
     * 
     * 依據傳入的浮點數分數，四捨五入到最近的 0.5 星並輸出對應符號。
     *
     * 💡 程式碼調用簡單範例：
     * <code>
     * // 範例 1：輸出純文字/Emoji 符號（適合簡訊、通知或手機窄版）
     * $starText = ArticleRankingSystem::getFrontendStarHTML(4.3); 
     * // 輸出結果："★★★★🌓"
     * 
     * // 範例 2：輸出 HTML 標籤（適合網頁端搭配 CSS 給予金色與灰色上色）
     * $starHtml = ArticleRankingSystem::getFrontendStarHTML(4.3, true); 
     * // 輸出結果："<span class="star star-full">★</span>...<span class="star star-half">★</span>"
     * </code>
     * 
     * @param float $stars 經由 calculateFrontendStars 計算出的分數
     * @param bool $asHtml 是否輸出為帶有 CSS Class 的 HTML 標籤（預設 false 輸出純文字）
     * @return string 星星圖樣字串
     */
        /**
     * 【前台星星】將浮點數分數轉換為 0.5 星精細度的星星圖樣
     * （已修正：當傳入得分為 0 時，完美輸出五顆全空心星）
     * 
     * @param float $stars 經由公式計算出來的 0.0 ~ 5.0 浮點數得分
     * @param bool $asHtml 是否輸出為帶有 CSS Class 的 HTML 標籤（預設 false 輸出純文字）
     * @return string 星星圖樣字串
     */
    public static function getFrontendStarHTML(float $stars, bool $asHtml = false): string {
        // 💡 核心修正：當得分為 0 或 0.0 時，直接攔截並回傳 5 顆全空心星
        if ($stars == 0.0) {
            if ($asHtml) {
                return str_repeat('<span class="star star-empty">☆</span>', 5);
            } else {
                return '☆☆☆☆☆';
            }
        }

        // 正常分數限制與定錨（大於 0 的分數才會走到這裡）
        $stars = max(1.0, min(5.0, $stars));
        
        // 四捨五入到最近的 0.5 單位
        $roundedStars = round($stars * 2) / 2;

        $fullStarsCount = (int) floor($roundedStars);
        $hasHalfStar = ($roundedStars - $fullStarsCount) === 0.5 ? 1 : 0;
        $emptyStarsCount = 5 - $fullStarsCount - $hasHalfStar;

        if ($asHtml) {
            $html = '';
            for ($i = 0; $i < $fullStarsCount; $i++) $html .= '<span class="star star-full">★</span>';
            if ($hasHalfStar) $html .= '<span class="star star-half">★</span>';
            for ($i = 0; $i < $emptyStarsCount; $i++) $html .= '<span class="star star-empty">☆</span>';
            return $html;
        } else {
            $fullStars = str_repeat('★', $fullStarsCount);
            $halfStar  = $hasHalfStar ? '🌓' : '';
            $emptyStars = str_repeat('☆', $emptyStarsCount);
            return $fullStars . $halfStar . $emptyStars;
        }
    }


    /**
     * 【後台】計算推廣權重分數 (Promotion Score / TDS)
     * 
     * 結合用戶互動率（讚、倒讚、點閱）與 Hacker News 時間重力懲罰機制。
     *
     * 💡 程式碼調用簡單範例（建議每 10-15 分鐘透過 Cron Job 跑批次更新）：
     * <code>
     * $promoScore = ArticleRankingSystem::calculateBackendPromotionScore(
     *     $likes = 45, 
     *     $dislikes = 2, 
     *     $views = 1500, 
     *     $hoursSincePublished = 8.5
     * );
     * // 回傳結果：(float) 1.25843 (分數越高，推薦權重越高，可用於 ORDER BY 排序)
     * </code>
     * 
     * @param int $likes 文章累積的按讚總數
     * @param int $dislikes 文章累積的倒讚總數
     * @param int $views 文章總點閱數 (Views)
     * @param float $hoursSincePublished 文章發布至今的小時數 (可以有小數點)
     * @return float 分數越高推薦權重越高，可用於推薦榜 SQL 排序（可能為負分）
     */
    public static function calculateBackendPromotionScore(int $likes, int $dislikes, int $views, float $hoursSincePublished): float {
        $likes = max(0, $likes);
        $dislikes = max(0, $dislikes);
        $views = max(0, $views);
        $hoursSincePublished = max(0.0, $hoursSincePublished);

        // 分子：(讚 - 倒讚懲罰) + (瀏覽量 * 0.005)
        $engagementValue = ($likes - ($dislikes * self::$DISLIKE_WEIGHT)) + ($views * 0.005);
        
        // 分母：時間衰減重力
        $timeDecay = pow(($hoursSincePublished + 2.0), self::$GRAVITY);

        return round(($engagementValue / $timeDecay), 5);
    }
    /**
     * 【後台 - 星級制專用】計算推廣權重分數 (Promotion Score / TDS)
     * 
     * 適用於 1-5 星評分系統，結合平均星級、評分人數、點閱率與時間衰減。
     *
     * 💡 程式碼調用簡單範例：
     * <code>
     * $promoScore = ArticleRankingSystem::calculateBackendStarPromotionScore(
     *     $ratingSum = 450,       // 假設 100 個人評分，加起來共 450 顆星 (平均 4.5 星)
     *     $ratingCount = 100,     // 總評分人數 100 人
     *     $views = 2500,          // 點閱數 2500 次
     *     $hoursSincePublished = 4.0 // 發布 4 小時
     * );
     * // 回傳結果：(float) 1.70154 (可用於 ORDER BY 排序)
     * </code>
     * 
     * @param int $ratingSum 全站用戶評分的星星總累積滿分（例如3個人分別評5, 4, 3星，總和為12）
     * @param int $ratingCount 總共參與評分的人數
     * @param int $views 文章總點閱數 (Views)
     * @param float $hoursSincePublished 文章發布至今的小時數
     * @return float 分數越高推薦權重越高
     */
    public static function calculateBackendStarPromotionScore(int $ratingSum, int $ratingCount, int $views, float $hoursSincePublished): float {
        $ratingSum = max(0, $ratingSum);
        $ratingCount = max(0, $ratingCount);
        $views = max(0, $views);
        
        // 💡 改動 1：限制時間衰減的最大上限為 168 小時（7天），避免無限衰減
        $hoursSincePublished = min(168.0, max(0.0, $hoursSincePublished));

        $averageRating = $ratingCount > 0 ? ($ratingSum / $ratingCount) : 3.0;

        // 💡 改動 2：將瀏覽量權重提高至 0.05（可依實際體感調整）
        $engagementValue = (($averageRating - 3.0) * $ratingCount) + ($views * 0.05);
        
        $timeDecay = pow(($hoursSincePublished + 2.0), 1.5);

        // 💡 改動 3：若算出來是負數（低於3星），回傳 0；其餘保留
        $score = $engagementValue / $timeDecay;
        return round(max(0.0, $score), 5);
    }
    /**
     * 【後台 - 複合五組星級制專用】計算推廣權重分數 (Promotion Score / TDS)
     * 
     * 適用於擁有 5 組 1-5 分維度的複合式評分系統，結合複合平均星級、評分人數、點閱率與時間衰減。
     *
     * 💡 程式碼調用簡單範例：
     * <code>
     * // 假設 10 個人填寫了複合評分（每人填 5 組星，總滿分為 250 顆星）
     * // 所有人填寫的星星加總起來共 210 顆星
     * $promoScore = ArticleRankingSystem::calculateBackendCompositeStarPromotionScore(
     *     $ratingSum = 210,       // 五組維度全部累積的星星總和
     *     $ratingCount = 10,      // 總評分人數（填寫問卷的人數）
     *     $views = 1200,          // 點閱數 1200 次
     *     $hoursSincePublished = 2.5 // 發布 2.5 小時
     * );
     * // 運算邏輯：平均單一星級為 (210/10)/5 = 4.2 星。
     * // 回傳結果：(float) 1.25843 (可用於 ORDER BY 排序)
     * </code>
     * 
     * @param int $ratingSum 5組維度全部填寫的星星總累積得分（例如 1人各維度填5,4,5,4,4，總和為22）
     * @param int $ratingCount 總共參與評分的人數（問卷提交總次數）
     * @param int $views 文章總點閱數 (Views)
     * @param float $hoursSincePublished 文章發布至今的小時數
     * @return float 分數越高推薦權重越高
     */
    public static function calculateBackendCompositeStarPromotionScore(int $ratingSum, int $ratingCount, int $views, float $hoursSincePublished): float {
        $ratingSum = max(0, $ratingSum);
        $ratingCount = max(0, $ratingCount);
        $views = max(0, $views);
        $hoursSincePublished = max(0.0, $hoursSincePublished);

        // 每人提交 5 組，單人中立基準線為 3.0 * 5 = 15.0 顆星
        // 若無人評分，預設以全站冷啟動中立值 21.0 顆星（折合單一維度 4.2 星）作為基礎
        $averageCompositeRating = $ratingCount > 0 ? ($ratingSum / $ratingCount) : 21.0;

        // 分子：(每人平均總星數 - 15.0基準線) * 評分人數 + (瀏覽量 * 0.005)
        $engagementValue = (($averageCompositeRating - 15.0) * $ratingCount) + ($views * 0.005);
        
        // 分母：時間衰減重力 (Gravity = 1.5)
        $timeDecay = pow(($hoursSincePublished + 2.0), 1.5);

        return round(($engagementValue / $timeDecay), 5);
    }


}
