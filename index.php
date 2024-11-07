<?php require_once 'constants.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Is Pekařská 74 down?</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="electricity.png">
    <style>
        body {
            background-color: #0e0e0e; /* Dark background color */
            color: #fff; /* Light text color */
            font-family: monospace; /* 'Roboto', sans-serif; */
            font-size: 1.125rem;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 90vh;
            text-align: center;
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
        }
        
        a:hover {
            color: <?= NEON_AZURE_DARKER ?>;
            text-shadow: <?= NEON_AZURE_GLOW ?>;
        }

        .external-link, .external-link:visited, .external-link:active {
            color: <?= NEON_YELLOW ?>;
            text-shadow: <?= NEON_YELLOW_GLOW ?>;
        }
    </style>
</head>
<body>
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
    if (!include_once "$page.php") {
        echo "<h1>404 Not Found</h1>";
    }
    ?>
    <script>
        document.querySelectorAll('a[target="_blank"]').forEach(link => {
            link.classList.add('external-link');
        });
    </script>
</body>
</html>
