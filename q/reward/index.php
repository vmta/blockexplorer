<?php

require "../../include/config.php";


/*
 * Stage 1, get most recent bestblockhash
 */
$data = '{ "jsonrpc": "2.0", "id": "curl", "method": "getblockchaininfo", "params": [] }';

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

$chain_bestblockhash = $obj['result']['bestblockhash'];
//print_r("Best blockhash is: " . $chain_bestblockhash . "<br />");


/*
 * Stage 2, get coinbase transaction
 */
$data = '{ "jsonrpc": "2.0", "id": "curl", "method": "getblock", "params": ["'.$chain_bestblockhash.'"] }';

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

$chain_coinbase_tx = $obj['result']['tx'][0];
//print_r("Coinbase tx: " . $chain_coinbase_tx . "<br />");


/*
 * Stage 3, get sum of all vouts for coinbase to calculate reward
 */
$data = '{ "jsonrpc": "2.0", "id": "curl", "method": "getrawtransaction", "params": ["'.$chain_coinbase_tx.'", true] }';

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

$chain_coinbase_tx_vouts = count($obj['result']['vout']); // Get number of vouts in a transaction

$blockreward = 0;
for ($i = 0; $i <= $chain_coinbase_tx_vouts; $i++) // Loop through vouts and sum up the reward
{
    $blockreward += $obj['result']['vout'][$i]['value'];
}

//print_r("Block reward: " . $blockreward . "<br />");
print_r($blockreward);

?>
