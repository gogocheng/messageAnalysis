<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2018/9/28
 * Time: 17:26
 */

use Workerman\Worker;
use Workerman\Common\Auth;
use Workerman\Common\GetAboutParameter;
use Workerman\Common\GetPositionMessage;

require 'Autoloader.php';
require_once __DIR__ . '/vendor/autoload.php';

// 创建一个Worker监听2347端口，不使用任何应用层协议
$tcp_worker = new Worker("tcp://127.0.0.1:2400");

// 启动10个进程对外提供服务
$tcp_worker -> count = 10;

$tcp_worker -> onWorkerStart = function ($worker) {
    // 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
    global $db;
    $db = new \Workerman\MySQL\Connection('127.0.0.1', '3306', 'root', 'root', 'monitoring');
};
// 当客户端发来数据时
$tcp_worker -> onMessage = function ($connection, $data) {
    //转成10进制数组
    $data10Array = Auth ::get10Bytes($data);
    //转成16进制
    $data16Array = Auth ::getTo16Bytes($data10Array);
    //发送给客户端
    $sendClientData = GetAboutParameter ::getVerifyNumberArray($data16Array);
    if ($data16Array[1] == "2" && $data16Array[2] == "0") {
        //0200数据入库
        $data0200Array = GetPositionMessage ::getMessageArray($data16Array, 13);
        if (!empty($data0200Array['ship_id'])) {
            // 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
            global $db;
            // 执行SQL
            $sql = "Insert into `cmf_locus` (ship_id,time,latitude,longitude,ns,position,ew,alarm,gps,log) 
                  VALUES (" . "'{$data0200Array['ship_id']}','{$data0200Array['time']}','{$data0200Array['latitude']}','{$data0200Array['longitude']}','{$data0200Array['ns']}','{$data0200Array['position']}','{$data0200Array['ew']}','{$data0200Array['alarm']}','{$data0200Array['gps']}','{$data0200Array['log']}')";
            $data = $db -> query($sql);
            var_dump($data16Array[1] . $data16Array[2] . "||" . $data0200Array['time']);
        }
    } else {
        //0704数据包解析和入库
        $data0704Array = GetPositionMessage ::getMessageArray($data16Array, 18);
        if (!empty($data0704Array['ship_id'])) {
            global $db;
            // 执行SQL
            $sql = "Insert into `cmf_locus` (ship_id,time,latitude,longitude,ns,position,ew,alarm,gps,log) 
                  VALUES ("."'{$data0704Array['ship_id']}','{$data0704Array['time']}','{$data0704Array['latitude']}','{$data0704Array['longitude']}','{$data0704Array['ns']}','{$data0704Array['position']}','{$data0704Array['ew']}','{$data0704Array['alarm']}','{$data0704Array['gps']}','{$data0704Array['log']}')";
            $data = $db -> query($sql);
            var_dump($data16Array[1] . $data16Array[2] . "||" . $data0704Array['time']);
        }

    }

    // 向客户端发送hello $data
    $connection -> send($sendClientData);
};

// 运行worker
Worker ::runAll();