<?php
require_once __DIR__ . "/waitgroup.php";

go(function () {
$server = new Co\Http\Server("0.0.0.0", 9502, false);
$wgg = new WaitGroup();
echo 11111;
$server->handle('/websocket', function ($request, $ws)use(&$wgg) {
    $ws->upgrade();
    while (true) {
        $frame = $ws->recv();
		echo $frame;
        if ($frame === false) {
            echo "error : " . swoole_last_error() . "\n";
            break;
        } else if ($frame == '') {
            break;
        } else {
			$wgg->add();
			go(function()use($ws,&$wgg,$frame){
				$wgg->done('协程1完成');
				$ws->push("Hello {$frame->data}!");
				$ws->push("How are you, {$frame->data}?");
			});
			
			//$wgg->wait();
			echo $wgg->count;
            
        }
    }
});
$server->start();
});

