<?php
$filePresent = true;
if (!file_exists('../Config/database.php')):
  echo '<span class="notice-failure">Database configuration file is not present. Please contact admin@website</span>';
  $filePresent = false;
endif;
if ($filePresent!=false):
  uses('model' . DS . 'connection_manager');
  $db = ConnectionManager::getInstance();
  @$connected = $db->getDataSource('default');
  if (!$connected->isConnected()):
    echo '<p><span class="notice-failure">Not able to connect to the database. Please contact admin@website</span></p>';
  endif;
endif;
?>
