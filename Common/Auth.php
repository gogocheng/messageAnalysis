<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2018/7/10
 * Time: 16:14
 */

namespace Workerman\Common;

//公共方法类库
class Auth
{


    /**
     * description  转成10进制字符串数组
     * @param $string 16进制字符串
     * @return array   10进制数组
     */
    public static function get10Bytes ($string)
    {
        $bytes = array ();
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            array_push($bytes, ord($string[$i]));
        }
        return $bytes;
    }

    /**
     * description  10进制字符串数组转成16进制字符串数组
     * @param $data 10进制字符串数组
     * @return mixed 16进制字符串数组
     */
    public static function getTo16Bytes ($data)
    {
        $len = count($data);
        for ($i = 0; $i < $len; $i++) {
            $array[$i] = base_convert($data[$i], 10, 16);
        }
        return $array;
    }


    /**
     * description   接受到的16进制字符补0  例如：01=>0x01
     * @param $data 16进制数组
     * @return array  补0之后的16进制数组
     */
    public static function supplementZero ($data)
    {
        $len = count($data);
        $res = array ();
        for ($j = 0; $j < $len; $j++) {
            if (strlen($data[$j]) == 1) {
                $res[$j] = "0x" . "0" . $data[$j];
            } else {
                $res[$j] = "0x" . $data[$j];
            }
        }
        return $res;
    }

    /**
     * description  把一个4位的数组转化位整形
     * @param array  接受数组
     * @return int  返回int
     */
    public static function bytesToInt ($data)
    {
        $len = count($data);
        for ($i = 0; $i < $len; $i++) {
            $data[$i] = intval(base_convert($data[$i], 16, 10));
        }
        $temp0 = $data[0] & 0xFF;
        $temp1 = $data[1] & 0xFF;
        $temp2 = $data[2] & 0xFF;
        $temp3 = $data[3] & 0xFF;
        return (($temp0 << 24) + ($temp1 << 16) + ($temp2 << 8) + $temp3);
    }

    /**
     * description  BCD码转字符串
     * @param array  数组
     * @return bool|string  返回字符串
     */
    public static function bcdToString ($data)
    {
        $len = count($data);
        $temp = "";
        for ($i = 0; $i < $len; $i++) {
            // 高四位
            $temp .= (($data[$i] & 0xf0) >> 4);
            // 低四位
            $temp .= ($data[$i] & 0x0f);
        }
        return (substr($temp, 0, 1) == 0) ? substr($temp, 1) : $temp;
    }

    /**
     * description  从接受到的16进制数组中获取设备号数组
     * @param $data  接受到的16进制数组
     * @return string 设备号id
     */
    public static function getSensorId ($data)
    {
        $sensorArray = array_slice($data, 3, 6);
        $sensorArrayZero = self ::supplementZero($sensorArray);
        $len = count($sensorArrayZero);
        $res = [];
        for ($i = 0; $i < $len; $i++) {
            $res[$i] = intval(base_convert($sensorArrayZero[$i], 16, 10));
        }
        $string = self ::bcdToString($res);
        return $string;
    }

    /**
     * description   把一个二字节数组转化成整型
     * @param $data  二字节数组
     * @return int   整型
     */
    public static function twoBytesToInteger ($data)
    {
        $len = count($data);
        for ($i = 0; $i < $len; $i++) {
            $data[$i] = intval(base_convert($data[$i], 16, 10));
        }
        $temp0 = $data[0] & 0xFF;
        $temp1 = $data[1] & 0xFF;
        return (($temp0 << 8) + $temp1);
    }

    /**
     * description  接受内容中4字节数组转成int
     * @param $data 16进制字节数组
     * @param int $a 开始位
     * @return int   int值
     */
    public static function getNum ($data, $a = 0)
    {
        $numArray = array_slice($data, $a, 4);
        $res = self ::bytesToInt($numArray);
        return $res;
    }

    /**
     * description  按位异或
     * @param $data
     * @return int
     */
    public static function getEveryXor ($data)
    {
        $len = count($data);
        $rew = 0;
        for ($i = 1; $i < $len; $i++) {
            $rew = $rew ^ $data[$i];
        }
        return $rew;
    }

    /**
     * description   将字节数组转为字符串
     * @param array   字节数组
     * @return string   返回字符串
     */
    public static function bytesArrayToString ($data)
    {
        $str = '';
        foreach ($data as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }

    /**
     * description 拼接字符串
     * @param $str
     * @param int $n
     * @param string $char
     * @return string
     */
    public static function getTurnStr ($str, $n = 1, $char = "")
    {
        for ($i = 0; $i < $n; $i++) {
            $str = $char . $str;
        }
        return $str;
    }

    /**
     * description  转成二进制字符串
     * @param $data array 16进制数组
     * @return string  字符串
     */
    public static function getTwoStr ($data)
    {
        //转成2进制
        $str = array ();
        $req = array ();
        foreach ($data as $key => $value) {
            $str[$key] = base_convert($data[$key], 16, 2);
            $leng = 8 - strlen($str[$key]);
            $req[] = self ::getTurnStr($str[$key], $leng, "0");
        }
        //拼接字符串
        $rtq = implode("", $req);
        return $rtq;
    }


}