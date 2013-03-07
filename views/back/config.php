<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_view_back_config extends WYSIJA_view_back{

    var $title='Settings';
    var $icon='icon-options-general';
    var $skip_header = true;


    function reinstall(){
        ?>
        <form name="wysija-settings" method="post" id="wysija-settings" action="" class="form-valid" autocomplete="off">
            <input type="hidden" value="doreinstall" name="action"/>
            <input type="hidden" value="reinstall" name="postedfrom"/>
            <h3><?php _e('If you confirm this, all your current Wysija data will be erased (newsletters, themes, statistics, lists, subscribers, etc.)',WYSIJA); ?></h3>
            <p class="submit">
                <input type="submit" value="<?php _e('Confirm Reinstallation',WYSIJA)?>" class="button-secondary" id="submit" name="submit" />
                <?php $this->secure(array('action'=>'doreinstall')); ?>
            </p>
        </form>
        <?php
    }

    function fieldFormHTML_commentform($key,$value,$model,$paramsex){
        /*second part concerning the checkbox*/
        $formsHelp=&WYSIJA::get('forms','helper');
        $checked=false;
        if($this->model->getValue($key))   $checked=true;
        $checkboxDetails=array('id'=>$key,'name'=>'wysija['.$model.']['.$key.']','class'=>'activateInput');
        $contentAfter='';

        //if it's the commentform field and jetpacks is activated with its comment module then we disable the box
        if($key=='commentform' && WYSIJA::is_plugin_active('jetpack/jetpack.php') && in_array( 'comments', Jetpack::get_active_modules() )){
            $checkboxDetails['disabled']='disabled';
            $contentAfter='<p>'.__('This feature cannot work because the "Comments" feature of the plugin JetPack is enabled.',WYSIJA).'</p>';
        }

        //if it's the register form field and registration is not allowed on the site then we just disable it
        $active_signup=false;
        if(is_multisite()){
           $active_signup = get_site_option( 'registration' );
            if ( !$active_signup )
                    $active_signup = 'all';

            $active_signup = apply_filters( 'wpmu_active_signup', $active_signup );
            if(in_array($active_signup, array('none','blog'))) $active_signup=false;
            else $active_signup=true;
        }else $active_signup=get_option('users_can_register');

        if($key=='registerform' && !$active_signup){
            $checkboxDetails['disabled']='disabled';

            $contentAfter='<p>'.__('Registration is disabled on this site.',WYSIJA).'</p>';
        }

        $fieldHTML='<p style="float:left;"><label for="'.$key.'">';
        $fieldHTML.=$formsHelp->checkbox($checkboxDetails,1,$checked);
        $fieldHTML.='</label>';

        $value=$this->model->getValue($key.'_linkname');

        $fieldHTML.='<div id="'.$key.'_linkname'.'" class="linknamecboxes">';

        if($contentAfter){
            $fieldHTML.='</div>';
            $fieldHTML.=$contentAfter;
        }else{
            $fieldHTML.=$formsHelp->input(array('name'=>'wysija['.$model.']['.$key.'_linkname]', 'size'=>'75'),$value).'</p>';
            $modelList=&WYSIJA::get('list','model');
            $lists=$modelList->get(array('name','list_id'),array('is_enabled'=>1));
            $valuefield=$this->model->getValue($key.'_lists');
            if(!$valuefield) $valuefield=array();
            foreach($lists as $list){
                if(in_array($list['list_id'], $valuefield)) $checked=true;
                else $checked=false;

                $fieldHTML.= '<p class="labelcheck"><label for="list-'.$list['list_id'].'">'.$formsHelp->checkbox( array('id'=>'list-'.$list['list_id'],
                            'name'=>'wysija[config]['.$key.'_lists][]', 'class'=>'validate[minCheckbox[1]]'),
                                $list['list_id'],$checked).$list['name'].'</label></p>';
            }
            $fieldHTML.='</div>';
        }


        return $fieldHTML;
    }

    function fieldFormHTML_managesubscribe($key,$value,$model,$paramsex){
        /*second part concerning the checkbox*/
        $formsHelp=&WYSIJA::get('forms','helper');
        $checked=false;
        if($this->model->getValue($key))   $checked=true;
        $fieldHTML='<p style="float:left;"><label for="'.$key.'">';
        $fieldHTML.=$formsHelp->checkbox(array('id'=>$key,'name'=>'wysija['.$model.']['.$key.']','class'=>'activateInput'),1,$checked);
        $fieldHTML.='</label>';
        $value=$this->model->getValue($key.'_linkname');

        $fieldHTML.='<div id="'.$key.'_linkname'.'" class="linknamecboxes">';
        $fieldHTML.=$formsHelp->input(array('name'=>'wysija['.$model.']['.$key.'_linkname]', 'size'=>'75'),$value).'</p>';
        $fieldHTML.='<p style="margin-bottom:0px;">'.__('Subscribers can choose from these lists :',WYSIJA).'</p>';
        $modelList=&WYSIJA::get('list','model');
        $lists=$modelList->get(array('name','list_id','is_public'),array('is_enabled'=>1));


        foreach($lists as $list){
            if($list['is_public']) $checked=true;
            else $checked=false;

            $fieldHTML.= '<p class="labelcheck"><label for="'.$key.'list-'.$list['list_id'].'">'.$formsHelp->checkbox( array('id'=>$key.'list-'.$list['list_id'],
                        'name'=>'wysija[config]['.$key.'_lists][]'),
                            $list['list_id'],$checked).$list['name'].'</label></p>';
        }
        $fieldHTML.='</div>';


        return $fieldHTML;
    }

    function fieldFormHTML_viewinbrowser($key,$value,$model,$paramsex){
        /*second part concerning the checkbox*/
        $formsHelp=&WYSIJA::get('forms','helper');
        $checked=false;
        if($this->model->getValue($key))   $checked=true;
        $field='<p><label for="'.$key.'">';
        $field.=$formsHelp->checkbox(array('id'=>$key,'name'=>'wysija['.$model.']['.$key.']','class'=>'activateInput'),1,$checked);
        $field.='</label>';
        $value=$this->model->getValue($key.'_linkname');

        $field.=$formsHelp->input(array("id"=>$key.'_linkname','name'=>'wysija['.$model.']['.$key.'_linkname]', 'size'=>'75'),$value).'</p>';

        return $field;
    }

    function fieldFormHTML_cron($key,$value,$model,$paramsex){
        //second part concerning the checkbox
        $formsHelp=&WYSIJA::get('forms','helper');
        $checked=false;
        if($this->model->getValue($key))   $checked=true;
        $field='<div><div class="cronleft"><label for="'.$key.'">';
        $field.=$formsHelp->checkbox(array('id'=>$key,'name'=>'wysija['.$model.']['.$key.']','class'=>'activateInput'),1,$checked);
        $field.='</label></div>';


        $checked=false;
        if($this->model->getValue('cron_page_hit_trigger'))   $checked=true;

        $urlcron=site_url( 'wp-cron.php').'?'.WYSIJA_CRON.'&action=wysija_cron&process=all';
        $field.='<div class="cronright" id="'.$key.'_linkname">';
        $field.='<p>'.'Almost done! Setup this cron job on your server or ask your host:'.'</p>';
        $field.='<p>Cron URL : <strong><a href="'.$urlcron.'" target="_blank">'.$urlcron.'</a></strong></p>';//cron_page_hit_trigger
        $field.='<p>'.$formsHelp->checkbox(array('id'=>'cron_page_hit_trigger','name'=>'wysija['.$model.'][cron_page_hit_trigger]','class'=>'activateInput'),1,$checked).'Scheduled tasks are triggerred by any "page view" frontend/backend/logged in users or visitors.</p>';
        $field.='</div></div>';

        return $field;
    }

    function fieldFormHTML_debugnew($key,$value,$model,$paramsex){
        /*second part concerning the checkbox*/
        $formsHelp=&WYSIJA::get('forms','helper');
        $selected=$this->model->getValue($key);
        if(!$selected)   $selected=0;
        $field='<p><label for="'.$key.'">';
        $options=array(0=>'off',1=>'SQL queries',2=>'&nbsp+extra data',3=>'&nbsp&nbsp+safe PHP errors');
        $field.=$formsHelp->dropdown(array('id'=>$key,'name'=>'wysija['.$model.']['.$key.']'),$options,$selected);
        $field.='</label></p>';

        return $field;
    }

    function fieldFormHTML_debuglog($key,$value,$model,$paramsex){
        /*second part concerning the checkbox*/
        $formsHelp=&WYSIJA::get('forms','helper');

        $lists=array('cron','post_notif','query_errors','queue_process','manual');

        $fieldHTML='<div id="'.$key.'_linkname'.'" class="linknamecboxes">';
        foreach($lists as $list){
            $checked=false;
            if($this->model->getValue($key.'_'.$list)) $checked=true;

            $fieldHTML.= '<p class="labelcheck"><label for="'.$key.'list-'.$list.'">'.$formsHelp->checkbox( array('id'=>$key.'list-'.$list,
                        'name'=>'wysija[config]['.$key.'_'.$list.'][]'),
                            1,$checked).$list.'</label></p>';
        }
        $fieldHTML.='</div>';

        return $fieldHTML;
    }

    function fieldFormHTML_dkim($key,$value,$model,$paramsex){

        $field='';
        $keypublickey=$key.'_pubk';

        if(!$this->model->getValue($keypublickey)){
            //refresh the public key private key generation
            $helpersLi=&WYSIJA::get('licence','helper');
            $helpersLi->dkim_config();
        }else{
            WYSIJA::update_option('dkim_autosetup',false);
            $formsHelp=&WYSIJA::get('forms','helper');


            $realkey=$key.'_active';
            $checked=false;
            if($this->model->getValue($realkey))   $checked=true;

            $field.='<p>';
            $field.=$formsHelp->checkbox(array('id'=>$realkey,'name'=>'wysija['.$model.']['.$realkey.']','style'=>'margin-left:0px;','class'=>'activateInput'),1,$checked);
            $field.='</p>';

            $field.='<div id="'.$realkey.'_linkname" >';
            //$titlelink=str_replace(array('[link]','[\link]'), array('<a href="">','</a>'),'');
            $titlelink= __('Configure your DNS by adding a key/value record in TXT as shown below.',WYSIJA).' <a href="http://support.wysija.com/knowledgebase/guide-to-dkim-in-wysija/?utm_source=wpadmin&utm_campaign=settings" target="_blank">'.__('Read more',WYSIJA).'</a>';
            $field.='<fieldset style=" border: 1px solid #ccc;margin: 0;padding: 10px;"><legend>'.$titlelink.'</legend>';

            $field.='<label id="drlab" for="domainrecord">'.__('Key',WYSIJA).' <input readonly="readonly" id="domainrecord" style="margin-right:10px;" type="text" value="wys._domainkey"/></label><label id="drpub" for="dkimpub">'.__('Value',WYSIJA).' <input readonly="readonly" id="dkimpub" type="text" size="70" value="v=DKIM1;k=rsa;g=*;s=email;h=sha1;t=s;p='.$this->model->getValue($keypublickey).'"/></label>';

            //the DKIM key is not a 1024 bits it is therefore obsolete
            if(!$this->model->getValue('dkim_1024')){
                $stringRegenerate= __('You\'re using an older DKIM key which is unsupported by Gmail.',WYSIJA).' '. __('You\'ll need to update your DNS if you upgrade.',WYSIJA);
                $field.='<p><strong>'.$stringRegenerate.'</strong></p>';
                $field.='<p><input type="hidden" id="dkim_regenerate" value="0" name="wysija[config][dkim_regenerate]"><a id="button-regenerate-dkim" class="button-secondary" href="javascript:;">'.__('Upgrade DKIM key',WYSIJA).'</a></p>';
            }

            $field.='</fieldset>';
            $realkey=$key.'_domain';
            $field.='<p><label class="dkim" for="'.$realkey.'">'.__('Domain',WYSIJA).'</label>';

            $field.=$formsHelp->input(array('id'=>$realkey,'name'=>'wysija['.$model.']['.$realkey.']'),$this->model->getValue($realkey));
            $field.='</p>';

            $field.='</div>';
        }

        return $field;
    }

    function fieldFormHTML_debug($key,$value,$model,$paramsex){
        /*second part concerning the checkbox*/
        $formsHelp=&WYSIJA::get('forms','helper');
        $checked=false;
        if($this->model->getValue($key))   $checked=true;
        $field='<p><label for="'.$key.'">';
        $field.=$formsHelp->checkbox(array('id'=>$key,'name'=>'wysija['.$model.']['.$key.']'),1,$checked);
        $field.='</label></p>';

        return $field;
    }

    function fieldFormHTML_capabilities($key,$value,$model,$paramsex){
        /*second part concerning the checkbox*/
        $formsHelp=&WYSIJA::get('forms','helper');

        $field='<table width="400" cellspacing="0" cellpadding="3" bordercolor="#FFFFFF" border="0" style="background-color:#FFFFFF" class="fixed">
    <thead>
        <tr>
<th class="rolestitle" style="width:200px">'.__('Roles and permissions',WYSIJA).'</th>';

        $wptools=&WYSIJA::get('wp_tools','helper');
        $editable_roles=$wptools->wp_get_roles();


        foreach($editable_roles as $role){
            $field.='<th class="rolestable" >'.$role['name'].'</th>';
        }

	$field.='</tr></thead><tbody>';

        $alternate=true;
        foreach($this->model->capabilities as $keycap=>$capability){
            $classAlternate='';
            if($alternate) $classAlternate=' class="alternate" ';
            $field.='<tr'.$classAlternate.'><td class="title"><p class="description">'.$capability['label'].'</p></td>';

                    foreach($editable_roles as $role){
                        $checked=false;
                        $keycheck='rolescap---'.$role['key'].'---'.$keycap;

                        //if($this->model->getValue($keycheck))   $checked=true;
                        $checkboxparams=array('id'=>$keycheck,'name'=>'wysija['.$model.']['.$keycheck.']');
                        if(in_array($role['key'], array('administrator','super_admin'))){
                            $checkboxparams['disabled']='disabled';
                        }

                        $roling = get_role( $role['key'] );

                        // add "organize_gallery" to this role object
                        if($roling->has_cap( 'wysija_'.$keycap )){
                            $checked=true;
                        }

                        $field.='<td class="rolestable" >'.$formsHelp->checkbox($checkboxparams,1,$checked).'</td>';
                    }

            $field.='</tr>';
            $alternate=!$alternate;
        }

        $field.='</tbody></table>';

        return $field;
    }



    function fieldFormHTML_email_notifications($key,$value,$model,$paramsex){
        /* first part concerning the field itself */
        $params=array();
        $params['type']='default';
        $field=$this->fieldHTML($key,$value,$model,$params);

        /*second part concerning the checkbox*/
        $threecheck=array(
            '_when_sub' =>__('When someone subscribes',WYSIJA)
            ,'_when_unsub'=>__('When someone unsubscribes',WYSIJA),
            '_when_dailysummary'=>__('Daily summary of emails sent',WYSIJA)
            //,"_when_bounce"=>__('When an email bounces',WYSIJA)
            );
        $formsHelp=&WYSIJA::get('forms','helper');
        foreach($threecheck as $keycheck => $checkobj){
            $checked=false;
            if($this->model->getValue($key.$keycheck))$checked=true;
            $field.='<p><label for="'.$key.$keycheck.'">';
            $field.=$formsHelp->checkbox(array("id"=>$key.$keycheck,'name'=>'wysija['.$model.']['.$key.$keycheck.']'),1,$checked);
            $field.=$checkobj.'</label></p>';
        }

        return $field;
    }


    function fieldFormHTML_selfsigned($key,$value,$model,$params){

        $formsHelp=&WYSIJA::get('forms','helper');

        $realvalue=$this->model->getValue($key);

        $value=0;
        $checked=false;
        if($value ==$realvalue) $checked=true;
        $id=str_replace('_','-',$key).'-'.$value;
        $field='<label for="'.$id.'">';
        $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija['.$model.']['.$key.']'),$value,$checked);
        $field.=__('No',WYSIJA).'</label>';

        $value=1;
        $checked=false;
        if($value ==$realvalue) $checked=true;
        $id=str_replace('_','-',$key).'-'.$value;
        $field.='<label for="'.$id.'">';
        $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija['.$model.']['.$key.']'),$value,$checked);
        $field.=__('Yes',WYSIJA).'</label>';

        return $field;
    }

    function tabs($current = 'basics') {
        $tabs = array(
            'basics' => __('Basics', WYSIJA),
            'subforms' => __('Subscription Form', WYSIJAFUTURE),
            'emailactiv' => __('Signup Confirmation', WYSIJA),
            'sendingmethod' => __('Send With...', WYSIJA),
            'advanced' => __('Advanced', WYSIJA),
            'premium' => __('Premium Upgrade', WYSIJA),
        );

        if(!$this->_user_can('change_sending_method')) unset($tabs['sendingmethod']);

        if(!WYSIJA::is_wysija_admin()) unset($tabs['subforms']);

        $tabs=apply_filters('wysija_extend_settings', $tabs);

        echo '<div id="icon-options-general" class="icon32"><br /></div>';
        echo '<h2 id="wysija-tabs" class="nav-tab-wrapper">';
        foreach($tabs as $tab => $name) {
            $class = ($tab === $current) ? ' nav-tab-active' : '';
            $extra = ($tab === 'premium') ? ' tab-premium' : '';
            echo "<a class='nav-tab$class$extra' href='#$tab'>$name</a>";
        }
        echo '</h2>';
    }


    function save(){
        $this->main();
    }

    /**
     *
     * @param string $action
     * @return boolean
     */
    function _user_can($action){
        if(empty($action)) return false;
        $is_network_admin=WYSIJA::current_user_can('manage_network');

        //$is_network_admin=true;//PROD comment that line
        if($is_network_admin) return true;

        $is_multisite=is_multisite();

        //$is_multisite=true;//PROD comment that line
        switch($action){
            case 'change_sending_method':
                if((!$is_multisite
                   || ($is_multisite && $this->model->getValue('ms_allow_admin_sending_method'))) && WYSIJA::current_user_can('switch_themes')
                           ){
                    return true;
                }
                return false;
                break;
            case 'toggle_signup_confirmation':
                if((!$is_multisite
                   || ($is_multisite && $this->model->getValue('ms_allow_admin_toggle_signup_confirmation'))) && WYSIJA::current_user_can('switch_themes')){
                    return true;
                }
                return false;
                break;
        }

    }

    function main(){

        $is_multisite=is_multisite();
        $is_network_admin=WYSIJA::current_user_can('manage_network');
        //$is_network_admin=$is_multisite=true;//PROD comment that line

        if($is_multisite && $is_network_admin) {
            add_filter('wysija_extend_settings',array($this,'ms_tab_name'),12);
            add_filter('wysija_extend_settings_content',array($this,'ms_tab_content'),12,2);
        }
        // check for debug
        if(isset($_REQUEST['wysija_debug'])) {
            switch((int)$_REQUEST['wysija_debug']) {
                // turn off debug
                case 0:
                    WYSIJA::update_option('debug_on', false);
                    WYSIJA::update_option('debug_new', false);
                break;

                // turn on debug (with debug level as value)
                case 1:
                case 2:
                case 3:
                case 4:
                case 99:
                    WYSIJA::update_option('debug_on', true);
                    WYSIJA::update_option('debug_new', (int)$_REQUEST['wysija_debug']);
                break;
            }
        }

        echo $this->messages();
        ?>
        <div id="wysija-config">
            <?php $this->tabs(); ?>
            <form name="wysija-settings" method="post" id="wysija-settings" action="" class="form-valid" autocomplete="off">
                <div id="basics" class="wysija-panel">
                    <?php $this->basics(); ?>
                    <p class="submit">
                    <input type="submit" value="<?php echo esc_attr(__('Save settings',WYSIJA)); ?>" class="button-primary wysija" />
                    </p>
                </div>
                <div id="subforms" class="wysija-panel">
                    <?php if(WYSIJA::is_wysija_admin()) $this->subforms(); ?>
                </div>

                    <div id="emailactiv" class="wysija-panel">
                        <?php $this->emailactiv(); ?>
                        <p class="submit">
                        <input type="submit" value="<?php echo esc_attr(__('Save settings',WYSIJA)); ?>" class="button-primary wysija" />
                        </p>
                    </div>
                <?php

                if($this->_user_can('change_sending_method')){
                    ?>
                    <div id="sendingmethod" class="wysija-panel">
                        <?php $this->sendingmethod(); ?>
                        <p class="submit">
                        <input type="submit" value="<?php echo esc_attr(__('Save settings',WYSIJA)); ?>" class="button-primary wysija" />
                        </p>
                    </div>
                    <?php
                }
                ?>


                <div id="advanced" class="wysija-panel">
                    <?php $this->advanced(); ?>
                    <p class="submit">
                    <input type="submit" value="<?php echo esc_attr(__('Save settings',WYSIJA)); ?>" class="button-primary wysija" />
                    </p>
                </div>

                <?php
                add_filter('wysija_extend_settings_content',array($this,'extend_settings_premium'),8,2);

                echo apply_filters('wysija_extend_settings_content','',array('viewObj'=>&$this));
                ?>

                <p class="submitee">
                    <?php $this->secure(array('action'=>"save")); ?>
                    <input type="hidden" value="save" name="action" />
                    <input type="hidden" value="" name="redirecttab" id="redirecttab" />
                </p>

            </form>
        </div>
        <?php
    }


    function basics(){
        $step=array();

        $step['company_address']=array(
            'type'=>'textarea',
            'label'=>__("Your company's address",WYSIJA),
            'desc'=>__("The address will be added to your newsletter's footer. This helps avoid spam filters.",WYSIJA),
            'rows'=>"3",
            'cols'=>"40",);

        $step['emails_notified']=array(
            'type'=>'email_notifications',
            'label'=>__('Email notifications',WYSIJA),
            'desc'=>__('Enter the email addresses that should receive notifications (separate by comma).',WYSIJA));

        $step['from_name']=array(
            'type'=>'fromname',
            'class'=>'validate[required]',
            'label'=>__('Sender of notifications',WYSIJA),
            'desc'=>__('Choose a FROM name and email address for notifications emails.',WYSIJA));


        $modelC=&WYSIJA::get('config','model');

        ?>
        <table class="form-table">
            <tbody>
                <?php
                echo $this->buildMyForm($step,$modelC->values,'config');
                ?>
            </tbody>
        </table>
        <?php
    }

    function subforms() {
        $mUserField =& WYSIJA::get('user_field','model');
        $mUserField->orderBy('field_id');
        $customFields = $mUserField->getRows(false);

        // get form editor rendering engine
        $hFormEngine =& WYSIJA::get('wj_form_engine', 'helper');

        ?>
        <a id="wj-create-new-form" class="button-secondary"><?php echo __('New form',WYSIJAFUTURE);?></a>
        <?php
        // get all forms
        $wysija_forms = json_decode(get_option('wysija_forms'), true);
        $wysija_forms = array();
        if(empty($wysija_forms)) {
            /*$wysija_forms = array();
            $defaultForm=array(
                'id'=>'default-form',
                'name'=>__('Default form',WYSIJA),
                'blocks'=>array(
                    0=>array('fields'=> array(
                                0=>array('type'=>'email', 'params'=>array('label'=>__('Email',WYSIJA)))
                            )
                        ),
                    1=>array('fields'=> array(
                                0=>array('type'=>'submit', 'params'=>array('label'=>__('Subscribe!',WYSIJA)))
                            )
                        ),
                    )
                );
            $wysija_forms['default-form']=$defaultForm;*/
            $wysija_forms = array('default' => $hFormEngine->getDefaultData());
            WYSIJA::update_option('wysija_forms',json_encode($wysija_forms));
        }
        ?>

        <select id="wysija_form_list" name="wysija[profiles][forms]">
            <option value=""><?php echo __('Edit a form...',WYSIJAFUTURE) ?></option>
            <?php
            foreach($wysija_forms as $key => $form) {
                echo '<option value="'.$key.'">'.$form['name'].'</option>';
            }
            ?>
        </select>

        <hr/>

        <div id="wysija_wrapper" class="clearfix">
            <div id="wj-form-edit-drag">
                <div id="wj-form-name">Edit <span id="wj-edit-form-name">
                        <span id="wj-form-name-label"></span>
                        <input type="text" id="wj-form-name-value" name="wysija[profiles][form][name]" value=""/>
                        <input type="hidden" id="wj-form-id-value" name="wysija[profiles][form][id]" value=""/>
                    </span>
                </div>
                <div id="wysija_form_editor">
                    <?php
                        $hFormEngine->setData($wysija_forms['default']);
//                        print '<pre>';
//                        print_r($wysija_forms['default']);
//                        exit;
                        echo $hFormEngine->renderEditor();
                    ?>
                </div>
                <div id="general-part">
                    <div class="list-selection"><p><?php _e('Add subscribers to these lists:',WYSIJAFUTURE) ?></p><?php
                    $fieldHTML= '';

                    $modelList=&WYSIJA::get('list','model');
                    $lists=$modelList->get(array('name','list_id'),array('is_enabled'=>1));
                    foreach($lists as $list){
                        $checked=false;
                        //if(in_array($list['list_id'], $valuefield)) $checked=true;
                        $formObj=&WYSIJA::get('forms','helper');
                        $fieldHTML.= '<p class="labelcheck listcheck"><label for="listid-'.$list['list_id'].'">'.$formObj->checkbox( array('id'=>'listid-'.$list['list_id'],
                                    'name'=>'wysija[profiles][form][lists][]', 'class'=>''),
                                        $list['list_id'],$checked).$list['name'].'</label></p>';
                        $fieldHTML.='<input type="hidden" name="wysija[profiles][form][list_name]['.$list['list_id'].']'.'" value="'.$list['name'].'" />';
                    }
                    echo $fieldHTML;

                    ?></div>
                </div>
                <p class="submit">
                    <a href="javascript:;" id="forms-save" class="button-primary wysija" ><?php echo esc_attr(__('Save',WYSIJAFUTURE)); ?></a>
                    <a href="javascript:;" id="form-delete"><?php echo esc_attr(__('Delete',WYSIJAFUTURE)); ?></a>
                </p>
            </div>
            <div id="wysija_form_toolbar">
                <ul class="wysija_form_toolbar_tabs">
                    <li class="wjt-content">
                        <a class="selected" href="javascript:;" rel="wj_content"><?php _e('Content',WYSIJAFUTURE)?></a>
                    </li>
                </ul>

                <!-- WIDGET TEMPLATES -->
                <div id="wysija_widget_templates">
                <?php
                    $hParser =& WYSIJA::get('wj_parser', 'helper');
                    $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
                    $fields = array();

                    foreach($customFields as $customField) {
                        $fieldData = array(
                            'field' => $customField['column_name'],
                            'type' => 'input',
                            'unique' => true,
                            'static' => (in_array($customField, array('email', 'submit'))),
                            'params' => array(
                                'label' => $customField['name']
                            )
                        );
                        // render field JS template
                        $fieldData['template'] = $hParser->renderForJS($fieldData, 'templates/form/widgets/input.html');
                        // add field data
                        $fields[] = $fieldData;
                    }

                    // render widget templates
                    echo $hParser->render(array('fields' => $fields), 'templates/form/widgets/template.html');
                ?>
                </div>
                <!-- CONTENT BAR -->
                <ul class="wj_content">
                    <?php

                    $fieldTemplates = array();

                    // custom fields that can only be inserted once per form
                    foreach($customFields as $customField) {
                        echo '<li>'.
                                '<a class="wysija_form_item" id="'.$customField['column_name'].'" wysija_field="'.$customField['column_name'].'" wysija_unique="1" wysija_type="input">'.$customField['name'].'</a>'.
                            '</li>';
                    }

                    // extra widgets that can be added more than once
                    $extraTypes = array(
                        'list-selection' => array('label'=>__('List selection',WYSIJAFUTURE), 'type'=>'list'),
                        'text-instructions' => array('label' => __('Random text or instructions', WYSIJAFUTURE), 'type' => 'text'),
                        'divider' => array('label' => __('Divider', WYSIJAFUTURE), 'type' => 'divider')
                    );
                    foreach($extraTypes as $key=>$data){
                        echo '<li><a class="wysija_form_item" id="'.$key.'" wysija_type="'.$data['type'].'">'.$data['label'].'</a></li>';
                    }

                    // premium widgets
                    add_filter('wysija_premium_fields_soon', array($this, 'premiumSoonFields'), 1);
                    echo apply_filters('wysija_premium_fields_soon', '');
                    ?>
                </ul>

                <div id="wysija_notices" style="display:none;"><span id="wysija_notice_msg"></span><img alt="loader" style="display:none;" id="ajax-loading" src="<?php echo WYSIJA_URL ?>img/wpspin_light.gif" /></div>
            </div>
        </div>
        <?php
    }
    function premiumSoonFields(){
        $html='';
        $html.='<li class="notice">'.str_replace(array('[link]','[/link]'), array('<a href="javascript:;" class="premium-tab">','</a>'), __('Soon available in [link]Premium[/link]:', WYSIJAFUTURE)).'</li>';
        $extraTypes=array(
                        'new-text'=>array('label'=>__('Text or number',WYSIJAFUTURE),'type'=>'text'),
                        'new-textarea'=>array('label'=>__('Paragraph text',WYSIJAFUTURE),'type'=>'textarea'),
                        'new-date'=>array('label'=>__('Date or birthday',WYSIJAFUTURE),'type'=>'date'),
                        'new-radio'=>array('label'=>__('Radio buttons',WYSIJAFUTURE),'type'=>'radio'),
                        'new-checkbox'=>array('label'=>__('Checkboxes',WYSIJAFUTURE),'type'=>'checkbox'),
                        'new-dropdown'=>array('label'=>__('Dropdown list',WYSIJAFUTURE),'type'=>'dropdown'),
                        'new-image'=>array('label'=>__('Image',WYSIJAFUTURE),'type'=>'image'),
                        'new-file'=>array('label'=>__('File',WYSIJAFUTURE),'type'=>'file'),
                        'new-country'=>array('label'=>__('Country, State or Province',WYSIJAFUTURE),'type'=>'country'));
        foreach($extraTypes as $key=>$data){
            $html.='<li><a class="wysija_form_item disabled" id="'.$key.'" wysija_type="'.$data['label'].'">'.$data['label'].'</a></li>';
        }
        return $html;
    }

    function emailactiv(){
        $step=array();

        $step['confirm_dbleoptin']=array(
            'type'=>'radio',
            'values'=>array(true=>__('Yes',WYSIJA),false=>__('No',WYSIJA)),
            'label'=>__('Enable activation email',WYSIJA),
            'desc'=>__('Prevent fake signups by sending activation emails to your subscribers.',WYSIJA).' <a href="http://support.wysija.com/knowledgebase/why-you-should-enforce-email-activation/?utm_source=wpadmin&utm_campaign=activation email" target="_blank">'.__('Learn more.',WYSIJA).'</a>');

        if(!$this->_user_can('toggle_signup_confirmation')){
            $step['confirm_dbleoptin']['type']='disabled_radio';
        }

        $step['confirm_email_title']=array(
            'type'=>'input',
            'label'=>__('Email subject',WYSIJA),
            'rowclass'=>'confirmemail');

        $step['confirm_email_body']=array(
            'type'=>'textarea',
            'label'=>__('Email content',WYSIJA),
            'rowclass'=>'confirmemail');


        $modelU=&WYSIJA::get('user','model');
        $modelU->getFormat=OBJECT;

        $objUser=$modelU->getOne(false,array('wpuser_id'=>WYSIJA::wp_get_userdata('ID')));
        $step['subscribed_title']=array(
            'type'=>'input',
            'label'=>__('Confirmation page title',WYSIJA),
            'desc'=>__('When subscribers click on the activation link, they are redirected to this [link]confirmation page[/link]',WYSIJA),
            'link'=>'<a href="'.$modelU->getConfirmLink($objUser,"subscribe",false,true).'&demo=1" target="_blank" title="'.__("Preview page",WYSIJA).'">',
            'rowclass'=>'confirmemail');
        $step['subscribed_subtitle']=array(
            'type'=>'input',
            'label'=>__('Confirmation page content',WYSIJA),
            'rowclass'=>'confirmemail');

        ?>

        <table class="form-table">
            <tbody>
                <?php
                echo $this->buildMyForm($step,'','config');

                ?>
            </tbody>
        </table>
        <?php
    }

    function sendingmethod(){
        $key='sending_method';
        $realvalue=$this->model->getValue($key);
        $formsHelp=&WYSIJA::get('forms','helper');
        ?>
        <table class="form-table" id="ms-sendingmethod">
            <tbody>

                <tr class="methods">
                    <?php

                    $is_multisite=is_multisite();
                    //$is_multisite=true;//PROD comment that line
                    if($is_multisite){
                        $field='<th scope="row">';
                        $checked=false;
                        $value='network';
                        $id=str_replace('_','-',$key).'-'.$value;
                        if($value ==$realvalue) $checked=true;
                        $field.='<label for="'.$id.'" class="clearfix">';
                        $field.=$formsHelp->radio(array('id'=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                        $field.='<h3>'.__('Network\'s Method' ,WYSIJA).'</h3></label>';
                        $field.='<p>'.__('Method set by the network admin.',WYSIJA).'</p>';
                        if(!$this->model->getValue('ms_sending_emails_ok')) $field.='<strong'.__('Not Configured!',WYSIJA).'</strong>';
                        $field.='</th>';
                        echo $field;
                    }

                    ?>

                    <th scope="row">
                        <?php
                            $checked=false;
                            $value='site';
                            $id=str_replace('_','-',$key).'-'.$value;
                            if($value ==$realvalue) $checked=true;
                            $field='<label for="'.$id.'" class="clearfix">';
                            $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.='<h3>'.__('Your own website',WYSIJA).'</h3></label>';
                            $field.='<p>'.__('The simplest solution for small lists. Your web host sets a daily email limit.',WYSIJA).'</p>';
                            echo $field;
                        ?>
                    </th>
                    <th scope="row">
                        <?php
                            $checked=false;
                            $value='gmail';
                            $id=str_replace("_",'-',$key).'-'.$value;
                            if($value ==$realvalue) $checked=true;
                            $field='<label for="'.$id.'" class="clearfix">';
                            $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.='<h3>Gmail</h3></label>';
                            $field.='<p>'.__("Easy to setup. Limited to 500 emails a day. We recommend that you open a dedicated Gmail account for this purpose.",WYSIJA).'</p>';
                            echo $field;
                        ?>
                    </th>
                    <th scope="row">
                        <?php
                            $checked = false;
                            $value = 'smtp';
                            if($value === $realvalue) $checked = true;

                            $id = str_replace('_', '-', $key).'-'.$value;
                            $field ='<label for="'.$id.'" class="clearfix">';
                            $field.= $formsHelp->radio(array('id' => $id, 'name' => 'wysija[config]['.$key.']'), $value, $checked);
                            $field.= '<h3>'.__('Third party',WYSIJA).'</h3></label>';
                            $field.='<p>'.__('Send with a professional SMTP provider, a great choice for big and small lists. We\'ve negotiated promotional offers with a few providers for you.',WYSIJA).' <a href="http://support.wysija.com/knowledgebase/send-with-smtp-when-using-a-professional-sending-provider/?utm_source=wpadmin&utm_campaign=sending method" target="_blank">'.__('Read more',WYSIJA).'</a>.</p>';
                            echo $field;
                        ?>
                    </th>

                    <td>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-site">
                    <th scope="row">
                        <?php
                            $field=__('Delivery method',WYSIJA);
                            $field.='<p class="description">'.__('Send yourself some test emails to confirm which method works with your server.',WYSIJA).'</p>';
                            echo $field;
                        ?>
                    </th>
                    <td colspan="2">
                        <?php
                            $key="sending_emails_site_method";
                            $checked=false;
                            $realvalue=$this->model->getValue($key);
                            $value="phpmail";
                            if($value ==$realvalue) $checked=true;

                            $id=str_replace("_",'-',$key).'-'.$value;
                            $field='<p class="title"><label for="'.$id.'">';
                            $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.='PHP Mail</label><a class="button-secondary" id="send-test-mail-phpmail">'.__('Send a test mail',WYSIJA).'</a></p>';
                            $field.='<p class="description">'.__('This email engine works on 95&#37; of servers',WYSIJA).'</p>';


                            $value="sendmail";
                            $checked=false;
                            if($value ==$realvalue) $checked=true;

                            $id=str_replace("_",'-',$key).'-'.$value;
                            $field.='<p class="title"><label for="'.$id.'">';
                            $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.='Sendmail</label>
                                <a class="button-secondary" id="send-test-mail-sendmail">'.__('Send a test mail',WYSIJA).'</a></p>';
                            $field.='<p class="description">'.__('This method works on 5&#37; of servers',WYSIJA).'</p>';

                            $id=str_replace("_",'-',$key).'-'.$value."-path";
                            $field.='<p class="title" id="p-'.$id.'"><label for="'.$id.'">';
                            $field.=__("Sendmail path",WYSIJA).'</label>'.$formsHelp->input(array("id"=>$id,'name'=>'wysija[config][sendmail_path]'),$this->model->getValue("sendmail_path")).'</p>';

                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp">
                    <th scope="row">
                        <?php
                            $key="smtp_host";
                            $id=str_replace("_",'-',$key);
                            $field='<label for="'.$id.'">'.__('SMTP Hostname',WYSIJA)."</label>";
                            $field.='<p class="description">'.__('e.g.:smtp.mydomain.com',WYSIJA).'</p>';
                            echo $field;
                        ?>
                    </th>
                    <td colspan="2">
                        <?php
                            $value=$this->model->getValue($key);
                            $field=$formsHelp->input(array('id'=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked);
                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp choice-sending-method-gmail">
                    <th scope="row">
                        <?php
                            $key="smtp_login";
                            $id=str_replace("_",'-',$key);
                            $field='<label for="'.$id.'">'.__('Login',WYSIJA)."</label>";

                            echo $field;
                        ?>
                    </th>
                    <td colspan="2">
                        <?php
                            $value=$this->model->getValue($key);
                            $field=$formsHelp->input(array("id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked);
                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp choice-sending-method-gmail">
                    <th scope="row">
                        <?php
                            $key="smtp_password";
                            $id=str_replace("_",'-',$key);
                            $field='<label for="'.$id.'">'.__('Password',WYSIJA)."</label>";
                            echo $field;
                        ?>
                    </th>
                    <td colspan="2">
                        <?php
                            $value=$this->model->getValue($key);
                            $field=$formsHelp->input(array("type"=>"password","id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked);
                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr id="restapipossible" class="hidechoice">
                    <th scope="row">
                        <?php
                            $key='smtp_rest';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">web API</label>';
                            $field.='<p class="description">'.__('Activate if your SMTP ports are blocked.',WYSIJA).'</p>';
                            echo $field;
                        ?>
                    </th>
                    <td colspan="2">
                        <?php
                            $value=$this->model->getValue($key);
                            $checked=false;
                            if($this->model->getValue('smtp_rest')) $checked=true;
                            $field=$formsHelp->checkbox(array('id'=>$id,'name'=>'wysija[config]['.$key.']','size'=>'3'),1,$checked);

                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp choice-no-restapi">
                    <th scope="row">
                        <?php
                            $key='smtp_port';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">'.__('SMTP port',WYSIJA)."</label>";

                            echo $field;
                        ?>
                    </th>
                    <td colspan="2">
                        <?php
                            $value=$this->model->getValue($key);
                            $field=$formsHelp->input(array('id'=>$id,'name'=>'wysija[config]['.$key.']','size'=>'3'),$value,$checked);

                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp choice-no-restapi">
                    <th scope="row">
                        <?php
                            $key="smtp_secure";
                            $id=str_replace("_",'-',$key);
                            $field='<label for="'.$id.'">'.__('Secure connection',WYSIJA)."</label>";
                            echo $field;
                        ?>
                    </th>
                    <td colspan="2">
                        <?php

                            $value=$this->model->getValue($key);

                            $field=$formsHelp->dropdown(array('name'=>'wysija[config]['.$key.']',"id"=>$id),array(false=>__("No"),"ssl"=>"SSL","tls"=>"TLS"),$value);
                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp choice-no-restapi">
                    <th scope="row">
                        <?php
                            $field=__('Authentication',WYSIJA);
                            echo $field.'<p class="description">'.__("Leave this option to Yes. Only a tiny portion of SMTP services ask Authentication to be turned off.",WYSIJA).'</p>';
                        ?>
                    </th>
                    <td colspan="2">
                        <?php

                            $key='smtp_auth';
                            $realvalue=$this->model->getValue($key);

                            $value=false;
                            $checked=false;
                            if($value ==$realvalue) $checked=true;
                            $id=str_replace('_','-',$key).'-'.$value;
                            $field='<label for="'.$id.'">';
                            $field.=$formsHelp->radio(array('id'=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.=__('No',WYSIJA).'</label>';

                            $value=true;
                            $checked=false;
                            if($value ==$realvalue) $checked=true;
                            $id=str_replace('_','-',$key).'-'.$value;
                            $field.='<label for="'.$id.'">';
                            $field.=$formsHelp->radio(array('id'=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.=__('Yes',WYSIJA).'</label>';


                            echo $field;
                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp choice-sending-method-gmail">
                    <th scope="row">
                        <a class="button-secondary" id="send-test-mail-smtp"><?php _e("Send a test mail",WYSIJA)?></a>
                    </th>
                    <td colspan="2">
                        <?php

                        ?>
                    </td>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp choice-sending-method-site choice-sending-method-gmail">
                    <th scope="row">
                        <?php
                            $field=__('Send...',WYSIJA);

                            echo $field.'<p class="description">'.str_replace(array('[link]','[/link]'),array('<a href="http://support.wysija.com/knowledgebase/wp-cron-batch-emails-sending-frequency/" target="_blank">','</a>'),__('Your web host has limits. We suggest 70 emails per hour to be safe. [link]Find out more[/link].',WYSIJA)).'</p>';
                        ?>
                    </th>
                    <td colspan="2">

                        <?php
                            $name='sending_emails_number';
                            $id=str_replace('_','-',$name);
                            $value=$this->model->getValue($name);
                            $params=array("id"=>$id,'name'=>'wysija[config]['.$name.']','size'=>'6');
                            //if($this->model->getValue("smtp_host")=="smtp.gmail.com") $params["readonly"]="readonly";
                            $field=$formsHelp->input($params,$value);
                            $field.= '&nbsp;'.__('emails', WYSIJA).'&nbsp;';


                            $name='sending_emails_each';
                            $id=str_replace('_','-',$name);
                            $value=$this->model->getValue($name);
                            $field.=$formsHelp->dropdown(array('name'=>'wysija[config]['.$name.']','id'=>$id),$formsHelp->eachValues,$value);
                            $field.='<span class="choice-under15"><b>'.__('This is fast!',WYSIJA).'</b> '.str_replace(array('[link]','[/link]'),array('<a href="http://support.wysija.com/knowledgebase/wp-cron-batch-emails-sending-frequency/?utm_source=wpadmin&utm_campaign=cron" target="_blank">','</a>'),__('We suggest you setup a cron job. [link]Read more[/link] on support.wysija.com',WYSIJA)).'</span>';
                            echo $field;


                        ?>
                    </td>
                </tr>

            </tbody>
        </table>
        <?php
    }

    function extend_settings_premium($resultHTML){

        $resultHTML='<div id="premium" class="wysija-panel">';
        $resultHTML.=$this->premium();
        $resultHTML.='</div>';
        return $resultHTML;
    }
    function clearlog(){

        echo '<h3>Logs have been cleared</h3>';
    }
    function log(){
        $option_log=get_option('wysija_log');

        foreach($option_log as $key => $data){
            echo '<h3>'.$key.'</h3>';
            dbg($data,0);
        }

    }

    function advanced(){

        $step=array();

        $step['role_campaign']=array(
            'type'=>'capabilities',
            '1col'=>1);

        $step['replyto_name']=array(
            'type'=>'fromname',
            'class'=>'validate[required]',
            'label'=>__('Reply-to name & email',WYSIJA),
            'desc'=>__('You can change the default reply-to name and email for your newsletters. This option is also used for the activation emails and Admin notifications (in Basics).',WYSIJA));

        $step['bounce_email']=array(
            'type'=>'input',
            'label'=>__('Bounce Email',WYSIJA),
            'desc'=>__('To which address should all the bounced emails go? Get the [link]Premium version[/link] to automatically handle these.',WYSIJA),
            'link'=>'<a class="premium-tab" href="javascript:;" title="'.__('Purchase the premium version.',WYSIJA).'">');

        $step=apply_filters('wysija_settings_advanced', $step);

        $modelU=&WYSIJA::get('user','model');
        $objUser=$modelU->getCurrentSubscriber();


        $step['commentform']=array(
            'type'=>'commentform',
            'label'=>__('Subscribe in comments',WYSIJA),
            'desc'=>__('Visitors who submit a comment on a post can click on a checkbox to subscribe.',WYSIJA),
            );

        $showregisteroption=true;
        //this option is only available for the main site
        if(is_multisite() && get_current_blog_id()!=1) $showregisteroption=false;
        if($showregisteroption) {
            $step['registerform']=array(
            'type'=>'commentform',
            'label'=>__('Subscribe in registration form',WYSIJA),
            'desc'=>__('Allow users who register to your site to subscribe on a list of your choice.',WYSIJA)
            );
        }

        $step['viewinbrowser']=array(
            'type'=>'viewinbrowser',
            'label'=>__('Link to browser version',WYSIJA),
            'desc'=>__('Displays at the top of your newsletters. Don\'t forget to include the link tag, ie: [link]The link[/link]',WYSIJA),
            );

        $step['unsubscribe_linkname']=array(
            'type'=>'input',
            'label'=>__('Text of "Unsubscribe" link',WYSIJA),
            'desc'=>__('This changes the label for the unsubscribe link in the footer of your newsletters.',WYSIJA));

        $step['unsubscribed_title']=array(
            'type'=>'input',
            'label'=>__('Unsubscribe page title',WYSIJA),
            'desc'=>__('This is the [link]confirmation page[/link] a user is directed to after clicking on the unsubscribe link at the bottom of a newsletter.',WYSIJA),
            'link'=>'<a href="'.$modelU->getConfirmLink($objUser,"unsubscribe",false,true).'&demo=1" target="_blank" title="'.__('Preview page',WYSIJA).'">');


        $step['unsubscribed_subtitle']=array(
            'type'=>'input',
            'label'=>__('Unsubscribe page content',WYSIJA));


        $step['manage_subscriptions']=array(
        'type'=>'managesubscribe',
        'label'=>__('Subscribers can edit their profile',WYSIJA),
        'desc'=>__('Add a link in the footer of all your newsletters so subscribers can edit their profile and lists. [link]See your own subscriber profile page.[/link]',WYSIJA),
        'link'=>'<a href="'.$modelU->getConfirmLink($objUser,'subscriptions',false,true).'" target="_blank" title="'.__('Preview page',WYSIJA).'">',);


        $step['advanced_charset']=array(
            'type'=>'dropdown_keyval',
            'values'=>array('UTF-8','UTF-7',
                'BIG5',
                "ISO-8859-1","ISO-8859-2","ISO-8859-3","ISO-8859-4","ISO-8859-5","ISO-8859-6","ISO-8859-7","ISO-8859-8","ISO-8859-9","ISO-8859-10","ISO-8859-13","ISO-8859-14","ISO-8859-15",
                'Windows-1251','Windows-1252'),
            'label'=>__('Charset',WYSIJA),
            'desc'=>__('Squares or weird characters are displayed in your emails? Select the encoding for your language.',WYSIJA));

        $step=apply_filters('wysija_settings_advancednext', $step);

        $step['cron_manual']=array(
            'type'=>'cron',
            'label'=>'Enable Wysija Cron\'s',
            'desc'=>__('None of your queued emails have been sent? Then activate this option.',WYSIJA));

        $step['debug_new']=array(
            'type'=>'debugnew',
            'label'=>__('Debug mode',WYSIJA),
            'desc'=>__('Enable this to show Wysija\'s errors. Our support might ask you to enable this if you seek their help.',WYSIJA));

        if(WYSIJA_DBG>1){
            $step['debug_log']=array(
            'type'=>'debuglog',
            'label'=>'Logs',
            'desc'=>  str_replace(array('[link]','[linkclear]','[/link]','[/linkclear]'),
                    array('<a href="admin.php?page=wysija_config&action=log">','<a href="admin.php?page=wysija_config&action=clearlog">','</a>','</a>'),
                    'View them [link]here[/link]. Clear them [linkclear]here[/linkclear]'));
        }


        ?>
        <table class="form-table">
            <tbody>
                <?php echo $this->buildMyForm($step,'','config'); ?>
                <?php if (current_user_can('delete_plugins')): ?>
                    <tr><th scope="row">
                        <div class="label"><?php _e('Reinstall from scratch',WYSIJA)?>
                        <p class="description"><?php _e('Want to start all over again? This will wipe out Wysija and reinstall anew.',WYSIJA)?></p>
                        </div>
                    </th><td><p><a class="button" href="admin.php?page=wysija_config&action=reinstall"><?php _e('Reinstall now...',WYSIJA); ?></a></p></td></tr>
                <?php endif ?>
            </tbody>
        </table>
        <?php
    }

    function premium(){
       $hLicence=&WYSIJA::get('licence','helper');
       $urlpremium = 'http://www.wysija.com/checkout/?wysijadomain='.$hLicence->getDomainInfo().'&nc=1&utm_source=wpadmin&utm_campaign=purchasebutton';

       $arrayPremiumBullets=array(
           'more2000'=>array(
               'title'=>__('Send to more than 2000 subscribers.',WYSIJA),
               'desc'=>__('You have no more limits. Send to 100 000 if you want.',WYSIJA)
               ),
           'linksstats'=>array(
               'title'=>__('Find out which links are clicked.',WYSIJA),
               'desc'=>__('This is the most important engagement metric. You\'ll get hooked.',WYSIJA)
               ),
           'advlinkstats'=>array(
               'title'=>__('Track clicked links for each subscriber.',WYSIJA),
               'desc'=>__('Find out who is really addicted to your newsletters.',WYSIJA)
               ),
           'trackga'=>array(
               'title'=>__('Track with Google Analytics.',WYSIJA),
               'desc'=>__('Find out what your subscribers do once on your site.',WYSIJA)
               ),
           'cron'=>array(
               'title'=>__('We activate a cron job for you.',WYSIJA),
               'desc'=>__('We make sure you\'re sending every 15 minutes to avoid unregular delivery.',WYSIJA)
               ),
           'bounces'=>array(
               'title'=>__('Let us handle your bounces.',WYSIJA),
               'desc'=>__('It\'s bad to send to invalid addresses. Wysija removes them for you. Your reputation stays clean.',WYSIJA)
               ),
           'themes'=>array(
               'title'=>__('Download more beautiful themes.',WYSIJA),
               'desc'=>__('We work with top notch designers. The latest and prettiest are exclusive. [link]View them on our site.[/link]',WYSIJA),
               'link'=>'http://www.wysija.com/newsletter-templates-wordpress/?utm_source=wpadmin&utm_campaign=premiumtab'
               ),
           'support'=>array(
               'title'=>__('Fast and efficient support.',WYSIJA),
               'desc'=>__('It\'s like a valet service from the engineers themselves: Ben, Jo and Kim.',WYSIJA)
               ),
           'dkim'=>array(
               'title'=>__('Increase your deliverability with DKIM.',WYSIJA),
               'desc'=>__('Add this signature to your emails with Wysija. Spam filters can then authenticate your emails and your domain.',WYSIJA)
               ),
           'install'=>array(
               'title'=>__('Upgrade in a few clicks.',WYSIJA),
               'desc'=>__('You don\'t need to reinstall. We\'ll simply activate your site and you\'ll download a small plugin.',WYSIJA)
               ),
           'happy'=>array(
               'title'=>__('Join our happy users.',WYSIJA),
               'desc'=>__('Wysija is getting better every day thanks to users like you. <br />Read [link]what they are saying[/link].',WYSIJA),
               'link'=>'http://wordpress.org/support/view/plugin-reviews/wysija-newsletters'
               ),
           'trynow'=>array(
               'title'=>__('Try it now. Not happy? Get your money back.',WYSIJA),
               'desc'=>__('30-Day money back guarantee. Good reason to try us out.',WYSIJA)
               ),
            'licences'=>array(
               'title'=>__('Your licence to thrill.',WYSIJA),
               'desc'=>'<ul><li>'.__('Blogger: 1 site for $99 / year.',WYSIJA).'</li><li>'.__('Freelance: 4 sites for $299 / year.',WYSIJA).'</li><li>'.__('Agency: unlimited sites for $599 / year.',WYSIJA).'</li></ul>'
               )
       );

       $htmlContent='<div id="premium-content"><h2>'.__('12 Cool Reasons to Upgrade to Premium',WYSIJA).'</h2><div class="bulletium">';

        foreach($arrayPremiumBullets as $key => $bullet){
            $htmlContent.='<div id="'.$key.'" class="bullet-hold clearfix"><div class="feat-thumb"></div><div class="description"><h3>'.$bullet['title'].'</h3><p>';

            if(isset($bullet['link'])){
                $htmlContent.= str_replace(array('[link]','[/link]'),array('<a href="'.$bullet['link'].'" target="_blank">','</a>'),$bullet['desc']);
            }else   $htmlContent.= $bullet['desc'];

            $htmlContent.='</p></div></div>';
        }
        $htmlContent.='</div></div>';
        $htmlContent.='<p class="wysija-premium-wrapper">
            <a class="wysija-premium-btns wysija-support" href="'.$urlpremium.'" target="_blank">'.__('Upgrade now',WYSIJA).'</a></p>';
        $htmlContent.='<br><p>'.__('Already paid?', WYSIJA).' <a id="premium-activate" type="submit" class="wysija" href="javascript:;" />'. esc_attr(__('Activate your Premium licence.',WYSIJA)).'</a></p>';

        $htmlContent.='<p>'.str_replace(array('[link]','[/link]'),array('<a href="http://www.wysija.com/contact/?utm_source=wpadmin&utm_campaign=premiumtab" target="_blank">','</a>'),__('Got a sales question? [link]Get in touch[/link] with Kim, Jo, Adrien and Ben.',WYSIJA)).'</p>';
        $htmlContent.='<p>'.str_replace(array('[link]','[/link]'),array('<a href="http://support.wysija.com/terms-conditions/?utm_source=wpadmin&utm_campaign=premiumtab" target="_blank">','</a>'),__('Read our simple and easy [link]terms and conditions.[/link]',WYSIJA)).'</p>';

        return $htmlContent;
    }

    /**
     * filter adding its own tab to wysija's config(this deals with the name of the tab)
     * @param string $tabs
     * @return string
     */
    function ms_tab_name($tabs){
        $tabs['multisite'] = 'MS';
        return $tabs;
    }

    /**
     * filter adding its own tab to wysija's config (this deals with the content of the tab)
     * @param type $htmlContent
     * @param type $arg
     * @return string
     */
    function ms_tab_content($htmlContent,$arg){
        $this->viewObj=$arg['viewObj'];
        $mConfig=&WYSIJA::get('config','model');
        $formsHelp=&WYSIJA::get('forms','helper');

        $htmlContent .='<div id="multisite" class="wysija-panel">';//start multisite div
        $htmlContent.= '<div class="intro"><h3>'.__('Pick your prefered configuration?',WYSIJA).'</h3></div>';

        $htmlContent.= '<table class="form-table" id="form-ms-config">
            <tbody>
                <tr class="methods">
                    <th scope="row">';

        $checked=false;
        $key='ms_sending_config';
        $realvalue=$mConfig->getValue($key);
        $value='one-for-all';
        $id=str_replace('_','-',$key).'-'.$value;
        if($value==$realvalue) $checked=true;
        $field='<label for="'.$id.'" class="clearfix">';
        $field.=$formsHelp->radio(array('id'=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
        $field.='<p class="title"><strong>'.__('One configuration for all sites',WYSIJA).'</strong></p></label>';
        $field.='<p>'.__('Enforce all sites to send with a unique FROM email address. You only need to configure the Automated Bounce Handling (Premium), SPF & DKIM only once.',WYSIJA).'</p>';
        $field.='<p>'.__('Users can still change their reply-to address for their newsletter. Network admins can still edit sending method for each site.',WYSIJA).'</p>';
        $htmlContent.= $field;

        $htmlContent.= '</th><th scope="row">';

        $checked=false;
        $value='one-each';
        $id=str_replace('_','-',$key).'-'.$value;
        if($value ==$realvalue) $checked=true;
        $field='<label for="'.$id.'" class="clearfix">';
        $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
        $field.='<p class="title"><strong>'.__('Configure each site manually',WYSIJA).'</strong></p></label>';
        $field.='<p>'.__('Configure Bounce Handling, SPF & DKIM independently for each site.',WYSIJA).'</p>';
        $htmlContent.= $field;

        $htmlContent.= '</th><td>
                    </td>
                </tr>';

        $htmlContent.='</tbody></table>';

        $htmlContent.='<div class="intro"><h3>'.__('Configuration and Permissions',WYSIJA).'</h3></div>';

        $fields=array();


        $fields['ms_from_email']=array(
            'type'=>'input',
            'label'=>__('FROM email address for all sites',WYSIJA),
            'class'=>'msfromemail',
            'rowclass'=>'choice-one-for-all');

        $fields['ms_allow_admin_sending_method']=array(
            'type'=>'debug',
            'label'=>__('Allow site admins to change the sending method',WYSIJA));
        $fields['ms_allow_admin_toggle_signup_confirmation']=array(
            'type'=>'debug',
            'label'=>__('Allow site admins to deactivate Signup Confirmation',WYSIJA));

        $htmlContent.='<table class="form-table"><tbody>';
        $htmlContent.=$this->viewObj->buildMyForm($fields,'','config');
        $htmlContent.='</tbody></table>';

        $htmlContent.='<div class="intro"><h3>'.__('Network\'s Default Sending Method',WYSIJA).'</h3></div>';
        $htmlContent.=$this->ms_sending_method();
        if(false){
            $htmlContent.= '<div class="intro"><h3>'.__('SPF and DKIM',WYSIJA).'</h3></div>';

            $htmlContent.= '<table class="form-table">
                <tbody>
                    <tr class="methods">
                        <th scope="row">';

            $htmlContent.='<p>'.__('Your SPF record',WYSIJA).'</p>';

            $htmlContent.= '</th>';
            $htmlContent.= '<th scope="row"></th><td></td></tr>';

            $htmlContent.='</tbody></table>';
        }


        $htmlContent .='<p class="submit"><input type="submit" value="'. esc_attr(__('Save settings',WYSIJA)).'" class="button-primary wysija" /></p>';
        $htmlContent.='</div>';//end multisite div

        return $htmlContent;
    }

    function ms_sending_method(){
        $prefix='ms_';
        $key=$prefix.'sending_method';
        $mConfig=&WYSIJA::get('config','model');
        $realvalue=$mConfig->getValue($key);
        $formsHelp=&WYSIJA::get('forms','helper');
        $htmlContent='<table class="form-table" id="ms-sendingmethod">
            <tbody>

                <tr class="methods">
                    <th scope="row">';

                            $checked=false;
                            $value='site';
                            $id=str_replace("_",'-',$key).'-'.$value;
                            if($value ==$realvalue) $checked=true;
                            $field='<label for="'.$id.'" class="clearfix">';
                            $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.='<h3>'.__('Your own website',WYSIJA).'</h3></label>';
                        $htmlContent.=$field;
                        $htmlContent.='</th>
                    <th scope="row">';

                            $checked = false;
                            $value = 'smtp';
                            if($value === $realvalue) $checked = true;

                            $id = str_replace('_', '-', $key).'-'.$value;
                            $field ='<label for="'.$id.'" class="clearfix">';
                            $field.= $formsHelp->radio(array('id' => $id, 'name' => 'wysija[config]['.$key.']'), $value, $checked);
                            $field.= '<h3>'.__('Third party',WYSIJA).'</h3></label>';
                            $htmlContent.=$field;
                        $htmlContent.='</th>

                    <td>
                    </td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-site">
                    <th scope="row">';
                            $field=__('Delivery method',WYSIJA);
                            $field.='<p class="description">'.__('Send yourself some test emails to confirm which method works with your server.',WYSIJA).'</p>';
                             $htmlContent.=$field;

                     $htmlContent.='</th>
                    <td colspan="2">';

                            $key=$prefix.'sending_emails_site_method';
                            $checked=false;
                            $realvalue=$mConfig->getValue($key);
                            $value='phpmail';
                            if($value ==$realvalue) $checked=true;

                            $id=str_replace('_','-',$key).'-'.$value;
                            $field='<p class="title"><label for="'.$id.'">';
                            $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.='PHP Mail</label><a class="button-secondary" id="ms-send-test-mail-phpmail">'.__('Send a test mail',WYSIJA).'</a></p>';
                            $field.='<p class="description">'.__('This email engine works on 95&#37; of servers',WYSIJA).'</p>';


                            $value='sendmail';
                            $checked=false;
                            if($value ==$realvalue) $checked=true;

                            $id=str_replace('_','-',$key).'-'.$value;
                            $field.='<p class="title"><label for="'.$id.'">';
                            $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.='Sendmail</label>
                                <a class="button-secondary" id="ms-send-test-mail-sendmail">'.__('Send a test mail',WYSIJA).'</a></p>';
                            $field.='<p class="description">'.__('This method works on 5&#37; of servers',WYSIJA).'</p>';

                            $id=str_replace("_",'-',$key).'-'.$value."-path";
                            $field.='<p class="title" id="p-'.$id.'"><label for="'.$id.'">';
                            $field.=__("Sendmail path",WYSIJA).'</label>'.$formsHelp->input(array("id"=>$id,'name'=>'wysija[config][sendmail_path]'),$mConfig->getValue("sendmail_path")).'</p>';

                             $htmlContent.=$field;
                     $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp">
                    <th scope="row">';

                            $key=$prefix.'smtp_host';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">'.__('SMTP Hostname',WYSIJA)."</label>";
                            $field.='<p class="description">'.__('e.g.:smtp.mydomain.com',WYSIJA).'</p>';
                             $htmlContent.=$field;
                     $htmlContent.='
                    </th>
                    <td colspan="2">';

                            $value=$mConfig->getValue($key);
                            $field=$formsHelp->input(array('id'=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked);
                             $htmlContent.=$field;
                        $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp">
                    <th scope="row">';

                            $key=$prefix.'smtp_login';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">'.__('Login',WYSIJA)."</label>";

                             $htmlContent.=$field;

                     $htmlContent.='</th>
                    <td colspan="2">';

                            $value=$mConfig->getValue($key);
                            $field=$formsHelp->input(array("id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked);
                             $htmlContent.=$field;
                  $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp">
                    <th scope="row">';

                            $key=$prefix.'smtp_password';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">'.__('Password',WYSIJA)."</label>";
                             $htmlContent.=$field;

                     $htmlContent.='</th>
                    <td colspan="2">';

                            $value=$mConfig->getValue($key);
                            $field=$formsHelp->input(array("type"=>"password","id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked);
                             $htmlContent.=$field;

                     $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr id="restapipossible" class="hidechoice">
                    <th scope="row">';

                            $key=$prefix.'smtp_rest';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">web API</label>';
                            $field.='<p class="description">'.__('Activate if your SMTP ports are blocked.',WYSIJA).'</p>';
                             $htmlContent.=$field;

                 $htmlContent.='</th>
                    <td colspan="2">';

                            $value=$mConfig->getValue($key);
                            $checked=false;
                            if($mConfig->getValue('smtp_rest')) $checked=true;
                            $field=$formsHelp->checkbox(array('id'=>$id,'name'=>'wysija[config]['.$key.']','size'=>'3'),1,$checked);

                             $htmlContent.=$field;

                     $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp ms-choice-no-restapi">
                    <th scope="row">';

                            $key=$prefix.'smtp_port';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">'.__('SMTP port',WYSIJA)."</label>";

                             $htmlContent.=$field;

                $htmlContent.='</th>
                    <td colspan="2">';

                            $value=$mConfig->getValue($key);
                            $field=$formsHelp->input(array('id'=>$id,'name'=>'wysija[config]['.$key.']','size'=>'3'),$value,$checked);

                             $htmlContent.=$field;
                $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp ms-choice-no-restapi">
                    <th scope="row">';

                            $key=$prefix.'smtp_secure';
                            $id=str_replace('_','-',$key);
                            $field='<label for="'.$id.'">'.__('Secure connection',WYSIJA)."</label>";
                            $htmlContent.=$field;
                     $htmlContent.='</th>
                    <td colspan="2">';

                            $value=$mConfig->getValue($key);

                            $field=$formsHelp->dropdown(array('name'=>'wysija[config]['.$key.']',"id"=>$id),array(false=>__("No"),"ssl"=>"SSL","tls"=>"TLS"),$value);
                             $htmlContent.=$field;

                     $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp ms-choice-no-restapi">
                    <th scope="row">';

                            $field=__('Authentication',WYSIJA);
                             $htmlContent.=$field.'<p class="description">'.__("Leave this option to Yes. Only a tiny portion of SMTP services ask Authentication to be turned off.",WYSIJA).'</p>';
                      $htmlContent.='</th>
                    <td colspan="2">';

                            $key=$prefix.'smtp_auth';
                            $realvalue=$mConfig->getValue($key);

                            $value=false;
                            $checked=false;
                            if($value ==$realvalue) $checked=true;
                            $id=str_replace('_','-',$key).'-'.$value;
                            $field='<label for="'.$id.'">';
                            $field.=$formsHelp->radio(array('id'=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.=__('No',WYSIJA).'</label>';

                            $value=true;
                            $checked=false;
                            if($value ==$realvalue) $checked=true;
                            $id=str_replace('_','-',$key).'-'.$value;
                            $field.='<label for="'.$id.'">';
                            $field.=$formsHelp->radio(array('id'=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked);
                            $field.=__('Yes',WYSIJA).'</label>';


                             $htmlContent.=$field;
                    $htmlContent.='</td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp">
                    <th scope="row">
                        <a class="button-secondary" id="ms-send-test-mail-smtp">'.__('Send a test mail',WYSIJA).'</a>
                    </th>
                    <td colspan="2">
                    </td>
                </tr>';

                 $htmlContent.='<tr class="ms-hidechoice ms-choice-sending-method-smtp ms-choice-sending-method-site">
                    <th scope="row">';

                            $field=__('Send...',WYSIJA);

                             $htmlContent.=$field.'<p class="description">'.str_replace(array('[link]','[/link]'),array('<a href="http://support.wysija.com/knowledgebase/wp-cron-batch-emails-sending-frequency/" target="_blank">','</a>'),__('Your web host has limits. We suggest 70 emails per hour to be safe. [link]Find out more[/link].',WYSIJA)).'</p>';
                     $htmlContent.='</th>
                    <td colspan="2">';

                            $name=$prefix.'sending_emails_number';
                            $id=str_replace('_','-',$name);
                            $value=$mConfig->getValue($name);
                            $params=array('id'=>$id,'name'=>'wysija[config]['.$name.']','size'=>'6');
                            $field=$formsHelp->input($params,$value);
                            $field.= '&nbsp;'.__('emails', WYSIJA).'&nbsp;';


                            $name=$prefix.'sending_emails_each';
                            $id=str_replace('_','-',$name);
                            $value=$mConfig->getValue($name);
                            $field.=$formsHelp->dropdown(array('name'=>'wysija[config]['.$name.']','id'=>$id),$formsHelp->eachValues,$value);
                            $field.='<span class="choice-under15"><b>'.__('This is fast!',WYSIJA).'</b> '.str_replace(array('[link]','[/link]'),array('<a href="http://support.wysija.com/knowledgebase/wp-cron-batch-emails-sending-frequency/?utm_source=wpadmin&utm_campaign=cron" target="_blank">','</a>'),__('We suggest you setup a cron job. [link]Read more[/link] on support.wysija.com',WYSIJA)).'</span>';
                             $htmlContent.=$field;


                    $htmlContent.='</td>
                </tr>
            </tbody>
        </table>';
        return $htmlContent;
    }

}
