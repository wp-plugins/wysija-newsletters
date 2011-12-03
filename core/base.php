<?php
require_once("constants.php");
defined('WYSIJA') or die('Restricted access');
global $wysija_msg;
global $wysija_wpmsg;
if(!$wysija_msg) $wysija_msg=array();
$wysija_wpmsg=array();
class WYSIJA_object{
    
    function WYSIJA_object(){

    }
    
    function wp_notice($msg){
        global $wysija_wpmsg;
        
        /* add the hook only once */
        if(!$wysija_wpmsg) add_action('admin_notices', array($this,'wp_msgs'));
        
        /* record msgs */
        $wysija_wpmsg[]=$msg;
    }
    
    function wp_msgs() {
        global $wysija_wpmsg;
        $msgs= "<div class='updated fade'>";
        foreach($wysija_wpmsg as $mymsg)
            $msgs.= "<p><strong>Wysija</strong> : ".$mymsg."</p>";
        $msgs.= "</div>";
        echo $msgs;
    }
    
    function error($msg,$public=false){
        $this->setInfo("error",$msg,$public);
    }

    function notice($msg,$public=true){
        $this->setInfo("updated",$msg,$public);
    }

    function setInfo($status,$msg,$public=false){
        global $wysija_msg;
        if(!$public) {
 
            if(!isset($wysija_msg['private'][$status])){
                $wysija_msg['private']=array();
                $wysija_msg['private'][$status]=array();
            }
            array_push($wysija_msg['private'][$status], $msg);
        }else{
            if(!isset($wysija_msg[$status]))  $wysija_msg[$status]=array();
            array_push($wysija_msg[$status], $msg);
        }

    }

    function getMsgs(){
        global $wysija_msg;

        if(isset($wysija_msg["private"]["error"])){
            $wysija_msg["error"][]=str_replace(array("[link]","[/link]"),array('<a class="showerrors" href="javascript:;">',"</a>"),__("An error occured. [link]Show more details.[/link]",WYSIJA));
        }

        if(isset($wysija_msg["private"]["updated"])){
            $wysija_msg["updated"][]=str_replace(array("[link]","[/link]"),array('<a class="shownotices" href="javascript:;">',"</a>"),__("[link]Show more details.[/link]",WYSIJA));
        }
        if(isset($wysija_msg["private"])){
            $prv=$wysija_msg["private"];
            unset($wysija_msg["private"]);
            if(isset($prv['error']))    $wysija_msg["xdetailed-errors"]=$prv['error'];
            if(isset($prv['updated']))    $wysija_msg["xdetailed-updated"]=$prv['updated'];
        }        
        return $wysija_msg;
    }
}


class WYSIJA_help extends WYSIJA_object{
    var $controller=null;
    function WYSIJA_help(){
        if(defined('WPLANG') && WPLANG!=''){
            $locale=explode("_",WPLANG);
            $wplang=$locale[0];
        }else{
            $wplang='en';
        }
        
        if(!defined('DOING_AJAX')){
            if(file_exists(WYSIJA_DIR."js".DS."validate".DS."languages".DS."jquery.validationEngine-".$wplang.".js")){
                wp_register_script('wysija-validator-lang',WYSIJA_URL."/js/validate/languages/jquery.validationEngine-".$wplang.".js", array( 'jquery' ), true);
            }else{
                wp_register_script('wysija-validator-lang',WYSIJA_URL."/js/validate/languages/jquery.validationEngine-en.js", array( 'jquery' ), true);
            }

            wp_register_script('wysija-validator',WYSIJA_URL."/js/validate/jquery.validationEngine.js", array( 'jquery' ), true );
            wp_register_script('wysija-form', WYSIJA_URL."/js/forms.js", array( 'jquery' ), true);
            wp_register_style('validate-engine-css',WYSIJA_URL."/css/validationEngine.jquery.css");

            wp_register_script('wysija-admin-ajax', WYSIJA_URL."/js/admin-ajax.js");
            wp_register_script('wysija-admin-ajax-proto', WYSIJA_URL."/js/admin-ajax-proto.js");
            wp_register_script('wysija-front-subscribers', WYSIJA_URL."/js/front-subscribers.js", array( 'jquery' ), true);
            
        }
        
        add_action('widgets_init', array($this, 'widgets_init'), 1);

    }
    
    function widgets_init() {
        register_widget('WYSIJA_NL_Widget');
    }
    
    
    
    /**
     * when doing an ajax request in admin this is the first place where we come
     */
    function ajax() {
       
        $resultArray=array();
        if(!$_REQUEST || !isset($_REQUEST['controller']) || !isset($_REQUEST['task'])){
            $resultArray=array("result"=>false);
        }else{
            $this->controller=&WYSIJA::get($_REQUEST['controller'],"controller");
            if(method_exists($this->controller, $_REQUEST['task'])){
                $resultArray["result"]=$this->controller->$_REQUEST['task']();
            }else{
                $this->error("Method doesn't exists for controller:'".$_REQUEST['controller']."'.");
            } 
            /*if(!check_ajax_referer('wysija_ajax','_wpnonce')){
                die("security error");
            }else{
                
            }*/
            
            
        }
        //dbg($resultArray);
        //if(isset($resultArray['']))
        $resultArray["msgs"]=$this->getMsgs();
        header('Content-type: application/json');
        echo json_encode($resultArray);
        die();
    }

}


class WYSIJA extends WYSIJA_object{

    function WYSIJA(){

    }
    function get_permalink($pageid,$params=array()){
        /*if(get_bloginfo("version")=="3.0"){
            $url=get_permalink($pageid);
        }else{
            $url=get_permalink($pageid);
            $url=site_url();
            if(array_pop(str_split($url))!="/") $url.="/";
            $url = $url."?p=".$pageid;
            $params['wysijap']=basename($url);
        }*/
        
        $url=get_permalink($pageid);

        $params['wysijap']=str_replace("?wysijap=","",basename($url));

        if($params){
            if (strpos($url, '?') !== false) $charStart='&';
            else $charStart='?';
            $url.=$charStart;
            $paramsinline=array();
            foreach($params as $k => $v){
                $paramsinline[]=$k."=".$v;
            }
            $url.=implode('&',$paramsinline);
        }

        return $url;
        
    }

    /**
     * function to generate objects of different types, managing file requiring in order to be the most efficient
     * @staticvar array $arrayOfObjects
     * @param type $name
     * @param type $type
     * @return type 
     */
    function get($name,$type,$forceside=false,$extendedplugin="wysija"){
        static $arrayOfObjects;

        /*store all the objects made so that we can reuse them accross the application*/
        if(isset($arrayOfObjects[$extendedplugin][$type.$name])) {
            return $arrayOfObjects[$extendedplugin][$type.$name];
        }
        if($forceside)  $side=$forceside;
        else    $side=WYSIJA_SIDE;
        
        $extendeconstant=strtoupper($extendedplugin);
        if(!defined($extendeconstant)) define($extendeconstant,$extendeconstant);
        //security to protect against ./../ includes
        $name = preg_replace('#[^a-z0-9_]#i','',$name);
        switch($type){
            case "controller":
                $ctrdir=WYSIJA_PLG_DIR.$extendedplugin.DS."controllers".DS;
                /*require the parent class necessary*/
                require_once(WYSIJA_CORE."controller.php");/*require the common controller file*/
                if(defined('DOING_AJAX')) {
                    $classpath=$ctrdir."ajax".DS.$name.".php";
                }else {
                    $classpath=$ctrdir.$side.DS.$name.".php";
                    require_once(WYSIJA_CTRL.$side.".php");/*require the side specific controller file*/
                }
                $classname = strtoupper($extendedplugin).'_control_'.$side.'_'.$name;
                break;
            case "view":
                $viewdir=WYSIJA_PLG_DIR.$extendedplugin.DS."views".DS;
                $classpath=$viewdir.$side.DS.$name.".php";
                $classname = strtoupper($extendedplugin).'_view_'.$side.'_'.$name;
                require_once(WYSIJA_CORE."view.php");/*require the common view file*/
                require_once(WYSIJA_VIEWS.$side.".php");/*require the side specific view file*/
                break;
            case "helper":
                $helpdir=WYSIJA_PLG_DIR.$extendedplugin.DS."helpers".DS;

                $classpath=$helpdir.$name.".php";
                $classname = strtoupper($extendedplugin).'_help_'.$name;

                break;
            case "model":
                $modeldir=WYSIJA_PLG_DIR.$extendedplugin.DS."models".DS;
                $classpath=$modeldir.$name.".php";
                $classname = strtoupper($extendedplugin).'_model_'.$name;
                /*require the parent class necessary*/
                require_once(WYSIJA_CORE."model.php");
                break;
            default:
                WYSIJA::setInfo("error",'WYSIJA::get does not accept this type of file "'.$type.'" .');
                return false;
        }

        if(!file_exists($classpath)) {
            WYSIJA::setInfo("error",'file has not been recognised '.$name);  
            return;
        }

        require_once($classpath);
        return $arrayOfObjects[$extendedplugin][$type.$name]=new $classname($extendedplugin);
        
    }
    
    /**
     * the filter to add option to the cron frequency instead of being stuck with hourly, daily and twicedaily...
     * @param type $param
     * @return type 
     */
    function filter_cron_schedules( $param ) {
        return array( 
            'fifteen_min' => array(
                'interval' => 900, 
                'display' => __( 'Once every fifteen minutes',WYSIJA)
                ),
            'thirty_min' => array(
                'interval' => 1800,
                'display' => __( 'Once every thirty minutes',WYSIJA)
                ),
            'two_hours' => array(
                'interval' => 7200,
                'display' => __( 'Once every two hours',WYSIJA)
                ) 
            );
    }  
    
    /**
     * cron where the frequency is decided by the administrator
     */
    function croned_queue() {
        $config=&WYSIJA::get("config","model");
        $premium=$config->getValue('premium_key');
        $subscribers=(int)$config->getValue('total_subscribers');
        
        if($subscribers<2000 || ($premium && $subscribers>=2000) ){
            $modelQ=&WYSIJA::get("queue","model");
            $modelQ->launch();
        } 
    }
    
    /**
     * cron where the frequency is decided by the administrator
     */
    function croned_bounce() {
        /*bounce handling*/
        $config = &WYSIJA::get('config','model');
        if(!$config->getValue("bounce_process_auto")) return false;
        
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
        $this->notice(sprintf(__('Successfully connected to %1$s'),$config->getValue('bounce_login')));
        $nbMessages = $bounceClass->getNBMessages();
        

        if(empty($nbMessages)){
            $this->error(__('There are no messages'),true);
            $res['result']=false;
            return $res;
        }else{
            $this->notice(sprintf(__('There are %1$s messages in your mailbox'),$nbMessages));
        }
        

        $bounceClass->handleMessages();
        $bounceClass->close();
    }
    
    
    /**
     * remove temporary files
     */
    function croned_daily() {
        @ini_set('max_execution_time',0);
        /*user refresh count total*/
        $helperU=&WYSIJA::get("user","helper");
        $helperU->refreshUsers();
        
        /*clear temporary folders*/
        $helperF=&WYSIJA::get("file","helper");
        $helperF->clear();
        
        /* send daily report about emails sent */
        $modelC=&WYSIJA::get("config","model");
        if($modelC->getValue("emails_notified_when_dailysummary")){
            $helperS=&WYSIJA::get("stats","helper");
            $helperS->sendDailyReport();
        }
        
        /* if premium let's do a licence check */
        if($modelC->getValue("emails_notified_when_dailysummary")){
            $helperS=&WYSIJA::get("stats","helper");
            $helperS->sendDailyReport();
        }
        
    }

    function deactivate() {
        wp_clear_scheduled_hook('wysija_cron_queue');
        wp_clear_scheduled_hook('wysija_cron_bounce');
        wp_clear_scheduled_hook('wysija_cron_daily');
    }
    
    function activate() {
        //WYSIJA::redirect('admin.php?page=wysija_config');
    }
    function return_bytes($size_str)
    {
        switch (substr ($size_str, -1))
        {
            case 'M': case 'm': return (int)$size_str * 1048576;
            case 'K': case 'k': return (int)$size_str * 1024;
            case 'G': case 'g': return (int)$size_str * 1073741824;
            default: return $size_str;
        }
    }
    function get_max_file_upload(){
        $u_bytes = ini_get( 'upload_max_filesize' );
        $p_bytes = ini_get( 'post_max_size' );
        $data=array();
        
        $data['maxbytes']=WYSIJA::return_bytes(min($u_bytes, $p_bytes));
        $data['maxmegas'] = apply_filters( 'upload_size_limit', min($u_bytes, $p_bytes), $u_bytes, $p_bytes );
        $data['maxchars'] =(int)floor(($p_bytes*1024*1024)/200);
        return $data;
    }
    
    function redirect($redirectTo){
         /* save the messages */
        global $wysija_msg,$wysija_queries;
        WYSIJA::update_option("wysija_msg",$wysija_msg);
        WYSIJA::update_option("wysija_queries",$wysija_queries);
        wp_redirect($redirectTo);
        exit;
    }
    
    function _make_domain_name($url){
        $domain_name=str_replace(array("http://","www."),"",$url);
        $domain_name=explode('/',$domain_name);
        return $domain_name[0];
    }
    
    function duration($s,$durationin=false,$level=1){
        $t=mktime();
        
        if($durationin){
            $e=$t+$s;
            $s=$t;
            /* Find out the seconds between each dates */
            $timestamp = $e - $s;

        }else{
            $timestamp = $t - $s;
        }
        

        /* Cleaver Maths! */
        $years=floor($timestamp/(60*60*24*365));$timestamp%=60*60*24*365;
        $weeks=floor($timestamp/(60*60*24*7));$timestamp%=60*60*24*7;
        $days=floor($timestamp/(60*60*24));$timestamp%=60*60*24;
        $hrs=floor($timestamp/(60*60));$timestamp%=60*60;
        $mins=floor($timestamp/60);$secs=$timestamp%60;

        /* Display for date, can be modified more to take the S off */
        $str="";
        $mylevel=0;
        if ($mylevel<$level && $years >= 1) { $str.= sprintf(_n( '%1$s year ', '%1$s years ', $years, WYSIJA ),$years);$mylevel++; }
        if ($mylevel<$level && $weeks >= 1) { $str.= sprintf(_n( '%1$s week ', '%1$s weeks ', $weeks, WYSIJA ),$weeks);$mylevel++; }
        if ($mylevel<$level && $days >= 1) { $str.=sprintf(_n( '%1$s day ', '%1$s days ', $days, WYSIJA ),$days);$mylevel++; }
        if ($mylevel<$level && $hrs >= 1) { $str.=sprintf(_n( '%1$s hour ', '%1$s hours ', $hrs, WYSIJA ),$hrs);$mylevel++; }
        if ($mylevel<$level && $mins >= 1) { $str.=sprintf(_n( '%1$s minute ', '%1$s minutes ', $mins, WYSIJA ),$mins);$mylevel++; }

        return $str;

    }
    
    
    function create_post_type() {
        register_post_type( 'wysijap',
            array(
                    'labels' => array(
                            'name' => __( 'Wysija page' ),
                            'singular_name' => __( 'Wysija page' )
                    ),
            'public' => true,
            'has_archive' => false,
            'show_ui' =>false,
            'show_in_menu' =>false,
            'rewrite' => array("slug"=>"wysijap"),
            'show_in_nav_menus'=>false,
            'can_export'=>false,
            'publicly_queryable'=>true,
            'exclude_from_search'=>true,
            )
        );

            if(!get_option("wysija_post_type_updated")) {
                $modelPosts=new WYSIJA_model();
                $modelPosts->tableWP=true;
                $modelPosts->table_prefix="";
                $modelPosts->table_name="posts";
                $modelPosts->noCheck=true;
                $modelPosts->pk="ID";
                if($modelPosts->exists(array("post_type"=>"wysijapage"))){
                    $modelPosts->update(array("post_type"=>"wysijap"),array("post_type"=>"wysijapage"));
                    flush_rewrite_rules( false );
                }
                WYSIJA::update_option("wysija_post_type_updated",mktime());
            }
        

        if(!get_option("wysija_post_type_created")) {
            flush_rewrite_rules( false );
            WYSIJA::update_option("wysija_post_type_created",mktime());
        }
    }
    
    
    function update_option($option_name,$newvalue){
        if ( get_option( $option_name ) != $newvalue ) {
            update_option( $option_name, $newvalue );
        } else {
            add_option( $option_name, $newvalue, '', 'no' );
        }
    }
}

/**
 * widget class for user registration
 */
class WYSIJA_NL_Widget extends WP_Widget {
    public $classid="";


    function WYSIJA_NL_Widget($coreOnly=false) {

        if(WYSIJA_SIDE=="front"){
            if(!isset($_REQUEST['controller'])){
                $controller="subscribers";
            }else $controller=$_REQUEST['controller'];
            
            $paramsajax=array(
                'action' => 'wysija_ajax',
                'controller' => $controller,
                'ajaxurl'=>admin_url( 'admin-ajax.php' ),
                'loadingTrans'  =>'Loading...'
            );

            if(is_user_logged_in()) $paramsajax['wysilog']=1;
            wp_localize_script( 'wysija-front-subscribers', 'wysijaAJAX',$paramsajax );
        }
        
        if($coreOnly) $this->coreOnly=true;
        $namekey='wysija';
        $title=__("Wysija Subscription",WYSIJA);
        $params=array( 'description' => __('Subscription form for your newsletters.'));
        $sizeWindow=array('width' => 400);
        
        $config=&WYSIJA::get("config","model");
        $this->successmsgconf=__('Check your inbox now to confirm your subscription.',WYSIJA);
        $this->successmsgsub=__('You’ve successfully subscribed.',WYSIJA);
        if($config->getValue("confirm_dbleoptin")){
            $successmsg=$this->successmsgsub." ".$this->successmsgconf;
        }else{
            $successmsg=$this->successmsgsub;
        }
        
        
        $this->fields=array(
            "title" =>array("label"=>__("Title:",WYSIJA),'default'=>__('Newsletter subscription',WYSIJA))
            ,"instruction" =>array("label"=>"",'default'=>__('To subscribe to our dandy newsletter simply add your email below. A confirmation email will be sent to you!',WYSIJA))
            ,"lists" =>array("core"=>1,"label"=>__("Subscribe to...",WYSIJA),'default'=>array(1))
            ,"submit" =>array("core"=>1,"label"=>__("Button label:",WYSIJA),'default'=>__('Subscribe!',WYSIJA))
            ,"success"=>array("core"=>1,"label"=>__("Success message:",WYSIJA),'default'=>$successmsg));

        $this->classid=strtolower(str_replace(__CLASS__."_","",get_class($this)));
        //parent::__construct( $namekey, $title, $params,$sizeWindow );
        $this->WP_Widget( $namekey, $title, $params,$sizeWindow );

    }


    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        foreach($new_instance as $key => $value) $instance[$key]=$value;

        return $instance;
    }

    function form( $instance ) {
        $formObj=&WYSIJA::get("forms","helper");

        $html='';

        foreach($this->fields as $field => $fieldParams){
            $valuefield="";

            if(isset($this->coreOnly) && !isset($fieldParams['core'])) continue;
            if(isset($instance[$field]))  {
                
                if($field=="success" && $instance[$field]==$this->successmsgsub." ".$this->successmsgconf){
                    $config=&WYSIJA::get("config","model");
                    if(!$config->getValue("confirm_dbleoptin")){
                        $valuefield=$this->successmsgsub;
                    }else{
                        $valuefield=$instance[$field];
                    }
                }else   $valuefield=$instance[$field];
            }
            elseif(isset($fieldParams['default'])) $valuefield=$fieldParams['default'];
            
            $classDivLabel=$fieldHTML='';
            switch($field){
                case "lists":
                    $modelList=&WYSIJA::get("list","model");
                    $lists=$modelList->get(array('name','list_id'),array('is_enabled'=>1));
                    
                    $classDivLabel='style="float:left"';
                    $fieldHTML= '<div style="max-height:116px;overflow:auto;float:right;">';
                    
                    if(!$valuefield) {
                        $modelConfig=&WYSIJA::get("config","model");
                        $valuefield[]=$modelConfig->getValue("default_list_id");
                    }
                    
                    foreach($lists as $list){
                        if(in_array($list['list_id'], $valuefield)) $checked=true;
                        else $checked=false;
                        $fieldHTML.= '<p style="margin:0 0 5px 0;"><label for="'.$this->get_field_id($field.$list['list_id']).'">'.$formObj->checkbox( array('id'=>$this->get_field_id($field.$list['list_id']),'name'=>$this->get_field_name($field)."[]"),$list['list_id'],$checked).$list['name'].'</label></p>';
                    }
                    $fieldHTML .= '</div>';
                    
                    break;
                case "instruction":
                case "success":
                    $fieldHTML= $formObj->textarea( array('id'=>$this->get_field_id($field),'name'=>$this->get_field_name($field),'value'=>$valuefield,"cols"=>46,"rows"=>4,"style"=>'width:404px'),$valuefield);
                    break;
                default:
                    $fieldHTML= $formObj->input( array('id'=>$this->get_field_id($field),'name'=>$this->get_field_name($field)),$valuefield ,' size="40" ');
                    break;
            }
            
            $html.='<div style="margin:10px 0;"><div '.$classDivLabel.'><label for="'.$this->get_field_id($field).'">'.$fieldParams['label'].'</label></div>';
            $html.=$fieldHTML;
            $html.='<div style="clear:both;"></div></div>';

        }
        
        echo $html;

    }

    function widget($args, $instance) {
        extract($args);
        $config=&WYSIJA::get("config","model");
        //if(!$config->getValue("sending_emails_ok")) return;
        foreach($this->fields as $field => $fieldParams){
            if(isset($this->coreOnly) && !isset($fieldParams['core'])) continue;
            if($field=="success" && $instance[$field]==$this->successmsgsub." ".$this->successmsgconf){
                if(!$config->getValue("confirm_dbleoptin")){
                    $instance[$field]=$this->successmsgsub;
                }
            }
        }

        $instance['id_form']=str_replace('_','-',$args['widget_id']);

        if(!isset($this->coreOnly)) $title = apply_filters('widget_title',$instance['title'], $instance, $this->id_base);
        //dbg($before_title);
        /* some worpress weird thing for widgets management */
        if(!isset($before_widget)) $before_widget="";
        if(!isset($after_widget)) $after_widget="";
        if(!isset($before_title)) $before_title="";
        if(!isset($after_title)) $after_title="";
        
        $glob= $before_widget;
        if ( !isset($this->coreOnly) && $title ) $title=$before_title . $title . $after_title;
        else $title="";
        $view=&WYSIJA::get("widget_nl","view","front");
        $glob.=$view->display($title,$instance,false);

        $glob.= $after_widget;

        if($this->coreOnly) return $glob;
        else echo $glob;
    }
}

/* some processing for cron management */
add_filter( 'cron_schedules', array( "WYSIJA", 'filter_cron_schedules' ) );
add_action( 'wysija_cron_queue', array( "WYSIJA", 'croned_queue' ) );
add_action( 'wysija_cron_bounce', array( "WYSIJA", 'croned_bounce' ) ); 
add_action( 'wysija_cron_daily', array( "WYSIJA", 'croned_daily' ) ); 
if(!wp_next_scheduled('wysija_cron_daily')) wp_schedule_event( mktime() , 'daily', 'wysija_cron_daily' );

/*if(isset($_GET['dbg'])){
    if(wp_next_scheduled( 'wysija_cron')) {
        global $wysija_msg;
        echo mktime()." hello scheduled ".wp_next_scheduled( 'wysija_cron');
        echo "<pre>";
        print_r($wysija_msg);
        echo "</pre>";
    }
}*/

if(!wp_next_scheduled('wysija_cron_queue')){
    $modelConf=&WYSIJA::get("config","model");
    
    wp_schedule_event( $modelConf->getValue('last_save') , $modelConf->getValue('sending_emails_each'), 'wysija_cron_queue' );
}

if(!wp_next_scheduled('wysija_cron_bounce')){
    $modelConf=&WYSIJA::get("config","model");
    
    wp_schedule_event( $modelConf->getValue('last_save') , $modelConf->getValue('bouncing_emails_each'), 'wysija_cron_bounce' );
}


register_activation_hook( WYSIJA_FILE, array('WYSIJA', 'activate') );
register_deactivation_hook(WYSIJA_FILE, array( "WYSIJA", 'deactivate' ));
add_action( 'init', array('WYSIJA','create_post_type') );

register_uninstall_hook(__FILE__,'wysija_uninstall');
function wysija_uninstall(){
    /*$helperUS=&WYSIJA::get("uninstall","helper");
    $helperUS->uninstall();*/
}

$helper=&WYSIJA::get(WYSIJA_SIDE,"helper");