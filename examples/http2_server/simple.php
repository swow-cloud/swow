<?php

declare(strict_types=1);

use Swow\Http2\Server;
use Swow\Socket;
use Swow\Http2\Error;

// SSL certificate configuration
$ssl_cert_file = __DIR__ . '/ssl/server.crt';
$ssl_key_file = __DIR__ . '/ssl/server.key';

if (!file_exists($ssl_cert_file) || !file_exists($ssl_key_file)) {
    echo "Please generate SSL certificate and key files first.\n";
    echo "You can use OpenSSL to generate self-signed certificates:\n";
    echo "openssl req -x509 -newkey rsa:2048 -keyout ssl/server.key -out ssl/server.crt -days 365 -nodes\n";
    exit(1);
}

// Create a SSL server socket
$socket = new Socket(Socket::TYPE_TCP);
$socket->enableCrypto();
$socket->setTlsCertificate($ssl_cert_file, $ssl_key_file);
$socket->bind('127.0.0.1', 9764)->listen();

echo "HTTP/2 server listening on https://127.0.0.1:9764\n";

while (true) {
    // Accept client connection
    $connection = $socket->accept();
    
    // Create HTTP/2 server instance
    $server = new Server($connection, [
        // You can customize settings here
        'header_table_size' => 4096,
        'max_concurrent_streams' => 100,
        'initial_window_size' => 65535,
        'max_frame_size' => 16384,
    ]);
    
    // Set request handler
    $server->setRequestHandler(function ($request) use ($server) {
        // Get request information
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        // Simple routing
        if ($path === '/') {
            // Send response
            $server->respond(
                $request->getStreamId(),
                200,
                ['content-type' => 'text/plain'],
                "Hello from Swow HTTP/2 Server!\n" .
                "Method: {$method}\n" .
                "Path: {$path}\n"
            );
        } else {
            // Not found
            $server->respond(
                $request->getStreamId(),
                404,
                ['content-type' => 'text/plain'],
                "404 Not Found\n"
            );
        }
    });
    
    try {
        // Handle the connection
        $server->handle();
    } catch (\Throwable $e) {
        // Handle any errors
        echo "Error: {$e->getMessage()}\n";
    } finally {
        // Close the connection
        $server->close(Error::NO_ERROR);
        $connection->close();
    }
}