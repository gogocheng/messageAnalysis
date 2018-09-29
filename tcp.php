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
$tcp_worker = new Worker("tcp://0.0.0.0:8992");

// 启动10个进程对外提供服务
$tcp_worker -> count = 8;

$tcp_worker -> onWorkerStart = function ($worker) {
    // 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
    global $db;
    $db = new \Workerman\MySQL\Connection('119.23.106.133', '3306', 'root', 'root', 'monitoring');
};
// 当客户端发来数据时
$tcp_worker -> onMessage = function ($connection, $data) {
    //转成10进制数组
    $data10Array = Auth ::get10Bytes($data);
    //转成16进制
    $data16Array = Auth ::getTo16Bytes($data10Array);
    //设备号
    $equipmentNumber = GetPositionMessage ::getEquipmentNumber($data16Array);
    //发送给客户端
    $sendClientData = GetAboutParameter ::getVerifyNumberArray($data16Array);
    // 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
    global $db;
    // 执行SQL
    $data = $db -> query();


    // 向客户端发送hello $data
    $connection -> send($sendClientData);
};

// 运行worker
Worker ::runAll();