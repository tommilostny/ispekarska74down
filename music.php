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
        font-family: 'Courier New', Courier, monospace;
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
        border: none;
        color: <?= NEON_AZURE ?>;
        font-family: inherit;
        cursor: pointer;
        transition: color 0.2s;
    }
    #play-pause {
        font-size: 2rem;
    }
    #prev, #next {
        font-size: 1.5rem;
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
</style>
<?php

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
$defaultCoverFilename = 'Na Pekařské 74.png';
$defaultCoverPath = ALBUM_COVERS_DIR . '/' . $defaultCoverFilename;
$defaultCoverUrl = file_exists($defaultCoverPath) ? ALBUM_COVERS_DIR . '/' . rawurlencode($defaultCoverFilename) : '';

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
            <button id="prev">⏮️</button>
            <button id="play-pause">▶️</button>
            <button id="next">⏭️</button>
        </div>
    </div>
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
    const audioPlayer = document.getElementById('audio-player');
    const audioSource = document.getElementById('audio-source');
    const playPauseBtn = document.getElementById('play-pause');
    const progressBar = document.getElementById('progress-bar');
    const currentTimeEl = document.getElementById('current-time');
    const durationEl = document.getElementById('duration');
    const musicSelect = document.getElementById('music-select');
    const nextBtn = document.getElementById('next');
    const prevBtn = document.getElementById('prev');
    const musicFiles = <?php echo json_encode(array_values($musicFiles)); ?>;
    const albumCover = document.getElementById('album-cover');
    const albumCoverMap = <?php echo json_encode($albumCoverMap); ?>;
    const lyricsMap = <?php echo json_encode($lyricsMap); ?>;
    const lyricsContainer = document.getElementById('lyrics-container');
    const lyricsContent = document.getElementById('lyrics-content');
    const canvas = document.getElementById('visualizer');
    const canvasCtx = canvas.getContext('2d');
    let audioContext, analyser, dataArray;

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
        requestAnimationFrame(drawVisualizer);
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

    audioPlayer.addEventListener('play', function() {
        playPauseBtn.textContent = '⏸️';
    });
    audioPlayer.addEventListener('pause', function() {
        playPauseBtn.textContent = '▶️';
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

    document.addEventListener('keydown', function(e) {
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
    });

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

    // Update cover on track change
    function updateTrackInfo() {
        updateAlbumCover();
        updateLyrics();
    }
    musicSelect.addEventListener('change', updateTrackInfo);
    nextBtn.addEventListener('click', updateTrackInfo);
    prevBtn.addEventListener('click', updateTrackInfo);
    audioPlayer.addEventListener('ended', updateTrackInfo);
    // Also update on page load
    updateTrackInfo();
</script>