# Workerman解析JT808协议

## **数据类型**

|数据类型|描述及要求|
|:---:|:---:|
|BYTE|无符号单字节整形（字节， 8 位）|
|WORD|无符号双字节整形（字， 16 位）|
|DWORD|无符号四字节整形（双字， 32 位）|
|BYTE[n]|n 字节|
|BCD[n]|8421 码， n 字节|
|STRING|GBK 编码，若无数据，置空|

## **消息结构**

|标识位|消息头|消息体|校验码|标识位|
|:---:|:---:|:---:|:---:|:---:|
|1byte(0x7e)|16byte||1byte|1byte(0x7e)|


## **消息头**

~~~
消息ID(0-1)   消息体属性(2-3)  终端手机号(4-9)  消息流水号(10-11)    消息包封装项(12-15)

byte[0-1]   消息ID word(16)
byte[2-3]   消息体属性 word(16)
        bit[0-9]    消息体长度
        bit[10-12]  数据加密方式
                        此三位都为 0，表示消息体不加密
                        第 10 位为 1，表示消息体经过 RSA 算法加密
                        其它保留
        bit[13]     分包
                        1：消息体卫长消息，进行分包发送处理，具体分包信息由消息包封装项决定
                        0：则消息头中无消息包封装项字段
        bit[14-15]  保留
byte[4-9]   终端手机号或设备ID bcd[6]
        根据安装后终端自身的手机号转换
        手机号不足12 位，则在前面补 0
byte[10-11]     消息流水号 word(16)
        按发送顺序从 0 开始循环累加
byte[12-15]     消息包封装项
        byte[0-1]   消息包总数(word(16))
                        该消息分包后得总包数
        byte[2-3]   包序号(word(16))
                        从 1 开始
        如果消息体属性中相关标识位确定消息分包处理,则该项有内容
        否则无该项
~~~




## **消息体**
### **消息体工具类库**
~~~
Workerman/Common/Auth;
~~~
### **解析数据发送客户端数据类库**
~~~
Workerman/Common/GetAboutParmeter;
~~~
### **解析客户端位置数据类库**
~~~
Workerman/Common/GetPositionMessage;
~~~

