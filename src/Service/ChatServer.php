<?php

namespace App\Service;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class ChatServer implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {
        // A collection to manage connected clients
        $this->clients = new \SplObjectStorage;
    }

    // Manage new connections
    public function onOpen(ConnectionInterface $conn) {
        // Add the new customer to the collection
        $this->clients->attach($conn);
        echo "Nouvelle connexion : {$conn->resourceId}\n";
    }

    // Broadcasts a received message to all connected clients
    public function onMessage(ConnectionInterface $from, $msg) {
        // Broadcast the message to all connected clients
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    // Remove a client when the connection is closed
    public function onClose(ConnectionInterface $conn) {
        // Remove client from collection
        $this->clients->detach($conn);
        echo "Connexion fermée : {$conn->resourceId}\n";
    }

    // Handle errors
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erreur : {$e->getMessage()}\n";
        $conn->close();
    }
}