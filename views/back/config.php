<?php
defined('WYSIJA') or die('Restricted access'); class WYSIJA_view_back_config extends WYSIJA_view_back{ var $title="Settings"; var $icon="icon-options-general"; function WYSIJA_view_back_support(){ $this->WYSIJA_view_back(); } function fieldFormHTML_debug($key,$value,$model,$paramsex){ $formsHelp=&WYSIJA::get("forms","helper"); $checked=false; if($this->model->getValue($key)) $checked=true; $field='<p><label for="'.$key.'">'; $field.=$formsHelp->checkbox(array("id"=>$key,'name'=>'wysija['.$model.']['.$key.']'),1,$checked); $field.='</label></p>'; return $field; } function fieldFormHTML_email_notifications($key,$value,$model,$paramsex){ $params=array(); $params['type']="default"; $field=$this->fieldHTML($key,$value,$model,$params); $threecheck=array( "_when_sub" =>__('When someone subscribes',WYSIJA) ,"_when_unsub"=>__('When someone unsubscribes',WYSIJA), "_when_dailysummary"=>__('Daily summary of emails sent',WYSIJA) ); $formsHelp=&WYSIJA::get("forms","helper"); foreach($threecheck as $keycheck => $checkobj){ $checked=false; if($this->model->getValue($key.$keycheck))$checked=true; $field.='<p><label for="'.$key.$keycheck.'">'; $field.=$formsHelp->checkbox(array("id"=>$key.$keycheck,'name'=>'wysija['.$model.']['.$key.$keycheck.']'),1,$checked); $field.=$checkobj.'</label></p>'; } return $field; } function fieldFormHTML_selfsigned($key,$value,$model,$params){ $formsHelp=&WYSIJA::get("forms","helper"); $realvalue=$this->model->getValue($key); $value=0; $checked=false; if($value ==$realvalue) $checked=true; $id=str_replace("_",'-',$key).'-'.$value; $field='<label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija['.$model.']['.$key.']'),$value,$checked); $field.=__('No',WYSIJA).'</label>'; $value=1; $checked=false; if($value ==$realvalue) $checked=true; $id=str_replace("_",'-',$key).'-'.$value; $field.='<label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija['.$model.']['.$key.']'),$value,$checked); $field.=__('Yes',WYSIJA).'</label>'; return $field; } function main(){ ?>
        <div id="tabs">
            <ul id="mainmenu" >
                <li><a href="#basics"><?php _e('The Basics',WYSIJA);?></a></li>
                <li><a href="#emailactiv"><?php _e('Subscription Activation Email',WYSIJA);?></a></li>
                <li><a href="#sendingmethod"><?php _e('Sending Method',WYSIJA);?></a></li>
                <li><a href="#bounce"><?php _e('Automated Bounce Handling',WYSIJA);?></a></li>
                <li><a href="#advanced"><?php _e('Advanced',WYSIJA);?></a></li>
            </ul>
            <form name="wysija-settings" method="post" id="wysija-settings" action="" class="form-valid" autocomplete="off">
                <div id="basics">
                    <?php $this->basics(); ?>
                </div>
                <div id="emailactiv">
                    <?php $this->emailactiv(); ?>
                </div>
                <div id="sendingmethod">
                    <?php $this->sendingmethod(); ?>
                </div>
                
                <div id="bounce">
                    <?php  $config=&WYSIJA::get("config","model"); if(!$config->getValue("premium_key")){ echo str_replace(array('[link]','[/link]'),array('<a class="wysija-premium" href="javascript:;" title="'.__("Purchase Wysija PREMIUM",WYSIJA).'">','<img alt="loader" src="'.WYSIJA_URL.'img/wpspin_light.gif" /></a>'),__("[link]Purchase the premium version[/link] and get access to the <strong>Automated Bounce Handling</strong> system.",WYSIJA)); }else { $this->bounce(); } ?>
                </div>
                <div id="advanced">
                    <?php $this->advanced(); ?>
                </div>
                
                <p class="submit">
                    <?php $this->secure(array('action'=>"save")); ?>
                    <input type="hidden" value="save" name="action" />
                    <input type="hidden" value="" name="redirecttab" id="redirecttab" />
                    <input type="submit" value="<?php echo esc_attr(__('Save settings',WYSIJA)); ?>" class="button-primary wysija">
                </p>
                
            </form>
        </div>
        <?php
 } function basics(){ $step=array(); if(defined('WYSIJA_DBG_ALL') && WYSIJA_DBG_ALL){ $step['premium_key']=array( 'type'=>'input', 'label'=>'Premium Account Serial Key', 'desc'=>'Get immediate support, add your own marketing tracking codes, enable automated bounce handling. [link]Click here to purchase.[/link]', 'link'=>'<a class="wysija-premium" href="javascript:;" title="'.__("Purchase the premium version.",WYSIJA).'">'); } $step['company_address']=array( 'type'=>'input', 'label'=>__("Your company's address",WYSIJA), 'desc'=>__("The address will be added to your newsletters' footer. This helps avoid spam filters.",WYSIJA)); $step['emails_notified']=array( 'type'=>'email_notifications', 'label'=>__('Email notifications',WYSIJA), 'desc'=>__('Put in the emails of the person who should received all notifications, comma separated.',WYSIJA)); $step['from_name']=array( 'type'=>'input', 'label'=>__('From name',WYSIJA), 'desc'=>__("The plugin sends automated notifications, but also to your subscribers, like the subscription activation email. Put in the name that should show up in these emails.",WYSIJA)); $step['from_email']=array( 'type'=>'input', 'label'=>__('From email',WYSIJA), 'desc'=>__('And the email.',WYSIJA)); $modelC=&WYSIJA::get("config","model"); if($modelC->getValue("premium_key")){ $title=__('You are using the premium version.',WYSIJA); $desc=__('Click [link]here[/link] to login to your Wysija account.',WYSIJA); $link='<a href="http://www.wysija.com/" target="_blank" title="'.__("Log in to Wysija.com",WYSIJA).'">'; $desc=str_replace( array("[link]","[/link]"), array($link,"</a>"),$desc); }else{ $title=__('You are using the free version.',WYSIJA); $desc=__('Send to more than 2000 subscribers, get immediate support, enable automated bounce handling and more. [link]Click here to purchase.[/link]',WYSIJA); $link='<a class="wysija-premium" href="javascript:;" title="'.__("Find out what's on offer.",WYSIJA).'">'; $desc=str_replace( array("[link]","[/link]"), array($link,'<img src="'.WYSIJA_URL.'img/wpspin_light.gif" alt="loader"/></a>'),$desc); } ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label><?php echo $title ?></p><p class="description"><?php echo $desc ?> </label>
                    </th>
                    <td></td>
                </tr>
                
                <?php
 echo $this->buildMyForm($step,$modelC->values,"config"); ?>
            </tbody>
        </table>
        <?php
 } function emailactiv(){ $step=array(); $step['confirm_dbleoptin']=array( 'type'=>'radio', 'values'=>array(true=>__("Yes",WYSIJA),false=>__("No",WYSIJA)), 'label'=>__('Send Activation Email',WYSIJA), 'desc'=>__('Subscribers will not receive any emails until they activate their subscriptions. Keep this activated to stop fake subscriptions by humans and robots.',WYSIJA).' <a href="http://support.wysija.com/knowledgebase/why-you-should-enforce-email-activation/">'.__("Read more.",WYSIJA)."</a>"); $step['confirm_email_title']=array( 'type'=>'input', 'label'=>__('Email subject',WYSIJA), 'rowclass'=>"confirmemail"); $step['confirm_email_body']=array( 'type'=>'textarea', 'label'=>__('Email content',WYSIJA), 'rowclass'=>"confirmemail"); $modelU=&WYSIJA::get("user","model"); $modelU->getFormat=OBJECT; $objUser=$modelU->getOne(false,array('wpuser_id'=>get_current_user_id())); $step['subscribed_title']=array( 'type'=>'input', 'label'=>__('Confirmation page title',WYSIJA), 'desc'=>__('When subscribers click on the activation link, they are redirected to this [link]confirmation page[/link]',WYSIJA), 'link'=>'<a href="'.$modelU->getConfirmLink($objUser,"subscribe",false,true).'&demo=1" target="_blank" title="'.__("Preview page",WYSIJA).'">', 'rowclass'=>"confirmemail"); $step['subscribed_subtitle']=array( 'type'=>'input', 'label'=>__('Confirmation page content',WYSIJA), 'rowclass'=>"confirmemail"); ?>

        <table class="form-table">
            <tbody>
                <?php
 echo $this->buildMyForm($step,"","config"); ?>
            </tbody>
        </table>
        <?php
 } function sendingmethod(){ $key="sending_method"; $realvalue=$this->model->getValue($key); $formsHelp=&WYSIJA::get("forms","helper"); ?>
        <table class="form-table">
            <tbody>
                
                <tr>
                    <th scope="row" class="row">
                        <?php  $checked=false; $value="site"; $id=str_replace("_",'-',$key).'-'.$value; if($value ==$realvalue) $checked=true; $field='<label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked); $field.=__('Your own website',WYSIJA).'</label>'; $field.='<p>'.__('Second best option for small lists. Your host sets the limit of emails per day.',WYSIJA).'</p>'; echo $field; ?>
                    </th>
                    <th scope="row" class="row">
                        <?php  $checked=false; $value="gmail"; $id=str_replace("_",'-',$key).'-'.$value; if($value ==$realvalue) $checked=true; $field='<label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked); $field.='Gmail</label>'; $field.='<p>'.__("The simplest of all solutions. Limited to 500 emails a day. We recommend that you open a dedicated Gmail account for this purpose.",WYSIJA).'</p>'; echo $field; ?>
                    </th>
                    <th scope="row" class="row">
                        <?php  $checked=false; $value="smtp"; if($value ==$realvalue) $checked=true; $id=str_replace("_",'-',$key).'-'.$value; $field='<label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked); $field.=__('SMTP server',WYSIJA).'</label>'; $field.='<p>'.__('Perfect for sending with a professional SMTP providers, which we highly recommended for big and small lists. We negotiated promotional offers with a few providers for you.',WYSIJA).'<a href="http://support.wysija.com/knowledgebase/send-with-smtp-when-using-a-professional-sending-provider/"> '.__('Find out more.',WYSIJA).'</a></p>'; echo $field; ?>
                    </th>
                    
                    <td>
                    </td>
                </tr>
                
                <tr class="hidechoice choice-sending-method-site">
                    <th scope="row">
                        <?php  $field=__('Delivery method',WYSIJA); $field.='<p class="description">'.__('Send yourself some test emails to confirm which method works with your server.',WYSIJA).'</p>'; echo $field; ?>
                    </th>
                    <td colspan="2">
                        <?php
 $key="sending_emails_site_method"; $checked=false; $realvalue=$this->model->getValue($key); $value="phpmail"; if($value ==$realvalue) $checked=true; $id=str_replace("_",'-',$key).'-'.$value; $field='<p class="title"><label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked); $field.='PHP Mail</label><a class="button-secondary" id="send-test-mail-phpmail">'.__('Send a test mail',WYSIJA).'</a></p>'; $field.='<p class="description">'.__('This email engine works on 95% of servers',WYSIJA).'</p>'; $value="sendmail"; $checked=false; if($value ==$realvalue) $checked=true; $id=str_replace("_",'-',$key).'-'.$value; $field.='<p class="title"><label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked); $field.='Sendmail</label>
                                <a class="button-secondary" id="send-test-mail-sendmail">'.__('Send a test mail',WYSIJA).'</a></p>'; $field.='<p class="description">'.__('This method works on 5% of servers',WYSIJA).'</p>'; $id=str_replace("_",'-',$key).'-'.$value."-path"; $field.='<p class="title" id="p-'.$id.'"><label for="'.$id.'">'; $field.=__("Sendmail path",WYSIJA).'</label>'.$formsHelp->input(array("id"=>$id,'name'=>'wysija[config][sendmail_path]'),$this->model->getValue("sendmail_path")).'</p>'; echo $field; ?>
                    </td>
                </tr>              
                
                <tr class="hidechoice choice-sending-method-smtp">
                    <th scope="row">
                        <?php  $key="smtp_host"; $id=str_replace("_",'-',$key); $field='<label for="'.$id.'">'.__('SMTP Hostname',WYSIJA)."</label>"; $field.="<p class='description'>e.g.:smtp.mydomain.com</p>"; echo $field; ?>
                    </th>
                    <td colspan="2">
                        <?php  $value=$this->model->getValue($key); $field=$formsHelp->input(array("id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked); echo $field; ?>
                    </td>
                </tr>
                
                <tr class="hidechoice choice-sending-method-smtp choice-sending-method-gmail">
                    <th scope="row">
                        <?php  $key="smtp_login"; $id=str_replace("_",'-',$key); $field='<label for="'.$id.'">'.__('Login',WYSIJA)."</label>"; echo $field; ?>
                    </th>
                    <td colspan="2">
                        <?php
 $value=$this->model->getValue($key); $field=$formsHelp->input(array("id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked); echo $field; ?>
                    </td>
                </tr>
                
                <tr class="hidechoice choice-sending-method-smtp choice-sending-method-gmail">
                    <th scope="row">
                        <?php  $key="smtp_password"; $id=str_replace("_",'-',$key); $field='<label for="'.$id.'">'.__('Password',WYSIJA)."</label>"; echo $field; ?>
                    </th>
                    <td colspan="2">
                        <?php
 $value=$this->model->getValue($key); $field=$formsHelp->input(array("type"=>"password","id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked); echo $field; ?>
                    </td>
                </tr>
                
                <tr class="hidechoice choice-sending-method-smtp">
                    <th scope="row">
                        <?php  $key="smtp_port"; $id=str_replace("_",'-',$key); $field='<label for="'.$id.'">'.__('SMTP port',WYSIJA)."</label>"; echo $field; ?>
                    </th>
                    <td colspan="2">
                        <?php
 $value=$this->model->getValue($key); $field=$formsHelp->input(array("id"=>$id,'name'=>'wysija[config]['.$key.']','size'=>'40'),$value,$checked); echo $field; ?>
                    </td>
                </tr>
                
                <tr class="hidechoice choice-sending-method-smtp">
                    <th scope="row">
                        <?php  $key="smtp_secure"; $id=str_replace("_",'-',$key); $field='<label for="'.$id.'">'.__('Secure connection',WYSIJA)."</label>"; echo $field; ?>
                    </th>
                    <td colspan="2">
                        <?php
 $value=$this->model->getValue($key); $field=$formsHelp->dropdown(array("name"=>'wysija[config]['.$key.']',"id"=>$id),array(false=>__("No"),"ssl"=>"SSL","tls"=>"TLS"),$value); echo $field; ?>
                    </td>
                </tr>
                
                <tr class="hidechoice choice-sending-method-smtp">
                    <th scope="row">
                        <?php  $field=__('Authentication',WYSIJA); echo $field.'<p class="description">'.__("Leave this option to Yes. Only a tiny portion of SMTP services ask Authentication to be turned off.",WYSIJA).'</p>'; ?>
                    </th>
                    <td colspan="2">
                        <?php
 $key="smtp_auth"; $realvalue=$this->model->getValue($key); $value=false; $checked=false; if($value ==$realvalue) $checked=true; $id=str_replace("_",'-',$key).'-'.$value; $field='<label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked); $field.=__('No',WYSIJA).'</label>'; $value=true; $checked=false; if($value ==$realvalue) $checked=true; $id=str_replace("_",'-',$key).'-'.$value; $field.='<label for="'.$id.'">'; $field.=$formsHelp->radio(array("id"=>$id,'name'=>'wysija[config]['.$key.']'),$value,$checked); $field.=__('Yes',WYSIJA).'</label>'; echo $field; ?>
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
                        <?php  $field=__('Send...',WYSIJA); echo $field; ?>
                    </th>
                    <td colspan="2">
                        
                        <?php  $name='sending_emails_number'; $id=str_replace('_','-',$name); $value=$this->model->getValue($name); $params=array("id"=>$id,'name'=>'wysija[config]['.$name.']','size'=>'6'); $field=$formsHelp->input($params,$value); $field.=__('emails every',WYSIJA); $name='sending_emails_each'; $id=str_replace('_','-',$name); $value=$this->model->getValue($name); $field.=$formsHelp->dropdown('wysija[config]['.$name.']', array("fifteen_min"=> __("15 minutes",WYSIJA), "thirty_min"=> __("30 minutes",WYSIJA), "hourly"=> __("1 hour",WYSIJA), "two_hours"=> __("2 hours",WYSIJA), "twicedaily"=> __("Twice daily",WYSIJA), "daily"=> __("Day",WYSIJA)), $value); echo $field; ?>
                    </td>
                </tr>
                
                <tr class="hidechoice choice-sending-method-site">
                    <th scope="row" colspan="2">
                        <?php  $field=__('What the hell should I put here?',WYSIJA); $field.='<p class="description">'.__("Leave this setting to 70 emails per 15 minutes, if you're unsure. To increase this value, get in touch with your host to find out the limit per hour of emails sent.",WYSIJA).'</p>'; echo $field; ?>
                    </th>
                </tr>

                <tr class="hidechoice choice-sending-method-smtp">
                    <th scope="row" colspan="2">
                        <?php  $field=__('What should I pick?',WYSIJA); $field.='<p class="description">'.__('This is tough question because that doesn’t depend on us, but on your server. Get in touch with your hosting provider. Otherwise, we have a great guide for you. ',WYSIJA).'</p>'; echo $field; ?>
                    </th>
                </tr>
                
                
            </tbody>
        </table>
        <?php
 } function bounce(){ $field='<p class="description"><span class="title">'.__('How does it work?',WYSIJA).'</span> </p>'; $field.="<ol>"; $field.="<li>".__('Create an email account dedicated solely to bounce handling, like on Gmail or your own domain. ',WYSIJA)."</li>"; $field.="<li>".__('Fill out the form below so we can connect to it. ',WYSIJA)."</li>"; $field.="<li>".__('Take it easy, the plugin does the rest.',WYSIJA)."</li>"; $field.='</ol>'; $field.='<p class="description"><span class="title">'.__('Need help?',WYSIJA).'</span> '.str_replace(array('[link]','[/link]'),array('<a href="http://support.wysija.com/knowledgebase/automated-bounce-handling-install-guide/">','</a>'),__('Check out [link]our guide[/link] on how to fill out the form.',WYSIJA)); $field.='</p>'; echo $field; ?>
    <div id="innertabs">
        <ul>
            <li><a href="#connection"><?php _e('Settings',WYSIJA);?></a></li>
            <li><a href="#actions"><?php _e('Actions & Notifications',WYSIJA);?></a></li>
        </ul>
        <div id="connection">
            
            <?php $this->connection(); ?>
        </div>
        <div id="actions">
            <p class="description"><?php echo __('There are plenty of reasons for bounces. Configure what to do in each scenario.',WYSIJA)?></p>
            <div id="bounce-msg-error"></div>

            <?php $this->rules(); ?>
        </div>
    </div>
        
        <?php
 } function connection(){ $step=array(); $step['bounce_email']=array( 'type'=>'input', 'label'=>__('Bounce Email',WYSIJA)); $step['bounce_host']=array( 'type'=>'input', 'label'=>__('Hostname',WYSIJA)); $step['bounce_login']=array( 'type'=>'input', 'label'=>__('Login',WYSIJA)); $step['bounce_password']=array( 'type'=>'password', 'label'=>__('Password',WYSIJA)); $step['bounce_port']=array( 'type'=>'input', 'label'=>__('Port',WYSIJA), 'size'=>"4", 'style'=>"width:10px;"); $step['bounce_connection_method']=array( 'type'=>'dropdown', 'values'=>array("pop3"=>"POP3","imap"=>"IMAP","pear"=>__("POP3 without imap extension",WYSIJA),"nntp"=>"NNTP"), 'label'=>__('Connection method',WYSIJA)); $step['bounce_connection_secure']=array( 'type'=>'radio', 'values'=>array(""=>__("No",WYSIJA),"ssl"=>__("Yes",WYSIJA)), 'label'=>__('Secure connection(SSL)',WYSIJA)); $step['bounce_selfsigned']=array( 'type'=>'selfsigned', 'label'=>__('Self-signed certificates',WYSIJA)); $step2=array(); $valuesDDP=array("unsub"=>__("Unsubscribe the user",WYSIJA),"del"=>__("Delete the subscriber",WYSIJA), "not"=>__("Do nothing",WYSIJA)); $step2['bounce_email_notexists']=array( 'type'=>'dropdown', 'values'=>$valuesDDP, 'label'=>__('When email does not exist... ',WYSIJA)); $step2['bounce_inbox_full']=array( 'type'=>'dropdown', 'values'=>$valuesDDP, 'label'=>__('When mailbox full...',WYSIJA)); ?>
        <table class="form-table">
            <tbody>
                <?php
 echo $this->buildMyForm($step,"","config"); $name='bouncing_emails_each'; $id=str_replace('_','-',$name); $value=$this->model->getValue($name); $formsHelp=&WYSIJA::get("forms","helper"); $field=$formsHelp->dropdown(array("name"=>'wysija[config]['.$name.']',"id"=>$id), array("fifteen_min"=> __("15 minutes",WYSIJA), "thirty_min"=> __("30 minutes",WYSIJA), "hourly"=> __("1 hour",WYSIJA), "two_hours"=> __("2 hours",WYSIJA), "twicedaily"=> __("Twice daily",WYSIJA), "daily"=> __("Day",WYSIJA)), $value); $checked=""; if($this->model->getValue("bounce_process_auto")) $checked='checked="checked"'; echo '<tr><td><label for="bounce-process-auto"><input type="checkbox" '.$checked.' id="bounce-process-auto" value="1" name="wysija[config][bounce_process_auto]" />
                    '.__("Process bounce automatically",WYSIJA).'</label></td><td id="bounce-frequency"><label for="'.$id.'">'.__("each",WYSIJA)."</label> ".$field.'</td></tr>'; echo '<tr><td><a class="button-secondary" id="bounce-connector">'.__("Does it work? Try to connect.",WYSIJA).'</a></td><td></td></tr>'; ?>
            </tbody>
        </table>
        <?php
 } function rules(){ $helpRules=&WYSIJA::get("rules","helper"); $rules=$helpRules->getRules(false,true); $modelList=&WYSIJA::get("list","model"); $query="SELECT * FROM ".$modelList->getPrefix()."list WHERE is_enabled>0"; $arrayList=$modelList->query("get_res",$query); $step2=array(); $valuesDDP=array(""=>__("Do nothing",WYSIJA),"delete"=>__("Delete the user",WYSIJA),"unsub"=>__("Unsubscribe the user",WYSIJA)); foreach($arrayList as $list){ $valuesDDP["unsub_".$list['list_id']]=sprintf(__('Unsubscribe the user and add him to the list "%1$s" '),$list['name']); } foreach($rules as $rule){ if(isset($rule['behave'])) continue; $label=$rule['title']; if(isset($rule['action_user_min']) && $rule['action_user_min']>0){ $label.=' '.sprintf(__('(after %1$s try)',WYSIJA),$rule['action_user_min']); } $step2['bounce_rule_'.$rule['key']]=array( 'type'=>'dropdown', 'values'=>$valuesDDP, 'label'=>$label); if(isset($rule['action_user'])){ $step2['bounce_rule_'.$rule['key']]['default']=$rule['action_user']; } if(isset($rule['forward'])){ $step2['bounce_rule_'.$rule['key']]['forward']=$rule['forward']; } } $formFields="<ol>";$i=0; $formHelp=&WYSIJA::get("forms","helper"); foreach($step2 as $row =>$colparams){ $formFields.='<li>'; $value=$this->model->getValue($row); if(!$value && isset($colparams['default'])) $value=$colparams['default']; if(isset($colparams['label'])) $label=$colparams['label']; else $label=ucfirst($row); $desc=''; if(isset($colparams['desc'])) $desc='<p class="description">'.$colparams['desc'].'</p>'; $formFields.='<label for="'.$row.'">'.$label.$desc.' </label>'; if(isset($colparams['forward'])){ $valueforward=$this->model->getValue($row."_forwardto"); if($valueforward===false) { $modelU=&WYSIJA::get("user","model"); $modelU->getFormat=OBJECT; $datauser=$modelU->getOne(false,array('wpuser_id'=>get_current_user_id())); $valueforward=$datauser->email; } $formFields.='<input  id="'.$row.'" size="30" type="text" class="bounce-forward-email" name="wysija[config]['.$row."_forwardto".']" value="'.$valueforward.'" />'; }else{ $formFields.=$formHelp->dropdown(array('id'=>$row, 'name'=>'wysija[config]['.$row.']'), $colparams['values'], $value, 'style="width:150px;"'); } $i++; $formFields.='</li>'; } $formFields.="</ol>"; echo $formFields; } function advanced(){ $step=array(); $step['role_campaign']=array( 'type'=>'roles', 'label'=>__('Who can create newsletters?',WYSIJA), 'desc'=>__('These are based on Wordpress user accounts.',WYSIJA)); $step['replyto_name']=array( 'type'=>'input', 'label'=>__('Default reply name',WYSIJA), 'desc'=>__('You can change the default reply-to name and email for your newsletters. This option is also used for the activation emails and Admin notifications (in Basics).',WYSIJA)); $step['replyto_email']=array( 'type'=>'input', 'label'=>__('Default reply email address',WYSIJA), 'desc'=>__('When the subscribers hit "reply", this is where they will send their email.',WYSIJA)); $config=&WYSIJA::get("config","model"); if(!$config->getValue("premium_key")){ $step['bounce_email']=array( 'type'=>'input', 'label'=>__('Bounce Email',WYSIJA)); } $modelU=&WYSIJA::get("user","model"); $modelU->getFormat=OBJECT; $objUser=$modelU->getOne(false,array('wpuser_id'=>get_current_user_id())); $step['unsubscribed_title']=array( 'type'=>'input', 'label'=>__('Unsubscribe page title',WYSIJA), 'desc'=>__('This is the [link]unsubscription confirmation[/link] page a user is directed to after clicking on the unsubscribe link at the bottom of a newsletter.',WYSIJA), 'link'=>'<a href="'.$modelU->getConfirmLink($objUser,"unsubscribe",false,true).'&demo=1" target="_blank" title="'.__("Preview page",WYSIJA).'">'); $step['unsubscribed_subtitle']=array( 'type'=>'input', 'label'=>__('Unsubscribe page content',WYSIJA)); $step['advanced_charset']=array( 'type'=>'dropdown_keyval', 'values'=>array("UTF-8","UTF-7", "BIG5", "ISO-8859-1","ISO-8859-2","ISO-8859-3","ISO-8859-4","ISO-8859-5","ISO-8859-6","ISO-8859-7","ISO-8859-8","ISO-8859-9","ISO-8859-10","ISO-8859-13","ISO-8859-14","ISO-8859-15", "Windows-1251","Windows-1252"), 'label'=>__('Charset',WYSIJA), 'desc'=>__('Squares or weird characters are displayed in your emails?',WYSIJA).' <a href="http://support.wysija.com/knowledgebase/automated-bounce-handling-install-guide/">'.__("Read more.",WYSIJA)."</a>"); $step['debug_on']=array( 'type'=>'debug', 'label'=>__('Debug mode',WYSIJA), 'desc'=>""); ?>
        <table class="form-table">
            <tbody>
                <?php
 echo $this->buildMyForm($step,"","config"); ?>
            </tbody>
        </table>
        <?php
 } } 