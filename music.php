<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header('HTTP/1.0 403 Forbidden');
    die();
}
require_once 'constants.php';

?>
<style>
    select {
        padding: 10px;
        font-size: 1.2rem;
        margin: 20px auto;
        margin-top: 5px;
        font-family: 'Roboto Mono', monospace;
        background-color: #222;
        color: #fff;
        border: 2px solid #333;
        border-radius: 5px;
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    .main-player-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        width: 90%;
        max-width: 1200px;
        margin: 20px auto;
        align-items: flex-start;
    }
    .player-left-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
        width: 100%;
        transition: width 0.5s ease;
    }
    #custom-player {
        width: 100%;
        background: #222;
        border: 2px solid #333;
        border-radius: 5px;
        padding: 20px;
        color: #fff;
        box-sizing: border-box;
        position: relative;
    }
    #lyrics-container {
        /* Base styles */
        background: #222;
        border: 0 solid #333;
        border-radius: 5px;
        padding: 0;
        color: #fff;
        box-sizing: border-box;
        display: none;
        text-align: left;
        
        /* Animation start state (Hidden) */
        opacity: 0;
        overflow: hidden;
        transition: all 0.5s ease;
        
        /* Mobile specific hidden state */
        width: 100%;
        max-height: 0;
        margin-top: 0;
    }
    #lyrics-container.active {
        /* Visible state */
        opacity: 1;
        padding: 20px;
        border-width: 2px;
        
        /* Mobile specific visible state */
        max-height: 600px;
        margin-top: 20px;
        overflow-y: auto;
    }
    #lyrics-content {
        white-space: pre-wrap;
        font-family: inherit;
        margin: 0;
        line-height: 1.4;
        font-size: 1rem;
    }
    @media (min-width: 900px) {
        .main-player-wrapper {
            flex-direction: row;
            align-items: stretch;
            justify-content: center;
        }
        .player-left-column {
            width: 48%;
            margin: 0;
        }
        #lyrics-container {
            /* Desktop specific hidden state */
            width: 0;
            max-height: 600px; /* Reset max-height constraint */
            margin-left: 0;
            margin-top: 0;
        }
        #lyrics-container.active {
            /* Desktop specific visible state */
            width: 48%;
            margin-left: 20px;
            margin-top: 0;
        }
    }
    .player-controls {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    .progress-time-group {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
    }
    .button-group {
        display: flex;
        justify-content: center;
        gap: 10px;
        width: 100%;
    }
    #custom-player button {
        background: none;
        border: 2px solid <?= NEON_GREEN ?>;
        color: <?= NEON_GREEN ?>;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        line-height: 1;
        font-weight: bold;
        font-size: 1.2rem;
        padding: 8px 16px;
        width: 66px;
        border-radius: 5px;
    }
    #custom-player button:hover {
        background-color: <?= NEON_GREEN ?>;
        color: #000;
        box-shadow: <?= NEON_GREEN_GLOW ?>;
        text-shadow: none;
    }
    #custom-player button:active {
        transform: scale(0.92);
        transition: transform 0.05s;
    }
    #progress-bar {
        flex: 1;
        margin: 0 10px;
        accent-color: <?= NEON_GREEN ?>;
    }
    .music-select-container {
        width: 100%;
        margin: 0;
        box-sizing: border-box;
    }
    .music-select-label {
        margin: 0 0 5px 0;
        padding: 0;
        font-size: 1rem;
        color: #fff;
        text-align: left;
    }
    #album-cover {
        margin-bottom: 5px;
        margin-top: 5px;
    }
    #track-info-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(34, 34, 34, 0.8);
        color: #aaa;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        text-align: center;
        line-height: 24px;
        cursor: pointer;
        font-size: 14px;
        border: 1px solid #444;
        z-index: 10;
        font-family: 'Roboto Mono', monospace;
    }
    #track-info-btn:hover {
        color: #fff;
        border-color: <?= NEON_AZURE ?>;
        color: <?= NEON_AZURE ?>;
    }
    #track-info-tooltip {
        position: absolute;
        top: 42px;
        right: 10px;
        background: #1a1a1a;
        border: 1px solid #333;
        padding: 12px;
        border-radius: 5px;
        width: 220px;
        display: none;
        font-size: 0.85rem;
        color: #ddd;
        text-align: left;
        z-index: 20;
        box-shadow: 0 4px 15px rgba(0,0,0,0.8);
        font-family: 'Roboto Mono', monospace;
    }
    #track-info-tooltip.visible {
        display: block;
    }
    .info-row {
        margin-bottom: 8px;
        word-break: break-word;
        line-height: 1.3;
    }
    .info-row:last-child {
        margin-bottom: 0;
    }
    .info-label {
        color: #777;
        font-size: 0.7rem;
        text-transform: uppercase;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
    }
    /* Ensure screen content fills height for matrix rain background */
    .screen-content {
        flex: 1;
        position: relative;
    }
    /* Ensure content sits above matrix rain */
    .main-player-wrapper, table {
        position: relative;
        z-index: 1;
    }
</style>
<?php
if (isset($_GET['secret'])) {
    echo '<canvas id="matrix-rain" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:0; opacity:0.15; pointer-events:none;"></canvas>';
}

/** Select all files from the music folder */
$musicFiles = glob(MUSIC_DIR . '/*.{mp3}', GLOB_BRACE);
if (empty($musicFiles)) {
    echo '<h1 class="neon_red">No music files found!</h1>';
    return;
}
// Sort files by name
sort($musicFiles);

// Swap file with name "Na Pekařské 74.mp3" to the first position if it exists
$specialFile = MUSIC_DIR . '/Na Pekařské 74.mp3';
if (in_array($specialFile, $musicFiles)) {
    $musicFiles = array_merge([$specialFile], array_diff($musicFiles, [$specialFile]));
}

// If GET variable secret is set, add secret music files
if (isset($_GET['secret']) && file_exists(SECRET_MUSIC_DIR)) {
    $secretFiles = glob(SECRET_MUSIC_DIR . '/*.{mp3}', GLOB_BRACE);
    if (!empty($secretFiles)) {
        // Sort secret files by name
        rsort($secretFiles);
        // Add secret files to the beginning of the list
        $musicFiles = array_merge($secretFiles, $musicFiles);
    }
}

// Build album cover mapping (URLs)
$loadingUrl = ALBUM_COVERS_DIR . '/loading.png';
if (!file_exists($loadingUrl)) {
    $loadingUrl = '';
}

$albumCoverMap = [];
$lyricsMap = [];
$metadataMap = [];
$defaultCoverFilename = 'Na Pekařské 74.png';
$defaultCoverPath = ALBUM_COVERS_DIR . '/' . $defaultCoverFilename;
$defaultCoverUrl = file_exists($defaultCoverPath) ? ALBUM_COVERS_DIR . '/' . rawurlencode($defaultCoverFilename) : '';

function getMp3Info($path) {
    $info = [
        'size' => filesize($path),
        'artist' => '',
        'title' => '',
        'album' => '',
        'year' => '',
        'sample_rate' => 0,
        'channels' => 0,
        'created_date' => filectime($path)
    ];
    
    $fp = fopen($path, 'rb');
    if (!$fp) return $info;

    // Try ID3v2 first
    $audioStart = 0;
    rewind($fp);
    $header = fread($fp, 10);
    if (substr($header, 0, 3) === 'ID3') {
        $major = ord($header[3]);
        if ($major >= 3) {
            $size = (ord($header[6]) << 21) | (ord($header[7]) << 14) | (ord($header[8]) << 7) | ord($header[9]);
            $tagData = fread($fp, $size);
            $audioStart = 10 + $size;
            $pos = 0;
            while ($pos < strlen($tagData)) {
                if ($pos + 10 > strlen($tagData)) break;
                $frameId = substr($tagData, $pos, 4);
                if (ord($frameId[0]) === 0) break;
                $frameSize = ($major === 3) ? unpack('N', substr($tagData, $pos + 4, 4))[1] : 
                             ((ord($tagData[$pos+4]) << 21) | (ord($tagData[$pos+5]) << 14) | (ord($tagData[$pos+6]) << 7) | ord($tagData[$pos+7]));
                
                if ($pos + 10 + $frameSize > strlen($tagData)) break;
                $content = substr($tagData, $pos + 10, $frameSize);
                
                if (in_array($frameId, ['TIT2', 'TPE1', 'TALB', 'TYER', 'TDRC'])) {
                    $encoding = ord($content[0]);
                    $text = substr($content, 1);
                    if ($encoding === 0) $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
                    else if ($encoding === 1) $text = mb_convert_encoding($text, 'UTF-8', 'UTF-16');
                    else if ($encoding === 2) $text = mb_convert_encoding($text, 'UTF-8', 'UTF-16BE');
                    else if ($encoding === 3) $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
                    $text = str_replace("\0", "", $text);
                    
                    if ($frameId === 'TIT2') $info['title'] = $text;
                    if ($frameId === 'TPE1') $info['artist'] = $text;
                    if ($frameId === 'TALB') $info['album'] = $text;
                    if ($frameId === 'TYER' || $frameId === 'TDRC') $info['year'] = substr($text, 0, 4);
                }
                $pos += 10 + $frameSize;
            }
        }
    }

    // Parse MP3 Frame Header for Sample Rate and Channels
    fseek($fp, $audioStart);
    $buffer = fread($fp, 4096); // Read 4KB to find sync
    $len = strlen($buffer);
    for ($i = 0; $i < $len - 3; $i++) {
        // Sync word is 11 bits set to 1 (0xFF followed by 111xxxxx)
        if (ord($buffer[$i]) === 0xFF && (ord($buffer[$i+1]) & 0xE0) === 0xE0) {
            $b2 = ord($buffer[$i+1]);
            $b3 = ord($buffer[$i+2]);
            $b4 = ord($buffer[$i+3]);
            
            $ver = ($b2 & 0x18) >> 3; // 00=2.5, 10=2, 11=1
            $lay = ($b2 & 0x06) >> 1; // 01=III
            if ($ver === 1 || $lay === 0) continue; // Invalid version/layer
            
            $srateIdx = ($b3 & 0x0C) >> 2;
            if ($srateIdx === 3) continue; // Reserved
            
            $rates = [
                3 => [44100, 48000, 32000], // V1
                2 => [22050, 24000, 16000], // V2
                0 => [11025, 12000, 8000]   // V2.5
            ];
            if (isset($rates[$ver][$srateIdx])) $info['sample_rate'] = $rates[$ver][$srateIdx];
            
            $info['channels'] = (($b4 & 0xC0) >> 6) === 3 ? 1 : 2;
            break;
        }
    }

    // Fallback ID3v1
    if (empty($info['artist']) || empty($info['title'])) {
        fseek($fp, -128, SEEK_END);
        $tag = fread($fp, 128);
        if (substr($tag, 0, 3) === 'TAG') {
            if (empty($info['title'])) $info['title'] = trim(substr($tag, 3, 30));
            if (empty($info['artist'])) $info['artist'] = trim(substr($tag, 33, 30));
            if (empty($info['album'])) $info['album'] = trim(substr($tag, 63, 30));
            if (empty($info['year'])) $info['year'] = trim(substr($tag, 93, 4));
        }
    }
    fclose($fp);
    foreach ($info as $k => $v) if (is_string($v)) $info[$k] = trim($v);
    return $info;
}

foreach ($musicFiles as $file) {
    $trackName = str_replace('.mp3', '', basename($file));
    $encodedTrackName = rawurlencode($trackName);
    $coverPng = ALBUM_COVERS_DIR . '/' . $trackName . '.png';
    $coverJpg = ALBUM_COVERS_DIR . '/' . $trackName . '.jpg';
    if (file_exists($coverPng)) {
        $albumCoverMap[$trackName] = ALBUM_COVERS_DIR . '/' . $encodedTrackName . '.png';
    } elseif (file_exists($coverJpg)) {
        $albumCoverMap[$trackName] = ALBUM_COVERS_DIR . '/' . $encodedTrackName . '.jpg';
    } else {
        $albumCoverMap[$trackName] = $defaultCoverUrl;
    }

    $lyricsFile = LYRICS_DIR . '/' . $trackName . '.txt';
    if (file_exists($lyricsFile)) {
        $lyricsMap[$trackName] = LYRICS_DIR . '/' . $encodedTrackName . '.txt';
    }
    
    $metadataMap[$trackName] = getMp3Info($file);
}
?>
<table>
    <tr>
        <td><a href="?page=home">&lt;&lt; Home</a></td>
        <td class="neon_green"><strong>Our special music</strong></td>
    </tr>
</table>
<div class="main-player-wrapper">
<div class="player-left-column">
    <div class="music-select-container">
        <p class="music-select-label">Select a track to play:</p>
        <select id="music-select" onchange="changeMusic(this.value)">
    <?php
    foreach ($musicFiles as $file) {
        $trackName = str_replace('.mp3', '', basename($file));
        echo "<option value=\"$file\">$trackName</option>";
    }
    echo '</select>';
    ?>
    </div>
<div id="custom-player">
    <div id="album-cover-container" style="text-align:center; margin-bottom:16px;">
        <img id="album-cover" src="<?= $loadingUrl ?>" alt="Album cover" style="max-width:220px; max-height:220px; border-radius:10px; box-shadow:0 0 10px #000; background:#222; object-fit:cover; transition: transform 0.05s ease;" loading="lazy">
        <canvas id="visualizer" width="220" height="40" style="display:block; margin:10px auto 0 auto; border-radius:5px; background:#111;"></canvas>
    </div>
    <div class="player-controls">
        <div class="progress-time-group">
            <span id="current-time">0:00</span>
            <input type="range" id="progress-bar" value="0" min="0" max="100" step="1">
            <span id="duration">0:00</span>
        </div>
        <div class="button-group">
            <button id="prev">&lt;&lt;</button>
            <button id="play-pause">&gt;</button>
            <button id="next">&gt;&gt;</button>
            <button id="shuffle" title="Shuffle">RND</button>
        </div>
    </div>
    <div id="track-info-btn" title="Track Info">i</div>
    <div id="track-info-tooltip"></div>
</div>
<p style="text-align:center; font-size:0.95rem; color:#aaa; margin-top:8px; margin-bottom:24px;">
    Controls: <b>Space</b> = Play/Pause, <b>←/→</b> = Previous/Next, <b>↑/↓</b> = Seek ±5s, or use the mouse.
</p>
</div>
<div id="lyrics-container">
    <h3 style="margin-top:0; color:<?= NEON_GREEN ?>;">Lyrics</h3>
    <pre id="lyrics-content"></pre>
</div>
</div>
<audio id="audio-player" style="display:none;">
    <source id="audio-source" src="<?= $musicFiles[0] ?>" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>
<script>
{
    <?php if (isset($_GET['secret'])): ?>
    const matrixCanvas = document.getElementById('matrix-rain');
    let matrixInterval;
    let resizeMatrix;
    if (matrixCanvas) {
        const matrixCtx = matrixCanvas.getContext('2d');
        const fontSize = 16;
        const rainDrops = [];

        resizeMatrix = () => {
            matrixCanvas.width = matrixCanvas.offsetWidth;
            matrixCanvas.height = matrixCanvas.offsetHeight;
            const columns = Math.ceil(matrixCanvas.width / fontSize);
            for (let x = 0; x < columns; x++) {
                if (rainDrops[x] === undefined) {
                    rainDrops[x] = Math.random() * (matrixCanvas.height / fontSize);
                }
            }
        };
        
        // Initial resize
        resizeMatrix();
        window.addEventListener('resize', resizeMatrix);

        const katakana = 'アァカサタナハマヤャラワガザダバパイィキシチニヒミリヰギジヂビピウゥクスツヌフムユュルグズブヅプエェケセテネヘメレヱゲゼデベペオォコソトノホモヨョロヲゴゾドボポヴッン';
        const latin = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const nums = '0123456789';
        const alphabet = katakana + latin + nums;

        const drawMatrix = () => {
            matrixCtx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            matrixCtx.fillRect(0, 0, matrixCanvas.width, matrixCanvas.height);

            matrixCtx.fillStyle = '#0F0';
            matrixCtx.font = fontSize + 'px monospace';

            for(let i = 0; i < rainDrops.length; i++) {
                const text = alphabet.charAt(Math.floor(Math.random() * alphabet.length));
                matrixCtx.fillText(text, i*fontSize, rainDrops[i]*fontSize);

                if(rainDrops[i]*fontSize > matrixCanvas.height && Math.random() > 0.975){
                    rainDrops[i] = 0;
                }
                rainDrops[i]++;
            }
        };

        matrixInterval = setInterval(drawMatrix, 30);
    }
    <?php endif; ?>

    const audioPlayer = document.getElementById('audio-player');
    const audioSource = document.getElementById('audio-source');
    const playPauseBtn = document.getElementById('play-pause');
    const progressBar = document.getElementById('progress-bar');
    const currentTimeEl = document.getElementById('current-time');
    const durationEl = document.getElementById('duration');
    const musicSelect = document.getElementById('music-select');
    const nextBtn = document.getElementById('next');
    const prevBtn = document.getElementById('prev');
    const shuffleBtn = document.getElementById('shuffle');
    let musicFiles = <?php echo json_encode(array_values($musicFiles)); ?>;
    const albumCover = document.getElementById('album-cover');
    const albumCoverMap = <?php echo json_encode($albumCoverMap); ?>;
    const lyricsMap = <?php echo json_encode($lyricsMap); ?>;
    const metadataMap = <?php echo json_encode($metadataMap); ?>;
    const infoBtn = document.getElementById('track-info-btn');
    const infoTooltip = document.getElementById('track-info-tooltip');
    const lyricsContainer = document.getElementById('lyrics-container');
    const lyricsContent = document.getElementById('lyrics-content');
    const canvas = document.getElementById('visualizer');
    const canvasCtx = canvas.getContext('2d');
    let audioContext, analyser, dataArray;
    let animationFrameId;

    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function updateProgress() {
        progressBar.value = audioPlayer.currentTime;
        currentTimeEl.textContent = formatTime(audioPlayer.currentTime);
    }

    function updateDuration() {
        progressBar.max = audioPlayer.duration;
        durationEl.textContent = formatTime(audioPlayer.duration);
        updateMetadataTooltip();
    }

    function initAudioVisualizer() {
        if (!audioContext) {
            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const source = audioContext.createMediaElementSource(audioPlayer);
                analyser = audioContext.createAnalyser();
                analyser.fftSize = 64;
                source.connect(analyser);
                analyser.connect(audioContext.destination);
                
                const bufferLength = analyser.frequencyBinCount;
                dataArray = new Uint8Array(bufferLength);
                drawVisualizer();
            } catch (e) {
                console.error("AudioContext init error", e);
            }
        }
        if (audioContext && audioContext.state === 'suspended') {
            audioContext.resume();
        }
    }

    function drawVisualizer() {
        animationFrameId = requestAnimationFrame(drawVisualizer);
        if (!analyser) return;
        
        analyser.getByteFrequencyData(dataArray);
        canvasCtx.fillStyle = '#111';
        canvasCtx.fillRect(0, 0, canvas.width, canvas.height);
        
        const bufferLength = analyser.frequencyBinCount;
        const barWidth = canvas.width / bufferLength;
        let x = 0;
        let sum = 0;
        for (let i = 0; i < bufferLength; i++) {
            const barHeight = (dataArray[i] / 255) * canvas.height;
            canvasCtx.fillStyle = '<?= NEON_GREEN ?>';
            canvasCtx.fillRect(x, canvas.height - barHeight, barWidth - 2, barHeight);
            x += barWidth;
            
            // Sum up low frequencies for the beat effect
            if (i < bufferLength / 2) {
                sum += dataArray[i];
            }
        }
        
        // Pulse effect based on bass frequencies
        const average = sum / (bufferLength / 2);
        const scale = 1 + (average / 255) * 0.1; // Scale up to 1.1x
        albumCover.style.transform = `scale(${scale})`;
    }

    playPauseBtn.addEventListener('click', function() {
        initAudioVisualizer();
        if (audioPlayer.paused) {
            audioPlayer.play();
        } else {
            audioPlayer.pause();
        }
    });

    const ASCII_PLAY = ">";
    const ASCII_PAUSE = "||";

    audioPlayer.addEventListener('play', function() {
        playPauseBtn.textContent = ASCII_PAUSE;
    });
    audioPlayer.addEventListener('pause', function() {
        playPauseBtn.textContent = ASCII_PLAY;
    });
    audioPlayer.addEventListener('timeupdate', updateProgress);
    audioPlayer.addEventListener('loadedmetadata', function() {
        updateDuration();
        updateProgress();
    });
    progressBar.addEventListener('input', function() {
        audioPlayer.currentTime = progressBar.value;
    });

    function setTitleToCurrentTrack() {
        const idx = musicSelect.selectedIndex;
        if (idx >= 0) {
            const option = musicSelect.options[idx];
            document.title = option.text + ' | Special Music';
        }
    }

    function changeMusic(file) {
        initAudioVisualizer();
        audioSource.src = file;
        audioPlayer.load();
        audioPlayer.play();
        // Update page title
        setTitleToCurrentTrack();
    }

    musicSelect.addEventListener('change', setTitleToCurrentTrack);
    // Set initial title
    setTitleToCurrentTrack();

    function getCurrentIndex() {
        // Extract and decode the relative path from the audio source
        let srcPath = audioSource.src;
        try {
            srcPath = new URL(audioSource.src).pathname;
        } catch (e) {
            // fallback for local files
            srcPath = audioSource.src;
        }
        // Remove leading slash if present
        if (srcPath.charAt(0) === '/') srcPath = srcPath.substring(1);
        // Decode URI components
        srcPath = decodeURIComponent(srcPath);
        // Find index in musicFiles
        return musicFiles.indexOf(srcPath);
    }

    nextBtn.addEventListener('click', function() {
        let idx = getCurrentIndex();
        if (idx === -1) idx = 0;
        idx = (idx + 1) % musicFiles.length;
        musicSelect.selectedIndex = idx;
        changeMusic(musicFiles[idx]);
    });

    prevBtn.addEventListener('click', function() {
        let idx = getCurrentIndex();
        if (idx === -1) idx = 0;
        idx = (idx - 1 + musicFiles.length) % musicFiles.length;
        musicSelect.selectedIndex = idx;
        changeMusic(musicFiles[idx]);
    });

    shuffleBtn.addEventListener('click', function() {
        // Create array of option objects
        const options = Array.from(musicSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text
        }));

        // Fisher-Yates shuffle
        for (let i = options.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [options[i], options[j]] = [options[j], options[i]];
        }

        // Rebuild select and update musicFiles
        musicSelect.innerHTML = '';
        musicFiles = [];
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt.value;
            el.textContent = opt.text;
            musicSelect.appendChild(el);
            musicFiles.push(opt.value);
        });

        // Pick a random song to play
        const randomIdx = Math.floor(Math.random() * musicFiles.length);
        musicSelect.selectedIndex = randomIdx;
        changeMusic(musicFiles[randomIdx]);
        updateTrackInfo();
    });

    // Auto play next track when current ends
    audioPlayer.addEventListener('ended', function() {
        let idx = getCurrentIndex();
        if (idx === -1) idx = 0;
        idx = (idx + 1) % musicFiles.length;
        musicSelect.selectedIndex = idx;
        changeMusic(musicFiles[idx]);
    });

    // Set initial duration if metadata is loaded
    if (audioPlayer.readyState > 0) {
        updateDuration();
        updateProgress();
    } else {
        audioPlayer.addEventListener('loadedmetadata', function() {
            updateDuration();
            updateProgress();
        });
    }

    const onKeyDown = function(e) {
        // Ignore if focus is on input, textarea, or select
        const tag = document.activeElement.tagName.toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select') return;
        if (e.code === 'Space' || e.key === ' ') {
            e.preventDefault();
            initAudioVisualizer();
            if (audioPlayer.paused) {
                audioPlayer.play();
            } else {
                audioPlayer.pause();
            }
        } else if (e.code === 'ArrowRight') {
            e.preventDefault();
            nextBtn.click();
        } else if (e.code === 'ArrowLeft') {
            e.preventDefault();
            prevBtn.click();
        } else if (e.code === 'ArrowUp') {
            e.preventDefault();
            audioPlayer.currentTime = Math.min(audioPlayer.currentTime + 5, audioPlayer.duration || audioPlayer.currentTime);
        } else if (e.code === 'ArrowDown') {
            e.preventDefault();
            audioPlayer.currentTime = Math.max(audioPlayer.currentTime - 5, 0);
        }
    };
    document.addEventListener('keydown', onKeyDown);

    function updateAlbumCover() {
        const idx = musicSelect.selectedIndex;
        if (idx >= 0) {
            const option = musicSelect.options[idx];
            const trackName = option.text;
            albumCover.src = albumCoverMap[trackName] || albumCoverMap['default'];
        }
    }

    function updateLyrics() {
        const idx = musicSelect.selectedIndex;
        if (idx >= 0) {
            const option = musicSelect.options[idx];
            const trackName = option.text;
            if (lyricsMap[trackName]) {
                fetch(lyricsMap[trackName])
                    .then(r => r.text())
                    .then(text => {
                        lyricsContent.textContent = text;
                        lyricsContainer.style.display = 'block';
                        // Force reflow to enable transition
                        void lyricsContainer.offsetWidth;
                        lyricsContainer.classList.add('active');
                    });
            } else {
                lyricsContainer.classList.remove('active');
                // Wait for transition to finish before hiding
                setTimeout(() => {
                    lyricsContainer.style.display = 'none';
                    lyricsContent.textContent = '';
                }, 500);
            }
        }
    }

    infoBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        infoTooltip.classList.toggle('visible');
    });

    const onDocClick = (e) => {
        if (!infoBtn.contains(e.target) && !infoTooltip.contains(e.target)) {
            infoTooltip.classList.remove('visible');
        }
    };
    document.addEventListener('click', onDocClick);

    function updateMetadataTooltip() {
        const idx = musicSelect.selectedIndex;
        if (idx >= 0) {
            const option = musicSelect.options[idx];
            const trackName = option.text;
            const meta = metadataMap[trackName] || {};
            const size = meta.size || 0;
            const duration = audioPlayer.duration;
            
            let bitrate = 'Calculating...';
            if (size > 0 && duration > 0) {
                const kbps = Math.round((size * 8) / duration / 1000);
                bitrate = kbps + ' kbps';
            }
            
            let html = '';
            if (meta.artist) html += `<div class="info-row"><div class="info-label">Artist</div>${meta.artist}</div>`;
            if (meta.album) html += `<div class="info-row"><div class="info-label">Album</div>${meta.album}</div>`;
            if (meta.year) html += `<div class="info-row"><div class="info-label">Year</div>${meta.year}</div>`;
            if (meta.sample_rate) html += `<div class="info-row"><div class="info-label">Sample Rate</div>${meta.sample_rate} Hz</div>`;
            if (meta.channels) html += `<div class="info-row"><div class="info-label">Channels</div>${meta.channels == 1 ? 'Mono' : 'Stereo'}</div>`;
            if (meta.created_date) {
                const date = new Date(meta.created_date * 1000);
                html += `<div class="info-row"><div class="info-label">Created</div>${date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })}</div>`;
            }
            html += `<div class="info-row"><div class="info-label">Bitrate</div>${bitrate}</div>`;
            html += `<div class="info-row"><div class="info-label">File Size</div>${(size / 1024 / 1024).toFixed(2)} MB</div>`;
            
            infoTooltip.innerHTML = html;
        }
    }

    // Update cover on track change
    function updateTrackInfo() {
        updateAlbumCover();
        updateLyrics();
        updateMetadataTooltip();
    }
    musicSelect.addEventListener('change', updateTrackInfo);
    nextBtn.addEventListener('click', updateTrackInfo);
    prevBtn.addEventListener('click', updateTrackInfo);
    audioPlayer.addEventListener('ended', updateTrackInfo);
    // Also update on page load
    updateTrackInfo();

    // Cleanup function for SPA navigation
    window.pageCleanup = function() {
        document.removeEventListener('keydown', onKeyDown);
        document.removeEventListener('click', onDocClick);
        if (animationFrameId) cancelAnimationFrame(animationFrameId);
        if (audioContext && audioContext.state !== 'closed') {
            audioContext.close();
        }
        <?php if (isset($_GET['secret'])): ?>
        if (matrixInterval) clearInterval(matrixInterval);
        if (resizeMatrix) window.removeEventListener('resize', resizeMatrix);
        <?php endif; ?>
    };
}
</script>