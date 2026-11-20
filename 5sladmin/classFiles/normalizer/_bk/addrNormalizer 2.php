<?php

//namespace Recca0120\Twzipcode;

//use Recca0120\Lodash\JString;

require(__DIR__ . '/lodash_string.php');


class NormAddress extends loString
{



//---- Address.php--------------------------------------------------------------------


    // @var int
    const NO = 0;

    // @var int
    const SUBNO = 1;

    // @var int
    const NAME = 2;

    // @var int
    const UNIT = 3;

    // @var Normalizer
    public $normalizer;

    // @var JArray
    public $tokens = [];

    
     //@param  static|string  $address
    
    public function __construct($address = '')
    {
        if (empty($address) === false) {
            $this->set($address);
        }
    }

    
    //@param  static|string  $address
    //@return $this
    public function set($address)
    {
        //$this->subject = $address;
        //$this->normalizer = (new Normalizer($address))->normalize()->normalizeAddress();
        //$this->normalized = $this->normalize()->normalizeAddress();
        $this->normalizer =  $this->regularize();
        $this->tokens = $this->tokenize();

        return $this;
    }

    //@return array
    private function tokenize()
    {
        $units = [static::NO => 'no', static::SUBNO => 'subno', static::NAME => 'name', static::UNIT => 'unit'];

        $patterns = implode('', [
            '(?:(?P<no>\d+)(?P<subno>之\d+)?(?=[巷弄號樓]|$)|(?P<name>.+?))',
            '(?:(?P<unit>([島縣市鄉鎮市區村里道鄰路街段巷弄號樓]|魚臺))|(?=\d+(?:之\d+)?[巷弄號樓]|$))',
        ]);

        //$tricky = Tricky::instance();
        //$address = $tricky->hash($this->normalizer)->value();
        $tokens = [];
        if (preg_match_all('/'.$patterns.'/u', $address, $matches, PREG_SET_ORDER) !== false) {
            foreach ($matches as $values) {
                $token = array_map(static function ($unit) use ($values) {
                    return isset($values[$unit]) === true ? $values[$unit] : '';
                }, $units);
                //$token[static::NAME] = $tricky->flip($token[static::NAME]);
                $tokens[] = $token;
            }
        }

        return $tokens;
    }


    /*
    //@return JArray
    
    public function tokens()
    {
        return new JArray($this->tokens);
    }

    
    //@param  string  $index
    //@return Point
    
    public function point($index)
    {
        if (isset($this->tokens[$index]) === false) {
            return new Point(0, 0);
        }

        $token = $this->tokens[$index];

        return new Point(
            (int) $token[static::NO] ?: 0,
            (int) str_replace('之', '', $token[static::SUBNO] ?: '0')
        );
    }

    
    //@param  int  $length
    //@param  int  $offset
    //@return string
    
    public function flat($length = null, $offset = 0)
    {
        $tokens = $this->tokens();
        $length = $length ?: $tokens->length();
        $end = $offset + $length;

        return (string) $tokens->slice($offset, $end)->map(function ($token) {
            return implode('', $token);
        })->join('');
    }

    
    //@return string
    
    public function __toString()
    {
        return $this->normalizer->value();
    }
    */





//------------------------------------------------------------------------




    //@return static
     
    public function normalizeAddress()
    {
        return $this
            ->replace([
                '台灣' => '臺灣',
                '台北' => '臺北',
                '台中' => '臺中',
                '台南' => '臺南',
                '台東' => '臺東',
                '釣魚台' => '釣魚臺',
                '台西鄉' => '臺西鄉',
                '霧台鄉' => '霧臺鄉',
                '[石曹]村' => '[石曹]里',
                '頭家東村' => '頭家東里',
                '拉芙蘭村' => '拉芙蘭里',
                '那瑪夏鄉' => '那瑪夏區',
                '達卡努瓦村' => '達卡努瓦里',
                '瑪雅村' => '瑪雅里',
                '南沙魯村' => '南沙魯里',
            ])
            ->replace('/^北縣/', '臺北縣')
            ->replace('/^北市/', '臺北市')
            ->replace('/^(桃園|臺北|臺中|臺南|高雄)縣(?:(\w{2})[市鄉鎮])?(?:(\w{2})村)?/u', function ($m) {
                return ($m[1] === '臺北' ? '新北' : $m[1]).'市'.
                    (isset($m[2]) === true ? $m[2].'區' : '').
                    (isset($m[3]) === true ? $m[3].'里' : '');
            })->replace([
                '苗栗縣頭份鎮' => '苗栗縣頭份市',
                '彰化縣員林鎮' => '彰化縣員林市',
                '臺南市中區' => '臺南市中西區',
                '臺南市西區' => '臺南市中西區',
                '南海諸島東沙' => '南海島東沙群島',
                '南海諸島南沙' => '南海島南沙群島',
                '南海諸島釣魚臺列嶼' => '釣魚臺釣魚臺',
            ]);
    }

    
    //@return static
    
    public function normalize()
    {
        //return "this";
        //return $this->trim()->regularize()->digitize();
        return $this->trim()->regularize();
    }

    
    //@return static
    
    public function digitize()
    {
        return $this->replace('/[一二三四五六七八九十百千]+(?=[段路街巷弄號樓])/u', function ($m) {
            //return (new static($m[0]))->chineseToNumber();
            return (new static($m[0]))->toNumber();
        });
    }

    
    //@return static
    
    public function regularize()
    {
        return $this
            //->toHalfCase()
            ->replace('/^\d+/', '')
            ->replace([
                ' ' => '',
                ',' => '',
                '~' => '之',
                '-' => '之',
                '台灣' => '臺灣',
                '台北' => '臺北',
                '台中' => '臺中',
                '台南' => '臺南',
                '台東' => '臺東',
                '釣魚台' => '釣魚臺',
                '台西鄉' => '臺西鄉',
                '霧台鄉' => '霧臺鄉',
            ]);

    }


    
    // toNumber.
    // @return int
     
    public function toNumber()
    {
        $sum = 0;
        $matches = [];
        if ((bool) preg_match_all('/(?P<number>[一二三四五六七八九]+)?(?P<unit>[萬千百十])?/u', $this->subject, $matches, PREG_SET_ORDER) === false) {
            return $sum;
        }

        $sum = 0;
        foreach ((array) $matches as $token) {
            $unit = empty($token['unit']) === false ? $token['unit'] : '個';
            $unit = static::$numberUnits[$unit];
            if (empty($token['number']) === true) {
                $sum = ($sum === 0 ? 1 : $sum) * $unit;

                continue;
            }

            $number = $token['number'];
            $number = strtr($number, [
                '零' => 0,
                '一' => 1,
                '二' => 2,
                '三' => 3,
                '四' => 4,
                '五' => 5,
                '六' => 6,
                '七' => 7,
                '八' => 8,
                '九' => 9,
            ]);

            if ($unit >= 10000) {
                $sum = $sum * $unit;
            }

            $sum += ($number * $unit);
        }

        return $sum;
    }



























}
