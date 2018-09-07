<?php
define('API_PASS', getenv('API_PASS'));
define('API_ALLOW_ORIGIN', getenv('API_ALLOW_ORIGIN'));
define('ADMIN_URL', getenv('BACKOFFICE_URL'));
define('PUBLIC_PATH', realpath('.').'/ressources/');
define('UPLOAD_PATH', PUBLIC_PATH.'upload/');
define('API_SESSION_LIFETIME', getenv(API_SESSION_LIFETIME));
define('MAP_API_KEY', getenv('MAP_API_KEY'));