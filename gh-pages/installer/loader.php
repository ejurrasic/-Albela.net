<?php
require path('installer/functions.php');
load_functions("admin");
load_functions("users");
register_pager('install', array('as' => 'installer', 'use' => 'install@index_pager'));
register_pager('install/db', array('as' => 'install-db', 'use' => 'install@database_pager'));
register_pager('install/require', array('as' => 'install-requirements', 'use' => 'install@require_pager'));
register_pager('install/plugins', array('as' => 'install-plugins', 'use' => 'install@plugins_pager'));
register_pager('install/info', array('as' => 'install-info', 'use' => 'install@info_pager'));
register_pager('install/finish', array('as' => 'install-finsh', 'use' => 'install@finish_pager'));
