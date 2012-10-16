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
                    $listids=$this->helperUser->unsubscribe($this->userData["details"]["user_id"]);


                    $this->title=$modelConf->getValue("unsubscribed_title");
                    $this->subtitle=$modelConf->getValue("unsubscribed_subtitle");
                    $this->helperUser->uid=$this->userData["details"]["user_id"];
                    if($modelConf->getValue("emails_notified") && $modelConf->getValue("emails_notified_when_unsub"))    $this->helperUser->_notify($this->userData["details"]["email"],false,$listids);
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
            $modelL=&WYSIJA::get('list','model');
            $modelL->orderBy('ordering','ASC');
            $data['list']=$modelL->get(array('list_id','name','description'),array('is_enabled'=>true,'is_public'=>true));

            $this->title=sprintf(__('Edit your subscriber profile: %1$s',WYSIJA),$data['user']['details']['email']);

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
            $modelConf=&WYSIJA::get('config','model');
            $this->helperUser->uid=$userid;
            /* if the status changed we might need to send notifications */
            if((int)$_REQUEST['wysija']['user']['status'] !=(int)$this->userData['details']['status']){
                if($_REQUEST['wysija']['user']['status']>0){
                    if($modelConf->getValue('emails_notified_when_sub'))    $this->helperUser->_notify($this->userData['details']['email']);
                }else{
                    if($modelConf->getValue('emails_notified_when_unsub'))    $this->helperUser->_notify($this->userData['details']['email'],false);
                }
            }

            //check whether the email address has changed if so then we should make sure that the new address doesnt exists already
            if(isset($_REQUEST['wysija']['user']['email'])){
                $_REQUEST['wysija']['user']['email']=trim($_REQUEST['wysija']['user']['email']);
                if($this->userData['details']['email']!=$_REQUEST['wysija']['user']['email']){
                    $this->modelObj->reset();
                    $result=$this->modelObj->getOne(false,array('email'=>$_REQUEST['wysija']['user']['email']));
                    if($result){
                        $this->error(sprintf(__('Email %1$s already exists.',WYSIJA),$_REQUEST['wysija']['user']['email']),1);
                        unset($_REQUEST['wysija']['user']['email']);
                    }
                }
            }

            $this->modelObj->update($_REQUEST['wysija']['user'],array('user_id'=>$userid));

            /* update the list subscriptions */
           /* update subscriptions */
            $modelUL=&WYSIJA::get('user_list','model');
            /* list of core list */
            $modelLIST=&WYSIJA::get('list','model');
            $results=$modelLIST->get(array('list_id'),array('is_enabled'=>1,'is_public'=>1));
            $core_listids=array();
            foreach($results as $res){
                $core_listids[]=$res['list_id'];
            }

            $user_lists=$modelUL->get(array('list_id'),array('user_id'=>$userid));

            $postedlistids=$differenceForPostNotif=$removedFromList=array();
            if(!empty($_POST['wysija']['user_list']['list_id']))    $postedlistids=$_POST['wysija']['user_list']['list_id'];

            foreach($core_listids as $listidunik){
                if(!in_array($listidunik, $postedlistids)){
                    $removedFromList[]=$listidunik;
                }

            }
            $modelUL->delete(array('user_id'=>$userid,'list_id'=>$removedFromList));


            if(isset($_POST['wysija']['user_list']) && $_POST['wysija']['user_list']){

                $alreadyindblistids=array();
                if($user_lists){
                    foreach($user_lists as $ulist){
                        $alreadyindblistids[]=$ulist['list_id'];
                    }
                }
                if(!empty($alreadyindblistids)){
                    foreach($postedlistids as $listidunik){
                        if(!in_array($listidunik, $alreadyindblistids)){
                            $differenceForPostNotif[]=$listidunik;
                        }
                    }
                }else{
                    $differenceForPostNotif=$postedlistids;
                }


                foreach($differenceForPostNotif as $listid)
                    $modelUL->insert(array("user_id"=>$userid,"list_id"=>$listid,"unsub_date"=>0));

            }

            $modelUL->reset();

            $this->notice(__('Newsletter profile has been updated.',WYSIJA));

            $this->subscriptions();

            //reset post otherwise wordpress will not recognise the post !!!
            $_POST=array();
        }

        return true;
    }



}