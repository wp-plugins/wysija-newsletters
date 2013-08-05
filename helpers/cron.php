<?php
defined('WYSIJA') or die('Restricted access');
class WYSIJA_help_cron extends WYSIJA_object{

    var $report=false;

    function WYSIJA_help_cron(){

    }

    /**
     * the cron tasks are being run for a certain number of processes (all queue, bounce etc..)
     * @return void
     */
    function run() {
        @ini_set('max_execution_time',0);
        $model_config = WYSIJA::get('config','model');
        $running = false;
        if(!$model_config->getValue('cron_manual')){
            return;
        }
        // get the param from where you want
        $report = $process = false;
        if(isset($_REQUEST['process']) && $_REQUEST['process']){
            $process = $_REQUEST['process'];
        }elseif(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SHELL']) && isset($_SERVER['argv'][2]) && $_SERVER['argv'][2]){
            $process = $_SERVER['argv'][2];
        }

        if(isset($_REQUEST['report']) && $_REQUEST['report']){
            $this->report = $_REQUEST['report'];
        }elseif(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SHELL']) && isset($_SERVER['argv'][3]) && $_SERVER['argv'][3]){
            $this->report = $_SERVER['argv'][3];
        }

        if($process){
            //include the needed parts of wp plus wysija
            if(isset($_REQUEST[WYSIJA_CRON]) || ( isset($_SERVER['argv'][1]) && $_SERVER['argv'][1]==WYSIJA_CRON )) echo '';
            else exit;
            $cron_schedules = get_option('wysija_schedules');

            $processes = array();
            if(strpos($process, ',')!==false){
                $processes = explode(',', $process);
            }else $processes[] = $process;

            foreach($processes as $scheduleprocess){
                if($scheduleprocess!='all'){
                    $this->check_scheduled_task($cron_schedules,$scheduleprocess);
                }else{
                    $allProcesses = array('queue','bounce','daily','weekly','monthly');
                    foreach($allProcesses as $processNK){
                        $this->check_scheduled_task($cron_schedules,$processNK);
                    }
                    if($this->report) echo 'processed : All<br/>';
                    if(!isset($_REQUEST['silent'])) echo 'Wysija\'s cron is ready. Simply setup a CRON job on your server (cpanel or other) to trigger this page.';
                    exit;
                }
            }
        }
        if(!isset($_REQUEST['silent'])) echo '"Wysija\'s cron is ready. Simply setup a CRON job on your server (cpanel or other) to trigger this page.' ;
        if($process)    exit;
    }

    /**
     * check that one scheduled task is ready to be executed
     * @param type $cron_schedules list of recorded cron schedules
     * @param type $processNK what to process all, queue, bounce etc...
     */
    function check_scheduled_task($cron_schedules,$processNK){
        $helper_toolbox = WYSIJA::get('toolbox','helper');
        $time_passed = $time_left = 0;
        if($cron_schedules[$processNK]['running']){
            $time_passed = time()-$cron_schedules[$processNK]['running'];
            $time_passed = $helper_toolbox->duration($time_passed,true,2);
        }else{
            $time_left = $cron_schedules[$processNK]['next_schedule']-time();
            $time_left = $helper_toolbox->duration($time_left,true,2);
        }

        if($cron_schedules[$processNK]['next_schedule']<time() && !$cron_schedules[$processNK]['running']){
            if($this->report) echo 'exec process '.$processNK.'<br/>';
            $this->run_scheduled_task($processNK);
        }else{
           if($this->report){
               if($time_passed) $texttime = ' running since : '.$time_passed;
               else  $texttime = ' next run : '.$time_left;
               echo 'skip process <strong>'.$processNK.'</strong>'.$texttime.'<br/>';
           }
        }
    }

    /**
     * run process if it's not detected as already running
     * @param type $process
     * @return type
     */
    function run_scheduled_task($process='queue'){

        // first let's make sure that the process asked to be run is not still   running
        $scheduled_times = WYSIJA::get_cron_schedule($process);
        $processes = WYSIJA::get_cron_frequencies();
        $process_frequency = $processes[$process];
        if(!empty($scheduled_times['running']) && ($scheduled_times['running'] + $process_frequency) > time()){
            if($this->report)   echo 'already running : '.$process.'<br/>';
            return;
        }

        // set schedule as running
        WYSIJA::set_cron_schedule($process,0,time());

        // execute schedule
        switch($process){
            case 'queue':

                // if premium is activated we execute the premium cron process
                if(defined('WYSIJANLP')){
                    $helper_premium = WYSIJA::get('premium', 'helper', false, WYSIJANLP);
                    $helper_premium->croned_queue_process();
                }else{
                    // run the standard queue process
                    WYSIJA::croned_queue($process);
                }
                break;
            case 'bounce':
                // if premium is activated we launch the premium function
                if(defined('WYSIJANLP')){
                    $helper_premium = WYSIJA::get('premium', 'helper', false, WYSIJANLP);
                    $helper_premium->croned_bounce();
                }
                break;
            case 'daily':
                WYSIJA::croned_daily();
                break;
            case 'weekly':
                if(defined('WYSIJANLP')){
                    $helper_premium = WYSIJA::get('premium', 'helper', false, WYSIJANLP);
                    $helper_premium->croned_weekly();
                }
                WYSIJA::croned_weekly();
                break;
            case 'monthly':
                WYSIJA::croned_monthly();
                break;
        }
        // set next_schedule details
        WYSIJA::set_cron_schedule($process);
        if($this->report) echo 'processed : '.$process.'<br/>';
    }
}
