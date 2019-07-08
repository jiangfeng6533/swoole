<?php
class GetMqMessage 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get mq message';

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

    private $exChange;

    private $connect;

    private $channel;

    private $exChangeName;

	private $conn_args = array( 
		'host' => '148.70.13.164',  
		'port' => '5672',  
		'login' => 'mq',  
		'password' => '123456', 
		'vhost'=>'/' 
	); 

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    public function getConnectToMq(){
        //建立mq连接
        $this->connect = new \AMQPConnection($this->conn_args);
        $this->connect->connect() or die('Unable to MQ');
        //创建频道
        $this->channel = new \AMQPChannel($this->connect);
        //生成交换机名称
        $this->exChangeName = "task.utalk.com";
        $this->exChange = new \AMQPExchange($this->channel);
        $this->exChange->setName($this->exChangeName);
        $this->exChange->setType(AMQP_EX_TYPE_TOPIC);
        $this->exChange->setFlags(AMQP_DURABLE);
        $this->exChange->declareExchange();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getConnectToMq();
        //回调单一队列中
        $queue = new \AMQPQueue($this->channel);
        $queue->setName('sms.Utalk');
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();
        $queue->bind($this->exChangeName,'*.*');

		while (true){
            $queue->consume(function ($envelope,$queue){
                go(function ()use($envelope,$queue){
                    $message = $envelope->getBody();
					$queue->ack($envelope->getDeliveryTag());
					echo $message;
					echo date('H:i:s',time());
					echo PHP_EOL;
					
                });
            });
        }
				
				
       
    }
	public function gotask($envelope,$queue){
		go(function ()use($envelope,$queue){
                    $message = $envelope->getBody();
//					co::sleep(.2);
					echo $message.PHP_EOL;
                    $queue->ack($envelope->getDeliveryTag());
	echo "完成".date('H:i:s',time()).PHP_EOL;                    
        });
	}

    function _request($curl,$https=true,$method='get',$data=null)
    {
        $ch=curl_init(); //初始化
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_HEADER,false);//设置不需要头信息
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);//获取页面内容，但不输出
        if($https)
        {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);//不做服务器认证
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//不做客户端认证
        }

        if($method=='post')
        {
            curl_setopt($ch, CURLOPT_POST,true);//设置请求是post方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置post请求数据

        }

        $str=curl_exec($ch);//执行访问
        curl_close($ch);//关闭curl，释放资源
        return $str;
    }

}


$mq = new GetMqMessage();
$mq->handle();

