<?php
/**
 * Created by PhpStorm.
 * User: 405848298@qq.com
 * Date: 2021/04/15
 */

namespace infoLightYang\RabbitMq;

class rabbitQueue
{

    /**
     * @var \AMQPConnection
     */
    public static $amqp = null;

    /**
     * @var \AMQPChannel
     */
    public static $channel = null;

    /**
     * @var \AMQPExchange
     */
    public static $exchange = null;


    //交换机名称
    const EXCHANGE_NAME = 'soyou';

    /**
     *  初始化rabbit交换机配置
     */
    public function __construct($exchanneName = self::EXCHANGE_NAME) {
        if (is_null(self::$amqp)) {
            try {
                self::$amqp = new \AMQPConnection(array(
                    'host' => '192.168.138.128',
                    'port' => '5672',
                    'vhost' => '/',
                    'login' => 'admin',
                    'password' => 'admin'
                ));
                self::$amqp->connect();
                self::$channel = new \AMQPChannel(self::$amqp);
                self::$exchange = new \AMQPExchange(self::$channel);
                self::$exchange->setName($exchanneName);
                //设置交换机类型
                //AMQP_EX_TYPE_DIRECT:直连交换机
                //AMQP_EX_TYPE_FANOUT:扇形交换机
                //AMQP_EX_TYPE_HEADERS:头交换机
                //AMQP_EX_TYPE_TOPIC:主题交换机
                self::$exchange->setType(AMQP_EX_TYPE_DIRECT);
                //设置交换机持久
                self::$exchange->setFlags(AMQP_DURABLE);
                self::$exchange->declareExchange();
            } catch (\AMQPException $e) {
                self::$amqp = false;
            }
        }
    }

    /**
     * 生成队列
     * @return \AMQPQueue;
     */
    public function setAMQQueue($Qname,$routeKey){
        $q = new \AMQPQueue(self::$channel);
        //设置队列名称
        $q->setName($Qname);
        //设置队列持久
        $q->setFlags(AMQP_DURABLE);
        //设置显式确认
        $q->setArguments(['noAck'=>false]);
        //声明消息队列
        $q->declareQueue();
        //交换机和队列通过$routingKey进行绑定
        $q->bind(self::$exchange->getName(), $routeKey);

        return $q;
    }

    /**
     * 发布消息到队列
     * @param $msg
     * @param $routingKey
     */
    public function pushlishMsg($msg,$routingKey){
        return self::$exchange->publish(json_encode($msg), $routingKey, AMQP_NOPARAM, array('delivery_mode' => 2));
    }
}
