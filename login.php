<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header('HTTP/1.0 403 Forbidden');
    die();
}
$show_anim = !$login_error;
?>
<style>
    .login-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        width: 100%;
        max-width: 600px;
        margin-top: 15vh;
        font-family: 'Roboto Mono', monospace;
    }
    .terminal-line {
        margin-bottom: 10px;
        text-align: left;
        width: 100%;
        color: <?= NEON_GREEN ?>;
        text-shadow: <?= NEON_GREEN_GLOW ?>;
    }
    .input-wrapper {
        display: flex;
        width: 100%;
        align-items: center;
    }
    .prompt {
        color: <?= NEON_GREEN ?>;
        text-shadow: <?= NEON_GREEN_GLOW ?>;
        margin-right: 10px;
        white-space: nowrap;
    }
    #password-input {
        background: transparent;
        border: none;
        color: <?= NEON_GREEN ?>;
        text-shadow: <?= NEON_GREEN_GLOW ?>;
        font-family: inherit;
        font-size: inherit;
        flex-grow: 1;
        outline: none;
        caret-color: <?= NEON_GREEN ?>;
        text-transform: uppercase;
    }
    .error-msg {
        color: <?= NEON_RED ?>;
        text-shadow: <?= NEON_RED_GLOW ?>;
        margin-top: 20px;
        min-height: 1.2em;
        font-weight: bold;
    }
    
    <?php if ($show_anim): ?>
    .anim-type {
        overflow: hidden;
        white-space: nowrap;
        border-right: .15em solid <?= NEON_GREEN ?>;
        animation: typing 2s steps(40, end), blink-caret .75s step-end 3 forwards;
        max-width: fit-content;
    }
    .anim-fade-1 {
        opacity: 0;
        animation: fadeIn 0.5s ease-in 2s forwards;
    }
    .anim-fade-2 {
        opacity: 0;
        animation: fadeIn 0.5s ease-in 2.5s forwards;
    }
    @keyframes typing { from { width: 0 } to { width: 100% } }
    @keyframes blink-caret { from, to { border-color: transparent } 50% { border-color: <?= NEON_GREEN ?> } }
    @keyframes fadeIn { to { opacity: 1; } }
    <?php else: ?>
    .anim-type { border-right: none; }
    <?php endif; ?>

    /* Prevent browser autofill styling */
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus, 
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px #0e0e0e inset !important;
        -webkit-text-fill-color: <?= NEON_GREEN ?> !important;
        transition: background-color 5000s ease-in-out 0s;
    }
</style>

<div class="login-container">
    <div class="terminal-line anim-type">ESTABLISHING SECURE CONNECTION...</div>
    <div class="terminal-line <?= $show_anim ? 'anim-fade-1' : '' ?>">ACCESS RESTRICTED. AUTHORIZED PERSONNEL ONLY.</div>
    
    <form method="post" style="width: 100%;" class="<?= $show_anim ? 'anim-fade-2' : '' ?>" autocomplete="off">
        <div class="input-wrapper">
            <span class="prompt">root@ispekarska74down:~$</span>
            <input type="password" name="password" id="password-input" autocomplete="new-password" autofocus>
        </div>
    </form>

    <?php if ($login_error): ?>
    <div class="error-msg">ACCESS DENIED: INVALID CREDENTIALS</div>
    <?php endif; ?>
</div>

<script>
    const pwdInput = document.getElementById('password-input');
    pwdInput.value = ''; // Ensure empty on load
    document.addEventListener('click', () => {
        pwdInput.focus();
    });
    // Ensure focus
    setTimeout(() => pwdInput.focus(), 100);
</script>