<?php
// General configuration

define('JWT_SECRET_KEY', 'your_secret_key_here_change_this_to_a_strong_key');
define('JWT_ISSUER', 'http://localhost');
define('JWT_AUDIENCE', 'http://localhost');
define('JWT_EXPIRATION_TIME', 3600); // 1 hour

// Upload directory for blog post images
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Base URL for accessing uploaded images
define('BASE_URL', 'http://localhost/phpclass/myblogapp/uploads/');

?>
