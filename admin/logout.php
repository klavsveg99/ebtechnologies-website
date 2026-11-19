<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';

ebt_logout();
ebt_redirect('/admin/login.php');
