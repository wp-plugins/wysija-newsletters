<?php
defined('WYSIJA') or die('Restricted access');


/**
 * class managing the admin vital part to integrate wordpress
 */
class WYSIJA_help_backloader extends WYSIJA_help{
    
    function WYSIJA_help_backloader(){
        
        parent::WYSIJA_help();

    }
    function initLoad(&$controller){
        wp_enqueue_style('wysija-admin-css', WYSIJA_URL."/css/admin.css");
            
            /* default script on all wysija interfaces in admin */
            wp_enqueue_script('wysija-admin-if', WYSIJA_URL."/js/admin-wysija.js", array( 'jquery' ), true);
            

            if(!$controller->jsTrans){
                $controller->jsTrans["selecmiss"]=__('Please make a selection first!',WYSIJA);
                $controller->jsTrans["suredelete"]=__('Deleting a list will not delete any subscribers.',WYSIJA);    
            }
            
            $controller->js[]='wysija-admin-ajax';
            $controller->js[]='thickbox';
            wp_enqueue_style( 'thickbox' );
    }
    function loadScriptsStyles($pagename,$dirname,$urlname,&$controller) {
        /*test if there is a page specific js and include it if ther is */
            if(file_exists($dirname."js".DS."admin-".$pagename.".js")) 
                wp_enqueue_script('wysijashop-admin-'.$pagename, $urlname."/js/admin-".$pagename.".js");
            /* test also the php format in case */
            if(file_exists($dirname."js".DS."admin-".$pagename.".php"))
                wp_enqueue_script('wysijashop-admin-'.$pagename, $urlname."/js/admin-".$pagename.".php");
            
            if(file_exists($dirname."css".DS."admin-".$pagename.".css"))
                wp_enqueue_style('wysijashop-admin-'.$pagename."css", $urlname."/css/admin-".$pagename.".css");

            
            if(isset($_GET['action'])){
                /*test if there is an action specific js and include it if there is */
                if(file_exists($dirname."js".DS."admin-".$pagename."-".$_REQUEST['action'].".js"))
                    wp_enqueue_script('wysijashop-admin-'.$pagename."-".$_REQUEST['action'], $urlname."/js/admin-".$pagename."-".$_REQUEST['action'].".js");
                /* test also the php format in case */
                if(file_exists($dirname."js".DS."admin-".$pagename."-".$_REQUEST['action'].".php"))
                    wp_enqueue_script('wysijashop-admin-'.$pagename."-".$_REQUEST['action'], $urlname."/js/admin-".$pagename."-".$_REQUEST['action'].".php");
                
                if(file_exists($dirname."css".DS."admin-".$pagename."-".$_REQUEST['action'].".css"))
                    wp_enqueue_style('wysijashop-admin-'.$pagename."-".$_REQUEST['action'], $urlname."/css/admin-".$pagename."-".$_REQUEST['action'].".css");
                
                /* add form validators script for add and edit */
                if($_GET['action']=="edit" || $_GET['action']=="add")
                    $controller->js[]="wysija-validator";

            }else{
                if(file_exists($dirname."js".DS."admin-".$pagename."-default".".js"))
                    wp_enqueue_script('wysijashop-admin-'.$pagename."-default", $urlname."/js/admin-".$pagename."-default.js");
                if($pagename!="config")  wp_enqueue_script('wysijashop-admin-list');
            }
        return true;
    }
   
}

