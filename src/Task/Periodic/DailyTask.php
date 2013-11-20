<?php

namespace CuteDaemon\Task\Periodic;

use CuteDaemon\Task\BaseTask;

/**
 * RegularTask, task need to run at a established time. 
 * like daily task, monthly task, etc.
 *
 * @author Pengfei Li
 */
class DailyTask extends BaseTask{
	/**
	 * The next time to run the task. Unix timestamp format.
	 */
	private $timeToRun;

	private $configTime = "03:00";
	private $configRepeatEnable = array("All");
	private $configRepeatDisable = array();

	private static $validWeek = array(
			"Mon","Tue","Web","Thu","Fri","Sat","Sun"
			);

	private static $validMonth = array(
			"Jan","Feb","Mar","Apr","May","Jun",
			"Jul","Aug","Sep","Oct","Nov","Dec"
			);
	private static $repeats = array(
			"Mon","Tue","Wed","Thu","Fri","Sat","Sun",
			"Jan","Feb","Mar","Apr","May","Jun",
			"Jul","Aug","Sep","Oct","Nov","Dec",
			"AllWeek","AllMonth","All",
			);

	private function calculateTimeToRun(){
		$todayRun = strtotime($this->configTime);
		if($todayRun > time()){
			$this->timeToRun = $todayRun;
		} else {
			$this->timeToRun = (int)$todayRun + 86400;
		}
	}

	private function checkD($time){
		$strD = date('D', $time);	
		return !in_array($strD, $this->configRepeatDisable)
				&& in_array($strD, $this->configRepeatEnable);
	}

	private function checkM($time){
		$strM = date('M', $time);
		return !in_array($strM, $this->configRepeatDisable)
				&& in_array($strM, $this->configRepeatEnable);
	}

	/**
	 * Check is the string a valid time like "23:59:59"
	 */
	private function isValidTime($time){
		return preg_match("/^([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $time);	
	}

	private function isValidRepeat($item){
		return in_array($item, self::$repeats);
	}

	private function generateAllInRepeat($repeats){
		if(in_array('All', $repeats)){
			$repeats = array_merge(self::$validMonth, self::$validWeek); 
		}
		if(in_array('AllMonth', $repeats)){
			$repeats = array_merge(
					array_diff($repeats, array('AllMonth')),
					self::$validMonth
					);
		}
		if(in_array('AllWeek', $repeats)){
			$repeats = array_merge(
					array_diff($repeats, array('AllWeek')),
					self::$validWeek
					);
		}
		return $repeats;
	}

	/**
	 * Perpare the script to run, initialize the settings from config.ini
	 */
	public function initialize($taskFile){
		$this->setTaskFrom($taskFile);
		$this->setScript($this->taskFrom);
		
		$configFile = dirname($taskFile)).'/config.ini';
		$taskFileName = basename($taskFile, '.php');
		$this->taskName = 'RegularTask::'.$taskFileName;

		$config = parse_ini_file($configFile, TRUE);
		if(isset($config[$taskFileName]['time'])
			&& $this->isValidTime($config[$taskFileName]['time'])){
			$this->configTime = $config[$taskFileName]['time'];
		}
		if(isset($config[$taskFileName]['repeat_enable'])){
			$this->configRepeatEnable = 
					$this->generateAllInRepeat(array_filter(
						explode(',', $config[$taskFileName]['repeat_enable']),
						array($this, 'isValidRepeat')));
		} else {
			$this->configRepeatEnable = $this->generateAllInRepeat(array('All'));
		}
		if(isset($config[$taskFileName]['repeat_disable'])){
			$this->configRepeatDisable = 
					$this->generateAllInRepeat(array_filter(
						explode(',', $config[$taskFileName]['repeat_disable']),
						array($this, 'isValidRepeat')));
		}

		$this->calculateTimeToRun();
	}

	/**
	 * Check whether it is the time to run the task.
	 *
	 * @return boolean.
	 */
	public function isTimeToWakeUp(){
		$now = time();
		if($this->checkD($now) && $this->checkM($now)
			&& $now >= $this->timeToRun){
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * After the run method was called, count down the timesneed,
	 * set lastRun.
	 * 
	 * @return void.
	 */
	public function afterRun(){
		$this->calculateTimeToRun();
	}
}
