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


/* Check if debug is of correct type (boolean) */
(is_bool($debug) === true) ? $debug : false;


/* Create a Block object */
$block = new Block($server, $auth, $debug);


/* Create a Page object */
$p = new Page($block);


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


<?php


/* HTML Content Holder */
$html_content = "";


/* Add Navigation bar to HTML Content Holder */
$html_content .= $p->getNavigationBar();


/* Add Page Content based on Request to HTML Content Holder */
if (isset($_GET['blockhash'])) {

  $html_content .= $p->getContent('blockhash', $_GET['blockhash']);

} elseif (isset($_GET['blockheight'])) {

  $html_content .= $p->getContent('blockheight', $_GET['blockheight']);

} elseif (isset($_GET['txid'])) {

  $html_content .= $p->getContent('txid', $_GET['txid']);

} elseif (isset($_GET['search'])) {

  $html_content .= $p->getContent('search', $_GET['search']);

} else {

  $html_content .= $p->getContent('default', '');

}


/* Add Footer to HTML Content Holder */
$html_content .= $p->getFooter();


/* Display HTML Content */
print_r($html_content);


?>


</body>
</html>
