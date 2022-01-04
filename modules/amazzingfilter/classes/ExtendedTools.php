<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class ExtendedTools extends Tools
{
    public static function explodeRangeValue($value)
    {
        $value = explode('-', $value);
        $from = (float)$value[0];
        $to = isset($value[1]) ? (float)$value[1] : $from;
        return array($from, $to);
    }

    public static function isBrightColor($color)
    {
        $hex_code = str_split(trim($color, '#'));
        if (count($hex_code) != 6) {
            $is_bright = false;
        } else {
            $r = hexdec($hex_code[0].$hex_code[1]);
            $g = hexdec($hex_code[2].$hex_code[3]);
            $b = hexdec($hex_code[4].$hex_code[5]);
            $is_bright = $r + $g + $b > 700;
        }
        return $is_bright;
    }
}
