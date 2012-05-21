<?php

define("WYSIJA_DBG",true);

global $wysija_queries;
$wysija_queries=array();
function dbg($mixed,$exit=true){
    if(!function_exists('is_user_logged_in')) include(ABSPATH."wp-includes".DS."pluggable.php");
    if(is_user_logged_in() || isset($_GET['dbg'])){
        echo "<h2>DEBUG START</h2>";
        echo "<pre>";
        print_r($mixed);
        echo "</pre>";
        if($exit) {
            global $wysija_msg,$wysija_queries;
            echo "<h2>WYSIJA MSG</h2>";
            echo "<pre>";
            print_r($wysija_msg);
            echo "</pre>";

            echo "<h2>WYSIJA QUERIES</h2>";
            echo "<pre>";
            print_r($wysija_queries);
            echo "</pre>";
            exit; 
        }
    }
}

function wysija_queries(){
    if(((is_admin() && (defined('WYSIJA_ITF') && WYSIJA_ITF)) || isset($_GET['dbg'])) ){
        global $wpdb,$wysija_queries;
        echo "<div class='wysija-footer'><h2>WYSIJA QUERIES</h2>";
        echo "<pre>";
        print_r($wysija_queries);
        echo "</pre>";
        
        
        /*echo "<h2>WYSIJA QUEUE</h2>";
        $modelQ=&WYSIJA::get('queue','model');
        $wysija_queue=$modelQ->getReady();
        echo "<pre>";
        print_r($wysija_queue);
        echo "</pre>";*/
            
        echo "</div>";
    }
}

if(defined("WP_ADMIN")) add_action('admin_footer','wysija_queries');
else add_action('get_footer','wysija_queries');
