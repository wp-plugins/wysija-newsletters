<?php

//get the param from where you want
$report=$process=false;
if(isset($_REQUEST['process']) && $_REQUEST['process']){
    $process=$_REQUEST['process'];
}elseif(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SHELL']) && isset($_SERVER['argv'][2]) && $_SERVER['argv'][2]){
    $process=$_SERVER['argv'][2];
}

if(isset($_REQUEST['report']) && $_REQUEST['report']){
    $report=$_REQUEST['report'];
}elseif(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SHELL']) && isset($_SERVER['argv'][3]) && $_SERVER['argv'][3]){
    $report=$_SERVER['argv'][3];
}

if($process){
    
    /*include the needed parts of wp plus wysija*/
    $plugin_path = dirname(__FILE__);
    $wp_root = dirname(dirname(dirname($plugin_path)));

    require_once($wp_root.DIRECTORY_SEPARATOR.'wp-config.php');
    require_once($wp_root.DIRECTORY_SEPARATOR.'wp-includes'.DIRECTORY_SEPARATOR.'wp-db.php');
    require_once($plugin_path.DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."base.php");
    if(!isset($_REQUEST[WYSIJA_CRON]) || (isset($_SERVER['argv'][1]) || $_SERVER['argv'][1]!=WYSIJA_CRON)) exit; 

    //if($report) require_once(WYSIJA_INC."debug.php");
    
    switch($process){
        case 'queue':
            WYSIJA::croned_queue();
            break;
        case 'bounce':
            WYSIJA::croned_bounce();
            break;
        case 'daily':
            WYSIJA::croned_daily();
            break;
        case 'weekly':
            WYSIJA::croned_weekly();
            break;
        case 'monthly':
            WYSIJA::croned_monthly();
            break;
    }
    
    //if($report) dbg('report');
}






