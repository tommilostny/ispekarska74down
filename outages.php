<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header('HTTP/1.0 403 Forbidden');
    die();
}
require_once 'constants.php';

//load outages history from outages_history.txt
//timestamps are stored line by line
$starts = [];
if (file_exists(OUTAGE_STARTS_FILE)) {
    $file = fopen(OUTAGE_STARTS_FILE, 'r');
    while (!feof($file)) {
        $line = fgets($file);
        if ($line != '') {
            $starts[] = $line;
        }
    }
    fclose($file);
}
$ends = [];
if (file_exists(OUTAGE_ENDS_FILE)) {
    $file = fopen(OUTAGE_ENDS_FILE, 'r');
    while (!feof($file)) {
        $line = fgets($file);
        if ($line != '') {
            $ends[] = $line;
        }
    }
    fclose($file);
}
?>
<table>
    <tr>
        <td><a href="?page=home">&lt;&lt; Home</a></td>
        <td class="neon_green"><strong>Outages history</strong></td>
    </tr>
</table>
<table>
    <tr>
        <th>From</th>
        <th>To</th>
        <th>Duration</th>
    </tr>
    <?php
    for ($i = count($starts) - 1; $i >= 0; $i--) {
        ?><tr>
            <td><?= date('d.m.Y H:i:s', $starts[$i]) ?></td><?php
            if (isset($ends[$i])) {
                ?><td><?= date('d.m.Y H:i:s', $ends[$i]) ?></td>
                <td><?php
                    $duration_minutes = ($ends[$i] - $starts[$i]) / 60;
                    $hours = floor($duration_minutes / 60);
                    $minutes = round($duration_minutes % 60);
                    echo ($hours > 0 ? $hours . " hours " : "") . $minutes . " minutes";
                ?></td></tr><?php
                continue;
            }
            $current_time = time();
            $duration_minutes = ($current_time - $starts[$i]) / 60;
            $hours = floor($duration_minutes / 60);
            $minutes = round($duration_minutes % 60);
        ?><td>Now</td>
        <td><?= ($hours > 0 ? $hours . " hours " : "") . $minutes . " minutes" ?></td></tr><?php
    }
?>
</table>
