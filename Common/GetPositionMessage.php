<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2018/9/27
 * Time: 15:16
 */

namespace Workerman\Common;

use Workerman\Common\Auth;
use Workerman\Common\GetAboutParameter;

//解析回传数据
class GetPositionMessage
{
    /**
     * description  获取设备号
     * @param $data array 16进制数组
     * @return bool|string   返回字符串设备号
     */
    public static function getEquipmentNumber ($data)
    {
        $equipmentArray = array_slice($data, 5, 6);
        $len = count($equipmentArray);
        for ($j = 0; $j < $len; $j++) {
            $equipmentArray[$j] = base_convert($equipmentArray[$j], 16, 10);
        }
        $equipmentNumber = Auth ::bcdToString($equipmentArray);
        return $equipmentNumber;
    }

    /**
     * description  获取报警信息
     * @param $data array 16进制数组
     * @param $index  数组索引
     * @return int  返回数字代表报警信息
     */
    public static function getAlarmMessage ($data, $index)
    {
        $alarmArray = Auth ::getTwoStr(array_slice($data, $index, 4));
        if (substr($alarmArray, -8, 1) == 1) {
            //主电源电压低
            $alarm = "主电源电压低";
        } elseif (substr($alarmArray, -30, 1) == 1) {
            //碰撞预警
            $alarm = "碰撞预警";
        } elseif (substr($alarmArray, -31, 1) == 1) {
            //侧翻预警
            $alarm = "侧翻预警";
        }
//        elseif (substr($alarmArray, -26, 1) == 1) {
//            //脱落(光感)报警
//            $alarm = 4;
//        }
        else {
            //正常
            $alarm = "一切正常";
        }
        return $alarm;
    }

    /**
     * description   获取位置信息
     * @param $data array 16进制数组
     * @param $index   数组索引
     * @return array  位置信息数组
     */
    public static function getPositionStatus ($data, $index)
    {
        $positionArray = Auth ::getTwoStr(array_slice($data, $index + 4, 4));
        //判断是否定位，0定位，1未定位
        $isPosition = substr($positionArray, -2, 1) == 0 ? $isPosition = "未定位" : $isPosition = "定位";
        //判断南北纬，0北纬，1南纬
        $isNorSou = substr($positionArray, -3, 1) == 0 ? $isNorSou = "北纬" : $isNorSou = "南纬";
        //判断东西经，0东经，1西经
        $isEasWes = substr($positionArray, -4, 1) == 0 ? $isEasWes = "东经" : $isEasWes = "西经";
        //判断定位方式
        if (substr($positionArray, -19, 1) == 1 && substr($positionArray, -20, 1) == 0) {
            //北斗定位
            $positionMethod = "北斗定位";
        } elseif (substr($positionArray, -19, 1) == 0 && substr($positionArray, -20, 1) == 1) {
            //GPS定位
            $positionMethod = "GPS定位";
        } elseif (substr($positionArray, -19, 1) == 1 && substr($positionArray, -20, 1) == 1) {
            //北斗GPS双定位
            $positionMethod = "北斗GPS双定位";
        } else {
            //北斗GPS都未定位
            $positionMethod = "北斗GPS都未定位";
        }
        $positionStatusArray = array (
            'position' => $isPosition,
            'ns' => $isNorSou,
            'ew' => $isEasWes,
            'gps' => $positionMethod
        );
        return $positionStatusArray;
    }

    /**
     * description   获取纬度
     * @param $data array  16进制数组
     * @param $index  数组索引
     * @return float|int   纬度
     */
    public static function getLatitude ($data, $index)
    {
        $latitudeBytes = array_slice($data, $index + 8, 4);
        $latitude = Auth ::bytesToInt($latitudeBytes) / pow(10, 6);
        return $latitude;
    }

    /**
     * description  获取经度
     * @param $data array  16进制数组
     * @param $index  数组索引
     * @return float|int  经度
     */
    public static function getLongitude ($data, $index)
    {
        $longitudeBytes = array_slice($data, $index + 12, 4);
        $longitude = Auth ::bytesToInt($longitudeBytes) / pow(10, 6);
        return $longitude;
    }

    /**
     * description  获取日期时间
     * @param $data array  16进制数组
     * @param $index  数组索引
     * @return string   日期时间字符串
     */
    public static function getDatetime ($data, $index)
    {
        $datetimeArray = array_slice($data, $index + 22, 6);
        $len = count($datetimeArray);
        for ($k = 0; $k < $len; $k++) {
            $datetimeArray[$k] = base_convert($datetimeArray[$k], 16, 10);
        }
        $datetime = Auth ::bcdToString($datetimeArray);
        $datetimeStr = "20" . substr($datetime, 0, 2) . "-" . substr($datetime, 2, 2) . "-" . substr($datetime, 4, 2) . " " . substr($datetime, 6, 2) . ":" . substr($datetime, 8, 2) . ":" . substr($datetime, 10, 2);
        return $datetimeStr;
    }

    /**
     * description  获取船舶id
     * @param $data array   16进制数组
     * @return mixd  $shipId
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getShipId ($data)
    {
        //设备号
//        $equipmentNumber = self ::getEquipmentNumber($data);
        $equipmentNumber = 18603127991;
        // 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
        global $db;
        // 执行SQL
        $data = $db -> select("id") -> from('cmf_ship') -> where("number = '$equipmentNumber'") -> query();
        var_dump($data[0]['id']);
        die();
        $shipId = $data[0]['id'];
        return $shipId;
    }

    /**
     * description  获取入库数组
     * @param $data array   进制数组
     * @param $index  数组索引
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getMessageArray ($data, $index)
    {
        //位置信息数组
        $positionStatusArray = self ::getPositionStatus($data, $index);
        //船舶id
        $shipId = self ::getShipId($data);
        //数据日志
        $log = implode(",", $data);
        //是否定位
        $position = $positionStatusArray['position'];
        //南北纬
        $ns = $positionStatusArray['ns'];
        //东西经
        $ew = $positionStatusArray['ew'];
        //定位方式
        $gps = $positionStatusArray['gps'];
        //报警信息
        $alarm = self ::getAlarmMessage($data, $index);
        //日期时间
        $time = self ::getDatetime($data, $index);
        //纬度
        $latitude = self ::getLatitude($data, $index);
        //经度
        $longitude = self ::getLongitude($data, $index);

        $positionArray = array (
            'ship_id' => $shipId,
            'time' => $time,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'ns' => $ns,
            'position' => $position,
            'ew' => $ew,
            'alarm' => $alarm,
            'gps' => $gps,
            'log' => $log
        );
        return $positionArray;
    }

}