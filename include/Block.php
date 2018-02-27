<?php



/*
 * Block class for Umkoin (SHA256) crypto currency
 *
 * Author: vmta
 * Date:   14 Jan 2018
 *
 * Version_Major: 0
 * Version_Minor: 0
 * Version_Build: 4
 *
 * Changelog:
 *
 * v0.0.4 (2018-02-27)
 * Update getTxFee(). Strip return value, so that
 * there would be no need to strip it elsewhere.
 * Consequently, update getBlockFee() and
 * getblocktransactionshtml().
 *
 * v0.0.3 (2018-02-27)
 * Update getblocktransactionshtml(). Add td css style
 * attribute to break and wrap long hash word.
 *
 * v0.0.2 (2018-01-27)
 * Update getTxCount(). Use parent getchaintxstats() instead
 * of gettxoutsetinfo().
 *
 * v0.0.1 (2018-01-14)
 * Initial code
 *
 */



class Block extends Api {

  public function __construct($server, $auth, $debug = false) {
    parent::__construct($server, $auth, $debug);
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



  /**********************************/
  /*    Block-related functions     */
  /**********************************/


  /*
   * Return cumulative value amount for the given block.
   */
  public function getBlockAmount($hash, $flag = 'hash', $inout = 'out') {

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
    }

    return $amount;
  }


  /*
   * Returns difficulty of the block by its hash.
   */
  public function getBlockDifficulty($blockhash) {

    $res = $this->getblock($blockhash);
    if ($res)
      return $res["difficulty"];
  }


  /*
   * Returns cumulative fee amount for the given block.
   */
  public function getBlockFee($blockhash, $flag = 'hash') {

    if ($flag != 'hash') {
      $blockhash = $this->getblockhash($blockhash);
    }

    $fees = 0;
    $txids = $this->getblock($blockhash)["tx"];

    for ($i = 0; $i < count($txids); $i++) {
      $fees += $this->getTxFee($txids[$i]);
    }

    return $fees;
  }


  /*
   * Returns hash of the block which contains requested
   * transaction based on the original getrawtransaction
   * set of data by extracting 'blockhash' field.
   */
  public function getBlockHashByTxid($txid) {

    $res = $this->getrawtransaction($txid, true);
    if ($res)
      return $res["blockhash"];
  }


  /*
   * Returns height of the block by its hash.
   */
  public function getBlockHeight($hash, $flag = "block") {

    if ($flag == "txid")
      $res = $this->getblock($this->getrawtransaction($hash, true)["blockhash"]);
    else
      $res = $this->getblock($hash);

    if ($res)
      return $res["height"];
  }


 /*
  * Returns size of the block by its hash.
  */
  public function getBlockSize($blockhash) {

    $res = $this->getblock($blockhash);
    if($res)
      return $res["size"];
  }


  /*
   * Returns time of the block by block hash.
   */
  public function getBlockTime($blockhash, $flag = "block") {

    if ($flag == "txid")
      $res = $this->getblock($this->getBlockHashByTxid($blockhash));
    else
      $res = $this->getblock($blockhash);

    if ($res)
      return $res["time"];
  }


  /*
   * Return transactions count in a block by block hash.
   */
  public function getBlockTxCount($blockhash) {

    $res = $this->getblock($blockhash);
    if ($res)
      return count($res["tx"]);
  }


  /*
   * Return html representation of transactions in a given block.
   */
  public function getblocktransactionshtml($hash) {

    $res = $this->getblock($hash);
    $html_str = "";

    for ($i = 0; $i < count($res["tx"]); $i++) {

      $tx_fee = 0;
      $tx_amount = 0;

      /* Get transaction */
      $tx_raw = $this->getrawtransaction($res["tx"][$i], true);

      /* Retrieve transaction fee */
      $tx_fee = $this->getTxFee($res["tx"][$i]);

      /* Calculate transaction total value */
      for ($j = 0; $j < count($tx_raw["vout"]); $j++) {
        $tx_amount += $tx_raw["vout"][$j]["value"];
      }

      /* Retrieve transaction size */
      $tx_size = $tx_raw["size"];

      $html_str .= "<tr>" .
                   "<td style='word-wrap: break-word; word-break: break-all; white-space: normal;'>" .
                   "<a href='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?txid=" . $tx_raw["txid"] . "'>" . $tx_raw["hash"] . "</a>" .
                   "</td><td>" .
                     $tx_fee .
                   "</td><td>" .
                     $tx_amount .
                   "</td><td>" .
                     $tx_size .
                   "</td>" .
                   "</tr>";
    }

    return $html_str;
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
  public function getUnconfirmedTxCount() {

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
   * Returns true if block is confirmed, false otherwise.
   */
  public function isConfirmed($hash, $flag = "block") {

    if ($flag == "txid")
      $res = $this->getrawtransaction($hash, true);
    else
      $res = $this->getblock($hash);

    if ($res["confirmations"])
      return true;

    return false;
  }



  /**********************************/
  /* Transactions-related functions */
  /**********************************/


  /*
   * Returns transactions amount as the sum of all VOUTs
   * based on the mix of getrawtransaction and
   * decoderawtransaction functions.
   */
  public function getTxAmount($txid) {

    $rawtx = $this->getrawtransaction($txid, false);
    $tx = $this->decoderawtransaction($rawtx)['vout'];

    $amount = 0;
    for ($i = 0; $i < count($tx); $i++) {
      $amount += $tx[$i]['value'];
    }

    return $amount;
  }


  /*
   * Returns number of block confirmations which holds
   * the requested transaction based on the original
   * getrawtransaction set of data by extracting
   * 'confirmations' field.
   */
  public function getTxConfirmCount($txid) {

    $res = $this->getrawtransaction($txid, true);
    if ($res)
      return $res['confirmations'];
  }


  /*
   * Returns seconds since 01.01.1970 a block holding
   * the requested transaction was first confirmed
   * based on the original getrawtransaction set of
   * data by extracting 'blocktime' field.
   */
  public function getTxConfirmTime($txid) {

    $res = $this->getrawtransaction($txid, true);
    if ($res)
      return $res['blocktime'];
  }


  /*
   * Returns transactions count based on the original
   * getchaintxstats set of data by extracting
   * 'txcount' field.
   */
  public function getTxCount() {

    $res = $this->getchaintxstats();
    if ($res)
      return $res['txcount'];
  }


  /*
   * Returns number of transaction inputs/outputs.
   */
  public function getTxCountInOut($txid, $flag = "vout") {

    $res = $this->getrawtransaction($txid, true);
    if ($res)
      return count($res[$flag]);
  }


  /*
   * Returns transaction fee by deducting sum of all
   * transaction outputs from sum of all inputs.
   */
  public function getTxFee($txid) {

    $res = $this->getrawtransaction($txid, true);

    for ($i = 0; $i < $this->getTxCountInOut($txid, "vin"); $i++) {
      $txVinAmount += $this->getTxVinAmount($res["vin"][$i]["txid"], $res["vin"][$i]["vout"]) * 100000000;
    }

    $txAmount = $this->getTxSum($txid, "vout") * 100000000;

    $txFee = $txVinAmount - $txAmount;

    return ($txFee > 0) ? round($txFee/100000000, 8) : 0;
  }


  /*
   * Returns transaction hash based on the original
   * getrawtransaction set of data by extracting
   * 'hash' field.
   */
  public function getTxHash($txid) {

    $res = $this->getrawtransaction($txid, true);
    if ($res)
      return $res["hash"];
  }


  /*
   * Returns number of transactions per second based on
   * the original getchaintxstats set of data by extracting
   * 'txrate' field.
   */
  public function getTxRate() {

    $res = $this->getchaintxstats($this->getblockcount() - 1);
    if ($res)
      return round($res["txrate"], 6);
  }


  /*
   * Returns transaction size based on the original
   * getrawtransaction set of data by extracting
   * 'size' field.
   */
  public function getTxSize($txid) {

    $res = $this->getrawtransaction($txid, true);
    if ($res)
      return $res["size"];
  }


  /*
   * Returns sum of transaction amounts depending
   * on the flag, whether VIN or VOUT, by default
   * assume only VOUTs are of interest.
   */
  public function getTxSum($txid, $flag = "vout") {

    $res = $this->getrawtransaction($txid, true);

    $txsum = 0;
    for ($i = 0; $i < $this->getTxCountInOut($txid, $flag); $i++) {

      $txsum += $res[$flag][$i]["value"];

    }

    if ($txsum)
      return $txsum;
  }


  /*
   * Returns transaction vout addresses based on
   * the original getrawtransaction set of data.
   */
  public function getTxVinAddress($txid, $vout = 0) {

    $res = $this->getrawtransaction($txid, true);

    for ($i = 0; $i < count($res["vout"][$vout]["scriptPubKey"]["addresses"]); $i++) {
      $addresses[] = $res["vout"][$vout]["scriptPubKey"]["addresses"][$i];
    }

    return (isset($addresses) ? $addresses : null);
  }


  /*
   * Returns transaction vout amount based on the
   * original getrawtransaction set of data by
   * extracting 'value' field.
   */
  public function getTxVinAmount($txid, $vout = 0) {

    $res = $this->getrawtransaction($txid, true);
    if ($res)
      return $res["vout"][$vout]["value"];
  }

}

?>
