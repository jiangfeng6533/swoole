<?php

class RabbitMqJob 
{
    private $conn_args = array( 
		'host' => '148.70.13.164',  
		'port' => '5672',  
		'login' => 'mq',  
		'password' => '123456', 
		'vhost'=>'/' 
	);

    //模拟数据类型
    private $messageType = [
        'sms' => [
            'route_key' => 'sms.Utalk',
            'queue' => null,
        ],
        'call' => [
            'route_key' => 'call.Utalk',
            'queue' => null,
        ],
        'voice' => [
            'route_key' => 'voice.Utalk',
            'queue' => null,
        ],
    ];
    private $message = [];

    private $exChange;

    private $connect;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message)
    {
	echo "ll\n";
        $this->message = $message;
    }

    public function getConnectToMq(){
        //建立mq连接
        $this->connect = new \AMQPConnection($this->conn_args);
        $this->connect->connect() or die('Unable to MQ');
        //创建频道
        $channel = new \AMQPChannel($this->connect);
        //生成交换机名称
        $exChangeName = "task.utalk.com";
        $this->exChange = new \AMQPExchange($channel);
        $this->exChange->setName($exChangeName);
        $this->exChange->setType(AMQP_EX_TYPE_TOPIC);
        $this->exChange->setFlags(AMQP_DURABLE);
        $this->exChange->declareExchange();
        //生成不同的队列
        foreach($this->messageType as $key => $routeKey){
            $this->messageType[$key]['queue'] = new \AMQPQueue($channel);
            $this->messageType[$key]['queue']->setName($routeKey['route_key']);
            $this->messageType[$key]['queue']->setFlags(AMQP_DURABLE);
            $this->messageType[$key]['queue']->declareQueue();
            $this->messageType[$key]['queue']->bind($exChangeName,$routeKey['route_key']);
        }
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getConnectToMq();
        //发送消息
        foreach($this->message as $message){
            $messageType = $this->messageType[$message['type']];
			$this->exChange->publish(json_encode($message,JSON_UNESCAPED_UNICODE),$messageType['route_key']);
            // for($i=0;$i<=1000;$i++){
				// echo "ok";
                // 
            // }
            //$this->exChange->publish(json_encode($message,JSON_UNESCAPED_UNICODE),$messageType['route_key']);
        }
        $this->connect->disconnect();
    }
}


$message = [
       
		
    ];
	for($i=0;$i<1000;$i++){
		$message[] = [
            'group_id' => 1,
            'phone' => 'c'.$i,
            'type' => 'sms',
            'message' => 'test sms again'
        ];
	}
	
	
$rabbit = new RabbitMqJob($message);
$rabbit->handle();

