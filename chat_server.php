<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require 'vendor/autoload.php';

class ChatSignal implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "🚀 WebSocket Server đang chạy tại cổng 8080...\n";
        echo "Đang chờ kết nối từ trình duyệt...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Nhận tín hiệu có tin nhắn mới và phát (Broadcast) cho tất cả những người khác
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}

$server = IoServer::factory(new HttpServer(new WsServer(new ChatSignal())), 8080);
$server->run();
?>