<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2018/9/26
 * Time: 17:19
 */

namespace Workerman\Common;

use Workerman\Common\Auth;


//发送客户端数据类库
class GetAboutParameter
{
    public static $sequenceNumber = 0;//流水号初始值

    /**
     * description  获取16进制数组来计算出设备号
     * @param $data 16进制数组
     * @return array  返回设备号数组
     */
    public static function getEquipmentNumberArray ($data)
    {
        $num_array = array_slice($data, 5, 6);
        $len = count($num_array);
        for ($j = 0; $j < $len; $j++) {
            $num_array[$j] = intval(base_convert($num_array[$j], 16, 10));
        }
        return $num_array;
    }

    /**
     * description   获取平台流水号
     * @return array   返回流水号数组
     */
    public static function getSequenceNumberArray ()
    {
        //计算流水号
        $number = self ::$sequenceNumber++;
        if ($number > 65025) { // 255 * 255 -1
            $number = 1;
        }
        //将十进制流水号换算成16进制流水号
        $get16Number = base_convert($number, 10, 16);
        $af = substr($get16Number, 0, 2);
        $bf = substr($get16Number, 2);
        $systemNumber = [];
        //判断
        if ($number > 0xff) {
            $systemNumber = array ( '0x' . $af, '0x' . $bf );
        } else {
            $systemNumber = array ( '0x00', '0x' . $get16Number );
        }
        $len = count($systemNumber);
        for ($i = 0; $i < $len; $i++) {
            $systemNumber[$i] = intval(base_convert($systemNumber[$i], 16, 10));
        }
        return $systemNumber;
    }

    /**
     * description  获取消息流水号
     * @param $data 16进制数组
     * @return array  消息流水号数组
     */
    public static function getMessageNumberArray ($data)
    {
        $messageNumber = array_slice($data, 11, 2);
        $messageNumber = Auth ::supplementZero($messageNumber);
        return $messageNumber;
    }

    /**
     * description  获取消息id
     * @param $data 16进制数组
     * @return array  消息id数组
     */
    public static function getMessageIdArray ($data)
    {
        $messageId = array_slice($data, 1, 2);
        $messageId = Auth ::supplementZero($messageId);
        return $messageId;
    }

    /**
     * description   获取消息体
     * @param $data 16进制数组
     * @return array   消息体数组
     */
    public static function getMessageBodyArray ($data)
    {
        //消息体 = 消息流水号 + 消息id
        $messageNumber = self ::getMessageNumberArray($data);
        $messageId = self ::getMessageIdArray($data);
        $messageBody = array_merge($messageNumber, $messageId);
        $len = count($messageBody);
        for ($i = 0; $i < $len; $i++) {
            $messageBody[$i] = intval(base_convert($messageBody[$i], 16, 10));
        }
        return $messageBody;
    }

    /**
     * description  发送给客户端的回传数据
     * @param $data 16进制数组
     * @return string   返回客户端字符串
     */
    public static function getVerifyNumberArray ($data)
    {
        //数组开始五位
        $arrayStartFiveBytes = array ( 0x7e, 0x80, 0x01, 0x00, 0x05 );
        //设备号
        $equipmentNumber = self ::getEquipmentNumberArray($data);
        //平台流水号
        $systemNumber = self ::getSequenceNumberArray();
        //消息体
        $messageBody = self ::getMessageBodyArray($data);
        //没有缓存指令
        $ret = array ( 0x00 );
        //数组开始5位和设备号合并
        $arrayStartAndEquipmentNumber = array_merge($arrayStartFiveBytes, $equipmentNumber);
        //接上一步继续与平台流水号合并
        $startEquipmentAndSystemNumber = array_merge($arrayStartAndEquipmentNumber, $systemNumber);
        //接上一步继续与消息体合并
        $startEquipmentSystemAndMessageBody = array_merge($startEquipmentAndSystemNumber, $messageBody);
        //接上一步与ret合并
        $dataAndRet = array_merge($startEquipmentSystemAndMessageBody, $ret);
        //按位异或
        $dataAndRetXor = Auth ::getEveryXor($dataAndRet);
        //数组末尾两位
        $arrayEndTwoBytes = array ( $dataAndRetXor, 0x7e );
        //整个数组
        $completeArray = array_merge($dataAndRet, $arrayEndTwoBytes);
        //发送给客户端的字符串
        $sendClientStr = Auth ::bytesArrayToString($completeArray);

        return $sendClientStr;
    }


}