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
   * Returns miner reward for the block based on
   * coinbase transaction amount.
   */
  public function getreward($height = 0) {
    
    if ( empty($height) ) $height = $this->getblockcount();

    $hash = $this->getblockhash($height);
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
   * Returns transactions count based on the original
   * gettxoutsetinfo set of data by extracting
   * 'transactions' field.
   */
  public function gettransactioncount() {

    $res = $this->gettxoutsetinfo();
    if ($res)
      return $res['transactions'];
  }

}

?>