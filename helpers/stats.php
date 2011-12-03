<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_stats extends WYSIJA_object{
   function sendDailyReport(){
       //know everything that happened in the last 24 hours 
        $onedayago=mktime()-1;
        $onedayago=$onedayago-(3600*24);
        
        $modelEUS=&WYSIJA::get("email_user_stat","model");
        $query="SELECT COUNT(".$modelEUS->getPk().") as count, status FROM `".$modelEUS->getPrefix().$modelEUS->table_name."` 
            WHERE sent_at>".$onedayago."
                GROUP BY status";
        $statuscount=$modelEUS->query("get_res",$query);
        
        $modelUH=&WYSIJA::get("user_history","model");
        $query="SELECT B.user_id,B.email FROM `".$modelUH->getPrefix().$modelUH->table_name."`  as A JOIN `".$modelUH->getPrefix()."user` as B on A.user_id=B.user_id
            WHERE A.executed_at>".$onedayago." AND A.type='bounce'";
        $details=$modelUH->query("get_res",$query);
        $total=0;
        foreach($statuscount as &$count){
            switch($count['status']){
                
                case "-1":
                    $count['status']=__('bounced',WYSIJA);
                    break;
                case "0":
                    $count['status']=__('unopened',WYSIJA);
                    break;
                case "1":
                    $count['status']=__('opened',WYSIJA);
                    break;
                case "2":
                    $count['status']=__('clicked',WYSIJA);
                    break;
                case "3":
                    $count['status']=__('unsubscribed',WYSIJA);
                    break;
            }
            $total=$total+$count['count'];
        }
        $html="<h2>".__("Today's statistics",WYSIJA)."</h2>";
        $html.="<h3>".sprintf(__('Today you have sent %1$s emails',WYSIJA),$total);
        foreach($statuscount as $count){
            $html.=sprintf(__(', %1$s of which were %2$s',WYSIJA),$count['count'],$count['status']);
        }
        $html.=".</h3>";
        if(count($details)>0){
            $html.="<h2>".sprintf(__('Here is the list bounced emails.',WYSIJA),$total)."</h2>";

            foreach($details as $email){
                $html.="<h4>".$email['email']."</h4>";
            }
        }
        $html.="<p>".__("Cheers, your Wysija Newsletter Plugin",WYSIJA)."</p>";
        
        $modelC=&WYSIJA::get("config","model");
        $mailer=&WYSIJA::get("mailer","helper");
        $mailer->testemail=true;
        //$res=$mailer->sendSimple($modelC->getValue('emails_notified'),str_replace("[sitename]",get_option('site'),__("[sitename] Wysija's Daily Report",WYSIJA)),$html);
        $res=$mailer->sendSimple($modelC->getValue('emails_notified'),__("Wysija's Daily Report",WYSIJA),$html);
        
   }
}