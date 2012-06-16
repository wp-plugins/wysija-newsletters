<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_model_wp_posts extends WYSIJA_model{
    
    var $pk="ID";
    var $table_name="posts";
    var $columns=array(
        'ID'=>array("req"=>true,"type"=>"integer"), 
        'post_author'=>array("type"=>"integer"),
        'post_date' => array(), 
        'post_date_gmt' => array(),
        'post_content' => array(),
        'post_title' => array(), 
        'post_excerpt' => array(), 
        'post_status' => array(), 
        'comment_status' => array(), 
        'ping_status' => array(),
        'post_password' => array(),
        'post_name' => array(), 
        'to_ping' => array(), 
        'pinged' => array(), 
        'post_modified' => array(), 
        'post_modified_gmt' => array(),
        'post_content_filtered' => array(),
        'post_parent'=>array("type"=>"integer"), 
        'guid' => array(),
        'menu_order'=>array("type"=>"integer"), 
        'post_type' => array(), 
        'post_mime_type' => array(),
        'comment_count'=>array("type"=>"integer"),
    );
    
    
    
    function WYSIJA_model_wp_posts(){
        $this->WYSIJA_model();
        $this->table_prefix='';
    }
    
    
    function get_posts($args=array()){
        if(!$args) return false;
        $customQuery='';
        
        /*
         * SELECT wp_posts.* FROM wp_posts 
         * INNER JOIN wp_term_relationships 
         * ON (wp_posts.ID = wp_term_relationships.object_id) 
         * WHERE 1=1 AND wp_posts.ID 
         * NOT IN (723,716,712,710,707,699,697,705,702,679,677,674) 
         * AND ( wp_term_relationships.term_taxonomy_id IN (21,22) ) 
         * AND wp_posts.post_type = 'post' 
         * AND (wp_posts.post_status = 'publish') 
         * GROUP BY wp_posts.ID 
         * ORDER BY wp_posts.post_date DESC 
         * LIMIT 0, 2
         * 
         */
        $customQuery='SELECT A.ID, A.post_title, A.post_content FROM `[wp]'.$this->table_name.'` as A ';
        if(isset($args['category']) && $args['category'])
            $customQuery.='JOIN `[wp]term_relationships` as B ON (A.ID = B.object_id) ';
        
        $conditionsOut=$conditionsIn=array();
        
        foreach($args as $col => $val){
            if(!$val) continue;
            switch($col){
                case 'category':
                    $conditionsIn['B.term_taxonomy_id']=array('sign'=>'IN','val' =>$val);
                    break;
                case 'include':
                    $conditionsIn['A.ID']=array('sign'=>'IN','val' =>$val);
                    break;
                case 'exclude':
                    $conditionsIn['A.ID']=array('sign'=>'NOT IN','val' =>$val);
                    break;
                case 'post_type':
                    $conditionsIn['A.post_type']=array('sign'=>'IN','val' =>$val);
                    break;
                case 'post_status':
                    $conditionsIn['A.post_status']=array('sign'=>'IN','val' =>$val);
                    break;
                case 'post_date':
                    //convert the date 
                    $toob=&WYSIJA::get('toolbox','helper');
                    $val= $toob->time_tzed($val);
                    $conditionsIn['A.post_date']=array('sign'=>'>','val' =>$val );
                    break;
                default:
            }
            //$dataGet[]
        }
        
        $customQuery.='WHERE ';

        $customQuery.=$this->setWhereCond($conditionsIn);
        //$customQuery.=' AND '.$this->setWhereCond($conditionsOut);
        
        if(isset($args['orderby'])){
            $customQuery.=' ORDER BY '.$args['orderby'];
            if(isset($args['order'])) $customQuery.=' '.$args['order'];
        }
        
        if(isset($args['numberposts'])){
            $customQuery.=' LIMIT 0,'.$args['numberposts'];
        }

        return $this->query('get_res',$customQuery);
        
    }
    
    
    function setWhereCond($conditionsIn){
        $customQuery='';
        $i=0;
        foreach($conditionsIn as $col => $data){
            if($i>0) $customQuery.=' AND ';
            $customQuery.=$col.' ';
            $valu=$data['val'];
            if(is_array($data['val'])) $valu=implode("','",$data['val']);
            switch($data['sign']){
                case 'IN':
                case 'NOT IN':
                    $customQuery.=$data['sign'].' ('."'".$valu."'".') ';
                    break;

                default:
                    $sign='=';
                    if(isset($data['sign'])) $sign=$data['sign'];
                    $customQuery.=$sign."'".$valu."' ";
            }
            $i++;
        }
        return $customQuery;
    }

}
