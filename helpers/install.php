<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_install extends WYSIJA_object{
    
    function WYSIJA_help_install(){
        require_once(ABSPATH . 'wp-admin'.DS.'includes'.DS.'upgrade.php');
    }
    
    function install(){
        $values=array();
        
        /* test server against few things to make sure the installation can be continued */
        if(!$this->testSystem()) return false;
        
        
        /* create the tables */
        $this->createTables();
        /* record custom fields lastname firstname in the user_field table */
        $this->recordDefaultUserField();
        /* save default values for the fields : from_name, from_email replyto_name, replyto_email*/
        $this->defaultSettings($values);

        /* create a default list */
        $this->defaultList($values);

        /* create a default campaign */
        $this->defaultCampaign($values);

        /* synchronize our user table with wordpress users */
        $helpImport=&WYSIJA::get("import","helper");    
        $values['importwp_list_id']=$helpImport->importWP();

        /* create subscription redirection page */
        $this->createPage($values);

        /* create the default dir */
        $this->createWYSIJAdir($values);
    
        /*move the themes to the right folder*/
        $this->moveThemes();
        
        /* save the confirmation email in the table */
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
        /* save the config into the db */
        $values['installed']=true;
        $values['installed_time']=mktime();
        
        $modelConf->save($values);
        
        /* look for existing newsletter plugins to import from  */
        $this->testNLplugins();
        $this->wp_notice(str_replace(array('[link]','[/link]'),array('<a href="admin.php?page=wysija_config">','</a>'),__("Wysija has been installed successfully. Go to the [link]settings page[/link] now, and start blasting emails.",WYSIJA)));

        global $wysija_installing;
        $wysija_installing=false;
        return true;
    }
    
    /**
     * 
     */
    function testSystem(){
        $haserrors=false;
        
        /* test that we can create tables on the mysql server */
        $modelObj=&WYSIJA::get("user","model");
        $query="CREATE TABLE `".$modelObj->getPrefix()."user_list` (
  `list_id` INT unsigned NOT NULL,
  `user_id` INT unsigned NOT NULL,
  `sub_date` INT unsigned DEFAULT 0,
  `unsub_date` INT unsigned DEFAULT 0,
  PRIMARY KEY (`list_id`,`user_id`)
) ENGINE=MyISAM";
        global $wpdb;

        //DB_USER, DB_PASSWORD, DB_NAME, DB_HOST
        $con = @mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, true );
        if (!$con){
            die('Could not connect: ' . mysql_error());
        }else{
            @mysql_select_db( DB_NAME ,$con);
            if(!mysql_query($query,$con)){
                $this->wp_error(sprintf(
                        __('The MySQL user you have setup on your Wordpress site (wp-config.php) doesn\'t have enough privileges to CREATE MySQL tables. Please change this user yourself or contact the administrator of your site in order to complete Wysija\'s installation. mysql errors:(%1$s)',WYSIJA),  mysql_error()));
                $haserrors=true;
            }
        }

        mysql_close($con);
        /* test that we can create folder in the uploads folder */
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


        $dataEmail['wj_data']=array (
          'version' => '0.0.9',
          'header' => 
          array (
            'text' => 
            array (
              'value' => '<h1>'.__("A Guide to Using Wysija for Beginners",WYSIJA).'</h1>',
            ),
            'image' => 
            array (
              'src' => WYSIJA_EDITOR_IMG."default-newsletter/full/white-label-logo.png",
              'width' => 128,
              'height' => 128,
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
              'divider' => 
              array (
                'src' => NULL,
                'width' => NULL,
                'height' => NULL,
              ),
              'position' => '1',
              'type' => 'divider',
            ),
            'block-2' => 
            array (
              'text' => 
              array (
                'value' => '<h2>'.__("Images and Text Together",WYSIJA).'</h2>',
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
                'value' => '<p>'.__("It's possible to mix text with images, aligned left or right.",WYSIJA).'</p><p>'.
                  __("If you want the image to be full width, <strong>it needs to be centered</strong>.",WYSIJA).'</p><p>'.
                  __("Because email clients don't load images by default, you can add an <strong>alternate text</strong> that will show. Do this by clicking on the link button of the image.",WYSIJA).'</p><p>'.
                  __("Finally, when you drop a WordPress post, it will include the first image in that post or the post's featured image. All other images will be ignored!",WYSIJA).'</p>',
              ),
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_07.png",
                'width' => 281,
                'height' => 190,
                'url' => 'http://www.wysija.com',
                'alt' => __('A bird with an envelop',WYSIJA),
                'alignment' => 'left',
                'static' => false,
              ),
              'alignment' => 'left',
              'static' => false,
              'position' => '3',
              'type' => 'content',
            ),
            'block-4' => 
            array (
              'divider' => 
              array (
                'src' => NULL,
                'width' => NULL,
                'height' => NULL,
              ),
              'position' => '4',
              'type' => 'divider',
            ),
            'block-5' => 
            array (
              'text' => 
              array (
                'value' => '<h3 style="text-align: left;">'.__("3 Types of Titles for Your Convenience",WYSIJA).'</h3>',
              ),
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_14.png",
                'width' => 52,
                'height' => 45,
                'alignment' => 'left',
                'static' => false,
              ),
              'alignment' => 'left',
              'static' => false,
              'position' => '5',
              'type' => 'content',
            ),
            'block-6' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.__("As you can see above, we simply aligned the image and the title to the left.",WYSIJA).'</p><p>'.
                  __("Three types of Titles are available:",WYSIJA).'</p><ol><li>'.
                  __('Heading 1',WYSIJA).'</li><li>'.
                  __('Heading 2',WYSIJA).'</li><li>'.
                  __('And you guessed it, Heading 3',WYSIJA).'</li></ol>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '6',
              'type' => 'content',
            ),
            'block-7' => 
            array (
              'divider' => 
              array (
                'src' => NULL,
                'width' => NULL,
                'height' => NULL,
              ),
              'position' => '7',
              'type' => 'divider',
            ),
            'block-8' => 
            array (
              'text' => NULL,
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_10.png",
                'width' => 323,
                'height' => 19,
                'alignment' => 'left',
                'static' => false,
              ),
              'alignment' => 'left',
              'static' => false,
              'position' => '8',
              'type' => 'content',
            ),
            'block-9' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.__("The choices of fonts on newsletters are fairly limited. <strong>Why?</strong> Because fonts aren't sent with emails, we need to rely on the very few font families that are installed on all computers of this World.",WYSIJA).
                    '</p><p>'.
                    __("<strong>Tip: </strong>you can use images to include your favorite font. Do keep in mind that most email clients don't show images inserted in your newsletters by default.<strong><br></strong>",WYSIJA)
                    .'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '9',
              'type' => 'content',
            ),
            'block-10' => 
            array (
              'text' => NULL,
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_21.png",
                'width' => 578,
                'height' => 17,
                'alignment' => 'center',
                'static' => false,
              ),
              'alignment' => 'center',
              'static' => false,
              'position' => '10',
              'type' => 'content',
            ),
            'block-11' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.__("You can also use images instead of horizontal lines, like the stars above. This makes your dividers a little more personalized.",WYSIJA).'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '11',
              'type' => 'content',
            ),
            'block-12' => 
            array (
              'divider' => 
              array (
                'src' => NULL,
                'width' => NULL,
                'height' => NULL,
              ),
              'position' => '12',
              'type' => 'divider',
            ),
            'block-13' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.__("You <strong>can't</strong> insert videos in your emails. Instead, use an image that looks like the player.",WYSIJA).'</p><p>&nbsp;</p>',
              ),
              'image' => 
              array (
                'src' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_25.png",
                'width' => 321,
                'height' => 236,
                'alignment' => 'left',
                'static' => false,
              ),
              'alignment' => 'left',
              'static' => false,
              'position' => '13',
              'type' => 'content',
            ),
            'block-14' => 
            array (
              'divider' => 
              array (
                'src' => NULL,
                'width' => NULL,
                'height' => NULL,
              ),
              'position' => '14',
              'type' => 'divider',
            ),
            'block-15' => 
            array (
              'text' => 
              array (
                'value' => '<p>'.__("The footer's content is <strong>mandatory</strong>: we enforce the unsubscription link.",WYSIJA).'</p><p>'.
                  __("To change the footer's content, visit the Wysija Settings. There, you can add an address (recommended to avoid spam filters) and change the unsubscribe label.").'</p>',
              ),
              'image' => NULL,
              'alignment' => 'center',
              'static' => false,
              'position' => '15',
              'type' => 'content',
            ),
          ),
        );


        $dataEmail['params']=array (
          'quickselection' => 
          array (
            'wp-301' => 
            array (
              'identifier' => 'wp-301',
              'width' => '281',
              'height' => '190',
              'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_07.png",
              'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/sample-newsletter-01_07-150x150.png",
              'IS_PAIR' => 0,
              'IS_LAST' => false,
              'IS_FIRST' => false,
            ),
            'wp-302' => 
            array (
              'identifier' => 'wp-302',
              'width' => '482',
              'height' => '30',
              'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_10.png",
              'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/sample-newsletter-01_10-150x30.png",
              'IS_PAIR' => 1,
              'IS_LAST' => false,
              'IS_FIRST' => false,
            ),
            'wp-303' => 
            array (
              'identifier' => 'wp-303',
              'width' => '52',
              'height' => '45',
              'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_14.png",
              'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_14.png",
              'IS_PAIR' => 0,
              'IS_LAST' => false,
              'IS_FIRST' => false,
            ),
            'wp-304' => 
            array (
              'identifier' => 'wp-304',
              'width' => '70',
              'height' => '42',
              'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_16.png",
              'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_16.png",
              'IS_PAIR' => 1,
              'IS_LAST' => false,
              'IS_FIRST' => false,
            ),
            'wp-305' => 
            array (
              'identifier' => 'wp-305',
              'width' => '546',
              'height' => '16',
              'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_21.png",
              'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/sample-newsletter-01_21-150x16.png",
              'IS_PAIR' => 0,
              'IS_LAST' => false,
              'IS_FIRST' => false,
            ),
            'wp-306' => 
            array (
              'identifier' => 'wp-306',
              'width' => '321',
              'height' => '236',
              'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/sample-newsletter-01_25.png",
              'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/sample-newsletter-01_25-150x150.png",
              'IS_PAIR' => 1,
              'IS_LAST' => false,
              'IS_FIRST' => false,
            ),
            'wp-307' => 
            array (
              'identifier' => 'wp-307',
              'width' => '140',
              'height' => '140',
              'url' => WYSIJA_EDITOR_IMG."default-newsletter/full/white-label-logo.png",
              'thumb_url' => WYSIJA_EDITOR_IMG."default-newsletter/full/white-label-logo.png",
              'IS_PAIR' => 0,
              'IS_LAST' => true,
              'IS_FIRST' => false,
            ),
          ),
        );

        $dataEmail['wj_styles']=array (
          'body' => 
          array (
            'color' => '000000',
            'family' => 'Arial',
            'size' => '12',
            'background' => 'FFFFFF',
          ),
          'h1' => 
          array (
            'color' => '000000',
            'family' => 'Arial',
            'size' => '36',
          ),
          'h2' => 
          array (
            'color' => '000000',
            'family' => 'Arial',
            'size' => '30',
          ),
          'h3' => 
          array (
            'color' => '000000',
            'family' => 'Arial',
            'size' => '28',
          ),
          'a' => 
          array (
            'color' => '0F1685',
            'family' => 'Arial',
            'size' => '12',
            'underline' => '1',
          ),
          'footer' => 
          array (
            'color' => '000000',
            'family' => 'Arial',
            'size' => '11',
            'background' => 'F2F2F2',
          ),
          'header' => 
          array (
            'background' => 'FFFFFF',
          ),
          'html' => 
          array (
            'background' => 'FFFFFF',
          ),
          'divider' => 
          array (
            'background' => '000000',
            'height' => '5',
          ),
        );
        
        
        $dataEmail['params']=base64_encode(serialize($dataEmail['params']));
        $dataEmail['wj_styles']=base64_encode(serialize($dataEmail['wj_styles']));
        $dataEmail['wj_data']=base64_encode(serialize($dataEmail['wj_data']));
            
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
        
        $results=array();
        foreach($queries as $qry){
            $results[]=dbDelta($qry);
        }
        
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
        
        $fileHelp=&WYSIJA::get("file","helper");
        $resultdir=$fileHelp->makeDir("themes");
        if(!$resultdir)   return false;
    }
    
    /* on install move the default themes to the upload folder */
    function moveThemes(){
        $fileHelp=&WYSIJA::get("file","helper");
        $resultdir=$fileHelp->makeDir("templates");
        
        $upload_dir = wp_upload_dir();
        
        $dirname=str_replace("/",DS,$upload_dir['basedir']).DS."wysija".DS."templates".DS;
        $defaultthemes=WYSIJA_DIR."themes".DS;
        if(!file_exists($defaultthemes)) return false;
        $files = scandir($defaultthemes);

        foreach($files as $filename){
            if(!in_array($filename, array('.','..',".DS_Store","Thumbs.db")) && is_dir($defaultthemes.$filename)){
                if(!file_exists($defaultthemes.$filename)) continue;
                $this->rcopy($defaultthemes.$filename, $dirname.$filename);
            }
        }
    }
    function rrmdir($dir) {
      if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file)
        if ($file != "." && $file != "..") $this->rrmdir("$dir/$file");
        rmdir($dir);
      }
      else if (file_exists($dir)) unlink($dir);
    }

    function rcopy($src, $dst) {
      if (file_exists($dst)) $this->rrmdir($dst);
      if (is_dir($src)) {
        mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file)
        if ($file != "." && $file != "..") $this->rcopy("$src/$file", "$dst/$file");
      }
      else if (file_exists($src)) copy($src, $dst);
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
        
        /* get the user data for the admin */
        $datauser=wp_get_current_user();

        $values['replyto_name']=$values['from_name']=$datauser->user_login;
        $values['emails_notified']=$values['replyto_email']=$values['from_email']=$datauser->user_email;
    }
   

    function createPage(&$values){
        
        /* get the user data for the admin */
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
