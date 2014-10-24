Example Usage
```php
require_once('snapshots.php');
$volumes = array(
	'vol-123af85a' => array('description' => 'dev server backup', 'snapshots' => 3, 'interval' => '1 day'),
);
$snapshots = new snapshots($volumes);
$snapshots->run();
```