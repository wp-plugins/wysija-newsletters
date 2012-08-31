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
                $this->controller=&WYSIJA::get($paramscontroller,"controller");
                if(method_exists($this->controller, $_REQUEST['action'])){
                    add_action('init',array($this->controller,$_REQUEST['action']));

                }else $this->error("Action does not exists.");
                if(isset($_REQUEST['wysija-page'])){
                    
                      add_filter('wp_title', array($this,'meta_page_title'));

                    add_filter( 'the_title', array($this,'scan_title'));
                    add_filter( 'the_content', array($this,'scan_content'));
                    if(isset($_REQUEST['message_success'])){
                        add_filter( 'the_content', array($this,'scan_content_NLform') );
                    }
                }
                if(isset($_REQUEST['wysija-page'])){
                    
                      add_filter('wp_title', array($this,'meta_page_title'));

                    add_filter( 'the_title', array($this,'scan_title'));
                    add_filter( 'the_content', array($this,'scan_content'));
                    if(isset($_REQUEST['message_success'])){
                        add_filter( 'the_content', array($this,'scan_content_NLform') );
                    }
                }
            }
        }else{
           add_filter( 'the_content', array($this,'scan_content_NLform') );
           add_filter( 'the_content', array($this,'scan_wysija_vib') );
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
    
    function scan_wysija_vib($content){
        preg_match('#\[wysija_view\](.*)\[\/wysija_view\]#Uis',$content,$matches);
        $modelEmail=&WYSIJA::get('email','model');
        $emailLoaded=$modelEmail->getOne(array('body','subject'),array('email_id'=>$matches[1]));
        $emailviewcontent='';
        $config=&WYSIJA::get('config','model');
        if($config->getValue('newsletter_view_html')){
            preg_match('#<span id="wysija_wrapper"[^>]*>(.*)</span>#Uis',$emailLoaded['body'],$extramatches);
            $emailH=&WYSIJA::get("email","helper");
            $emailviewcontent=$emailH->stripPersonalLinks($extramatches[0]);
        }else{
           $paramsurl=array(
                'wysija-page'=>1,
                'controller'=>'email',
                'action'=>'view',
               'email_id'=>$matches[1]
                );

            $fullurl=WYSIJA::get_permalink($config->getValue('confirm_email_link'),$paramsurl,true);
            $emailviewcontent='<iframe '.$onloadattr.' width="100%" height="600px" scrolling="yes" frameborder="0" src="'.$fullurl.'" name="wysija-viewinbrowser" class="iframe-wysija-vib" id="wysija-viewinbrowser" vspace="0" tabindex="0" style="position: static; top: 0pt; margin: 0px; border-style: none; height: 330px; left: 0pt; visibility: visible;" marginwidth="0" marginheight="0" hspace="0" allowtransparency="true" title="'.$emailLoaded['subject'].'"></iframe>';
        }
        $content=str_replace($matches[0],$emailviewcontent,$content);


        return $content;
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
