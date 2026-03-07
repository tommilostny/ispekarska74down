<?php
session_start();
require_once 'constants.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$login_error = false;
if (isset($_POST['password'])) {
    if ($_POST['password'] === 'pekar74') {
        $_SESSION['auth'] = true;
        unset($_SESSION['failed_attempts']);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $login_error = true;
        
        // Log failed attempt
        $log_entry = sprintf("[%s] Failed login from IP: %s | UA: %s\n", date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        file_put_contents('failed_logins.txt', $log_entry, FILE_APPEND);

        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = 0;
        }
        $_SESSION['failed_attempts']++;

        if ($_SESSION['failed_attempts'] === 3) {
            $to = 'tommilostny@live.com';
            $subject = 'Security Alert: Failed Login Attempts - ispekarska74down.xf.cz';
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            $message = "<html><body>";
            $message .= "<h2>Security Alert: 3 Failed Login Attempts</h2>";
            $message .= "<p>Site: <strong>ispekarska74down.xf.cz</strong></p>";
            $message .= "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
            $message .= "<p><strong>IP Address:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>";
            $message .= "<p><strong>User Agent:</strong> " . $_SERVER['HTTP_USER_AGENT'] . "</p>";
            $message .= "</body></html>";
            
            @mail($to, $subject, $message, $headers);
        }
    }
}

if (!isset($_SESSION['auth'])) {
    $page = 'login';
} else {
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Is Pekařská 74 down?</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Is Pekařská 74 down?">
    <meta name="keywords" content="Pekařská 74, outage, down, electricity, internet, vodafone, eg.d">
    <meta name="author" content="Tomáš Milostný">
    <link rel="icon" type="image/png" href="electricity.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        html {
            background-color: #000;
        }
        body {
            background-color: #000;
            color: #fff; /* Light text color */
            font-family: 'Roboto Mono', monospace;
            font-size: 1.125rem;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-wrapper {
            background-color: #0e0e0e; /* Dark background color */
            width: 100%;
            min-height: 100vh;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            <?php if ($page == 'home'): ?>
                justify-content: center;
            <?php endif; ?>
            text-align: center;
            padding: 5px;
            animation: turn-on 0.33s cubic-bezier(0.23, 1, 0.32, 1) forwards;
            transform-origin: center;
        }

        body.turn-off .main-wrapper {
            animation: turn-off 0.33s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }

        h1 {
            font-size: 4rem;
        }

        .neon_green {
            color: <?= NEON_GREEN ?>;
            text-shadow: <?= NEON_GREEN_GLOW ?>;
        }

        .neon_red {
            color: <?= NEON_RED ?>;
            text-shadow: <?= NEON_RED_GLOW ?>;
        }

        table {
            margin: 20px;
        }

        td {
            padding: 15px;
        }

        a, a:visited, a:active {
            color: <?= NEON_AZURE ?>;
            text-shadow: <?= NEON_AZURE_GLOW ?>;
            text-decoration: none;
            font-weight: bold;
        }
        
        a:hover {
            background-color: <?= NEON_AZURE_DARKER ?>;
            color: black;
            text-shadow: <?= NEON_AZURE_GLOW ?>;
            text-decoration: none;
        }

        .external-link, .external-link:visited, .external-link:active {
            color: <?= NEON_YELLOW ?>;
            text-shadow: <?= NEON_YELLOW_GLOW ?>;
            text-decoration: none;
        }

        .external-link:hover {
            background-color: <?= NEON_YELLOW_DARKER ?>;
            color: black;
            text-shadow: <?= NEON_YELLOW_GLOW ?>;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            color: <?= NEON_RED ?> !important;
            text-shadow: <?= NEON_RED_GLOW ?>;
            border: 1px solid <?= NEON_RED ?>;
            padding: 5px 10px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.2s;
            z-index: 100;
        }
        .logout-btn:hover {
            background-color: <?= NEON_RED ?>;
            color: #000 !important;
            box-shadow: <?= NEON_RED_GLOW ?>;
        }

        .crt-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                rgba(18, 16, 16, 0) 50%, 
                rgba(0, 0, 0, 0.25) 50%
            ), linear-gradient(
                90deg, 
                rgba(255, 0, 0, 0.06), 
                rgba(0, 255, 0, 0.02), 
                rgba(0, 0, 255, 0.06)
            );
            background-size: 100% 2px, 3px 100%;
            pointer-events: none;
            z-index: 9999;
            box-shadow: inset 0 0 5rem rgba(0, 0, 0, 0.5);
            animation: flicker 0.15s infinite;
            overflow: hidden;
        }

        .crt-overlay::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255, 255, 255, 0), rgba(255, 255, 255, 0) 40%, rgba(255, 255, 255, 0.04) 50%, rgba(255, 255, 255, 0) 60%);
            animation: scanline 8s linear infinite;
            pointer-events: none;
        }

        @keyframes flicker {
            0% { opacity: 0.9; }
            5% { opacity: 0.65; }
            10% { opacity: 0.8; }
            15% { opacity: 0.65; }
            20% { opacity: 0.95; }
            25% { opacity: 0.75; }
            30% { opacity: 0.65; }
            35% { opacity: 0.85; }
            40% { opacity: 0.95; }
            45% { opacity: 0.65; }
            50% { opacity: 0.95; }
            55% { opacity: 0.65; }
            60% { opacity: 0.85; }
            65% { opacity: 0.7; }
            70% { opacity: 1; }
            75% { opacity: 0.75; }
            80% { opacity: 0.9; }
            85% { opacity: 0.65; }
            90% { opacity: 0.95; }
            95% { opacity: 0.7; }
            100% { opacity: 0.9; }
        }

        @keyframes scanline {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        .screen-content {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: jitter 0.23s infinite;
        }

        @keyframes jitter {
            0% { transform: translateX(0); }
            10% { transform: translateX(-0.5px); }
            20% { transform: translateX(0.5px); }
            30% { transform: translateX(0); }
            40% { transform: translateX(0.5px); }
            50% { transform: translateX(-0.5px); }
            60% { transform: translateX(0.5px); }
            70% { transform: translateX(0); }
            80% { transform: translateX(-1px); }
            90% { transform: translateX(0.5px); }
            100% { transform: translateX(0); }
        }

        @keyframes turn-on {
            0% { transform: scale(1, 0.005) scaleX(0); filter: brightness(30); opacity: 0; }
            30% { transform: scale(1, 0.005) scaleX(1); filter: brightness(10); opacity: 1; }
            100% { transform: scale(1, 1) scaleX(1); filter: brightness(1); opacity: 1; }
        }

        @keyframes turn-off {
            0% { transform: scale(1, 1) scaleX(1); filter: brightness(1); opacity: 1; }
            60% { transform: scale(1, 0.005) scaleX(1); filter: brightness(10); opacity: 1; }
            100% { transform: scale(1, 0.005) scaleX(0); filter: brightness(30); opacity: 0; }
        }

    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php if (isset($_SESSION['auth'])): ?>
            <a href="?logout=1" class="logout-btn">LOGOUT</a>
        <?php endif; ?>
        <div class="screen-content">
            <?php
            if (!include_once "$page.php") {
                echo "<h1>404 Not Found</h1>";
            }
            ?>
        </div>
        <div class="crt-overlay"></div>
    </div>
    <audio id="crt-on-sound" src="crt_on.mp3" preload="auto"></audio>
    <audio id="crt-off-sound" src="crt_off.mp3" preload="auto"></audio>
    <script>
        // Handle bfcache restores (browser back button from cache)
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                document.body.classList.remove('turn-off');
                // Trigger reflow to restart animation if needed, though removing class is usually enough
                void document.body.offsetWidth;
            }
        });

        const sndOn = document.getElementById('crt-on-sound');
        const sndOff = document.getElementById('crt-off-sound');

        function playCrtSound(type) {
            const audio = type === 'on' ? sndOn : sndOff;
            audio.currentTime = 0;
            audio.play().catch(e => console.log('Autoplay prevented:', e));
        }

        // Try to play on initial load
        window.addEventListener('load', () => playCrtSound('on'));

        function attachLinkListeners() {
            document.querySelectorAll('a[target="_blank"]').forEach(link => {
                link.classList.add('external-link');
            });

            document.querySelectorAll('a:not([target="_blank"]):not(.logout-btn):not([href^="mailto:"])').forEach(link => {
                // Remove old listeners to prevent duplicates if re-running
                link.removeEventListener('click', handleLinkClick);
                link.addEventListener('click', handleLinkClick);
            });

            const logoutBtn = document.querySelector('.logout-btn');
            if (logoutBtn) {
                logoutBtn.removeEventListener('click', handleLogoutClick);
                logoutBtn.addEventListener('click', handleLogoutClick);
            }
        }

        function handleLinkClick(e) {
            e.preventDefault();
            const href = this.href;
            if (href === window.location.href) return;
            loadPage(href, true);
        }

        function handleLogoutClick(e) {
            e.preventDefault();
            playCrtSound('off');
            document.body.classList.remove('turn-on');
            document.body.classList.add('turn-off');
            setTimeout(() => {
                window.location.href = this.href;
            }, 500);
        }

        async function loadPage(url, pushState = true) {
            // 1. Animate Turn Off
            playCrtSound('off');
            document.body.classList.remove('turn-on');
            document.body.classList.add('turn-off');

            // 2. Wait for animation
            await new Promise(r => setTimeout(r, 500));

            try {
                // 3. Fetch new content
                const response = await fetch(url);
                const text = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const newContent = doc.querySelector('.screen-content').innerHTML;
                const newTitle = doc.title;

                // 4. Cleanup previous page logic
                if (window.pageCleanup) {
                    window.pageCleanup();
                    window.pageCleanup = null;
                }

                // 5. Update DOM and History
                if (pushState) {
                    history.pushState({}, '', url);
                }
                document.title = newTitle;
                const contentDiv = document.querySelector('.screen-content');
                contentDiv.innerHTML = newContent;

                // 6. Execute Scripts from new content
                const scripts = contentDiv.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                // 7. Re-attach listeners and Animate Turn On
                attachLinkListeners();
                document.body.classList.remove('turn-off');
                // Force reflow to restart turn-on animation
                void document.body.offsetWidth;
                document.body.classList.add('turn-on'); // Ensure class is there (or rely on removing turn-off)
                playCrtSound('on');
                
                // Remove the turn-on class after animation to allow re-triggering later if needed
                // But CSS has it on 'body' by default. 
                // To re-trigger: remove 'turn-on', reflow, add 'turn-on'.
                document.body.style.animation = 'none';
                document.body.offsetHeight; /* trigger reflow */
                document.body.style.animation = null; 
                
            } catch (error) {
                console.error('Navigation failed:', error);
                window.location.href = url; // Fallback to standard navigation
            }
        }

        // Handle Browser Back/Forward Buttons
        window.addEventListener('popstate', () => {
            loadPage(window.location.href, false);
        });

        // Initial setup
        attachLinkListeners();
    </script>
</body>
</html>
