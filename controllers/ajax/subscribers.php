<?php
defined('WYSIJA') or die('Restricted access');
include(dirname(dirname(__FILE__)).DS."front.php");
class WYSIJA_control_back_subscribers extends WYSIJA_control_front{
    var $model="user";
    var $view="";
    
    function WYSIJA_control_back_subscribers(){
        parent::WYSIJA_control_front();
        $data=array();
        foreach($_REQUEST['data'] as $vals){
            $data[$vals['name']]=$vals['value'];
        }
        if(isset($data['message_success'])){
            $this->messages['insert'][true]=$data['message_success'];
        }else{
            $this->messages['insert'][true]=__("User has been inserted.",WYSIJA);
        }
        
        $this->messages['insert'][false]=__("User has not been inserted.",WYSIJA);
        $this->messages['update'][true]=__("User has been updated.",WYSIJA);
        $this->messages['update'][false]=__("User has not been updated.",WYSIJA);
    }
    
    function save(){
        
        $data=array();
        foreach($_REQUEST['data'] as $vals){
            $data[$vals['name']]=$vals['value'];
        }
        
        $_REQUEST['_wpnonce']=$data['_wpnonce'];

        /* validate the email*/
        $userHelper=&WYSIJA::get("user","helper");
        if(!$userHelper->validEmail($data['wysija['.$this->model.'][email]'])) return $this->error(sprintf(__('The email %1$s is not valid!',WYSIJA),"<strong>".$data['wysija['.$this->model.'][email]']."</strong>"),true);
        
        $emailsent=true;
        $config=&WYSIJA::get('config','model');
        $dbloptin=$config->getValue('confirm_dbleoptin');
        $this->modelObj=&WYSIJA::get("user","model");
        /*Test if email already exists*/
        $resexists=$this->modelObj->exists(array('email'=>trim($data['wysija['.$this->model.'][email]'])));

        if($resexists){

            if($dbloptin){
                $this->modelObj->getFormat=OBJECT;
                $receiver=$this->modelObj->getOne(false,array('email'=>trim($data['wysija['.$this->model.'][email]'])));

                $emailsent=$userHelper->sendConfirmationEmail($receiver,true);
                
            }else $this->notice(__("Oops! You're already subscribed.",WYSIJA));
            $this->registerToLists($data,$resexists[0]["user_id"]);
            return true;
        }
        $this->modelObj->reset();

        /*record the ip and save the user*/
        $_REQUEST['wysija'][$this->model]['email']=$data['wysija['.$this->model.'][email]'];
        $_REQUEST['wysija'][$this->model]['ip']=$userHelper->getIP();
        
        /*custom fields*/
        if(isset($data['wysija['.$this->model.'][firstname]'])) $_REQUEST['wysija'][$this->model]['firstname']=$data['wysija['.$this->model.'][firstname]'];
        if(isset($data['wysija['.$this->model.'][lastname]'])) $_REQUEST['wysija'][$this->model]['lastname']=$data['wysija['.$this->model.'][lastname]'];
        
        
        $uid=parent::save();
        if(!$uid) return false;
        //if(!$uid) return false;
        /*global $EZSQL_ERROR;
        if($EZSQL_ERROR){
            foreach($EZSQL_ERROR as $sqlarray){
                $this->error('DBG: qry :'.$sqlarray['query'].' error_str :'.$sqlarray['error_str']);
            }
        }*/
        
        
        $this->registerToLists($data,$uid);
        
        /* if the doble optin is activated then we send a confirmation email */
        if($dbloptin){
            /* TODO send a confirmation email now */
            $mailer=&WYSIJA::get("mailer","helper");
            $emailsent=$mailer->sendOne($config->getValue('confirm_email_id'),$uid);
        }else{
            if($config->getValue("emails_notified") && $config->getValue("emails_notified_when_sub")){
                
                $this->helperUser=&WYSIJA::get("user","helper");
                $this->helperUser->uid=$uid;
                $this->helperUser->_notify($_REQUEST['wysija'][$this->model]['email']);
            }
        }
        
        

        return $emailsent;
    }
    function registerToLists($data,$uid){
        if($data['wysija[user_list][list_ids]']){
            $listids=explode(',',$data['wysija[user_list][list_ids]']);
            $model=&WYSIJA::get('user_list',"model");
            $subdate=mktime();

            foreach($listids as $listid){
                $model->replace(array("list_id"=>$listid,"user_id"=>$uid,"sub_date"=>$subdate));
                $model->reset();
            }
        }
        
    }
}