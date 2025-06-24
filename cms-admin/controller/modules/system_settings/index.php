<?php

/**
 * Import Custom Settings Pages
 */
if ($Jambi->get['custom']) {
    require_once dirname(__FILE__) . '/custom/' . $Jambi->get['custom'] . '.php';
}