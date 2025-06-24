<?php

if ($Site->recordInfo['rec_table'] == 'system_pages' && $Site->content['moduleType'] == 'landing') {
    include 'controller/landing/'. $Site->content['module'] .'/'. $Site->content['module'] .'.php';
} else {
    include 'controller/layouts/header.php';
    include 'controller/layouts/contentSub.php';
    include 'controller/layouts/footer.php';
}