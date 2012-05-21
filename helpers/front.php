<?php
defined('WYSIJA') or die('Restricted access');

class WYSIJA_help_front extends WYSIJA_help{
    function WYSIJA_help_front(){
        parent::WYSIJA_help();
        
        
        if(isset($_REQUEST['wysija-page']) || isset($_REQUEST['wysija-launch'])){
            if(defined('WYSIJA_DBG')){
                include_once(WYSIJA_INC."debug.php");
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }else{
                if(defined("WP_DEBUG") && !WP_DEBUG){
                    error_reporting(0);
                    ini_set('display_errors', '0');
                }
            }
            if(defined('DOING_AJAX')){
                add_action('wp_ajax_nopriv_wysija_ajax', array($this, 'ajax'));
            }else{
                
                $this->controller=&WYSIJA::get($_REQUEST['controller'],"controller");
                if(method_exists($this->controller, $_REQUEST['action'])){
                    add_action('init',array($this->controller,$_REQUEST['action']));

                }else $this->error("Action does not exists.");
                if(isset($_REQUEST['wysija-page'])){
                    
                    add_filter( 'the_title', array($this,'scan_title'));
                    add_filter( 'the_content', array($this,'scan_content'));
                    if(isset($_REQUEST['message_success'])){
                        add_filter( 'the_content', array($this,'scan_content_NLform') ); 
                    }
                }
            }    
        }else{
           add_filter( 'the_content', array($this,'scan_content_NLform') ); 
        }
        
    }
    
    function scan_title($title){
        global $post;
        if(trim($title)==trim(single_post_title( '', false ))){
            $post->comment_status="close";
            $post->post_password="";
            return $this->controller->title;
        }else{
            return $title;
        }
        
        
    }
    function scan_content($content){
        $wysija_content="";
        if(isset($this->controller->subtitle))  $wysija_content=$this->controller->subtitle;
        return str_replace("[wysija_page]",$wysija_content,$content);
    }
    function scan_content_NLform($content){

        preg_match_all('/\<div class="wysija-register">(.*?)\<\/div>/i',$content,$matches);
        foreach($matches[1] as $key => $mymatch){
            if($mymatch){
                $widgetdata=unserialize(base64_decode($mymatch));
                $widgetNL=new WYSIJA_NL_Widget(1);
                $contentTABLE= $widgetNL->widget($widgetdata,$widgetdata); 
                $content=str_replace($matches[0][$key],$contentTABLE,$content);
            }//endif
        }//endforeach

        return $content;
    }
}   