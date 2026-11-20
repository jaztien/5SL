<?php


class loString
{
    //use Javascript;
    //use PHP;

    /**
     * $subject.
     *
     * @var string
     */
    public static $fullCaseMap = [
        '　' => ' ',
        '！' => '!',
        '＂' => '"',
        '＃' => '#',
        '＄' => '$',
        '％' => '%',
        '＆' => '&',
        '＇' => "'",
        '（' => '(',
        '）' => ')',
        '＊' => '*',
        '＋' => '+',
        '，' => ',',
        '－' => '-',
        '．' => '.',
        '／' => '/',
        '０' => '0',
        '１' => '1',
        '２' => '2',
        '３' => '3',
        '４' => '4',
        '５' => '5',
        '６' => '6',
        '７' => '7',
        '８' => '8',
        '９' => '9',
        '：' => ':',
        '；' => ';',
        '＜' => '<',
        '＝' => '=',
        '＞' => '>',
        '？' => '?',
        '＠' => '@',
        'Ａ' => 'A',
        'Ｂ' => 'B',
        'Ｃ' => 'C',
        'Ｄ' => 'D',
        'Ｅ' => 'E',
        'Ｆ' => 'F',
        'Ｇ' => 'G',
        'Ｈ' => 'H',
        'Ｉ' => 'I',
        'Ｊ' => 'J',
        'Ｋ' => 'K',
        'Ｌ' => 'L',
        'Ｍ' => 'M',
        'Ｎ' => 'N',
        'Ｏ' => 'O',
        'Ｐ' => 'P',
        'Ｑ' => 'Q',
        'Ｒ' => 'R',
        'Ｓ' => 'S',
        'Ｔ' => 'T',
        'Ｕ' => 'U',
        'Ｖ' => 'V',
        'Ｗ' => 'W',
        'Ｘ' => 'X',
        'Ｙ' => 'Y',
        'Ｚ' => 'Z',
        '［' => '[',
        '＼' => '\\',
        '］' => ']',
        '＾' => '^',
        '＿' => '_',
        '｀' => '`',
        'ａ' => 'a',
        'ｂ' => 'b',
        'ｃ' => 'c',
        'ｄ' => 'd',
        'ｅ' => 'e',
        'ｆ' => 'f',
        'ｇ' => 'g',
        'ｈ' => 'h',
        'ｉ' => 'i',
        'ｊ' => 'j',
        'ｋ' => 'k',
        'ｌ' => 'l',
        'ｍ' => 'm',
        'ｎ' => 'n',
        'ｏ' => 'o',
        'ｐ' => 'p',
        'ｑ' => 'q',
        'ｒ' => 'r',
        'ｓ' => 's',
        'ｔ' => 't',
        'ｕ' => 'u',
        'ｖ' => 'v',
        'ｗ' => 'w',
        'ｘ' => 'x',
        'ｙ' => 'y',
        'ｚ' => 'z',
        '｛' => '{',
        '｜' => '|',
        '｝' => '}',
        '～' => '~',
    ];

    public $subject;

    /**
     * __construct.
     *
     * @param string $subject
     */
    public function __construct($subject = '')
    {
        $this->subject = $subject === null ? '' : $subject;
    }

    /**
     * factory.
     *
     * @param string $subject
     * @return static
     */
    public static function factory($subject = '')
    {
        return new static((string) $subject);
    }

    /**
     * supports.
     * @param mixed $object
     * @return bool
     */
    public static function supports($object)
    {
        return $object instanceof static || is_string($object) === true;
    }

    /**
     * __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value();
    }

    /**
     * value.
     *
     * @return string
     */
    public function value()
    {
        return $this->subject;
    }


    /**
     * The replace() method returns a new string with some or all matches of a pattern replaced by a replacement. The pattern can be a string or a RegExp, and the replacement can be a string or a function to be called for each match.
     *
     * @param mixed $regexp
     * @param mixed $replacement
     * @return static
     */
    public function replace($regexp, $replacement = null)
    {
        if (is_array($regexp) === true && is_null($replacement) === true) {
            return new static(strtr($this->subject, $regexp));
        }

        if (is_callable($replacement) === true) {
            return new static(preg_replace_callback($regexp, $replacement, $this->subject));
        }

        return new static(preg_replace($regexp, $replacement, $this->subject));
    }

    /**
     * The trim() method removes whitespace from both ends of a string. Whitespace in this context is all the whitespace characters (space, tab, no-break space, etc.) and all the line terminator characters (LF, CR, etc.).
     *
     * @param string $characterMask
     * @return static
     */
    public function trim($characterMask = " \t\n\r\0\x0B")
    {
        return $this->subject;
        return new static(trim($this->subject, $characterMask));
    }

    /**
     * The trimLeft() method removes whitespace from the left end of a string.
     *
     * @param string $characterMask
     * @return static
     */
    public function trimLeft($characterMask = " \t\n\r\0\x0B")
    {
        return $this->ltrim($characterMask);
    }

    /**
     * The trimRight() method removes whitespace from the right end of a string.
     *
     * @param string $characterMask
     * @return static
     */
    public function trimRight($characterMask = " \t\n\r\0\x0B")
    {
        return $this->rtrim($characterMask);
    }


    /**
     * toHalfCase.
     *
     * @return string
     */
    public function toHalfCase()
    {
        //return static::$fullCaseMap;
        return strtr($this->subject, static::$fullCaseMap);
    }

    /**
     * toFullCase.
     *
     * @return string
     */
    public function toFullCase()
    {
        return strtr($this->subject, array_flip(static::$fullCaseMap));
    }










}










?>