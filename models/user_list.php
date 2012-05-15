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
    
    function updateSubscription($subid,$lists){
		/*$result = true;
		$time = time();
		$listHelper = acymailing_get('helper.list');
		$listHelper->sendNotif = $this->sendNotif;
		$listHelper->sendConf = $this->sendConf;
		$listHelper->survey = $this->survey;
		foreach($lists as $status => $listids){
			if(empty($listids)) continue;
			JArrayHelper::toInteger($listids);
			//-1 is unsubscribe
			if($status == '-1') $column = 'unsubdate';
			else $column = 'subdate';
			$query = 'UPDATE '.acymailing_table('listsub').' SET `status` = '.intval($status).','.$column.'='.$time.' WHERE subid = '.intval($subid).' AND listid IN ('.implode(',',$listids).')';
			$this->database->setQuery($query);
			$result = $this->database->query() && $result;
			if($status == 1){
				$listHelper->subscribe($subid,$listids);
			}elseif($status == -1){
				$listHelper->unsubscribe($subid,$listids);
			}
		}
		return $result;*/
	}
	function removeSubscription($subid,$listids){
		/*JArrayHelper::toInteger($listids);
		$query = 'DELETE FROM '.acymailing_table('listsub').' WHERE subid = '.intval($subid).' AND listid IN ('.implode(',',$listids).')';
		$this->database->setQuery($query);
		$this->database->query();
		$listHelper = acymailing_get('helper.list');
		$listHelper->sendNotif = $this->sendNotif;
		$listHelper->unsubscribe($subid,$listids);
		return true;*/
	}
	function addSubscription($subid,$lists){
		/*$app =& JFactory::getApplication();
		$my = JFactory::getUser();
		$result = true;
		$time = time();
		$subid = intval($subid);
		$listHelper = acymailing_get('helper.list');
		foreach($lists as $status => $listids){
			$status = intval($status);
			JArrayHelper::toInteger($listids);
			$this->database->setQuery('SELECT `listid`,`access_sub` FROM '.acymailing_table('list').' WHERE `listid` IN ('.implode(',',$listids).') AND `type` = \'list\'');
			$allResults = $this->database->loadObjectList('listid');
			$listids = array_keys($allResults);
			//-1 is unsubscribe
			if($status == '-1') $column = 'unsubdate';
			else $column = 'subdate';
			$values = array();
			foreach($listids as $listid){
				if(empty($listid)) continue;
				if($status > 0 && acymailing_level(3)){
					if(!$app->isAdmin() && $this->checkAccess && $allResults[$listid]->access_sub != 'all'){
						if(!acymailing_isAllowed($allResults[$listid]->access_sub,$this->gid)) continue;
					}
				}
				$values[] = intval($listid).','.$subid.','.$status.','.$time;
			}
			if(empty($values)) continue;
			$query = 'INSERT INTO '.acymailing_table('listsub').' (listid,subid,`status`,'.$column.') VALUES ('.implode('),(',$values).')';
			$this->database->setQuery($query);
			$result = $this->database->query() && $result;
			if($status == 1){
				$listHelper->subscribe($subid,$listids);
			}
		}
		return $result;*/
	}

}
