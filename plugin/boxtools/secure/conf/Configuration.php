<?php
date_default_timezone_set('Asia/Seoul');       // Set the default timezone
define('CMIS_BROWSER_URL', 'http://localhost:8080/alfresco/api/-default-/public/cmis/versions/1.1/browser');
define('CMIS_BROWSER_USER', 'admin');
define('CMIS_BROWSER_PASSWORD', 'joohyeon3438');
// if empty the first repository will be used
define('CMIS_REPOSITORY_ID', null);