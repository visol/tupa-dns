<?php
/* You can overwrite the $TUPA_CONF_VARS values in "lib/config_default.php" here 
  * 
  * If you want to change your default SOA Primary Server for example use this:
  * $TUPA_CONF_VARS['DNS']['defaultSoaPrimary']='your.nameserver.com';
  *
  * Or the default SOA eMail address
  * $TUPA_CONF_VARS['DNS']['defaultSoaHostmaster']='your@email-address.com';
  *
  * Or if you want to enable maintenance mode
  * $TUPA_CONF_VARS['SYS']['maintenanceEnabled']=true;
  *
  * They will be configurable over an interface in a later release.
  */

## INSTALLER EDIT POINT TOKEN - all lines after this points may be changed by the installer!

// md5 encoded installer password
$installer_password = 'db82d9865acccd8d8ccf583b0ecf99e9';

// Database config
$tupa_db = 'database';
$tupa_db_host = 'localhost';
$tupa_db_port = 3306;
$tupa_db_username = 'user';
$tupa_db_password = 'password';

?>
