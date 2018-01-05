<?php

require "../../include/config.php";

$data = '{ "jsonrpc": "2.0", "id": "curl", "method": "getnetworkhashps", "params": [] }';

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $proto."://".$host);
curl_setopt($ch, CURLOPT_PORT, $port);
curl_setopt($ch, CURLOPT_HTTPHEADER, 'content-type: text/plain;');
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $rpcuser.":".$rpcpass);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$result = curl_exec($ch);
$obj = json_decode($result, TRUE);
curl_close($ch);

print_r($obj['result']);

?>
