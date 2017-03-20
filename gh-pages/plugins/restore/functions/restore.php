<?php
/* restore the whole database */
function restore_tables($host, $user, $pass, $name, $backup_sql_file = 'backup.sql')
{
    set_time_limit(0);
    ini_set("mysql.connect_timeout", -1);
    $db = new mysqli($host, $user, $pass, $name);
    $db->query( 'SET @@global.max_allowed_packet = ' . 500 * 1024 * 1024 );
    $sql = file_get_contents($backup_sql_file);
    $db->multi_query($sql);

}

/* backup the db OR just a table */
function backup_tables($host, $user, $pass, $name, $tables = '*', $backup_sql_file = 'backup.sql')
{
    $return = "";
    $db = new mysqli($host, $user, $pass, $name);

    //get all of the tables
    if($tables == '*')
    {
        $tables = array();
        $result = $db->query('SHOW TABLES');
        while($row = $result->fetch_row())
        {
            $tables[] = $row[0];
        }
    }
    else
    {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }

    //cycle through
    foreach($tables as $table)
    {
        $result = $db->query('SELECT * FROM '.$table);
        $num_fields = $result->field_count;

        $return.= 'DROP TABLE '.$table.';';
        $query = $db->query('SHOW CREATE TABLE '.$table);
        $row2 = $query->fetch_row();
        $return.= "\n\n".$row2[1].";\n\n";

        for ($i = 0; $i < $num_fields; $i++)
        {
            while($row = $result->fetch_row())
            {
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j < $num_fields; $j++)
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j < ($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }

    //save file
    //$handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
    $handle = fopen($backup_sql_file,'w+');
    fwrite($handle,$return);
    fclose($handle);
}

function uploadToFTP($ftp_server, $ftp_user_name, $ftp_user_pass, $point)
{
    // set up basic connection
    $conn_id = ftp_connect($ftp_server);

    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

    // turn passive mode on
    ftp_pasv($conn_id, true);

    zipUpload($conn_id, $point);
}

function zipUpload($conn_id, $point)
{
    $zipname = $point;
    folderToZip(".",$zipname );

    ftp_put($conn_id, $zipname, $zipname, FTP_BINARY);
}

function folderToZip($path, $zipFile) {
    // Get real path for our folder
    $rootPath = realpath($path);

    // Initialize archive object
    $zip = new ZipArchive();
    $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $name => $file)
    {
        // Skip directories (they would be added automatically)
        if (!$file->isDir())
        {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
        }
    }

    // Zip archive will be created only after closing object
    $zip->close();
}


function downloadFromFTP($ftp_server, $ftp_user_name, $ftp_user_pass, $src_dir, $dst_dir)
{

    // set up basic connection
    $conn_id = ftp_connect($ftp_server)
    or die("Couldn't connect to $ftp_server");

    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    if ((!$conn_id) || (!$login_result))
        die("FTP Connection Failed");

    // turn passive mode on
    ftp_pasv($conn_id, true);

    ftp_get($conn_id, $dst_dir, $src_dir, FTP_BINARY);

    ftp_close($conn_id);

}

function extractZip($source, $name)
{
    $zip = new ZipArchive;
    if ($zip->open($name) === TRUE)
    {
        $zip->extractTo($source);
        $zip->close();
    }
    else
    {
        exit('failed');
    }
}

function backup_update($id)
{
    $db = db();
    $today = date("d/m/Y H:i:s");
    $sql = "UPDATE `restore_types` SET `last_backup_date` = '{$today}' WHERE `id` = {$id} ";
    $query = $db->query($sql);
}

function restore_update($id)
{
    $db = db();
    $today = date("d/m/Y H:i:s");
    $sql = "UPDATE `restore_types` SET `last_restore_date` = '{$today}' WHERE `id` = {$id} ";
    $query = $db->query($sql);
}