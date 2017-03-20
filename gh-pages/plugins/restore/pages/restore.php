<?php
function backup_files($app){

    //ENTER THE RELEVANT INFO BELOW
    $mysqlUserName        = config('mysqlusername', '');
    $mysqlPassword        = config('mysqlpassword', '');
    $mysqlHostName        = config('mysqlhostname', '');
    $DbName               = config('dbname', '');
    $backup_sql_file      = "restore.sql";
    $tables               = config('tables', '');
    $restore_point        = input("id"). '.zip';
    $ftp_server           = config('ftpserver', '');
    $ftp_user_name        = config('ftpusername', '');
    $ftp_user_pass        = config('ftpuserpass', '');
    $backup_id            = input("id");

    backup_update($backup_id);
    backup_tables($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName, $tables, $backup_sql_file);
    uploadToFTP($ftp_server, $ftp_user_name, $ftp_user_pass, $restore_point);
    unlink($restore_point);
    unlink($backup_sql_file);
    $restore_page = url_to_pager('restore-files-categories-list', array('append' => '/?msg=backup-success'));
    header("Location: $restore_page");

}

function restore_files($app){

    //ENTER THE RELEVANT INFO BELOW
    $mysqlUserName      = config('mysqlusername', '');
    $mysqlPassword      = config('mysqlpassword', '');
    $mysqlHostName      = config('mysqlhostname', '');
    $DbName             = config('dbname', '');
    $restore_sql_file   = "restore.sql";
    $tables             = config('tables', '');
    $ftp_server         = config('ftpserver', '');
    $ftp_user_name      = config('ftpusername', '');
    $ftp_user_pass      = config('ftpuserpass', '');
    $src_dir            = input("id"). '.zip';
    $dst_dir            = input("id"). '.zip';
    $backup_id          = input("id");

    downloadFromFTP($ftp_server, $ftp_user_name, $ftp_user_pass, $src_dir, $dst_dir);
    extractZip(".", $dst_dir);
    restore_tables($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName, $restore_sql_file);
    unlink($dst_dir);
    unlink($restore_sql_file);
    restore_update($backup_id);
    $restore_page = url_to_pager('restore-files-categories-list', array('append' => '/?msg=restore-success'));
    header("Location: $restore_page");

}