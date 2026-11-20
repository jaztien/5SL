<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require './vendor/autoload.php';

class MyChat implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $onlineusers = array();
    //protected $onlineadmin = array();

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    ////////////////////////////////////////////////
    ////////////// 使用服務時操作 ////////////////////
    ////////////////////////////////////////////////
    public function onOpen(ConnectionInterface $conn) {
        // 限制最大連接數
        $maxConnections = 100; // 設定最大連接數
        if (count($this->clients) >= $maxConnections) {
            $conn->close(); // 超出限制，拒絕連接
            echo "Connection rejected: max connections reached ({$maxConnections})\n";
            return;
        }
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    ////////////////////////////////////////////////
    /////////////////// 監聽內容 ////////////////////
    ////////////////////////////////////////////////
    public function onMessage(ConnectionInterface $from, $msg) {
        $msgObj = json_decode($msg);//將字串轉為課程商品
        if($msgObj->action === 'onChat'){//當 onload 聊天頁面時（無論會員端或是管理端）
            //$msgObj['resourceId'] = $from->resourceId;
            $this->$onlineusers[$msgObj->postType][$msgObj->postID] = $msgObj;//將 unload 的資料併入 online 陣列
            //$this->$onlineusers[$msgObj->postType][$from->resourceId] = $msgObj;//將 unload 的資料併入 online 陣列
            $onlineData = json_encode(array("action"=>"onlineList","adminList"=>$this->$onlineusers[1],"userList"=>$this->$onlineusers[2]));//將 online 資料轉為字串以利監聽傳遞
            foreach ($this->clients as $client) {
                if($from === $client){//online 陣列僅傳送到 onload 本人所在的頁面生成列表（無論會員端或是管理端）
                    $client->send($onlineData);
                } else {//單獨 online 信息原樣傳送到除了本人外的所有在線者 
                    $client->send($msg);
                }
            }
        } else if($msgObj->action === 'outChat'){//當離開應答頁面，或是 onclose 監聽服務時
            unset($this->$onlineusers[$msgObj->postType][$msgObj->postID]);//移除 online 陣列中相關資料
            //unset($this->$onlineusers[$msgObj->postType][$from->resourceId]);//移除 online 陣列中相關資料
            //$sendData = json_encode($msgObj);
            foreach ($this->clients as $client) {//原樣發送到全員
                $client->send($msg);
            }
        } else {//其他監聽項目時（通常僅剩純文字傳遞）
            foreach ($this->clients as $client) {
                if ($from !== $client) {//發送到除了本人外其他所有在線者
                    $client->send($msg);
                }
            }
        }
    }
    ////////////////////////////////////////////////
    ////////////// 停用服務時操作 ////////////////////
    ////////////////////////////////////////////////
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    ////////////////////////////////////////////////
    ////////////// 發生錯誤時操作 ////////////////////
    ////////////////////////////////////////////////
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

}

////////////////////////////////////////////////////////////
///////////// 依設定啟動 WEBSOCKET 伺服器 ////////////////////
////////////////////////////////////////////////////////////
$chatApp = new MyChat();
// 使用 ExtEventLoop 代替 StreamSelectLoop
$loop = \React\EventLoop\Factory::create();
//$loop = new ExtEventLoop();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $chatApp
        )
    ),
    8089,
    '0.0.0.0',
    $loop
);

// 使用定期計時器來清理不活動的連接
/*
$loop->addPeriodicTimer(5, function() use ($chatApp) {
    foreach ($chatApp->clients as $client) {
        if (!$client->isConnected()) {
            $chatApp->clients->detach($client);
        }
    }
    echo "Cleanup inactive connections";
});
*/
$server->run();


?>