<?php
defined('WYSIJA') or die('Restricted access');

class WYSIJA_help_front extends WYSIJA_help{
    function WYSIJA_help_front(){
        parent::WYSIJA_help();
        
        
        
        

        
        if(isset($_REQUEST['wysija-page']) || isset($_REQUEST['wysija-launch'])){

            if(defined('DOING_AJAX')){
                add_action('wp_ajax_nopriv_wysija_ajax', array($this, 'ajax'));
            }else{
                $paramscontroller=$_REQUEST['controller'];

                if($paramscontroller=='stat') $paramscontroller='stats';
                $this->controller=&WYSIJA::get($paramscontroller,'controller');
                if(method_exists($this->controller, $_REQUEST['action'])){
                    add_action('init',array($this->controller,$_REQUEST['action']));

                }else $this->error('Action does not exists.');
                if(isset($_REQUEST['wysija-page'])){
                    
                      add_filter('wp_title', array($this,'meta_page_title'));

                    add_filter( 'the_title', array($this,'scan_title'));
                    add_filter( 'the_content', array($this,'scan_content'));
                    if(isset($_REQUEST['message_success'])){
                        add_filter( 'the_content', array($this,'scan_content_NLform'),99 );
                    }
                }
                if(isset($_REQUEST['wysija-page'])){
                    
                      add_filter('wp_title', array($this,'meta_page_title'));

                    add_filter( 'the_title', array($this,'scan_title'));
                    add_filter( 'the_content', array($this,'scan_content'));
                    if(isset($_REQUEST['message_success'])){
                        add_filter( 'the_content', array($this,'scan_content_NLform'),99 );
                    }
                }
            }
        }else{
           add_filter( 'the_content', array($this,'scan_content_NLform'),99 );

        }

    }

    function meta_page_title(){

        return $this->controller->title;
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
        if(!empty($matches[1]) && count($matches[1])>0)   require_once(WYSIJA_WIDGETS.'wysija_nl.php');
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
