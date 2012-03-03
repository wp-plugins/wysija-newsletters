<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_model_user_list extends WYSIJA_model{
    
    var $pk=array("list_id","user_id");
    var $table_name="user_list";
    var $columns=array(
        'list_id'=>array("req"=>true,"type"=>"integer"),
        'user_id'=>array("req"=>true,"type"=>"integer"),
        'sub_date' => array("type"=>"integer"),
        'unsub_date' => array("type"=>"integer")
    );
    
    
    
    function WYSIJA_model_user_list(){
        $this->WYSIJA_model();
    }
    

}
