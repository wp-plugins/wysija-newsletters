<?php
defined('WYSIJA') or die('Restricted access');
/**
 * class managing the admin vital part to integrate
 */
class WYSIJA_help_front extends WYSIJA_help{
    
    function WYSIJA_help_front(){
        parent::WYSIJA_help();
        /* the controller is frontend if there is any wysija data requested */
        if(defined('WYSIJA_DBG_ALL')) include_once(WYSIJA_INC."debug.php");
        if(isset($_REQUEST['wysija-page']) || isset($_REQUEST['wysija-launch'])){
            if(defined('DOING_AJAX')){
               
                add_action('wp_ajax_nopriv_wysija_ajax', array($this, 'ajax'));
            }else{
                
                
                $this->controller=&WYSIJA::get($_REQUEST['controller'],"controller");
                if(method_exists($this->controller, $_REQUEST['action'])){
                    add_action('init',array($this->controller,$_REQUEST['action']));
                    //$this->controller->$_REQUEST['action']();
                }else $this->error("Action does not exists.");
                
                if(isset($_REQUEST['wysija-page'])){
                    /* set the content filter to replace the shortcode */
                    add_filter( 'the_title', array($this,'scan_title'));
                    add_filter( 'the_content', array($this,'scan_content'));
                }
            }    
            
        }else{
           add_filter( 'the_content', array($this,'scan_content_NLform') ); 
        }
            
        
        
    }

    
    function scan_title($title){
        global $post;
        
        if(isset($post->post_title) && $post->post_title!=$title) return $title;

        $post->comment_status="close";
        $post->post_password="";
        return $this->controller->title;
    }
    
    function scan_content($content){

        $wysija_content="";
        if(isset($this->controller->subtitle))  $wysija_content="<p>".$this->controller->subtitle."</p>";
        
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