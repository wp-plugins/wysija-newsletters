<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_campaigns extends WYSIJA_object{
        
    function WYSIJA_help_campaigns(){
    }
    function saveParameters($campaign_id, $key, $value)
    {

        $modelEmail =& WYSIJA::get('email', 'model');
        $campaign = $modelEmail->getOne('params', array('campaign_id' => $campaign_id));
        $params = unserialize(base64_decode($campaign['params']));
        if(!is_array($params)) {
            $params = array();
        }

        if(array_key_exists($key, $params)) {
            $params[$key] = $value;
        } else {
            $params = array_merge($params, array($key => $value));
        }

        return $modelEmail->update(array('params' => $params), array('campaign_id' => $campaign_id));
    }
}