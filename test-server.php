<?php
// save as test-server.php in the SAME directory as your cert and key
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/vendor/autoload.php';

$cert = __DIR__ . '/docker/certs/eventsourcerer.pem';
$key  = __DIR__ . '/docker/certs/eventsourcerer-key.pem';

echo "Cert exists: "    . (file_exists($cert) ? 'YES' : 'NO') . "\n";
echo "Key exists: "     . (file_exists($key)  ? 'YES' : 'NO') . "\n";
echo "Cert readable: "  . (is_readable($cert) ? 'YES' : 'NO') . "\n";
echo "Key readable: "   . (is_readable($key)  ? 'YES' : 'NO') . "\n";

$server = new React\Socket\SocketServer('tls://127.0.0.1:1985', [
    'tls' => [
        'local_cert'        => $cert,
        'local_pk'          => $key,
        'allow_self_signed' => true,
        'verify_peer'       => false,
    ],
]);

$server->on('connection', function (React\Socket\ConnectionInterface $conn) {
    echo "Connected: " . $conn->getRemoteAddress() . "\n";
    $conn->write("Hello!\n");
});

$server->on('error', function (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
});

echo "Running on tls://127.0.0.1:1985\n";

React\EventLoop\Loop::run();
