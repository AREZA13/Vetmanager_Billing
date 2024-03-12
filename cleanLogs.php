<?php
/**script for cleaning logs in billing once a week via crontab
 * we are looking new billing logs in usr/local/mgr5/var which are more than 40mb only
 * deleting old logs in /usr/local/mgr5/var
 * compressing to .gz and moving new logs into /usr/local/mgr5/var/logs
 * if there are no new logs we keep the old ones
 *
 * script is here /var/billmanager-scripts/src/vetmanager
 * setting for crontab -e
 * export EDITOR=nano
 *##BILLmanager clean logs script  “At 00:00 on Sunday.”
 * 0 0 * * 0 /usr/bin/php /usr/local/mgr5/var/cleanLogs.php > /usr/local/mgr5/var/cronPhpLog.txt
 * **/
$folder_path = '/usr/local/mgr5/var';
$logFolderPath = $folder_path . '/logs';

// Get all *.gz files in the folder
$files = glob($logFolderPath . '/*.gz');
foreach ($files as $file) {
    if (is_file($file)) {
        // delete all gz files if there are same names in logs
        $mainFile = basename($file, '.gz') . '.log';
        if (is_file("{$folder_path}/" . $mainFile)) {
            unlink($file); // Delete each file one by one
        } else {
            echo "Skipping: " . $file . "\n";
        }
    }
}

// Compress and move  *.log files from the var folder to var/logs folder
$mainFolderFiles = glob("{$folder_path}/*.log");
foreach ($mainFolderFiles as $file) {
    if (is_file($file)) {
        //Check .logs size if more than 40mb
        if (filesize(''.$file.'') > 40 * 1024 * 1024) {
            // Compress .log file to .gz
            exec('gzip -f ' . escapeshellarg($file));
            // Move *logs.gz to ****var/logs
            rename("{$folder_path}/" . basename($file) . '.gz', "{$logFolderPath}" . '/' . basename($file) . '.gz');
        }
    }
}
