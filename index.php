<?php

/*
 * Blockexplorer for Umkoin (SHA256) crypto currency
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

/* Load configuration file */
require "./include/config.php";

/* Autoload and register any classes not previously loaded */
spl_autoload_register(function ($class_name){
  $classFile = "include/" . $class_name . ".php";
  if( is_file($classFile) && ! class_exists($class_name) )
    include $classFile;
});

/* Construct underlying RPC daemon connection credentials */
$server = "$proto://$host:$port";
$auth = "$rpcuser:$rpcpass";

/* Create a Block object */
$block = new Block($server, $auth);

/* Provide human readable number formatting */
function prettynum($val, $index = "K", $precision = 4) {
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

?>

<!DOCTYPE html>
<html>

<head lang="en">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="../favicon.ico">
  <link rel="icon" type="image/icon" href="../favicon.ico" >
  <title>Umkoin (&#85;) [UMK] Block Explorer</title>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-timeago/1.4.0/jquery.timeago.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-sparklines/2.1.2/jquery.sparkline.min.js"></script>
  <link href="/css/themes/dark/style.css" rel="stylesheet" id="theme_link">
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="//fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet" type="text/css">
</head>


<body>


<div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand " href="/"><span id="coinIcon">&#85;</span> <strong>UMK</strong>oin</a>
			<div id="stats_updated"><i class="fa fa-bolt"></i></div>
        </div>

        <div class="collapse navbar-collapse">

            <ul class="nav navbar-nav navbar-left explorer_menu">

			    <li>
                <a class="hot_link" data-page="home.php" href="#">
                    <i class="fa fa-cubes" aria-hidden="true"></i> Block Explorer
                </a></li>

				<li>
                <a class="hot_link" data-page="pools.php" href="#pools">
                    <i class="fa fa-gavel" aria-hidden="true"></i> Pools
                </a></li>

				<li>
                <a class="hot_link" data-page="api.php" href="#api">
                    <i class="fa fa-code" aria-hidden="true"></i> API
                </a></li>

                <li style="display:none;">
                <a class="hot_link" data-page="blockchain_block.php" href="#blockchain_block">
                    <i class="fa fa-cubes"></i> Block
                </a></li>

                <li style="display:none;">
                <a class="hot_link" data-page="blockchain_transaction.php" href="#blockchain_transaction">
                    <i class="fa fa-cubes"></i> Transaction
                </a></li>

				<li style="display:none;">
                <a class="hot_link" data-page="blockchain_payment_id.php" href="#blockchain_payment_id">
                    <i class="fa fa-cubes"></i> Transactions by Payment ID
                </a></li>
    <!--
                <li><a  style="display:none;" class="hot_link" data-page="support.php" href="#support">
                    <i class="fa fa-comments"></i> Help
                </a></li>
    //-->
            </ul>


			<div class="nav col-md-6 navbar-right explorer-search">
				<div class="input-group">
					<input class="form-control" placeholder="Search by block height / hash, transaction hash, payment id" id="txt_search">
					<span class="input-group-btn">
                    <button class="btn btn-default" type="button" id="btn_search">
						<span><i class="fa fa-search"></i> Search</span>
					</button>
                    </span>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="content">
	<div class="container">
		<div id="page">

        <div class="row">
	<div class="col-sm-12 col-md-6">
		<div class="panel panel-default" id="network-stats">
		  <div class="panel-heading">
			<h3 class="panel-title"><i class="fa fa-tasks fa-fw" aria-hidden="true"></i> Stats</h3>
		  </div>
		  <div class="panel-body">
			<div class="row">
				<div class="col-sm-6 col-md-6">
					<ul class="nav nav-pills nav-stacked">

						<li>
							<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Blockchain height, total amount of blocks starting from zero.">
								<i class="fa fa-bars"></i> Height: 
								<span id="networkHeight">
									<?php print_r($block->getblockcount()); ?>
								</span>
							</a>
						</li>

						<li>
							<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="The number of transactions in the network (excluding coinbase, i.e. reward for mined blocks).">
								<i class="fa fa fa-exchange"></i> Transactions: 
								<span id="networkTransactions">
									<?php print_r($block->gettransactioncount()); ?>
								</span>
							</a>
						</li>

						<li>
                            <a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Current Base Reward (for last mined block).">
                                <i class="fa fa-certificate"></i> Reward: 
                                <span id="currentReward">
                                    <?php print_r($block->getreward()); ?>
                                </span>
                            </a>
                        </li>

						<li>
                            <a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Total UMK supply in circulation.">
                                <i class="fa fa-money"></i> Supply: 
                                <span id="totalCoins">
                                    <?php print_r($block->getsupply()); ?>
                                </span>
                            </a>
                        </li>

						<li>
                            <a href="#" data-toggle="tooltip" data-placement="bottom" data-original-title="Percent of already emitted coins related to Initial supply before Tail emission.">
                                <i class="fa fa-percent" aria-hidden="true"></i> Emission: 
                                <span id="emissionPercent">
                                    <?php print_r(round($block->getsupply() * 100 / 21000000, 4)); ?>
                                </span> %
                            </a>
                        </li>

					</ul>
				</div>
				<div class="col-sm-6 col-md-6">
					<ul class="nav nav-pills nav-stacked">

						<li>
                            <a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Difficulty for next block. Ratio at which at the current hashing speed blocks will be mined with 4 minutes interval.">
                                <i class="fa fa-unlock-alt"></i> Difficulty: 
                                    <span id="networkDifficulty">
                                        <?php print_r(prettynum($block->getdifficulty(), "K", 4)); ?>
                                    </span>
                            </a>
                        </li>

						<li>
                            <a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Average difficulty by last 30 blocks.">
                                <i class="fa fa-lock"></i> Average Difficulty: 
                                    <span id="avgDifficulty">
                                        <?php print_r(prettynum($block->getaveragedifficulty(), "K", 4)); ?>
                                    </span>
                            </a>
                        </li>


						<li>
                            <a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Current estimated network hash rate. Calculated by current difficulty.">
                                <i class="fa fa-tachometer"></i> Hash Rate: 
                                <span id="networkHashrate">
                                      <?php print_r(prettynum($block->getnetworkhashps(), "G", 3) . "H/s"); ?>
                                </span>
                            </a>
                        </li>

						<li>
                            <a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Average estimated network hash rate. Calculated by average difficulty.">
                                <i class="fa fa-clock-o"></i> Average Hash Rate: 
                                <span id="avgHashrate">
                                    <?php print_r(prettynum($block->getaveragenetworkhashps(), "G", 3) . "H/s"); ?>
                                </span>
                            </a>
                        </li>

						<li>
                            <a href="#" data-toggle="tooltip" data-placement="bottom" data-original-title="Estimated block solve time at estimated network speed and current difficulty.">
                                <i class="fa fa-circle-o-notch" aria-hidden="true"></i> Solve time: 
                                <span id="blockSolveTime">
                                    <?php print_r(gmdate("H:i:s", $block->getsolvetime())); ?>
                                </span>
                            </a>
                        </li>
					
					</ul>
				</div>
			</div>
			
		  </div>
		</div>
	</div>

	<div class="col-sm-6 col-md-6">
		<div class="panel panel-default">
		  <div class="panel-heading">
		  
			<h3 class="panel-title"><i class="fa fa-area-chart" aria-hidden="true"></i> Charts
		  
			<span class="text-default" data-toggle="tooltip" data-placement="right" data-original-title="Difficulty based on last blocks from the list below. Block size, transactions count. Load more blocks to enlarge chart range."><i class="fa fa-question-circle"></i></span>
			</h3>
			
		  </div>
		  <div class="panel-body chart-wrapper">
			<canvas id="difficultyChart" height="210"></canvas>
		  </div>
		</div>
	</div>
</div>


<div class="panel panel-default">
  <div class="panel-heading">
  
	<h3 class="panel-title"><i class="fa fa-exchange fa-fw" aria-hidden="true"></i> Transaction pool 
	<span id="mempool_count" class="badge">
    <?php
      $counter = $block->getmempoolinfosize();
      if (!empty($counter))
        print_r($counter); 
    ?>
    </span>
	<span class="text-default" data-toggle="tooltip" data-placement="right" data-original-title="Recent transactions waiting to be included into a block. Once it happens a transaction gets into the blockchain and becomes confirmed."><i class="fa fa-question-circle"></i></span>
    </h3>
	
  </div>
  <div class="panel-body">
    
	<div>
		<div class="table-responsive">
			<table class="table table-hover" id="mem_pool_table">
				<thead>
				<tr>
					<th width="30%"><i class="fa fa-clock-o"></i> Date &amp; time</th>
					<th width="10%"><i class="fa fa-money"></i> Amount</th>
					<th width="10%"><i class="fa fa-tag"></i> Fee</th>
					<th width="10%"><i class="fa fa-archive"></i> Size</th>
					<th width="40%"><i class="fa fa-paw"></i> Hash</th>
				</tr>
				</thead>
				<tbody id="mem_pool_rows">

<?php

$txids = $block->getrawmempool();
$txids_length = count($txids);

/*
 * @TODO construct link url to get TXOUT details
 */
$html_str = "";

//var_dump($txids);
//print_r("<br />");
//print_r("<br />");
//var_dump($txids_length);
//print_r("<br />");
//print_r("<br />");

if ($txids_length > 0) {

    $transactions = [];
    $transactions_value = [];

    for ($i = $txids_length - 1; $i >= 0; $i--) {
        $transactions[$i] = $block->getmempoolentry($txids[$i]);

        $rawtx = $block->getrawtransaction($txids[$i], false);
        $tx = $block->decoderawtransaction($rawtx)['vout'];
        $amount = 0;
        for ($j = 0; $j < count($tx); $j ++) {
            $amount += $tx[$j]['amount'];
        }
        $transactions_value[$i] = $amount;
    }

    for($i = $txids_length - 1; $i >= 0; $i--) {

        $html_str .= "<tr>";
        //print_r("<tr>");
        $html_str .= "<td>";
        //print_r("<td>");
        $html_str .= date("d.m.Y H:i:s", $transactions[$i]["time"]);
        //print_r(date("d.m.Y H:i:s", $transactions[$i]["time"]));
        $html_str .= "</td>";
        //print_r("</td>");
        $html_str .= "<td>";
        //print_r("<td>");
        $html_str .= $transactions_value[$i]["value"];
        //print_r($transactions_value[$i]);
        $html_str .= "</td>";
        //print_r("</td>");
        $html_str .= "<td>";
        //print_r("<td>");
        $html_str .= $transactions[$i]["fee"];
        //print_r($transactions[$i]["fee"]);
        $html_str .= "</td>";
        //print_r("</td>");
        $html_str .= "<td>";
        //print_r("<td>");
        $html_str .= $transactions[$i]["size"];
        //print_r($transactions[$i]["size"]);
        $html_str .= "</td>";
        //print_r("</td>");
        $html_str .= "<td>";
        //print_r("<td>");
        $tx_id = $transactions[$i]["wtxid"];
        $html_str .= "<a href=\"http://" . $_SERVER['HTTP_HOST'] . "/index.php?txid=" . $tx_id . "\">" . $tx_id . "</a>";
        //print_r($transactions[$i]["wtxid"]);
        $html_str .= "</td>";
        //print_r("</td>");
        $html_str .= "</tr>";
        //print_r("</tr>");

    }
} else {
    print_r("There are no unconfirmed transactions.");
}

print_r($html_str);

?>


				</tbody>
			</table>
		</div>
	</div>
  </div>
</div>


<div class="panel panel-default">
  <div class="panel-heading">
	<h3 class="panel-title"><i class="fa fa-chain fa-fw" aria-hidden="true"></i> Recent blocks</h3>
  </div>
  <div class="panel-body">
	<div class="row">
		<div class="col-sm-8 col-md-6 col-lg-5">
			<div class="input-group">
				<a id="prev-page" href="#" class="btn btn-default input-group-addon"><i class="fa fa-arrow-left" aria-hidden="true"></i> Older</a>
				<span class="input-group-addon">№</span>
				<input id="goto-height" type="text" class="form-control" placeholder="Height">
				<a id="goto-height-go" href="#" class="btn btn-default input-group-addon">Go</a>
				<a id="next-page" href="#" class="btn btn-default input-group-addon disabled">Newer <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
			</div>
		</div>
	</div>

	<div class="table-responsive">
		<table class="table table-hover">
			<thead>
			<tr>
				<th><i class="fa fa-bars"></i> Height</th>
				<th><i class="fa fa-clock-o"></i> Date &amp; time</th>
				<th><i class="fa fa-archive"></i> Size</th>
				<th><i class="fa fa-paw"></i> Block Hash</th>
				<th><i class="fa fa-unlock-alt"></i> Difficulty</th>
				<th><i class="fa fa-bars"></i> Transactions</th>
			</tr>
			</thead>
			<tbody id="blocks_rows">

<?php

$blocks = [];
$blockchain_length = $block->getblockcount();

for ($i = $blockchain_length; $i > (($blockchain_length - 20) > 0 ? ($blockchain_length - 20) : 0); $i--) {
    $blocks[$i] = $block->getblock($block->getblockhash($i));
}
if (count($blocks) > 0) {
    for ($i = $blockchain_length; $i > (($blockchain_length - 20) > 0 ? ($blockchain_length - 20) : 0); $i--) {
        print_r("<tr>");
        print_r("<td>");
        print_r($blocks[$i]["height"]);
        print_r("</td>");
        print_r("<td>");
        print_r(date("d.m.Y H:i:s", $blocks[$i]["time"]));
        print_r("</td>");
        print_r("<td>");
        print_r($blocks[$i]["size"]);
        print_r("</td>");
        print_r("<td>");
        print_r($blocks[$i]["hash"]);
        print_r("</td>");
        print_r("<td>");
        print_r($blocks[$i]["difficulty"]);
        print_r("</td>");
        print_r("<td>");
        print_r(count($blocks[$i]["tx"]));
        print_r("</td>");
        print_r("</tr>");
    }
} else {
    print_r("There are no blocks yet.");
}

?>





			</tbody>
		</table>
	</div>

	<p class="text-center">
		<button type="button" class="btn btn-default" id="loadMoreBlocks">Load More</button>
	</p>

  </div>
</div>








        </div>
	</div>
</div>


<footer>
	<div class="container">
		<div class="row">
			<div class="col-lg-4 col-md-4 col-sm-6">
				<p>
					<small>
						&copy; 2017 <strong>Umkoin</strong>.
					</small>
				</p>
			</div>
			<div class="col-lg-4 col-md-4 col-sm-6">
				<p>
					<small>
					Powered by <a target="_blank" href="https://github.com/vmta/blockexplorer"><i class="fa fa-github"></i> Block Explorer</a>
					<br />
					<span class="text-muted">Partially based on <strong>cryptonote-universal-pool</strong><br />
					open sourced under the <a href="http://www.gnu.org/licenses/gpl-2.0.html">GPL</a></span>
					</small>
				</p>
			</div>
			<div class="col-lg-4 col-md-4 col-sm-6">
				<ul>
					<li><a href="http://ua-moloko.com/">ua-moloko.com</li>
				</ul>
				
			</div>
		</div>
    </div>
</footer>

</body>
</html>
