<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_toolbox extends WYSIJA_object{
    
    function WYSIJA_help_toolbox(){
        
    }
    
    /**
     * Get the full path of a file
     * @param type $csvfilename
     * @param type $folder
     * @return boolean 
     */
    function send_test_mail($values,$send_method=false){
        if(!$send_method){
            switch($values["sending_method"]){
            case "site":
                if($values["sending_emails_site_method"]=="phpmail") $send_method="PHP Mail";
                else $send_method="Sendmail";
                break;
            case "smtp":
                $send_method="SMTP";
                break;
            case "gmail":
                $send_method="Gmail";
                break;
            }
        }
        
        $mailer=&WYSIJA::get("mailer","helper");
        $mailer->WYSIJA_help_mailer("",$values);
        $modelU=&WYSIJA::get("user","model");
        $modelU->getFormat=OBJECT;
        $datauser=$modelU->getOne(false,array('wpuser_id'=>get_current_user_id()));
        $mailer->testemail=true;
        //$mailer->report=false;
        $res=$mailer->sendSimple(
                $datauser->email,str_replace("[send_method]",$send_method,
                        __("[send_method] works with Wysija",WYSIJA)),
                __("Yup, it works. You can start blasting away emails to the moon.",WYSIJA));

        
        if($res){
            $this->notice(sprintf(__("Test email successfully sent to <b><i>%s</i></b>",WYSIJA),$datauser->email));
            return true;
        }else{
            $config=&WYSIJA::get("config","model");
            $bounce = $config->getValue('bounce_email');
            if(in_array($config->getValue('sending_method'),array('smtp','gmail')) && $config->getValue('smtp_secure')=='ssl' && !function_exists('openssl_sign')){
                $this->error(__('The PHP Extension openssl is not enabled on your server, this extension is required to use an SSL connection, please enable it.',WYSIJA));
            }elseif(!empty($bounce) AND !in_array($config->getValue('sending_method'),array('smtp_com','elasticemail'))){
                $this->error(sprintf(__('The specified bounce e-mail address "%1$s" might cause the problem, please delete it (leave the field bounce address empty) and try again.',WYSIJA),$bounce));
            //Case 2 : you are using SMTP but you didn't add an authentification
            }elseif(in_array($config->getValue('sending_method'),array('smtp','gmail')) AND !$config->getValue('smtp_auth') AND strlen($config->getValue('smtp_password')) > 1){
                $this->error(__("You specified an SMTP password but you don't require an authentification, you might want to turn the SMTP authentification ON.",WYSIJA));
            //Case 3 : you are on localhost!
                
            }elseif((strpos(WYSIJA_URL,'localhost') || strpos(WYSIJA_URL,'127.0.0.1')) && in_array($config->getValue('sending_method'),array('sendmail','qmail','mail'))){
                $this->error(__('Your local website may not have a mail server. Please make sure you can send e-mails with Wordpress first (password request, registration confirmation...).',WYSIJA));
            }

            $this->error($mailer->reportMessage);
            return false;
        }
    }
    
    /**
     * make a temporary file
     * @param type $content
     * @param type $key
     * @param type $format
     * @return type 
     */
    function temp($content,$key="temp",$format=".tmp"){
        $helperF=&WYSIJA::get("file","helper");
        $tempDir=$helperF->makeDir();
        
        
        $filename=$key."-".mktime().$format;
        $handle=fopen($tempDir.$filename, "w");
        fwrite($handle, $content);
        fclose($handle);
        
        return array('path'=>$tempDir.$filename,'name'=>$filename, 'url'=>$this->url($filename,"temp"));
    }
    
    /**
     * Get the url of a wysija file based on the filename and the wysija folder
     * @param type $filename
     * @param type $folder
     * @return string 
     */
    function url($filename,$folder="temp"){
        $upload_dir = wp_upload_dir();

        if(file_exists($upload_dir['basedir'].DS."wysija")){
            $url=$upload_dir['baseurl']."/wysija/".$folder."/".$filename;
        }else{
            $url=$upload_dir['baseurl']."/".$filename;
        }
        return $url;
    }
    
    /**
     * send file to be downloaded
     * @param type $path 
     */
    function send($path){
        /* submit the file to the admin */
        if(file_exists($path)){
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="export_wysija.csv"');
            readfile($path);
            exit();
        }else $this->error(__('File does not exists.',WYSIJA),true);
        
    }
    
    /*
     * 
     */
    function clear(){
        $foldersToclear=array("import","temp");
        $filenameRemoval=array("import-","export-");
        $deleted=array();
        $helperF=&WYSIJA::get("file","helper");
        foreach($foldersToclear as $folder){
            $path=$helperF->getUploadDir($folder);
            /* get a list of files from this folder and clear them */
            
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
}

