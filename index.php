<?php

// Require init file.
require_once dirname(__FILE__) . '/cms-content/_init.php';

if ($Site->isHome()) {
    // Include index page
    include dirname(__FILE__) . '/cms-content/themes/default/index.php';
} else {
    if ($Site->is404Page()) {
        // Return 404 Page
        header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    }

    // Include Sub page
    include dirname(__FILE__) . '/cms-content/themes/default/sub.php';
}