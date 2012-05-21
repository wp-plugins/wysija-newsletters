<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_control_back_campaigns extends WYSIJA_control{

    function WYSIJA_control_back_campaigns(){
        $modelC=&WYSIJA::get("config","model");
        if(!current_user_can($modelC->getValue("role_campaign")))  die("Action is forbidden.");
        parent::WYSIJA_control();
    }

    function switch_theme() {
        if(isset($_POST['wysijaData'])) {
            $rawData = $_POST['wysijaData'];
            // avoid using stripslashes as it's not reliable depending on the magic quotes settings
            $rawData = str_replace('\"', '"', $rawData);
            // decode JSON data
            $rawData = json_decode($rawData, true);
            
            $theme = (isset($rawData['theme'])) ? $rawData['theme'] : 'default';
            
            $wjEngine =& WYSIJA::get('wj_engine', 'helper');
            $res['templates'] = $wjEngine->renderTheme($theme);
            
            $campaign_id = (int)$_REQUEST['campaignID'];
            $campaignsHelper =& WYSIJA::get('campaigns', 'helper');
            $campaignsHelper->saveParameters($campaign_id, 'divider', $res['templates']['divider_options']);
            
            $res['templates']['theme'] = $theme;
            $res['styles'] = $wjEngine->renderThemeStyles($theme);
        } else {
            $res['msg'] = __("The theme you selected could not be loaded.",WYSIJA);
            $res['result'] = false;
        }
        return $res;
    }

    function save_editor() {
        // decode json data and convert to array
        $rawData = '';
        if(isset($_POST['wysijaData'])) {
            $rawData = $_POST['wysijaData'];
            // avoid using stripslashes as it's not reliable depending on the magic quotes settings
            $rawData = str_replace('\"', '"', $rawData);
            // decode JSON data
            $rawData = json_decode($rawData, true);
        }

        if(!$rawData){
            $this->error("Error saving",false);
            return array('result' => false);
        }

        $wjEngine =& WYSIJA::get('wj_engine', 'helper');
        $wjEngine->setData($rawData);
        $result = false;

        // get campaign id
        $campaign_id = $_REQUEST['campaignID'];
        $modelEmail =& WYSIJA::get('email', 'model');
        $emailData=$modelEmail->getOne(array('wj_styles', 'subject'),array("campaign_id"=>$campaign_id));

        $wjEngine->setStyles($emailData['wj_styles'], true);

        $values = array('wj_data' => $wjEngine->getEncoded('data'));

        $values['body'] = $wjEngine->renderEmail($emailData['subject']);

        // update data in DB
        $result = $modelEmail->update($values, array('campaign_id' => $campaign_id));

        if(!$result) {
            // throw error
            $this->error(__("Your email could not be saved", WYSIJA));
        } else {
            // save successful
            $this->notice(__("Your email has been saved", WYSIJA));
        }

        return array('result' => $result);
    }
    
    function save_styles() {
        // decode json data and convert to array
        $rawData = '';
        if(isset($_POST['wysijaStyles'])) {
            $rawData = $_POST['wysijaStyles'];
            // avoid using stripslashes as it's not reliable depending on the magic quotes settings
            $rawData = str_replace('\"', '"', $rawData);
            // decode JSON data
            $rawData = json_decode($rawData, true);
            
        }

        // handle checkboxes
        if(array_key_exists('a-underline', $rawData) === false) {
            $rawData['a-underline'] = -1;
        }

        $wjEngine =& WYSIJA::get('wj_engine', 'helper');
        $wjEngine->setStyles($wjEngine->formatStyles($rawData));

        $result = false;

        $values = array(
            'wj_styles' => $wjEngine->getEncoded('styles')
        );

        // get campaign id
        $campaign_id = $_REQUEST['campaignID'];

        // update data in DB
        $modelEmail =& WYSIJA::get('email', 'model');
        $result = $modelEmail->update($values, array('campaign_id' => $campaign_id));

        if(!$result) {
            // throw error
            $this->error(__("Styles could not be saved", WYSIJA));
        } else {
            // save successful
            $this->notice(__("Styles have been saved", WYSIJA));
        }

        return array(
            'styles' => $wjEngine->renderStyles(),
            'result' => $result
        );
    }

    function deleteimg(){

        if(isset($_REQUEST['imgid']) && $_REQUEST['imgid']>0){
            /* delete the image with id imgid */
             $result=wp_delete_attachment($_REQUEST['imgid'],true);
             if($result){
                 $this->notice(__("Image has been deleted.",WYSIJA));
             }
        }

        $res=array();
        $res['result'] = $result;
        return $res;
    }
    
    function deleteTheme(){
        if(isset($_REQUEST['themekey']) && $_REQUEST['themekey']){
            /* delete the image with id imgid */
            $helperTheme=&WYSIJA::get("themes","helper");
            $result=$helperTheme->delete($_REQUEST['themekey']);
        }

        $res=array();
        $res['result'] = $result;
        return $res;
    }


    function save_IQS() {
        // decode json data and convert to array
        $wysijaIMG = '';
        if(isset($_POST['wysijaIMG'])) {
            $wysijaIMG = json_decode(stripslashes($_POST['wysijaIMG']), TRUE);
        }
        $values = array(
            'params' => array('quickselection'=>$wysijaIMG)
        );

        // get campaign id
        $campaign_id = (int)$_REQUEST['campaignID'];

        // update data in DB
        $modelEmail =& WYSIJA::get('email', 'model');
        $result = $modelEmail->update($values, array('campaign_id' => $campaign_id));

        if(!$result) {
            // throw error
            $this->error(__("Image selection has not been saved.", WYSIJA));
        } else {
            // save successful
            $this->notice(__("Image selection has been saved.", WYSIJA));
        }

        return array('result' => $result);
    }


    function view_NL() {
        // get campaign id
        $campaign_id = (int)$_REQUEST['id'];

        // update data in DB
        $modelEmail =& WYSIJA::get('email', 'model');
        $result = $modelEmail->getOne(false,array('campaign_id' => $campaign_id));

        echo $result['body'];
        exit;
    }

    function display_NL() {
        // get campaign id
        $campaign_id = (int)$_REQUEST['id'];

        // update data in DB
        $modelEmail =& WYSIJA::get('email', 'model');
        $result = $modelEmail->getOne(false,array('campaign_id' => $campaign_id));

        //echo $result['body'];

        $wjEngine =& WYSIJA::get('wj_engine', 'helper');
        $wjEngine->setStyles($result['wj_styles'], true);
        $wjEngine->setData($result['wj_data'], true);

        $html = $wjEngine->renderEmail($result['subject']);
        print $html;
        exit;
    }

    function getarticles(){
        // fixes issue with pcre functions
        @ini_set('pcre.backtrack_limit', 1000000);
        
        $model=&WYSIJA::get("user","model");
        global $wpdb;
        $modelConfig=&WYSIJA::get("config","model");
        $fullarticlepref=$modelConfig->getValue("editor_fullarticle");
        /* test to set the default value*/
        if(!$fullarticlepref && isset($_REQUEST['fullarticle'])){
            
            $modelConfig->save(array("editor_fullarticle"=>true));
        }

        if($fullarticlepref && !isset($_REQUEST['fullarticle'])){
            
            $modelConfig->save(array("editor_fullarticle"=>false));
        }
        

        if(isset($_REQUEST['search'])){
            $querystr = "SELECT $wpdb->posts.ID , $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_excerpt
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_title like '%".addcslashes(mysql_real_escape_string($_REQUEST['search'],$wpdb->dbh), '%_' )."%' 
            AND $wpdb->posts.post_status = 'publish' 
            AND $wpdb->posts.post_type = 'post'
            ORDER BY $wpdb->posts.post_date DESC
            LIMIT 0,30";
        }else{
            $querystr = "SELECT $wpdb->posts.ID , $wpdb->posts.post_title, $wpdb->posts.post_content, $wpdb->posts.post_excerpt
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_status = 'publish' 
            AND $wpdb->posts.post_type = 'post'
            ORDER BY $wpdb->posts.post_date DESC
            LIMIT 0,10";
        }

        $res=array();
        $res['posts']=$model->query("get_res",$querystr);

        $helper_engine=&WYSIJA::get("wj_engine","helper");

        if($res['posts']){
            $res['result'] = true;
            foreach($res['posts'] as $k =>$v){
                if(!function_exists('has_post_thumbnail'))    require_once(ABSPATH . WPINC . '/post-thumbnail-template.php');
                if(has_post_thumbnail( $v['ID'] )){

                    $postthumb=get_post_thumbnail_id( $v['ID'] );
                    $image = wp_get_attachment_image_src($postthumb , 'single-post-thumbnail' );
                }else $image=false;

                /* get the featured image and if there is no featured image get the first image in the post */
                if(has_post_thumbnail( $v['ID'] )){

                    $postthumb=get_post_thumbnail_id( $v['ID'] );
                    $image = wp_get_attachment_image_src($postthumb , 'single-post-thumbnail' );
                }else $image=false;


                //htmlentities fucks up the accents so we use str_replace instead
                $res['posts'][$k]['post_title']=  str_replace(array("<",">"),array("&lt;","&gt;"),$res['posts'][$k]['post_title']);
                if($image){
                    $res['posts'][$k]['post_firstimage']["src"] = $image[0];
                    $res['posts'][$k]['post_firstimage']["width"]=$image[1];
                    $res['posts'][$k]['post_firstimage']["height"]=$image[2];

                }else{
                    $matches=$matches2=array(); 

                    $output = preg_match_all(
                            '/<img.+src=['."'".'"]([^'."'".'"]+)['."'".'"].*>/i', 
                            $v['post_content'], 
                            $matches);

                    if(isset($matches[0][0])){
                        preg_match_all('/(src|height|width|)="([^"]*)"/i',$matches[0][0], $matches2);

                        if(isset($matches2[1])){
                           foreach($matches2[1] as $k2 =>$v2){
                                if(in_array($v2, array("src","width","height"))){
                                    $res['posts'][$k]['post_firstimage'][$v2]=$matches2[2][$k2];
                                }
                            } 
                        }else{
                            $res['posts'][$k]['post_firstimage']=null;
                        }
                    }else{
                        $res['posts'][$k]['post_firstimage']=null;
                    }


                }

                if(isset($res['posts'][$k]['post_firstimage']["src"])){
                    $res['posts'][$k]['post_firstimage']["alignment"]="left";
                    $res['posts'][$k]['post_firstimage']["url"]=  get_permalink($v['ID']);
                }else{
                    $res['posts'][$k]['post_firstimage']=null;
                }
                

                /* if excerpt has been requested then we try to provide it */
                if(!isset($_REQUEST['fullarticle'])){
                    //check first the excerpt field
                    if($res['posts'][$k]['post_excerpt']){
                        $res['posts'][$k]['post_content']=$res['posts'][$k]['post_excerpt'];
                    }else{
                        //check then the more tag
                        $arrayexcerpts=explode("<!--more-->",$res['posts'][$k]['post_content']);

                        if(count($arrayexcerpts)>1){
                            $res['posts'][$k]['post_content']=$arrayexcerpts[0];
                        }else{
                            //finally get a made up excerpt if ther is no other choice
                            $helperToolbox=&WYSIJA::get("toolbox","helper");
                            $res['posts'][$k]['post_content']=$helperToolbox->excerpt($res['posts'][$k]['post_content'],60);
                        }
                        
                        
                    }
                    
                }
                unset($res['posts'][$k]['post_excerpt']);
                // convert new lines into <p>
                $content = wpautop($res['posts'][$k]['post_content'], false);

                // remove images
                $content = preg_replace('/<img[^>]+./','', $content);

                // remove shortcodes
                $content = preg_replace('/\[.*\]/', '', $content);

                // remove wysija nl shortcode
                $content= preg_replace('/\<div class="wysija-register">(.*?)\<\/div>/','',$content);

                // convert embedded content if necessary
                $content = $this->convertEmbeddedContent($content);

                // convert h4 h5 h6 to h3
                $content = preg_replace('/<([\/])?h[456](.*?)>/', '<$1h3$2>', $content);
                
                // convert ol to ul
                $content = preg_replace('/<([\/])?ol(.*?)>/', '<$1ul$2>', $content);

                // strip useless tags
                $content = strip_tags($content, '<p><em><b><strong><i><h1><h2><h3><a><ul><ol><li>');

                // set post title if present
                if(strlen(trim($res['posts'][$k]['post_title'])) > 0) {
                    $content = '<h1>'.  $res['posts'][$k]['post_title'].'</h1>'.$content;
                }

                // add read online link
                $content .= '<p><a href="'.get_permalink($v['ID']).'">'.__('Read online.', WYSIJA).'</a></p>';

                $block = array(
                  'position' => 1,
                  'type' => 'content',
                  'text' => array(
                      'value' => $content
                  ),
                  'image' => $res['posts'][$k]['post_firstimage'],
                  'alignment' => 'left'
                );
                unset($res['posts'][$k]['post_content']);

                $res['posts'][$k]['html']=base64_encode($helper_engine->renderEditorBlock($block));

            }

        }else {

            $res['msg'] = __("There are no posts corresponding to that search.",WYSIJA);
            $res['result'] = false;
        }


        return $res;
    }
    
    function convertEmbeddedContent($content = '') {
        // remove embedded video and replace with links
        $content = preg_replace('#<iframe.*?src=\"(.+?)\".*><\/iframe>#', '<a href="$1">'.__('Click here to view media.', WYSIJA).'</a>', $content);
        
        // replace youtube links
        $content = preg_replace('#http://www.youtube.com/embed/([a-zA-Z0-9_-]*)#Ui', 'http://www.youtube.com/watch?v=$1', $content);
        
        return $content;
    }

    function send_preview($showcase=false){
        $mailer=&WYSIJA::get("mailer","helper");
        $campaign_id = $_REQUEST['campaignID'];

        // update data in DB
        $modelEmail =& WYSIJA::get('email', 'model');
        $modelEmail->getFormat=OBJECT;
        $emailObject = $modelEmail->getOne(false,array('campaign_id' => $campaign_id));
        $mailer->testemail=true;
        
        
        if(isset($_REQUEST['data'])){
           $dataTemp=$_REQUEST['data'];
            $_REQUEST['data']=array();
            foreach($dataTemp as $val) $_REQUEST['data'][$val["name"]]=$val["value"];
            $dataTemp=null;
            foreach($_REQUEST['data'] as $k =>$v){
                $newkey=str_replace(array("wysija[email][","]"),"",$k);
                $configVal[$newkey]=$v;
            }   
            if(isset($configVal['from_name'])){
                $params=array(
                    'from_name'=>$configVal['from_name'],
                    'from_email'=>$configVal['from_email'],
                    'replyto_name'=>$configVal['replyto_name'],
                    'replyto_email'=>$configVal['replyto_email']);
                if(isset($configVal['subject']))    $emailObject->subject=$configVal['subject'];
            }
            
        }else{
            $params=array(
                'from_name'=>$emailObject->from_name,
                'from_email'=>$emailObject->from_email,
                'replyto_name'=>$emailObject->replyto_name,
                'replyto_email'=>$emailObject->replyto_email
            );
        }

        $receivers=explode(',',$_REQUEST['receiver']);
        
        if($showcase){
            $emailObject->subject="[Wysija Showcase from : ".get_site_url()." ] ".$emailObject->subject;
            $successmsg=__("You have sent us your newsletter successfully. Thanks! We'll be in touch if we feature your design.", WYSIJA);
        }else{
            $successmsg=__('Your email preview has been sent to %1$s', WYSIJA);
        }
        if(isset($emailObject->params)) {
            $params['params']=$emailObject->params;

            if(isset($configVal['params[googletrackingcode'])){
                
                $paramsemail=unserialize(base64_decode($emailObject->params));

                if(trim($configVal['params[googletrackingcode'])) {
                    $paramsemail['googletrackingcode']=$configVal['params[googletrackingcode'];
                }
                else {
                    unset($paramsemail['googletrackingcode']);
                }
                $params['params']=base64_encode(serialize($paramsemail));
            }

        }
        foreach($receivers as $receiver){
            $res=$mailer->sendSimple($receiver,$emailObject->subject,$emailObject->body,$params);
            if($res)    $this->notice(sprintf($successmsg,$_REQUEST['receiver']));
        }

        return array('result' => $res);
    }
    
    function send_showcase(){
       $_REQUEST['receiver']="team@wysija.com";
        return $this->send_preview(true);
    }
    
    function set_divider()
    {
        $src = isset($_POST['wysijaData']['src']) ? $_POST['wysijaData']['src'] : NULL;
        $width = isset($_POST['wysijaData']['width']) ? (int)$_POST['wysijaData']['width'] : NULL;
        $height = isset($_POST['wysijaData']['height']) ? (int)$_POST['wysijaData']['height'] : NULL;
        
        if($src === NULL OR $width === NULL OR $height === NULL) {
            // there is a least one missing parameter, fallback to default divider
            $dividersHelper =& WYSIJA::get('dividers', 'helper');
            $divider = $dividersHelper->getDefault();
        } else {
            // use provided params
            $divider = array(
                'src' => $src,
                'width' => $width,
                'height' => $height
            );
        }
        
        // update campaign parameters
        $campaign_id = (int)$_REQUEST['campaignID'];
        $campaignsHelper =& WYSIJA::get('campaigns', 'helper');
        $campaignsHelper->saveParameters($campaign_id, 'divider', $divider);

        // set params
        $block = array_merge(array('no-block' => true, 'type' => 'divider'), $divider);
        
        $helper_engine=&WYSIJA::get("wj_engine","helper");
        return base64_encode($helper_engine->renderEditorBlock($block));
    }
    
    function get_social_bookmarks() {
        $size = isset($_POST['wysijaData']['size']) ? $_POST['wysijaData']['size'] : NULL;
        $bookmarksHelper =& WYSIJA::get('bookmarks', 'helper');
        $bookmarks = $bookmarksHelper->getAll($size);
        
        return json_encode(array('icons' => $bookmarks));
    }
    
    function generate_social_bookmarks() {
        
        $size = 'medium';
        $iconset = '01';
        
        if(isset($_POST['wysijaData']) && !empty($_POST['wysijaData'])) {
            $data = $_POST['wysijaData'];
            $items = array();
            
            foreach($data as $key => $values) {
                if($values['name'] === 'bookmarks-size') {
                    // get size
                    $size = $values['value'];
                } else if($values['name'] === 'bookmarks-iconset') {
                    // get iconset
                    $iconset = $values['value'];
                    if(strlen(trim($iconset)) === 0) {
                        $this->error('No iconset specified', false);
                        return false;
                    }
                } else {
                    $keys = explode('-', $values['name']);
                    $network = $keys[1];
                    $property = $keys[2];
                    if(array_key_exists($network, $items)) {
                        $items[$network][$property] = $values['value'];
                    } else {
                        $items[$network] = array($property => $values['value']);
                    }
                }
            }
        }

        $urls = array();
        // check data and remove network with an empty url
        foreach($items as $network => $item) {
            if(strlen(trim($item['url'])) === 0) {
                // empty url
                unset($items[$network]);
            } else {
                // url specified
                $urls[$network] = $item['url'];
            }
        }
        
        // check if there's at least one url left
        if(empty($urls)) {
            $this->error('No url specified', false);
            return false;
        }
        
        // save url in config
        $config=&WYSIJA::get('config',"model");
        $config->save(array('social_bookmarks' => $urls));
        
        // get iconset icons 
        $bookmarksHelper =& WYSIJA::get('bookmarks', 'helper');
        $icons = $bookmarksHelper->getAllByIconset($size, $iconset);
        
        // format data
        $block = array(
            'position' => 1,
            'type' => 'gallery',
            'items' => array(),
            'alignment' => 'center'
        );

        $width = 0;
        foreach($items as $key => $item) {
            $block['items'][] = array_merge($item, $icons[$key], array('alt' => ucfirst($key)));
            $width += (int)$icons[$key]['width'];
        }
        // add margin between icons
        $width += (count($block['items']) - 1) * 10;
        // set optimal width
        $block['width'] = max(0, min($width, 564));
        
        $helper_engine=&WYSIJA::get("wj_engine","helper");
        return base64_encode($helper_engine->renderEditorBlock($block));
    }
    
    function install_theme() {
        if( isset($_REQUEST['theme_id'])){
            
            
            //check if theme is premium if you have the premium licence
            if(isset($_REQUEST['premium']) && $_REQUEST['premium']){
                $modelC=&WYSIJA::get("config","model");
                if(!$modelC->getValue("premium_val")){
                    $wjEngine =& WYSIJA::get('wj_engine', 'helper');
                    $themes = $wjEngine->renderThemes();
                    
                    $helperLicence=&WYSIJA::get("licence","helper");
                    $urlpremium="http://www.wysija.com/?wysijap=checkout&wysijashop-page=1&testprod=1&controller=orders&action=checkout&popformat=1&wysijadomain=".$helperLicence->getDomainInfo();
                    
                    $errormsg=str_replace(array('[link]','[/link]'),
                    array('<a title="'.__('Get Premium now',WYSIJA).'" class="premium-tab" href="'.$urlpremium.'" >','</a>'),
                            __("Theme is available in premium version only. [link]Get Premium now![/link]",WYSIJA));
                    $this->error($errormsg,1);
                    
                    return array("result"=>false, 'themes' => $themes);
                }
            }
            
            //check if theme already exists on this server
            /* $helperF=&WYSIJA::get('file',"helper");
             * $filename=$helperF->exists("templates".DS.$_REQUEST['theme_key']);
            if($filename['result']){
                $this->error(sprintf(__('Theme already exists on the server.(%1$s)',WYSIJA),$filename['file']),1);
                return array('result'=>false);
            }*/
            
            $httpHelp=&WYSIJA::get("http","helper");
            $url=admin_url('admin.php');
            
            $helperToolbox=&WYSIJA::get("toolbox","helper");
            $domain_name=$helperToolbox->_make_domain_name($url);
            
            $request="http://api.wysija.com/download/zip/".$_REQUEST['theme_id']."?domain=".$domain_name;
            $ZipfileResult = $httpHelp->request($request);

            if(!$ZipfileResult){
                $result=false;
                $this->error(__("We were unable to contact the API, the site may be down. Please try again later.",WYSIJA),true);
            }else{
                $themesHelp=&WYSIJA::get("themes","helper");
                $result = $themesHelp->installTheme($ZipfileResult);
                
                // refresh themes list
                $wjEngine =& WYSIJA::get('wj_engine', 'helper');
                $themes = $wjEngine->renderThemes();
            }
        }else{
            $result=false;
            $this->notice("missing info");
        }
        
        return array("result"=>$result, 'themes' => $themes);
    }
}