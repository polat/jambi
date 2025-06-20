<?php

$layoutName = basename(__FILE__, '.php');

echo $Site->twig->render('layouts/'. $layoutName .'.html.twig', array(
    
));