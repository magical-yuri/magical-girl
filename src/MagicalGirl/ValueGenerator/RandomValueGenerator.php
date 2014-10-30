<?php

namespace MagicalGirl\ValueGenerator;

class RandomValueGenerator
{
    const LETTER_TYPE_ALL = 0;
    const LETTER_TYPE_SINGLE = 1;
    const LETTER_TYPE_MULTI = 2;
    const LETTER_TYPE_UPPER_CASE = 3;
    const LETTER_TYPE_UPPER_CASE_MULTI = 4;
    const LETTER_TYPE_LOWER_CASE = 5;
    const LETTER_TYPE_LOWER_CASE_MULTI = 6;
    const LETTER_TYPE_NUM = 7;
    const LETTER_TYPE_NUM_MULTI = 8;
    const LETTER_TYPE_SYMBOL = 9;
    const LETTER_TYPE_SYMBOL_MULTI = 10;
    const LETTER_TYPE_HIRAGANA = 11;
    const LETTER_TYPE_KATAKANA = 12;
    const LETTER_TYPE_KATAKANA_MULTI = 13;

    private static $LETTER_SINGLE = array (
        self::LETTER_TYPE_UPPER_CASE => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::LETTER_TYPE_LOWER_CASE => 'abcdefghijklmnopqrstuvwxyz',
        self::LETTER_TYPE_NUM => '0123456789',
        self::LETTER_TYPE_SYMBOL => '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~',
        self::LETTER_TYPE_KATAKANA => 'ｦｧｨｩｪｫｬｭｮｯｰｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜﾝ',
    );

    private static $LETTER_MULTI = array (
        self::LETTER_TYPE_UPPER_CASE_MULTI => 'ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ',
        self::LETTER_TYPE_LOWER_CASE_MULTI => 'ａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚ',
        self::LETTER_TYPE_NUM_MULTI => '０１２３４５６７８９',
        self::LETTER_TYPE_SYMBOL_MULTI => '！”＃％＆’（）＊＋、ー。／；：＜＝＞？＠「・」＾＿｀『｜』〜',
        self::LETTER_TYPE_HIRAGANA => 'ぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをん',
        self::LETTER_TYPE_KATAKANA_MULTI => 'アィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶ'
    );

    public static function getRandomString($length, array $letterTypes, $step = 0)
    {
        if ($step == 0) {
            $step = $length;
        }

        $seed = '';
        foreach ($letterTypes as $letterType) {
            $seed .= self::getLetterString($letterType);
        }

        $max = mb_strlen($seed) - 1;
        $str = '';
        for ($i = 0; $i < $length; $i += $step) {
            $str .= str_repeat(mb_substr($seed, mt_rand(0, $max), 1), $step);
        }

        return $str;
    }

    private static function getLetterString($letterType)
    {
        switch ($letterType) {
            case self::LETTER_TYPE_ALL:
                return implode(self::$LETTER_SINGLE) . implode(self::$LETTER_MULTI);
                break;
            case self::LETTER_TYPE_MULTI:
                return implode(self::$LETTER_MULTI);
                break;
            case self::LETTER_TYPE_SINGLE:
                return implode(self::$LETTER_SINGLE);
                break;
            default:
                if (array_key_exists($letterType, self::$LETTER_SINGLE)) {
                    return self::$LETTER_SINGLE[$letterType];
                } else if (array_key_exists($letterType, self::$LETTER_MULTI)) {
                    return self::$LETTER_MULTI[$letterType];
                }
                return '';
        }
    }

    public static function getRandomInt($min, $max)
    {
        $max = $max > mt_getrandmax() ? mt_getrandmax() : $max;
        return mt_rand($min, $max);
    }

    public static function getRandomFloat($min, $max, $precision = 10)
    {
        $ajust = pow(10, $precision);
        return mt_rand($min * $ajust, $max * $ajust) / $ajust;
    }

    public static function getRandomBool()
    {
        return (bool) mt_rand(0, 1);
    }

    public static function getRandomDate($minTimestamp, $maxTimestamp)
    {
        date_default_timezone_set('Asia/Tokyo');
        return strftime('%Y-%m-%d', mt_rand($minTimestamp, $maxTimestamp));
    }

    public static function getRandomDateTime($minTimestamp, $maxTimestamp)
    {
        date_default_timezone_set('Asia/Tokyo');
        return strftime('%Y-%m-%d %H:%M:%S', mt_rand($minTimestamp, $maxTimestamp));
    }

    public static function getRandomMultilineText($lineLength, $lineCount, array $letterTypes, $lineSeparator = "\n")
    {
        $lines = array();
        for ($i = 0; $i < $lineCount; $i++) {
            $lines [] = self::getRandomString($lineLength, $letterTypes);
        }
        $str = implode($lineSeparator, $lines);

        return $str;
    }

    public static function getRandomList(array $list)
    {
        return $list[mt_rand(0,count($list) - 1)];
    }

}
