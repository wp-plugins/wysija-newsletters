<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_model_queue extends WYSIJA_model{
    
    var $pk=array("email_id","user_id");
    var $table_name="queue";
    var $columns=array(
        'email_id'=>array("type"=>"integer"),
        'user_id'=>array("type"=>"integer"),
        'send_at' => array("req"=>true,"type"=>"integer"),
        'priority' => array("type"=>"integer"),
        'number_try' => array("type"=>"integer")
    );
    
    
    
    function WYSIJA_model_queue(){
        $this->WYSIJA_model();
    }
    
    function queueCampaign($campaignid){
        if(!$campaignid) {
            $this->error("Missing campaign id in queueCampaign()");
            return false;
        }
        /* get campaign information */
        $modelCamp=&WYSIJA::get("campaign","model");
        $data=$modelCamp->getDetails($campaignid);
        $modelC=&WYSIJA::get("config","model");
        if($modelC->getValue("confirm_dbleoptin")) $statusmin=0;
        else $statusmin=-1;
        
        
        $query="INSERT IGNORE INTO ".$this->getPrefix()."queue (`email_id` ,`user_id`,`send_at`) ";
        $query.="SELECT ".$data['email']['email_id'].", A.user_id,".mktime()." FROM ".$this->getPrefix()."user_list as A JOIN ".$this->getPrefix()."user as B on A.user_id=B.user_id WHERE B.status>".$statusmin." AND A.list_id IN (".implode(",",$data['campaign']['lists']['ids']).")  ";
//dbg($query);
        $this->query($query);

        return true;
    }
    
    
    function launch(){
        $modelC=&WYSIJA::get("config","model");
        /* check if the daily limit there is, has been reached already */
        $sent_today=0;
        if($sent_today<$modelC->getValue("sending_emails_number")){
            /* get a list of queue ordered by priority/campaign and send them */
            $modelQ=&WYSIJA::get("queue","model");
            $query="SELECT A.email_id,B.* FROM `".$modelQ->getPrefix()."queue` as A 
                LEFT JOIN `".$modelQ->getPrefix()."user` as B on A.user_id=B.user_id 
                LEFT JOIN `".$modelQ->getPrefix()."email` as C on C.email_id=A.email_id
                    WHERE C.status >0 
                    ORDER BY priority, A.email_id DESC LIMIT 0,".$modelC->getValue("sending_emails_number");
           
            $users=$modelQ->query("get_res",$query,OBJECT);

            if($users){
                /* make a list of email_id to be sent */ 
                $emailids=$modelQ->query("get_res","SELECT A.email_id FROM `".$modelQ->getPrefix()."queue` as A 
                    LEFT JOIN `".$modelQ->getPrefix()."user` as B on A.user_id=B.user_id 
                        GROUP BY A.email_id
                        ORDER BY priority, A.email_id DESC LIMIT 0,".$modelC->getValue("sending_emails_number"),ARRAY_A);

                $allemailids=array();
                foreach($emailids as $emailob)  $allemailids[]=$emailob['email_id'];

                /* add the confirmation email_id */
                $allemailids[]=$modelC->getValue("confirm_email_id");

                /* let's load the emails to be sent */
                $emails=$modelQ->query("get_res","SELECT * FROM `".$modelQ->getPrefix()."email` 
                    WHERE email_id IN ('".implode("','",$allemailids)."')",OBJECT_K);
                /*foreach($emails as $emailid => $email){
                    if($emailid!=$modelC->getValue('confirm_email_id')) {
                        $emails[$emailid]->body.="[subscriptions_links]";
                        $emails[$emailid]->body.="\n[footer_address]";
                    }
                }*/
                
                /* if there is double optin "on" we load the confirmation email to send to the people having not confirmed yet */
                $mailer=&WYSIJA::get("mailer","helper");
                $time=mktime();
                $error=$success=array();
                $confirmemailid=$modelC->getValue("confirm_email_id");
                $modelEUS=&WYSIJA::get("email_user_stat","model");
                foreach($users as $usr){
                    if($usr->status>=0){
                        if($modelC->getValue("confirm_dbleoptin") && $usr->status==0) $email_id=$confirmemailid;
                        else $email_id=$usr->email_id;

                        if($mailer->sendOne($emails[$email_id],$usr)){
                            /* remove from the queue insert into email_user_stat */
                            $success[$email_id][]=$usr->user_id;
                            $modelEUS->reset();
                            $insertdata=array("user_id"=>$usr->user_id,"email_id"=>$email_id,"sent_at"=>$time,"status"=>"0");
                            //dbg($insertdata);
                            $modelEUS->fieldValid=false;
                            $modelEUS->insert($insertdata);
                            $modelQ->reset();
                            $modelQ->delete(array("equal"=>array("user_id"=>$usr->user_id, "email_id"=>$email_id)));
                        }else{
                            /* increment the number of try */
                            $error[$email_id][]=$usr->user_id;
                            $modelQ->reset();
                            $modelQ->update(array("number_try"=>"[increment]"), array("user_id"=>$usr->user_id, "email_id"=>$email_id));
                        }
                    }

                }

            }else{
                $this->notice(__("Queue is empty.",WYSIJA));
            }

        }
    }
    
        
    function ACYdelete($filters){
            $query = 'DELETE a.* FROM '.$this->getPrefix().'queue as a';
            if(!empty($filters)){
                    $query .= ' JOIN '.$this->getPrefix().'user as b on a.user_id = b.user_id';
                    $query .= ' JOIN '.$this->getPrefix().'email as c on a.email_id = c.email_id';
                    $query .= ' WHERE ('.implode(') AND (',$filters).')';
            }
            //dbg($filters);
            $this->query($query);
            $nbRecords = $this->getAffectedRows();
            if(empty($filters)){
                $this->query('TRUNCATE TABLE '.$this->getPrefix().'queue');
            }
            return $nbRecords;
    }

    function nbQueue($mailid){
            $mailid = (int) $mailid;
            return $this->query('get_res','SELECT count(user_id) FROM '.$this->getPrefix().'queue WHERE email_id = '.$mailid.' GROUP BY email_id');
    }

    function queue($mailid,$time,$onlyNew = false){
            $mailid = intval($mailid);
            if(empty($mailid)) return false;
 
            $classLists =&WYSIJA::get("campaign_list","model");
            $lists = $classLists->getReceivers($mailid,false);
            if(empty($lists)) return 0;
            $config = &WYSIJA::get("config","model");
            $querySelect = 'SELECT DISTINCT a.user_id,'.$mailid.','.$time.','.(int) $config->getValue('priority_newsletter',3);
            $querySelect .= ' FROM '.$this->getPrefix().'user_list as a ';
            $querySelect .= ' JOIN '.$this->getPrefix().'user as b ON a.user_id = b.user_id ';
            $querySelect .= 'WHERE a.list_id IN ('.implode(',',array_keys($lists)).') AND a.status = 1 ';

            if($config->getValue('confirm_dbleoptin')){ $querySelect .= 'AND b.status = 1 '; }
            $query = 'INSERT IGNORE INTO '.$this->getPrefix().'queue (user_id,email_id,send_at,priority) '.$querySelect;

            if(!$this->query($query)){
                    //acymailing_display($this->database->getErrorMsg(),'error');
                $this->error($this->getErrorMsg());
            }
            $totalinserted = $this->getAffectedRows();
            if($onlyNew){
                    $query='DELETE b.* FROM `'.$this->getPrefix().'email_user_stat` as a JOIN `'.$this->getPrefix().'queue` as b on a.user_id = b.user_id WHERE a.email_id = '.$mailid;
                    $this->query($query);
                    $totalinserted = $totalinserted - $this->getAffectedRows();
            }
            //JPluginHelper::importPlugin('acymailing');
    /*$dispatcher = &JDispatcher::getInstance();
    $dispatcher->trigger('onAcySendNewsletter',array($mailid));*/
            return $totalinserted;
    }

    function getReady($limit,$mailid = 0){
            $query = 'SELECT c.*,a.* FROM '.$this->getPrefix().'queue as a';
            $query .= ' JOIN '.$this->getPrefix().'email as b on a.`email_id` = b.`email_id` ';
            $query .= ' JOIN '.$this->getPrefix().'user as c on a.`user_id` = c.`user_id` ';
            $query .= ' WHERE a.`send_at` <= '.time().' AND b.`status` = 1';
            if(!empty($mailid)) $query .= ' AND a.`email_id` = '.$mailid;
            $query .= ' ORDER BY a.`priority` ASC, a.`send_at` ASC, a.`user_id` ASC';
            if(!empty($limit)) $query .= ' LIMIT '.$limit;

            $results=$this->query("get_res",$query,OBJECT_K);
            //$results = $this->database->loadObjectList();
            if($results === null){
                $this->query('REPAIR TABLE '.$this->getPrefix().'queue, '.$this->getPrefix().'user, '.$this->getPrefix().'email');    
            }

            if(!empty($results)){
                    $firstElementQueued = reset($results);
                    //$this->database->setQuery();
                    $this->query('UPDATE '.$this->getPrefix().'queue SET send_at = send_at + 1 WHERE email_id = '.$firstElementQueued->email_id.' AND user_id = '.$firstElementQueued->user_id.' LIMIT 1');
            }
            return $results;
    }

    function queueStatus($mailid,$all = false){
            $query = 'SELECT a.email_id, count(a.user_id) as nbsub,min(a.send_at) as send_at, b.subject FROM '.$this->getPrefix().'queue as a';
            $query .= ' JOIN '.$this->getPrefix().'email as b on a.email_id = b.email_id';
            $query .= ' WHERE b.published > 0';
            if(!$all){
                    $query .= ' AND a.send_at < '.time();
                    if(!empty($mailid)) $query .= ' AND a.email_id = '.$mailid;
            }
            $query .= ' GROUP BY a.email_id';
            //$this->database->setQuery($query);
            $queueStatus=$this->query("get_res",$query,OBJECT_K);
            //$queueStatus = $this->database->loadObjectList('email_id');
            return $queueStatus;
    }

}
