<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_cron extends WYSIJA_object{
    var $report=false;
    function WYSIJA_help_cron(){
    }
    function run() {
        @ini_set('max_execution_time',0);

        $report=$process=false;
        if(isset($_REQUEST['process']) && $_REQUEST['process']){
            $process=$_REQUEST['process'];
        }elseif(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SHELL']) && isset($_SERVER['argv'][2]) && $_SERVER['argv'][2]){
            $process=$_SERVER['argv'][2];
        }
        if(isset($_REQUEST['report']) && $_REQUEST['report']){
            $this->report=$_REQUEST['report'];
        }elseif(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SHELL']) && isset($_SERVER['argv'][3]) && $_SERVER['argv'][3]){
            $this->report=$_SERVER['argv'][3];
        }
        if($process){
            
            
            if(isset($_REQUEST[WYSIJA_CRON]) || ( isset($_SERVER['argv'][1]) && $_SERVER['argv'][1]==WYSIJA_CRON )) echo '';
            else exit;
            $cron_schedules=get_option('wysija_schedules');
            $processes=array();
            if(strpos($process, ',')!==false){
                $processes=explode(',', $process);
            }else $processes[]=$process;
            foreach($processes as $scheduleprocess){
                if($scheduleprocess!='all'){
                    if($cron_schedules[$scheduleprocess]['next_schedule']<time() && !$cron_schedules[$scheduleprocess]['running']){
                        if($this->report) echo 'exec process '.$scheduleprocess.'<br/>';
                        $this->wysija_exec_process($scheduleprocess);
                    }else{
                       if($this->report) echo 'skip process '.$scheduleprocess.'<br/>';
                    }
                }else{
                    $this->wysija_exec_process('queue');
                    $this->wysija_exec_process('bounce');
                    $this->wysija_exec_process('daily');
                    $this->wysija_exec_process('weekly');
                    $this->wysija_exec_process('monthly');
                    if($this->report) echo 'processed : All'.'<br/>';
                    exit;
                }
            }
        }

        exit;
    }
    function wysija_exec_process($process='queue'){
        $scheduled_times=WYSIJA::get_cron_schedule($process);
        if(isset($scheduled_times['running']) && $scheduled_times['running'] && $scheduled_times['running']+900>time()){
            if($this->report)   echo 'already running : '.$process.'<br/>';
            return;
        }

        WYSIJA::set_cron_schedule($process,0,time());

        switch($process){
            case 'queue':
                WYSIJA::croned_queue($process);
                $hPremium =& WYSIJA::get('premium', 'helper', false, WYSIJANLP);
                if(is_object($hPremium)) $hPremium->splitVersion_croned_queue_process();
                break;
            case 'bounce':
                $hPremium =& WYSIJA::get('premium', 'helper', false, WYSIJANLP);
                if(is_object($hPremium)) $hPremium->croned_bounce();
                break;
            case 'daily':
                WYSIJA::croned_daily();
                break;
            case 'weekly':
                $hPremium =& WYSIJA::get('premium', 'helper', false, WYSIJANLP);
                if(is_object($hPremium)){
                    $hPremium->croned_weekly();
                }
                break;
            case 'monthly':
                WYSIJA::croned_monthly();
                break;
        }

        WYSIJA::set_cron_schedule($process);
        if($this->report) echo 'processed : '.$process.'<br/>';
    }
}
