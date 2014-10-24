## Requirements
- [AWS CLI](http://aws.amazon.com/cli/)
- AWS IAM Permissions ([example policy](#example-iam-policy))
- PHP 5.3+
- Access to CRON (or some other type of scheduler)

## Example Usage
```php
require_once('snapshots.php');
$volumes = array(
   'vol-123af85a' => array('description' => 'dev server backup', 'snapshots' => 3, 'interval' => '7 days'),
   'vol-321bg96c' => array('description' => 'build server', 'snapshots' => 2, 'interval' => '3 hours'),
);
$snapshots = new snapshots($volumes);
$snapshots->run();
```

## Volume Config

| Name | Type | Description |
|------|------|-------------|
| *volume id* | string | AWS EBS volume ID
| description | string | snapshot description that shows in the Snapshot section within AWS console |
| snapshots | integer | total number of snapshots to store for volume |
| interval | string | how often to create snapshot (1 day, 7 days, 2 weeks - full list below)

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
        "ec2:DescribeSnapshots",
      ],
      "Resource": [
        "*"
      ]
    }
  ]
}
```
