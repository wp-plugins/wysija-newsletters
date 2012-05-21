<?php
defined('WYSIJA') or die('Restricted access');


class WYSIJA_help_back extends WYSIJA_help{
    function WYSIJA_help_back(){
        parent::WYSIJA_help();
        
        $config=&WYSIJA::get("config","model");

        if($config->getValue("debug_on")) include_once(WYSIJA_INC."debug.php");

        
        if(isset($_GET['page']) && substr($_GET['page'],0,7)=="wysija_"){
            define("WYSIJA_ITF",TRUE);
            
            if(defined('WYSIJA_DBG') && WYSIJA_DBG===true){
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }else{
                error_reporting(0);
                ini_set('display_errors', '0');
            }
            $this->controller=&WYSIJA::get(str_replace("wysija_","",$_GET['page']),"controller");
        }else{
            define("WYSIJA_ITF",FALSE);
            if(defined('WYSIJA_DBG_ALL')){
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }
        }
        
        if(defined('DOING_AJAX')){

            if(!isset($_REQUEST['adminurl']) && !isset($_REQUEST['wysilog']))    add_action('wp_ajax_nopriv_wysija_ajax', array($this, 'ajax'));
            else    add_action('wp_ajax_wysija_ajax', array($this, 'ajax'));
        }else{
            if(WYSIJA_ITF)  {
                add_action('admin_init', array($this->controller, 'main'));
                add_action('admin_footer',array($this,'version'),9);
                add_action('after_setup_theme',array($this,'resolveConflicts'));
            }
            
            add_action('admin_menu', array($this, 'define_translated_strings'),98);
            add_action('admin_menu', array($this, 'add_menus'),99);
            add_action('admin_enqueue_scripts',array($this, 'add_js'),10,1);
            
            
            add_action('admin_head-post-new.php',array($this,'addCodeToPagePost'));
            add_action('admin_head-post.php',array($this,'addCodeToPagePost'));
        }
        
    }
    function resolveConflicts(){
        
            $modelConfig=&WYSIJA::get('config','model');

            $possibleConflictiveThemes = $modelConfig->getValue('conflictiveThemes');
            $conflictingTheme = null;
            $currentTheme = strtolower(get_current_theme());
            foreach($possibleConflictiveThemes as $keyTheme => $conflictTheme) {
                if($keyTheme === $currentTheme) {
                    $conflictingTheme = $keyTheme;
                }
            }

            if($conflictingTheme !== null) {
                $helperConflicts =& WYSIJA::get('conflicts', 'helper');
                $helperConflicts->resolve(array($possibleConflictiveThemes[$conflictingTheme]));
            }

            $possibleConflictivePlugins=$modelConfig->getValue("conflictivePlugins");
            $conflictingPlugins=array();
            foreach($possibleConflictivePlugins as $keyPlg => $conflictPlug){
                $arrayactiveplugins=get_option('active_plugins');
                if(in_array($conflictPlug['file'], $arrayactiveplugins)) {

                    $conflictingPlugins[$keyPlg]=$conflictPlug;
                }
            }
            if($conflictingPlugins){
                $helperConflicts=&WYSIJA::get("conflicts","helper");
                $helperConflicts->resolve($conflictingPlugins);
            }
    }
    function define_translated_strings(){
        $config=&WYSIJA::get("config","model");
        $linkcontent=__("It doesn't always work the way we want it to, doesn't it? We have a [link]dedicated support website[/link] with documentation and a ticketing system.",WYSIJA);
        $finds=array("[link]",'[/link]');
        $replace=array('<a target="_blank" href="http://support.wysija.com" title="support.wysija.com">','</a>');
        $truelinkhelp="<p>".str_replace($finds,$replace,$linkcontent)."</p>";
        
        $extra=__("[link]Request a feature for Wysija[/link] in User Voice.",WYSIJA);
        $finds=array("[link]",'[/link]');
        $replace=array('<a target="_blank" href="http://wysija.uservoice.com/forums/150107-feature-request" title="Wysija User Voice">','</a>');
        $truelinkhelp.="<p>".str_replace($finds,$replace,$extra)."</p>";
        
        $truelinkhelp.="<p>".__("Wysija Version: ",WYSIJA)."<strong>".WYSIJA::get_version()."</strong></p>";
        $this->menus=array(
            "campaigns"=>array("title"=>__("Wysija",WYSIJA)),
            "subscribers"=>array("title"=>__("Subscribers",WYSIJA)),
            "config"=>array("title"=>__("Settings",WYSIJA)),

        );
        $this->menuHelp=$truelinkhelp;
        if(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) $this->wp_notice(__('The CRON system is disabled on your wordpress site. Wysija will not work correctly while it stays disabled.',WYSIJA)); 
        
        $importPossible=$config->getValue("pluginsImportableEgg");

        if(!$config->getValue("pluginsImportedEgg") && $importPossible){
            foreach($importPossible as $tableName =>$pluginInfos){
                if((isset($_REQUEST['action']) && $_REQUEST['action']!="importplugins") || !isset($_REQUEST['action'])){
                    $msg=$config->getValue("ignore_msgs");
                    if(!isset($msg['importplugins-'.$tableName])&& (int)$pluginInfos['total']>0){
                        if(!isset($pluginInfos['total_lists']) || !$pluginInfos['total_lists'] || (int)$pluginInfos['total_lists']<1) $pluginInfos['total_lists']=1;
                        $sprintfedmsg=sprintf(__('Would you like to import the %1$s lists with a total of %2$s subscribers from the plugin %3$s. [link]Yes[/link]. [link_ignore]I\'ll import them later.[/link_ignore]',WYSIJA),$pluginInfos['total_lists'],$pluginInfos['total'],'<strong>"'.$pluginInfos['name'].'"</strong>');
                        $this->notice(
                            str_replace(array("[link_ignore]","[link]","[/link]","[/link_ignore]"),
                                    array('<a class="linkignore importplugins-'.$tableName.'" href="javascript:;">','<a href="admin.php?page=wysija_subscribers&action=importplugins">','</a>','</a>'),
                                    $sprintfedmsg
                                    ));
                    }
                }  
            }
        }
        if(WYSIJA_ITF){
            global $wysija_installing; 
            if( !$config->getValue("sending_emails_ok")){
                $msg=$config->getValue("ignore_msgs");
                $urlsendingmethod='admin.php?page=wysija_config#sendingmethod';
                if($_REQUEST['page']=='wysija_config'){
                    $urlsendingmethod="#sendingmethod";
                }
                if(!isset($msg['setupmsg']) && $wysija_installing!==true){
                    $this->notice(str_replace(array("[link_widget]","[link_ignore]","[link]","[/link]","[/link_widget]","[/link_ignore]"),
                        array('<a href="widgets.php">','<a class="linkignore setupmsg" href="javascript:;">','<a id="linksendingmethod" href="'.$urlsendingmethod.'">','</a>','</a>','</a>'),
                        __('Hurray! Add a form to your site using [link_widget]the Widget[/link_widget] and confirm your site can send emails in the [link]Settings[/link]. [link_ignore]Ignore[/link_ignore].',WYSIJA)),true,true);
                }
                
            }
            
        }
    }

    function add_menus(){
        $modelC=&WYSIJA::get("config","model");
        $count=0;
        global $menu,$submenu;
        
        $position=50;
        while(isset($menu[$position])){
            $position++;
        }
        global $wysija_installing;
        foreach($this->menus as $action=> $menutemp){
            $actionFull='wysija_'.$action;
            if(!isset($menutemp['subtitle'])) $menutemp['subtitle']=$menutemp['title'];
            if($action=='campaigns')    $roleformenu=$modelC->getValue('role_campaign');
            elseif($action=='subscribers')    $roleformenu=$modelC->getValue('role_subscribers');
            else $roleformenu='manage_options';
            if($wysija_installing===true){
                if($count==0){
                    $parentmenu=$actionFull;
                    $hookname=add_menu_page($menutemp['title'], $menutemp['subtitle'], $roleformenu, $actionFull , array($this->controller, 'errorInstall'), WYSIJA_EDITOR_IMG.'mail.png', $position);
                }
            }else{
                if($count==0){
                    $parentmenu=$actionFull;
                    $hookname=add_menu_page($menutemp['title'], $menutemp['subtitle'], $roleformenu, $actionFull , array($this->controller, 'render'), WYSIJA_EDITOR_IMG.'mail.png', $position);
                }else{
                    $hookname=add_submenu_page($parentmenu,$menutemp['title'], $menutemp['subtitle'], $roleformenu, $actionFull , array($this->controller, 'render'));
                }
                
                if(WYSIJA_ITF){
                    
                    if(version_compare(get_bloginfo('version'), '3.3.0')>= 0){
                        add_action('load-'.$hookname, array($this,'add_help_tab'));
                    }else{
                        
                        add_contextual_help($hookname, $this->menuHelp); 
                    }
                }
            }
            $count++;
        }
        if(isset($submenu[$parentmenu])){
            if($submenu[$parentmenu][0][2]=="wysija_subscribers") $textmenu=__('Subscribers',WYSIJA);
            else $textmenu=__('Newsletters',WYSIJA);
            $submenu[$parentmenu][0][0]=$submenu[$parentmenu][0][3]=$textmenu;
        }
    }
    function add_help_tab($params){
        $screen = get_current_screen();
        if(method_exists($screen, "add_help_tab")){
            $screen->add_help_tab(array(
            'id'	=> 'wysija_help_tab',
            'title'	=> __('Get Help!',WYSIJA),
            'content'=> $this->menuHelp));
            $tabfunc=true;
            
            
            
        }
    }
    
    function add_js($hook) {
        
        $jstrans=array();
        wp_register_script('wysija-charts', "https://www.google.com/jsapi", array( 'jquery' ), true);
        wp_register_script('wysija-admin-list', WYSIJA_URL."js/admin-listing.js", array( 'jquery' ), true, WYSIJA::get_version());
        wp_register_script('wysija-base-script-64', WYSIJA_URL."js/base-script-64.js", array( 'jquery' ), true, WYSIJA::get_version());
        wp_enqueue_style('wysija-admin-css-global', WYSIJA_URL."css/admin-global.css",array(),WYSIJA::get_version());
        wp_enqueue_script('wysija-admin-js-global', WYSIJA_URL."js/admin-wysija-global.js",array(),WYSIJA::get_version());
        
        if(WYSIJA_ITF){          
            $pagename=str_replace("wysija_","",$_REQUEST['page']);
            $backloader=&WYSIJA::get("backloader","helper");
            $backloader->initLoad($this->controller);

            $jstrans=$this->controller->jsTrans;

            $jstrans['gopremium']=__("Go Premium!",WYSIJA);
            
            $backloader->jsParse($this->controller,$pagename,WYSIJA_URL);
            
            $backloader->loadScriptsStyles($pagename,WYSIJA_DIR,WYSIJA_URL,$this->controller);
        }
            $jstrans["newsletters"]=__('Newsletters',WYSIJA);
            $jstrans["urlpremium"]='admin.php?page=wysija_config#premium';
            if(isset($_REQUEST['page']) && $_REQUEST['page']=='wysija_config'){
                $jstrans["urlpremium"]="#premium";
            }
            wp_localize_script('wysija-admin', 'wysijatrans', $jstrans);
    }
    
    function addCodeToPagePost(){
        
        if ( get_user_option('rich_editing') == 'true') {
         add_filter("mce_external_plugins", array($this,"addRichPlugin"));
         add_filter('mce_buttons', array($this,'addRichButton1'),999);
         $myStyleUrl = "../../plugins/wysija-newsletters/css/tmce/style.css";
         add_editor_style($myStyleUrl);   

         wp_enqueue_style('custom_TMCE_admin_css', WYSIJA_URL.'css/tmce/panelbtns.css');
         wp_print_styles('custom_TMCE_admin_css');
       }
    }
    function addRichPlugin($plugin_array) {
       $plugin_array['wysija_register'] = WYSIJA_URL.'mce/wysija_register/editor_plugin.js';

       return $plugin_array;
    }
    function addRichButton1($buttons) {
       $newButtons=array();
       foreach($buttons as $value) $newButtons[]=$value;

       array_push($newButtons, "|", "wysija_register");

       return $newButtons;
    }
    function version(){
        $wysijaversion= "<div class='wysija-version clearfix'>";
        $wysijaversion.= '<div class="version">'.__("Wysija Version: ",WYSIJA)."<strong>".WYSIJA::get_version()."</strong></div>";
        $wysijaversion.= '<div class="help">'.__('Need help?',WYSIJA).' <a href="http://support.wysija.com/" target="_blank">'.__('Get it here!',WYSIJA)."</a></div>";
        $config=&WYSIJA::get('config','model');
        $msg=$config->getValue("ignore_msgs");
        
        
        $wysijaversion.= "</div>";
        echo $wysijaversion;
    }
}

