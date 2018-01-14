<?php

class Block extends Api {

  public function __construct($server, $auth) {
    parent::__construct($server, $auth);
  }


  /*
   * Calculate average difficulty based on each block's
   * difficulty by suming diffs and dividing the result
   * on blocks count. (As the block count inadvertently
   * rises, the upper limit is set to 10000 last blocks).
   */
  public function getaveragedifficulty() {

    $difficulties = [];

    $height = $this->getblockcount();
    $i = $height;
    do {
      $hash = $this->getblockhash($i);
      $difficulties[] = $this->getblock($hash)['difficulty'];
      $i--;
    } while ( $i > ((($height - 10000) > 0) ? ($height - 10000) : 0) );

    return array_sum($difficulties) / count($difficulties);
  }


  /*
   * Calculate average hash rate based on each block's
   * hash rate by suming them and dividing the result
   * on blocks count. (As the block count inadvertently
   * rises, the upper limit is set to 10000 last blocks).
   */
  public function getaveragenetworkhashps() {

    $hashps = [];

    $height = $this->getblockcount();
    $i = $height;
    do {
      $hashps[] = $this->getnetworkhashps($i);
      $i--;
    } while ( $i > ((($height - 10000) > 0) ? ($height - 10000) : 0) );

    return array_sum($hashps) / count($hashps);
  }


  /*
   * Returns basereward for the given block by its height or hash.
   */
  public function getbasereward($height = 0, $flag = 'height') {
    if($flag == 'hash') {
      $height = $this->getblockheight($height);
    }

    return (5000000000 >> round($height / 210000, 0)) / 100000000;
  }


  /*
   * Return cumulative value amount for the given block.
   */
  public function getblockamount($hash, $flag = 'hash', $inout = 'out') {

    if ($flag != 'hash') {
      $hash = $this->getblockhash($hash);
    }

    $res = $this->getblock($hash);

    $amount = 0;
    for ($i = 0; $i < count($res["tx"]); $i++) {
      $tx_raw = $this->getrawtransaction($res["tx"][$i]);
      $tx_decoded = $this->decoderawtransaction($tx_raw);
      $varr = $tx_decoded["v$inout"];
      for ($j = 0; $j < count($varr); $j++) {
        $amount += $varr[$j]["value"];
      }
//var_dump($tx_decoded["v$inout"]);

//      $amount += $tx_decoded["size"];
    }

    return $amount;
  }


  /*
   * Returns difficulty of the block by its hash.
   */
  public function getblockdifficulty($hash) {

    $res = $this->getblock($hash);
    if ($res)
      return $res["difficulty"];
  }


  /*
   * Returns cumulative fee amount for the given block.
   */
  public function getblockfees($hash, $flag = 'hash') {

    if ($flag != 'hash') {
      $hash = $this->getblockhash($hash);
    }

    $fees = 0;
    $txids = $this->getblock($hash)["tx"];

    for ($i = 0; $i < count($txids); $i++) {
      $txid = $this->gettransaction($txids[$i]);
      if (isset($txid["fee"]))
        $fees += $txid["fee"];
    }

    return $fees;
  }


  /*
   * Returns height of the block by its hash.
   */
  public function getblockheight($hash) {

    $res = $this->getblock($hash);
    if ($res)
      return $res["height"];
  }


 /*
  * Returns size of the block by its height.
  */
  public function getblocksize($hash) {

    $res = $this->getblock($hash);
    if($res)
      return $res["size"];
  }


  /*
   * Returns time of the block by its hash.
   */
  public function getblocktime($hash) {

    $res = $this->getblock($hash);
    if ($res)
      return $res["time"];
  }


  /*
   * Return transactions count in a block by block hash.
   */
  public function getblocktransactionscount($hash) {

    $res = $this->getblock($hash);
    if ($res)
      return count($res["tx"]);
  }


  /*
   * Return transactions size in a block by block hash.
   */
  public function getblocktransactionssize($hash) {

    $res = $this->getblock($hash);

    $tx_size = 0;
    for ($i = 0; $i < count($res["tx"]); $i++) {
      $tx_raw = $this->getrawtransaction($res["tx"][$i]);
      $tx_decoded = $this->decoderawtransaction($tx_raw);
      $tx_size += $tx_decoded["size"];
    }

    if ($tx_size)
      return $tx_size;
  }


  /*
   * Return transactions amount in a block by block hash.
   */
  public function getblocktransactionsamount($hash) {

    $res = $this->getblock($hash);

    $tx_amount = 0;
    for ($i = 0; $i < count($res["tx"]); $i++) {
      $tx_raw = $this->getrawtransaction($res["tx"][$i]);
      $tx_decoded = $this->decoderawtransaction($tx_raw);

      for ($j = 0; $j < count($tx_decoded["vout"]); $j++) {
        $tx_amount += $tx_decoded[$j]["value"];
      }
    }

    if ($tx_amount)
      return $tx_amount;
  }


  /*
   * Returns unconfirmed transactions count in mempool
   * based on the original getmempoolinfo set of data.
   */
  public function getmempoolinfosize() {

    $res = $this->getmempoolinfo();
    if ($res)
      return $res["size"];
  }


  /*
   * Returns miner reward for the block based on
   * coinbase transaction amount.
   */
  public function getreward($height = 0, $flag = 'height') {

    if ( $flag == 'height') {
      if( empty($height) ) $height = $this->getblockcount();
      $hash = $this->getblockhash($height);
    } else {
      $hash = $height;
    }

    $blockdata = $this->getblock($hash);

    $chain_coinbase_tx = $blockdata['tx'][0];

    $transactiondata = $this->getrawtransaction($chain_coinbase_tx, true);
    $chain_coinbase_tx_vouts = count($transactiondata['vout']);

    $blockreward = 0;
    for ($i = 0; $i <= $chain_coinbase_tx_vouts; $i++) {
      $blockreward += $transactiondata['vout'][$i]['value'];
    }

    return $blockreward;
  }


  /*
   * Returns time spent for solving last block based on
   * difference between current block timestamp and previous
   * block timestamp.
   */
  public function getsolvetime($height = 0) {

    if ( empty($height) ) $height = $this->getblockcount();

    $current_block = $height;
    $current_block_time = $this->getblock($this->getblockhash($current_block))['time'];
    $previous_block = ((($current_block - 1) > -1) ? ($current_block - 1) : 0);
    $previous_block_time = $this->getblock($this->getblockhash($previous_block))['time'];

    return $current_block_time - $previous_block_time;
  }


  /*
   * Returns current emission amount in circulation
   * based on the original gettxoutsetinfo set of
   * data by extracting 'total_amount' field.
   */
  public function getsupply() {

    $res = $this->gettxoutsetinfo();
    if ($res)
      return $res['total_amount'];
  }


  /*
   * Returns transactions amount as the sum of all VOUTs
   * based on the mix of getrawtransaction and
   * decoderawtransaction functions.
   */
  public function gettransactionamount($txid) {

    $rawtx = $this->getrawtransaction($txid, false);
    $tx = $this->decoderawtransaction($rawtx)['vout'];

    $amount = 0;
    for ($i = 0; $i < count($tx); $i++) {
      $amount += $tx[$i]['value'];
    }

    return $amount;
  }


  /*
   * Returns transactions count based on the original
   * gettxoutsetinfo set of data by extracting
   * 'transactions' field.
   */
  public function gettransactioncount() {

    $res = $this->gettxoutsetinfo();
    if ($res)
      return $res['transactions'];
  }


  /**********************************/
  /* Transactions-related functions */
  /**********************************/


  public function gettransactionsize($txid) {}

}

?>
