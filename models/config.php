<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_model_config extends WYSIJA_object{
    var $name_option="wysija";
    var $cboxes=array(
        "emails_notified_when_sub",
        "emails_notified_when_unsub",
        "emails_notified_when_bounce",
        "emails_notified_when_dailysummary",
        "bounce_process_auto",
        "debug_on"
    );
    var $defaults=array(
        "version"=>"1.0",
        "limit_listing"=>10,
        "role_campaign"=>"switch_themes",
        "emails_notified_when_unsub" =>true,
        "sending_method"=>"gmail",
        "sending_emails_number"=>'200',
        "sending_method"=>"site",
        "sending_emails_site_method"=>"phpmail",
        "smtp_port"=>"",
        "smtp_auth" =>true,
        "bounce_port"=>"",
        "confirm_dbleoptin" =>1,
        "bounce_selfsigned"=>0,
        "bounce_email_notexists"=>"unsub",
        "bounce_inbox_full"=>"not",
        "pluginsImported"=>false,
        "advanced_charset"=>"UTF-8",
        "sendmail_path"=>"/usr/sbin/sendmail",
        "sending_emails_each"=>"daily",
        "bounce_max"=>8
        
    );
    var $values=array();
    
    function WYSIJA_model_config(){
        /* definition of extra translated defaults fields */
        $this->defaults["confirm_email_title"]=sprintf(__('Confirm your subscription to %1$s',WYSIJA),get_option('blogname'));
        $this->defaults["confirm_email_body"]=__("Hello! 
            
Hurray! You've subscribed to our site. 
We need you to activate your 
subscription by clicking the link 
below: 

[activation_link]Click here to confirm your subscription.[/activation_link]

Thank you, 

The team!
",WYSIJA);
        
        $this->defaults["subscribed_title"]=__("You've subscribed!",WYSIJA);
        $this->defaults["subscribed_subtitle"]=__("Yup, we've added you to our list. You'll hear from us shortly.",WYSIJA);
        $this->defaults["unsubscribed_title"]=__("You've unsubscribed!",WYSIJA);
        $this->defaults["unsubscribed_subtitle"]=__("Great, you'll never hear from us again!",WYSIJA);

        $encoded_option=get_option($this->name_option);
        $installApp=false;
        if($encoded_option){
            $this->values=unserialize(base64_decode($encoded_option));
            if(!isset($this->values['installed'])) $installApp=true;

        }else $installApp=true;

        
        /*install the application because there is no option setup it's safer than the classic activation scheme*/
        if($installApp){
            $installer=&WYSIJA::get("install","helper");
            add_action( 'admin_init', array($installer,'install') );
        }

        if(isset($_REQUEST['wysija-uninstall']) && is_admin()){
            $uninstaller=&WYSIJA::get("uninstall","helper");
            add_action( 'admin_init', array($uninstaller,'uninstall') );
        }
        
    }
    
    /**
     * we have a specific save for option since we are saving it in wordpress options table
     * @param type $data
     * @param type $savedThroughInterface 
     */
    function save($data=false,$savedThroughInterface=false) {

        if($data){
            /* when saving configuration from the settings page we need to make sure that if checkboxes have been unticked we remove the corresponding option */
            if($savedThroughInterface){
                foreach($this->cboxes as $cbox){
                    if(!in_array($cbox,$data) && isset($this->values[$cbox])){
                        $this->values[$cbox]=false;
                    }
                }
                
                /* in that case the admin changed the frequency of the wysija cron meaning that we need to clear it */
                if($data['sending_emails_each']!=$this->getValue("sending_emails_each")){
                    wp_clear_scheduled_hook('wysija_cron_queue');
                    $data['last_save']=mktime();
                }
                
                if(isset($data['bouncing_emails_each']) && $data['bouncing_emails_each']!=$this->getValue("bouncing_emails_each")){
                    wp_clear_scheduled_hook('wysija_cron_bounce');
                    $data['last_save']=mktime();
                }
                
                /* if saved with gmail then we set up the smtp settings */
                if($data['sending_method']=="gmail") {
                    $data['smtp_host']='smtp.gmail.com';
                    $data['smtp_port']='465';
                    $data['smtp_secure']='ssl';
                    $data['smtp_auth']=true;
                }
                
                /* specific case to identify common action to different rules there some that doesnt show in the interface, yet we use them.*/
                foreach($data as $key => $value){
                    $fs="bounce_rule_";
                    if(strpos($key,$fs)!== false){
                        if(strpos($key,"_forwardto")===false){
                            $indexrule=str_replace($fs, "", $key);
                            $helpRules=&WYSIJA::get("rules","helper");
                            $rules=$helpRules->getRules();
                            foreach($rules as $keyy => $vals){
                                if(isset($vals['behave'])){
                                    $ruleMain=$helpRules->getRules($vals['behave']);
                                    
                                    $data[$fs.$vals['key']]=$value;
                                }
                            }
                        }
                        
                    }

                }
                
            }
            foreach($data as $key => $value){
                /*verify that the confirm email body contains an activation link if it doesn't add i at the end of the email*/
                if($key=="confirm_email_body" && strpos($value, "[activation_link]")=== false){
                    /*the activation link was not found*/
                    $value.="\n".'[activation_link]Click here to confirm your subscription.[/activation_link]';
                }
                if(is_string($value)) $value=$value;
                /* we save it only if it is different than the default no need to overload the db*/
                if(!isset($this->defaults[$key]) || (isset($this->defaults[$key]) && $value!=$this->defaults[$key])){
                    
                    $this->values[$key]=  $value;
                }else{
                    unset($this->values[$key]);
                }
   
            }
            

            if(!isset($data["emails_notified_when_unsub"])){
                $this->values["emails_notified_when_unsub"]=false;
            }
            
            /* save the confirmation email in the email table */
            if(isset($data["confirm_email_title"]) && isset($data["confirm_email_body"])){
                $mailModel=&WYSIJA::get("email","model");
                $mailModel->update(array("from_name"=>$data["from_name"],"from_email"=>$data["from_email"],
                    "replyto_name"=>$data["replyto_name"],"replyto_email"=>$data["replyto_email"],
                    "subject"=>$data["confirm_email_title"],"body"=>$data["confirm_email_body"]),array("email_id"=>$this->values["confirm_email_id"]));
            }
            unset($this->values["confirm_email_title"]);
            unset($this->values["confirm_email_body"]);
        }
        

        update_option($this->name_option,base64_encode(serialize($this->values)));
        if($savedThroughInterface)  $this->notice(__("Your Wysija settings have been updated!",WYSIJA));
    }
    
    
    /**
     *
     * @param type $key
     * @param type $type
     * @return type 
     */
    function getValue($key,$default=false,$type="normal") {
        if(isset($this->values[$key])) {
            /*if($type=="trans")  return stripslashes($this->values[$key]);
            else return $this->values[$key]; */
            return $this->values[$key];
        }else{
            /* special case for the confirmation email */
            if(in_array($key, array("confirm_email_title","confirm_email_body"))){
                $mailModel=&WYSIJA::get("email","model");
                $mailObj=$mailModel->getOne($this->getValue("confirm_email_id"));
                if($mailObj){
                   $this->values["confirm_email_title"]=$mailObj["subject"];
                   $this->values["confirm_email_body"]=$mailObj["body"]; 
                   return $this->values[$key];
                }else{
                    if($default===false && isset($this->defaults[$key])) return $this->defaults[$key];
                    elseif(!($default===false)){
                        return $default;
                    } 
                }
                
            }else{
               if($default===false && isset($this->defaults[$key])) return $this->defaults[$key];
                elseif(!($default===false)){
                    return $default;
                } 
            }
            
        }
        return false;
    }
    
}