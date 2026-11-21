<?php

/**
 * Class Evs (Evaluation System)
 * 
 * 綜合評級統計與熱度排序系統（完全體）。
 * 支援三種模式：複合多組星級(mode=1)、單組綜合星級(mode=2)、讚與倒讚二元制(mode=3)。
 * 本系統全面對齊資料庫實體欄位命名（例如 visits 瀏覽量）。
 */
class Evs {
    
    /** @var float 拉普拉斯常數 K（分子加權，確保新課程商品預設為 4.2 星） */
    public static float $K = 1.6;
    
    /** @var float 拉普拉斯常數 M（分母加權） */
    public static float $M = 2.0;

    /**
     * 【新評分寫入】當收到新評分時，即時更新分組與總平均
     * 
     * 📌 傳入參數說明：
     * @param string $prefix 目標資料表的欄位前綴，通常為 'advp_' 或 'md_'，用於生成欄位名稱。
     * 
     * @param int $mode 模式類別 (1: 五組星級, 2: 一組星級, 3: 讚與倒讚)
     * 
     * @param array $current 來自資料庫目前該紀錄的資料。結構需為：
     *                      - 模式 1: ['total_score'=>120, 'total_votes'=>6, 'g1_avg'=>4.0, 'g2_avg'=>4.2, 'g3_avg'=>3.8, 'g4_avg'=>4.0, 'g5_avg'=>4.0]
     *                      - 模式 2: ['total_score'=>24,  'total_votes'=>6, 'g1_avg'=>4.0]
     *                      - 模式 3: ['total_score'=>12,  'total_votes'=>2] (total_score代表讚總數, total_votes代表倒讚總數)
     * 
     * @param array|int $new 新接收到的評分數據：
     *                       - 模式 1: 傳入陣列 [5, 4, 5, 3, 4] (代表五個維度各給幾分)
     *                       - 模式 2: 傳入陣列 [4] (代表綜合評分給幾分)
     *                       - 模式 3: 傳入整數 1 代表點讚，整數 2 代表點倒讚
     * 
     * 💡 程式碼調用簡單範例：
     * <code>
     * $curr = ['mb_ratetop' => 20, 'mb_ratebottom' => 5, 'mb_s1' => 4.0];
     * $res = Evs::update('mb_', 2, $curr, [5]); 
     * // 回傳結果：
     * // [
     * //   'update_data'    => ['total_votes'=>6, 'total_score'=>25, 'g1_avg'=>4.2],
     * //   'group_averages' => ['g1_avg'=>4.2],
     * //   'total_average'  => 4.2
     * // ]
     * </code>
     * 
     * @return array 包含 update_data(供UPDATE SQL使用), group_averages(分組最新平均), total_average(最新總平均)
     */
    public static function update($prefix,int $mode, array $current, $new): array {
        $up = [];
        $gAvg = [];
        $totalAvg = 0.0;

        if ($mode === 1 || $mode === 2) {
            $groups = ($mode === 1) ? 5 : 1;
            $nextCounts = (int)$current[$prefix.'ratebottom'] + 1;
            $up[$prefix.'ratebottom'] = $nextCounts;
            $up[$prefix.'ratetop'] = (int)$current[$prefix.'ratetop'] + array_sum($new);

            $sumAvg = 0.0;
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $groups) {
                    $oldAvg = isset($current[$prefix."s{$i}"]) ? (float)$current[$prefix."s{$i}"] : 0.0;
                    $newVal = (int)$new[$i - 1];
                    $newAvg = round((($oldAvg * $current[$prefix.'ratebottom']) + $newVal) / $nextCounts, 1);
                    
                    $gAvg[$prefix."s{$i}"] = $newAvg;
                    $up[$prefix."s{$i}"] = $newAvg;
                    $sumAvg += $newAvg;
                } else {
                    $gAvg[$prefix."s{$i}"] = 0.0;
                }
            }
            $totalAvg = round($sumAvg / $groups, 1);

        } else if ($mode === 3) {
            $nextLikes = (int)$current[$prefix.'ratetop'] + ((int)$new === 1 ? 1 : 0);
            $nextDislikes = (int)$current[$prefix.'ratebottom'] + ((int)$new === 2 ? 1 : 0);

            $up[$prefix.'ratetop'] = $nextLikes;
            $up[$prefix.'ratebottom'] = $nextDislikes;

            $totalCounts = $nextLikes + $nextDislikes;
            $totalAvg = ($totalCounts > 0) ? round(1.0 + 4.0 * ($nextLikes / $totalCounts), 1) : 4.2;
        }

        return ['upParams' => $up, 'groupAVG' => $gAvg, 'totalAVG' => $totalAvg];
    }

    /**
     * 【LSR 核心】透過「真實總平均」與「有效總票數」計算拉普拉斯平滑星級
     * 
     * 適用於模式 1 與模式 2，將已經算好的實時或快取總平均進行統計平滑修正。
     * 
     * 📌 傳入參數說明：
     * @param float $avg 1.0 ~ 5.0 的真實總平均分數
     * @param int $count 有效的總評分人數（或總投票數）
     * 
     * 💡 程式碼調用簡單範例：
     * <code>
     * $score = Evs::lsrFromAvg(4.8, 15); 
     * // 回傳結果：(float) 4.5 
     * </code>
     * 
     * @return float 平滑後的星級分數 (1.0 到 5.0)
     */
    public static function lsrFromAvg(float $avg, int $count): float {
        if ($count === 0) return 4.2;
        $p = ($avg - 1.0) / 4.0;
        $smoothedP = ($count * $p + self::$K) / ($count + self::$M);
        return round(1.0 + 4.0 * $smoothedP, 1);
    }

    /**
     * 【LSR 模式 3 快捷】直接傳入「讚總數」與「倒讚總數」計算拉普拉斯平滑星級
     * 
     * 專門提供給模式 3（讚/倒讚二元制）使用的快捷入口，內部自動將二元數據換算為統計學標準指標。
     * 
     * 📌 傳入參數說明：
     * @param int $likes 該課程商品目前累積的按讚總次數
     * @param int $dislikes 該課程商品目前累積的倒讚總次數
     * 
     * 💡 程式碼調用簡單範例：
     * <code>
     * $score = Evs::lsrForLikes(8, 2); 
     * // 回傳結果：(float) 4.1
     * </code>
     * 
     * @return float 平滑後的星級分數 (1.0 到 5.0)
     */
    public static function lsrForLikes(int $likes, int $dislikes): float {
        $total = max(0, $likes) + max(0, $dislikes);
        $avg = ($total > 0) ? (1.0 + 4.0 * (max(0, $likes) / $total)) : 4.2;
        return self::lsrFromAvg($avg, $total);
    }

    /**
     * 【TDS 後台排序】計算結合星級、投票數、瀏覽量(visits)與時間衰減的推廣分數
     * 
     * 📌 傳入參數說明：
     * @param float $totalAvg 1.0 ~ 5.0 的真實總平均得分
     * @param int $totalCount 有效的總評分人數（或總投票數）
     * @param int $visits 來自資料表的最新瀏覽量 (visits)
     * @param float $hours 發布至今的小時數
     * 
     * 💡 程式碼調用簡單範例：
     * <code>
     * $promoScore = Evs::tds(4.2, 25, 1500, 8.5);
     * // 回傳結果：(float) 1.15423
     * </code>
     * 
     * @return float 推廣排序分數（分數越高推薦權重越高，可用於排序）
     */
    public static function tds(float $totalAvg, int $totalCount, int $visits, float $hours): float {
        // 分子：((平均分 - 3.0基準線) * 投票數) + (瀏覽量 visits * 0.005紅利)
        $engagement = (($totalAvg - 3.0) * max(0, $totalCount)) + (max(0, $visits) * 0.005);
        
        // 分母：時間衰減重力 (Gravity = 1.5)
        $timeDecay = pow((max(0.0, $hours) + 2.0), 1.5);

        return round(($engagement / $timeDecay), 5);
    }

    /**
     * 【前台星星】將浮點數分數轉換為 0.5 星精細度的星星圖樣
     * 
     * 📌 傳入參數說明：
     * @param float $stars 經由平滑公式計算出來的 1.0 ~ 5.0 浮點數得分
     * @param bool $asHtml 是否輸出為帶有 CSS Class 的 HTML 標籤（預設 false 輸出純文字）
     * 
     * 💡 程式碼調用簡單範例：
     * <code>
     * $emoji = Evs::stars(4.3, false); // 輸出結果: "★★★★🌓"
     * $html  = Evs::stars(4.3, true);  // 輸出結果: "<span class=\"star star-full\">★</span>..."
     * </code>
     * 
     * @return string 星星圖樣字串
     */
    public static function stars(float $stars, bool $asHtml = false): string {
        $stars = max(1.0, min(5.0, $stars));
        $rounded = round($stars * 2) / 2;
        $full = (int) floor($rounded);
        $half = ($rounded - $full) === 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;

        if ($asHtml) {
            $html = '';
            for ($i = 0; $i < $full; $i++) $html .= '<span class="star star-full">★</span>';
            if ($half) $html .= '<span class="star star-half">★</span>';
            for ($i = 0; $i < $empty; $i++) $html .= '<span class="star star-empty">☆</span>';
            return $html;
        } else {
            return str_repeat('★', $full) . ($half ? '🌓' : '') . str_repeat('☆', $empty);
        }
    }
}
