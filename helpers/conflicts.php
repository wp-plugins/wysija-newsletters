<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_conflicts extends WYSIJA_object{
    var $cleanHooks=array();
    function WYSIJA_help_conflicts(){
    }
    
    function resolve($conflictingPlugins){
        $this->whatToClean=array();
        foreach($conflictingPlugins as $keyPlg =>$plugin){
            foreach($plugin['clean'] as $action => $details){
                foreach($details as $priority =>$info){
                    $this->cleanHooks[$action][$priority][]=$info;
                }
            }
        }
        foreach($this->cleanHooks as $hookToclean => $info){
            switch($hookToclean){
               case "admin_head":
                   add_action("admin_init",array($this,"remove_admin_head"),999);
                   break;
               default:
            }
        }
    }
    
    function remove_admin_head(){
        $this->remove_actions('admin_head');
    }
    function remove_actions($actionsToClear){
        global $wp_filter;
        foreach($wp_filter[$actionsToClear] as $priority => $callbacks){
            if(!isset($this->cleanHooks[$actionsToClear][$priority]))   continue;
            foreach($callbacks as $identifier => $arrayInfo){
                if(is_array($arrayInfo['function'])){
                    foreach($arrayInfo['function'] as $id =>$myobject){
                        foreach($this->cleanHooks[$actionsToClear][$priority] as $infoCLear){
                            if(isset($infoCLear["objects"]) && is_object($myobject) && in_array(get_class($myobject),$infoCLear["objects"])){
                                unset($wp_filter[$actionsToClear][$priority][$identifier]);
                            }
                        }
                    }
                }else{
                    foreach($this->cleanHooks[$actionsToClear][$priority] as $infoCLear){
                        if(isset($infoCLear["functions"]) && function_exists($arrayInfo['function']) && in_array($arrayInfo['function'],$infoCLear["functions"])){
                            unset($wp_filter[$actionsToClear][$priority][$identifier]);
                        }
                    }
                }
            }
        }
    }
}
