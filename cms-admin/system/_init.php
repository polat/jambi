<?php

// Require necessary files and classes.
require_once dirname(__FILE__) . '/_bootstrap.php';

if (!$Jambi->Login->checkLogin()) {
    die();
}