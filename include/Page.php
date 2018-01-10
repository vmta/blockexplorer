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


class Api {


  /*
   * $html_string variable of type string is the holder of dynamic
   * html code to be later displayed in user browser.
   * 
   */
  private $html_string;


  /* Class constructor */
  public function __construct() {
    $this->html_string = "";
  }


  /*
   * Prepare and retrieve html page.
   * 
   */
  public function get($req) {
    switch ($req) {
        case "blockhash":
          $this->html_string .= "Block info based on blockhash to be returned";
          break;
        case "txid":
          $this->html_string .= "Transaction info based on txid to be returned";
          break;
        case "search":
          $this->html_string .= "Search for something...";
          break;
        default:
          $this->html_string = "Default page";
    }
    return $this->html_string;
  }


}


?>
