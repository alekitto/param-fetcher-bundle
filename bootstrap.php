<?php

require 'vendor/autoload.php';

// Disable symfony deprecated errors (will count as test failed if not disabled)
error_reporting(E_ALL & ~E_USER_DEPRECATED);
