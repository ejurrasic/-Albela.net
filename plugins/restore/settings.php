<?php
return array(
    'title' => 'Backup and Restore',
    'description' => lang("restore::restore-setting-description"),
    'settings' => array(
        'mysqlusername' => array(
            'type' => 'text',
            'title' => lang('restore::mysql-user-name-title'),
            'description' => lang('restore::mysql-user-name-desc'),
            'value' => lang('restore::mysql-user-name-value')
        ),
        'mysqlpassword' => array(
            'type' => 'text',
            'title' => lang('restore::mysql-password-title'),
            'description' => lang('restore::mysql-password-desc'),
            'value' => lang('restore::mysql-password-value'),
        ),
        'mysqlhostname' => array(
            'type' => 'text',
            'title' => lang('restore::mysql-host-name-title'),
            'description' => lang('restore::mysql-host-name-desc'),
            'value' => lang('restore::mysql-host-name-value')
        ),
        'dbname' => array(
            'type' => 'text',
            'title' => lang('restore::db-name-title'),
            'description' => lang('restore::db-name-desc'),
            'value' => lang('restore::db-name-value')
        ),
        'tables' => array(
            'type' => 'text',
            'title' => lang('restore::tables-title'),
            'description' => lang('restore::tables-desc'),
            'value' => lang('restore::tables-value')
        ),
        'ftpserver' => array(
            'type' => 'text',
            'title' => lang('restore::ftp-server-title'),
            'description' => lang('restore::ftp-server-desc'),
            'value' => lang('restore::ftp-server-value')
        ),
        'ftpusername' => array(
            'type' => 'text',
            'title' => lang('restore::ftp-user-name-title'),
            'description' => lang('restore::ftp-user-name-desc'),
            'value' => lang('restore::ftp-user-name-value'),
        ),
        'ftpuserpass' => array(
            'type' => 'text',
            'title' => lang('restore::ftp-user-pass-title'),
            'description' => lang('restore::ftp-user-pass-desc'),
            'value' => lang('restore::ftp-user-pass-value')
        ),
    )
);