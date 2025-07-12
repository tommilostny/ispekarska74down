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
    #custom-player {
        width: 90%;
        max-width: 600px;
        margin: 20px auto;
        background: #222;
        border: 2px solid #333;
        border-radius: 5px;
        padding: 20px;
        color: #fff;
        box-sizing: border-box;
        transition: width 0.3s;
    }
    @media (min-width: 900px) {
        #custom-player {
            width: 50%;
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
    /* Webkit browsers */
    #progress-bar::-webkit-slider-thumb {
        background: <?= NEON_GREEN ?>;
    }
    #progress-bar::-webkit-slider-runnable-track {
        background: <?= NEON_GREEN ?>33;
    }
    /* Firefox */
    #progress-bar::-moz-range-thumb {
        background: <?= NEON_GREEN ?>;
    }
    #progress-bar::-moz-range-track {
        background: <?= NEON_GREEN ?>33;
    }
    /* IE */
    #progress-bar::-ms-fill-lower {
        background: <?= NEON_GREEN ?>33;
    }
    #progress-bar::-ms-fill-upper {
        background: <?= NEON_GREEN ?>33;
    }
    .music-select-container {
        width: 90%;
        max-width: 600px;
        margin: 0 auto 0 auto;
        box-sizing: border-box;
    }
    @media (min-width: 900px) {
        .music-select-container {
            width: 50%;
        }
    }
    .music-select-label {
        margin: 20px 0 5px 0;
        padding: 0;
        font-size: 1rem;
        color: #fff;
        text-align: left;
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

// Build album cover mapping in PHP (base64-encoded data URLs)
function imageToDataUrl($path) {
    if (!file_exists($path)) return null;
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = base64_encode($data);
    $mime = ($type === 'png') ? 'image/png' : 'image/jpeg';
    return 'data:' . $mime . ';base64,' . $base64;
}
$loadingBase64 = imageToDataUrl(ALBUM_COVERS_DIR . '/loading.png');
$albumCoverMap = [];
foreach ($musicFiles as $file) {
    $trackName = str_replace('.mp3', '', basename($file));
    $coverPng = ALBUM_COVERS_DIR . '/' . $trackName . '.png';
    $coverJpg = ALBUM_COVERS_DIR . '/' . $trackName . '.jpg';
    if (file_exists($coverPng)) {
        $albumCoverMap[$trackName] = imageToDataUrl($coverPng);
    } elseif (file_exists($coverJpg)) {
        $albumCoverMap[$trackName] = imageToDataUrl($coverJpg);
    } else {
        $albumCoverMap[$trackName] = imageToDataUrl(ALBUM_COVERS_DIR . '/Na Pekařské 74.png');
    }
}
?>
<table>
    <tr>
        <td><a href="?page=home">&lt;&lt; Home</a></td>
        <td class="neon_green"><strong>Our special music</strong></td>
    </tr>
</table>
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
        <img id="album-cover" src="<?= $loadingBase64 ?>" alt="Album cover" style="max-width:220px; max-height:220px; border-radius:10px; box-shadow:0 0 10px #000; background:#222; object-fit:cover;" loading="lazy">
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

    playPauseBtn.addEventListener('click', function() {
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

    // Update cover on track change
    musicSelect.addEventListener('change', updateAlbumCover);
    nextBtn.addEventListener('click', updateAlbumCover);
    prevBtn.addEventListener('click', updateAlbumCover);
    audioPlayer.addEventListener('ended', updateAlbumCover);
    // Also update on page load
    updateAlbumCover();
</script>