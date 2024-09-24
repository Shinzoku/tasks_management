<?php

namespace App\Service;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\JWT\Configuration;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class ChatServer implements MessageComponentInterface
{
    protected $clients;
    protected $entityManager;

    public function __construct() {
        // A collection to manage connected clients
        $this->clients = new \SplObjectStorage;
        // Inject the EntityManager
        $this->entityManager = $entityManager;
    }

    // Method to manage new connections
    public function onOpen(ConnectionInterface $conn) {
        // Stores connection
        $this->clients->attach($conn);

        // Retrieve user from login
        $user = $this->getUserFromConnection($conn);

        // Optional: Inform other clients that a new user has logged in
        $msgToSend = [
        'action' => 'user_joined',
        'user' => [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            ]
        ];
        $this->broadcastMessage($msgToSend, $conn);
    }

    // Broadcasts a received message to all connected clients
    public function onMessage(ConnectionInterface $from, $msg) {
        $msgData = json_decode($msg, true);

        // If new message
        if ($msgData['action'] === 'new_message') {
            $this->handleNewMessage($from, $msgData);
        }

        // If ask edit message
        if ($msgData['action'] === 'edit_message') {
            $this->handleEditMessage($from, $msgData);
        }

        // If ask delete message
        if ($msgData['action'] === 'delete_message') {
            $this->handleDeleteMessage($from, $msgData);
        }
    }

    // Method to handle the new message
    protected function handleNewMessage(ConnectionInterface $from, $msgData) {
        // Create and save the message in the data base
        $message = new Message();
        $message->setContent($msgData['content']);

        // Get user from ID stored in WebSocket session
        $user = $this->getUserFromConnection($from); 
        $message->setUser($user);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Broadcast message to all WebSocket clients
        $msgToSend = [
            'action' => 'new_message',
            'message_id' => $message->getId(),
            'content' => $message->getContent(),
            'user_id' => $user->getId(),
            'updated_at' => $message->getUpdatedAt()->format('Y-m-d H:i:s')
        ];
        $this->broadcastMessage($msgToSend, $from);
    }

    // Method to get the user from connection
    protected function getUserFromConnection(ConnectionInterface $conn) {
        // Get query string from URL
        $querystring = $conn->httpRequest->getUri()->getQuery();

        // Parse query parameters
        parse_str($querystring, $queryParams);

        // Extract the JWT token
        $token = $queryParams['token'] ?? '';

        // Retrieve JWT secret key from .env file
        $jwtSecretKey = $_ENV['JWT_SECRET_KEY'];
    
        // Configure the parser for the JWT
        $config = Configuration::forSymmetricSigner(
            new \Lcobucci\JWT\Signer\Hmac\Sha256(),
            \Lcobucci\JWT\Signer\Key\InMemory::plainText($jwtSecretKey)
        );
    
        try {
            // Analyze the JWT token
            $parsedToken = $config->parser()->parse($token);
            $constraints = $config->validationConstraints();
            $config->validator()->assert($parsedToken, ...$constraints); // Validate the token

            // Retrieve user ID from claims
            $userId = $parsedToken->claims()->get('user_id');

            // Find and return user in database
            return $this->entityManager->getRepository(User::class)->find($userId);
        } catch (\Exception $e) {
            // Return null on error (invalid token or other)
            return null;
        }
    }

    // Method to handle the edit message
    protected function handleEditMessage(ConnectionInterface $from, $msgData) {
        $messageId = $msgData['message_id'];
        $newContent = $msgData['new_content'];
    
        // Retrieve the message to edit
        $message = $this->entityManager->getRepository(Message::class)->find($messageId);
        
        // Check that the message exists and that the user has the right to edit it
        if ($message && $this->canEdit($from, $message)) {
            $message->setContent($newContent);
            $this->entityManager->flush();
    
            // Broadcast the modified message to all WebSocket clients
            $msgToSend = [
                'action' => 'edit_message',
                'message_id' => $message->getId(),
                'new_content' => $message->getContent()
            ];
            $this->broadcastMessage($msgToSend, $from);
        } else {
            // Handle the case where the message does not exist or the user does not have permissions
            $from->send(json_encode(['error' => 'Message not found or permission denied']));
        }
    }
    
    // Method to handle the delete message
    protected function handleDeleteMessage(ConnectionInterface $from, $msgData) {
        $messageId = $msgData['message_id'];
    
        // Retrieve the message to delete
        $message = $this->entityManager->getRepository(Message::class)->find($messageId);
    
        // Check that the message exists and that the user has the right to delete it
        if ($message && $this->canDelete($from, $message)) {
            $this->entityManager->remove($message);
            $this->entityManager->flush();
    
            // Broadcast the deleted message to all WebSocket clients
            $msgToSend = [
                'action' => 'delete_message',
                'message_id' => $messageId
            ];
            $this->broadcastMessage($msgToSend, $from);
        } else {
            // Handle the case where the message does not exist or the user does not have permissions
            $from->send(json_encode(['error' => 'Message not found or permission denied']));
        }
    }

    // Method to broadcast the message
    protected function broadcastMessage(array $msg, ConnectionInterface $from) {
        foreach ($this->clients as $client) {
            $client->send(json_encode($msg));
        }
    }

    // Method to check if user can edit message
    protected function canEdit(ConnectionInterface $from, Message $message) {
        // Checks if the user is the author of the message or if he has the admin role
        return $message->getUser()->getId() === $this->getUserFromConnection($from)->getId() || $this->isAdmin($from);
    }

    // Method to check if user can delete message
    protected function canDelete(ConnectionInterface $from, Message $message) {
        // Only checks if the user has the admin role
        return $this->isAdmin($from);
    }

    // Method to check if user has admin role
    protected function isAdmin(ConnectionInterface $from) {
        // Retrieve logged in user
        $user = $this->getUserFromConnection($from);
        return in_array('ROLE_ADMIN', $user->getRoles());
    }

    // Method to remove a client when the connection is closed
    public function onClose(ConnectionInterface $conn) {
        // Remove the client from the list of connected clients
        if (isset($this->clients[$conn->resourceId])) {
            unset($this->clients[$conn->resourceId]);
        }

        // Retrieve user from login
        $user = $this->getUserFromConnection($conn);
    
        // Optional: Inform other clients that someone has disconnected
        $msgToSend = [
            'action' => 'user_left',
            'user' => [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
            ]
        ];
        $this->broadcastMessage($msgToSend, $conn);
    }

    // Method to handle the errors
    public function onError(ConnectionInterface $conn, \Exception $e) {
        // Log error
        error_log("An error has occurred: {$e->getMessage()}");

        // Optional: Inform the client of the error
        $conn->send(json_encode(['error' => 'An error occurred. Please try again later.']));

        // Close connection on error
        $conn->close();
    }
}