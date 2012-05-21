<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_control_back_config extends WYSIJA_control_back{
    var $view="config";
    var $model="config";
    
    function WYSIJA_control_back_config(){

    }
    
    
    function main(){
        parent::WYSIJA_control_back();
        $this->js[]='jquery-ui-tabs';
        $this->js[]='wysija-admin-ajax';
        $this->js[]='thickbox';
        wp_enqueue_style( 'thickbox' );
        
        if(!isset($_REQUEST['action'])) $this->action='main';
        else $this->action=$_REQUEST['action'];
        
        switch($this->action){
            case "save":
                $this->save();
                break;
            case "reinstall":
                $this->reinstall();
                if(defined('WYSIJA_REDIRECT'))  $this->redirectProcess();
                return;
                break;
            case "doreinstall":
                $this->doreinstall();
                if(defined('WYSIJA_REDIRECT')){
                     global $wysi_location;
                     $wysi_location="admin.php?page=wysija_campaigns";
                    $this->redirectProcess();
                }
                return;
                break;
        }

        $this->data=array();
        $this->action="main";
        $this->jsTrans["testemail"]=__("Sending a test email",WYSIJA);
        $this->jsTrans["bounceconnect"]=__("Bounce handling connection test",WYSIJA);
        $this->jsTrans["processbounceT"]=__("Bounce handling processing",WYSIJA);
        $this->jsTrans["doubleoptinon"]=__("Subscribers will now need to activate their subscription by email in order to receive your newsletters. This is recommended.",WYSIJA);
        $this->jsTrans["doubleoptinoff"]=__("Unconfirmed subscribers will receive your newslettters from now on without the need to activate their subscriptions.",WYSIJA);
        $this->jsTrans["processbounce"]=__("Process bounce handling now!",WYSIJA);
        $this->jsTrans["errorbounceforward"]=__("When setting up the bounce system, you need to have a different address for the bounce email and the forward to address",WYSIJA);
        
        if(isset($_REQUEST['validate'])){
            $this->notice(str_replace(array('[link]','[/link]'),
            array('<a title="'.__('Get Premium now',WYSIJA).'" class="premium-tab" href="javascript:;">','</a>'),
            __('You\'re almost there. Click this [link]link[/link] to activate the licence you have just purchased.',WYSIJA)));
 
        }
        
    }
    
    function save(){
        $_REQUEST   = stripslashes_deep($_REQUEST);
        $_POST   = stripslashes_deep($_POST);
        $this->requireSecurity();
        $this->modelObj->save($_REQUEST['wysija']['config'],true);
        wp_redirect('admin.php?page=wysija_config'.$_REQUEST['redirecttab']);

    }
    
    function reinstall(){
        $this->viewObj->title=__("Reinstall Wysija?",WYSIJA);
        return true;
    }
    
    function doreinstall(){

        if(isset($_REQUEST['postedfrom']) && $_REQUEST['postedfrom']=="reinstall"){
            $uninstaller=&WYSIJA::get("uninstall","helper");
            $uninstaller->reinstall();
            
        }
        $this->redirect("admin.php?page=wysija_config");
        return true;
    }
    
    function render(){
        $this->_checkTotalSubscribers();
        $this->viewObj->render($this->action,$this->data);
    }
}