<?php /**/
defined('WYSIJA') or die('Restricted access');
class WYSIJA_view_back_campaigns extends WYSIJA_view_back{

    var $icon="icon-edit-news";
    var $column_action_list="name";
    var $queuedemails=false;
    function WYSIJA_view_back_campaigns(){
        $this->title=__("All Newsletters");
        $this->WYSIJA_view_back();
        $this->jsTrans["selecmiss"]=__('Please select some users first!',WYSIJA);
        $this->search=array("title"=>__("Search newsletters",WYSIJA));
        $this->column_actions=array('editlist'=>__('Edit',WYSIJA),'duplicatelist'=>__('Duplicate',WYSIJA),'deletelist'=>__('Delete',WYSIJA));
    }
   
    function main($data){
        $this->menuTop($this->action);

        echo '<form method="post" action="" id="posts-filter">';
        $this->filtersLink($data);
        $this->filterDDP($data);
        $this->listing($data);
        echo '</form>';
    }
    
    function menuTop($actionmenu=false){
        $arrayTrans=array("back"=>__("Back",WYSIJA),"add"=>__('Create a new newsletter',WYSIJA),"duplicate"=>__('Duplicate',WYSIJA));
        $arrayMenus=false;
        switch($actionmenu){
            case "add":
            case "edit":
                
                break;
            case "main":
                 $arrayMenus=array();
                /*if($this->queuedemails){
                    $arrayTrans["send_test_editor"]=sprintf(__('Send %1$s queued emails right now.',WYSIJA),$this->queuedemails);
                    $arrayMenus[]="send_test_editor"; 
                }*/
                $arrayMenus[]="add";
                break;
            case "viewstats":
                $arrayMenus=array("duplicate");
                break;
            default:
               $arrayMenus=false; 
        }
        $menu="";
        if($arrayMenus){
            foreach($arrayMenus as $action){
                $actionParams=$action;
                if($action=="duplicate"){
                    $actionParams=$action."&id=".$_REQUEST['id'];
                }
                $menu.= '<a id="action-'.str_replace("_","-",$action).'" href="admin.php?page=wysija_campaigns&action='.$actionParams.'" class="action-'.str_replace("_","-",$action).' button-secondary2">'.$arrayTrans[$action].'</a>';
                if($actionmenu=="main" && $action=="add"){
                     $menu.='<span class="description" > '.__("... or duplicate one below to copy its design.",WYSIJA)."</span>";
                }
                
            }
            
            
        }
        
        return $menu;

    }
    
    
    function filterDDP($data){

        ?>
        <ul class="subsubsub">
            <?php 
            $total=count($data['counts']);
            $i=1;
            foreach($data['counts'] as $countType =>$count){
                if(!$count) {$i++;continue;}
                switch($countType){
                    case "all":
                        $tradText=__('All',WYSIJA);
                        break;
                    case "sent":
                        $tradText=__('Sent',WYSIJA);
                        break;
                    case "sending":
                        $tradText=__('Sending',WYSIJA);
                        break;
                    case "draft":
                        $tradText=__('Draft',WYSIJA);
                        break;
                    
                        break;
                    case "paused":
                        $tradText=__('Paused',WYSIJA);
                        break;
                }
                $classcurrent='';
                if((isset($_REQUEST['link_filter']) && $_REQUEST['link_filter']==$countType) || ($countType=='all' && !isset($_REQUEST['link_filter']))) $classcurrent='class="current"';
                echo '<li><a '.$classcurrent.' href="admin.php?page=wysija_campaigns&link_filter='.$countType.'">'.$tradText.' <span class="count">('.$count.')</span></a>';
            
                if($total!=$i) echo ' | ';
                echo '</li>';
                $i++;
            }
            
            ?>
        </ul>

        <?php $this->searchBox(); ?>

        <div class="tablenav">    
            <div class="alignleft actions">
                <select name="filter-date" class="global-filter">
                    <option selected="selected" value=""><?php echo esc_attr(__('Show all months', WYSIJA)); ?></option>
                    <?php 
                     //echo $this->fieldListHTML_created_at($row["created_at"])

                    foreach($data['dates'] as $listK => $list){
                        $selected="";
                        if(isset($_REQUEST['filter-date']) && $_REQUEST['filter-date']== $listK) $selected=' selected="selected" ';
                        echo '<option '.$selected.' value="'.esc_attr($listK).'">'.$list.'</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="alignleft actions">
                <select name="filter-list" class="global-filter">
                    <option selected="selected" value=""><?php _e('View by lists', WYSIJA); ?></option>
                    <?php 
                    
                    foreach($data['lists'] as $listK => $list){
                        $selected="";
                        if(isset($_REQUEST['filter-list']) && $_REQUEST['filter-list']== $listK) $selected=' selected="selected" ';
                       if($list['users']>0) echo '<option '.$selected.' value="'.$list['list_id'].'">'.$list['name'].' ('.$list['users'].')'.'</option>';
                    }
                    ?>
                </select>
                <input type="submit" class="filtersubmit button-secondary action" name="doaction" value="<?php echo esc_attr(__('Filter', WYSIJA)); ?>">
            </div>
            <?php $this->pagination(); ?>
            
            <div class="clear"></div>
        </div>
        <?php
    }
    
    function getTransStatusEmail($status){
        switch($status){
            case "all":
                $tradText=__('All',WYSIJA);
                break;
            case "allsent":
                $tradText=__('All Sent',WYSIJA);
                break;
            case "inqueue":
                $tradText=__('In Queue',WYSIJA);
                break;
            case "notsent":
                $tradText=__('Not Sent',WYSIJA);
                break;
            case "sent":
                $tradText=__('Unopened',WYSIJA);
                break;
            case "opened":
                $tradText=__('Opened',WYSIJA);
                break;
            case "bounced":
                $tradText=__('Bounced',WYSIJA);
                break;
            case "clicked":
                $tradText=__('Clicked',WYSIJA);
                break;
            case "unsubscribe":
                $tradText=__('Unsubscribe',WYSIJA);
                break;
            default:
                $tradText="status : ". $status;
        }
        return $tradText;
    }
    
    function filterDDPVIEW($data){

        ?>
        <ul class="subsubsub">
            <?php 
            
            $total=count($data['counts']);
            $i=1;
            foreach($data['counts'] as $countType =>$count){
                if(!$count || $countType=='all') {$i++;continue;}
                $tradText=$this->getTransStatusEmail($countType);
                $classcurrent='';
                if((isset($_REQUEST['link_filter']) && $_REQUEST['link_filter']==$countType) || ($countType=='allsent' && !isset($_REQUEST['link_filter']))) $classcurrent='class="current"';
                
                echo '<li><a '.$classcurrent.' href="admin.php?page=wysija_campaigns&action=viewstats&id='.$_REQUEST['id'].'&link_filter='.$countType.'">'.$tradText.' <span class="count">('.$count.')</span></a>';
            
                if($total!=$i) echo ' | ';
                echo '</li>';
                $i++;
            }
            
            ?>
        </ul>

        <?php $this->searchBox(); ?>

        <div class="tablenav">    

            <div class="alignleft actions">
                <select name="action2" class="global-action">
                    <option selected="selected" value=""><?php _e('With this segment', WYSIJA); ?></option>
                    <?php
                    if(isset($_REQUEST['link_filter']) && $_REQUEST['link_filter']=='notsent'){
                        /*$config=&WYSIJA::get("config","model");
                        if($config->getValue("confirm_dbleoptin")){
                            ?>
                            <option value="sendconfirmation"><?php _e('Resend the activation email', WYSIJA); ?></option>
                            <?php
                        }*/
                        ?>
                        <option value="removequeue"><?php _e('Remove from the queue', WYSIJA); ?></option>
                        <?php
                    }
                    ?>
                    <option value="createnewlist"><?php _e('Create a new list', WYSIJA); 
                    /*$prefix="";
                    if(isset($_REQUEST['link_filter'])) $prefix="[".$this->getTransStatusEmail($_REQUEST['link_filter'])."]";
                    $listname=sprintf(__('Segment of %1$s'),$prefix.$this->namecampaign);
                    
                    echo " ".$listname*/ ?></option>
                    <option value="unsubscribeall"><?php _e('Unsubscribe from all lists', WYSIJA); ?></option>
                    <?php 
                        foreach($data['lists'] as $listK => $list){
                            if($list['is_enabled'])   echo '<option value="actionvar_unsubscribelist-listid_'.$list['list_id'].'">'.sprintf(__('Unsubscribe from list: %1$s',WYSIJA),$list['name']).' ('.$list['users'].')'.'</option>';
                        }
                    ?>
                    <option value="export"><?php _e('Export to CSV', WYSIJA); ?></option>
                    
                </select>
                <input type="submit" class="bulksubmitcamp button-secondary action" name="doaction" value="<?php echo esc_attr(__('Apply', WYSIJA)); ?>">
            </div>
            <?php $this->pagination(); ?>
            
            <div class="clear"></div>
        </div>
        <?php
    }
    
    /*
     * main view
     */
    function listing($data){    

        ?>
        <div class="list">
            <table cellspacing="0" class="widefat fixed">
                    <thead>
                        <?php 
                            $openedsorting=$statussorting=$namesorting=$datesorting=" sortable desc";
                            $hiddenOrder="";
                            if(isset($_REQUEST["orderby"])){
                                switch($_REQUEST["orderby"]){
                                    case "name":
                                        $namesorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                    case "created_at":
                                        $datesorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                    case "status":
                                        $statussorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                    case "number_opened":
                                        $openedsorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                }
                                $hiddenOrder='<input type="hidden" name="orderby" id="wysija-orderby" value="'.esc_attr($_REQUEST["orderby"]).'"/>';
                                $hiddenOrder.='<input type="hidden" name="ordert" id="wysija-ordert" value="'.esc_attr($_REQUEST["ordert"]).'"/>';
                            }
                            $header='<tr class="thead">
                            <th scope="col" id="campaign-id" class="manage-column column-campaign-id check-column"><input type="checkbox" /></th>
                            <th class="manage-column column-name'.$namesorting.'" id="name" scope="col" style="width:140px;"><a href="#" class="orderlink" ><span>'.__('Name',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>';
                            /*$header.='<th class="manage-column column-fname'.$fnamesorting.'" id="firstname" scope="col" style="width:80px;">'.__('First name',WYSIJA).'</th>
                            <th class="manage-column column-lname'.$lnamesorting.'" id="lastname" scope="col" style="width:80px;">'.__('Last name',WYSIJA).'</th>';*/
                            $header.='<th class="manage-column column-status'.$statussorting.'" id="status" scope="col" style="width:80px;"><a href="#" class="orderlink" ><span>'.__('Status',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>';
                            $header.='<th class="manage-column column-list-names" id="list-list" scope="col">'.__('Lists',WYSIJA).'</th>';
                            $header.='<th class="manage-column column-opened'.$openedsorting.'" id="number_opened" scope="col" style="width:80px;"><a href="#" class="orderlink" ><span>'.__('Open, clicks, unsubscribed',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>';
                            
                            
                            /*$header.='<th class="manage-column column-emails" id="emails-list" scope="col">'.__('Emails',WYSIJA).'</th>
                            <th class="manage-column column-opened" id="opened-list" scope="col">'.__('Opened',WYSIJA).'</th>
                            <th class="manage-column column-clic" id="clic-list" scope="col">'.__('Clicked',WYSIJA).'</th>';*/
                            $header.='<th class="manage-column column-date'.$datesorting.'" id="created_at" scope="col"><a href="#" class="orderlink" ><span>'.__('Created on',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>
                        </tr>';
                            echo $header;
                        ?>
                    </thead>
                    <tfoot>
                        <?php
                        echo $header;
                        ?>
                    </tfoot>

                    <tbody class="list:<?php echo $this->model->table_name.' '.$this->model->table_name.'-list" id="wysija-'.$this->model->table_name.'"' ?>>
                        
                            <?php
                            $listingRows="";
                            $alt=true;
                            
                            $statuses=array("-1"=>__('Sent to %1$s out of %2$s',WYSIJA),"0"=>__("Draft",WYSIJA),"1"=>__('%1$s out of %2$s sent.',WYSIJA),"2"=>__('Sent to %1$s out of %2$s',WYSIJA));

                            foreach($data['campaigns'] as $row){
                                $classRow="";
                                if($alt) $classRow='alternate';
                                if($row["status"]==1) $classRow.=" sending";
                                ?>
                                <tr class="<?php echo $classRow ?>" >
                                
                                    <th scope="col" class="check-column" >
                                        <input type="checkbox" name="wysija[campaign][campaign_id][]" id="campaign_id_<?php echo $row["campaign_id"] ?>" value="<?php echo esc_attr($row["campaign_id"]) ?>" class="checkboxselec" />
                                    </th>
                                    <td class="name column-name">
                                        <strong>
                                        <?php 
                                        if($row["status"]==0){

                                            ?><a href="admin.php?page=wysija_campaigns&id=<?php 
                                            echo $row["campaign_id"] ?>&action=edit" class="row-title"><?php  
                                            echo $row["name"]; ?></a> - <span class="post-state"><?php 
                                            echo $statuses[(int)$row["status"]]; ?></span>

                                        <?php
                                        }else{

                                         if(isset($data['sent'][$row["campaign_id"]]['to']) && $data['sent'][$row["campaign_id"]]['to']>0){
                                              ?><a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=viewstats" class="row-title"><?php  echo $row["name"]; ?></a>
                                        <?php 
                                          }else{
                                              ?><?php  echo $row["name"]; ?>
                                        <?php 
                                          }
                                             
                                        } 
                                        ?></strong>
                                        <div class="row-actions">
                                            
                                                <?php 
                                                $paramsurl=array(
                                                    'wysija-page'=>1,
                                                    'controller'=>"email",
                                                    'action'=>"view",
                                                    'email_id'=>$row["email_id"]
                                                    );

                                                $modelConf=&WYSIJA::get("config","model");
                                                $fullurl=WYSIJA::get_permalink($modelConf->getValue("confirm_email_link"),$paramsurl);

                                                ?><span class="viewnl">
                                                    <a href="<?php echo $fullurl ?>" target="_blank" class="viewnews"><?php _e('View',WYSIJA)?></a>
                                                </span><?php
                                                if($row["status"]==0){
                                                    ?>
                                                   | <span class="edit">        
                                                        <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=editTemplate" class="submitedit"><?php _e('Edit',WYSIJA)?></a>
                                                    </span>
                                                   | <span class="duplicate">
                                                        <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=duplicate" class="submitedit"><?php _e('Duplicate',WYSIJA)?></a>
                                                    </span>
                                                  | <span class="delete">
                                                        <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=delete&_wpnonce=<?php echo $this->secure(array("action"=>"delete","id"=>$row["campaign_id"]),true); ?>" class="submitdelete"><?php _e('Delete',WYSIJA)?></a>
                                                    </span>
                                                        <?php
                                                }else{
                                                    
                                                    if($row["status"]==-1){
                                                        ?>
                                                      | <span class="edit"><a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=editTemplate" class="submitedit"><?php _e('Edit',WYSIJA)?></a></span>
                                                      | <span class="duplicate">
                                                          <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=duplicate" class="submitedit"><?php _e('Duplicate',WYSIJA)?></a>
                                                        </span>
                                                      | <span class="delete">
                                                            <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=delete&_wpnonce=<?php echo $this->secure(array("action"=>"delete","id"=>$row["campaign_id"]),true); ?>" class="submitdelete"><?php _e('Delete',WYSIJA)?></a>
                                                        </span>
                                                       <?php

                                                    }else{
                                                        if(isset($data['sent'][$row["campaign_id"]]['to']) && $data['sent'][$row["campaign_id"]]['to']>0){
                                                           ?>
                                                            
                                                           | <span class="viewstats">
                                                                <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=viewstats" class="submitedit"><?php _e('Stats',WYSIJA)?></a>
                                                            </span>
                                                            
                                                                <?php 
                                                        }
                                                        
                                                        ?>
                                                      | <span class="duplicate">
                                                          <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=duplicate" class="submitedit"><?php _e('Duplicate',WYSIJA)?></a>
                                                      </span>
                                                      | <span class="delete">
                                                            <a href="admin.php?page=wysija_campaigns&id=<?php echo $row["campaign_id"] ?>&action=delete&_wpnonce=<?php echo $this->secure(array("action"=>"delete","id"=>$row["campaign_id"]),true); ?>" class="submitdelete"><?php _e('Delete',WYSIJA)?></a>
                                                        </span>
                                                      <?php
                                                    }
                                                } ?>
                                        </div>
                                    </td>
                                    <td><?php  

                                        switch((int)$row["status"]){
                                            case 2:
                                            case 1:
                                                $sentto=$senttotal=$sentleft=0;
                                                if(isset($data['sent'][$row["campaign_id"]]['to'])) $sentto=$data['sent'][$row["campaign_id"]]['to'];
                                                if(isset($data['sent'][$row["campaign_id"]]['total']))  $senttotal=$data['sent'][$row["campaign_id"]]['total'];
                                                if(isset($data['sent'][$row["campaign_id"]]['left']))  $sentleft=$data['sent'][$row["campaign_id"]]['left'];
                                                $statusdata= sprintf($statuses[$row["status"]],$sentto,$senttotal);
                                                if($sentleft>0){
                                                    $pause= ' | <a href="admin.php?page=wysija_campaigns&id='.$row["campaign_id"].'&action=pause" class="submitedit">'.__("Pause",WYSIJA).'</a>';
                                                    $config=&WYSIJA::get("config","model");
                                                    $premium=$config->getValue('premium_key');
                                                    $subscribers=(int)$config->getValue('total_subscribers');

                                                    if($subscribers<2000 || ($premium && $subscribers>=2000) ){
                                                       /*$schedules=wp_get_schedules();
                                                       $nextbatch=(int)wp_next_scheduled( 'wysija_cron_queue')-mktime();
                                                       $totalestimate=$data['sent'][$row["campaign_id"]]['remaining_time']-($schedules[wp_get_schedule('wysija_cron_queue')]['interval'])+$nextbatch;
                                                       */
                                                        $helperToolbox=&WYSIJA::get("toolbox","helper");
                                                       echo "<p><strong>".sprintf(__('Time remaining: %1$s',WYSIJA),$helperToolbox->duration($data['sent'][$row["campaign_id"]]['remaining_time'],true,4))."</strong><br/>".$statusdata.$pause."</p>";
                                                       echo "<div class='info-stats'><a href='javascript:;' class='moredetails'>".__("Show more details",WYSIJA)."</a>";
                                                       
                                                       if($sentleft>(int)$config->getValue('sending_emails_number')) $nextBatchnumber=(int)$config->getValue('sending_emails_number');
                                                       else  $nextBatchnumber=(int)$sentleft;
                                                       //Next batch of xx emails will be sent in xx minutes. Don't wait & send right now.
                                                       echo "<div class='thedetails'><p>".sprintf(__('Next batch of %1$s emails will be sent in %2$s. ',WYSIJA),$nextBatchnumber,trim($helperToolbox->duration($data['sent'][$row["campaign_id"]]['next_batch'],true,4)));
                                                       echo "<a href='admin.php?page=wysija_campaigns&action=send_test_editor&emailid=".$row["email_id"]."' class='action-send-test-editor' >".__('Don\'t wait & send right now.',WYSIJA)."</a>";
                                                       echo "</div></div>";
                                                    }else{
                                                        echo $statusdata;
                                                        $link= str_replace(
                        array("[link]","[/link]"),
                        array('<a title="'.__('Get Premium now',WYSIJA).'" class="premium-tab" href="javascript:;">','</a>'),
                        __("To resume send [link]Go premium now![/link]",WYSIJA));
                                                         echo '<p>'.$link.'</p>';
                                                    }
                                                    
                                                }else echo $statusdata;
                                                break;
                                            case -1:
                                                echo sprintf($statuses[$row["status"]],$data['sent'][$row["campaign_id"]]['to'],$data['sent'][$row["campaign_id"]]['total']);
                                                echo ' | <a href="admin.php?page=wysija_campaigns&id='.$row["campaign_id"].'&action=resume" class="submitedit">'.__("Resume",WYSIJA).'</a>';
                                                break;
                                            case 0:
                                                echo __('Not sent yet',WYSIJA);//$statuses[$row["status"]];
                                                break;
                                        }

                                    ?></td>
                                    <td><?php if(isset($row["lists"])) echo $row["lists"] ?></td>
                                    <td><?php if(isset($row["stats"])) echo $row["stats"];
                                              else echo $row["number_opened"]." - ".$row["number_clicked"]." - ".$row["number_unsub"]; ?></td>
                                    <td><?php echo $this->fieldListHTML_created_at($row["created_at"]) ?></td>
                                
                                </tr><?php
                                $alt=!$alt;
                            }

                        ?>

                    </tbody>
                </table>
            </div>            
       
            <?php
            echo $hiddenOrder;
    }
  
    
    
    /*
     * main view
     */
    function viewstats($data){    

        $this->menuTop($this->action);
        $this->search['title']=__("Search recipients",WYSIJA);
        ?>
        <div id="wysistats">
            <div id="wysistats1" class="left">
                <div id="statscontainer"></div>
                <h3><?php 
                $helperToolbox=&WYSIJA::get("toolbox","helper");
                $sentwhen=$data['email']['sent_at'];
                if(!$sentwhen)$sentwhen=$data['email']['created_at'];
                if(isset($data['counts']['all']))  echo sprintf(__('%1$s emails sent %2$s ago',WYSIJA),$data['counts']['all'],$helperToolbox->duration($sentwhen));
                else __('No emails have been sent yet.',WYSIJA);
                ?></h3>
            </div>
            <div id="wysistats2" class="left">
                <ul>
                    <?php 
                    foreach($data['charts']['stats'] as $stats){
                        echo "<li>".$stats['name'].":".$stats['number']."</li>";
                    }
                    ?>
                    
                </ul>
            </div>
            <div id="wysistats3" class="left">
                
                    <?php
                $modelC=&WYSIJA::get("config","model");
                if($modelC->getValue("premium_key")){
                    ?>
                    <p class="title"><?php echo __('What got clicked?',WYSIJA);?></p>
                
                    <?php 
                    if(count($data['clicks'])>0){
                        echo  "<ol>";
                        foreach($data['clicks'] as $click){
                            echo "<li>".$click['name']." : ".$click['url']."</li>";
                        }
                        echo  "</ol>";
                    }else  echo __('Nothing yet!',WYSIJA);

                }else{
                    if(count($data['clicks'])>0){
                        echo '<p style="font-size:14px;font-weight:bold;">';
                        echo str_replace(
                                array("[link]","[/link]"),
                                array('<a title="'.__('Just a few clicks. No need to reinstall. Easy.',WYSIJA).'" class="premium-tab" href="javascript:;">','</a>'),
                                __("Detailed view of links and their number of clicks is available in the Premium version. [link]Get it now.[/link]",WYSIJA));
                        echo '</p>';
                    }
                }
                 ?>
                
                

            </div>
            <div class="clear"></div>
        </div>
        <?php
        echo '<form method="post" action="" id="posts-filter">';
        $this->filtersLink($data);
        $this->filterDDPVIEW($data);

        ?>
        <div class="list">
            <table cellspacing="0" class="widefat fixed">
                    <thead>
                        <?php 
                            $umstatussorting=$statussorting=$fnamesorting=$lnamesorting=$usrsorting=$datesorting=" sortable desc";
                            $hiddenOrder="";
                            if(isset($_REQUEST["orderby"])){
                                switch($_REQUEST["orderby"]){
                                    case "email":
                                        $usrsorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                    case "created_at":
                                        $datesorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                    case "ustatus":
                                        $statussorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                    case "umstatus":
                                        $umstatussorting=" sorted ".$_REQUEST["ordert"];
                                        break;
                                }
                                $hiddenOrder='<input type="hidden" name="orderby" id="wysija-orderby" value="'.esc_attr($_REQUEST["orderby"]).'"/>';
                                $hiddenOrder.='<input type="hidden" name="ordert" id="wysija-ordert" value="'.esc_attr($_REQUEST["ordert"]).'"/>';
                            }
                            $header='<tr class="thead">
                            <th class="manage-column column-username'.$usrsorting.'" id="email" scope="col" style="width:140px;"><a href="#" class="orderlink" ><span>'.__('Email',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>';
                            /*$header.='<th class="manage-column column-fname'.$fnamesorting.'" id="firstname" scope="col" style="width:80px;">'.__('First name',WYSIJA).'</th>
                            <th class="manage-column column-lname'.$lnamesorting.'" id="lastname" scope="col" style="width:80px;">'.__('Last name',WYSIJA).'</th>';*/
                            $header.='<th class="manage-column column-umstatus'.$umstatussorting.'" id="umstatus" scope="col" style="width:80px;"><a href="#" class="orderlink" ><span>'.__('Email Status',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>';
                            $header.='<th class="manage-column column-list-names" id="list-list" scope="col">'.__('Lists',WYSIJA).'</th>';
                            $header.='<th class="manage-column column-ustatus'.$statussorting.'" id="ustatus" scope="col" style="width:80px;"><a href="#" class="orderlink" ><span>'.__('Subscriber Status',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>';
                            /*$header.='<th class="manage-column column-emails" id="emails-list" scope="col">'.__('Emails',WYSIJA).'</th>
                            <th class="manage-column column-opened" id="opened-list" scope="col">'.__('Opened',WYSIJA).'</th>
                            <th class="manage-column column-clic" id="clic-list" scope="col">'.__('Clicked',WYSIJA).'</th>';*/
                            $header.='<th class="manage-column column-date'.$datesorting.'" id="created_at" scope="col"><a href="#" class="orderlink" ><span>'.__('Subscribed on',WYSIJA).'</span><span class="sorting-indicator"></span></a></th>
                        </tr>';
                            echo $header;
                        ?>
                    </thead>
                    <tfoot>
                        <?php
                        echo $header;
                        ?>
                    </tfoot>

                    <tbody class="list:<?php echo $this->model->table_name.' '.$this->model->table_name.'-list" id="wysija-'.$this->model->table_name.'"' ?>>
                        
                            <?php
                            $listingRows="";
                            $alt=true;
                            
                            $statuses=array("-1"=>__("Unsubscribed",WYSIJA),"0"=>__("Unconfirmed",WYSIJA),"1"=>__("Subscribed",WYSIJA));
                            $config=&WYSIJA::get("config","model");
                            if(!$config->getValue("confirm_dbleoptin"))  $statuses["0"]=$statuses["1"];

                            
                            $mstatuses=array("-2"=>$this->getTransStatusEmail("notsent"),"-1"=>$this->getTransStatusEmail("bounced"),"0"=>$this->getTransStatusEmail("sent")
                                ,"1"=>$this->getTransStatusEmail("opened"),"2"=>$this->getTransStatusEmail("clicked"),"3"=>$this->getTransStatusEmail("unsubscribe"));
                            //dbg($data,false);
                            foreach($data['subscribers'] as $row){
                                $classRow="";
                                if($alt) $classRow=' class="alternate" ';

                                ?>
                                <tr <?php echo $classRow ?> >
                                    <td class="username column-username">
                                        <?php 
                                        echo get_avatar( $row["email"], 32 );
                                        echo "<strong>".$row["email"]."</strong>";
                                        echo "<p style='margin:0;'>".$row["lastname"]." ".$row["firstname"]."</p>";
                                        
                                        ?>
                                        <div class="row-actions">
                                            <span class="edit">
                                                <a href="admin.php?page=wysija_subscribers&id=<?php echo $row["user_id"] ?>&action=edit" class="submitedit"><?php _e('View stats or edit',WYSIJA)?></a>
                                            </span>
                                        </div>
                                    </td>
                                    <?php /*<td><?php echo $row["firstname"] ?></td>
                                    <td><?php  echo $row["lastname"] ?></td> */ ?>
                                    <td><?php  echo $mstatuses[$row["umstatus"]]; ?></td>
                                    <td><?php if(isset($row["lists"])) echo $row["lists"] ?></td>
                                    <td><?php  echo $statuses[$row["ustatus"]]; ?></td>
                                    <?php /*<td><?php echo $row["emails"] ?></td>
                                    <td><?php echo $row["opened"] ?></td>
                                    <td><?php echo $row["clicked"] ?></td> */?>
                                    <td><?php echo $this->fieldListHTML_created_at($row["created_at"]) ?></td>
                                
                                </tr><?php
                                $alt=!$alt;
                            }

                        ?>

                    </tbody>
                </table>
            </div>            
       
            <?php
            echo $hiddenOrder;
            $this->limitPerPage();
            echo '</form>';
    }
    
    /* when creating a newsletter or when editing as a draft*/
    function add($data=false){
        
        $this->data=$data;
        $step=array();
        $step['subject']=array(
            'type'=>'subject',
            'label'=>__('Subject line',WYSIJA),
            'class'=>'validate[required]',
            'desc'=>__('This is the subject of the email. Be creative since it’s the first thing your subscribers will see.',WYSIJA));
        
        if($this->data['lists']){
            $step['lists']=array(
            'type'=>'lists',
            'class'=>'validate[minCheckbox[1]] checkbox',
            'label'=>__('Lists',WYSIJA),
            'desc'=>__('The list of subscribers which will be used for that campaign.',WYSIJA));
        }
        
        $step['from_name']=array(
            'type'=>'input',
            'class'=>'validate[required]',
            'label'=>__('From name',WYSIJA),
            'desc'=>__('This is name of the sender. ie, yourself or your company.',WYSIJA));
        
        $step['from_email']=array(
            'type'=>'input',
            'class'=>'validate[required]',
            'label'=>__('From email',WYSIJA),
            'desc'=>__('This is the email of the sender. ie, your organization or its president. ',WYSIJA));

        
        if(!isset($msg['browsermsg'])){
        ?>
        <div id="browsernotsupported" class="updated" style="display:none;">
            <?php echo str_replace(
                        array("[/linkchrome]","[/linkff]","[/linkie]","[/linksafari]","[/link_ignore]",
                            "[linkchrome]","[linkff]","[linkie]","[linksafari]","[link_ignore]"),
                        array("</a>","</a>","</a>","</a>","</a>",
                            '<a href="http://www.google.com/chrome/" target="_blank">','<a href="http://www.getfirefox.com" target="_blank">','<a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home" target="_blank">','<a href="http://www.apple.com/safari/download/" target="_blank">','<a class="linkignore browsermsg" href="javascript:;">'),
                        __("Yikes! Your browser might not be supported. Get the latest [linkchrome]Chrome[/linkchrome], [linkff]Firefox[/linkff], [linkie]Internet Explorer[/linkie] or [linksafari]Safari[/linksafari]. It seems to work?[link_ignore]Ignore message[/link_ignore].",WYSIJA));?>
        </div>
        <?php
        }
        ?>
        <form name="step1" method="post" id="campaignstep3" action="" class="form-valid">
            
            <table class="form-table">
                <tbody>                    
                    <?php
                        //dbg($data);
                        echo $this->buildMyForm($step,$data,"email",true);
                    ?>
                </tbody>
            </table>
 
            <?php $this->_savebuttonsecure($data,"savecamp",__("Next step",WYSIJA)); ?>
            
        </form>
        <?php
    }

    function editTemplate($data=false){
        $wjEngine =& WYSIJA::get('wj_engine', 'helper');

        if(isset($data['email']['wj_data'])) {
            $wjEngine->setData($data['email']['wj_data'], true);
        } else {
            $wjEngine->setData();
        }
        if(isset($data['email']['wj_styles'])) {
            $wjEngine->setStyles($data['email']['wj_styles'], true);
        } else {
            $wjEngine->setStyles();
        }
        
        // print "\n\n--------\n\n";
        // echo '<div style="width:900px;margin:0 auto;">';
        // echo $wjEngine->renderEmail();
        // echo '</div>';
        // print "\n\n--------\n\n";
        // exit;
        ?>
            <style type="text/css" id="wj_css">
                <?php echo $wjEngine->renderStyles(); ?>
            </style>

            <!-- BEGIN: Wysija Editor -->
            <?php echo $wjEngine->renderEditor(); ?>
            <!-- END: Wysija Editor -->
            <div id="wysija_default_header" style="display:none;"><?php 
                $defaultData = $wjEngine->getDefaultData();
                echo $wjEngine->renderEditorHeader($defaultData['header']);
            ?></div>
            <div id="wysija_default_footer" style="display:none;"><?php 
                echo $wjEngine->renderEditorFooter($defaultData['footer']);
            ?></div>
            <div id="wysija_widgets_settings" style="display:none;">
                <div class="divider">
                    <?php 
                    $params = unserialize(base64_decode($data['email']['params']));

                    if(is_array($params) and isset($params['divider'])) {
                        $divider = $params['divider'];
                    } else {
                        $divider = $defaultData['widgets']['divider'];
                    }
                    echo $wjEngine->renderEditorBlock(array_merge(array('type' => 'divider', 'no-block' => true), $divider));
                    ?>
                </div>
            </div>
            <!-- BEGIN: Wysija Toolbar -->
            <div id="wysija_toolbar">
                <ul class="tabs">
                    <li class="wjt-content">
                        <a class="selected" href="javascript:;" rel="content"><?php _e("Content",WYSIJA)?></a>
                    </li>
                    <li class="wjt-images"><a href="javascript:;" rel="images"><?php _e("Images",WYSIJA)?></a></li>
                    <li class="wjt-styles"><a href="javascript:;" rel="styles"><?php _e("Styles",WYSIJA)?></a></li>
                    <li class="last wjt-themes"><a href="javascript:;" rel="themes"><?php _e("Themes",WYSIJA)?></a></li>
                </ul>

                <!-- CONTENT BAR -->
                <ul class="wj_content" style="display:block;">
                    <li class="notice"><?php _e('Drag the widgets below into your newsletter.', WYSIJA) ?></li>
                    <li><a class="wysija_item" wysija_type="text"><?php _e('Plain text',WYSIJA) ?></a></li>
                    <li><a class="wysija_item" wysija_type="post"><?php _e('WordPress post',WYSIJA) ?></a></li>
                    <li>
                        <a class="wysija_item" wysija_type="divider" wysija_src="<?php echo $divider['src'] ?>" wysija_width="<?php echo $divider['width'] ?>" wysija_height="<?php echo $divider['height'] ?>"><?php _e('Horizontal line',WYSIJA) ?></a>
                        <a id="wysija_divider_settings" class="wysija_item_settings" href="javascript:;" href2="admin.php?page=wysija_campaigns&action=dividers&tab=dividers&campaignId=<?php echo $_REQUEST['id'] ?>"><img src="<?php echo WYSIJA_URL ?>img/controls/settings.png" alt=""/></a>
                    </li>
                    <li><a class="wysija_item" wysija_type="popup-bookmark"><?php _e('Social bookmarks',WYSIJA) ?></a></li>
                </ul>

                <!-- IMAGES BAR -->
                <div class="wj_images" style="display:none;">
                    <div class="wj_button">
                        <a id="wysija-upload-browse" class="button" href="javascript:;" href2="admin.php?page=wysija_campaigns&action=medias&tab=special_wp_upload&campaignId=<?php echo $_REQUEST['id'] ?>"><?php _e('Add images',WYSIJA) ?></a>
                    </div>
                    
                    <ul id="wj-images-quick" class="clearfix">
                        <?php 
                        //get list images from template
                        $helperImage=&WYSIJA::get('images','helper');
                        $result=$helperImage->getList();

                        $quick_select=unserialize(base64_decode($data['email']['params']));
                        if(!isset($quick_select['quickselection'])) $quick_select['quickselection']="";
                        if($result && !$quick_select['quickselection']) echo $wjEngine->renderImages($result);
                    
                        echo $wjEngine->renderImages($quick_select['quickselection']);
                        ?>
                    </ul>
                    <div id="wj_images_preview" style="display:none;"></div>
                </div>

                <!-- STYLES BAR -->
                <div class="wj_styles" style="display:none;">
                    <form id="wj_styles_form" action="" method="post" accept-charset="utf-8">
                        <?php
                            echo $wjEngine->renderStylesBar();
                        ?>
                    </form>
                </div>
                
                <!-- THEMES BAR -->
                <div class="wj_themes" style="display:none;">
                    <div class="wj_button">
                        <a id="wysija-themes-browse" class="button" href="javascript:;" href2="admin.php?page=wysija_campaigns&action=themes"><?php _e('Install themes',WYSIJA) ?></a>
                        <?php /*<a id="wysija-themes-showcase" class="button" href="javascript:;"><?php xx_e('Showcase your theme',WYSIJA) ?></a> */ ?>
                    </div>
                    <ul id="wj_themes_list" class="clearfix">
                        <?php 
                        //get themes
                        echo $wjEngine->renderThemes();
                        ?>
                    </ul>
                    <div id="wj_themes_preview" style="display:none;"></div>
                </div>
                
                <div id="wysija_notices" style="display:none;"><span id="wysija_notice_msg"></span><img alt="loader" style="display:none;" id="ajax-loading" src="<?php echo WYSIJA_URL ?>img/wpspin_light.gif" /></div>
            </div>
        <!-- END: Wysija Toolbar -->
        <?php
                    $modelU=&WYSIJA::get("user","model");
                    $modelU->getFormat=OBJECT;
                    $datauser=$modelU->getOne(false,array('wpuser_id'=>get_current_user_id()));
                
                ?>
        <p><input type="text" name="receiver-preview" id="preview-receiver" value="<?php echo $datauser->email ?>" /> <a href="javascript:;" id="wj-send-preview" class="button wysija"><?php _e("Send preview",WYSIJA) ?></a></p>
            <p class="submit">
                <?php $this->secure(array('action'=>"saveemail",'id'=>$data['email']['campaign_id'])); ?>
                <input type="hidden" name="wysija[email][email_id]" id="email_id" value="<?php echo esc_attr($data['email']['email_id']) ?>" />
                <input type="hidden" value="saveemail" name="action" />

                <a id="wj_next" class="button-primary wysija" href="admin.php?page=wysija_campaigns&action=editDetails&id=<?php echo $data['email']['campaign_id'] ?>"><?php _e("Next step",WYSIJA) ?></a>
                <?php echo '<a href="admin.php?page=wysija_campaigns&action=edit&id='.$data['email']['campaign_id'].'">'.__('go back to Step 1',WYSIJA).'</a>' ?>
            </p>
        <!-- BEGIN: Wysija Toolbar -->
        <script type="text/javascript" charset="utf-8">
            wysijaAJAX.campaignID = <?php echo $_REQUEST['id'] ?>;

            function saveWYSIJA(callback) {
                wysijaAJAX.task = 'save_editor';
                wysijaAJAX.wysijaData = Wysija.save();
                wysijaAJAX.popTitle = "Save editor";
                WYSIJA_AJAX_POST(callback);
            }
            // auto save on next step click
            $('wj_next').observe('click', function(e) {
                Event.stop(e);
                saveWYSIJA(function() {
                    window.location.href = e.target.href;
                });
            });
            
            function switchThemeWYSIJA(event) {
                // get event target
                var target = (event.currentTarget) ? event.currentTarget : event.srcElement.parentElement;
                
                if(window.confirm("<?php _e('If you confirm the theme switch, it will override your header/footer and styles', WYSIJA) ?>")) {
                    wysijaAJAX.task = 'switch_theme';
                    wysijaAJAX.wysijaData = Object.toJSON(new Hash({theme: $(target).readAttribute('rel')}));
                    wysijaAJAX.popTitle = "Switch theme";
                    WYSIJA_AJAX_POST(function(response) {
                        // set css
                        if(response.responseJSON.result.styles.css != null) {
                            updateStyles(response.responseJSON.result.styles.css);
                        }
                    
                        // update styles form
                        if(response.responseJSON.result.styles.form != null) {
                            // refresh styles form
                            $('wj_styles_form').innerHTML = response.responseJSON.result.styles.form;
                            // init color pickers
                            jscolor.init();
                            // setup styles form and apply styles
                            setupStylesForm();
                            applyStyles();
                        }
                    
                        // set header
                        if(response.responseJSON.result.templates.header != undefined) {
                            $$('.'+Wysija.options.header)[0].replace(response.responseJSON.result.templates.header);
                        }
                        // set footer
                        if(response.responseJSON.result.templates.footer != undefined) {
                            $$('.'+Wysija.options.footer)[0].replace(response.responseJSON.result.templates.footer);
                        }
                        // set divider
                        if(response.responseJSON.result.templates.divider != undefined) {
                            Wysija.setDivider(response.responseJSON.result.templates.divider, response.responseJSON.result.templates.divider_options);
                            Wysija.replaceDividers();
                        }
                        Wysija.init();
                        Wysija.autoSave();
                    });
                    return false;
                }
            }
            
            // auto save
            new Timer(15 * 1000, function(){
              if (this.count > 0) {
                  if(Wysija.doSave == true) {
                      saveWYSIJA(function() {
                          Wysija.doSave = false;
                      });
                  }
              }
            });

            function applyStyles() {
                wysijaAJAX.task = 'save_styles';
                wysijaAJAX.wysijaStyles = Object.toJSON($('wj_styles_form').serialize(true));
                wysijaAJAX.popTitle = "Save styles";
                WYSIJA_AJAX_POST(function(response) {
                    // remove fixed height for each text block
                    $$('.wysija_text').invoke('setStyle', {height:'auto'});
                    
                    // apply new styles
                    updateStyles(response.responseJSON.result.styles);
                });

                return false;
            }
            
            function updateStyles(styles) {
                // remove previous styles
                if($('wj_css') != undefined) $('wj_css').remove();
                
                // append new styles
                var head = document.getElementsByTagName('head')[0],
                    style = document.createElement('style'),
                    rules = document.createTextNode(styles);
                style.type = 'text/css';
                style.id = 'wj_css';
                if(style.styleSheet) style.styleSheet.cssText = rules.nodeValue; else style.appendChild(rules);
                head.appendChild(style);
            }
            
            // auto apply styles change
            function setupStylesForm() {
                $$('#wj_styles_form select, #wj_styles_form input[class!="transparent"]').invoke('observe', 'change', applyStyles);
                $$('#wj_styles_form input[class="transparent"]').invoke('observe', 'click', function() {
                    $(this).previous('input.color')[$F(this) ? 'addClassName' : 'removeClassName']('disabled');
                    applyStyles();
                });
            }
            setupStylesForm();

            function saveIQS(){
                wysijaAJAX.task = 'save_IQS';
                wysijaAJAX.wysijaIMG = Object.toJSON(wysijaIMG);
                WYSIJA_AJAX_POST();
            }
            
            var konami = new Konami();
            konami.code = function() {
                Wysija.flyToTheMoon();
            }
            konami.load();
        </script>
        <!-- END: Wysija Toolbar -->
        <div id="wysija-konami" >
            <div id="wysija-konami-overlay" style="display:none;width:100%; height:100%; position:fixed;top:0;left:0;background-color:#fff;z-index:99998;overflow:hidden;">
                <img id="wysija-konami-bird" src="<?php echo WYSIJA_URL ?>img/wysija_bird.jpg" style="display:none;z-index:99999;position:absolute;top:100px;left:100px;" width="597" height="483" />
            </div>
        </div>
        
        <div id="wysija-divider">
            
        </div>
        <?php
    }
    /* when newsletter has been sent let's see the feedback */
    function editDetails($data=false){

        $this->data=$data;
        $step=array();
        $step['subject']=array(
            'type'=>'subject',
            'label'=>__('Subject line',WYSIJA),
            'class'=>'validate[required]',
            'desc'=>__('This is the subject of the email. Be creative since it’s the first thing your subscribers will see.',WYSIJA));

        if($this->data['lists']){
            $step['lists']=array(
            'type'=>'lists',
            'class'=>'validate[minCheckbox[1]] checkbox',
            'label'=>__('Lists',WYSIJA),
            'desc'=>__('The list of subscribers which will be used for that campaign.',WYSIJA));
        }

        
        
        $step['from_name']=array(
            'type'=>'input',
            'class'=>'validate[required]',
            'label'=>__('From name',WYSIJA),
            'desc'=>__('This is name of the sender. ie, yourself or your company.',WYSIJA));
        
        $step['from_email']=array(
            'type'=>'input',
            'class'=>'validate[required]',
            'label'=>__('From email',WYSIJA),
            'desc'=>__('This is the email of the sender. ie, your organization or its president. ',WYSIJA));

        $step['replyto_name']=array(
            'type'=>'input',
            'class'=>'validate[required]',
            'label'=>__('Reply-to name',WYSIJA),
            'desc'=>__('When the subscribers hit "reply", this is to who they will send their email ',WYSIJA));
        
        $step['replyto_email']=array(
            'type'=>'input',
            'class'=>'validate[required]',
            'label'=>__('Reply-to email',WYSIJA),
            'desc'=>__('When the subscribers hit "reply", this is where they will send their email.',WYSIJA));
        
        $config=&WYSIJA::get("config","model");
        if($config->getValue('premium_key')){
            $step['googletrackingcode']=array(
                'type'=>'input',
                'isparams' => "params",
                'class'=>'',
                'label'=>__('Google Analytics Campaign',WYSIJA),
                'desc'=>__('Give it a name, like "Spring newsletter". Find out how many visits this newsletter generates in GA > Traffic Sources > Campaigns',WYSIJA));
        }
        
        
  
        ?>
        <form name="step3" method="post" id="campaignstep3" action="" class="form-valid">
            
            <table class="form-table">
                <tbody>                    
                    <?php
                    
                        echo $this->buildMyForm($step,$data,"email");
                    
                    ?>
                    
                </tbody>
            </table>
             <?php
                    $modelU=&WYSIJA::get("user","model");
                    $modelU->getFormat=OBJECT;
                    $datauser=$modelU->getOne(false,array('wpuser_id'=>get_current_user_id()));
                
                ?>
                
            <p><input type="text" name="receiver-preview" id="preview-receiver" value="<?php echo $datauser->email ?>" /> <a href="javascript:;" id="wj-send-preview" class="button wysija"><?php _e("Send preview",WYSIJA) ?></a></p>
            <p class="submit">
                <?php $this->secure(array('action'=>"savelast",'id'=>$_REQUEST['id'])); ?>
                <input type="hidden" name="wysija[email][email_id]" id="email_id" value="<?php echo esc_attr($data['email']['email_id']) ?>" />
                <input type="hidden" name="wysija[campaign][campaign_id]" id="campaign_id" value="<?php echo esc_attr($data['email']['campaign_id']) ?>" />
                <input type="hidden" value="savelast" name="action" />
                <?php
                    if($this->data['email']['status']==0){
                        if($this->data['lists']){
                            ?>
                        <input type="submit" value="<?php echo esc_attr(__("Send now",WYSIJA)) ?>" id="submit-send" name="submit-send" class="button-primary wysija"/>
                            <?php
                        }?>
                        <input type="submit" value="<?php echo esc_attr(__("Send later",WYSIJA)) ?>" name="submit-draft" class="button wysija"/>
                        <?php
                    }else{
                        ?>
                        <input type="submit" value="<?php echo esc_attr(__("Save",WYSIJA)) ?>" name="submit-pause" class="button wysija"/>
                
                        <input type="submit" value="<?php echo esc_attr(__("Save and resume send",WYSIJA)) ?>" id="submit-send" name="submit-resume" class="button-primary wysija"/>
                        <?php
                    }
                ?>
                
                <?php echo str_replace(
                        array('[link]','[/link]'),
                        array('<a href="admin.php?page=wysija_campaigns&action=editTemplate&id='.$data['email']['campaign_id'].'">','</a>'),
                        __("or simply [link]go back to design[/link].",WYSIJA)
                        ); ?>
            </p>
        </form>
        <?php
    }
    
    function fieldFormHTML_subject($key,$val,$model,$params){
        $fieldHTML= '';
        $field=$key;


        $formObj=&WYSIJA::get("forms","helper");
        $fieldHTML='<div id="titlediv">
            <div id="titlewrap" style="width:70%;">
                    <input class="'.$params['class'].'" id="title" name="wysija[email][subject]" size="30" type="text" autocomplete="off" value="'.esc_attr($val).'" />
            </div>
        </div>';


        return $fieldHTML;
    }
    
    function fieldFormHTML_lists($key,$val,$model,$params){
        $fieldHTML= '<div class="list-checkbox">';
        $field=$key;
        $valuefield=array();

        if(isset($this->data['campaign_list']) && $this->data['campaign_list']){
            foreach($this->data['campaign_list'] as $list){
                $valuefield[$list['list_id']]=$list;
            } 
        }


        $formObj=&WYSIJA::get("forms","helper");
        foreach($this->data['lists'] as $list){

            $checked=false;
            if(isset($valuefield[$list['list_id']]))    $checked=true;

            $fieldHTML.= '<p><label for="'.$field.$list['list_id'].'">';
            $fieldHTML.=$formObj->checkbox( array('class'=>$params['class'],'alt'=>$list['name'], 'id'=>$field.$list['list_id'],'name'=>"wysija[campaign_list][list_id][]"),$list['list_id'],$checked).$list['name'];
            $fieldHTML.='<input type="hidden" id="'.$field.$list['list_id'].'count" value="'.$list['count'].'" />';
            $fieldHTML.='</label></p>';

        }

        $fieldHTML.="</div>";
        return $fieldHTML;
    }
 
    
    function edit($data){
        $this->menuTop("edit");
        $formid='wysija-'.$_REQUEST['action'];
        
        ?>
        <div id="wysistats">
            <div id="wysistats1" class="left">
                <div id="statscontainer"></div>
                <h3><?php _e(sprintf('%1$s emails received.',$data['user']['emails']),WYSIJA)?></h3>
            </div>
            <div id="wysistats2" class="left">
                <ul>
                    <?php 

                    foreach($data['charts']['stats'] as $stats){
                        echo "<li>".$stats['name'].":".$stats['number']."</li>";
                    }
                        echo "<li>".__('Added',WYSIJA).":".$this->fieldListHTML_created_at($data['user']['details']["created_at"])."</li>";
                    ?>
                    
                </ul>
            </div>
            <div id="wysistats3" class="left">
                <p class="title"><?php echo __(sprintf('Total of %1$d clicks:',count($data['clicks'])),WYSIJA);?></p>
                <ol>
                    <?php 

                    foreach($data['clicks'] as $click){
                        echo "<li>".$click['name']." : ".$click['url']."</li>";
                    }
                     
                    ?>
                    
                </ol>
            </div>
            <div class="clear"></div>
        </div>
        
        <?php
        $this->buttonsave=__('Save',WYSIJA);
        $this->add($data);
    }
    
    function popup_image_data($data){
        echo $this->messages(true);
        if(!$data) $data=$_GET;
        ?>  
        <div style="width:300px;">
        <form method="post" action="" class="image-data-form validate" id="image-data-form">
            <div class="ml-submit">
                <p>
                    <label for="url"><?php _e('Address:', WYSIJA) ?></label>
                    <br />
                    <input type="text" size="40" name="url" value="<?php echo (isset($data['url'])) ? $data['url'] : 'http://' ?>" id="url" />
                </p>
                <p>
                    <label for="alt"><?php _e('Alternative text:', WYSIJA) ?></label>
                    <br />
                    <input type="text" name="alt" value="<?php echo (isset($data['alt'])) ? $data['alt'] : '' ?>" id="alt" />
                    <br /><p class="notice"><?php _e('This text is displayed when email clients block images, which is most of the time.', WYSIJA) ?></p>
                </p>
                <p><input id="image-data-submit" type="submit" name="submit" value="<?php _e('Save',WYSIJA) ?>" /></p>
            </div>
        </form>

        </div>
        <?php
    }
    
    function popup_themes($errors){
        ?><div id="overlay"><img id="loader" src="<?php echo WYSIJA_URL ?>img/wpspin_light.gif" /></div><?php
        echo $this->messages(true);
        ?>  
        <form enctype="multipart/form-data" method="post" action="" class="wrap media-upload-form validate" id="gallery-form">
            <div id="searchview" class="ml-submit searchview">
                <?php /*?>
                <ul>
                    <li><?php _e("Newest",WYSIJA)?></li>
                    <li><a href="javascript:;"><?php _e("Popular",WYSIJA)?></a></li>
                    <li><a href="javascript:;"><?php _e("Premium",WYSIJA)?></a></li>
                    <li><a href="javascript:;"><?php _e("For Sale",WYSIJA)?></a></li>
                </ul>
                <input type="text" id="search-box" name="search" autocomplete="off" />
                <input type="submit" id="sub-search-box" name="submit" value="<?php echo esc_attr(__('Search',WYSIJA));?>" />
                 * <?php */ ?>
                <p>
                    <input type="button" id="sub-theme-box" name="submit" value="<?php echo esc_attr(__('Upload Theme',WYSIJA));?>" class="button-secondary"/>
                    <span id="filter-selection"></span>
                </p>
                <div id="search-results" ></div>
            </div>

            <div id="theme-view" class="ml-submit" ></div>
            
        </form>
        <form enctype="multipart/form-data" method="post" action="" class="wrap media-upload-form validate" id="gallery-form">
            <div id="theme-upload" class="ml-submit" >
                <div class="actions">
                    <a class="button-secondary2 theme-view-back" href="javascript:;"><?php echo __("<< Back",WYSIJA)?></a>
                </div>
                <div class="form">
                <?php 
                    $secure=array('action'=>"themeupload");
                    $this->secure($secure);
                    ?>
                    <p><input type="file" name="my-theme"/>( <?php 
                    $helperToolbox=&WYSIJA::get("toolbox","helper");
                    $data =$helperToolbox->get_max_file_upload();
                    $bytes=$data['maxmegas'];
                                    
                                    echo sprintf(__('total max upload file size : %1$s',WYSIJA),$bytes)?> )</p>
                    <p><label for="overwrite"><input type="checkbox" id="overwrite" name="overwriteexistingtheme" /><?php echo __("If a theme with the same name exists, overwrite it.",WYSIJA); ?></label></p>
                    <p><input type="hidden" name="action" value="themeupload" />
                    <input type="submit" class="button-primary" name="submitter" value="<?php _e("Upload",WYSIJA)?>" /></p>
                </div>
            </div>
        </form>
        <?php
    }
    
    function themeupload(){
        $this->popup_themes(false);
    }
    
    function popup_articles($errors){
        echo $this->messages(true);
        ?>  
       
        <form enctype="multipart/form-data" method="post" action="" class="media-upload-form validate" id="gallery-form">
            <div class="ml-submit">
                <input type="text" id="search-box" name="search" autocomplete="off" />
                <input type="submit" id="sub-search-box" name="submit" value="<?php echo esc_attr(__('Search',WYSIJA));?>" />
                <label id="labelfullarticlesget" for="fullarticlesget">
                    <?php 
                    $modelConfig=&WYSIJA::get("config","model");
                    $checked="";
                    if($modelConfig->getValue("editor_fullarticle")) $checked=' checked="checked" ';
                    ?>
                    <input type="checkbox" name="fullarticles" id="fullarticlesget" <?php echo $checked ?>/>
                    <?php 
                    echo __("Insert entire post, not just excerpt",WYSIJA);
                    ?>
                </label>
            </div>
            <div id="search-results" class="ml-submit"></div>
            
        </form>
        <?php
    }
    
    function popup_dividers($data = array()) {
        echo $this->messages(true);
        // get saved divider in params
        $params = unserialize(base64_decode($data['email']['params']));

        if(isset($params['divider'])) {
            $selected_divider = $params['divider'];
        } else {
            $dividersHelper =& WYSIJA::get('dividers', 'helper');
            $selected_divider = $dividersHelper->getDefault();
        }
        
        ?>
        
        <div class="wysija_popup_content dividers">
            <form enctype="multipart/form-data" method="post" action="" class="" id="dividers-form">
                <ul class="dividers">
                    <li class="clearfix"><a href="javascript:;" class="selected"><img src="<?php echo $selected_divider['src'] ?>" alt="" width="<?php echo $selected_divider['width'] ?>" height="<?php echo $selected_divider['height'] ?>" /></a></li>
                    <?php
                        foreach($data['dividers'] as $divider) {
                            if($divider['src'] === $selected_divider['src']) continue;
                        ?>
                        <li class="clearfix"><a href="javascript:;"><img src="<?php echo $divider['src'] ?>" alt="" width="<?php echo $divider['width'] ?>" height="<?php echo $divider['height'] ?>" /></a></li>
                        <?php
                        }
                    ?>
                </ul>
                <input type="hidden" name="campaign_id" value="<?php echo $data['email']['campaign_id'] ?>" id="campaign_id" />
                <input type="hidden" name="divider_src" value="" id="divider_src" />
                <input type="hidden" name="divider_width" value="" id="divider_width" />
                <input type="hidden" name="divider_height" value="" id="divider_height" />
                <input type="submit" id="dividers-submit" class="button-primary alignright" name="submit" value="<?php echo esc_attr(__('Done',WYSIJA));?>" />
            </form>
        </div>
        
        <?php
    }
    
    function popup_bookmarks($data = array()){
        echo $this->messages(true);
        
        if(empty($data)) {
            $networks = array(
                'facebook' => array(
                    'label' => 'Facebook',
                    'url' => 'http://www.facebook.com/wysija'
                ),
                'twitter' => array(
                    'label' => 'Twitter',
                    'url' => 'http://www.twitter.com/wysija'
                ),
                'google' => array(
                    'label' => 'Google+',
                    'url' => null
                ),
                'linkedin' => array(
                    'label' => 'LinkedIn',
                    'url' => null
                )
            );
            
            // get networks' url from config
            $config=&WYSIJA::get('config',"model");
            $urls = $config->getValue('social_bookmarks');
            
            // set url from config for each network if specified
            foreach($networks as $network => $values) {
                if(isset($urls[$network]) and strlen(trim($urls[$network])) > 0) {
                    $networks[$network]['url'] = $urls[$network];
                }
            }
            
            $data = array(
                'networks' => $networks,
                'size' => 'medium',
                'iconset' => '01'
            );
        }
        
        ?>  
        <div class="wysija_popup_content bookmarks">
            <form enctype="multipart/form-data" method="post" action="" class="" id="bookmarks-form">
                <ul class="networks">
                    <?php
                    $i = 0;
                    foreach($data['networks'] as $key => $network) {
                    ?>
                        <li class="clearfix">
                            <input type="hidden" name="bookmarks-<?php echo($key) ?>-position" value="<?php echo($i++) ?>" />
                            <label for="bookmarks-url-<?php echo($key) ?>"><?php echo($network['label']) ?></label><input type="text" name="bookmarks-<?php echo($key) ?>-url" value="<?php echo htmlentities($network['url']) ?>" id="bookmarks-url-<?php echo($key) ?>" />
                        </li>
                    <?php
                    }
                    ?>
                </ul>
                
                <div class="sizes">
                    <span><?php _e('Size:', WYSIJA) ?></span>
                    <a href="javascript:;" class="small<?php if($data['size'] === 'small') echo ' selected' ?>" rel="small"><?php _e('small', WYSIJA) ?></a>
                    <a href="javascript:;" class="medium<?php if($data['size'] === 'medium') echo ' selected' ?>" rel="medium"><?php _e('medium', WYSIJA) ?></a>
                    <a href="javascript:;" class="large<?php if($data['size'] === 'large') echo ' selected' ?>" rel="large"><?php _e('large', WYSIJA) ?></a>
                    <input type="hidden" name="bookmarks-size" value="<?php echo $data['size'] ?>" id="bookmarks-size" />
                </div>
                
                <ul class="icons"><!-- this will be loaded via ajax --></ul>
                <input type="hidden" name="bookmarks-iconset" value="" id="bookmarks-iconset" />

                <p><input type="submit" id="bookmarks-submit" name="submit" value="<?php echo esc_attr(__("Done",WYSIJA)) ?>" class="button-primary alignright"/></p>
            </form>
            
        </div>
        <?php
    }
    
    function popup_wysija_browse($errors){
        echo $this->messages(true);
        ?><div id="overlay"><img id="loader" src="<?php echo WYSIJA_URL ?>img/wpspin_light.gif" /></div><?php
        global $redir_tab, $type;
        
	$redir_tab = 'wysija_browse';
	media_upload_header();
	$post_id = intval($_REQUEST['post_id']);

        ?>  

        <form enctype="multipart/form-data" method="post" action="" class="media-upload-form validate" id="gallery-form">
            <?php 
            
            $secure=array('action'=>"medias");
            $this->secure($secure); ?>
            
            <div id="media-items">
            <?php echo $this->_get_media_items($post_id, $errors); ?>
                <div class="clear"></div>
            </div>
        </form>
        
        <?php
        $this->_alt_close();
    }
    
    function _alt_close(){
        ?>
        <p><input type="submit" id="close-pop-alt" value="<?php echo esc_attr(__("Done",WYSIJA)) ?>" name="submit-draft" class="button-primary wysija"/></p>
        <?php
    }
    
    function popup_wp_browse($errors){
        echo $this->messages(true);
        ?><div id="overlay"><img id="loader" src="<?php echo WYSIJA_URL ?>img/wpspin_light.gif" /></div><?php
        global $redir_tab, $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types;
        
	$redir_tab = 'wp_browse';

	media_upload_header();
        
        $limit=20;
        
	$_GET['paged'] = isset( $_GET['paged'] ) ? intval($_GET['paged']) : 0;
	if ( $_GET['paged'] < 1 )
		$_GET['paged'] = 1;
	$start = ( $_GET['paged'] - 1 ) * $limit;
	if ( $start < 1 )
		$start = 0;
	add_filter( 'post_limits', create_function( '$a', "return 'LIMIT $start, $limit';" ) );

	list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query();

        ?>
        
        <form enctype="multipart/form-data" method="post" action="" class="media-upload-form validate" id="library-form">

            <div class="tablenav">

                <?php
                $page_links = paginate_links( array(
                        'base' => add_query_arg( 'paged', '%#%' ),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => ceil($wp_query->found_posts / $limit),
                        'current' => $_GET['paged']
                ));

                if ( $page_links )
                        echo "<div class='tablenav-pages'>$page_links</div>";
                ?>

                <br class="clear" />
            </div>


            <?php 
            
            $secure=array('action'=>"medias");
            $this->secure($secure); ?>

            <div id="media-items">
            <?php echo $this->_get_media_items(null, $errors,true); ?>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </form>
        
        <?php
        $this->_alt_close();
    }
    
    
    
    function popup_wp_upload($errors){
        global $redir_tab,$type, $tab;
        
        $redir_tab = 'wp_upload';
        
        media_upload_header();
	$flash_action_url = admin_url('async-upload.php');

	// If Mac and mod_security, no Flash. :(
	$flash = true;
        /*
	if(false !== stripos($_SERVER['HTTP_USER_AGENT'], 'mac') && apache_mod_loaded('mod_security')) {
            $flash = false;
        }*/

	$flash = apply_filters('flash_uploader', $flash);
	$post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;

	$upload_size_unit = $max_upload_size =  wp_max_upload_size();
	$sizes = array( 'KB', 'MB', 'GB' );
	for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
		$upload_size_unit /= 1024;
	if ( $u < 0 ) {
		$upload_size_unit = 0;
		$u = 0;
	} else {
		$upload_size_unit = (int) $upload_size_unit;
	}
        echo $this->messages(true);
        ?><div id="overlay"><img id="loader" src="<?php echo WYSIJA_URL ?>img/wpspin_light.gif" /></div>
        
        <script type="text/javascript">
        //<![CDATA[
        var uploaderMode = 0;
        jQuery(document).ready(function($){
                uploaderMode = getUserSetting('uploader');
                $('.upload-html-bypass a').click(function(){deleteUserSetting('uploader');uploaderMode=0;swfuploadPreLoad();return false;});
                $('.upload-flash-bypass a').click(function(){setUserSetting('uploader', '1');uploaderMode=1;swfuploadPreLoad();return false;});
        });
        //]]>
        </script>
        
        <div id="media-upload-notice">
        <?php if (isset($errors['upload_notice']) ) { ?>
                <?php echo $errors['upload_notice']; ?>
        <?php } ?>
        </div>
        <div id="media-upload-error">
        <?php if (isset($errors['upload_error']) && is_wp_error($errors['upload_error'])) { ?>
                <?php echo $errors['upload_error']->get_error_message(); ?>
        <?php } ?>
        </div>
        <?php
        // Check quota for this blog if multisite
        if ( is_multisite() && !is_upload_space_available() ) {
            echo '<p>' . sprintf( __( 'Sorry, you have filled your storage quota (%s MB).' ), get_space_allowed() ) . '</p>';
            return;
        }

        do_action('pre-upload-ui');

        if ( $flash ) : ?>
        <script type="text/javascript">
        //<![CDATA[
        var swfu;
        SWFUpload.onload = function() {
                var settings = {
                                button_text: '<span class="button"><?php _e('Select Files'); ?><\/span>',
                                button_text_style: '.button { text-align: center; font-weight: bold; font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; font-size: 11px; text-shadow: 0 1px 0 #FFFFFF; color:#464646; }',
                                button_height: "23",
                                button_width: "132",
                                button_text_top_padding: 3,
                                button_image_url: '<?php echo includes_url('images/upload.png?ver=20100531'); ?>',
                                button_placeholder_id: "flash-browse-button",
                                upload_url : "<?php echo esc_attr( $flash_action_url ); ?>",
                                flash_url : "<?php echo includes_url('js/swfupload/swfupload.swf'); ?>",
                                file_post_name: "async-upload",
                                file_types: "<?php echo apply_filters('upload_file_glob', '*.*'); ?>",
                                post_params : {
                                        "post_id" : "<?php echo $post_id; ?>",
                                        "auth_cookie" : "<?php echo (is_ssl() ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE]); ?>",
                                        "logged_in_cookie": "<?php echo $_COOKIE[LOGGED_IN_COOKIE]; ?>",
                                        "_wpnonce" : "<?php echo wp_create_nonce('media-form'); ?>",
                                        "type" : "<?php echo $type; ?>",
                                        "tab" : "<?php echo $tab; ?>",
                                        "short" : "1"
                                },
                                file_size_limit : "<?php echo $max_upload_size; ?>b",
                                file_dialog_start_handler : fileDialogStart,
                                file_queued_handler : fileQueued,
                                upload_start_handler : uploadStart,
                                upload_progress_handler : uploadProgress,
                                upload_error_handler : uploadError,
                                upload_success_handler : WYSIJAuploadSuccess,
                                upload_complete_handler : WYSIJAuploadComplete,
                                file_queue_error_handler : fileQueueError,
                                file_dialog_complete_handler : fileDialogComplete,
                                swfupload_pre_load_handler: swfuploadPreLoad,
                                swfupload_load_failed_handler: swfuploadLoadFailed,
                                custom_settings : {
                                        degraded_element_id : "html-upload-ui", // id of the element displayed when swfupload is unavailable
                                        swfupload_element_id : "flash-upload-ui" // id of the element displayed when swfupload is available
                                },
                                debug: false
                        };
                        swfu = new SWFUpload(settings);
        };
        //]]>
        </script>

        <div id="flash-upload-ui" class="hide-if-no-js">
        <?php do_action('pre-flash-upload-ui'); ?>

                <div>
                <?php _e( 'Choose files to upload' ); ?>
                <div id="flash-browse-button"></div>
                <span><input id="cancel-upload" disabled="disabled" onclick="cancelUpload()" type="button" value="<?php esc_attr_e('Cancel Upload'); ?>" class="button" /></span>
                </div>
                <p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[$u] ); ?></p>
        <?php do_action('post-flash-upload-ui'); ?>
        </div>
        <?php endif; // $flash ?>

        <div id="html-upload-ui">
        <?php do_action('pre-html-upload-ui'); ?>
                <p id="async-upload-wrap">
                <label class="screen-reader-text" for="async-upload"><?php _e('Upload'); ?></label>
                <input type="file" name="async-upload" id="async-upload" /> <input type="submit" class="button" name="html-upload" value="<?php esc_attr_e('Upload'); ?>" /> <a href="#" onclick="try{top.tb_remove();}catch(e){}; return false;"><?php _e('Cancel'); ?></a>
                </p>
                <div class="clear"></div>
                <p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[$u] ); ?></p>
                <?php if ( is_lighttpd_before_150() ): ?>
                <p><?php _e('If you want to use all capabilities of the uploader, like uploading multiple files at once, please upgrade to lighttpd 1.5.'); ?></p>
                <?php endif;?>
        <?php do_action('post-html-upload-ui', $flash); ?>
        </div>
        <?php do_action('post-upload-ui'); ?>
        <div id="media-items">
            
                <div class="clear"></div>
            </div>
        <?php
    }
    
    function _get_media_items( $post_id, $errors, $wpimage=false ) {
            $attachments = array();
            if ( $post_id ) {
                    $post = get_post($post_id);
                    if ( $post && $post->post_type == 'attachment' )
                            $attachments = array($post->ID => $post);
                    else
                            $attachments = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'orderby' => 'ID', 'order' => 'DESC') );
            } else {
		if ( is_array($GLOBALS['wp_the_query']->posts) )
                            foreach ( $GLOBALS['wp_the_query']->posts as $attachment )
                                    $attachments[$attachment->ID] = $attachment;
            }
            
            $selectedImages=$this->_getSelectedImages();
            
            $output = '';
            foreach ( (array) $attachments as $id => $attachment ) {
                if(!$post_id && $attachment->post_parent==$_REQUEST['post_id'])    continue;
                if ( $attachment->post_status == 'trash' )
                            continue;
                    if ( ( $id = intval( $id ) ) && $thumb_details = wp_get_attachment_image_src( $id, 'thumbnail', true ) )
                            $thumb_url = $thumb_details[0];
                    else
                            $thumb_url = false;
                    
                     if ( ( $id = intval( $id ) )) $img_details = wp_get_attachment_image_src( $id, 'full', true );
                     $classname="";

                     if(isset($selectedImages["wp-".$attachment->ID])) $classname=" selected ";
                    $output.='<div class="wysija-thumb image-'.$attachment->ID.$classname.'"><img title="'.$attachment->post_title.'" alt="'.$attachment->post_title.'" src="'.$thumb_url.'" class="thumbnail" />';
                    if(!$wpimage)    $output.='<span class="delete-wrap"><span class="delete del-attachment">'.$attachment->ID.'</span></span>';
                    $output.='<span class="identifier">'.$attachment->ID.'</span>
                        <span class="width">'.$img_details[1].'</span>
                        <span class="height">'.$img_details[2].'</span>
                        <span class="url">'.$attachment->guid.'</span>
                        <span class="thumb_url">'.$thumb_url.'</span></div>';
            }
            if(!$output) $output="<em>".__("This tab will be filled with images from your current and previous newsletters.",WYSIJA)."</em>";
            return $output;
    }
    
    function _getSelectedImages() {
        $modelEmail=&WYSIJA::get("email","model");
        $email=$modelEmail->getOne(false,array("campaign_id"=>$_REQUEST['campaignId']));

        $array= unserialize(base64_decode($email['params']));
        return $array['quickselection'];
    }
    
    
}