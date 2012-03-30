<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_update extends WYSIJA_object{
    function WYSIJA_help_update(){
        $this->modelWysija=new WYSIJA_model();
        $this->updates=array("1.1");
    }
    function check(){
        
        $config=&WYSIJA::get("config","model");
        if(!$config->getValue("wysija_db_version") || version_compare($config->getValue("wysija_db_version"),WYSIJA::get_version()) < 0){
            $this->update(WYSIJA::get_version());
        }
    }
    function update($version){
        $config=&WYSIJA::get('config',"model");
        $config->getValue("wysija_db_version");
        foreach($this->updates as $version){
            if(version_compare($config->getValue("wysija_db_version"),$version) < 0){
                if(!$this->runUpdate($version)){
                    $this->error(sprintf(__('Update procedure to Wysija version "%1$s" failed!',WYSIJA),$version),true);
                    return false;
                }else{
                    $config->save(array("wysija_db_version"=>$version));
                    $this->notice(sprintf(__('Update procedure to Wysija version "%1$s" is successful!',WYSIJA),$version));
                }
            }
        }
    }
    function runUpdate($version){


        switch($version){
            case "1.1":
                
                $modelconfig=&WYSIJA::get("config","model");
                if(!$this->modelWysija->query("SHOW COLUMNS FROM `".$this->modelWysija->getPrefix()."list` LIKE 'namekey';")){
                    $querys[]="ALTER TABLE `".$this->modelWysija->getPrefix()."list` ADD `namekey` VARCHAR( 255 ) NULL;";
                }
                $querys[]="UPDATE `".$this->modelWysija->getPrefix()."list` SET `namekey` = 'users' WHERE `list_id` =".$modelconfig->getValue('importwp_list_id').";";
                $errors=$this->runUpdateQueries($querys);
                $importHelp=&WYSIJA::get("import","helper");
                $importHelp->testPlugins();

                $installHelper =& WYSIJA::get('install', 'helper');
                $installHelper->moveData('dividers');
                $installHelper->moveData('bookmarks');
                $installHelper->moveData('themes');
                if($errors){
                    $this->error(implode($errors,"\n"));
                    return false;
                }
                return true;
                break;
            default:
                return false;
        }
        return false;
    }
    
    function runUpdateQueries($queries){
        $failed=array();
        global $wpdb;
        foreach($queries as $query){
            $result=mysql_query($query, $wpdb->dbh);
            if(!$result)    $failed[]=mysql_error($wpdb->dbh)." ($query)";
        }
        if($failed) return $failed;
        else return false;
    }
}
