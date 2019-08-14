YfwHome 介绍
=================
1、本程序基于GatewayWorker开发的物联网控制系统 

YfwHome 版本 
=================
 版本1.0
 
 功能：
 1、设备接入
 2、客户端app接入
 3、设备客户端绑定功能


程序启动与停止
=================
php start.php start  启动程序
php start.php start -d  后台启动程序 
php start.php stop   结束程序







GatewayWorker 介绍
=================

GatewayWorker基于[Workerman](https://github.com/walkor/Workerman)开发的一个项目框架，用于快速开发长连接应用，例如app推送服务端、即时IM服务端、游戏服务端、物联网、智能家居等等。

GatewayWorker使用经典的Gateway和Worker进程模型。Gateway进程负责维持客户端连接，并转发客户端的数据给Worker进程处理；Worker进程负责处理实际的业务逻辑，并将结果推送给对应的客户端。Gateway服务和Worker服务可以分开部署在不同的服务器上，实现分布式集群。

GatewayWorker提供非常方便的API，可以全局广播数据、可以向某个群体广播数据、也可以向某个特定客户端推送数据。配合Workerman的定时器，也可以定时推送数据。

GatewayWorker框架手册
==================================
http://www.workerman.net/gatewaydoc/
