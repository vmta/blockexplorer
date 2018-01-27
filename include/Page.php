<?php



/*
 * Page class for Umkoin (SHA256) crypto currency
 *
 * Author:  vmta
 * Date:    10 Jan 2018
 *
 * Version_Major: 0
 * Version_Minor: 0
 * Version_Build: 1
 *
 * Changelog:
 *
 */



class Page {



  /*
   * $block variable of type Block is the holder of block object,
   * which is a PHP JSON API connector to the underlying daemon.
   *
   */
  private $block;



  /*
   * $html_navbar_string variable of type string is the holder of dynamic
   * html code to be later displayed in user browser as navigation bar.
   *
   */
  private $html_navbar_string;



  /*
   * $html_content_string variable of type string is the holder of dynamic
   * html code to be later displayed in user browser as main body content.
   *
   */
  private $html_content_string;



  /*
   * $html_footer_string variable of type string is the holder of dynamic
   * html code to be later displayed in user browser as footer.
   *
   */
  private $html_footer_string;



  /* Class constructor */
  public function __construct($block) {

    $this->block = $block;
    $this->html_navbar_string = "";
    $this->html_content_string = "";
    $this->html_footer_string = "";

  }



  /* Provide human readable number formatting */
  public function prettynum($val, $index = "K", $precision = 4) {

    $res = "";

    switch ($index) {

      case "K":
        $res = round(($val / 1024), $precision) . " K";
        break;

      case "M":
        $res = round(($val / pow(1024, 2)), $precision) . " M";
        break;

      case "G":
        $res = round(($val / pow(1024, 3)), $precision) . " G";
        break;

      case "P":
        $res = round(($val / pow(1024, 4)), $precision) . " P";
        break;

    }

    return $res;

  }



  /* Construct, populate and return a list of mempool transactions */
  public function gettransactionslist() {

    $txids = $this->block->getrawmempool();
    $txids_length = count($txids);
    $html_str = "";

    if ($txids_length > 0) {

      $transactions = [];
      $transactions_value = [];

      for ($i = $txids_length - 1; $i >= 0; $i--) {

        $transactions[$i] = $this->block->getmempoolentry($txids[$i]);
        $transactions_value[$i] = $this->block->getTxAmount($txids[$i]);

      }

      for($i = $txids_length - 1; $i >= 0; $i--) {

        $html_str .= "<tr><td>" .
                     date("d.m.Y H:i:s", $transactions[$i]["time"]) .
                     "</td><td>" .
                     $transactions_value[$i] .
                     "</td><td>" .
                     $transactions[$i]["fee"] .
                     "</td><td>" .
                     $transactions[$i]["size"] .
                     "</td><td>" .
//                     "<a href='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?txid=" . $transactions[$i]["wtxid"] . "'>" . $transactions[$i]["wtxid"] . "</a>" .
                     $transactions[$i]["wtxid"] .
                     "</td></tr>";
        }

      } else {

        $html_str = "<tr><td colspan=5>There are no unconfirmed transactions.</td></tr>";

      }

      return $html_str;
  }



  /* Construct, populate and return a list of blocks */
  public function displayBlockList() {

    $blocks = [];
    $blockchain_length = $this->block->getblockcount();
    $html_str = "";

    for ($i = $blockchain_length; $i > (($blockchain_length - 20) > 0 ? ($blockchain_length - 20) : 0); $i--) {

      $blocks[$i] = $this->block->getblock($this->block->getblockhash($i));

    }

    if (count($blocks) > 0) {

      for ($i = $blockchain_length; $i > (($blockchain_length - 20) > 0 ? ($blockchain_length - 20) : 0); $i--) {

        $html_str .= "<tr><td>" .
                     $blocks[$i]["height"] .
                     "</td><td>" .
                     date("d.m.Y H:i:s", $blocks[$i]["time"]) .
                     "</td><td>" .
                     $blocks[$i]["size"] .
                     "</td><td>" .
                     "<a href='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?blockhash=" . $blocks[$i]["hash"] . "'>" . $blocks[$i]["hash"] . "</a>" .
                     "</td><td>" .
                     $blocks[$i]["difficulty"] .
                     "</td><td>" .
                     count($blocks[$i]["tx"]) .
                     "</td></tr>";

      }

    } else {

      $html_str = "<tr><td colspan=6>There are no blocks yet.</td></tr>";

    }

    return $html_str;
  }



  /*
   * Prepare transaction inputs/outputs table.
   */
  public function displayTxTableInOut($hash, $flag = "vout") {

    $str = "";
    $res = $this->block->getrawtransaction($hash, true);

    if ($flag == "vin") {
      for ($i = 0; $i < $this->block->getTxCountInOut($hash, $flag); $i++) {
        if (!isset($res[$flag][$i]["coinbase"])) {
          $addresses = $this->block->getTxVinAddress($res[$flag][$i]["txid"], $res[$flag][$i]["vout"]);
          for ($j = 0; $j < count($addresses); $j++) {
            $addresses_str = "<div>" . $addresses[$j] . "</div>";
          }
          $str .= "<tr>" .
                  "<td>" . $this->block->getTxVinAmount($res[$flag][$i]["txid"], $res[$flag][$i]["vout"]) . " UMK</td>" .
                  "<td><a href='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?txid=" . $res[$flag][$i]["txid"] . "'>" . $res[$flag][$i]["txid"] . "</a></td>" .
                  "<td>" . (isset($addresses_str) ? $addresses_str : "") . "</td>" .
                  "</tr>";
        } else {
          $str .= "<tr>" .
                  "<td>" . $this->block->getreward($res["blockhash"], "hash") . " UMK</td>" .
                  "<td>Block reward</td>" .
                  "<td>Coinbase</td>" .
                  "</tr>";
        }
      }
    } else {
      for ($i = 0; $i < $this->block->getTxCountInOut($hash, $flag); $i++) {
        if (isset($res[$flag][$i]["scriptPubKey"]["addresses"])) {
          for ($j = 0; $j < count($res[$flag][$i]["scriptPubKey"]["addresses"]); $j++) {
            $addresses_str = "<div>" . $res[$flag][$i]["scriptPubKey"]["addresses"][$j] . "</div>";
          }
        }
        $str .= "<tr>" .
                "<td>" . $res[$flag][$i]["value"] . " UMK</td>" .
                "<td>" . $res[$flag][$i]["scriptPubKey"]["hex"] . "</td>" .
                "<td>" . (isset($addresses_str) ? $addresses_str : "") . "</td>" .
                "</tr>";
      }
    }

    return $str;
  }



  /*
   * Prepare and return html navigation bar.
   *
   */
  public function getNavigationBar() {

    $this->html_navbar_string .= "<div class='navbar navbar-default navbar-fixed-top' role='navigation'>" .
      "<div class='container'>" .
        "<div class='navbar-header'>" .
          "<button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>" .
            "<span class='sr-only'>Menu</span>" .
            "<span class='icon-bar'></span>" .
            "<span class='icon-bar'></span>" .
            "<span class='icon-bar'></span>" .
          "</button>" .
          "<a class='navbar-brand ' href='/'>" .
            "<span id='coinIcon'>&#85;</span> <strong>UMK</strong>oin" .
          "</a>" .
          "<div id='stats_updated'><i class='fa fa-bolt'></i></div>" .
        "</div>" .
        "<div class='collapse navbar-collapse'>" .
          "<ul class='nav navbar-nav navbar-left explorer_menu'>" .
            "<li>" .
              "<a class='hot_link' data-page='home.php' href='#'>" .
                "<i class='fa fa-cubes' aria-hidden='true'></i> Block Explorer" .
              "</a>" .
            "</li>" .
//            "<li>" .
//              "<a class='hot_link' data-page='pools.php' href='#pools'>" .
//                "<i class='fa fa-gavel' aria-hidden='true'></i> Pools" .
//              "</a>" .
//            "</li>" .
//            "<li>" .
//              "<a class='hot_link' data-page='api.php' href='#api'>" .
//                "<i class='fa fa-code' aria-hidden='true'></i> API" .
//              "</a>" .
//            "</li>" .
//            "<li style='display:none;'>" .
//              "<a class='hot_link' data-page='blockchain_block.php' href='#blockchain_block'>" .
//                "<i class='fa fa-cubes'></i> Block" .
//              "</a>" .
//            "</li>" .
//            "<li style='display:none;'>" .
//              "<a class='hot_link' data-page='blockchain_transaction.php' href='#blockchain_transaction'>" .
//                "<i class='fa fa-cubes'></i> Transaction" .
//              "</a>" .
//            "</li>" .
//            "<li style='display:none;'>" .
//              "<a class='hot_link' data-page='blockchain_payment_id.php' href='#blockchain_payment_id'>" .
//                "<i class='fa fa-cubes'></i> Transactions by Payment ID" .
//              "</a>" .
//            "</li>" .
//            "<!--<li>" .
//              "<a style='display:none;' class='hot_link' data-page='support.php' href='#support'>" .
//                "<i class='fa fa-comments'></i> Help" .
//              "</a>" .
//            "</li>//-->" .
          "</ul>" .
          "<div class='nav col-md-6 navbar-right explorer-search'>" .
            "<div class='input-group'>" .
              "<input class='form-control' placeholder='Search by block height / hash, transaction hash, payment id' id='txt_search'>" .
              "<span class='input-group-btn'>" .
                "<button class='btn btn-default' type='button' id='btn_search'>" .
                  "<span><i class='fa fa-search'></i> Search</span>" .
                "</button>" .
              "</span>" .
            "</div>" .
          "</div>" .
        "</div>" .
      "</div>" .
    "</div>";

    return $this->html_navbar_string;

  }



  /*
   * Prepare and return html main body content page.
   *
   */
  public function getContent($req, $reqval) {

    switch ($req) {

        case "blockhash":

          $this->html_content_string .= "<div id='content>" .
            "<div class='container'>" .
              "<div id='page'>" .
                "<h2><i class='fa fa-cube fa-fw' aria-hidden='true'></i> Block <small id='block.hash' style='word-break: break-all;'>" . $reqval . "</small></h2>" .
                "<div class='row'>" .
                  "<div class='col-md-6 stats'>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Block index in the chain, counting from zero (i.e. genesis block).'><i class='fa fa-question-circle'></i></span> Height: <span id='block_height'><span id='block.height'>" . $this->block->getBlockHeight($reqval) . "</span></span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Block timestamp displayed as UTC. The timestamp correctness it up to miner, who mined the block.'><i class='fa fa-question-circle'></i></span> Timestamp: <span id='block.timestamp'>" . date("M d, Y H:i:s", $this->block->getBlockTime($reqval)) . "</span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='How difficult it is to find a solution for the block. More specifically, it`s mathematical expectation for number of hashes someone needs to calculate in order to find a correct nonce value solving the block.'><i class='fa fa-question-circle'></i></span> Difficulty: <span id='block.difficulty'>" . $this->block->getBlockDifficulty($reqval) . "</span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Number of transactions in the block, including coinbase transaction (which transfers block reward to the miner).'><i class='fa fa-question-circle'></i></span> Transactions: <span id='block.transactions'>" . $this->block->getBlockTxCount($reqval) . "</span></a></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Cumulative amount of coins issued by all the blocks in blockchain from the genesis and up to this block.'><i class='fa fa-question-circle'></i></span> Total coins in the network: <span id='block.totalCoins'>" . $this->block->getsupply() . "</span></div>" .
                    "<div>" .
                      "<span data-toggle='tooltip' data-placement='right' data-original-title='Cumulative number of transactions in the blockchain, from the genesis block and up to this block.'>" .
                        "<i class='fa fa-question-circle'></i>" .
                      "</span> Total transactions in the network: " .
                      "<span id='block.totalTransactions'>" .
                        $this->block->getTxCount() .
                        (($this->block->getUnconfirmedTxCount() > 0) ? ' (+'.$this->block->getUnconfirmedTxCount().' unconfirmed)' : '') .
                      "</span>" .
                    "</div>" .
                  "</div>" .

                  "<div class='col-md-6 stats'>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Cumulative size of all transactions in the block, including coinbase. In case it's exceeding 'effective txs median' the reward penalty occurs and therefore miner receives less reward.'><i class='fa fa-question-circle'></i></span> Total transactions size, bytes: <span id='block.transactionsSize'>" . $this->block->getblocktransactionssize($reqval) . "</span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Size of the whole block, i.e. block header plus all transactions.'><i class='fa fa-question-circle'></i></span> Total block size, bytes: <span id='block.blockSize'>" . $this->block->getBlockSize($reqval) .  "</span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Sum of values for all transactions in the block.'><i class='fa fa-question-circle'></i></span> Transactions amount: <span id='block.totalAmount'>" . $this->block->getBlockAmount($reqval) . "</span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Sum of fees for all transactions in the block.'><i class='fa fa-question-circle'></i></span> Transactions fee: <span id='block.transactionsFee'>" . $this->block->getBlockFee($reqval) . "</span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Base value for calculating the block reward. Does not depend on how many transactions are included into the block. Also, this is how many coins the miner would receive if the block contains only coinbase transaction.'><i class='fa fa-question-circle'></i></span> Base reward: <span id='block.baseReward'>" . $this->block->getbasereward($reqval) . "</span></div>" .
                    "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Actual amount of coins the miner received for finding the block. &lt;reward&gt; = &lt;base reward&gt; × (1 − &lt;penalty&gt;) + &lt;transactions fee&gt;'><i class='fa fa-question-circle'></i></span> Reward: <span id='block.reward'>" . $this->block->getreward($reqval, 'hash') . "</span></div>" .
                  "</div>" .
                "</div>" .

                "<h3 class='transactions'><i class='fa fa-exchange fa-fw' aria-hidden='true'></i> Transactions</h3>" .
                "<div class='table-responsive'>" .
                  "<table class='table table-hover'>" .
                  "<thead>" .
                  "<tr>" .
                    "<th><i class='fa fa-paw'></i> Hash</th>" .
                    "<th><i class='fa fa-percent'></i> Fee</th>" .
                    "<th><i class='fa fa-money'></i> Total Amount</th>" .
                    "<th><i class='fa fa-arrows'></i> Size</th>" .
                  "</tr>" .
                  "</thead>" .
                  "<tbody id='transactions_rows'>" .
                    $this->block->getblocktransactionshtml($reqval) .
                  "</tbody>" .
                  "</table>" .
                "</div>" .
              "</div>" .
            "</div>" .
          "</div>";

          break;

        case "blockheight":

          $blockhash = $this->block->getblockhash(intval($reqval));
          $this->getContent("blockhash", $blockhash);

          break;

        case "txid":

          if ($this->block->isConfirmed($reqval, "txid")) {
            $confirmtime = $this->block->getTxConfirmTime($reqval);
            $currenttime = time();
            $elapsed = $currenttime - $confirmtime;
            switch ($elapsed) {
              case $elapsed > 31536000:
                $elapsed = floor($elapsed / 31536000);
                if ($elapsed == 1)
                  $elapsed_str = $elapsed . " year ago";
                else
                  $elapsed_str = $elapsed . " years ago";
                break;
              case $elapsed > 86400:
                $elapsed = floor($elapsed / 86400);
                if ($elapsed == 1)
                  $elapsed_str = $elapsed . " day ago";
                else
                  $elapsed_str = $elapsed . " days ago";
                break;
              case $elapsed > 3600:
                $elapsed = floor($elapsed / 3600);
                if ($elapsed == 1)
                  $elapsed_str = $elapsed . " hour ago";
                else
                  $elapsed_str = $elapsed . " hours ago";
                break;
              case $elapsed > 60:
                $elapsed = floor($elapsed / 60);
                if ($elapsed == 1)
                  $elapsed_str = $elapsed . " minute ago";
                else
                  $elapsed_str = $elapsed . " minutes ago";
                break;
              default:
                if ($elapsed == 1)
                  $elapsed_str = $elapsed . " second ago";
                else
                  $elapsed_str = $elapsed . " seconds ago";
                break;
            }

            $blockconfirmed = "<div id='confirmations' style='display: block;'>" .
                              "<span data-toggle='tooltip' data-placement='right' data-original-title='The number of network confirmations.'>" .
                              "<i class='fa fa-question-circle'></i>" .
                              "</span> Confirmations: " .
                              "<span id='transaction.confirmations'>" .
                              $this->block->getTxConfirmCount($reqval) .
                              "</span>, First confirmation time: " .
                              "<span id='transaction.timestamp'>" .
                              date("M d, Y H:i:s", $this->block->getTxConfirmTime($reqval)) .
                              "</span> (<time class='transaction-timeago'>" .
                              (isset($elapsed_str) ? $elapsed_str : "") .
                              "</time>)</div>";
          }

          $this->html_content_string .= "<h2><i class='fa fa-exchange fa-fw' aria-hidden='true'></i> Transaction</h2>" .
            "<div class='row' id='tx_info'>" .
              "<div class='col-md-12 stats'>" .
                "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Unique fingerprint of the transaction.'><i class='fa fa-question-circle'></i></span> Hash: <span id='transaction.hash' style='word-break: break-all;'>" . $this->block->getTxHash($reqval) . "</span></div>" .
                  (isset($blockconfirmed) ? $blockconfirmed : "") .
                "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Money that goes to the miner, who included this transaction into block.'><i class='fa fa-question-circle'></i></span> Fee: <span id='transaction.fee'>" . round($this->block->getTxFee($reqval)/100000000, 8) . "</span></div>" .
                "<div><span data-toggle='tooltip' data-placement='right' data-original-title='It does not mean that this is the amount that is actually transferred.'><i class='fa fa-question-circle'></i></span> Sum of outputs: <span id='transaction.amount_out'>" . $this->block->getTxSum($reqval) . "</span></div>" .
                "<div><span data-toggle='tooltip' data-placement='right' data-original-title='Size of the transaction in bytes.'><i class='fa fa-question-circle'></i></span> Size: <span id='transaction.size'>" . $this->block->getTxSize($reqval) . "</span></div>" .
              "</div>" .
            "</div>" .
            "<div id='tx_block'>" .
              "<h3><i class='fa fa-cube fa-fw' aria-hidden='true'></i> In block</h3>" .
              "<div class='row'>" .
                "<div class='col-md-12 stats'>" .
                  "<div><i class='fa fa-circle-o'></i> Hash: <span id='block.hash' style='word-break: break-all;'>" .
                    "<a href='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?blockhash=" . $this->block->getBlockHashByTxid($reqval) . "'>" . $this->block->getBlockHashByTxid($reqval) . "</a>" .
                  "</span></div>" .
                  "<div><i class='fa fa-circle-o'></i> Height: <span id='block.height'>" . $this->block->getBlockHeight($reqval, 'txid') . "</span></div>" .
                  "<div><i class='fa fa-circle-o'></i> Timestamp: <span id='block.timestamp'>" . date("M d, Y H:i:s", $this->block->getBlockTime($reqval, 'txid')) . "</span></div>" .
                "</div>" .
              "</div>" .
            "</div>" .

            "<h3 class='inputs'>Inputs (<span id='inputs_count'>" . $this->block->getTxCountInOut($reqval, 'vin') . "</span>)</h3>" .
            "<div class='table-responsive'>" .
              "<table class='table table-hover'>" .
              "<thead>" .
              "<tr>" .
                "<th><i class='fa fa-money'></i> Amount</th>" .
                "<th><i class='fa fa-paw'></i> Image</th>" .
                "<th><i class='fa fa-paw'></i> Address</th>" .
              "</tr>" .
              "</thead>" .
              "<tbody id='inputs_rows'>" .
                $this->displayTxTableInOut($reqval, "vin") .
              "</tbody>" .
              "</table>" .
            "</div>" .

            "<h3 class='outputs'>Outputs (<span id='outputs_count'>" . $this->block->getTxCountInOut($reqval, 'vout') . "</span>)</h3>" .
            "<div class='table-responsive'>" .
              "<table class='table table-hover'>" .
              "<thead>" .
              "<tr>" .
                "<th><i class='fa fa-money'></i> Amount</th>" .
                "<th><i class='fa fa-key'></i> Key</th>" .
                "<th><i class='fa fa-key'></i> Address</th>" .
              "</tr>" .
              "</thead>" .
              "<tbody id='outputs_rows'>" .
                $this->displayTxTableInOut($reqval, "vout") .
              "</tbody>" .
              "</table>" .
            "</div>";


          break;

        case "search":

          $this->html_content_string .= "Search for $reqval...";
          break;

        default:

          $this->html_content_string .= "<div id='content'>" .
            "<div class='container'>" .
              "<div id='page'>" .
                "<div class='row'>" .
                  "<div class='col-sm-12 col-md-6'>" .
                    "<div class='panel panel-default' id='network-stats'>" .

                      "<div class='panel-heading'>" .
                        "<h3 class='panel-title'><i class='fa fa-tasks fa-fw' aria-hidden='true'></i> Stats</h3>" .
                      "</div>" .

                      "<div class='panel-body'>" .
                        "<div class='row'>" .
                          "<div class='col-sm-6 col-md-6'>" .

                            "<ul class='nav nav-pills nav-stacked'>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='Blockchain height, total amount of blocks starting from zero.'>" .
                                  "<i class='fa fa-bars'></i> Height: " .
                                  "<span id='networkHeight'>" .
                                    $this->block->getblockcount() .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='The number of transactions in the network (excluding coinbase, i.e. reward for mined blocks.).'>".
                                  "<i class='fa fa fa-exchange'></i> Transactions: " .
                                  "<span id='networkTransactions'>" .
                                    $this->block->getTxCount() .
                                    (($this->block->getUnconfirmedTxCount() > 0) ? ' (+'.$this->block->getUnconfirmedTxCount().' unconfirmed)' : '') .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='Current Base Reward (for last mined block).'>" .
                                  "<i class='fa fa-certificate'></i> Reward: " .
                                  "<span id='currentReward'>" .
                                    $this->block->getreward() .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='Total UMK supply in circulation.'>" .
                                  "<i class='fa fa-money'></i> Supply: " .
                                  "<span id='totalCoins'>" .
                                    $this->block->getsupply() .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='bottom' data-original-title='Percent of already emitted coins related to Initial supply before Tail emission.'>".
                                  "<i class='fa fa-percent' aria-hidden='true'></i> Emission: " .
                                  "<span id='emissionPercent'>" .
                                    round($this->block->getsupply() * 100 / 21000000, 4) .
                                  "</span> %" .
                                "</a>" .
                              "</li>" .

                            "</ul>" .

                          "</div>" .
                          "<div class='col-sm-6 col-md-6'>" .

                            "<ul class='nav nav-pills nav-stacked'>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='Difficulty for next block. Ratio at which at the current hashing speed blocks will be mined with 10 minutes interval.'>" .
                                  "<i class='fa fa-unlock-alt'></i> Difficulty: ".
                                  "<span id='networkDifficulty'>" .
                                    $this->prettynum($this->block->getdifficulty(), "K", 4) .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='Average difficulty by last 30 blocks.'>" .
                                  "<i class='fa fa-lock'></i> Average Difficulty: ".
                                  "<span id='avgDifficulty'>" .
                                    $this->prettynum($this->block->getaveragedifficulty(), "K", 4) .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='Current estimated network hash rate. Calculated by current difficulty.'>" .
                                  "<i class='fa fa-tachometer'></i> Hash Rate: ".
                                  "<span id='networkHashrate'>" .
                                    $this->prettynum($this->block->getnetworkhashps(), "G", 3) . "H/s" .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='top' data-original-title='Average estimated network hash rate. Calculated by average difficulty.'>" .
                                  "<i class='fa fa-clock-o'></i> Average Hash Rate: ".
                                  "<span id='avgHashrate'>" .
                                    $this->prettynum($this->block->getaveragenetworkhashps(), "G", 3) . "H/s" .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                              "<li>" .
                                "<a href='#' data-toggle='tooltip' data-placement='bottom' data-original-title='Estimated block solve time at estimated network speed and current difficulty.'>" .
                                  "<i class='fa fa-circle-o-notch' aria-hidden='true'></i> Solve time: " .
                                  "<span id='blockSolveTime'>" .
                                    gmdate('H:i:s', $this->block->getsolvetime()) .
                                  "</span>" .
                                "</a>" .
                              "</li>" .

                            "</ul>" .

                          "</div>" .

                        "</div>" .
                      "</div>" .
                    "</div>" .
                  "</div>" .

//                  "<div class='col-sm-6 col-md-6'>" .
//                    "<div class='panel panel-default'>" .
//                      "<div class='panel-heading'>" .
//
//                        "<h3 class='panel-title'><i class='fa fa-area-chart' aria-hidden='true'></i> Charts" .
//                          "<span class='text-default' data-toggle='tooltip' data-placement='right' data-original-title='Difficulty based on last blocks from the list below. Block size, transactions count. Load more blocks to enlarge chart range.'>" .
//                          "<i class='fa fa-question-circle'></i></span>" .
//                        "</h3>" .
//
//                      "</div>" .
//                      "<div class='panel-body chart-wrapper'>" .
//
//                        "<canvas id='difficultyChart' height='210'></canvas>" .
//
//                      "</div>" .
//                    "</div>" .
//                  "</div>" .
                "</div>" .

                "<div class='panel panel-default'>" .
                  "<div class='panel-heading'>" .

                    "<h3 class='panel-title'><i class='fa fa-exchange fa-fw' aria-hidden='true'></i> Transaction pool " .
                      "<span id='mempool_count' class='badge'>" .
                        $this->block->getUnconfirmedTxCount() .
                      "</span>" .
                      "<span class='text-default' data-toggle='tooltip' data-placement='right' data-original-title='Recent transactions waiting to be included into a block. Once it happens a transaction gets into the blockchain and becomes confirmed.'><i class='fa fa-question-circle'></i></span>" .
                    "</h3>" .

                  "</div>" .

                  "<div class='panel-body'>" .
                    "<div class='table-responsive'>" .
                      "<table class='table table-hover' id='mem_pool_table'>" .
                      "<thead>" .
                      "<tr>" .
                        "<th width='30%'><i class='fa fa-clock-o'></i> Date &amp; time</th>" .
                        "<th width='10%'><i class='fa fa-money'></i> Amount</th>" .
                        "<th width='10%'><i class='fa fa-tag'></i> Fee</th>" .
                        "<th width='10%'><i class='fa fa-archive'></i> Size</th>" .
                        "<th width='40%'><i class='fa fa-paw'></i> Hash</th>" .
                      "</tr>" .
                      "</thead>" .
                      "<tbody id='mem_pool_rows'>" .
                        $this->gettransactionslist() .
                      "</tbody>" .
                      "</table>" .
                    "</div>" .
                  "</div>" .
                "</div>" .
              "</div>" .

              "<div class='panel panel-default'>" .
                "<div class='panel-heading'>" .
                  "<h3 class='panel-title'><i class='fa fa-chain fa-fw' aria-hidden='true'></i> Recent blocks</h3>" .
                "</div>" .
                "<div class='panel-body'>" .
                  "<div class='row'>" .
                    "<div class='col-sm-8 col-md-6 col-lg-5'>" .
                      "<div class='input-group'>" .
                        "<a id='prev-page' href='#' class='btn btn-default input-group-addon'><i class='fa fa-arrow-left' aria-hidden='true'></i> Older</a>" .
                        "<span class='input-group-addon'>№</span>" .
                        "<input id='goto-height' type='text' class='form-control' placeholder='Height'>" .
                        "<span id='goto-height-go' class='btn btn-default input-group-addon'>Go</span>" .
                        "<a id='next-page' href='#' class='btn btn-default input-group-addon disabled'>Newer <i class='fa fa-arrow-right' aria-hidden='true'></i></a>" .
                      "</div>" .
                    "</div>" .
                  "</div>" .
                  "<div class='table-responsive'>" .
                    "<table class='table table-hover'>" .
                    "<thead>" .
                    "<tr>" .
                      "<th><i class='fa fa-bars'></i> Height</th>" .
                      "<th><i class='fa fa-clock-o'></i> Date &amp; time</th>" .
                      "<th><i class='fa fa-archive'></i> Size</th>" .
                      "<th><i class='fa fa-paw'></i> Block Hash</th>" .
                      "<th><i class='fa fa-unlock-alt'></i> Difficulty</th>" .
                      "<th><i class='fa fa-bars'></i> Transactions</th>" .
                    "</tr>" .
                    "</thead>" .
                    "<tbody id='blocks_rows'>" .
                      $this->displayBlockList() .
                    "</tbody>" .
                    "</table>" .
                  "</div>" .
                "</div>" .
              "</div>" .
            "</div>" .
           "</div>" .
          "</div>" .
          "<script>" .
          "document.getElementById('goto-height-go').onclick = function () {" .
          "window.location.href = 'http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?blockheight='.concat(document.getElementById('goto-height').value);" .
          "};" .
          "</script>";

          break;

    }

    return $this->html_content_string;

  }



  /*
   * Prepare and return html footer.
   *
   */
  public function getFooter() {

    $this->html_footer_string .= "<footer>" .
      "<div class='container'>" .
        "<div class='row'>" .
          "<div class='col-lg-4 col-md-4 col-sm-6'>" .
            "<p>" .
              "<small>" .
                "&copy; 2017-" . date("Y") . " <strong>Umkoin</strong>." .
              "</small>" .
            "</p>" .
          "</div>" .
          "<div class='col-lg-4 col-md-4 col-sm-6'>" .
            "<p>" .
              "<small>" .
                "Powered by <a target='_blank' href='https://github.com/vmta/blockexplorer'><i class='fa fa-github'></i> Block Explorer</a>" .
              "</small>" .
            "</p>" .
          "</div>" .
          "<div class='col-lg-4 col-md-4 col-sm-6'>" .
            "<ul>" .
              "<li><a href='http://ua-moloko.com/'>ua-moloko.com</li>" .
            "</ul>" .
          "</div>" .
        "</div>" .
      "</div>" .
    "</footer>";

    return $this->html_footer_string;

  }


}


?>
