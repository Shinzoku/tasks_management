#!/usr/bin/env php
<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\Service\ChatServer;

require __DIR__ . '/../vendor/autoload.php';

// Create the WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080 // Port the server is listening on
);

echo "Serveur WebSocket démarré sur ws://localhost:8080\n";

// Start the server
$server->run();
