Create automatic AWS EC2 snapshots with limits on the total number of snapshots created and the interval which the snapshots are created.

For example, you could create a snapshot every day and only keep the last 7 for a running week's worth of snapshots. Or create a snapshot once a week and only keep the last 4 so you would have a running month's worth of snapshots.


## Requirements
- [AWS CLI](http://aws.amazon.com/cli/)
- AWS IAM snapshot permissions ([example policy](#example-iam-policy))
- PHP 5.3+
- Access to crontab (or some other job scheduler)

## Setup
This assumes you've already installed and setup [AWS CLI](http://aws.amazon.com/cli/) and added the correct IAM permissions within your AWS console.

### 1. Create PHP file to load class and hold snapshot configuration
```php
<?php
require_once('snapshots.php');
$volumes = array(
   'vol-123af85a' => array('snapshots' => 7, 'interval' => '1 day', 'description' => 'dev server backup'),
   'vol-321bg96c' => array('snapshots' => 4, 'interval' => '1 week', 'description' => 'image server'),
);
$snapshots = new snapshots($volumes);
$snapshots->run();
```
### 2. Add cron job
The cron job schedule will depend on your configuration. The class honors the interval setting, but you may not want it to run every minute of every day when you just need a nightly backup.
```bash
# run every night at 3:00 am
00	03	* * * /usr/bin/php /root/scripts/run-snapshots.php
```

## Volume Configuration

| Name | Type | Description |
|------|------|-------------|
| *volume id* | string | AWS EBS volume ID
| snapshots | integer | total number of snapshots to store for volume |
| interval | string | how often to create snapshot (1 day, 7 days, 2 weeks - full list below)
| description | string | snapshot description that shows in the Snapshot section within AWS console |

### Interval Values
The format **must** be `number type`

Valid types:
- hour, hours
- day, days
- week, weeks
- month, months
- year, years

Not practical but you *could* also us
- second, seconds
- minute, minutes

## Example IAM Policy
This is a minimal policy that includes ONLY the permissions needed to work. You could also limit the "Resources" option to restrict it even further.
```
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ec2:CreateSnapshot",
        "ec2:DeleteSnapshot",
        "ec2:DescribeSnapshots"
      ],
      "Resource": [
        "*"
      ]
    }
  ]
}
```

## Fresh install on Ubuntu 14.04
```
sudo apt-get install python-pip php5-cli
sudo pip install awscli

// must set region - ie: us-east-1, us-west-1
aws configure
```

## Questions, issues or suggestions
Please use the [issues section](https://github.com/jveldboom/php-aws-snapshots/issues) for any questions or issues you have. Also, suggestions, pull request or any help is most welcome!
