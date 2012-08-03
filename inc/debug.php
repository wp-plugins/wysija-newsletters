<?php

global $wysija_queries;
$wysija_queries=array();
if(WYSIJA_DBG>1){
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    
}else{
    include_once('dBug.php');
    function dbg($mixed,$exit=true){
        if(!function_exists('is_user_logged_in')) include(ABSPATH.'wp-includes'.DS.'pluggable.php');
        if(is_user_logged_in() || isset($_GET['dbg'])){
            new dBug ( $mixed );
            if($exit) {
                global $wysija_msg,$wysija_queries;
                echo '<h2>WYSIJA MSG</h2>';
                echo '<pre>';
                print_r($wysija_msg);
                echo '</pre>';

                echo '<h2>WYSIJA QUERIES</h2>';
                echo '<pre>';
                print_r($wysija_queries);
                echo '</pre>';
                exit; 
            }
        }
    }
}


function wysija_queries(){
    if(((is_admin() && (defined('WYSIJA_ITF') && WYSIJA_ITF)) || isset($_GET['dbg'])) ){
        global $wpdb,$wysija_queries;
        echo '<div class="wysija-footer"><h2>WYSIJA QUERIES</h2>';
        echo '<pre>';
        print_r($wysija_queries);
        echo '</pre>';
        
        
        /*echo "<h2>WYSIJA QUEUE</h2>";
        $modelQ=&WYSIJA::get('queue','model');
        $wysija_queue=$modelQ->getReady();
        echo "<pre>";
        print_r($wysija_queue);
        echo "</pre>";*/
            
        echo '</div>';
    }
}

if(defined('WP_ADMIN')){
    if(defined('WYSIJA_DBG_ALL')){
        if(version_compare(phpversion(), '5.4')>= 0){
            error_reporting(E_ALL ^ E_STRICT);

        }else{
            error_reporting(E_ALL);
        }
        ini_set('display_errors', '1');
    }
    
    add_action('admin_footer','wysija_queries');
}else{
   add_action('get_footer','wysija_queries'); 
}

