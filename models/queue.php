<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_model_queue extends WYSIJA_model{

    var $pk=array('email_id','user_id');
    var $table_name='queue';
    var $columns=array(
        'email_id'=>array('type'=>'integer'),
        'user_id'=>array('type'=>'integer'),
        'send_at' => array('req'=>true,'type'=>'integer'),
        'priority' => array('type'=>'integer'),
        'number_try' => array('type'=>'integer')
    );



    function WYSIJA_model_queue(){
        $this->WYSIJA_model();
    }

    /**
     * used to put emails in the queue when starting to send or adding user to a follow_up email
     * @param mixed $email
     * @param boolean $follow_up
     * @return boolean
     */
    function queue_email($email, $follow_up=false){
        // check if $email contains the email_id or not
        $email_id=false;
        if(is_numeric($email))  $email_id= $email;
        if(isset($email['email_id']))   $email_id= $email['email_id'];
        if($email_id===false) {
            $this->error('Missing email id in queue_email()');
            return false;
        }

        // if it is a standard email we get the campaign list
        if(!$follow_up){
            $model_campaign = &WYSIJA::get('campaign','model');
            $data = $model_campaign->getDetails($email_id);

            $lists_to_send_to = $data['campaign']['lists']['ids'];
            $to_be_sent=time();
        }else{
            // if it is a follow up we get the campaign list
            $lists_to_send_to = array($email['params']['autonl']['subscribetolist']);
            $delay=$this->calculate_delay($email['params']['autonl']);
            $to_be_sent='(B.created_at) + '.$delay;
        }

        // get the minimum status to queue emails based on the double optin config
        $model_config=&WYSIJA::get('config','model');
        if($model_config->getValue('confirm_dbleoptin')) $status_min=0;
        else $status_min=-1;

        if(empty($lists_to_send_to)){
            $this->error(__('There are no list to send to.',WYSIJA),1);
            return false;
        }
        // insert into the queue
        $query='INSERT IGNORE INTO [wysija]queue (`email_id` ,`user_id`,`send_at`) ';
        $query.='SELECT '.$email_id.', A.user_id,'.$to_be_sent.'
            FROM [wysija]user_list as A
                JOIN [wysija]user as B on A.user_id=B.user_id
                    WHERE B.status>'.$status_min.' AND A.list_id IN ('.implode(',',$lists_to_send_to).') AND A.sub_date>'.$status_min.' AND A.unsub_date=0;';
        $this->query($query);

        // rows were inserted
        if((int) $this->getAffectedRows() > 0) return true;
        $this->error('Queue failure : '.$query);
        return false;
    }


    /**
     * get a list of the delaied queued emails
     * @param type $mailid
     * @return type
     */
    function getDelayed($mailid=0){
        if(!$mailid) return array();
        $query = 'SELECT c.*,a.* FROM [wysija]queue as a';
        $query .= ' JOIN [wysija]email as b on a.`email_id` = b.`email_id` ';
        $query .= ' JOIN [wysija]user as c on a.`user_id` = c.`user_id` ';
        $query .= ' WHERE  b.`status` IN (1,3,99)';
        if(!empty($mailid)) $query .= ' AND a.`email_id` = '.$mailid;
        $query .= ' ORDER BY a.`priority` ASC, a.`send_at` ASC, a.`user_id` ASC';

        $results=$this->query('get_res',$query);


        return $results;
    }

    /**
     * get a list of the emails ready to be sent
     * @param string $sql_limit
     * @param int $email_id
     * @param int $user_id
     * @return type
     */
    function getReady($sql_limit,$email_id = 0,$user_id=false){
        $query = 'SELECT c.*,a.* FROM [wysija]queue as a';
        $query .= ' JOIN [wysija]email as b on a.`email_id` = b.`email_id` ';
        $query .= ' JOIN [wysija]user as c on a.`user_id` = c.`user_id` ';
        $query .= ' WHERE a.`send_at` <= '.time().' AND b.`status` IN (1,3,99)';
        if(!empty($email_id)) $query .= ' AND a.`email_id` = '.$email_id;
        if($user_id) $query .= ' AND a.`user_id` = '.$user_id;
        $query .= ' ORDER BY a.`priority` ASC, a.`send_at` ASC, a.`user_id` ASC';
        if(!empty($sql_limit)) $query .= ' LIMIT '.$sql_limit;

        $results=$this->query('get_res',$query,OBJECT_K);
        if($results === null){
            $this->query('REPAIR TABLE [wysija]queue, [wysija]user, [wysija]email');
        }

        if(!empty($results)){
                $first_element_queued = reset($results);
                $this->query('UPDATE [wysija]queue SET send_at = send_at + 1 WHERE email_id = '.$first_element_queued->email_id.' AND user_id = '.$first_element_queued->user_id.' LIMIT 1');
        }
        return $results;
    }

    /**
     * calculate the delay of the follow up based on the email parameters that have been setup
     * @param array $email_params
     * @return int
     */
    function calculate_delay($email_params){
        $delay=0;
        //check if there is a delay, if so we just set a send_at params
        if(isset($email_params['numberafter']) && (int)$email_params['numberafter']>0){
            switch($email_params['numberofwhat']){
                case 'immediate':
                    $delay=0;
                    break;
                case 'hours':
                    $delay=(int)$email_params['numberafter']*3600;
                    break;
                case 'days':
                    $delay=(int)$email_params['numberafter']*3600*24;
                    break;
                case 'weeks':
                    $delay=(int)$email_params['numberafter']*3600*24*7;
                    break;
            }
        }
        return $delay;
    }

}
