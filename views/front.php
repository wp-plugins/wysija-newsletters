<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_view_front extends WYSIJA_view{
    var $controller="";
    function WYSIJA_view_front(){

    }
    
    function addScripts($print=true){
        if($print){

            wp_enqueue_script('wysija-validator-lang');
            wp_enqueue_script('wysija-validator');
             
            wp_enqueue_script('wysija-front-subscribers');
            
            /* put that one in the head of your theme*/
            wp_print_styles('validate-engine-css');
        }else{
            wp_enqueue_script('wysija-validator-lang');
            wp_enqueue_script('wysija-validator');
            wp_enqueue_script('wysija-form');
            wp_enqueue_style('validate-engine-css'); 
            
        }
        
        
    }

}