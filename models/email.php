<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_model_email extends WYSIJA_model{
    
    var $pk="email_id";
    var $table_name="email";
    var $columns=array(
        'email_id'=>array("auto"=>true),
        'campaign_id' => array("type"=>"integer"),
        'subject' => array("req"=>true),
        'body' => array("req"=>true,"html"=>1),
        'from_email' => array("req"=>true),
        'from_name' => array("req"=>true),
        'replyto_email' => array(),
        'replyto_name' => array(),
        'attachments' => array(),
        'status' => array("type"=>"integer"),
        /*draft :0
          sending:1
          sent:2
          paused:-1*/
        'type' => array("type"=>"integer"),
        'number_sent'=>array("type"=>"integer"),
        'number_opened'=>array("type"=>"integer"),
        'number_clicked'=>array("type"=>"integer"),
        'number_unsub'=>array("type"=>"integer"),
        'number_bounce'=>array("type"=>"integer"),
        'number_forward'=>array("type"=>"integer"),
        'sent_at' => array("type"=>"date"),
        'created_at' => array("type"=>"date"),
        'params' => array(),
        'wj_data' => array(),
        'wj_styles' => array()
    );
    /*var $escapeFields=array('subject','body');
    var $escapingOn=true;*/
    
    
    
    
    function WYSIJA_model_email(){
        $this->WYSIJA_model();
    }
    
    function beforeInsert(){
        $this->checkParams();
        $modelConfig=&WYSIJA::get("config","model");
        if(!isset($this->values["from_email"])) $this->values["from_email"]=$modelConfig->getValue("from_email");
        if(!isset($this->values["from_name"])) $this->values["from_name"]=$modelConfig->getValue("from_name");
        if(!isset($this->values["replyto_email"])) $this->values["replyto_email"]=$modelConfig->getValue("replyto_email");
        if(!isset($this->values["replyto_name"])) $this->values["replyto_name"]=$modelConfig->getValue("replyto_name");
        
        return true;
    }  
    
    function beforeUpdate(){
        $this->checkParams();
        
        return true;
    }  
    
    function checkParams(){

        if(isset($this->values["params"]) && is_array($this->values["params"])){
            $this->values["params"]=base64_encode(serialize($this->values["params"]));
        }
    }
    
    function getPreviewLink($email_id,$text=false,$urlOnly=true){
        if(!$text) $text=__("View",WYSIJA);

        $this->reset();
        $modelConf=&WYSIJA::get("config","model");

        $params=array(
            'wysija-page'=>1,
            'controller'=>"email",
            'action'=>"view",
            'email_id'=>$email_id,
            );


        $fullurl=WYSIJA::get_permalink($modelConf->getValue("confirm_email_link"),$params);
        if($urlOnly) return $fullurl;
        return '<a href="'.$fullurl.'" target="_blank">'.$text.'</a>';

    }


}
