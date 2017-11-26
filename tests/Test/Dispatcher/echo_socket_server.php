<?php

$socketServer = stream_socket_server('tcp://127.0.0.1:16986');
$conn = stream_socket_accept($socketServer);

while ($data = fread($conn, 8196)) {
    fwrite($conn, $data, 8196);
}