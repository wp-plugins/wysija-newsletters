<?php
defined('WYSIJA') or die('Restricted access');


/**
 * class managing the admin vital part to integrate wordpress
 */
class WYSIJA_help_back extends WYSIJA_help{
    
    function WYSIJA_help_back(){
        parent::WYSIJA_help();
        /*check that the application has been installed properly*/
        $config=&WYSIJA::get("config","model");
        
        $linkcontent=__("It doesn't always work the way we want it to, doesn't it? We have a [link]dedicated support website[/link] with documentation and a ticketing system.",WYSIJA);
        $finds=array("[link]",'[/link]');
        $replace=array('<a target="_blank" href="http://support.wysija.com" title="support.wysija.com">','</a>');
        $truelinkhelp=str_replace($finds,$replace,$linkcontent);
        $truelinkhelp.="<p>".__("Wysija Version: ",WYSIJA)."<strong>".$config->getValue("version")."</strong></p>";
        $this->menus=array(

            "campaigns"=>array("title"=>__("Wysija",WYSIJA),"help"=>$truelinkhelp),
            "subscribers"=>array("title"=>__("Subscribers",WYSIJA),"help"=>$truelinkhelp),
            "config"=>array("title"=>__("Settings",WYSIJA),"help"=>$truelinkhelp),
            //"support"=>array("title"=>__("Support",WYSIJA))
        );
        
        
        if($config->getValue("debug_on")) include_once(WYSIJA_INC."debug.php");
        
        /* the controller is backend is it from our pages or from wordpress?
         * are we pluging-in to wordpress interfaces or doing entirely our own page?*/
        if(isset($_GET['page']) && substr($_GET['page'],0,7)=="wysija_"){

            define("WYSIJA_ITF",TRUE);
            if(WYSIJA_DBG===true){
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }
            $this->controller=&WYSIJA::get(str_replace("wysija_","",$_GET['page']),"controller");
            
        }else{/*check if we are pluging in wordpress interface*/
            define("WYSIJA_ITF",FALSE);
            if(defined('WYSIJA_DBG_ALL')){
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }
        }

        /*we set up the important hooks for backend: menus js css etc*/
        if(defined('DOING_AJAX')){
            //difference between frontend and backend
            if(!isset($_REQUEST['adminurl']) && !isset($_REQUEST['wysilog']))    add_action('wp_ajax_nopriv_wysija_ajax', array($this, 'ajax'));
            else    add_action('wp_ajax_wysija_ajax', array($this, 'ajax'));
            
        }else{
            if(WYSIJA_ITF)  {
                add_action('admin_init', array($this->controller, 'main'));
                add_action('admin_footer',array($this,'version'),9);
                
                if(get_option('wysija_write_uploads')=="not"){
                    /*try again to write*/
                    $helperF=&WYSIJA::get('file',"helper");
                    $writable=$helperF->makeDir();
                    if($writable){
                        update_option('wysija_write_uploads',true);
                    }else{
                        $this->error(sprintf(__('The folder "%1$s" is not writable, please change the access rights to this folder so that Wysija can setup itself properly.',WYSIJA),"/wp-content/uploads")."<a target='_blank' href='http://codex.wordpress.org/Changing_File_Permissions'>".__('Read documentation',WYSIJA)."</a>");
                    }

                }
                if( !$config->getValue("sending_emails_ok")){
                    $msg=$config->getValue("ignore_msgs");
                    
                    $urlsendingmethod='admin.php?page=wysija_config#sendingmethod';
                    if($_REQUEST['page']=='wysija_config'){
                        $urlsendingmethod="#sendingmethod";
                    }
                    if(!isset($msg['setupmsg'])){
                        $this->notice(str_replace(array("[link_widgget]","[link_ignore]","[link]","[/link]","[/link_widgget]","[/link_ignore]"),
                            array('<a href="widgets.php">','<a class="linkignore setupmsg" href="javascript:;">','<a id="linksendingmethod" href="'.$urlsendingmethod.'">','</a>','</a>','</a>'),
                            __('Hurray! Add a form to your site using [link_widgget]the Widget[/link_widgget] and confirm your site can send emails in the [link]Settings[/link]. [link_ignore]Ignore[/link_ignore].',WYSIJA)),true);
                    }
                    

                }
            }
            add_action('admin_menu', array($this, 'add_menus'));
            add_action('admin_enqueue_scripts',array($this, 'add_js'),10,1);
            add_action('user_register', array($this, 'add_WP_subscriber'), 1);
            add_action('profile_update', array($this, 'edit_WP_subscriber'), 1);
            add_action('delete_user', array($this, 'del_WP_subscriber'), 1);
             
            if(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) $this->wp_notice(__('The CRON system is disabled on your wordpress site. This might affect the configuration of Wysija.',WYSIJA)); 
        
            /* if during install this value has been set then that means we can import from a plugin */
            $importPossible=$config->getValue("pluginsImportable");
            if(!$config->getValue("pluginsImported") && $importPossible){
                foreach($importPossible as $tableName =>$pluginInfos){
                    if((isset($_REQUEST['action']) && $_REQUEST['action']!="importplugins") || !isset($_REQUEST['action'])) 
                        $this->notice(
                                str_replace(array("[link]","[/link]"),
                                        array('<a href="admin.php?page=wysija_subscribers&action=importplugins">','</a>'),
                                        sprintf(__('The plugin %1$s has been found. If you wish to import into Wysija the %2$s subscribers from that plugin click [link]here[/link].',WYSIJA),'<strong>"'.$pluginInfos['name'].'"</strong>',$pluginInfos['total'])));
                }
            }
            
            /*add specific page script*/
            add_action('admin_head-post-new.php',array($this,'addCodeToPagePost'));
            add_action('admin_head-post.php',array($this,'addCodeToPagePost'));
        }

        
    }
    

    
    function add_WP_subscriber($user_id) {
        $data=get_userdata($user_id);
        $modelUser=&WYSIJA::get("user","model");
        $modelUser->noCheck=true;
        $uid=$modelUser->insert(array("email"=>$data->user_email,"wpuser_id"=>$data->ID,"firstname"=>$data->first_name,"lastname"=>$data->last_name,"status"=>1));
        $modelConf=&WYSIJA::get("config","model");
        $modelUL=&WYSIJA::get("user_list","model");
        $modelUL->insert(array("user_id"=>$uid,"list_id"=>$modelConf->getValue("importwp_list_id")));
        return true;
    }
    
    function edit_WP_subscriber($user_id) {
        $data=get_userdata($user_id);

        $modelUser=&WYSIJA::get("user","model");
        
        return $modelUser->update(array("email"=>$data->user_email,"firstname"=>$data->first_name,"lastname"=>$data->last_name),array("wpuser_id"=>$data->ID));   
    }
    
    function del_WP_subscriber($user_id) {
        
        $modelConf=&WYSIJA::get("config","model");
        $modelUser=&WYSIJA::get("user","model");
        $data=$modelUser->getOne(array("user_id"),array("wpuser_id"=>$user_id));
        $modelUser=&WYSIJA::get("user_list","model");
        $modelUser->delete(array("user_id"=>$data['user_id'],"list_id"=>$modelConf->getValue("importwp_list_id")));

        $this->wp_notice(__("User has been removed from the <b>Synched</b> Wordpress user list.",WYSIJA));
    }
    
 
    function add_contextual_help(){
        foreach($this->menus as $action=> $menu){
            $actionFull='wysija_'.$action;
            add_contextual_help($actionFull, $menu['help'] );
        }
    }


    function add_menus(){
        $modelC=&WYSIJA::get("config","model");
        $count=0;
        foreach($this->menus as $action=> $menu){
            $actionFull='wysija_'.$action;
            if(!isset($menu['subtitle'])) $menu['subtitle']=$menu['title'];
            if($count==0){
                $parentmenu=$actionFull;
                
                $hookname=add_menu_page(__($menu['title'],WYSIJA), __($menu['subtitle'],WYSIJA), $modelC->getValue('role_campaign'), $actionFull , array($this->controller, 'render'), WYSIJA_EDITOR_IMG.'mail.png', 50);
            }else{
                if($action=='campaigns')    $rolecampaign=$modelC->getValue('role_campaign');
                else $rolecampaign='manage_options';
                
                $hookname=add_submenu_page($parentmenu,__($menu['title'],WYSIJA), __($menu['subtitle'],WYSIJA), $rolecampaign, $actionFull , array($this->controller, 'render'));
            }
            if(WYSIJA_ITF) add_contextual_help($hookname, $menu['help'] );
            $count++;
            
        }
        
    }

    
    
    function add_js($hook) {
        /*needed in all the wordpress admin pages including wysija's ones*/
        wp_enqueue_script('wysija-admin', WYSIJA_URL."/js/admin.js", array( 'jquery' ), true);
        $jstrans=array();
        wp_register_script('wysija-charts', "https://www.google.com/jsapi", array( 'jquery' ), true);
        wp_register_script('wysija-admin-list', WYSIJA_URL."/js/admin-listing.js", array( 'jquery' ), true);
        wp_register_script('wysija-base64', WYSIJA_URL."/js/base64.js", array( 'jquery' ), true);
        
        
        /* we are in wysija's admin interface */
        if(WYSIJA_ITF){          
            $pagename=str_replace("wysija_","",$_REQUEST['page']);
            $backloader=&WYSIJA::get("backloader","helper");
            $backloader->initLoad($this->controller);

            //$this->controller->jsTrans["ignoremsg"]=__('Are you sure you want to ignore this message?.',WYSIJA);
            $jstrans=$this->controller->jsTrans;
            //if(!in_array('wysija-admin-ajax-proto',$this->controller->js)) $this->controller->js[]='wysija-admin-ajax';
            
            $jstrans['gopremium']=__("Go Premium Now!");

            /* enqueue all the scripts that have been declared in the controller */
            if($this->controller->js){
                foreach($this->controller->js as $kjs=> $js){
                    switch($js){
                        case "jquery-ui-tabs":
                            wp_enqueue_script($js);
                            wp_enqueue_style('wysija-tabs-css', WYSIJA_URL."/css/smoothness/jquery-ui-1.8.15.custom.css");
                            break;
                        case "wysija-validator":
                            wp_enqueue_script('wysija-validator-lang');
                            wp_enqueue_script($js);
                            wp_enqueue_script('wysija-form');
                            wp_enqueue_style('validate-engine-css');
                            break;
                        case "wysija-admin-ajax":
                            wp_localize_script( 'wysija-admin-ajax', 'wysijaAJAX', array(
                                'action' => 'wysija_ajax',
                                'controller' => $pagename,
                                'dataType'=>"json",
                                'ajaxurl'=>admin_url( 'admin-ajax.php' ),
                                'adminurl'=>admin_url( 'admin.php' ),
                                'loadingTrans'  =>__('Loading...',WYSIJA)
                            ));
                            wp_enqueue_script("jquery-ui-dialog");
                            wp_enqueue_script($js);
                            wp_enqueue_style('wysija-tabs-css', WYSIJA_URL."/css/smoothness/jquery-ui-1.8.15.custom.css");
                            break;
                        case "wysija-admin-ajax-proto":
                            /*wp_localize_script( 'wysija-admin-ajax-proto', 'wysijaAJAX', array(
                            'action' => 'wysija_ajax',
                            'controller' => $pagename,
                            'ajaxurl'=>admin_url( 'admin-ajax.php' ),
                            'adminurl'=>admin_url( 'admin.php' ),
                              'loadingTrans'  =>__('Loading...',WYSIJA)
                            ));*/
                            wp_enqueue_script($js);
                            break;
                        case "wysija-editor":
                            wp_enqueue_script("wysija-prototype", WYSIJA_URL."/js/prototype/prototype.js");
                            wp_deregister_script('thickbox');
                            wp_register_script('thickbox',WYSIJA_URL."/js/thickbox/thickbox.js");
                            wp_localize_script('thickbox', 'thickboxL10n', array(
                                'next' => __('Next &gt;'),
                                'prev' => __('&lt; Prev'),
                                'image' => __('Image'),
                                'of' => __('of'),
                                'close' => __('Close'),
                                'noiframes' => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
                                'l10n_print_after' => 'try{convertEntities(thickboxL10n);}catch(e){};'
                            ));
                            
                            wp_enqueue_script("wysija-proto-scriptaculous", WYSIJA_URL."/js/prototype/scriptaculous.js?load=effects",array("wysija-prototype"));
                            wp_enqueue_script("wysija-proto-dragdrop", WYSIJA_URL."/js/prototype/dragdrop.js",array("wysija-proto-scriptaculous"));
                            wp_enqueue_script("wysija-proto-controls", WYSIJA_URL."/js/prototype/controls.js",array("wysija-proto-scriptaculous")); 
                            wp_enqueue_script("wysija-timer", WYSIJA_URL."/js/timer.js");
                            wp_enqueue_script($js, WYSIJA_URL."/js/".$js.".js");
                            wp_enqueue_script('wysija-tinymce', WYSIJA_URL."/js/tinymce/tiny_mce.js");
                            wp_enqueue_script('wysija-tinymce-init', WYSIJA_URL."/js/tinymce_init.js");
                            wp_enqueue_style('wysija-editor-css', WYSIJA_URL."/css/wysija-editor.css");
                            wp_enqueue_script('wysija-colorpicker', WYSIJA_URL."/js/jscolor/jscolor.js");
                            
                            /* Wysija editor i18n */
                            wp_localize_script('wysija-editor', 'Wysija_i18n', $this->controller->jsTrans);
                            break;
                        default:
                            if(is_string($kjs)) wp_enqueue_script($js,WYSIJA_URL."/js/".$js.".js");
                            else wp_enqueue_script($js);
                            
                    }

                }
            }
            
            
            $backloader->loadScriptsStyles($pagename,WYSIJA_DIR,WYSIJA_URL,$this->controller);
            
        }
            $jstrans["newsletters"]=__('Newsletters',WYSIJA);
            wp_localize_script('wysija-admin', 'wysijatrans', $jstrans);
    }
    
    /**
     * code only executed in the page or post in admin
     */
    function addCodeToPagePost(){

        /* code to add external buttons to the tmce*/
        if ( get_user_option('rich_editing') == 'true') {
         add_filter("mce_external_plugins", array($this,"addRichPlugin"));
         add_filter('mce_buttons', array($this,'addRichButton1'),999);
         add_filter('tiny_mce_before_init', array($this,'TMCEinnercss'),12 );
         wp_enqueue_style('custom_TMCE_admin_css', WYSIJA_URL.'/css/tmce/panelbtns.css');
         wp_print_styles('custom_TMCE_admin_css');
         
       }
    }
    
    function addRichPlugin($plugin_array) {
       $plugin_array['wysija_register'] = WYSIJA_URL.'/mce/wysija_register/editor_plugin.js';
       //$plugin_array['wysija_links'] = WYSIJA_URL.'/mce/wysija_links/editor_plugin.js';

       return $plugin_array;
    }

    function addRichButton1($buttons) {
       $newButtons=array();
       foreach($buttons as $value) $newButtons[]=$value;
       //array_push($newButtons, "|", "styleselect");
       array_push($newButtons, "|", "wysija_register");
       //array_push($newButtons, "|", "wysija_links");
       return $newButtons;
    }
    
    function TMCEinnercss($init) {
      $myStyleUrl = WYSIJA_URL."/css/tmce/style.css";
      $init['content_css'] = $myStyleUrl;
      return $init;
    }
    
    function version(){
        $modelC=&WYSIJA::get("config","model");
        echo "<div class='wysija-version'>";
        echo __("Wysija Version: ",WYSIJA)."<strong>".$modelC->getValue("version")."</strong>";
        echo "</div>";
    }

}


