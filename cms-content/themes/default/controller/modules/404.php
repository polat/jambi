<?php

$moduleName = basename(__FILE__, '.php');

echo $Site->twig->render('modules/'. $moduleName .'.html.twig', array(
    'content' => $content,
));
