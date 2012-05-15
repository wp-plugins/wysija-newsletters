<?php
global $viewMedia;
defined('WYSIJA') or die('Restricted access');
class WYSIJA_control_back_campaigns extends WYSIJA_control_back{
    var $model="campaign";
    var $view="campaigns";
    var $list_columns=array("campaign_id","name", "description");
    var $searchable=array("name", "description");
    var $filters=array();
    
    function WYSIJA_control_back_campaigns(){
        
    }
    function licok(){
        parent::WYSIJA_control_back();
        $dt=get_option("wysijey");

        if(isset($_REQUEST['xtz']) && $dt==$_REQUEST['xtz']){
        $dataconf=array('premium_key'=>base64_encode(get_option('home').mktime()),'premium_val'=>mktime());
        $this->notice(__("Premium version is valid for your site.",WYSIJA));
        }else{
        $dataconf=array('premium_key'=>"",'premium_val'=>"");
        //$datadomain=unserialize(base64_decode($dt));
        $this->error(str_replace(array("[link]","[/link]"),array('<a href="http://www.wysija.com/?wysijap=checkout&wysijashop-page=1&controller=orders&action=checkout&wysijadomain='.$dt.'" target="_blank">','</a>'),
        __("Premium version licence does not exists for your site. Purchase it [link]here[/link].",WYSIJA)),1);
        }
        WYSIJA::update_option("wysicheck",false);
        $modelConf=&WYSIJA::get("config","model");
        $modelConf->save($dataconf);

        $this->redirect('admin.php?page=wysija_config#premium');
    }
    
    function validateLic(){
        $helpLic=&WYSIJA::get("licence","helper");
        $res=$helpLic->check();

        $this->redirect();
    }
    function send_test_editor($dataPost=false){
        $modelQ=&WYSIJA::get("queue","model");
        $config=&WYSIJA::get("config","model");
        $premium=$config->getValue('premium_key');
        $subscribers=(int)$config->getValue('total_subscribers');
        
        if($subscribers<2000 || ($premium && $subscribers>=2000) ){
            if($modelQ->count()>0){
                $helperQ=&WYSIJA::get("queue","helper");
                $emailid=false;
                if($_REQUEST['emailid']){
                    $emailid=$_REQUEST['emailid'];
                }
                $helperQ->process($emailid);
            }else{
                echo "<strong>".__("Queue is empty!",WYSIJA)."</strong>";
            }
        }else echo "<strong>".__("Go premium, you cannot send anymore!",WYSIJA)."</strong>";
        
        exit;
    }
    
    function test(){
        @ini_set('max_execution_time',0);

        $config = &WYSIJA::get('config','model');
        $bounceClass = &WYSIJA::get('bounce','helper');
        $bounceClass->report = true;
        if(!$bounceClass->init()){
                $res['result']=false;
                return $res;
        }
        if(!$bounceClass->connect()){
                $this->error($bounceClass->getErrors());
                $res['result']=false;
                return $res;
        }
        $this->notice(sprintf(__('Successfully connected to %1$s',WYSIJA),$config->getValue('bounce_login')));
        $nbMessages = $bounceClass->getNBMessages();
        

        if(empty($nbMessages)){
            $this->error(__('There are no messages',WYSIJA),true);
            $res['result']=false;
            return $res;
        }else{
            $this->notice(sprintf(__('There are %1$s messages in your mailbox',WYSIJA),$nbMessages));
        }
        

        $bounceClass->handleMessages();
        $bounceClass->close();
        exit;
        return true;
    }
    
    function add($dataPost=false){
        $this->title=sprintf(__('Step %1$s',WYSIJA),1);
        $this->js[]='wysija-validator';
        $this->js['admin-campaigns-edit']='admin-campaigns-edit';
        
        $this->viewObj->title=__('First step : main details',WYSIJA);
        $this->viewShow='add';
        $this->data=array();
        $this->data['campaign']=array("name"=>"","description"=>"");
        $modelConfig=&WYSIJA::get("config","model");
        $this->data['email']=array("subject"=>"","from_email"=>$modelConfig->getValue("from_email"),"from_name"=>$modelConfig->getValue("from_name"));  
        $this->data['lists']=$this->__getLists(false);

    }
    
    function __getLists($enabled=true,$count=false){
        $modelList=&WYSIJA::get("list","model");
        /* get lists which have users  and are enabled */
        if($enabled) $enabledstrg=' is_enabled>0 and';
        else $enabledstrg='';
        
        $query="SELECT * FROM ".$modelList->getPrefix()."list WHERE $enabledstrg list_id in (SELECT distinct(list_id) from ".$modelList->getPrefix()."user_list )";
        $listres=$modelList->query("get_res",$query);
        if($count){
          $configM=&WYSIJA::get("config","model");
          $condit='>=';
          if($configM->getValue("confirm_dbleoptin")) $condit='>';
          $qry1="SELECT count(distinct A.user_id) as nbsub,A.list_id FROM `".$modelList->getPrefix()."user_list` as A LEFT JOIN `".$modelList->getPrefix()."user` as B on A.user_id=B.user_id WHERE B.status $condit 0 GROUP BY list_id";  
          
          $total=$modelList->getResults($qry1);
            
            foreach($total as $tot){
                foreach($listres as $key=>$res){
                    if($tot['list_id']==$res['list_id']) $listres[$key]['count']=$tot['nbsub'];
                }
            }
        }
        foreach($listres as $key =>$res){
            if(!isset($res['count']))   $listres[$key]['count']=0;
        }
        return $listres;
    }
    
    function edit($dataPost=false){
        $this->add();
        
        $modelEmail=&WYSIJA::get("email","model");
        
        $this->data['email']=$modelEmail->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        
        if($this->data['email']['status']>0){
            $this->redirect();
        }
        $this->title=sprintf(__('Step %1$s',WYSIJA),1)." | ".$this->data['email']['subject'];
        $modelCamp=&WYSIJA::get("campaign","model");
        $this->data['campaign']=$modelCamp->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        
        $modelCL=&WYSIJA::get("campaign_list","model");
        $this->data['campaign_list']=$modelCL->get(false,array("campaign_id"=>$_REQUEST['id']));
    }
    
    function editTemplate(){
        $this->viewShow='editTemplate';
        
        wp_enqueue_style('thickbox');
        
        $wjEngine =& WYSIJA::get('wj_engine', 'helper');
        /* WJ editor translations */
        $this->jsTrans = array_merge($this->jsTrans, $wjEngine->getTranslations());
        
        $this->jsTrans['savingnl']=__("Saving newsletter...",WYSIJA);
        $this->jsTrans['errorsavingnl']=__("Error Saving newsletter...",WYSIJA);
        $this->jsTrans['savednl']=__("Newsletter has been saved.",WYSIJA);
        $this->jsTrans['previewemail']=__("Sending preview...",WYSIJA);
        $this->jsTrans['alertshowcase']=__("Would you like to have your theme design featured on Wysija's blog?",WYSIJA);
        $this->jsTrans['alertshowcaseextra']=__("If so, click Ok to submit the newsletter you're currently working on for our showcase on www.wysija.com",WYSIJA);
        
        /* WJ editor JS */
        $this->js[]='wysija-editor';
        $this->js[]='wysija-admin-ajax-proto';
        $this->js[]='wysija-admin-ajax';
        $this->js[]='wysija-base-script-64';
        $this->js[]='media-upload';
        $this->js['admin-campaigns-editDetails']='admin-campaigns-editDetails';
        $modelEmail=&WYSIJA::get("email","model");
        $this->data=array();
        $this->data['email']=$modelEmail->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        if($this->data['email']['status']>0){
            $this->redirect();
        }
        
        $this->viewObj->title=sprintf(__('Second step : design "%1$s"',WYSIJA),$this->data['email']['subject']);
        $this->title=sprintf(__('Step %1$s',WYSIJA),2)." | ".$this->data['email']['subject'];
    }
    
    function pause(){
        /* pause the campaign entry */
        if(isset($_REQUEST['id']) && $_REQUEST['id']){
            $modelEmail=&WYSIJA::get("email","model");
            $modelEmail->update(array("status"=>-1),array("campaign_id"=>$_REQUEST['id']));

            $this->notice(__("Sending is now paused.",WYSIJA));
        }

        $this->redirect();
    }
    
    function resume(){
        /* pause the campaign entry */
        if(isset($_REQUEST['id']) && $_REQUEST['id']){
            $modelEmail=&WYSIJA::get("email","model");
            $modelEmail->update(array("status"=>1),array("campaign_id"=>$_REQUEST['id']));
            $this->notice(__("Sending has resumed.",WYSIJA));
        }

        $this->redirect();
    }
    
    function duplicate(){
        /* 1 - copy the campaign entry */
        
        $model=&WYSIJA::get("campaign","model");
        $query='INSERT INTO `'.$model->getPrefix().'campaign` (`name`,`description`) 
            SELECT concat("'.stripslashes(__("Copy of ",WYSIJA)).'",`name`),`description` FROM '.$model->getPrefix().'campaign
            WHERE campaign_id='.(int)$_REQUEST['id'];
        $campaignid=$model->query($query);

        /* 2 - copy the email entry */
        $query='INSERT INTO `'.$model->getPrefix().'email` (`campaign_id`,`subject`,`body`,`params`,`wj_data`,`wj_styles`,`from_email`,`from_name`,`replyto_email`,`replyto_name`,`attachments`,`status`,`created_at`) 
            SELECT '.$campaignid.', concat("'.stripslashes(__("Copy of ",WYSIJA)).'",`subject`),`body`,`params`,`wj_data`,`wj_styles`,`from_email`,`from_name`,`replyto_email`,`replyto_name`,`attachments`,0,'.mktime().' FROM '.$model->getPrefix().'email
            WHERE campaign_id='.(int)$_REQUEST['id'];
        $model->query($query);
        
        /* 3 - copy the campaign_list entry */
        $query="INSERT INTO `".$model->getPrefix()."campaign_list` (`campaign_id`,`list_id`,`filter`) 
            SELECT $campaignid,`list_id`,`filter` FROM ".$model->getPrefix()."campaign_list
            WHERE campaign_id=".(int)$_REQUEST['id'];
        $model->query($query);
        
        $this->notice(__("The newsletter has been duplicated.",WYSIJA));

        $this->redirect('admin.php?page=wysija_campaigns&id='.$campaignid.'&action=edit');
    }
    
    function editDetails(){
        $this->viewObj->title=__('Final step : last details',WYSIJA);
        $this->viewShow='editDetails';
        $this->js[]='wysija-validator';
        $this->jsTrans['previewemail']=__("Sending preview...",WYSIJA);
        $this->jsTrans['alertsend']=__('[#] emails are about to be sent to the list(s) [#nms].',WYSIJA);
        
        
        $modelList=&WYSIJA::get("list","model");
        $modelList->limitON=false;
        $this->data=array();
        $this->data['lists']=$this->__getLists(false,true);
        
        $modelEmail=&WYSIJA::get("email","model");
        $this->data['email']=$modelEmail->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        if($this->data['email']['status']>0){
            $this->redirect();
        }
        $this->title=sprintf(__('Step %1$s',WYSIJA),3)." | ".$this->data['email']['subject'];
        
        $modelCL=&WYSIJA::get("campaign_list","model");
        $this->data['campaign_list']=$modelCL->get(false,array("campaign_id"=>$_REQUEST['id']));

    }
 
    function delete(){
        $this->requireSecurity();
        
        if(isset($_REQUEST['id'])){
            $modelCampaign=&WYSIJA::get("campaign","model");
            $modelCampaign->delete(array("campaign_id"=>$_REQUEST['id']));
            
            $modelCampaignL=&WYSIJA::get("campaign_list","model");
            $modelCampaignL->delete(array("campaign_id"=>$_REQUEST['id']));

            $modelEmail=&WYSIJA::get("email","model");
            $modelEmail->delete(array("campaign_id"=>$_REQUEST['id']));
            
            $this->notice(__("Newsletter deleted.",WYSIJA));

        }else{
            
            $this->notice(__("Newsletter can't be deleted.",WYSIJA));
        }

        $this->redirect();


    }
    
     function savecamp(){
        $this->redirectAfterSave=false;
        $this->requireSecurity();
        /* update email */
        $data=array();

        if(isset($_REQUEST['id'])){
            $modelCampaign=&WYSIJA::get("campaign","model");
            $modelCampaign->update(array("name"=>$_POST['wysija']['email']['subject'],"description"=>""),array("campaign_id"=>$_REQUEST['id']));

            $modelEmail=&WYSIJA::get("email","model");
            $modelEmail->fieldValid=false;
            $campaign_id=$_REQUEST['id'];

            $data['email']['email_id']=$modelEmail->update(array(
                "from_name"=>$_POST['wysija']['email']['from_name'],
                "from_email"=>$_POST['wysija']['email']['from_email'],
                "campaign_id"=>$_REQUEST['id'],
                "subject"=>$_POST['wysija']['email']['subject']),
                    array("campaign_id"=>$_REQUEST['id']));
        }else{
            $modelCampaign=&WYSIJA::get("campaign","model");
            $campaign_id=$modelCampaign->insert(array("name"=>$_POST['wysija']['email']['subject'],"description"=>""));
            
            $modelEmail=&WYSIJA::get("email","model");
            $modelEmail->fieldValid=false;
            $emaildata=array(
                        "from_name"=>$_POST['wysija']['email']['from_name'],
                        "from_email"=>$_POST['wysija']['email']['from_email'],
                        "campaign_id"=>$campaign_id,
                        "subject"=>$_POST['wysija']['email']['subject']);

            $newparams = array(
                'quickselection' => array(
                    'wp-301' => array(
                        'identifier' => 'wp-301',
                        'width' => 281,
                        'height' => 190,
                        'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_07.png",
                        'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/sample-newsletter-01_07-150x150.png"
                    )
                )
            );

            $emaildata['params']=$newparams;
            
            $newwjdata = array(
                'version' => '0.9.1',
                'header' => array(
                    'text' => null,
                    'image' => array(
                        'src' => WYSIJA_EDITOR_IMG."transparent.png",
                        'width' => 600,
                        'height' => 86,
                        'alignment' => 'center',
                        'static' => true
                    ),
                    'alignment' => 'center',
                    'static' => 1,
                    'type' => 'header'
                ),
                'body' => array(
                    'block-1' => array(
                        'text' => array(
                            'value' => "<h2>".__("The Messenger Pigeon's Tale",WYSIJA)."</h2>"
                        ),
                        'image' => null,
                        'alignment' => 'center',
                        'static' => false,
                        'position' => 1,
                        'type' => 'content'
                    ),
                    'block-2' => array(
                        'text' => array(
                            'value' => "<p>".__("The pigeon spread his wings to catch the wind. Even the smallest breeze could make me rise above the the Northern peaks, he thought.",WYSIJA)."</p><p>".
                            __("The city's highrises spread below him. His shadow a simple dot on a sidewalk. For a moment he forgot the weary task at hand. A messenger pigeon he is, after all.",WYSIJA)."</p>"
                        ),
                        'image' => array(
                            'src' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_07.png",
                            'width' => 281,
                            'height' => 190,
                            'alignment' => 'left',
                            'static' => false
                        ),
                        'alignment' => 'left',
                        'static' => false,
                        'position' => 2,
                        'type' => 'content'
                    )
                )
            );

            

            $emaildata['wj_data']=base64_encode(serialize($newwjdata));

            $data['email']['email_id']=$modelEmail->insert($emaildata);
            
            $this->notice(__("Newsletter successfully created.",WYSIJA));
        }
        
        $this->_saveLists($campaign_id,true);
        
        if(isset($_REQUEST['return'])) $this->redirect();
        else {
            $this->redirect("admin.php?page=wysija_campaigns&action=editTemplate&id=".$campaign_id);
        }

    }
    
    function saveemail(){
        $this->redirectAfterSave=false;
        $this->requireSecurity();
     
        if(isset($_REQUEST['return'])) $this->redirect();
        else $this->redirect("admin.php?page=wysija_campaigns&action=editDetails&id=".$_REQUEST['id']);

    }
    
    function savelast(){
        $this->redirectAfterSave=false;
        $this->requireSecurity();
 
        if(!isset($_POST['wysija']['email']['from_name'])|| !isset($_POST['wysija']['email']['from_email']) || !isset($_POST['wysija']['email']['replyto_name']) || !isset($_POST['wysija']['email']['replyto_email'])){
            $this->error(__("Information is missing.",WYSIJA));
            return $this->editDetails();
        }
        
        if(isset($_POST['wysija']['email']["params"]['googletrackingcode']) && $_POST['wysija']['email']["params"]['googletrackingcode'] && (!is_string($_POST['wysija']['email']["params"]['googletrackingcode']) OR preg_match('|[^a-z0-9#_.-]|i',$_POST['wysija']['email']["params"]['googletrackingcode']) !== 0 )){
            //force to simple text
            $this->error(__("Google marketing code needs to be an alphanumeric value.",WYSIJA),1);
            return $this->editDetails();
        }

        
        $updateemail=array(
            "email_id"=>$_POST['wysija']['email']['email_id'],
            "from_name"=>$_POST['wysija']['email']['from_name'],
            "from_email"=>$_POST['wysija']['email']['from_email'],
            "replyto_name"=>$_POST['wysija']['email']['replyto_name'],
            "replyto_email"=>$_POST['wysija']['email']['replyto_email'],
            "subject"=>$_POST['wysija']['email']['subject'],
        );
        
        if(isset($_POST['wysija']['email']['params']))  $updateemail["params"]=$_POST['wysija']['email']['params'];
        
        
        $this->_saveLists($_POST['wysija']['campaign']['campaign_id']);
        
        /**/
        
        if(isset($_POST['submit-draft']) || isset($_POST['submit-pause'])){
            $this->notice(__("Newsletter has been saved as a draft.",WYSIJA));
        }else{
            if(!isset($_POST['submit-resume'])){
                /* add all the subscribers to the queue */
                /* insert select all the subscribers from the lists related to that campaign */
                $modelQ=&WYSIJA::get('queue','model');
                $modelQ->queueCampaign($_POST['wysija']['campaign']['campaign_id']);
            }
            $updateemail["status"]=1;
            $updateemail["sent_at"]=mktime();
            $this->notice(__("Your latest newsletter is being sent.",WYSIJA));
            
        }
        /* update email */
        $modelEmail=&WYSIJA::get("email","model");
        $modelEmail->reset();
        $modelEmail->update($updateemail);
        
        $modelEmail=&WYSIJA::get("campaign","model");
        $modelEmail->reset();
        $updatecampaign=array("campaign_id"=>$_REQUEST['id'],"name"=>$_POST['wysija']['email']['subject']);
        $modelEmail->update($updatecampaign);

        
        return $this->redirect();
    }
    
    
    function _saveLists($campaignId,$flagup=false){
        /* record the list that we have in that campaign */
        $modelCampL=&WYSIJA::get("campaign_list","model");
        if($flagup || (isset($_POST['submit-draft']) || isset($_POST['submit-send'])|| isset($_POST['submit-resume']))){
            
            $modelCampL->delete(array("equal"=>array("campaign_id"=>$campaignId)));
            $modelCampL->reset();
        }
        

        if(isset($_POST['wysija']['campaign_list']['list_id'])){
            //$modelCampL=&WYSIJA::get("campaign_list","model");
            foreach($_POST['wysija']['campaign_list']['list_id'] as $listid){
                $modelCampL->insert(array("campaign_id"=>$campaignId,"list_id"=>$listid));
            }
        }
        
    }
    
    
    function defaultDisplay(){
        $this->title=__('Newsletters',WYSIJA);
        $this->viewShow=$this->action='main';
        $this->js[]='wysija-admin-list';
        $this->jsTrans["selecmiss"]=__('Please select a newsletter.',WYSIJA);
        $this->jsTrans['suredelete']=__("Delete this newsletter for ever?",WYSIJA);
        $this->jsTrans['processqueue']=__("Sending batch of emails...",WYSIJA);
        $this->jsTrans['viewnews']=__("View newsletter",WYSIJA);



        
        $config=&WYSIJA::get("config","model");
        /*if(!$config->getValue("premium_key") && !defined('DOING_AJAX')){
            $msg=__("Support us with a [link][paypal][/link] donation or get the [link_full]full version.[/link_full]",WYSIJA);
            $find=array('[link]','[/link]','[link_full]','[/link_full]','[paypal]');
            $replace=array('<a href="#" title="'.__("Donate now",WYSIJA).'">','</a>','<a href="#" title="'.__("Buy now",WYSIJA).'">','</a>','<img  src="https://www.paypal.com/fr_XC/i/logo/PayPal_mark_37x23.gif" border="0" alt="Paypal">');
            $this->notice(str_replace($find,$replace,$msg));
        }*/
        
        /*get the filters*/
        if(isset($_REQUEST['search']) && $_REQUEST['search']){
            $this->filters["like"]=array();
            foreach($this->searchable as $field)
                $this->filters["like"][$field]=$_REQUEST['search'];
        }
        
        if(isset($_REQUEST['filter-list']) && $_REQUEST['filter-list']){
            $this->filters["equal"]=array('C.list_id'=>$_REQUEST['filter-list']);
        }
        
        if(isset($_REQUEST['filter-date']) && $_REQUEST['filter-date']){
            $this->filters["greater_eq"]=array('created_at'=>$_REQUEST['filter-date']);
            $this->filters["less_eq"]=array('created_at'=>strtotime("+1 month",$_REQUEST['filter-date']));
        }
        
        if(isset($_REQUEST['link_filter']) && $_REQUEST['link_filter']){
            switch($_REQUEST['link_filter']){
                case "draft":
                    $this->filters["equal"]=array('status'=>0);
                    break;
                case "sending":
                    $this->filters["equal"]=array('status'=>1);
                    break;
                case "sent":
                    $this->filters["equal"]=array('status'=>2);
                    break;
                case "paused":
                    $this->filters["equal"]=array('status'=>-1);
                    break;
            }
        }

        $this->modelObj->noCheck=true;
        
        
        /* 0 - counting request */
        $queryCmmonStart="SELECT count(distinct A.campaign_id) as campaigns FROM `".$this->modelObj->getPrefix().$this->modelObj->table_name."` as A";
        $queryCmmonStart.=" LEFT JOIN `".$this->modelObj->getPrefix()."email` as B on A.campaign_id=B.campaign_id";
        $queryCmmonStart.=" LEFT JOIN `".$this->modelObj->getPrefix()."campaign_list` as C on A.campaign_id=C.campaign_id";
        
        /* all the counts query */
        $query="SELECT count(campaign_id) as campaigns, campaign_id, status FROM `".$this->modelObj->getPrefix()."email` WHERE campaign_id > 0 GROUP BY status";

        $countss=$this->modelObj->query("get_res",$query,ARRAY_A);
        $counts=array();
        $total=0;
        foreach($countss as $count){
            switch($count['status']){
                case "0":
                    $type='draft';
                    break;
                case "1":
                    $type='sending';
                    break;
                case "2":
                    $type='sent';
                    break;
                case "-1":
                    $type='paused';
                    break;
            }
            $total=$total+$count['campaigns'];
            $counts[$type]=$count['campaigns'];
        }

        $counts['all']=$total;
        
        $this->modelObj->reset();

        if($this->filters)  $this->modelObj->setConditions($this->filters);  

        /* 1 - campaign request */
        $query="SELECT A.campaign_id, A.name, A.description, B.number_opened,B.number_clicked,B.number_unsub,B.status,B.status,B.created_at,B.email_id FROM `".$this->modelObj->getPrefix().$this->modelObj->table_name."` as A";
        $query.=" LEFT JOIN `".$this->modelObj->getPrefix()."email` as B on A.campaign_id=B.campaign_id";
        $query.=" LEFT JOIN `".$this->modelObj->getPrefix()."campaign_list` as C on A.campaign_id=C.campaign_id";
        $queryFinal=$this->modelObj->makeWhere();
        
        
        /* campaign created the longest ago */
        $query2="SELECT MIN(B.created_at) as datemin FROM `".$this->modelObj->getPrefix().$this->modelObj->table_name."` as A";
        $query2.=" LEFT JOIN `".$this->modelObj->getPrefix()."email` as B on A.campaign_id=B.campaign_id";
        $query2.=" LEFT JOIN `".$this->modelObj->getPrefix()."campaign_list` as C on A.campaign_id=C.campaign_id";
        $queryFinal2=$this->modelObj->makeWhere();

        /* without filter we already have the total number of campaigns */
        if($this->filters)  $this->modelObj->countRows=$this->modelObj->count($queryCmmonStart.$queryFinal);
        else $this->modelObj->countRows=$counts['all'];

        $orderby=" ORDER BY ";
        if(isset($_REQUEST['orderby'])){
            $orderby.=$_REQUEST['orderby']." ".$_REQUEST['ordert'];
        }else{
            $orderby.="A.".$this->modelObj->getPk()." desc";
        }

        $this->data['campaigns']=$this->modelObj->getResults($query.$queryFinal." GROUP BY A.campaign_id".$orderby.$this->modelObj->setLimit());
        
        $emailids=array();
        foreach($this->data['campaigns'] as $emailcamp){
            if($emailcamp['status']==1) $emailids[]=$emailcamp['email_id'];
        }
        $modelQ=&WYSIJA::get("queue","model");
        $modelQ->setConditions(array("email_id"=>$emailids));
        $queue=$modelQ->count();
        if($queue){
            $this->viewObj->queuedemails=$queue;
        }
        $this->modelObj->reset();
        
        $this->data['datemin']=$this->modelObj->query("get_row",$query2.$queryFinal2);
        $this->modelObj->reset();
        
        /* make a loop from the first created to now and increment an array of months */
        $now=mktime();
        $this->data['dates']=array();

        if((int)$this->data['datemin']['datemin']>1){
            setlocale(LC_TIME, "en_US");
            $firstdayof=getdate($this->data['datemin']['datemin']);

            $formtlettres="1 ".date("F",$this->data['datemin']['datemin'])." ".date("Y",$this->data['datemin']['datemin']) ;
            $monthstart=strtotime($formtlettres);

            if($monthstart>0){
               for($i=$monthstart;$i<$now;$i=strtotime("+1 month",$i)){
                    $this->data['dates'][$i]=date_i18n('F Y',$i);//date('F Y',$i);
                } 
            }
            
        }



        /*make the data object for the listing view*/
        $modelList=&WYSIJA::get("list","model");
        
        /* 2 - list request */
        $query="SELECT A.list_id, A.name, count( B.campaign_id ) AS users FROM `".$modelList->getPrefix().$modelList->table_name."` as A";
        $query.=" LEFT JOIN `".$modelList->getPrefix()."campaign_list` as B on A.list_id = B.list_id";
        $query.=" GROUP BY A.list_id";
        $listsDB=$modelList->getResults($query); 
        
        $lists=array();
        foreach($listsDB as $listobj){
            $lists[$listobj["list_id"]]=$listobj;
        }
        
        $listsDB=null;
 
        $campaign_ids_sent=$campaign_ids=array();
        foreach($this->data['campaigns'] as $campaign){
            $campaign_ids[]=$campaign['campaign_id'];

            if(in_array((int)$campaign['status'],array(-1,1,2)))  $campaign_ids_sent[]=$campaign['campaign_id'];
        }

        /* 3 - campaign_list request & count request for queue */
        if($campaign_ids){
            $modeluList=&WYSIJA::get("campaign_list","model");
            $userlists=$modeluList->get(array("list_id","campaign_id"),array("campaign_id"=>$campaign_ids));
            
            if($campaign_ids_sent){
                $modeluList=&WYSIJA::get("email_user_stat","model");
                $queuedtotal=$modeluList->getResults("SELECT COUNT(A.user_id) as count,B.campaign_id FROM `".$modeluList->getPrefix()."queue` as A 
                     JOIN `".$modeluList->getPrefix()."email` as B on A.email_id=B.email_id 
                        WHERE B.campaign_id IN (".implode(",",$campaign_ids_sent).") group by B.email_id");
                
                $senttotalgroupedby=$modeluList->getResults("SELECT COUNT(A.user_id) as count,B.campaign_id,B.email_id,B.status,A.status as statususer FROM `".$modeluList->getPrefix().$modeluList->table_name."` as A 
                     JOIN `".$modeluList->getPrefix()."email` as B on A.email_id=B.email_id 
                        WHERE B.campaign_id IN (".implode(",",$campaign_ids_sent).") group by A.status,B.email_id");//,A.status
                $updateEmail=array();
                $columnnamestatus=array(0=>"number_sent",1=>"number_opened",2=>"number_clicked",3=>"number_unsub",-1=>"number_bounce");
                foreach($senttotalgroupedby as $sentbystatus){
                    if($sentbystatus['statususer']!="-2")   $updateEmail[$sentbystatus['email_id']][$columnnamestatus[$sentbystatus['statususer']]]=$sentbystatus['count'];
                    if(isset($senttotal[$sentbystatus['campaign_id']])){
                        $senttotal[$sentbystatus['campaign_id']]['count']=(int)$senttotal[$sentbystatus['campaign_id']]['count']+(int)$sentbystatus['count'];
                    }else{
                        unset($sentbystatus['statususer']);
                        $senttotal[$sentbystatus['campaign_id']]=$sentbystatus;
                    }
                }
                
                $modelEmail=&WYSIJA::get("email","model");
                
                foreach($updateEmail as $emailid=>$update){
                    
                    foreach($columnnamestatus as $v){
                        if(!isset($update[$v])) $update[$v]=0;
                    }
                    
                    
                    $modelEmail->update($update,array("email_id"=>$emailid));
                    $modelEmail->reset();
                }
                
    
                
                $modelC=&WYSIJA::get("config","model");
                $schedules=wp_get_schedules();

                if(isset($senttotal) && $senttotal){
                    foreach($senttotal as $sentot){
                        if($sentot){
                            $this->data['sent'][$sentot['campaign_id']]['total']=$sentot['count'];
                            $this->data['sent'][$sentot['campaign_id']]['to']=$sentot['count'];
                        }else{
                            $this->data['sent'][$sentot['campaign_id']]['total']=$this->data['sent'][$sentot['campaign_id']]['to']=0;
                        }
                        $this->data['sent'][$sentot['campaign_id']]['status']=$sentot['status'];
                        $this->data['sent'][$sentot['campaign_id']]['left']= (int)$this->data['sent'][$sentot['campaign_id']]['total'] - (int)$this->data['sent'][$sentot['campaign_id']]['to'];

                    }
                }

                foreach($queuedtotal as $sentot){
                    if(!isset($this->data['sent'][$sentot['campaign_id']])) {
                        $this->data['sent'][$sentot['campaign_id']]['total']=0;
                        $this->data['sent'][$sentot['campaign_id']]['to']=0;
                    }
                    
                    $this->data['sent'][$sentot['campaign_id']]['total']=$this->data['sent'][$sentot['campaign_id']]['total']+$sentot['count'];
                    $this->data['sent'][$sentot['campaign_id']]['left']= (int)$this->data['sent'][$sentot['campaign_id']]['total'] - (int)$this->data['sent'][$sentot['campaign_id']]['to'];
                    
                }

                $timesec=$schedules[wp_get_schedule('wysija_cron_queue')]['interval'];
                $status_sent_complete=array();
               
               if(isset($this->data['sent'])){
                   foreach($this->data['sent'] as $key => $camp){

                        if($this->data['sent'][$key]['left']>0){
                            $cronsneeded=ceil($this->data['sent'][$key]['left']/$modelC->getValue('sending_emails_number'));
                            $this->data['sent'][$key]['remaining_time']=$cronsneeded *$timesec;

                            //$schedules=wp_get_schedules();
                            $this->data['sent'][$key]['next_batch']=(int)wp_next_scheduled( 'wysija_cron_queue')-mktime();
                            $this->data['sent'][$key]['remaining_time']=$this->data['sent'][$key]['remaining_time']-($timesec)+$this->data['sent'][$key]['next_batch'];
                        }else{
                            if($this->data['sent'][$key]['status']==1) $status_sent_complete[]=$key;
                        }
                    } 

                   
               }
               
                
                
                if(count($status_sent_complete)>0){
                    $modelEmail=&WYSIJA::get("email","model");
                    $modelEmail->noCheck=true;
                    $modelEmail->reset();

                    $modelEmail->update(array("status"=>2),array("equal"=>array("campaign_id"=>$status_sent_complete)));
                }

            }
            
        }

        $this->data['lists']=$lists;

        $this->data['counts']=array_reverse($counts);

        /* regrouping all the data in the same array */
        foreach($this->data['campaigns'] as $keysus=>$campaign){
            /* default key while we don't have the data*/
            //TODO add data for stats about emails opened clicked etc
            $this->data['campaigns'][$keysus]["emails"]=0;
            $this->data['campaigns'][$keysus]["opened"]=0;
            $this->data['campaigns'][$keysus]["clicked"]=0;

            if($userlists){
                foreach($userlists as $key=>$userlist){
                    if($campaign["campaign_id"]==$userlist["campaign_id"] && isset($lists[$userlist["list_id"]])){
                        if(!isset($this->data['campaigns'][$keysus]["lists"]) ) $this->data['campaigns'][$keysus]["lists"]=$lists[$userlist["list_id"]]["name"];
                        else $this->data['campaigns'][$keysus]["lists"].=", ".$lists[$userlist["list_id"]]["name"];
                    }
                }
            }
        }
        
        if(!$this->data['campaigns']){
            $this->notice(__("We looked everywhere, but we couldn't find the newsletter you're looking for.",WYSIJA));
        }

    }
    
    
    
    function setviewStatsfilter(){
        /*get the filters*/
        $this->searchable=array("email", "firstname", "lastname");
        $this->filters=array();
        if(isset($_REQUEST['search']) && $_REQUEST['search']){
            $this->filters["like"]=array();
            foreach($this->searchable as $field)
                $this->filters["like"][$field]=$_REQUEST['search'];
        }
        $this->tableQuery='email_user_stat';
        $this->statusemail='B.status as umstatus';
        if(isset($_REQUEST['link_filter']) && $_REQUEST['link_filter']){
            switch($_REQUEST['link_filter']){
                case "notsent":
                    $this->tableQuery='queue';
                    $this->statusemail="-2 as umstatus";
                    break;
                case "sent":
                    $this->filters["equal"]=array('B.status'=>0);
                    break;
                case "bounced":
                    $this->filters["equal"]=array('B.status'=>-1);
                    break;
                case "opened":
                    $this->filters["equal"]=array('B.status'=>1);
                    break;
                case "clicked":
                    $this->filters["equal"]=array('B.status'=>2);
                    break;
                case "unsubscribe":
                    $this->filters["equal"]=array('B.status'=>3);
                    break;
            }
        }
    }
    
    function viewstats(){
        
        $this->js[]='wysija-admin-list';
        $this->js[]='wysija-charts';
        $this->viewShow='viewstats';
        $limit_pp=false;
        if(isset($this->modelObj->limit_pp)) $limit_pp = $this->modelObj->limit_pp;
        $this->modelObj->limitON=false;
        $campaign=$this->modelObj->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        
        $this->viewObj->namecampaign=$campaign['name'];
        $this->viewObj->title=sprintf(__('Stats : %1$s',WYSIJA),$campaign['name']);
        
        $this->modelObj=&WYSIJA::get("email","model");
        $this->modelObj->limitON=false;
        
        $emailObj=$this->modelObj->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        $this->viewObj->model=$this->modelObj;
        
        $this->setviewStatsfilter();

        $this->modelObj->reset();
        $this->modelObj->noCheck=true;
        
        /* 0 - counting request */
        $queryCmmonStart="SELECT count(distinct B.user_id) as users FROM `".$this->modelObj->getPrefix()."user` as A";
        $queryCmmonStart.=" LEFT JOIN `".$this->modelObj->getPrefix()."email_user_stat` as B on A.user_id=B.user_id";

        /* all the counts query */
        $query="SELECT count(user_id) as users, status FROM `".$this->modelObj->getPrefix()."email_user_stat` as A 
            WHERE A.email_id=".$emailObj['email_id']." GROUP BY status";
        $countss=$this->modelObj->query("get_res",$query,ARRAY_A);

        /*we also count what is in the queue */
        $query="SELECT count(user_id) as users FROM `".$this->modelObj->getPrefix()."queue` as A 
            WHERE A.email_id=".$emailObj['email_id'];
        $countss[-2]['status']=-3;
        $countss[-2]['users']=$this->modelObj->count($query);

        $counts=array();
        $truetotal=$total=0;

        foreach($countss as $count){
            switch($count['status']){
                case "-3":
                    $type='inqueue';
                    break;
                case "-2":
                    $type='notsent';
                    break;
                case "-1":
                    $type='bounced';
                    break;
                case "0":
                    $type='sent';
                    break;
                case "1":
                    $type='opened';
                    break;
                case "2":
                    $type='clicked';
                    break;
                case "3":
                    $type='unsubscribe';
                    break;
            }
            if($count['status']!="-2")  $total=$total+$count['users'];
            $truetotal=$truetotal+$count['users'];
            $counts[$type]=$count['users'];
        }

        $counts['allsent']=$total;
        $counts['all']=$truetotal;

        $this->modelObj->reset();
        $this->filters['equal']["B.email_id"]=$emailObj['email_id'];
        
        $this->modelObj->noCheck=true;
        if($this->filters)  $this->modelObj->setConditions($this->filters);
        
        //$this->modelObj->setConditions(array("equal"=>array("B.email_id"=>$emailObj['email_id'])));
        
        /* 1 - user request */
        $query="SELECT A.user_id, A.firstname, A.lastname,A.status as ustatus,".$this->statusemail." , A.email, A.created_at FROM `".$this->modelObj->getPrefix()."user` as A";
        $query.=" LEFT JOIN `".$this->modelObj->getPrefix().$this->tableQuery."` as B on A.user_id=B.user_id";
        $queryFinal=$this->modelObj->makeWhere();

        /* without filter we already have the total number of subscribers */
        if($this->filters)  $this->modelObj->countRows=$this->modelObj->count($queryCmmonStart.$queryFinal);
        else $this->modelObj->countRows=$counts['all'];

        $orderby=" ORDER BY ";
        if(isset($_REQUEST['orderby'])){
            $orderby.=$_REQUEST['orderby']." ".$_REQUEST['ordert'];
        }else{
            $orderby.=$this->modelObj->pk." desc";
        }
        $this->modelObj->limitON=true;

        $this->data['subscribers']=$this->modelObj->getResults($query.$queryFinal." GROUP BY A.user_id".$orderby.$this->modelObj->setLimit(0,$limit_pp));
        $this->modelObj->reset();
        
        /*make the data object for the listing view*/
        $modelList=&WYSIJA::get("list","model");
        
        /* 2 - list request */
        $query="SELECT A.list_id, A.name,A.is_enabled, count( B.user_id ) AS users FROM `".$modelList->getPrefix().$modelList->table_name."` as A";
        $query.=" LEFT JOIN `".$modelList->getPrefix()."user_list` as B on A.list_id = B.list_id";
        $query.=" GROUP BY A.list_id";
        $listsDB=$modelList->getResults($query); 
        
        $lists=array();
        foreach($listsDB as $listobj){
            $lists[$listobj["list_id"]]=$listobj;
        }
        
        $listsDB=null;
 
        $user_ids=array();
        foreach($this->data['subscribers'] as $subscriber){
            $user_ids[]=$subscriber['user_id'];
        }
        
        /* 3 - user_list request */
        if($user_ids){
            $modeluList=&WYSIJA::get("user_list","model");
            $userlists=$modeluList->get(array("list_id","user_id"),array("user_id"=>$user_ids));
        }
        

        $this->data['lists']=$lists;
        $this->data['counts']=array_reverse($counts);

        /* regrouping all the data in the same array */
       foreach($this->data['subscribers'] as $keysus=>$subscriber){
            /* default key while we don't have the data*/
            //TODO add data for stats about emails opened clicked etc
            $this->data['subscribers'][$keysus]["emails"]=0;
            $this->data['subscribers'][$keysus]["opened"]=0;
            $this->data['subscribers'][$keysus]["clicked"]=0;

            if($userlists){
                foreach($userlists as $key=>$userlist){
                    if($subscriber["user_id"]==$userlist["user_id"] && isset($lists[$userlist["list_id"]])){
                        if(!isset($this->data['subscribers'][$keysus]["lists"]) ) $this->data['subscribers'][$keysus]["lists"]=$lists[$userlist["list_id"]]["name"];
                        else $this->data['subscribers'][$keysus]["lists"].=", ".$lists[$userlist["list_id"]]["name"];
                    }
                }
            }
        }
        
        
        /* we prepare the data to be pased to the charts script*/
        //$this->data['charts']['title']=__('Statistics for campaign.',WYSIJA); 
        $this->data['charts']['title']=" "; 
        $this->data['charts']['stats']=array();
        $keys=array(
            'opened'=>array('order'=>0),
            'bounced'=>array('order'=>1),
            'sent'=>array('order'=>2),
            'clicked'=>array('order'=>3),
            'unsubscribe'=>array('order'=>4),
            'notsent'=>array('order'=>5),
            'inqueue'=>array('order'=>6)
            );

        foreach(array_reverse($counts) as $key=> $count){
            if($key!="all" && $key!="allsent"){
                if(isset($keys[$key]['name']))  $name=$keys[$key]['name'];
                else $name=$this->viewObj->getTransStatusEmail($key);
                if($count>0) $this->data['charts']['stats'][$keys[$key]['order']]=array("name"=>$name,"number"=>$count);
            }
        }
        
        $modelEUU=&WYSIJA::get('email_user_url',"model");
        $modelEUU->colCheck=false;
        $modelEUU->setConditions(array("equal"=>array("A.email_id"=>$emailObj['email_id'])));
        $query="SELECT count(A.user_id) as count,A.*,B.*,C.subject as name FROM `".$modelEUU->getPrefix().$modelEUU->table_name."` as A JOIN `".$modelEUU->getPrefix()."url` as B on A.url_id=B.url_id JOIN `".$modelEUU->getPrefix()."email` as C on C.email_id=A.email_id ";
        $query.=$modelEUU->makeWhere();
        $query.="GROUP BY A.url_id";
        $query.=" ORDER BY count Desc";
        $this->data['clicks']=$modelEUU->query("get_res",$query,ARRAY_A);

        /*echo '<pre>';
        print_r($this->data['clicks']);
        echo '</pre>';*/
        foreach($this->data['clicks'] as $k => &$v){
            $this->data['clicks'][$k]['name']="<strong>".sprintf(_n('%1$s hit', '%1$s hits', $v['count'],WYSIJA), $v['count'])."</strong> ";
            //header("Content-type:text/html;charset=utf-8");
            $v['url']=urldecode(utf8_encode($v['url']));
        }
   
        $this->data['email']=$emailObj;
        $chartsencoded=base64_encode(json_encode($this->data['charts']));
        wp_enqueue_script('wysija-admin-subscribers-edit-manual', WYSIJA_URL."js/admin-subscribers-edit-manual.php?data=".$chartsencoded, array( 'wysija-charts' ), true);
        
        if(!$this->data['subscribers']){
            $this->notice(__("Your request can't retrieve any subscribers. Change your filters!",WYSIJA));
        }

    }
    
    function getListSubscriberQry($selectcolumns){
        $this->modelObj=&WYSIJA::get("email","model");
        $this->emailObj=$this->modelObj->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        
        /* use the filter if there is */
        $this->setviewStatsfilter();
        
        if($selectcolumns=="B.user_id"){
            //unset($this->filters["like"]);
        }
        
        $this->filters['equal']["B.email_id"]=$this->emailObj['email_id'];
        $this->modelObj->noCheck=true;
        if($this->filters)  $this->modelObj->setConditions($this->filters);

        /* select insert all the subscribers from that campaign into user_list */
        if($selectcolumns=="B.user_id"){
           $query="SELECT $selectcolumns FROM `".$this->modelObj->getPrefix().$this->tableQuery."` as B";
           $query.=$this->modelObj->makeWhere(); 
        }else{
            $query="SELECT $selectcolumns FROM `".$this->modelObj->getPrefix()."user` as A";
            $query.=" LEFT JOIN `".$this->modelObj->getPrefix().$this->tableQuery."` as B on A.user_id=B.user_id";
            $query.=$this->modelObj->makeWhere();
        }

        return $query;
    }
    
    function createnewlist(){
        /* get the campaign details */
        
        $campaign=$this->modelObj->getOne(false,array("campaign_id"=>$_REQUEST['id']));
        $this->modelObj->reset();
        
        /* set the name of the new list*/
        $prefix="";
        if(isset($_REQUEST['link_filter'])) $prefix=" (".$this->viewObj->getTransStatusEmail($_REQUEST['link_filter']).")";
        $listname=sprintf(__('Segment of %1$s',WYSIJA),$campaign['name'].$prefix);
        
        /*insert new list*/
        $modelL=&WYSIJA::get("list","model");
        $listid=$modelL->insert(array("is_enabled"=>1,"name"=>$listname,"description"=>__("List created based on a newsletter segment.",WYSIJA)));
        
        /* get list of subscribers filtered or not */
        $query=$this->getListSubscriberQry($listid.", A.user_id, 0, 0");

        $query2="INSERT INTO `".$this->modelObj->getPrefix()."user_list` (`list_id`,`user_id`,`sub_date`,`unsub_date`) 
            ".$query;
        
        $this->modelObj->query($query2);
        
        $this->notice(sprintf(__('A new list "%1$s" has been created out of this segment.',WYSIJA), $listname));
        $this->redirect("admin.php?page=wysija_campaigns&action=viewstats&id=".$_REQUEST['id']);
    }

    function unsubscribeall(){
        /* delete from user_lists where select from email_user_stat */
        $query=$this->getListSubscriberQry("B.user_id");
        //$query2="DELETE FROM `".$this->modelObj->getPrefix()."user_list` as A LEFT JOIN `".$this->modelObj->getPrefix()."list` as B on A.list_id=B.list_id where B.is_enabled >0 and A.user_id IN ($query)";
        $query2="DELETE FROM `".$this->modelObj->getPrefix()."user_list` where user_id IN ($query) AND list_id not IN(SELECT list_id from `".$this->modelObj->getPrefix()."list` WHERE is_enabled<1)";
        $this->modelObj->query($query2);
        
        /* unsubscribe from user where select from email_user_stat */
        $query2="UPDATE `".$this->modelObj->getPrefix()."user` set `status`=-1 where user_id IN ($query)";
        $this->modelObj->query($query2);
        
        $this->notice(__("The segment has been unbsubscribed from all the lists.",WYSIJA));
        $this->redirect("admin.php?page=wysija_campaigns&action=viewstats&id=".$_REQUEST['id']);
    }
    
    
    function sendconfirmation(){
        /* delete from user_lists where select from email_user_stat */
        $query=$this->getListSubscriberQry("B.user_id ");
        
        $user_ids=$this->modelObj->query("get_res",$query);
        
        $uids=array();
        foreach($user_ids as $data){
            $uids[]=$data['user_id'];
        }
        
        $helperUser=&WYSIJA::get("user","helper");
        $helperUser->sendConfirmationEmail($uids);
        $this->redirect("admin.php?page=wysija_campaigns&action=viewstats&id=".$_REQUEST['id']);
    }
    
    
    function removequeue(){
        /* delete from queue where select from email_user_stat */
        $query=$this->getListSubscriberQry("B.user_id");
        $query2="DELETE FROM `".$this->modelObj->getPrefix()."queue` where user_id IN ($query) AND email_id=".$this->emailObj['email_id'];
        $this->modelObj->query($query2);
        
        $this->notice(__("The segment has been removed from the queue of this newsletter.",WYSIJA));
        $this->redirect("admin.php?page=wysija_campaigns&action=viewstats&id=".$_REQUEST['id']);
    }
    
    function export(){
        /* select from email_user_stat left join user */
        $query=$this->getListSubscriberQry("B.user_id");
        $result=$this->modelObj->query("get_res",$query);
        $user_ids=array();
        foreach($result as $user) $user_ids[]=$user['user_id'];
        
        $fileHelp=&WYSIJA::get("file","helper");
        $tempfilename=$fileHelp->temp(implode(",",$user_ids),"export_userids",".txt");

        //$this->redirect("admin.php?page=wysija_campaigns&action=viewstats&id=".$_REQUEST['id']."&user_ids=".serialize($result));
        $this->redirect("admin.php?page=wysija_subscribers&action=exportcampaign&camp_id=".$_REQUEST['id']."&file_name=".  base64_encode($tempfilename['path']));
    }

    
    
    function unsubscribelist($data){

        $modelL=&WYSIJA::get("list","model");
        $list=$modelL->getOne(false,array("list_id"=>$data['listid']));
        if($list['is_enabled']){
            /* delete from user_lists where select from email_user_stat */
            $query=$this->getListSubscriberQry("B.user_id");
            $query2="DELETE FROM `".$this->modelObj->getPrefix()."user_list` where user_id IN ($query) and list_id=".$data['listid'];
            $this->modelObj->query($query2);

            $this->notice(sprintf(__('The segment has been unbsubscribed from the list "%1$s"',WYSIJA),$list['name']));
        }else{
            $this->notice(sprintf(__('The segment cannot be unbsubscribed from an [IMPORT] list.',WYSIJA),$list['name']));
        }
        
        $this->redirect("admin.php?page=wysija_campaigns&action=viewstats&id=".$_REQUEST['id']);
    }
    
    
    
    function articles(){
       $this->iframeTabs=array('articles'=>__("Article Selection",WYSIJA)); 
       $this->js[]='wysija-admin-ajax';
       $this->js[]='wysija-base-script-64';
       
       $_GET['tab']='articles';
       return $this->popupContent();
    }
    
    function themeupload(){
        $helperToolbox=&WYSIJA::get("toolbox","helper");
        $bytes=$helperToolbox->get_max_file_upload();

        if(isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH']>$bytes['maxbytes']){
            if(isset($_FILES['my-theme']['name']) && $_FILES['my-theme']['name']){
                $filename=$_FILES['my-theme']['name'];
            }else{
                $filename="";
            }

            $this->error(sprintf(__('Upload error, file %1$s is too large! (MAX:%2$s)',WYSIJA),$filename,$bytes['maxmegas']),true);
            $this->redirect('admin.php?page=wysija_campaigns&action=themes');

            return false;
        }

        if($_FILES['my-theme']['type']=="application/zip"){
            $ZipfileResult=trim(file_get_contents($_FILES['my-theme']['tmp_name']));
        
            $themesHelp=&WYSIJA::get("themes","helper");
            $result=$themesHelp->installTheme($_FILES['my-theme']['tmp_name'],true);
            $this->redirect('admin.php?page=wysija_campaigns&action=themes');

            return true;
        }else{
            $this->error(__("Wysija themes need to be in the zip format.",WYSIJA),1);
            $this->redirect('admin.php?page=wysija_campaigns&action=themes');
            return false;
        }
        
    }
    
    function themes(){
       $this->iframeTabs=array('themes'=>__("Install Themes",WYSIJA)); 
       $this->js[]='wysija-admin-ajax';
       $this->js[]='wysija-base-script-64';
       $this->jsTrans['viewinfos']=__("Details & PSD",WYSIJA);
       $this->jsTrans['viewback']=__("<< Back",WYSIJA);
       $this->jsTrans['install']=__("Install",WYSIJA);
       $this->jsTrans['reinstall']=__("Reinstall",WYSIJA);
       $this->jsTrans['premiumonly']=__("Premium Only",WYSIJA);
       
       $configM=&WYSIJA::get("config","model");
       if($configM->getValue("premium_key"))$this->jsTrans['ispremium']=1;
       else $this->jsTrans['ispremium']=0;
       
       $this->jsTrans['premiumfiles']=__('Photoshop file available for Premium users. [link]Get Premium Now![/link]',WYSIJA);
       $helperLicence=&WYSIJA::get("licence","helper");
       $urlpremium="http://www.wysija.com/?wysijap=checkout&wysijashop-page=1&testprod=1&controller=orders&action=checkout&popformat=1&wysijadomain=".$helperLicence->getDomainInfo();

       $this->jsTrans['premiumfiles']=str_replace(array('[link]','[/link]'),array('<a href="'.$urlpremium.'">','</a>'),$this->jsTrans['premiumfiles']);

       $this->jsTrans['showallthemes']=__('Show all themes',WYSIJA);
       $this->jsTrans['totalvotes']=__('(%1$s votes)',WYSIJA);
       $this->jsTrans['voterecorded']=__("Your vote has been recorded.",WYSIJA);
       $this->jsTrans['votenotrecorded']=__("Your vote could not be recorded.",WYSIJA);
       $this->jsTrans['reinstallwarning']=__('Watch out! If you reinstall this theme all the files which are in the folder:/wp-content/uploads/wysija/themes/%1$s will be overwritten. Are you sure you want to reinstall?',WYSIJA);
       $this->jsTrans['errorconnecting']=__("We were unable to contact the API, the site may be down. Please try again later.",WYSIJA);
       
       $this->jsTrans['viewallthemes']=__('View all themes by %1$s',WYSIJA);
       $this->jsTrans['downloadpsd']=__("Download original Photoshop file",WYSIJA);
       $this->jsTrans['viewauthorsite']=__("View author's website",WYSIJA);
       $this->jsTrans['stars']=__('Average rating: %1$s',WYSIJA);
       $this->jsTrans['starsyr']=__('My rating: %1$s',WYSIJA);
       $this->jsTrans['downloads']=__('Downloads: %1$s',WYSIJA);
       $this->jsTrans['tags']=__('Tags: %1$s',WYSIJA);
       $this->jsTrans['lastupdated']=__('Last updated: %1$s',WYSIJA);
       $this->jsTrans['includes']=__('Includes: %1$s',WYSIJA);
       
       $themesHelp=&WYSIJA::get("themes","helper");
       
       $this->jsTrans['installedthemes']=$themesHelp->getInstalled();
       
        $url=admin_url('admin.php');
        $helperToolbox=&WYSIJA::get("toolbox","helper");
        $domain_name=$helperToolbox->_make_domain_name($url);
       $this->jsTrans['domainname']=$domain_name;

       $_GET['tab']='themes';
       
       return $this->popupContent();
    }
    
    function bookmarks() {
        $this->iframeTabs=array('bookmarks'=>__("Bookmarks Selection",WYSIJA));
        $this->js[]='wysija-admin-ajax';
        $_GET['tab']='bookmarks';
       return $this->popupContent();
    }
    
    function dividers() {
        $this->iframeTabs=array('dividers'=>__("Dividers Selection",WYSIJA));
        $this->js[]='wysija-admin-ajax';
        $this->js[]='wysija-base-script-64';
        
        $_GET['tab']='dividers';
        
        // get dividers
        $dividersHelper =& WYSIJA::get('dividers', 'helper');
        $this->data['dividers'] = $dividersHelper->getAll();
        
        $modelEmail =& WYSIJA::get('email', 'model');
        $this->data['email'] = $modelEmail->getOne(false, array('campaign_id' => $_REQUEST['campaignId']));
        
        
       return $this->popupContent();
    }

    function image_data() {
        $_GET['url']=(isset($_GET['url']) && $_GET['url'] !== '') ? trim($_GET['url']) : null;
        $_GET['alt']=(isset($_GET['alt'])) ? trim($_GET['alt']) : '';
        $this->iframeTabs=array('image_data'=>__("Image Parameters",WYSIJA)); 
       
       $_GET['tab']='image_data';
       return $this->popupContent();
    }
    
    
    
    function medias(){
       $this->popupContent();
    }
    
    
    function special_wysija_browse() {
        $this->_wysija_subaction();
        $this->jsTrans['deleteimg']=__("Delete image for all newsletters?",WYSIJA);
        return wp_iframe( array($this->viewObj,'popup_wysija_browse'), array() );
    }
    
    function special_wp_browse() {
        $this->_wysija_subaction();
        $this->jsTrans['deleteimg']=__("This image might be in an article. Delete anyway?",WYSIJA);
        return wp_iframe( array($this->viewObj,'popup_wp_browse'), array() );
    }
    
    
    function _wysija_subaction() {
        
        //$this->js[]='wysija-admin-ajax';
        //dbg($_REQUEST);
        if(isset($_REQUEST['subaction'])){
            if($_REQUEST['subaction']=="delete"){
                if(isset($_REQUEST['imgid']) && $_REQUEST['imgid']>0){
                    /* delete the image with id imgid */
                     $res=wp_delete_attachment($_REQUEST['imgid'],true);
                     if($res){
                         $this->notice(__("Image has been deleted.",WYSIJA));
                     }
                }
            }
        }
        return true;
    }
    
    function special_wp_upload() {
        
        wp_enqueue_script('swfupload-all');
        wp_enqueue_script('swfupload-handlers');
        wp_enqueue_script('wysija-upload-handlers',WYSIJA_URL."js/jquery/uploadHandlers.js");
        wp_enqueue_script('image-edit');
        wp_enqueue_script('set-post-thumbnail' );
        wp_enqueue_style('imgareaselect');
        
        $errors = array();
	$id = 0;
        if(isset($_GET['flash']))$_GET['flash']=1;
	if ( isset($_POST['html-upload']) && !empty($_FILES) ) {
		// Upload File button was clicked
		$id = media_handle_upload('async-upload', $_REQUEST['post_id']);
		unset($_FILES);
		if ( is_wp_error($id) ) {
			$errors['upload_error'] = $id;
			$id = false;
		}
	}

	if ( !empty($_POST['insertonlybutton']) ) {
		$href = $_POST['insertonly']['href'];
		if ( !empty($href) && !strpos($href, '://') )
			$href = "http://$href";

		$title = esc_attr($_POST['insertonly']['title']);
		if ( empty($title) )
			$title = basename($href);
		if ( !empty($title) && !empty($href) )
			$html = "<a href='" . esc_url($href) . "' >$title</a>";
		$html = apply_filters('file_send_to_editor_url', $html, esc_url_raw($href), $title);
		return media_send_to_editor($html);
	}

	if ( !empty($_POST) ) {
		$return = media_upload_form_handler();

		if ( is_string($return) )
			return $return;
		if ( is_array($return) )
			$errors = $return;
	}

	if ( isset($_POST['save']) ) {
		$errors['upload_notice'] = __('Saved.',WYSIJA);
		return media_upload_gallery();
	}
        
        
        return wp_iframe( array($this->viewObj,'popup_wp_upload'), $errors );
    }
    
}