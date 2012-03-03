<?php
defined('WYSIJA') or die('Restricted access');


class WYSIJA_control_front_stats extends WYSIJA_control_front{
    var $model="";
    var $view="";
    
    function WYSIJA_control_front_stats(){
        parent::WYSIJA_control_front();
    }
    
    
    function analyse(){
        if(isset($_REQUEST['email_id']) && isset($_REQUEST['user_id'])){
            $email_id=(int)$_REQUEST['email_id'];
            $user_id=(int)$_REQUEST['user_id'];
            if(isset($_REQUEST['urlencoded'])){
                /* clicked stats */
                $decodedUrl=base64_decode($_REQUEST['urlencoded']);
                if($email_id){ //if not email_id that means it is an email preview
                    /* look for url entry and insert if not exists*/
                    $modelUrl=&WYSIJA::get("url","model");
                    
                    $urlObj=$modelUrl->getOne(false,array("url"=>$decodedUrl));

                    if(!$urlObj){
                        /* we need to insert in url */
                        $modelUrl->insert(array("url"=>$decodedUrl));
                        $urlObj=$modelUrl->getOne(false,array("url"=>$decodedUrl));
                    }
                    $modelUrl=null;

                    /* look for email_user_url entry and insert if not exists*/
                    $modelEmailUserUrl=WYSIJA::get("email_user_url","model");
                    $dataEmailUserUrl=array("email_id"=>$email_id,"user_id"=>$user_id,"url_id"=>$urlObj['url_id']);
                    $emailUserUrlObj=$modelEmailUserUrl->getOne(false,$dataEmailUserUrl);
                    $uniqueclick=false;
                    if(!$emailUserUrlObj){
                        /* we need to insert in email_user_url */
                        $modelEmailUserUrl->insert($dataEmailUserUrl);
                        $uniqueclick=true;
                    }

                    /* increment stats counter on email_user_url clicked */
                    $modelEmailUserUrl=WYSIJA::get("email_user_url","model");
                    $modelEmailUserUrl->update(array('clicked_at'=>mktime(),'number_clicked'=>'[increment]'),$dataEmailUserUrl);
                    $modelEmailUserUrl=null;

                    /* look for url_mail entry and insert if not exists*/
                    $modelUrlMail=&WYSIJA::get("url_mail","model");
                    $dataUrlEmail=array("email_id"=>$email_id,"url_id"=>$user_id);
                    $urlMailObj=$modelUrlMail->getOne(false,$dataUrlEmail);
                    if(!$urlMailObj){
                        /* we need to insert in url_mail */
                        $modelUrlMail->insert($dataUrlEmail);
                    }

                    $dataUpdate=array('total_clicked'=>'[increment]');
                    if(!$uniqueclick)    $dataUpdate['unique_clicked']="[increment]";
                    /* increment stats counter on url_mail clicked */
                    $modelUrlMail->update($dataUpdate,$dataUrlEmail);
                    $modelUrlMail=null;

                    /* increment email_stat  clicked */
                    //$modelEmail=&WYSIJA::get("email","model");
                    //$modelEmail->update(array('number_clicked'=>"[increment]"),array("email_id"=>$email_id));

                    $statusEmailUserStat=2;
                    if(in_array($decodedUrl,array("[unsubscribe_link]","[subscriptions_link]"))){
                        $this->subscriberClass = &WYSIJA::get("user","model");
                        $this->subscriberClass->getFormat=OBJECT;
                        $receiver = $this->subscriberClass->getOne($user_id);
                        switch($decodedUrl){
                            case "[unsubscribe_link]":
                                $link=$this->subscriberClass->getUnsubLink($receiver,true);
                                $statusEmailUserStat=3;
                                break;
                            case "[subscriptions_link]":
                                $link=$this->subscriberClass->getEditsubLink($receiver,true);
                                break;
                        }
                        $decodedUrl=$link;
                        
                    }else{
                        if(strpos($decodedUrl, "http://" )=== false) $decodedUrl="http://".$decodedUrl;
                    }
                    
                    $modelEmailUS=&WYSIJA::get("email_user_stat","model");
                    $exists=$modelEmailUS->getOne(false,array("equal"=>array("email_id"=>$email_id,"user_id"=>$user_id), "less"=>array("status"=>$statusEmailUserStat)));
                    $dataupdate=array('status'=>$statusEmailUserStat);
                    if(!(int)$exists['opened_at']){
                        $dataupdate['opened_at']=mktime();
                    }
                    //,'opened_at'=>mktime()

                    $modelEmailUS->reset();
                    $modelEmailUS->colCheck=false;
                    $modelEmailUS->update($dataupdate,array("equal"=>array("email_id"=>$email_id,"user_id"=>$user_id), "less"=>array("status"=>$statusEmailUserStat)));
                }else{
                   if(in_array($decodedUrl,array("[unsubscribe_link]","[subscriptions_link]"))){
                        $modelU=&WYSIJA::get("user","model");
                        $modelU->getFormat=OBJECT;
                        $objUser=$modelU->getOne(false,array('wpuser_id'=>get_current_user_id()));
                        switch($decodedUrl){
                            case "[unsubscribe_link]":
                                $link=$modelU->getConfirmLink($objUser,"unsubscribe",false,true).'&demo=1';

                                break;
                            case "[subscriptions_link]":
                                $link=$modelU->getConfirmLink($objUser,"subscriptions",false,true).'&demo=1';
                                //$link=$this->subscriberClass->getEditsubLink($receiver,true);
                                break;
                        }
                        $decodedUrl=$link;
                        
                    }else{
                        if(strpos($decodedUrl, "http://" )=== false) $decodedUrl="http://".$decodedUrl;
                    } 
                }

                $this->redirect($decodedUrl);
                
            }else{
                /* opened stat */
                //$modelEmail=&WYSIJA::get("email","model");
                //$modelEmail->update(array('number_opened'=>"[increment]"),array("email_id"=>$email_id));
                
                $modelEmailUS=&WYSIJA::get("email_user_stat","model");
                $modelEmailUS->reset();
                $modelEmailUS->update(
                        array('status'=>1,'opened_at'=>mktime()),
                        array("email_id"=>$email_id,"user_id"=>$user_id,"status"=>0));
               
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );

		if(empty($picture)) $picture = WYSIJA_DIR_IMG.'statpicture.png';
		$handle = fopen($picture, 'r');

		if(!$handle) exit;
		header("Content-type: image/png");
		$contents = fread($handle, filesize($picture));
		fclose($handle);
		echo $contents;
                exit;
            }
            
            
        }

        return true;
    }
    

        

}