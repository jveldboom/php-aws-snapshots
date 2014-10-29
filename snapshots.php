<?php
class snapshots
{
	function __construct($volumes)
	{
		$this->volumes = $volumes;
	}
	
	function run()
	{
		foreach($this->volumes as $volume_id => $options)
		{
			if(!self::setOptions($options)){
				echo 'Volume '.$volume_id.' not ran due to invalid config options'.PHP_EOL;
				continue;
			};
			
			$snapshots = self::getSnapshots(array('volume-id'=>$volume_id,'description'=>$this->options['description']));
			if(!$snapshots) continue;
			
			if(self::shouldCreate($snapshots)){
				self::create($volume_id,$this->options['description']);
			}
			
			// delete extra snapshots base on number of 'snapshots' option
			self::deleteExtra($volume_id);
		}

		return true;
	}
	
	/**
	 * Check if a snapshot should be created based set options (number of snapshots & interval)
	 * @param  object $snapshots list of snapshots return from AWS CLI
	 * @return boolean
	 */
	private function shouldCreate($snapshots)
	{
		// create snapshot if none exist and the option is set for 1 or more
		if(count($snapshots->Snapshots) < 1 && $this->options['snapshots'] > 0) return true;
		
		$interval = (new \DateTime())->modify('-'.$this->options['interval']);
		$last_snapshot = new DateTime(end($snapshots->Snapshots)->StartTime);

		// use same timezones for comparison below
		$interval->setTimezone(new DateTimeZone('EDT'));
		$last_snapshot->setTimezone(new DateTimeZone('EDT'));

		// check if last snapshot is before the interval time-frame
		if($last_snapshot < $interval) return true;
		
		return false;
	}

	/**
	 * Create new EBS snapshot
	 * @param  string $volume_id
	 * @param  string $description
	 * @return string
	 */
	public function create($volume_id,$description='PHP Snapshots')
	{
		$cmd = sprintf('/usr/local/bin/aws ec2 create-snapshot --volume-id %s --description "'.$description.'"',escapeshellarg($volume_id));
		return shell_exec($cmd);
	}

	/**
	 * Delete snapshot
	 * @param  string $snapshot_id
	 * @return string
	 */
	public function delete($snapshot_id)
	{
		$cmd = sprintf('/usr/local/bin/aws ec2 delete-snapshot --snapshot-id %s',escapeshellarg($snapshot_id));
		return shell_exec($cmd);
	}
	
	/**
	 * Delete extra snapshots if $snapshot limit is met
	 * @param  string $volume_id
	 * @return string
	 */
	private function deleteExtra($volume_id)
	{
		$snapshots = self::getSnapshots(array('volume-id'=>$volume_id,'description'=>$this->options['description']));
		$snapshot_count = count($snapshots->Snapshots);
		
		if($snapshot_count <= $this->options['snapshots']) return false;
		
		for($x=0;$x<$snapshot_count - $this->options['snapshots']; ++$x)
		{
			self::delete($snapshots->Snapshots[$x]->SnapshotId);
		}		
	}
	
	/**
	 * Get list of snapshots based on filters
	 * @param  array $filters
	 * @return mixed  json object on true
	 */
	public function getSnapshots($filters=array())
	{
		$cmd_filters = false;
		foreach($filters as $name => $value) $cmd_filters .= 'Name='.escapeshellarg($name).',Values='.escapeshellarg($value).' ';

		$cmd = '/usr/local/bin/aws ec2 describe-snapshots '.($cmd_filters ? '--filters '.trim($cmd_filters) : '');
		$response = shell_exec($cmd);

		$snapshots = json_decode($response);
		if(!$snapshots) return false;

		// sort asc by date
		usort($snapshots->Snapshots, function($a,$b){
			return strtotime($a->StartTime) - strtotime($b->StartTime);
		});

		return $snapshots;
	}

	/**
	 * Sets volume options to current object
	 * @param  array $options
	 * @return boolean
	 */
	private function setOptions($options)
	{
		if(!isset($options['snapshots']) || !isset($options['interval'])) return false;

		$this->options = array(
			'snapshots'   => (int) $options['snapshots'],
			'interval'    => $options['interval'],
			'description' => $options['description'],
		);
		
		return true;
	}
}