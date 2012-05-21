<?php
defined('WYSIJA') or die('Restricted access');


class WYSIJA_control_front_confirm extends WYSIJA_control_front{
    var $model="user";
    var $view="confirm";
    
    function WYSIJA_control_front_confirm(){
        parent::WYSIJA_control_front();
    }
    
    function _testKeyuser(){
        $this->helperUser=&WYSIJA::get("user","helper");
        
        $this->userData=$this->helperUser->checkUserKey();
        add_action('init',array($this,'testsession'));
        
        if(!$this->userData){
            $this->title=__("Page does not exist.",WYSIJA);
            $this->subtitle=__("Please verify your link to this page.",WYSIJA);
            return false;
        }
        return true;
    }
    

    function subscribe(){
        if(isset($_REQUEST['demo'])){
            $modelConf=&WYSIJA::get("config","model");
            $this->title=$modelConf->getValue("subscribed_title");
            $this->subtitle=$modelConf->getValue("subscribed_subtitle");
  
        }else{
           if($this->_testKeyuser()){
               $modelConf=&WYSIJA::get("config","model"); 

               if((int)$this->userData["details"]['status']<1){
                    $this->helperUser->subscribe($this->userData["details"]["user_id"]);
                    $modelConf=&WYSIJA::get("config","model");
                    $this->title=$modelConf->getValue("subscribed_title");
                    $this->subtitle=$modelConf->getValue("subscribed_subtitle");
                    $this->helperUser->uid=$this->userData["details"]["user_id"];
                    if($modelConf->getValue("emails_notified") && $modelConf->getValue("emails_notified_when_sub"))    $this->helperUser->_notify($this->userData["details"]["email"]);
                    return true;
                }else{
                    $this->title=__("You are already subscribed.",WYSIJA);

                    return false;
                }

            } 
        }
        
        return true;
    }
    
    function unsubscribe(){
        if(isset($_REQUEST['demo'])){
            $modelConf=&WYSIJA::get("config","model");
            $this->title=$modelConf->getValue("unsubscribed_title");
            $this->subtitle=$modelConf->getValue("unsubscribed_subtitle");
        }else{
            if($this->_testKeyuser()){
                $modelConf=&WYSIJA::get("config","model");
                $statusint=(int)$this->userData["details"]['status'];
                if( ($modelConf->getValue("confirm_dbleoptin") && $statusint>0) || (!$modelConf->getValue("confirm_dbleoptin") && $statusint>=0)){
                    $this->helperUser->unsubscribe($this->userData["details"]["user_id"]);

                    
                    $this->title=$modelConf->getValue("unsubscribed_title");
                    $this->subtitle=$modelConf->getValue("unsubscribed_subtitle");
                    $this->helperUser->uid=$this->userData["details"]["user_id"];
                    if($modelConf->getValue("emails_notified") && $modelConf->getValue("emails_notified_when_unsub"))    $this->helperUser->_notify($this->userData["details"]["email"],false);
                }else{
                    $this->title=__("You are already unsubscribed.",WYSIJA);

                    return false;
                }

            }
        }
        

        return true;
    }
    
    function subscriptions(){
        $data=array();
        
        /* get the user_id out of the params passed */
        if($this->_testKeyuser()){
            $data['user']=$this->userData;
            /* get the list of user */
            $modelL=&WYSIJA::get("list","model");
            $modelL->orderBy("ordering","ASC");
            $data['list']=$modelL->get(array("list_id","name","description"),array("is_enabled"=>true));

            $this->title=sprintf(__('Edit your newsletter profile: %1$s',WYSIJA),$data['user']['details']['email']);
        
            $this->subtitle=$this->viewObj->subscriptions($data);

        
            return true;
        }

            
    }
    
    
    function save(){

        /* get the user_id out of the params passed */
        if($this->_testKeyuser()){
            /* update the general details */
            $userid=$_REQUEST['wysija']['user']['user_id'];
            unset($_REQUEST['wysija']['user']['user_id']);
            $modelConf=&WYSIJA::get("config","model");
            $this->helperUser->uid=$userid;
            /* if the status changed we might need to send notifications */
            if((int)$_REQUEST['wysija']['user']['status'] !=(int)$this->userData['details']['status']){
                if($_REQUEST['wysija']['user']['status']>0){
                    if($modelConf->getValue("emails_notified_when_sub"))    $this->helperUser->_notify($this->userData["details"]["email"]);
                }else{
                    if($modelConf->getValue("emails_notified_when_unsub"))    $this->helperUser->_notify($this->userData["details"]["email"],false);
                }
            }

            $this->modelObj->update($_REQUEST['wysija']['user'],array("user_id"=>$userid));

            /* update the list subscriptions */
           /* update subscriptions */
            $modelUL=&WYSIJA::get("user_list","model");
            /* list of core list */
            $modelLIST=&WYSIJA::get("list","model");
            $results=$modelLIST->get(array("list_id"),array("is_enabled"=>"0"));
            $core_listids=array();
            foreach($results as $res){
                $core_listids[]=$res['list_id'];
            }

            if(isset($_POST['wysija']['user_list']) && $_POST['wysija']['user_list']){
                foreach($_POST['wysija']['user_list']['list_id'] as $listid)
                    $core_listids[]=$listid;

                /* what we subscribe to*/
                foreach($_POST['wysija']['user_list']['list_id'] as $listid)
                    $modelUL->replace(array("user_id"=>$userid,"list_id"=>$listid,"unsub_date"=>0));
            }

            //unsubscribe
            $condiFirst=array("notequal"=>array("list_id"=>$core_listids),"equal"=>array("user_id"=>$userid,'unsub_date'=>0));
            $modelUL->reset();
            $modelUL->specialUpdate=true;
            $modelUL->noCheck=true;
            $modelUL->update(array("unsub_date"=>mktime()),$condiFirst);
            $modelUL->reset();

            $this->notice(__("Newsletter profile has been updated.",WYSIJA));
            $this->subscriptions();
        }
        
        return true;
    }
        
        

}