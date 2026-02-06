<?php
require_once 'constants.php';

if ($_SERVER['REMOTE_ADDR'] != ALLOWED_IP) {
    $file = fopen('forbidden_access.log', 'a');
    $ts = time();
    fwrite($file, "Forbidden access from {$_SERVER['REMOTE_ADDR']} at $ts.\n");
    fclose($file);
    header('HTTP/1.0 403 Forbidden');
    die();
}

//save timestamp to last_alive.txt
$file = fopen(LAST_ALIVE_TIMESTAMP_FILE, 'w');
$ts = time();
fwrite($file, $ts);
fclose($file);
echo "Timestamp saved: $ts\n";

//read boolean from last_state.txt, if it is 0, then the apartment had an outage, meaning the outage ended, mark its end
if (file_exists(LAST_STATE_FILE)) {
    $file = fopen(LAST_STATE_FILE, 'r');
    $state = fread($file, filesize(LAST_STATE_FILE));
    fclose($file);
    if ($state == '0') {
        //write 1 to last_state.txt, meaning the apartment is alive
        $file = fopen(LAST_STATE_FILE, 'w');
        fwrite($file, '1');
        fclose($file);

        //write outage end timestamp to outage_ends.txt
        $file = fopen(OUTAGE_ENDS_FILE, 'a');
        fwrite($file, $ts);
        fwrite($file, "\n");
        fclose($file);
    }
}
?>
