<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_toolbox extends WYSIJA_object{
    function WYSIJA_help_toolbox(){
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
    function wp_get_editable_roles() {
        global $wp_roles;
        $all_roles = $wp_roles->roles;
        $editable_roles = apply_filters('editable_roles', $all_roles);
        $possible_values=array();
        foreach ( $all_roles as $role => $details ) {
            $name = translate_user_role($details['name'] ); 
            switch($role){
                case 'administrator':
                    $keyrole='switch_themes';
                    break;
                case 'editor':
                    $keyrole='moderate_comments';
                    break;
                case 'author':
                    $keyrole='upload_files';
                    break;
                case 'contributor':
                    $keyrole='edit_posts';
                    break;
                case 'subscriber':
                    $keyrole='read';
                    break;
                default:
                    $keyrole=$role;
            }
            $possible_values[$keyrole]=$name;

        }
        return $possible_values;
    }
    function get_max_file_upload(){
        $u_bytes = ini_get( 'upload_max_filesize' );
        $p_bytes = ini_get( 'post_max_size' );
        $data=array();
        $data['maxbytes']=$this->return_bytes(min($u_bytes, $p_bytes));
        $data['maxmegas'] = apply_filters( 'upload_size_limit', min($u_bytes, $p_bytes), $u_bytes, $p_bytes );
        $data['maxchars'] =(int)floor(($p_bytes*1024*1024)/200);
        return $data;
    }
    
    function send_test_mail($values,$send_method=false){
        $content_email=__("Yup, it works. You can start blasting away emails to the moon.",WYSIJA);
        if(!$send_method){
            switch($values["sending_method"]){
                case "site":
                    if($values["sending_emails_site_method"]=="phpmail") $send_method="PHP Mail";
                    else $send_method="Sendmail";
                    if($values["sending_emails_site_method"]=="phpmail"){
                        $send_method="PHP Mail";
                    }else{
                        $sendmail_path=$_POST['data']['wysija[config][sendmail_path]'];
                        $send_method="Sendmail";
                    }
                    break;
                case "smtp":
                    $smtp=array();
                    $send_method="SMTP";
                    break;
                case "gmail":
                    $send_method="Gmail";
                    $values['smtp_host']='smtp.gmail.com';
                    $values['smtp_port']='465';
                    $values['smtp_secure']='ssl';
                    $values['smtp_auth']=true;
                    $content_email=__("You're all setup! You've successfully sent with Gmail.",WYSIJA)."<br/><br/>";
                    $content_email.=str_replace(
                            array('[link]','[/link]'),
                            array('<a href="http://support.wysija.com/knowledgebase/send-with-smtp-when-using-a-professional-sending-provider/" target="_blank" title="SendGrid partnership">','</a>'),
                            __("Looking for a faster method to send? [link]Read more[/link] on sending with a professional SMTP.",WYSIJA));
                    break;
            }
        }
        $mailer=&WYSIJA::get("mailer","helper");
        $mailer->WYSIJA_help_mailer("",$values);
        
        global $current_user;

        $mailer->testemail=true;
        $mailer->wp_user=&$current_user->data;
        $res=$mailer->sendSimple($current_user->data->user_email,str_replace("[send_method]",$send_method,__("[send_method] works with Wysija",WYSIJA)),$content_email);
        
        if($res){
            $this->notice(sprintf(__("Test email successfully sent to <b><i>%s</i></b>",WYSIJA),$current_user->data->user_email));
            return true;
        }else{
            $config=&WYSIJA::get("config","model");
            $bounce = $config->getValue('bounce_email');
            if(in_array($config->getValue('sending_method'),array('smtp','gmail')) && $config->getValue('smtp_secure')=='ssl' && !function_exists('openssl_sign')){
                $this->error(__('The PHP Extension openssl is not enabled on your server. Ask your host to enable it if you want to use an SSL connection.',WYSIJA));
            }elseif(!empty($bounce) AND !in_array($config->getValue('sending_method'),array('smtp_com','elasticemail'))){
                $this->error(sprintf(__('The bounce email address "%1$s" might actually cause the problem. Leave the field empty and try again.',WYSIJA),$bounce));

            }elseif(in_array($config->getValue('sending_method'),array('smtp','gmail')) AND !$config->getValue('smtp_auth') AND strlen($config->getValue('smtp_password')) > 1){
                $this->error(__("You specified an SMTP password but you don't require an authentification, you might want to turn the SMTP authentification ON.",WYSIJA));

            }elseif((strpos(WYSIJA_URL,'localhost') || strpos(WYSIJA_URL,'127.0.0.1')) && in_array($config->getValue('sending_method'),array('sendmail','qmail','mail'))){
                $this->error(__('Your localhost may not have a mail server. To verify, please log out and click on the "Lost your password?" link on the login page. Do you receive the reset password email from your WordPress?',WYSIJA));
            }
            $this->error($mailer->reportMessage);
            return false;
        }
    }
    
    function temp($content,$key="temp",$format=".tmp"){
        $helperF=&WYSIJA::get("file","helper");
        $tempDir=$helperF->makeDir();
        
        $filename=$key."-".mktime().$format;
        $handle=fopen($tempDir.$filename, "w");
        fwrite($handle, $content);
        fclose($handle);
        return array('path'=>$tempDir.$filename,'name'=>$filename, 'url'=>$this->url($filename,"temp"));
    }
    
    function url($filename,$folder="temp"){
        $upload_dir = wp_upload_dir();
        if(file_exists($upload_dir['basedir'].DS."wysija")){
            $url=$upload_dir['baseurl']."/wysija/".$folder."/".$filename;
        }else{
            $url=$upload_dir['baseurl']."/".$filename;
        }
        return $url;
    }
    
    function send($path){
        
        if(file_exists($path)){
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="export_wysija.csv"');
            readfile($path);
            exit();
        }else $this->error(__('File does not exists.',WYSIJA),true);
    }
    
    function clear(){
        $foldersToclear=array("import","temp");
        $filenameRemoval=array("import-","export-");
        $deleted=array();
        $helperF=&WYSIJA::get("file","helper");
        foreach($foldersToclear as $folder){
            $path=$helperF->getUploadDir($folder);
            
            $files = scandir($path);
            foreach($files as $filename){
                if(!in_array($filename, array('.','..',".DS_Store","Thumbs.db"))){
                    if(preg_match('/('.implode($filenameRemoval,'|').')[0-9]*\.csv/',$filename,$match)){
                       $deleted[]=$path.$filename;
                    }
                }
            }
        }
        foreach($deleted as $filename){
            if(file_exists($filename)){
                unlink($filename);
            }
        }
    }
    function excerpt($text,$num_words=8,$more=" ..."){
        $words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY ); 
        if ( count( $words_array ) > $num_words ) {
                array_pop( $words_array );
                $text = implode( ' ', $words_array );
                $text = $text . $more;
        } else {
                $text = implode( ' ', $words_array );
        }
        return  $text;
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
            
            $timestamp = $e - $s;
        }else{
            $timestamp = $t - $s;
        }

        
        $years=floor($timestamp/(60*60*24*365));$timestamp%=60*60*24*365;
        $weeks=floor($timestamp/(60*60*24*7));$timestamp%=60*60*24*7;
        $days=floor($timestamp/(60*60*24));$timestamp%=60*60*24;
        $hrs=floor($timestamp/(60*60));$timestamp%=60*60;
        $mins=floor($timestamp/60);
        if($timestamp>60)$secs=$timestamp%60;
        else $secs=$timestamp;

        
        $str="";
        $mylevel=0;
        if ($mylevel<$level && $years >= 1) { $str.= sprintf(_n( '%1$s year', '%1$s years', $years, WYSIJA ),$years)." ";$mylevel++; }
        if ($mylevel<$level && $weeks >= 1) { $str.= sprintf(_n( '%1$s week', '%1$s weeks', $weeks, WYSIJA ),$weeks)." ";$mylevel++; }
        if ($mylevel<$level && $days >= 1) { $str.=sprintf(_n( '%1$s day', '%1$s days', $days, WYSIJA ),$days)." ";$mylevel++; }
        if ($mylevel<$level && $hrs >= 1) { $str.=sprintf(_n( '%1$s hour', '%1$s hours', $hrs, WYSIJA ),$hrs)." ";$mylevel++; }
        if ($mylevel<$level && $mins >= 1) { $str.=sprintf(_n( '%1$s minute', '%1$s minutes', $mins, WYSIJA ),$mins)." ";$mylevel++; }
        if ($mylevel<$level && $secs >= 1) { $str.=sprintf(_n( '%1$s second', '%1$s seconds', $secs, WYSIJA ),$secs)." ";$mylevel++; }
        return $str;
    }
}
