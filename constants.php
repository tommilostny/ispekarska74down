<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header('HTTP/1.0 403 Forbidden');
    die();
}
define('LAST_ALIVE_TIMESTAMP_FILE', 'last_alive.txt');
define('OUTAGE_STARTS_FILE', 'outage_starts.txt');
define('OUTAGE_ENDS_FILE', 'outage_ends.txt');
define('LAST_STATE_FILE', 'last_state.txt');
define('HASS_URL', 'https://ourtafhome.online');
define('ALLOWED_IP', '86.49.253.179');
define('NEON_GREEN', '#00ffaa');
define('NEON_GREEN_GLOW', '0 0 5px #00ee99, 0 0 15px #00ee99, 0 0 25px #00ee99');
define('NEON_RED', '#ff0000');
define('NEON_RED_GLOW', '0 0 5px #bb0000, 0 0 15px #bb0000, 0 0 25px #bb0000');
define('NEON_AZURE', '#00ffff');
define('NEON_AZURE_DARKER', '#00cccc');
define('NEON_AZURE_GLOW', '0 0 5px #00cccc, 0 0 15px #00cccc, 0 0 25px #00cccc');
define('NEON_YELLOW', '#ffff00');
define('NEON_YELLOW_GLOW', '0 0 5px #cccc00, 0 0 15px #cccc00, 0 0 25px #cccc00');
define('NEON_YELLOW_DARKER', '#cccc00');
?>