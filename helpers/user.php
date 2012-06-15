<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_user extends WYSIJA_object{
    function getIP(){
        $ip = '';
        if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) AND strlen($_SERVER['HTTP_X_FORWARDED_FOR'])>6 ){
            $ip = strip_tags($_SERVER['HTTP_X_FORWARDED_FOR']);
        }elseif( !empty($_SERVER['HTTP_CLIENT_IP']) AND strlen($_SERVER['HTTP_CLIENT_IP'])>6 ){
             $ip = strip_tags($_SERVER['HTTP_CLIENT_IP']);
        }elseif(!empty($_SERVER['REMOTE_ADDR']) AND strlen($_SERVER['REMOTE_ADDR'])>6){
             $ip = strip_tags($_SERVER['REMOTE_ADDR']);
        }//endif
        if(!$ip) $ip="127.0.0.1";
        return strip_tags($ip);
    }
    function validEmail($email){
            if(empty($email) OR !is_string($email)) return false;
            if(!preg_match('/^([a-z0-9_\'&\.\-\+\æ\ø\å])+\@(([a-z0-9\-\æ\ø\å])+\.)+([a-z0-9]{2,10})+$/i',$email)) return false;
            return true;
    }
    function checkUserKey(){
        if(isset($_REQUEST['wysija-key'])){
            $modelUser=&WYSIJA::get("user","model");
            $result=$modelUser->getDetails(array("keyuser"=>$_REQUEST['wysija-key']));
            if($result) {
                return $result;
            }
            else{
                $this->error(__("Page is not accessible.",WYSIJA),true);
                return false;
            }
        }else{
            $this->error(__("Page is not accessible.",WYSIJA),true);
            return false;
        }
    }
    function clearSession(){

    }
    function getUserLists($user_id){
        $userModel=&WYSIJA::get('user','model');
        $query='SELECT A.* FROM [wysija]user_list as A LEFT JOIN [wysija]list as B on A.list_id=B.list_id WHERE A.user_id='.$user_id.' AND B.is_enabled=1';
        return $userModel->getResults($query);
    }
    
    function subscribe($user_id,$status=true,$auto=false){
        $time=time();
        if($status){
            $status=1;
            $datecol="sub_date";
            $cols=array($datecol=>$time,"unsub_date"=>0);
            

        }else{

            $status=-1;
            $datecol="unsub_date";
            $cols=array($datecol=>$time);
            $modelU=&WYSIJA::get("queue","model");
            $modelU->delete(array("user_id"=>$user_id));
            if($auto){
                $modelU=&WYSIJA::get("user_list","model");
                $modelU->delete(array("user_id"=>$user_id));
            }

        }
        $modelUser=&WYSIJA::get("user","model");
        $modelUser->update(array("status"=>$status),array("user_id"=>$user_id));
        
        if($status){
            $lists=$this->getUserLists($user_id);
            $this->sendAutoNl($user_id,$lists);
        }
        if(!$auto){
            $modelUserList=&WYSIJA::get("user_list","model");
            $modelUserList->update($cols,array("user_id"=>$user_id));
        }
    }
    
    function sendAutoNl($user_id,$extraparams=false,$checkfortype='subs-2-nl'){
        $modelEmail=&WYSIJA::get('email','model');
        $emails=$modelEmail->get(false,array('type'=>2,'status'=>array(1,3,99)));
        if(is_object($emails)){
            $emailarr=null;
            foreach($emails as $keyob => $valobj){
                $emailarr[$keyob]=$valobj;
            }
            $emails=$emailarr;
        }
        if(is_array($emails) && isset($emails['body'])) $emails=array($emails);
        foreach($emails as $key=> $email){
            if($email['params']['autonl']['event']!=$checkfortype) continue;
            switch($checkfortype){
                case 'subs-2-nl':
                    
                    foreach($extraparams as $details){
                        if(isset($email['params']['autonl']['subscribetolist']) && $email['params']['autonl']['subscribetolist']==$details['list_id'])
                            $this->insertAutoQueue($user_id,$email['email_id'], $email['params']['autonl']);
                    }
                    break;
                case 'new-user':
                    
                    $okInsert=false;
                    switch($email['params']['autonl']['roles']){
                        case 'any':
                            $okInsert=true;
                            break;
                        default:
                            foreach($extraparams->roles as $rolename){
                                if($rolename==$email['params']['autonl']['roles'])
                                    $okInsert=true;
                            }
                            break;
                    }
                    if($okInsert)
                        $this->insertAutoQueue($user_id,$email['email_id'], $email['params']['autonl']);
                    break;
            }
        }
    }
    function insertAutoQueue($user_id,$email_id,$emailparams){
        $modelQueue=&WYSIJA::get('queue','model');
        $queueData=array('priority'=>'-1','email_id'=>$email_id,'user_id'=>$user_id);
        $delay=0;
        
        if(isset($emailparams['numberafter']) && (int)$emailparams['numberafter']>0){
            switch($emailparams['numberofwhat']){
                case 'immediate':
                    $delay=0;
                    break;
                case 'hours':
                    $delay=(int)$emailparams['numberafter']*3600;
                    break;
                case 'days':
                    $delay=(int)$emailparams['numberafter']*3600*24;
                    break;
                case 'weeks':
                    $delay=(int)$emailparams['numberafter']*3600*24*7;
                    break;
            }
            $queueData['send_at']=time()+$delay;
        }
        
        if(isset($emailparams['unique_send']) && $emailparams['unique_send']){
            
            $modelEUS=&WYSIJA::get('email_user_stat','model');
            if(!$modelEUS->exists(array('email_id'=>$email_id,'user_id'=>$user_id))) 
                    $modelQueue->insert($queueData);
        }else{
            $modelQueue->insert($queueData);
        }

        if($delay==0){
            $queueH=&WYSIJA::get('queue','helper');
            $queueH->report=false;
            $queueH->process($email_id,$user_id);
        }
        return true;
    }
    function unsubscribe($user_id,$auto=false){
        return $this->subscribe($user_id,false,$auto);
    }
    function sendConfirmationEmail($user_ids,$sendone=false){
        if($sendone || is_object($user_ids)){
            
            $users=array($user_ids);
        }else{
            if(!is_array($user_ids)){
                $user_ids=(array)$user_ids;
            }
            
            $modelU=&WYSIJA::get('user','model');
            $modelU->getFormat=OBJECT_K;
            $users=$modelU->get(false,array('equal'=>array('user_id'=>$user_ids,'status'=>0)));
        }
        $config=&WYSIJA::get('config','model');
        $mailer=&WYSIJA::get("mailer","helper");
        foreach($users as $userObj){
            $resultsend=$mailer->sendOne($config->getValue('confirm_email_id'),$userObj,true);
        }
        if(!$sendone)$this->notice(sprintf(__('%1$d emails have been sent to unconfirmed subscribers.',WYSIJA),count($users)));
        else return $resultsend;
        return true;
    }
    
    function delete($user_ids,$sendone=false){
        if($sendone){
            
            $users=array($user_ids);
        }else{
            if(!is_array($user_ids)){
                $user_ids=(array)$user_ids;
            }
        }
        $modelEUR=&WYSIJA::get('user_history','model');
        $modelEUR->delete(array("user_id"=>$user_ids));
        $modelEUR=&WYSIJA::get('email_user_url','model');
        $modelEUR->delete(array("user_id"=>$user_ids));
        $modelEUS=&WYSIJA::get('email_user_stat','model');
        $modelEUS->delete(array("user_id"=>$user_ids));
        $modelUL=&WYSIJA::get('user_list','model');
        $modelUL->delete(array("user_id"=>$user_ids));
        $modelU=&WYSIJA::get("queue","model");
        $modelU->delete(array("user_id"=>$user_ids));
        $modelU=&WYSIJA::get('user','model');
        $emailsarr=$modelU->get(array('email'),array("user_id"=>$user_ids));
        $modelU->reset();
        $modelU->delete(array("user_id"=>$user_ids));
        $emails=array();
        foreach($emailsarr as $emobj)   $emails[]=$emobj['email'];
        if(count($user_ids)>1)  $this->notice(sprintf(__(' %1$s users have been deleted.',WYSIJA),count($user_ids)));
        else    $this->notice(sprintf(__(' %1$s subscriber has been deleted.',WYSIJA),  implode(',', $emails)));
        return true;
    }
    
    function addToList($listid,$userids){
        $modelUser=&WYSIJA::get("user","model");
        $query="INSERT IGNORE INTO `[wysija]user_list` (`list_id`,`user_id`)";
        $query.=" VALUES ";
        $total=count($userids);
        foreach($userids as $key=> $uid){
            $query.="(".(int)$listid.",".(int)$uid.")\n";
            if($total>($key+1)) $query.=",";
        }
        $modelUser->query($query);
    }
    
    function _notify($email,$subscribed=true){
        
        $modelUser=&WYSIJA::get("user_list","model");
        $qry="Select B.name from `[wysija]user_list` as A join `[wysija]list` as B on A.list_id=B.list_id where A.user_id=".$this->uid." and B.is_enabled>0";
        $result=$modelUser->query("get_res",$qry);
        $listnames=array();
        foreach($result as $arra){
            $listnames[]=$arra['name'];
        }
        if($subscribed){
            $title=sprintf(__('New subscriber to %1$s',WYSIJA),implode(',',$listnames));
            $body=sprintf(__('Howdy,'."\n\n".'The subscriber %1$s has just subscribed himself to your list "%2$s".'."\n\n".'Cheers,'."\n\n".'The Wysija Plugin',WYSIJA),"<strong>".$email."</strong>","<strong>".implode(',',$listnames)."</strong>");
        }else{
            $title=sprintf(__('One less subscriber to %1$s',WYSIJA),implode(',',$listnames));
            $body=sprintf(__('Howdy,'."\n\n".'The subscriber : %1$s has just unsubscribed himself to your list "%2$s".'."\n\n".'Cheers,'."\n\n".'The Wysija Plugin',WYSIJA),"<strong>".$email."</strong>","<strong>".implode(',',$listnames)."</strong>");
        }
        $modelConf=&WYSIJA::get("config","model");
        $mailer=&WYSIJA::get("mailer","helper");
        $notifieds=$modelConf->getValue('emails_notified');

        $notifieds=explode(",",$notifieds);
        $mailer->testemail=true;
        foreach($notifieds as $receiver){
            $mailer->sendSimple(trim($receiver),$title,$body);
        }
    }
    function refreshUsers(){
        $modelU=&WYSIJA::get("user","model");
        $modelU->reset();
        $config=&WYSIJA::get("config","model");
        if($config->getValue("confirm_dbleoptin")){
            $modelU->setConditions(array("greater"=>array("status"=>0)));
        }else{
            $modelU->setConditions(array("greater"=>array("status"=>-1)));
        }
        $count=$modelU->count();
        $modelC=&WYSIJA::get("config","model");
        $modelC->save(array('total_subscribers'=>$count));
        return true;
    }
    
    function synchList($listid,$total=false){
        $model=&WYSIJA::get("list","model");
        $data=$model->getOne(false,array("list_id"=>(int)$listid,"is_enabled"=>"0"));
        if($data){
            if(strpos($data['namekey'], "-listimported-")!==false){
                $model->reset();

                $listdata=explode("-listimported-",$data['namekey']);
                $dataMainList=$model->getOne(false,array("namekey"=>$listdata[0],"is_enabled"=>"0"));
                $importHelper=&WYSIJA::get("import","helper");
                $dataPlugi=$importHelper->getPluginsInfo($listdata[0]);
                $listsids=array(
                    "wysija_list_main_id"=>$dataMainList['list_id'],
                    "wysija_list_id"=>$data['list_id'],
                    "plug_list_id"=>$listdata[1]
                );
                $importHelper->import($listdata[0],$dataPlugi,false,false,$listsids);
            }elseif($data['namekey']=="users"){

                $ismainsite=true;
                if (is_multisite()){
                    global $wpdb;
                    if($wpdb->prefix!=$wpdb->base_prefix){
                        $ismainsite=false;
                    }
                }
                $infosImport=array("name"=>"WordPress",
            "pk"=>"ID",
            "matches"=>array("ID"=>"wpuser_id","user_email"=>"email","display_name"=>"firstname"),
            "matchesvar"=>array("status"=>1));
                $importHelper=&WYSIJA::get("import","helper");
                $listsids=array(
                    "wysija_list_main_id"=>$data['list_id']
                );

                $importHelper->import($data['namekey'],$infosImport,false,$total,$listsids);
            }else{
            }
            $this->notice(sprintf(__('List "%1$s" has been synchronised.',WYSIJA),$data['name']));
            return true;
        }else{
            $this->error(__('The list does not exists or cannot be synched.',WYSIJA),true);
            return false;
        }
    }
}