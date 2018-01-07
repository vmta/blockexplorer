<?php


/*
 * Api class for Umkoin (SHA256) crypto currency
 * 
 * Author:  vmta
 * Date:    5 Jan 2018
 * 
 * Version_Major: 0
 * Version_Minor: 0
 * Version_Build: 1
 * 
 * Changelog:
 * 
 */


class Api {

  /*
   * $server variable of type string shall be of form:
   *     protocol :// host_address : port_number
   *
   */
  private $server;


  /*
   * $auth variable of type string shall be of form:
   *     rpcuser : rpcpass
   *
   */
  private $auth;


  /*
   * $args variable of type array will keep some handy
   * initial settings for JSON RPC call
   *
   * Array keys:
   *     jsonrpc
   *     id
   *     method
   *     params
   *
   */
  private $args;


  /*
   * Class constructor.
   *
   * Without arguments, assume that underlying daemon
   * is running with default config, i.e. has protocol
   * "http", on "localhost" and with rpc port 6332.
   *
   */
  public function __construct($server = "http://127.0.0.1:6332", $auth = "rpcuser:rpcpass") {
    $this->server = $server;
    $this->auth = $auth;
    $this->args = [ "jsonrpc" => "2.0", "id" => "curl", "method" => "", "params" => [] ];
  }


  /*
   * API call wrapper accepting single parameter of type array.
   *
   * Set CURL object packed with necessary options.
   * Perform curl exec on the CURL object.
   * Check and report on error.
   * Return decoded result on success.
   *
   */
  private function call($req) {

    $data = json_encode($req);

    static $ch = null;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $this->server);
    curl_setopt($ch, CURLOPT_HTTPHEADER, 'content-type: text/plain;');
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $this->auth);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $res = curl_exec($ch);
    if ($res === false) $this->throw(new Exception('Could not get reply: ' . curl_error($ch)));

    $obj = json_decode($res, TRUE);
    if (!isset($obj['result'])) {
      print_r("API call to '" . $req['method'] . "' returned ");
      if (isset($obj['error'])) {
        print_r("Error(" . $obj['error']['code'] . "): " . $obj['error']['message'] . PHP_EOL);
      } else {
        print_r("Unknown Error: " . $obj . PHP_EOL);
      }
      return false;
    }

    curl_close($ch);
    return $obj;
  }


  ////////////////////////////////////////
  //             BLOCKCHAIN             //
  ////////////////////////////////////////


  /* 1
   * getbestblockhash
   *
   * Returns stripped JSON object, a string indicating recent block hash.
   *
     Input params:
     {
     }

     Output:
     {
       "result": "0000000000181e0f3939ba5960e55ef853f9526348c9f3e51661fcaa1ad1d978"
     }
   *
   */
  public function getbestblockhash() {

    $args = $this->args;
    $args["method"] = "getbestblockhash";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 2
   * getblock "blockhash"
   *
   * Returns JSON object with various block data.
   *
     Input params:
     {
       "params": ["00000000470b9e0dd4f6fb72c93e0c655f68069899a5b2a0b4e413ef8006469a"]
     }

     Output:
     {
       "hash": "00000000470b9e0dd4f6fb72c93e0c655f68069899a5b2a0b4e413ef8006469a",
       "confirmations": 2472,
       "strippedsize": 248,
       "size": 248,
       "weight": 992,
       "height": 0,
       "version": 1,
       "versionHex": "00000001",
       "merkleroot": "550ccf92f28cb76c3f8ccd5073a0175182ac8b03abe96d6b18da9b46f2e2941d",
       "tx": [
         "550ccf92f28cb76c3f8ccd5073a0175182ac8b03abe96d6b18da9b46f2e2941d"
       ],
       "time": 1511563812,
       "mediantime": 1511563812,
       "nonce": 4263252653,
       "bits": "1d00ffff",
       "difficulty": 1,
       "chainwork": "0000000000000000000000000000000000000000000000000000000100010001",
       "nextblockhash": "0000000053d3d3b583afbe4751ca900a4facdfbe4c12f03c1a96d17889e84b3e"
     }
   *
   */
  public function getblock($blockhash = "00000000470b9e0dd4f6fb72c93e0c655f68069899a5b2a0b4e413ef8006469a") {

    $args = $this->args;
    $args["method"] = "getblock";
    $args["params"] = ["$blockhash"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 3
   * getblockchaininfo
   *
   * Returns JSON object with various block data.
   *
     Input params:
     {
     }

     Output:
     {
       "chain": "main",
       "blocks": 2475,
       "headers": 2475,
       "bestblockhash": "000000000020f2b513d7b01b5f28ad4e52f156009e5eaa26ed50aac1c5a4e8ae",
       "difficulty": 1091.992009334139,
       "mediantime": 1514982385,
       "verificationprogress": 0.9691356004320361,
       "initialblockdownload": false,
       "chainwork": "00000000000000000000000000000000000000000000000000089e283daffdd4",
       "size_on_disk": 1472010,
       "pruned": false,
       "softforks": [
         {
           "id": "bip34",
           "version": 2,
           "reject": {
             "status": true
           }
         },
         {
           "id": "bip66",
           "version": 3,
           "reject": {
             "status": true
           }
         },
         {
           "id": "bip65",
           "version": 4,
           "reject": {
             "status": true
           }
         }
       ],
       "bip9_softforks": {
         "csv": {
           "status": "active",
           "startTime": 1511563812,
           "timeout": 1514112600,
           "since": 432
         },
         "segwit": {
           "status": "active",
           "startTime": 1511563812,
           "timeout": 1514112600,
           "since": 432
         }
       },
       "warnings": ""
     }
   *
   */
  public function getblockchaininfo() {

    $args = $this->args;
    $args["method"] = "getblockchaininfo";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 4
   * getblockcount
   *
   * Returns stripped JSON object, an int indicating current chain height.
   *
     Input:
     {
     }

     Output:
     {
       "result": 12345
     }
   *
   */
  public function getblockcount() {

    $args = $this->args;
    $args["method"] = "getblockcount";

    $res = $this->call($args);
      if ($res)
        return $res['result'];
  }


  /* 5
   * getblockhash height
   *
   * Returns stripped JSON object, a string indicating blockhash by
   * its height.
   *
     Input:
     {
       "params": [1325]
     }

     Output:
     {
       "result": "0000000000054c0daefd9191bb8fe669f7c325cafe3c9702abaf9e9ab4266b90"
     }
   *
   */
  public function getblockhash($height = 0) {

    $args = $this->args;
    $args["method"] = "getblockhash";
    $args["params"] = [$height];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 6
   * getblockheader "blockhash"
   *
   * Returns JSON object with various block header data.
   *
     Input:
     {
       "params": ["000000000020f2b513d7b01b5f28ad4e52f156009e5eaa26ed50aac1c5a4e8ae"]
     }

     Output:
     {
       "hash": "000000000020f2b513d7b01b5f28ad4e52f156009e5eaa26ed50aac1c5a4e8ae",
       "confirmations": 1,
       "height": 2475,
       "version": 536870912,
       "versionHex": "20000000",
       "merkleroot": "c86843418f915eb6e3d9c9ff390551d1068923564f378e0f2e778cf72230bf31",
       "time": 1514986783,
       "mediantime": 1514982385,
       "nonce": 2029006921,
       "bits": "1b3c03a1",
       "difficulty": 1091.992009334139,
       "chainwork": "00000000000000000000000000000000000000000000000000089e283daffdd4",
       "previousblockhash": "000000000018d3aa573b5384459d50ef709b04294ab8d1940bed802f17e2b554"
     }
   *
   */
  public function getblockheader($hash) {

    $args = $this->args;
    $args["method"] = "getblockheader";
    $args["params"] = ["$hash"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 7
   * getchaintips
   *
   * Returns JSON object.
   *
     Input:
     {
     }

     Output:
     [
       {
         "height": 2475,
         "hash": "000000000020f2b513d7b01b5f28ad4e52f156009e5eaa26ed50aac1c5a4e8ae",
         "branchlen": 0,
         "status": "active"
       }
     ]
   *
   */
  public function getchaintips() {

    $args = $this->args;
    $args["method"] = "getchaintips";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 8
   * getchaintxstats nblocks
   *
   * Returns JSON object.
   *
     Input:
     {
       "params": [2605]
     }

     Output:
     {
       "time": 1515018984,
       "txcount": 3021,
       "window_block_count": 2523,
       "window_tx_count": 3018,
       "window_interval": 1325174,
       "txrate": 0.002277436774340577
     }
   *
   */
  public function getchaintxstats($nblocks = 0) {

    $args = $this->args;
    $args["method"] = "getchaintxstats";
    $args["params"] = [$nblocks];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 9
   * getdifficulty
   * 
   * Returns stripped JSON object, a float indicating current difficulty.
   *
     Input:
     {
     }

     Output:
     {
       "result": 1091.992009334139
     }
   *
   */
  public function getdifficulty() {

    $args = $this->args;
    $args["method"] = "getdifficulty";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 10
   * getmempoolancestors "txid"
   * 
   * Returns JSON object, array of ancestor transaction hashes.
   * 
     Input:
     {
       "params": "77afbfb7f3e0c017cc879d9aed5f5e77fe7d42f2ec360b251bbf4b3bf5a9e840"
     }

     Output:
     [
       "f0b0138f08ff67674736e46d9b01be723b13f1cdac00fe8c1d4cf9b2b5e903a1"
     ]
   *
   */
  public function getmempoolancestors($txid = 0) {

    $args = $this->args;
    $args["method"] = "getmempoolancestors";
    $args["params"] = ["$txid"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 11
   * getmempooldescendants "txid"
   * 
   * Returns JSON object, array of descending transaction hashes.
   * 
     Input:
     {
       "param": "77afbfb7f3e0c017cc879d9aed5f5e77fe7d42f2ec360b251bbf4b3bf5a9e840"
     }

     Output:
     [
       "d9788638b675a5468ddb4e0f010730b3616f1363d7872d00cf4654dad178b12f",
       "6ce4296c9fac98bc74689128b00bdce614655bbad7bb10df2e4cc363eee4399f"
     ]
   *
   */
  public function getmempooldescendants($txid = 0) {

    $args = $this->args;
    $args["method"] = "getmempooldescendants";
    $args["params"] = ["$txid"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 12
   * getmempoolentry "txid"
   * 
   * Returns JSON object.
   * 
     Input:
     {
       "params": "1ea520afe55fb37f35caf82f5be0f664c87d1b14ab21cb386b85bcf9d4880292"
     }

     Output:
     {
       "size": 226,
       "fee": 0.00004593,
       "modifiedfee": 0.00004593,
       "time": 1515075217,
       "height": 2633,
       "descendantcount": 1,
       "descendantsize": 226,
       "descendantfees": 4593,
       "ancestorcount": 1,
       "ancestorsize": 226,
       "ancestorfees": 4593,
       "wtxid": "1ea520afe55fb37f35caf82f5be0f664c87d1b14ab21cb386b85bcf9d4880292",
       "depends": [
       ]
     }
   *
   */
  public function getmempoolentry($txid = 0) {

    if ( empty($txid) ) $txid = $this->getrawmempool[0];

    $args = $this->args;
    $args["method"] = "getmempoolentry";
    $args["params"] = ["$txid"];

    $res = $this->call($args);
    if($res)
      return $res['result'];
  }


  /* 13
   * getmempoolinfo
   *
   * Returns JSON object containing info on mempool state
   * 
     Input:
     {
     }

     Output:
     {
       "size": 0,
       "bytes": 0,
       "usage": 96,
       "maxmempool": 300000000,
       "mempoolminfee": 0.00001000,
       "minrelaytxfee": 0.00001000
     }
   *
   */
  public function getmempoolinfo() {

    $args = $this->args;
    $args["method"] = "getmempoolinfo";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 14
   * getrawmempool
   * 
   * Returns JSON object, an array of transactions
   * in a mempool.
   * 
     Input:
     {
     }

     Output:
     [
       "10a238f129d07f5b5ef4c34e1ecc0ef6b0fb7406d6489bf724b013b05110b3ac",
       "4d5a2418c1f39b154eb9a77d740309f274441846648d882d6c6b7152888b0871",
       "4f4b6da3dddb26911aeb2c1333fd5b8d09d862ce1eb82c5e6cc820d983b1041e"
     ]
   * 
   */
  public function getrawmempool() {

    $args = $this->args;
    $args["method"] = "getrawmempool";

    $res = $this->call($args);
    if($res)
      return $res['result'];
  }


  /* 15
   * gettxout "txid" vout
   *
   * Returns JSON object.
   * 
     Input:
     {
       "params": ["txid", vout]
     }

     Output:
     {
       "bestblock": "00000000001cacf99c1a8e57fe0af51fbf5b4286066209f265f23ab027648540",
       "confirmations": 0,
       "value": 5.00000000,
       "scriptPubKey": {
         "asm": "OP_DUP OP_HASH160 9e1502512566ef23183857c3e516d132b765514e OP_EQUALVERIFY OP_CHECKSIG",
         "hex": "76a9149e1502512566ef23183857c3e516d132b765514e88ac",
         "reqSigs": 1,
         "type": "pubkeyhash",
         "addresses": [
           "1FQrw3XoXmXD1aG9JB4kdUHJuhyHwxhNKT"
         ]
       },
       "coinbase": false
     }
   *
   */
  public function gettxout($txid, $vout = 0) {

    $args = $this->args;
    $args["method"] = "gettxout";
    $args["params"] = ["$txid", $vout];

    $res = $this->call($args);
    if($res)
      return $res['result'];
  }


  /* 16
   * gettxoutproof "txid" ("blockhash")
   * 
   * Returns a hex-encoded proof that "txid" was included in a block.
   * 
     Input:
     {
       "params": ["txid", ("blockhash")]
     }

     Output:
     {
       NOTE: By default this function only works sometimes.
     }
   *
   */
  public function gettxoutproof($txid, $hash) {

    $args = $this->args;
    $args["method"] = "gettxoutproof";
    $args["params"] = ["$txid"];

    if (!empty($hash))
      $args["params"][] = "$hash";
    
    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 17
   * gettxoutsetinfo
   *
   * Returns JSON object.
   * 
     Input:
     {
     }

     Output:
     {
       "height": 2605,
       "bestblock": "00000000002fe74295a7bbbd858fd031b39960ca96facbe8af97e93b9b2bf000",
       "transactions": 2773,
       "txouts": 2883,
       "bogosize": 216225,
       "hash_serialized_2": "877feb1af9940248014e3f803a09f05bf472fb42fbc4b5fd9e92e54ce1e17bad",
       "disk_size": 151486,
       "total_amount": 130250.00000000
     }
   *
   */
  public function gettxoutsetinfo() {

    $args = $this->args;
    $args["method"] = "gettxoutsetinfo";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 18
   *
   * Returns
   *
   */
  public function preciousblock($hash) {
    $args = $this->args;
  }


  /* 19
   * pruneblockchain height
   *
   * Returns height of the last block pruned.
   *
     Input:
     {
       "params": 1000
     }

     Output:
     {

     }
   * 
   */
  public function pruneblockchain($height = 0) {

    $args = $this->args;
    $args["method"] = "pruneblockchain";
    $args["params"] = [$height];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 20
   * verifychain
   * 
   * Returns boolean value indicating whether chain is fine.
   * 
     Input:
     {
     }

     Output:
     {
       true
     }
   *
   */
  public function verifychain() {

    $args = $this->args;
    $args["method"] = "verifychain";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 21
   *
   * Returns
   *
   */
  public function verifytxoutproof($proof) {
    $args = $this->args;
  }


  ////////////////////////////////////////
  //              CONTROL               //
  ////////////////////////////////////////


  /* 22
   * getmemoryinfo
   * 
   * Returns JSON object indicating memory information.
   * 
     Input:
     {
     }

     Output:
     {
       "locked": {
         "used": 65856,
         "free": 261824,
         "total": 327680,
         "locked": 65536,
         "chunks_used": 2058,
         "chunks_free": 3
       }
     }

   *
   */
  public function getmemoryinfo() {

    $args = $this->args;
    $args["method"] = "getmemoryinfo";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 23
   *
   * Returns
   *
   */
  public function help($command) {
    $args = $this->args;
  }


  /* 24
   * stop
   * 
   * Stops the underlying daemon. (You're warned!)
   *
   */
  public function stop() {

    $args = $this->args;
    $args["method"] = "stop";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 25
   * uptime
   * 
   * Returns stripped JSON object, an int in seconds since daemon last start.
   * 
     Input:
     {
     }
     
     Output:
     {
       131427
     }
   *
   */
  public function uptime() {

    $args = $this->args;
    $args["method"] = "uptime";

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  ////////////////////////////////////////
  //              GENERATE              //
  ////////////////////////////////////////


  /* 26
   *
   * Returns
   *
   */
  public function generate($nblocks = 1, $maxtries = 1000) {
    $args = $this->args;
  }


  /* 27
   *
   * Returns
   *
   */
  public function generatetoaddress($nblocks = 1, $address, $maxtries = 1000) {
    $args = $this->args;
  }


  ////////////////////////////////////////
  //                MINING              //
  ////////////////////////////////////////


  /* 28
   *
   * Returns
   *
   */
  public function getblocktemplate() {
    $args = $this->args;
  }


  /* 29
   *
   * Returns
   *
   */
  public function getmininginfo() {
    $args = $this->args;
  }


  /* 30
   * getnetworkhashps height
   * 
   * Returns stripped JSON object, a float indicating current hashing power.
   *
     Input:
     {
       "params": 1234
     }

     Output:
     {
       "result": 8481777537.985468
     }
   *
   */
  public function getnetworkhashps($height = 0) {

    $args = $this->args;
    $args["method"] = "getnetworkhashps";
    $args["params"] = [$height];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 31
   *
   * Returns
   *
   */
  public function prioritisetransaction($txid, $dummyvalue, $feedelta) {
    $args = $this->args;
  }


  /* 32
   *
   * Returns
   *
   */
  public function submitblock($hexdata) {
    $args = $this->args;
  }


  ////////////////////////////////////////
  //               NETWORK              //
  ////////////////////////////////////////


  /* 33
   *
   * Returns
   *
   */
  public function addnode($node, $cmd) {
    // $cmd may be one of add|remove|onetry
    $args = $this->args;
  }


  /* 34
   *
   * Returns
   *
   */
  public function clearbanned() {
    $args = $this->args;
  }


  /* 35
   *
   * Returns
   *
   */
  public function disconnectnode($address, $nodeid) {
    $args = $this->args;
  }


  /* 36
   *
   * Returns
   *
   */
  public function getaddednodeinfo($node) {
    $args = $this->args;
  }


  /* 37
   *
   * Returns
   *
   */
  public function getconnectioncount() {
    $args = $this->args;
  }


  /* 38
   *
   * Returns
   *
   */
  public function getnettotals() {
    $args = $this->args;
  }


  /* 39
   *
   * Returns
   *
   */
  public function getnetworkinfo() {
    $args = $this->args;
  }


  /* 40
   *
   * Returns
   *
   */
  public function getpeerinfo() {
    $args = $this->args;
  }


  /* 41
   *
   * Returns
   *
   */
  public function listbanned() {
    $args = $this->args;
  }


  /* 42
   *
   * Returns
   *
   */
  public function ping() {
    $args = $this->args;
  }


  /* 43
   *
   * Returns
   *
   */
  public function setban($subnet, $cmd, $bantime, $absolute) {
    $args = $this->args;
  }


  /* 44
   *
   * Returns
   *
   */
  public function setnetworkactive($flag) {
    // $flag may be one of true|false
    $args = $this->args;
  }


  ////////////////////////////////////////
  //            RAWTRANSACTIONS         //
  ////////////////////////////////////////


  /* 45
   *
   * Returns
   *
   */
  public function combinerawtransaction($hexstring) {
    $args = $this->args;
  }


  /* 46
   *
   * Returns
   *
   */
  public function createrawtransaction() {
    $args = $this->args;
  }


  /* 47
   *
   * Returns
   *
   */
  public function decoderawtransaction($hexstring) {
    $args = $this->args;
  }


  /* 48
   *
   * Returns
   *
   */
  public function decodescript($hexstring) {
    $args = $this->args;
  }


  /* 49
   *
   * Returns
   *
   */
  public function fundrawtransaction($hexstring, $options) {
    $args = $this->args;
  }


  /* 50
   * getrawtransaction "txid" (true|false)
   *
   * Returns JSON object.{
   * 
     Input:
     {
     }

     Output:
     {
       "txid": "399e24d59fa4f2786ba0689f071c0695e60875d7451f785d5058ad89c426db18",
       "hash": "e2841e99b0424decb01b11e43a6e4e00cb2b9a9ec97bec98f85461717e095604",
       "version": 1,
       "size": 233,
       "vsize": 206,
       "locktime": 0,
       "vin": [
         {
           "coinbase": "02410a04f3294e5a086fffff0db90100000d2f6e6f64655374726174756d2f",
           "sequence": 0
         }
       ],
       "vout": [
         {
           "value": 0.00000000,
           "n": 0,
           "scriptPubKey": {
             "asm": "OP_RETURN aa21a9ede2f61c3f71d1defd3fa999dfa36953755c690689799962b48bebd836974e8cf9",
             "hex": "6a24aa21a9ede2f61c3f71d1defd3fa999dfa36953755c690689799962b48bebd836974e8cf9",
             "type": "nulldata"
           }
         },
         {
           "value": 49.50000000,
           "n": 1,
           "scriptPubKey": {
             "asm": "OP_DUP OP_HASH160 55ceda0d3baf3ff198e36185fa57ccc855ce5939 OP_EQUALVERIFY OP_CHECKSIG",
             "hex": "76a91455ceda0d3baf3ff198e36185fa57ccc855ce593988ac",
             "reqSigs": 1,
             "type": "pubkeyhash",
             "addresses": [
               "18piEcrRn3juEBwS3kHDsizkbYmSaVLA1e"
             ]
           }
         },
         {
           "value": 0.50000000,
           "n": 2,
           "scriptPubKey": {
             "asm": "OP_DUP OP_HASH160 9e1502512566ef23183857c3e516d132b765514e OP_EQUALVERIFY OP_CHECKSIG",
             "hex": "76a9149e1502512566ef23183857c3e516d132b765514e88ac",
             "reqSigs": 1,
             "type": "pubkeyhash",
             "addresses": [
               "1FQrw3XoXmXD1aG9JB4kdUHJuhyHwxhNKT"
             ]
           }
         }
       ]
      }
   * 
   */
  public function getrawtransaction($txid, $flag = true) {

    $args = $this->args;
    $args["method"] = "getrawtransaction";
    $args["params"] = ["$txid", $flag];

    $res = $this->call($args);
    if($res)
      return $res['result'];
  }


  /* 51
   *
   * Returns
   *
   */
  public function sendrawtransaction($hexstring, $allowhighfees) {
    $args = $this->args;
  }


  /* 52
   *
   * Returns
   *
   */
  public function signrawtransaction() {
    $args = $this->args;
  }


  ////////////////////////////////////////
  //                UTIL                //
  ////////////////////////////////////////


  /* 53
   *
   * Returns
   *
   */
  //public function createmultisig($nrequired, $key = []) {
    public function createmultisig($nrequired, $key) {
      $args = $this->args;
    }
  
  
  /* 54
   *
   * Returns
   *
   */
  public function estimatesmartfee($nblocks = 0) {
    $args = $this->args;
  }


  /* 55
   *
   * Returns
   *
   */
  public function signmessagewithprivkey($privkey, $message) {
    $args = $this->args;
  }


  /* 56
   *
   * Returns
   *
   */
  public function validateaddress($address) {
    $args = $this->args;
  }


  /* 57
   *
   * Returns
   *
   */
  public function verifymessage($address, $signature, $message) {
    $args = $this->args;
  }


  ////////////////////////////////////////
  //               WALLET               //
  ////////////////////////////////////////


  /* 58
   *
   * Returns
   *
   */
  public function abandontransaction($txid) {
    $args = $this->args;
  }


  /* 59
   *
   * Returns
   *
   */
  public function abortscan() {
    $args = $this->args;
  }


  /* 60
   *
   * Returns
   *
   */
  public function addmultisigaddress($nrequired, $key, $account) {
    $args = $this->args;
  }


  /* 61
   *
   * Returns
   *
   */
  public function addwithnessaddress($address) {
    $args = $this->args;
  }


  /* 62
   *
   * Returns
   *
   */
  public function backupwallet($destination) {
    $args = $this->args;
  }


  /* 63
   *
   * Returns
   *
   */
  public function bumpfee($txid, $options) {
    $args = $this->args;
  }


  /* 64
   *
   * Returns
   *
   */
  public function dumpprivkey($address) {
    $args = $this->args;
  }


  /* 65
   *
   * Returns
   *
   */
  public function dumpwallet($filename) {
    $args = $this->args;
  }


  /* 66
   *
   * Returns
   *
   */
  public function encryptwallet($passphrase) {
    $args = $this->args;
  }


  /* 67
   *
   * Returns
   *
   */
  public function getaccount($address) {
    $args = $this->args;
  }


  /* 68
   *
   * Returns
   *
   */
  public function getaccountaddress($account) {
    $args = $this->args;
  }


  /* 69
   *
   * Returns
   *
   */
  public function getaddressbyaccount($account) {
    $args = $this->args;
  }


  /* 70
   *
   * Returns
   *
   */
  public function getbalance($account, $minconf = 0, $include_watchonly) {
    // $include_watchonly may be one of true|false
    $args = $this->args;
  }


  /* 71
   *
   * Returns
   *
   */
  public function getnewaddress($account) {
    $args = $this->args;
  }


  /* 72
   *
   * Returns
   *
   */
  public function getrawchangeaddress() {
    $args = $this->args;
  }


  /* 73
   *
   * Returns
   *
   */
  public function getreceivedbyaccount($account, $minconf = 0) {
    $args = $this->args;
  }


  /* 74
   *
   * Returns
   *
   */
  public function getreceivedbyaddress($address, $minconf = 0) {
    $args = $this->args;
  }


  /* 75
   *
   * Returns
   *
   */
  public function gettransaction($txid, $include_watchonly) {
    // $include_watchonly may be one of true|false
    $args = $this->args;
  }


  /* 76
   *
   * Returns
   *
   */
  public function getunconfirmedbalance() {
    $args = $this->args;
  }


  /* 77
   *
   * Returns
   *
   */
  public function getwalletinfo() {
    $args = $this->args;
  }


  /* 78
   *
   * Returns
   *
   */
  public function importaddress($address, $label, $rescan, $p2sh) {
    $args = $this->args;
  }


  /* 79
   *
   * Returns
   *
   */
  public function importmultu($requests, $options) {
    $args = $this->args;
  }


  /* 80
   *
   * Returns
   *
   */
  public function importprivkey($privkey, $label, $rescan) {
    $args = $this->args;
  }


  /* 81
   *
   * Returns
   *
   */
  public function importprunedfunds() {
    $args = $this->args;
  }


  /* 82
   *
   * Returns
   *
   */
  public function importpubkey($pubkey, $label, $rescan) {
    $args = $this->args;
  }


  /* 83
   *
   * Returns
   *
   */
  public function importwallet($filename) {
    $args = $this->args;
  }


  /* 84
   *
   * Returns
   *
   */
  public function keypoolrefill($newsize) {
    $args = $this->args;
  }


  /* 85
   *
   * Returns
   *
   */
  public function listaccounts($minconf = 0, $include_watchonly) {
    $args = $this->args;
  }


  /* 86
   *
   * Returns
   *
   */
  public function listaddressgroupings() {
    $args = $this->args;
  }


  /* 87
   *
   * Returns
   *
   */
  public function listlockunspent() {
    $args = $this->args;
  }


  /* 88
   *
   * Returns
   *
   */
  public function listreceivedbyaccount($minconf = 0, $include_empty, $include_watchonly) {
    $args = $this->args;
  }


  /* 89
   *
   * Returns
   *
   */
  public function listreceivedbyaddress($minconf = 0, $include_empty, $include_watchonly) {
    $args = $this->args;
  }


  /* 90
   *
   * Returns
   *
   */
  public function listsinceblock($blockhash, $target_confirmations, $include_watchonly, $include_removed) {
    $args = $this->args;
  }


  /* 91
   *
   * Returns
   *
   */
  public function listtransactions($account, $count, $skip, $include_watchonly) {
    // $count is of type int
    // $skip is of type int
    // $include_watchonly may be one of true|false
    $args = $this->args;
  }


  /* 92
   *
   * Returns
   *
   */
  //public function listunspent($minconf = 0, $maxconf = 100, $addresses = [], $include_unsafe, $query_options = []) {
  public function listunspent($minconf = 0, $maxconf = 100, $addresses, $include_unsafe, $query_options) {
    $args = $this->args;
  }


  /* 93
   *
   * Returns
   *
   */
  public function listwallets() {
    $args = $this->args;
  }


  /* 94
   *
   * Returns
   *
   */
  public function lockunspent($unlock, $txid) {
    $args = $this->args;
  }


  /* 95
   *
   * Returns
   *
   */
  public function move($fromaccount, $toaccount, $minconf = 0, $comment) {
    $args = $this->args;
  }


  /* 96
   *
   * Returns
   *
   */
  public function removeprunedfunds($txid) {
    $args = $this->args;
  }


  /* 97
   *
   * Returns
   *
   */
  public function sendfrom($fromaccount, $toaddress, $amount, $minconf = 0, $comment, $comment_to) {
    $args = $this->args;
  }


  /* 98
   *
   * Returns
   *
   */
  public function sendmany($fromaccount, $address, $amount, $minconf = 0, $comment, $replaceable, $conf_target, $estimate_mode) {
    $args = $this->args;
  }


  /* 99
   *
   * Returns
   *
   */
  public function sendtoaddress($address, $amount, $comment, $comment_to, $subtractfeefromamount, $replaceable, $conf_target, $estimate_mode) {
    $args = $this->args;
  }


  /* 100
   *
   * Returns
   *
   */
  public function setaccount($address, $account) {
    $args = $this->args;
  }


  /* 101
   *
   * Returns
   *
   */
  public function signmessage($address, $message) {
    $args = $this->args;
  }


}


?>
