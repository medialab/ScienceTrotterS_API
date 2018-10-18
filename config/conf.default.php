<?php
define('API_PASS', getenv('API_PASS'));
define('API_ALLOW_ORIGIN', getenv('API_ALLOW_ORIGIN'));
define('BACKOFFICE_URL', getenv('BACKOFFICE_URL'));
define('PUBLIC_PATH', realpath('.').'/ressources/');
define('UPLOAD_PATH', getenv('UPLOAD_PATH'));
define('API_SESSION_LIFETIME', getenv('API_SESSION_LIFETIME'));
define('MAP_API_KEY', getenv('MAP_API_KEY'));

# we keep this variable as a fallback. this should be deprecated
define('API_UPLOAD_COPY_FROM_PATH', '/backoffice/media/upload/');

