<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_install extends WYSIJA_object{
    function WYSIJA_help_install(){
        if(file_exists(ABSPATH . 'wp-admin'.DS.'includes'.DS.'upgrade.php'))    require_once(ABSPATH . 'wp-admin'.DS.'includes'.DS.'upgrade.php');
    }
    function install(){
        $values=array();
        
        if(!$this->testSystem()) return false;
        
        
        if(!$this->createTables()) return false;
        
        $this->moveData('themes');
        $this->moveData('dividers');
        $this->moveData('bookmarks');
        
        $this->recordDefaultUserField();
        
        $this->defaultSettings($values);
        
        $this->defaultList($values);
        
        $this->defaultCampaign($values);
        
        $helpImport=&WYSIJA::get("import","helper");    
        $values['importwp_list_id']=$helpImport->importWP();
        
        $this->createPage($values);
        
        $this->createWYSIJAdir($values);
        
        $modelConf=&WYSIJA::get("config","model");
        $mailModel=&WYSIJA::get("email","model");
        $mailModel->blockMe=true;
        $values["confirm_email_id"]=$mailModel->insert(
                array("type"=>"0",
                    "from_email"=>$values["from_email"],
                    "from_name"=>$values["from_name"],
                    "replyto_email"=>$values["from_email"],
                    "replyto_name"=>$values["from_name"],
                    "subject"=>$modelConf->getValue("confirm_email_title"),
                    "body"=>$modelConf->getValue("confirm_email_body"),
                    "status"=>"1"));
        
        $values['installed']=true;
        $values['installed_time']=mktime();
        $values['wysija_db_version']="1.1";
        $modelConf->save($values);
        
        $this->testNLplugins();
        $this->wp_notice(str_replace(array('[link]','[/link]'),array('<a href="admin.php?page=wysija_config">','</a>'),__("Wysija has been installed successfully. Go to the [link]settings page[/link] now, and start blasting emails.",WYSIJA)));
        global $wysija_installing;
        $wysija_installing=false;
        return true;
    }
    
    
    function testSystem(){
        $haserrors=false;
        
        $modelObj=&WYSIJA::get("user","model");
        $query="CREATE TABLE IF NOT EXISTS `".$modelObj->getPrefix()."user_list` (
  `list_id` INT unsigned NOT NULL,
  `user_id` INT unsigned NOT NULL,
  `sub_date` INT unsigned DEFAULT 0,
  `unsub_date` INT unsigned DEFAULT 0,
  PRIMARY KEY (`list_id`,`user_id`)
) ENGINE=MyISAM";
        global $wpdb;

        

        $wpdb->query($query);
        $query="SHOW TABLES like '".$modelObj->getPrefix()."user_list';";
        $res=$wpdb->get_var($query);
        if(!$res){
            $this->wp_error(sprintf(
                    __('The MySQL user you have setup on your Wordpress site (wp-config.php) doesn\'t have enough privileges to CREATE MySQL tables. Please change this user yourself or contact the administrator of your site in order to complete Wysija\'s installation. mysql errors:(%1$s)',WYSIJA),  mysql_error()));
            $haserrors=true;
        }
        

        
        $helperF=&WYSIJA::get('file',"helper");
        if(!$helperF->makeDir()){
            $upload_dir = wp_upload_dir();
            $this->wp_error(sprintf(__('The folder "%1$s" is not writable, please change the access rights to this folder so that Wysija can setup itself properly.',WYSIJA),$upload_dir['basedir'])."<a target='_blank' href='http://codex.wordpress.org/Changing_File_Permissions'>".__('Read documentation',WYSIJA)."</a>");
            $haserrors=true;
        }
        
        if($haserrors) return false;
        return true;
    }
    function defaultList(&$values){
        $model=&WYSIJA::get("list","model");
        $listname=__("My first list",WYSIJA);
        $defaultListId=$model->insert(array(
            "name"=>$listname,
            "description"=>__('The list created automatically on install of the Wysija.',WYSIJA),
            "is_enabled"=>1));
        $values['default_list_id']=$defaultListId;
    }
    function defaultCampaign($valuesconfig){
        $modelCampaign=&WYSIJA::get("campaign","model");
        $campaign_id=$modelCampaign->insert(
                array(
                    "name"=>__('Example Newsletter',WYSIJA),
                    "description"=>__('Default newsletter created automatically during installation.',WYSIJA),
                    ));
        $modelEmail=&WYSIJA::get("email","model");
        $modelEmail->fieldValid=false;
        $dataEmail=array(
            "campaign_id"=>$campaign_id,
            "subject"=>__('Example Newsletter',WYSIJA));


        $wjEngine =& WYSIJA::get('wj_engine', 'helper');
        $defaultStyles = $wjEngine->getDefaultStyles();
        $defaultStyles['html']['background']=$defaultStyles['header']['background']=$defaultStyles['footer']['background']="E8E8E8";

        $dividersHelper =& WYSIJA::get('dividers', 'helper');
        $defaultDivider = $dividersHelper->getDefault();
        $dataEmail['wj_data']=array (
          'version' => '1.1.0',
          'header' => 
          array (
            'text' => NULL,
            'image' => 
            array (
              'src' => WYSIJA_EDITOR_IMG.'default-newsletter/full/white-label-logo-02.png',
              'width' => 600,
              'height' => 240,
              'alignment' => 'left',
              'static' => false,
            ),
            'alignment' => 'left',
            'static' => true,
            'type' => 'header',
          ),
          'body' => 
          array (
            'block-1' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("Wysija Bootcamp",WYSIJA).'</h2><p>'.
                  __("This example newsletter is for you to <span style='color: #008000;'>experiment</span> with. It takes about 15 minutes to read through this guide. Its pretty intuitive altogether.",WYSIJA).'</p><p>'.
                  __("Things you can do right in this newsletter :",WYSIJA).'</p><ul><li>'.
                  __("Drop a WordPress post",WYSIJA).'</li><li>'.
                  __("<strong>Insert</strong> images",WYSIJA).'</li><li>'.
                  __("Change your <strong>divider style</strong>",WYSIJA).'</li><li>'.
                  __("Click on this text to <strong>edit</strong> it",WYSIJA).'</li><li>'.
                  __("<strong>Reorder</strong> items around",WYSIJA).'</li><li>'.
                  __("Install a <strong>new theme</strong> and apply it",WYSIJA).'</li><li>'.
                  __("Change the styles in the Style tab",WYSIJA).'</li></ul>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '1',
              'type' => 'content',
            ),
            'block-2' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("Duplicate, Don't Save",WYSIJA).'</h2><p>'.
                  __("We often get asked: <strong><em>How can I save my design</em>?</strong> Well, you can't.",WYSIJA).'</p><p>'.
                  __("You simply need to duplicate a previous newsletter to take its design. If you're installing Wysija for a colleague or a client, create a draft newsletter he or she can duplicate at will.",WYSIJA).'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '2',
              'type' => 'content',
            ),
            'block-3' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("Images and Text Together",WYSIJA).'</h2>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '3',
              'type' => 'content',
            ),
            'block-4' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.
                  __("It's possible to mix text with images.</p><p><strong>Try it:</strong> mouse over the image to realign it left or right.",WYSIJA).'</p><p>'.
                  __("You can replace that image by dropping a new one over it.",WYSIJA).'</p><p>'.
                  __("Because email clients don't load images by default, you can add an <strong>alternate text</strong> that will show.",WYSIJA).'</p><p>'.
                  __("<strong>Try it:</strong> click on the link button of the image.",WYSIJA).'</p><p>'.
                  __("When you drop a <strong>WordPress post, it will include the first image in that post or the post's featured image</strong>. All other images will be ignored!",WYSIJA).'</p>',
              ),
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG.'default-newsletter/full/sample-newsletter-01_07.png',
                'width' => 281,
                'height' => 190,
                'url' => 'http://www.wysija.com',
                'alt' => 'A bird with an envelop',
                'alignment' => 'left',
                'static' => false,
              ),
              'alignment' => 'left',
              'static' => false,
              'position' => '4',
              'type' => 'content',
            ),
            'block-5' => array_merge($defaultDivider, array(
              'position' => '5',
              'type' => 'divider'
            ))
            ,
            'block-6' => 
            array (
              'text' => NULL,
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG.'default-newsletter/full/sample-newsletter-01_10.png',
                'width' => 340,
                'height' => 20,
                'alignment' => 'center',
                'static' => false,
              ),
              'alignment' => 'center',
              'static' => false,
              'position' => '6',
              'type' => 'content',
            ),
            'block-7' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.
                  __("If you want to use a special font for your titles, you'll have to create them as an image in <strong>Photoshop</strong>, just like the example title above.",WYSIJA).'</p><p>'.
                  __("<strong>Why? </strong>The choices of fonts on newsletters are very very limited.",WYSIJA).' '.
                  __("Fonts aren't sent with emails. Fonts reside in the email clients of the world (Outlook, Gmail, Yahoo) and the computers that open those emails. Only a few fonts are really on all these machines. Crazy but true.",WYSIJA).'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '7',
              'type' => 'content',
            ),
            'block-8' => array_merge($defaultDivider, array(
              'position' => '8',
              'type' => 'divider'
            )
            ),
            'block-9' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("Can I Insert Videos in my Newsletters?",WYSIJA).'</h2>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '9',
              'type' => 'content',
            ),
            'block-10' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.
                  __("You <strong>can't</strong> insert videos in your emails. Instead, use an image that looks like the player.",WYSIJA).'</p><p>'.
                  __("Find images of popular players by visiting this page (copy and paste it!):",WYSIJA).'</p><p>http://support.wysija.com/knowledgebase/how-to-embed-a-video-from-youtube-or-vimeo-in-your-newsletter/</p><p>&nbsp;</p>',
              ),
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG.'default-newsletter/full/sample-newsletter-01_25.png',
                'width' => 321,
                'height' => 236,
                'alignment' => 'left',
                'static' => false,
              ),
              'alignment' => 'left',
              'static' => false,
              'position' => '10',
              'type' => 'content',
            ),
            'block-11' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("Social Bookmark Icons",WYSIJA).'</h2>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '11',
              'type' => 'content',
            ),
            'block-12' => 
            array (
              'width' => 184,
              'alignment' => 'center',
              'items' => 
              array (
                0 => 
                array (
                  'src' => WYSIJA_UPLOADS_URL.'bookmarks/medium/02/facebook.png',
                  'width' => 32,
                  'height' => 32,
                  'url' => 'http://www.facebook.com/wysija',
                  'alt' => 'Facebook',
                  'cellWidth' => 61,
                  'cellHeight' => 32,
                ),
                1 => 
                array (
                  'src' => WYSIJA_UPLOADS_URL.'bookmarks/medium/02/twitter.png',
                  'width' => 32,
                  'height' => 32,
                  'url' => 'http://www.twitter.com/wysija',
                  'alt' => 'Twitter',
                  'cellWidth' => 61,
                  'cellHeight' => 32,
                ),
                2 => 
                array (
                  'src' => WYSIJA_UPLOADS_URL.'bookmarks/medium/02/google.png',
                  'width' => 32,
                  'height' => 32,
                  'url' => 'http://www.google.com',
                  'alt' => 'Google',
                  'cellWidth' => 61,
                  'cellHeight' => 32,
                ),
              ),
              'position' => '12',
              'type' => 'gallery',
            ),
            'block-13' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.
                  __("The icons above were added using the <em>Social bookmarks</em> widget. Try it. It's in the <em>Contents</em> tab on the right. You'll see you have plenty of icon choices. Neat!",WYSIJA).'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '13',
              'type' => 'content',
            ),
            'block-14' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("Be Cool, Submit Your Design",WYSIJA).'</h2><p>'.
                  __("You feel your own newsletter design is awesome? Share it with the Wysija team, in the <em>Themes</em> tab on the right, and we might showcase it in our blog: www.wysija.com/blog <br><br>You can even share your themes with the entire community. Get in touch on www.wysija.com to submit yours via our contact form.",WYSIJA).'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '14',
              'type' => 'content',
            ),
            'block-15' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("Get Help on support.wysija.com",WYSIJA).'</h2><p>'.
                  __("Of course, things don't always work the way they should. But we're here to help. Find us on support.wysija.com. We have documentation and a ticket system if you need to get us involved.",WYSIJA).'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '15',
              'type' => 'content',
            ),
            'block-16' => 
            array (
              'text' => 
              array (
                'value' => '<h2 class="align-center">'.
                  __("I Want to Change my Footer's Content!",WYSIJA).'</h2><p class="align-left">'.
                  __("You can change the footer text in Wysija's Settings, and not here.",WYSIJA).' '.
                  __("In <em>The Basics</em> tab, you can add <strong>your postal address</strong> (good against spam filters), or whatever you see fit.",WYSIJA).' '.
                  __('Change the text for the "<strong>Unsubscribe</strong>" link in the <em>Advanced</em> tab<span> of the Settings.<br></span>',WYSIJA).'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '16',
              'type' => 'content',
            ),
          ),
          'footer' => 
          array (
            'text' => NULL,
            'image' => 
            array (
              'src' => WYSIJA_EDITOR_IMG.'default-newsletter/full/footer1.png',
              'width' => 600,
              'height' => 46,
              'alignment' => 'center',
              'static' => false,
            ),
            'alignment' => 'center',
            'static' => true,
            'type' => 'footer',
          ),
        );
        
        foreach( $dataEmail['wj_data'] as $key =>&$eachval){
            if($key=="body") {
                foreach($eachval as &$realeachval){
                    if(isset($realeachval['text']['value']))    $realeachval['text']['value']=base64_encode($realeachval['text']['value']);
                }
            }
        }
        $dataEmail['params'] = array(
            'quickselection' => array(
                'wp-301' => array(
                    'identifier' => 'wp-301',
                    'width' => 281,
                    'height' => 190,
                    'url' => WYSIJA_EDITOR_IMG.'default-newsletter/full/sample-newsletter-01_07.png',
                    'thumb_url' => WYSIJA_EDITOR_IMG.'default-newsletter/sample-newsletter-01_07-150x150.png'
                ),
                'wp-302' => array(
                    'identifier' => 'wp-302',
                    'width' => 482,
                    'height' => 30,
                    'url' => WYSIJA_EDITOR_IMG.'default-newsletter/full/sample-newsletter-01_10.png',
                    'thumb_url' => WYSIJA_EDITOR_IMG.'default-newsletter/sample-newsletter-01_10-150x30.png'
                ),
                'wp-303' => array(
                    'identifier' => 'wp-303',
                    'width' => 321,
                    'height' => 236,
                    'url' => WYSIJA_EDITOR_IMG.'default-newsletter/full/sample-newsletter-01_25.png',
                    'thumb_url' => WYSIJA_EDITOR_IMG.'default-newsletter/sample-newsletter-01_25-150x150.png'
                )
            )
        );
        $dataEmail['wj_styles'] = $defaultStyles;
        $dataEmail['params'] = base64_encode(serialize($dataEmail['params']));
        $dataEmail['wj_styles'] = base64_encode(serialize($dataEmail['wj_styles']));
        $dataEmail['wj_data'] = base64_encode(serialize($dataEmail['wj_data']));
            
        $dataEmail['replyto_name']=$dataEmail['from_name']=$valuesconfig['from_name'];
        $dataEmail['replyto_email']=$dataEmail['from_email']=$valuesconfig['from_email'];
        $data['email']['email_id']=$modelEmail->insert($dataEmail);
        $this->notice(__("Example Campaign created.",WYSIJA));
    }
    function createTables(){
        $filename = dirname(__FILE__).DS."install.sql";
        $handle = fopen($filename, "r");
        $query = fread($handle, filesize($filename));
        fclose($handle);
        $modelObj=&WYSIJA::get("user","model");
        $query=str_replace("CREATE TABLE IF NOT EXISTS `","CREATE TABLE IF NOT EXISTS `".$modelObj->getPrefix(),$query);
        $queries=explode("-- QUERY ---",$query);
        
        $con = @mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, true );
        $haserrors=false;
        if (!$con){
            die('Could not connect: ' . mysql_error());
            $haserrors=true;
        }else{
            @mysql_select_db( DB_NAME ,$con);
            foreach($queries as $qry){
                if(!mysql_query($qry,$con)){
                    $this->notice(mysql_error());
                    $haserrors=true;
                }
            }
        }
        $arraytables=array("user_list","user","list","campaign","campaign_list","email","user_field","queue","user_history","email_user_stat","url","email_user_url","url_mail");
        $modelWysija=new WYSIJA_model();
        $missingtables=array();
        foreach($arraytables as $tablename){
            if(!$modelWysija->query("SHOW TABLES like '".$modelWysija->getPrefix().$tablename."';")) {
                $missingtables[]=$modelWysija->getPrefix().$tablename;
            }
        }
        mysql_close($con);
        if($missingtables) {
            $this->error(sprintf(__('These tables could not be created on installation: %1$s',WYSIJA),implode(', ',$missingtables)),1);
            $haserrors=true;
        }
        if($haserrors) return false;
        return true;
    }
    function createWYSIJAdir(&$values){
        $upload_dir = wp_upload_dir();
        $dirname=$upload_dir['basedir'].DS."wysija".DS;
        $url=$upload_dir['baseurl']."/wysija/";
        if(!file_exists($dirname)){
            if(!mkdir($dirname, 0755,true)){
                return false;
            }
        }
        $values['uploadfolder']=$dirname;
        $values['uploadurl']=$url;
    }
    function moveData($folder) {
        $fileHelper =& WYSIJA::get('file', 'helper');

        $targetDir = $fileHelper->makeDir($folder);
        if($targetDir === FALSE) {

            return FALSE;
        } else {

            $sourceDir = WYSIJA_DATA_DIR.$folder.DS;

            if(is_dir($sourceDir) === FALSE) return FALSE;

            $files = scandir($sourceDir);

            foreach($files as $filename) {
                if(!in_array($filename, array('.', '..', '.DS_Store', 'Thumbs.db'))) {
                    $this->rcopy($sourceDir.$filename, $targetDir.$filename);
                }
            }
        }
    }
    function rrmdir($dir) {
      if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file)
        if ($file != "." && $file != "..") $this->rrmdir("$dir".DS."$file");
        rmdir($dir);
      }
      else if (file_exists($dir)) {
          $dir=str_replace('/',DS,$dir);
          unlink($dir);
      }
    }
    function rcopy($src, $dst) {
      if (file_exists($dst)) $this->rrmdir($dst);
      if (is_dir($src)) {
        mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file)
        if ($file != "." && $file != "..") $this->rcopy("$src/$file", "$dst/$file");
      }
      else if (file_exists($src)) {
          copy(str_replace('/',DS,$src), str_replace('/',DS,$dst));
      }
    }
    function recordDefaultUserField(){
        $modelUF=&WYSIJA::get("user_field","model");
        $arrayInsert=array(
            array("name"=>__("First name",WYSIJA),"column_name"=>"firstname","error_message"=>__("Please enter first name",WYSIJA)),
            array("name"=>__("Last name",WYSIJA),"column_name"=>"lastname","error_message"=>__("Please enter last name",WYSIJA)));
        foreach($arrayInsert as $insert){
            $modelUF->insert($insert);
            $modelUF->reset();
        }
        
    }
    function defaultSettings(&$values){
        

        global $current_user;
        $values['replyto_name']=$values['from_name']=$current_user->user_login;
        $values['emails_notified']=$values['replyto_email']=$values['from_email']=$current_user->user_email;
    }

    function createPage(&$values){
        
        $my_post = array(
        'post_status' => 'publish', 
        'post_type' => 'wysijap',
        'post_author' => 1,
        'post_content' => '[wysija_page]',
        'post_title' => __("Subscription confirmation",WYSIJA),
        'post_name' => 'subscriptions');
        $values['confirm_email_link']=wp_insert_post( $my_post );
        flush_rewrite_rules();
    }
    
    function testNLplugins(){
        $importHelp=&WYSIJA::get("import","helper");
        $importHelp->testPlugins();
    }
}
