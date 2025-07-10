<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header('HTTP/1.0 403 Forbidden');
    die();
}
require_once 'constants.php';

//curl request to tafhome.online, check if the response is html or an error
//if the response is html, then the website is up and running
//if the response is an error, then the website is down

$ch = curl_init(HASS_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo '<h1 class="neon_green">No :)</h1>';

    $ts = time();
    echo "<p>Last time checked: " . date('d.m.Y H:i:s', $ts) . "</p>";

    //write alive timestamp to last_alive.txt
    $file = fopen(LAST_ALIVE_TIMESTAMP_FILE, 'w');
    fwrite($file, $ts);
    fclose($file);

    //read boolean from last_state.txt, if it is 0, then the apartment had an outage, meaning the outage ended, mark its end
    if (file_exists(LAST_STATE_FILE)) {
        $file = fopen(LAST_STATE_FILE, 'r');
        $state = fread($file, filesize(LAST_STATE_FILE));
        fclose($file);
        if ($state == '0') {
            //write outage end timestamp to outage_ends.txt
            $file = fopen(OUTAGE_ENDS_FILE, 'a');
            fwrite($file, $ts);
            fwrite($file, "\n");
            fclose($file);
        }
    }

    //read not alive timestamp from last_not_alive.txt
    if (file_exists(OUTAGE_STARTS_FILE) && file_exists(OUTAGE_ENDS_FILE)) {
        //read the last line from both files to get start and end of the last outage
        $file = fopen(OUTAGE_STARTS_FILE, 'r');
        $starts = [];
        while (!feof($file)) {
            $line = fgets($file);
            if ($line != '') {
                $starts[] = $line;
            }
        }
        fclose($file);
        $file = fopen(OUTAGE_ENDS_FILE, 'r');
        $ends = [];
        while (!feof($file)) {
            $line = fgets($file);
            if ($line != '') {
                $ends[] = $line;
            }
        }
        fclose($file);
        $i = count($ends) - 1; 
        echo "<p><strong>Last outage</strong>:<br>From: " . date('d.m.Y H:i:s', $starts[$i]);
        echo "<br>To: " . date('d.m.Y H:i:s', $ends[$i]);
        $duration_minutes = ($ends[$i] - $starts[$i]) / 60;
        $hours = floor($duration_minutes / 60);
        $minutes = round($duration_minutes % 60);
        echo "<br>Duration: " . ($hours > 0 ? $hours . " hours " : "") . $minutes . " minutes</p>";
        echo "<br>Number of known outages: " . ($i + 1) . "</p>";
    }
    else {
        echo "<p>Last outage time: unknown</p>";
    }

    //save boolean 1 to last_state.txt, meaning the apartment is alive
    $file = fopen(LAST_STATE_FILE, 'w');
    fwrite($file, '1');
    fclose($file);
}
else {
    echo '<h1 class="neon_red">Yes :(</h1>';

    //check last_alive.txt file for the last timestamp
    if (file_exists(LAST_ALIVE_TIMESTAMP_FILE)) {
        $file = fopen(LAST_ALIVE_TIMESTAMP_FILE, 'r');
        $ts = fread($file, filesize(LAST_ALIVE_TIMESTAMP_FILE));
        fclose($file);
        echo "<p>Last alive: " . date('d.m.Y H:i:s', $ts) . "</p>";
    }
    else {
        echo "<p>Last alive: unknown</p>";
    }

    //check last_state.txt file for the last state, if it is 1, then the apartment was alive and not alive timestamp can be saved
    if (file_exists(LAST_STATE_FILE)) {
        $file = fopen(LAST_STATE_FILE, 'r');
        $state = fread($file, filesize(LAST_STATE_FILE));
        fclose($file);
        if ($state == '1') {
            $saveNotAlive = true;
        }
        else {
            $saveNotAlive = false;
        }
    }
    else {
        $saveNotAlive = true;
    }

    if ($saveNotAlive) {
        $file = fopen(OUTAGE_STARTS_FILE, 'a');
        fwrite($file, $ts);
        fwrite($file, "\n");
        fclose($file);
    }

    //save boolean 0 to last_state.txt, meaning the apartment is not alive
    $file = fopen(LAST_STATE_FILE, 'w');
    fwrite($file, '0');
    fclose($file);
}
?>
<table>
    <tr>
        <th colspan="2">Usual offenders:</th>
    </tr>
    <tr>
        <td>
            <a href="https://www.vodafone.cz/vypadky/?ruianid=19107145" target="_blank">Internet (Vodafone)</a>
        </td>
        <td>
            <!--
            <form action="https://www.egd.cz/formular-bezproudi" method="post" target="_blank" id="power-outage-form" accept-charset="UTF-8" data-action="https://www.egd.cz/formular-bezproudi">
                <input type="hidden" name="address_ean[id]" value="Pekařská, 60200 Brno, Staré Brno"></input>
                <input type="hidden" name="address_ean[input]" value></input>
                <input type="submit" value="Electricity (EG.D)"></input>
            </form>
            -->
            <a href="https://www.egd.cz/formular-bezproudi" target="_blank">Electricity (EG.D)</a>
        </td>
    </tr>
    <!--
    <tr>
        <td colspan="2">
            <a href="http://www.tognerapartneri.cz/" target="_blank">Constuction workers (Togner &amp; Partners)</a>
        </td>
    </tr>
    -->
</table>

<p><a href="?page=outages">Outages history</a></p>
<p><a href="?page=music">Special Music to listen to while waiting for an outage to end :)</a></p>