<?php


/*
 * Api class for Umkoin (SHA256) crypto currency
 *
 * Author:  vmta
 * Date:    5 Jan 2018
 * Update:  18 Jan 2018 / v0.0.2
 *
 * Version_Major: 0
 * Version_Minor: 0
 * Version_Build: 2
 *
 * Changelog:
 *
 * v0.0.2
 * Introduce $debug variable to turn on/off debugging
 * Implement JSON call exception interception
 *
 * v0.0.1
 * Initial code
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
   * $debug variable of type boolean sets whether to
   * generate debug info on calls or not
   *
   */
  private $debug;


  /*
   * Class constructor.
   *
   * Without arguments, assume that underlying daemon
   * is running with default config, i.e. has protocol
   * "http", on "localhost" and with rpc port 6332.
   *
   */
  public function __construct($server = "http://127.0.0.1:6332", $auth = "rpcuser:rpcpass", $debug = false) {
    $this->server = $server;
    $this->auth = $auth;
    $this->args = [ "jsonrpc" => "2.0", "id" => "curl", "method" => "", "params" => [] ];
    $this->debug = $debug;
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: text/plain']);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $this->auth);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $res = null;
    try {

      $res = curl_exec($ch);
      if ($res === FALSE)
        throw new Exception("Could not get reply: " . curl_error($ch));

    } catch (Exception $e) {

      if ($this->debug)
        print_r($e->getMessage());

    }

    $obj = null;
    try {

      $obj = json_decode($res, TRUE);
      if (!isset($obj["result"])) {

        if (isset($obj["error"])) {
          throw new Exception("Error(" . $obj["error"]["code"] . "): " . $obj["error"]["message"] . PHP_EOL);
        } else {
          throw new Exception("Unknown Error: " . $obj . PHP_EOL);
        }

      }

    } catch (Exception $e) {

      if ($this->debug)
        print_r($e->getMessage());

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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if($res)
      return $res['result'];
  }


  /* 15
   * gettxout "txid" vout include_mempool
   *
   * Returns JSON object.
   *
     Input:
     {
       "params": ["txid", vout, include_mempool]
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
  public function gettxout($txid, $vout = 1, $include_mempool = false) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$txid", $vout, $include_mempool];

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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 18
   * preciousblock "blockhash"
   * 
   * Treats a block as if it were received before others with the same work.
   * A later preciousblock call can override the effect of an earlier one.
   * The effects of preciousblock are not retained across restarts.
   * 
   * Arguments:
   * 1. "blockhash"   (string, required) the hash of the block to mark as precious
   *
   */
  public function preciousblock($hash) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
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
    $args["method"] = __FUNCTION__;
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
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 21
   * verifytxoutproof "proof"
   * 
   * Verifies that a proof points to a transaction in a block, returning the
   * transaction it commits to and throwing an RPC error if the block is not
   * in our best chain
   * 
   * Arguments:
   * 1. "proof"    (string, required) The hex-encoded proof generated by
   *               gettxoutproof
   * 
   * Result:
   * ["txid"]      (array, strings) The txid(s) which the proof commits to,
   *               or empty array if the proof is invalid
   *
   */
  public function verifytxoutproof($proof) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$proof"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;

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
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  ////////////////////////////////////////
  //              GENERATE              //
  ////////////////////////////////////////


  /* 26
   * generate nblocks ( maxtries )
   *
   * Mine up to nblocks blocks immediately (before the RPC call returns) to an address in the wallet.
   *
     Input:
     {
       "params": [ nblocks, maxtries ]
     }

     Output:
     {
       [ "blockhash" ]
     }
   *
   */
  public function generate($nblocks = 1, $maxtries = 1000) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [$nblocks, $maxtries];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 27
   * generatetoaddress nblocks address (maxtries)
   *
   * Mine blocks immediately to a specified address (before the RPC call returns)
   *
     Input:
     {
       "params": [ nblocks, address, maxtries ]
     }

     Output:
     {
       [ "blockhash" ]
     }
   *
   */
  public function generatetoaddress($nblocks = 1, $address, $maxtries = 1000) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [$nblocks, $address, $maxtries];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  ////////////////////////////////////////
  //                MINING              //
  ////////////////////////////////////////


  /* 28
   * getblocktemplate
   *
   * Returns JSON object.
   *
     Input:
     {
     }

     Output:
     {
       "capabilities": [
         "proposal"
       ],
       "version": 536870912,
       "rules": [
         "testdummy",
         "csv",
         "segwit"
       ],
       "vbavailable": {
       },
       "vbrequired": 0,
       "previousblockhash": "000000000010d2abe821e7329fa78c8de7bc1ae21bd7a5dc4991fb90bc8c6025",
       "transactions": [
       ],
       "coinbaseaux": {
         "flags": ""
       },
       "coinbasevalue": 5000000000,
       "longpollid": "000000000010d2abe821e7329fa78c8de7bc1ae21bd7a5dc4991fb90bc8c602523",
       "target": "00000000004df8f5000000000000000000000000000000000000000000000000",
       "mintime": 1515486583,
       "mutable": [
         "time",
         "transactions",
         "prevblock"
       ],
       "noncerange": "00000000ffffffff",
       "sigoplimit": 80000,
       "sizelimit": 4000000,
       "weightlimit": 4000000,
       "curtime": 1515489375,
       "bits": "1b4df8f5",
       "height": 3310
     }
   *
   */
  public function getblocktemplate() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 29
   * getmininginfo
   *
   * Returns JSON object.
   *
     Input:
     {
     }

     Output:
     {
       "blocks": 3309,
       "currentblockweight": 4000,
       "currentblocktx": 0,
       "difficulty": 840.4887588172614,
       "networkhashps": 7936521523.330769,
       "pooledtx": 0,
       "chain": "main",
       "warnings": ""
     }
   *
   */
  public function getmininginfo() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
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
    $args["method"] = __FUNCTION__;
    $args["params"] = [$height];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 31
   * prioritisetransaction <txid> <dummy value> <fee delta>
   * 
   * Accepts the transaction into mined blocks at a higher (or lower) priority
   * 
   * Arguments:
   * 1. "txid"    (string, required) The transaction id.
   * 2. dummy     (numeric, optional) API-Compatibility for previous API. Must be zero or null.
   *              DEPRECATED. For forward compatibility use named arguments and omit this parameter.
   * 3. fee_delta (numeric, required) The fee value (in satoshis) to add (or subtract, if negative).
   *              The fee is not actually paid, only the algorithm for selecting transactions into a block
   *              considers the transaction as it would have paid a higher (or lower) fee.
   *
   * Result:
   * true         (boolean) Returns true
   *
   */
  public function prioritisetransaction($txid, $dummyvalue = 0, $feedelta) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [ "$txid", $dummyvalue, $feedelta ];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 32
   * submitblock "hexdata"  ( "dummy" )
   *
   * Attempts to submit new block to network.
   *
     Input:
     {
       "params": [ "hexdata", "dummy" ]
     }

     Output:
     {
     }
   *
   */
  public function submitblock($hexdata, $dummy = "dummy") {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [ "$hexdata", "$dummy" ];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  ////////////////////////////////////////
  //               NETWORK              //
  ////////////////////////////////////////


  /* 33
   * addnode "node" "add|remove|onetry"
   *
   * Attempts to add or remove a node from the addnode list.
   * Or try a connection to a node once.
   *
     Input:
     {
       "params": [ "node", "add|remove|onetry" ]
     }

     Output:
     {
     }
   *
   */
  public function addnode($node, $cmd = "onetry") {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [ "$node", "$cmd" ];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 34
   * clearbanned
   *
   * Clear all banned IPs.
   *
     Input:
     {
     }

     Output:
     {
     }
   *
   */
  public function clearbanned() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 35
   * disconnectnode "[address]" [nodeid]
   *
   * Immediately disconnects from the specified peer node.
   * Strictly one out of 'address' and 'nodeid' can be provided to identify the node.
   * To disconnect by nodeid, either set 'address' to the empty string, or call using the named 'nodeid' argument only.
   *
     Input:
     {
       "params": [ "address", nodeid ]
     }

     Output:
     {
     }
   *
   */
  public function disconnectnode($address, $nodeid) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    if (!empty($nodeid))
      $args["params"] = [ "", $nodeid ];
    else
      $args["params"] = [ "$address" ];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 36
   * getaddednodeinfo "node"
   *
   * Returns JSON object representing information on added node.
   * Note: if "node" is undefined, information on all added nodes is
   *       returned as an array.
   * Note: if "node" is defined but such a node is not on the added
   *       node list, an error is returned.
   * Note: "node" shall exactly match added node address:port.
   *
     Input:
     {
       "params": "node"
     }

     Output:
     [
       {
         "addednode": "192.168.1.1:6333",
         "connected": true,
         "addresses": [
           {
             "address": "192.168.1.1:6333",
             "connected": "outbound"
           }
         ]
       },
       {
         "addednode": "192.168.1.2:6333",
         "connected": true,
         "addresses": [
           {
             "address": "192.168.1.2:6333",
             "connected": "outbound"
           }
         ]
       }
     ]
   *
   */
  public function getaddednodeinfo($node = "") {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    if (!empty($node))
      $args["params"] = ["$node"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 37
   * getconnectioncount
   *
   * Returns stripped JSON object, an int representing current number
   * of established connections.
   *
     Input:
     {
     }

     Output:
     {
       7
     }
   *
   */
  public function getconnectioncount() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 38
   * getnettotals
   *
   * Returns JSON object with various historical network data.
   *
     Input:
     {
     }

     Output:
     {
       "totalbytesrecv": 651917,
       "totalbytessent": 427366,
       "timemillis": 1515287291358,
       "uploadtarget": {
         "timeframe": 86400,
         "target": 0,
         "target_reached": false,
         "serve_historical_blocks": true,
         "bytes_left_in_cycle": 0,
         "time_left_in_cycle": 0
       }
     }
   *
   */
  public function getnettotals() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 39
   * getnetworkinfo
   *
   * Returns JSON object representing current network status.
   *
     Input:
     {
     }

     Output:
     {
       "version": 159913,
       "subversion": "/Satoshi:0.15.99.13/",
       "protocolversion": 70015,
       "localservices": "000000000000040d",
       "localrelay": true,
       "timeoffset": 0,
       "networkactive": true,
       "connections": 4,
       "networks": [
         {
           "name": "ipv4",
           "limited": false,
           "reachable": true,
           "proxy": "",
           "proxy_randomize_credentials": false
         },
         {
           "name": "ipv6",
           "limited": true,
           "reachable": false,
           "proxy": "",
           "proxy_randomize_credentials": false
         },
         {
           "name": "onion",
           "limited": true,
           "reachable": false,
           "proxy": "",
           "proxy_randomize_credentials": false
         }
       ],
       "relayfee": 0.00001000,
       "incrementalfee": 0.00001000,
       "localaddresses": [
       ],
       "warnings": ""
     }
   *
   */
  public function getnetworkinfo() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 40
   * getpeerinfo
   *
   * Returns data about each connected network node as a json array of objects.
   *
     Input:
     {
     }

     Output:
     [
       {
         "id": n,                   (numeric) Peer index
         "addr":"host:port",      (string) The IP address and port of the peer
         "addrbind":"ip:port",    (string) Bind address of the connection to the peer
         "addrlocal":"ip:port",   (string) Local address as reported by the peer
         "services":"xxxxxxxxxxxxxxxx",   (string) The services offered
         "relaytxes":true|false,    (boolean) Whether peer has asked us to relay transactions to it
         "lastsend": ttt,           (numeric) The time in seconds since epoch (Jan 1 1970 GMT) of the last send
         "lastrecv": ttt,           (numeric) The time in seconds since epoch (Jan 1 1970 GMT) of the last receive
         "bytessent": n,            (numeric) The total bytes sent
         "bytesrecv": n,            (numeric) The total bytes received
         "conntime": ttt,           (numeric) The connection time in seconds since epoch (Jan 1 1970 GMT)
         "timeoffset": ttt,         (numeric) The time offset in seconds
         "pingtime": n,             (numeric) ping time (if available)
         "minping": n,              (numeric) minimum observed ping time (if any at all)
         "pingwait": n,             (numeric) ping wait (if non-zero)
         "version": v,              (numeric) The peer version, such as 7001
         "subver": "/Satoshi:0.8.5/",  (string) The string version
         "inbound": true|false,     (boolean) Inbound (true) or Outbound (false)
         "addnode": true|false,     (boolean) Whether connection was due to addnode/-connect or if it was an automatic/inbound connection
         "startingheight": n,       (numeric) The starting height (block) of the peer
         "banscore": n,             (numeric) The ban score
         "synced_headers": n,       (numeric) The last header we have in common with this peer
         "synced_blocks": n,        (numeric) The last block we have in common with this peer
         "inflight": [
            n,                        (numeric) The heights of blocks we're currently asking from this peer
            ...
         ],
         "whitelisted": true|false, (boolean) Whether the peer is whitelisted
         "bytessent_per_msg": {
            "addr": n,              (numeric) The total bytes sent aggregated by message type
            ...
         },
         "bytesrecv_per_msg": {
            "addr": n,              (numeric) The total bytes received aggregated by message type
            ...
         }
       }
       ,...
     ]
   *
   */
  public function getpeerinfo() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 41
   * listbanned
   *
   * Returns stripped JSON object, an array of banned hosts.
   *
     Input:
     {
     }

     Output:
     [
     ]
   *
   */
  public function listbanned() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 42
   * ping
   *
   * Requests that a ping be sent to all other nodes, to measure ping time.
   * Ping command is handled in queue with all other commands, so it
   * measures processing backlog, not just network ping. Results provided
   * in getpeerinfo, pingtime and pingwait fields are decimal seconds.
   *
   */
  public function ping() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 43
   * setban "subnet" "add|remove" (bantime) (absolute)
   *
   * Attempts to add or remove an IP/Subnet from the banned list.
   * 1. "subnet"       (string, required) The IP/Subnet (see getpeerinfo for
   * nodes IP) with an optional netmask (default is /32 = single IP)
   * 2. "command"      (string, required) 'add' to add an IP/Subnet to the
   * list, 'remove' to remove an IP/Subnet from the list
   * 3. "bantime"      (numeric, optional) time in seconds how long (or until
   * when if [absolute] is set) the IP is banned (0 or empty means using the
   * default time of 24h which can also be overwritten by the -bantime startup
   * argument)
   * 4. "absolute"     (boolean, optional) If set, the bantime must be an
   * absolute timestamp in seconds since epoch (Jan 1 1970 GMT)
   *
     Input:
     {
       "params": [ "subnet", "add|remove", (bantime), (absolute) ]
     }

     Output:
     {
     }
   *
   */
  public function setban($subnet, $cmd = "add", $bantime = 86400, $absolute = 0) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    if (!empty($absolute))
      $args["params"] = ["$subnet", "$cmd", $absolute];
    else
      $args["params"] = ["$subnet", "$cmd", $bantime];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 44
   * setnetworkactive true|false
   *
   * Disable/enable all p2p network activity.
   *
     Input:
     {
       "params": [true|false]
     }

     Output:
     {
     }
   *
   */
  public function setnetworkactive($flag = true) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [$flag];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  ////////////////////////////////////////
  //            RAWTRANSACTIONS         //
  ////////////////////////////////////////


  /* 45
   * combinerawtransaction ["hexstring",...]
   * 
   * Combine multiple partially signed transactions into one transaction.
   * The combined transaction may be another partially signed transaction or a
   * fully signed transaction.
   * 
   * Arguments:
   * 1. "txs"         (string) A json array of hex strings of partially signed transactions
   *     [
   *       "hexstring" (string) A transaction hash
   *       ,...
   *     ]
   * 
   * Result:
   * "hex"            (string) The hex-encoded raw transaction with signature(s)
   *
   */
  public function combinerawtransaction($hexstring) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = $hexstring;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 46
   * createrawtransaction [{"txid":"id","vout":n},...] {"address":amount,"data":"hex",...} ( locktime ) ( replaceable )
   *
   * Create a transaction spending the given inputs and creating new outputs.
   * Outputs can be addresses or data.
   * Returns hex-encoded raw transaction.
   * Note that the transaction's inputs are not signed, and
   * it is not stored in the wallet or transmitted to the network.
   *
     Input:
     1. "inputs"                (array, required) A json array of json objects
          [
            {
              "txid":"id",    (string, required) The transaction id
              "vout":n,         (numeric, required) The output number
              "sequence":n      (numeric, optional) The sequence number
            }
            ,...
          ]
     2. "outputs"               (object, required) a json object with outputs
         {
           "address": x.xxx,    (numeric or string, required) The key is the umkoin address, the numeric value (can be string) is the UMK amount
           "data": "hex"      (string, required) The key is "data", the value is hex encoded data
           ,...
         }
     3. locktime                  (numeric, optional, default=0) Raw locktime. Non-0 value also locktime-activates inputs
     4. replaceable               (boolean, optional, default=false) Marks this transaction as BIP125 replaceable.
                                  Allows this transaction to be replaced by a transaction with higher fees. If provided, it is an error if explicit sequence numbers are incompatible.

     Output:
     "transaction"              (string) hex string of the transaction
   *
   *
   */
  public function createrawtransaction() {
    // @TODO
    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 47
   * decoderawtransaction "hexstring" ( iswitness )
   *
   * Returns JSON object representing the serialized, hex-encoded transaction.
   *
     Input:
     {
       "params": "hexstring"
     }

     Output:
     {
       "txid": "9dad93c8cf6b1a3179005ae8bdea297054ad65148aa91772c980d5d7e53b8f5e",
       "hash": "9dad93c8cf6b1a3179005ae8bdea297054ad65148aa91772c980d5d7e53b8f5e",
       "version": 2,
       "size": 373,
       "vsize": 373,
       "locktime": 3122,
       "vin": [
         {
           "txid": "98d5662e7c530a5413e2017595a6a67c6bdd5a397b6e6004732e6b1f942a8a2e",
           "vout": 0,
           "scriptSig": {
             "asm": "304402202a6a06cb253a31b9379023a7a7d0f729c2238b4a0a7f4752b477bb3a768e350702204c02a9101a0f0f32401c7db08a64ccd7f8b157965a68d3159847765a683d9916[ALL] 03be2cecbc29bfc8d1c1e37a194d50e0eea68d6e3eae645c2f079977c25fc3f9ae",
             "hex": "47304402202a6a06cb253a31b9379023a7a7d0f729c2238b4a0a7f4752b477bb3a768e350702204c02a9101a0f0f32401c7db08a64ccd7f8b157965a68d3159847765a683d9916012103be2cecbc29bfc8d1c1e37a194d50e0eea68d6e3eae645c2f079977c25fc3f9ae"
           },
           "sequence": 4294967294
         },
         {
           "txid": "b612673bb45454e0de59fa08f5e71386b89208f3fcfcdf485f346d7513cf15f5",
           "vout": 0,
           "scriptSig": {
             "asm": "3045022100901f9104018d35cad8bc7878aae87cc2a52d5c5913b03e417d0576e5159fc011022058252a72d8758ec82259e612c6e0122b146f5d21e1d73a241df0b69901e68d95[ALL] 022be11093dee0c52a62f1e24cf1a7a71f06db69c51e48dee5fb32d522b1b5130b",
             "hex": "483045022100901f9104018d35cad8bc7878aae87cc2a52d5c5913b03e417d0576e5159fc011022058252a72d8758ec82259e612c6e0122b146f5d21e1d73a241df0b69901e68d950121022be11093dee0c52a62f1e24cf1a7a71f06db69c51e48dee5fb32d522b1b5130b"
           },
           "sequence": 4294967294
         }
       ],
       "vout": [
         {
           "value": 1000.00000000,
           "n": 0,
           "scriptPubKey": {
             "asm": "OP_DUP OP_HASH160 a09b7a0ea8e3f56bb71f1af38406a12ffc048fe9 OP_EQUALVERIFY OP_CHECKSIG",
             "hex": "76a914a09b7a0ea8e3f56bb71f1af38406a12ffc048fe988ac",
             "reqSigs": 1,
             "type": "pubkeyhash",
             "addresses": [
               "1FeDNQk5FuNCxJK7un4NhW8hRjpSC99g5t"
             ]
           }
         },
         {
           "value": 0.01039287,
           "n": 1,
           "scriptPubKey": {
             "asm": "OP_DUP OP_HASH160 3fea90a3df29c99e43768738b4a88016907ee8f8 OP_EQUALVERIFY OP_CHECKSIG",
             "hex": "76a9143fea90a3df29c99e43768738b4a88016907ee8f888ac",
             "reqSigs": 1,
             "type": "pubkeyhash",
             "addresses": [
               "16pxZwwcu7qsCYxoC7CdLoMc9qMMj9oFyA"
             ]
           }
         }
       ]
     }
   *
   */
  public function decoderawtransaction($hexstring) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$hexstring"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 48
   * decodescript "hexstring"
   * 
   * Decode a hex-encoded script.
   * 
   * Arguments:
   * 1. "hexstring"     (string) the hex encoded script
   * 
   * Result:
   * {
   *   "asm":"asm",   (string) Script public key
   *   "hex":"hex",   (string) hex encoded public key
   *   "type":"type", (string) The output type
   *   "reqSigs": n,    (numeric) The required signatures
   *   "addresses": [   (json array of string)
   *      "address"     (string) umkoin address
   *      ,...
   *   ],
   *   "p2sh","address" (string) address of P2SH script wrapping this redeem script (not returned if the script is already a P2SH).
   * }
   *
   */
  public function decodescript($hexstring) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$hexstring"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 49
   * fundrawtransaction "hexstring" ( options iswitness )
   *
   * Add inputs to a transaction until it has enough in value to meet its out value.
   * This will not modify existing inputs, and will add at most one change output to the outputs.
   * No existing outputs will be modified unless "subtractFeeFromOutputs" is specified.
   * Note that inputs which were signed may need to be resigned after completion since in/outputs have been added.
   * The inputs added will not be signed, use signrawtransaction for that.
   * Note that all existing inputs must have their previous output transaction be in the wallet.
   * Note that all inputs selected must be of standard form and P2SH scripts must be
   * in the wallet using importaddress or addmultisigaddress (to calculate fees).
   * You can see whether this is the case by checking the "solvable" field in the listunspent output.
   * Only pay-to-pubkey, multisig, and P2SH versions thereof are currently supported for watch-only

   * Arguments:
   * 1. "hexstring"             (string, required) The hex string of the raw transaction
   * 2. options                 (object, optional)
   *   {
   *     "changeAddress"          (string, optional, default pool address) The umkoin address to receive the change
   *     "changePosition"         (numeric, optional, default random) The index of the change output
   *     "includeWatching"        (boolean, optional, default false) Also select inputs which are watch only
   *     "lockUnspents"           (boolean, optional, default false) Lock selected unspent outputs
   *     "feeRate"                (numeric, optional, default not set: makes wallet determine the fee) Set a specific fee rate in UMK/kB
   *     "subtractFeeFromOutputs" (array, optional) A json array of integers.
   *                              The fee will be equally deducted from the amount of each specified output.
   *                              The outputs are specified by their zero-based index, before any change output is added.
   *                              Those recipients will receive less umkoins than you enter in their corresponding amount field.
   *                              If no outputs are specified here, the sender pays the fee.
   *                                  [vout_index,...]
   *     "replaceable"            (boolean, optional) Marks this transaction as BIP125 replaceable.
   *                              Allows this transaction to be replaced by a transaction with higher fees
   *     "conf_target"            (numeric, optional) Confirmation target (in blocks)
   *     "estimate_mode"          (string, optional, default=UNSET) The fee estimate mode, must be one of:
   *      "UNSET"
   *      "ECONOMICAL"
   *      "CONSERVATIVE"
   *}
   *                      for backward compatibility: passing in a true instead of an object will result in {"includeWatching":true}
   *3. iswitness               (boolean, optional) Whether the transaction hex is a serialized witness transaction
   *                              If iswitness is not present, heuristic tests will be used in decoding
   *
   *Result:
   *{
   *  "hex":       "value", (string)  The resulting raw transaction (hex-encoded string)
   *  "fee":       n,         (numeric) Fee in UMK the resulting transaction pays
   *  "changepos": n          (numeric) The position of the added change output, or -1
   *}
   *
   */
  public function fundrawtransaction($hexstring, $options) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["patams"] = ["$hexstring"];
    if (!empty($options))
      $args["params"][] = $options;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 50
   * getrawtransaction "txid" (verbose "blockhash")
   *
   * Returns JSON object.
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
  public function getrawtransaction($txid, $verbose = false, $blockhash = "") {

    $params_array = ["$txid", $verbose];
    if (!empty($blockhash))
      $params_array[] = "$blockhash";

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = $params_array;

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
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 52
   *
   * Returns
   *
   */
  public function signrawtransaction() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
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
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }
  
  
  /* 54
   * estimatesmartfee conf_target ("estimate_mode")
   * 
   * Estimates the approximate fee per kilobyte needed for a transaction to
   * begin confirmation within conf_target blocks if possible and return the
   * number of blocks for which the estimate is valid. Uses virtual
   * transaction size as defined in BIP 141 (witness data is discounted).
   *
   * Arguments:
   * 1. conf_target     (numeric) Confirmation target in blocks (1 - 1008)
   * 2. "estimate_mode" (string, optional, default=CONSERVATIVE) The fee
   *               estimate mode. Whether to return a more conservative
   *               estimate which also satisfies a longer history. A
   *               conservative estimate potentially returns a higher feerate
   *               and is more likely to be sufficient for the desired target,
   *               but is not as responsive to short term drops in the
   *               prevailing fee market.  Must be one of:
   *   "UNSET" (defaults to CONSERVATIVE)
   *   "ECONOMICAL"
   *   "CONSERVATIVE"
   *
   * Result:
   * {
   *   "feerate" : x.x,     (numeric, optional) estimate fee rate in UMK/kB
   *   "errors": [ str... ] (json array of strings, optional) Errors encountered
   *                        during processing
   *   "blocks" : n         (numeric) block number where estimate was found
   * }
   *
   * The request target will be clamped between 2 and the highest target
   * fee estimation is able to return based on how long it has been running.
   * An error is returned if not enough transactions and blocks
   * have been observed to make an estimate for any number of blocks.
   *
   */
  public function estimatesmartfee($conf_target, $estimate_mode = "CONSERVATIVE") {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [$conf_target, "$estimate_mode"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 55
   *
   * Returns
   *
   */
  public function signmessagewithprivkey($privkey, $message) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 56
   * validateaddress "address"
   * 
   * Return information about the given umkoin address.
   *
   * Arguments:
   * 1. "address"     (string, required) The umkoin address to validate
   *
   * Result:
   * {
   *   "isvalid" : true|false,       (boolean) If the address is valid or not. If not, this is the only property returned.
   *   "address" : "address",        (string) The umkoin address validated
   *   "scriptPubKey" : "hex",       (string) The hex encoded scriptPubKey generated by the address
   *   "ismine" : true|false,        (boolean) If the address is yours or not
   *   "iswatchonly" : true|false,   (boolean) If the address is watchonly
   *   "isscript" : true|false,      (boolean, optional) If the address is P2SH or P2WSH. Not included for unknown witness types.
   *   "iswitness" : true|false,     (boolean) If the address is P2WPKH, P2WSH, or an unknown witness version
   *   "witness_version" : version   (number, optional) For all witness output types, gives the version number.
   *   "witness_program" : "hex"     (string, optional) For all witness output types, gives the script or key hash present in the address.
   *   "script" : "type"             (string, optional) The output script type. Only if "isscript" is true and the redeemscript is known. Possible types: nonstandard, pubkey, pubkeyhash, scripthash, multisig, nulldata, witness_v0_keyhash, witness_v0_scripthash, witness_unknown
   *   "hex" : "hex",                (string, optional) The redeemscript for the P2SH or P2WSH address
   *   "addresses"                   (string, optional) Array of addresses associated with the known redeemscript (only if "iswitness" is false). This field is superseded by the "pubkeys" field and the address inside "embedded".
   *     [
   *       "address"
   *       ,...
   *     ]
   *   "pubkeys"                     (string, optional) Array of pubkeys associated with the known redeemscript (only if "script" is "multisig")
   *     [
   *       "pubkey"
   *       ,...
   *     ]
   *   "sigsrequired" : xxxxx        (numeric, optional) Number of signatures required to spend multisig output (only if "script" is "multisig")
   *   "pubkey" : "publickeyhex",    (string, optional) The hex value of the raw public key, for single-key addresses (possibly embedded in P2SH or P2WSH)
   *   "embedded" : {...},           (object, optional) information about the address embedded in P2SH or P2WSH, if relevant and known. It includes all validateaddress output fields for the embedded address, excluding "isvalid", metadata ("timestamp", "hdkeypath", "hdmasterkeyid") and relation to the wallet ("ismine", "iswatchonly", "account").
   *   "iscompressed" : true|false,  (boolean) If the address is compressed
   *   "account" : "account"         (string) DEPRECATED. The account associated with the address, "" is the default account
   *   "timestamp" : timestamp,      (number, optional) The creation time of the key if available in seconds since epoch (Jan 1 1970 GMT)
   *   "hdkeypath" : "keypath"       (string, optional) The HD keypath if the key is HD and available
   *   "hdmasterkeyid" : "<hash160>" (string, optional) The Hash160 of the HD master pubkey
   * }
   *
   */
  public function validateaddress($address) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$address"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 57
   * verifymessage "address" "signature" "message"
   * 
   * Verify a signed message
   *
   * Arguments:
   * 1. "address"         (string, required) The umkoin address to use for the signature.
   * 2. "signature"       (string, required) The signature provided by the signer in base 64 encoding (see signmessage).
   * 3. "message"         (string, required) The message that was signed.
   *
   * Result:
   * true|false   (boolean) If the signature is verified or not.
   *
   */
  public function verifymessage($address, $signature, $message) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$address", "$signature", "$message"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  ////////////////////////////////////////
  //               WALLET               //
  ////////////////////////////////////////


  /* 58
   * abandontransaction "txid"
   *
   * Mark in-wallet transaction <txid> as abandoned
   * This will mark this transaction and all its in-wallet descendants as abandoned which will allow
   * for their inputs to be respent.  It can be used to replace "stuck" or evicted transactions.
   * It only works on transactions which are not included in a block and are not currently in the mempool.
   * It has no effect on transactions which are already conflicted or abandoned.
   *
   * Arguments:
   * 1. "txid"    (string, required) The transaction id
   *
   * Result:
   *
   */
  public function abandontransaction($txid) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$txid"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 59
   *
   * Returns
   *
   */
  public function abortscan() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 60
   *
   * Returns
   *
   */
  public function addmultisigaddress($nrequired, $key, $account) {
    
    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 61
   *
   * Returns
   *
   */
  public function addwithnessaddress($address) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 62
   * backupwallet "destination"
   *
   * Safely copies current wallet file to destination, which can be a directory or a path with filename.
   *
   * Arguments:
   * 1. "destination"   (string) The destination directory or file
   *
   */
  public function backupwallet($destination) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$destination"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 63
   * bumpfee "txid" ( options )
   *
   * Bumps the fee of an opt-in-RBF transaction T, replacing it with a new transaction B.
   * An opt-in RBF transaction with the given txid must be in the wallet.
   * The command will pay the additional fee by decreasing (or perhaps removing) its change output.
   * If the change output is not big enough to cover the increased fee, the command will currently fail
   * instead of adding new inputs to compensate. (A future implementation could improve this.)
   * The command will fail if the wallet or mempool contains a transaction that spends one of T's outputs.
   * By default, the new fee will be calculated automatically using estimatefee.
   * The user can specify a confirmation target for estimatefee.
   * Alternatively, the user can specify totalFee, or use RPC settxfee to set a higher fee rate.
   * At a minimum, the new fee rate must be high enough to pay an additional new relay fee (incrementalfee
   * returned by getnetworkinfo) to enter the node's mempool.
   *
   * Arguments:
   * 1. txid                  (string, required) The txid to be bumped
   * 2. options               (object, optional)
   *    {
   *      "confTarget"        (numeric, optional) Confirmation target (in blocks)
   *      "totalFee"          (numeric, optional) Total fee (NOT feerate) to pay, in satoshis.
   *                          In rare cases, the actual fee paid might be slightly higher than the specified
   *                          totalFee if the tx change output has to be removed because it is too close to
   *                          the dust threshold.
   *      "replaceable"       (boolean, optional, default true) Whether the new transaction should still be
   *                          marked bip-125 replaceable. If true, the sequence numbers in the transaction will
   *                          be left unchanged from the original. If false, any input sequence numbers in the
   *                          original transaction that were less than 0xfffffffe will be increased to 0xfffffffe
   *                          so the new transaction will not be explicitly bip-125 replaceable (though it may
   *                          still be replaceable in practice, for example if it has unconfirmed ancestors which
   *                          are replaceable).
   *      "estimate_mode"     (string, optional, default=UNSET) The fee estimate mode, must be one of:
   *          "UNSET"
   *          "ECONOMICAL"
   *          "CONSERVATIVE"
   *    }
   *
   * Result:
   * {
   *   "txid":    "value",   (string)  The id of the new transaction
   *   "origfee":  n,         (numeric) Fee of the replaced transaction
   *   "fee":      n,         (numeric) Fee of the new transaction
   *   "errors":  [ str... ] (json array of strings) Errors encountered during processing (may be empty)
   * }
   * 
   */
  public function bumpfee($txid, $options = null) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$txid"];
    if (!empty($options))
      $args["params"][] = $options;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 64
   * dumpprivkey "address"
   *
   * Reveals the private key corresponding to 'address'.
   * Then the importprivkey can be used with this output
   *
   * Arguments:
   * 1. "address"   (string, required) The umkoin address for the private key
   *
   * Result:
   * "key"                (string) The private key
   *
   */
  public function dumpprivkey($address) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$address"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 65
   * dumpwallet "filename"
   *
   * Dumps all wallet keys in a human-readable format to a server-side file. This does not allow overwriting existing files.
   * Imported scripts are included in the dumpfile, but corresponding BIP173 addresses, etc. may not be added automatically by importwallet.
   * Note that if your wallet contains keys which are not derived from your HD seed (e.g. imported keys), these are not covered by
   * only backing up the seed itself, and must be backed up too (e.g. ensure you back up the whole dumpfile).
   *
   * Arguments:
   * 1. "filename"  (string, required) The filename with path (either absolute or relative to umkoind)
   *
   * Result:
   * "filename" :   (string) The filename with full absolute path
   *
   */
  public function dumpwallet($filename) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$filename"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 66
   * encryptwallet "passphrase"
   * 
   * Encrypts the wallet with 'passphrase'. This is for first time encryption.
   * After this, any calls that interact with private keys such as sending or signing
   * will require the passphrase to be set prior the making these calls.
   * Use the walletpassphrase call for this, and then walletlock call.
   * If the wallet is already encrypted, use the walletpassphrasechange call.
   * Note that this will shutdown the server.
   *
     Input:
     {
       "params": "passphrase" (string)
     }

     Output:
     {
     }
   *
   */
  public function encryptwallet($passphrase) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$passphrase"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 67
   * getaccount "address"
   * 
   * DEPRECATED. Returns the account associated with the given address.
   * 
     Input:
     {
       "params": "address"
     }

     Output:
     {

     }
   *
   */
  public function getaccount($address) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$address"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 68
   * getaccountaddress "account"
   *
   * DEPRECATED. Returns the current Umkoin address for receiving payments to
   * this account.
   *
   * Arguments:
   * 1. "account" (string, required) The account name for the address. It can
   * also be set to the empty string "" to represent the default account. The
   * account does not need to exist, it will be created and a new address
   * created if there is no account by the given name.
   *
   * Result:
   * "address"    (string) The account umkoin address
   *
   */
  public function getaccountaddress($account) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$account"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 69
   *
   * Returns
   *
   */
  //public function getaddressbyaccount($account) {

  //  $args = $this->args;
  //  $args["method"] = __FUNCTION__;

  //  $res = $this->call($args);
  //  if ($res)
  //    return $res['result'];
  //}


  /* 70
   * getbalance ("account") (minconf) (include_watchonly)
   *
   * Returns total amount of available coins.
   *
   * Arguments:
   * 1. "account"          (string, optional) The account name for which the
   *                       balance will be displayed. It could be set to empty
   *                       string "" to represent the balance for default
   *                       account.
   * 2. minconf            (numeric, optional) Display spendable balance with
   *                       a minimum number of confirmations. Set to zero to
   *                       retrieve grand total for balance, including
   *                       unconfirmed transactions.
   * 3. include_watchonly  (boolean, optional)
   *
   * Result:
   * x.xx                  (numeric) The corresponding amount.
   *
   */
  public function getbalance($account = "", $minconf = 1, $include_watchonly = true) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$account", $minconf, $include_watchonly];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 71
   * getnewaddress ( "account" )
   *
   * Creates new address within the specified account.
   * 
   * Arguments:
   * 1. "account"        (string, optional) The account name within which new
   *                     address will be created. It could be set to empty
   *                     string "" to create new address within the default
   *                     account.
   * 
   * Result:
   * "address"           (string) New umkoin address.
   *
   */
  public function getnewaddress($account = "") {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$account"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 72
   *
   * Returns
   *
   */
  public function getrawchangeaddress() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 73
   *
   * Returns
   *
   */
  public function getreceivedbyaccount($account, $minconf = 0) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 74
   *
   * Returns
   *
   */
  public function getreceivedbyaddress($address, $minconf = 0) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 75
   * gettransaction "txid" ( include_watchonly )
   *
   * Get detailed information about in-wallet transaction <txid>
   *
     Input:
     {
       "params": ["txid", (include_watchonly)]
     }

     Output:
     {
       "amount" : x.xxx,        (numeric) The transaction amount in UMK
       "fee": x.xxx,            (numeric) The amount of the fee in UMK. This is negative and only available for the
                                'send' category of transactions.
       "confirmations" : n,     (numeric) The number of confirmations
       "blockhash" : "hash",  (string) The block hash
       "blockindex" : xx,       (numeric) The index of the transaction in the block that includes it
       "blocktime" : ttt,       (numeric) The time in seconds since epoch (1 Jan 1970 GMT)
       "txid" : "transactionid",   (string) The transaction id.
       "time" : ttt,            (numeric) The transaction time in seconds since epoch (1 Jan 1970 GMT)
       "timereceived" : ttt,    (numeric) The time received in seconds since epoch (1 Jan 1970 GMT)
       "bip125-replaceable": "yes|no|unknown",  (string) Whether this transaction could be replaced due to BIP125 (replace-by-fee);
                                                   may be unknown for unconfirmed transactions not in the mempool
       "details" : [
         {
           "account" : "accountname",      (string) DEPRECATED. The account name involved in the transaction, can be "" for the default account.
           "address" : "address",          (string) The umkoin address involved in the transaction
           "category" : "send|receive",    (string) The category, either 'send' or 'receive'
           "amount" : x.xxx,                 (numeric) The amount in UMK
           "label" : "label",              (string) A comment for the address/transaction, if any
           "vout" : n,                       (numeric) the vout value
           "fee": x.xxx,                     (numeric) The amount of the fee in UMK. This is negative and only available for the
                                             'send' category of transactions.
           "abandoned": xxx                  (bool) 'true' if the transaction has been abandoned (inputs are respendable). Only available for the
                                             'send' category of transactions.
         }
         ,...
       ],
       "hex" : "data"         (string) Raw data for transaction
     }
   *
   */
  public function gettransaction($txid, $include_watchonly = false) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$txid", $include_watchonly];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 76
   *
   * Returns
   *
   */
  public function getunconfirmedbalance() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 77
   *
   * Returns
   *
   */
  public function getwalletinfo() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 78
   *
   * Returns
   *
   */
  public function importaddress($address, $label, $rescan, $p2sh) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 79
   * importmulti "requests" ( "options" )
   * 
   * RetuImport addresses/scripts (with private or public keys, redeem script
   * (P2SH)), rescanning all addresses in one-shot-only (rescan can be
   * disabled via options). Requires a new wallet backup.
   * 
     Input:
     {
       "params": ["requests", ("options")]
     }

     Output:
     {
     }
   *
   */
  public function importmulti($requests, $options = []) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    if(!empty($options))
      $args["params"] = [$requests, $options];
    else
      $args["params"] = [$requests];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 80
   *
   * Returns
   *
   */
  public function importprivkey($privkey, $label, $rescan) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 81
   *
   * Returns
   *
   */
  public function importprunedfunds() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 82
   *
   * Returns
   *
   */
  public function importpubkey($pubkey, $label, $rescan) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 83
   *
   * Returns
   *
   */
  public function importwallet($filename) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 84
   *
   * Returns
   *
   */
  public function keypoolrefill($newsize) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 85
   * listaccounts (minconf) (include_watchonly)
   * 
   * Returns JSON object as an array of all wallet accounts with respectful balances.
   * 
     Input:
     {
       "params": [minconf, include_watchonly]
     }

     Output:
     {
       "Account1": 129.34890554,
       "Account2": 11.65889236
     }
   *
   */
  public function listaccounts($minconf = 0, $include_watchonly = true) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = [$minconf, $include_watchonly];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 86
   * listaddressgroupings
   * 
   * Returns JSON object as an array of available addresses and their balances.
   * 
     Input:
     {
     }

     Output:
     [
       [
         [
           "1n6LcwLcAEBPYLDBwPY6YS8kgeA4pQ2Ud",
           0.49970713
         ],
         [
           "12xUHwcZigWUWQ4uoGc2TkodrERcbrpJWF",
           0.49990028
         ],
         [
           "138uZ8MtMhnNspmq1g5sz2QqJ62hXXLaUN",
           0.00000000
         ]
       ]
     ]
   *
   */
  public function listaddressgroupings() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 87
   * listlockunspent
   * 
   * Returns list of temporarily unspendable outputs.
   * See the lockunspent call to lock and unlock transactions for spending.
   * 
     Input:
     {
     }

     Output:
     [
       {
         "txid" : "transactionid",   (string) The transaction id locked
         "vout" : n                  (numeric) The vout value
       }
       ,...
     ]
   *
   */
  public function listlockunspent() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 88
   *
   * Returns
   *
   */
  public function listreceivedbyaccount($minconf = 0, $include_empty, $include_watchonly) {
    
    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 89
   *
   * Returns
   *
   */
  public function listreceivedbyaddress($minconf = 0, $include_empty, $include_watchonly) {
    
    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 90
   *
   * Returns
   *
   */
  public function listsinceblock($blockhash, $target_confirmations, $include_watchonly, $include_removed) {
    
    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
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
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 92
   *
   * Returns
   *
   */
  //public function listunspent($minconf = 0, $maxconf = 100, $addresses = [], $include_unsafe, $query_options = []) {
  public function listunspent($minconf = 0, $maxconf = 100, $addresses, $include_unsafe, $query_options) {
    
    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 93
   *
   * Returns
   *
   */
  public function listwallets() {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 94
   *
   * Returns
   *
   */
  public function lockunspent($unlock, $txid) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 95
   *
   * Returns
   *
   */
  public function move($fromaccount, $toaccount, $minconf = 0, $comment) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 96
   *
   * Returns
   *
   */
  public function removeprunedfunds($txid) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 97
   *
   * Returns
   *
   */
  public function sendfrom($fromaccount, $toaddress, $amount, $minconf = 0, $comment, $comment_to) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 98
   *
   * Returns
   *
   */
  public function sendmany($fromaccount, $address, $amount, $minconf = 0, $comment, $replaceable, $conf_target, $estimate_mode) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 99
   * sendtoaddress "address" amount ( "comment" "comment_to" subtractfeefromamount replaceable conf_target "estimate_mode")
   * 
   * Send an amount to a given address.
   * 
   * Arguments:
   * 1. "address"       (string, required) The umkoin address to send to.
   * 2. "amount"        (numeric or string, required) The amount in UMK to send. eg 0.1
   * 3. "comment"       (string, optional) A comment used to store what the transaction is for.
   *                    This is not part of the transaction, just kept in your wallet.
   * 4. "comment_to"    (string, optional) A comment to store the name of the person or organization
   *                    to which you're sending the transaction. This is not part of the
   *                    transaction, just kept in your wallet.
   * 5. subtractfeefromamount  (boolean, optional, default=false) The fee will be deducted from the amount being sent.
   *                    The recipient will receive less umkoins than you enter in the amount field.
   * 6. replaceable     (boolean, optional) Allow this transaction to be replaced by a transaction with higher fees via BIP 125
   * 7. conf_target     (numeric, optional) Confirmation target (in blocks)
   * 8. "estimate_mode" (string, optional, default=UNSET) The fee estimate mode, must be one of:
   *        "UNSET"
   *        "ECONOMICAL"
   *        "CONSERVATIVE"
   * 
   * Result:
   * "txid"             (string) The transaction id.
   *
   */
  public function sendtoaddress($address, $amount, $comment, $comment_to, $subtractfeefromamount, $replaceable, $conf_target, $estimate_mode) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$address", $amount];

    if(isset($comment) && !empty($comment))
      $args["params"][] = "$comment";

    if(isset($comment_to) && !empty($comment_to))
      $args["params"][] = "$comment_to";

    if(isset($subtractfeefromamount))
      $args["params"][] = $subtractfeefromamount;

    if(isset($replaceable))
      $args["params"][] = $replaceable;
    
    if(isset($conf_target) && !empty($conf_target))
      $args["params"][] = $conf_target;

    if(isset($estimate_mode) && !empty($estimate_mode))
      $args["params"][] = "$estimate_mode";
    
    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 100
   * setaccount "address" "account"
   *
   * DEPRECATED. Sets the account associated with the given address.
   *
   * Arguments:
   * 1. "address"         (string, required) The umkoin address to be associated with an account.
   * 2. "account"         (string, required) The account to assign the address to.
   *
   */
  public function setaccount($address, $account) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;
    $args["params"] = ["$address", "$account"];

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


  /* 101
   *
   * Returns
   *
   */
  public function signmessage($address, $message) {

    $args = $this->args;
    $args["method"] = __FUNCTION__;

    $res = $this->call($args);
    if ($res)
      return $res['result'];
  }


}


?>
