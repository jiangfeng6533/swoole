# Demo说明

&ensp;&ensp;server文件主要保存在使用swoole过程记录与测试的demo，readme.md会主要介绍各个demo文件的意义。

##websocke Demo 

####&ensp;&ensp;&ensp;支持功能：
&ensp;&ensp;&ensp;&ensp;`imstart.php`入口文件与`websocketserver.php、waitgroup.php`服务器类与进程通讯分组

* websocket通讯 
    * open 握手连接
    * message 消息监听
    * close 关闭连接
* udp通讯
    * Packet 接收数据
* tcp demo中暂未测试
* http交互
    * request消息交互接收
* redis队列使用 

###rabbitmq Demo

####&ensp;&ensp;支持功能
&ensp;&ensp;&ensp;&ensp;`pushrabbitmq.php`生产者、`getrabbitmq.php`消费者

* 连接生成队列
* publish 发送
* 连接设置交换机选定对垒
* consume 获取消息




* 邮件(jiangfeng6533#163.com, 把#换成@)
* QQ: 624804922
* weChat: look10086


##感激
感谢以下的成员,排名不分先后

* 博哥（提供rabbitmq生产消费代码） 
* 延君（提供rabbitmq服务器）

##关于作者

```javascript
  var ihubo = {
    nickName  : "核桃",
    site : "http://adyy.net"
  }
```
