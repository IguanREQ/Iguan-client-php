<?php
const MODE_ALRIGHT = 1;
const MODE_INVALID = 2;
const MODE_NO_ANSWER = 3;

$port = $argv[1];
$mode = (int)$argv[2];

$socketServer = stream_socket_server('tcp://127.0.0.1:' . $port);
$conn = stream_socket_accept($socketServer);

$data = fgets($conn);

//skip auth byte
$data = json_decode(substr($data, MODE_ALRIGHT));

$id = $data->id;

if ($mode === MODE_ALRIGHT) {
    $writeData = json_encode(['id' => $id, 'data' => ['empty']]) . "\n";
} else if ($mode === MODE_INVALID) {
    $writeData = json_encode(['id' => 'invalid_value', 'data' => ['empty']]) . "\n";;
} else {
    $writeData = '';
}

if ($mode !== MODE_NO_ANSWER) {
    fwrite($conn, $writeData, strlen($writeData));
} else {
    sleep(60);
}
