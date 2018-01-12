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

/* Display Navigation bar */
print_r($p->get_navbar());


/* Construct html based on page request */
//$html_content = "";
if (isset($_GET['blockhash'])) {
//  $html_content .= $p->get_content('blockhash', $_GET['blockhash'], $block);
  print_r($p->get_content('blockhash', $_GET['blockhash'], $block));
} elseif (isset($_GET['txid'])) {
//  $html_content .= $p->get_content('txid', $_GET['txid'], $block);
  print_r($p->get_content('txid', $_GET['txid'], $block));
} elseif (isset($_GET['search'])) {
//  $html_content .= $p->get_content('search', $_GET['search'], $block);
  print_r($p->get_content('search', $_GET['search'], $block));
} else {
//  $html_content .= $p->get_content('default', '', $block);
  print_r($p->get_content('default', '', $block));
}


/* Display Main content */
//print_r($html_content);


/* Display footer */
print_r($p->get_footer());

?>


</body>
</html>
