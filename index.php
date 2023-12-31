<?php
/**
 * IPTV Web Player
 * Index page - Insert M3U URL or upload M3U file
 * 
 * @author mkcdr
 * @copyright 2023
 */

error_reporting(0);

$darkmode = isset($_COOKIE['darkmode']) ? ($_COOKIE['darkmode'] == '1') : true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="IPTV Web Player for playing Live TV streams using M3U URL or M3U file">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPTV Web Player</title>
    <style>
        :root {
            --text-color: #f1f1f1;
            --mute-color: #a0a0a0;
            --hover-color: #ffffff;
            --header-color: #2196F3;
            --highlight-color: #87ceeb;
            --dark-background: #000000;
            --primary-background: #111111;
            --secondry-background: #1d1d1d;
            --warning-color: #857919;
            --hover-background: rgb(35 54 255);
            --border-color: rgb(255 255 255 / 15%);
            --scrollbar-track: rgb(255 255 255 / 5%);
            --scrollbar-thumb: rgb(51 68 255);
            --scrollbar-hover: rgb(79 94 255);
        }

        body.light {
            --text-color: #1d1d1d;
            --mute-color: #444444;
            --hover-color: #000000;
            --highlight-color: #36acdc;
            --dark-background: #ffffff;
            --primary-background: #f9f9f9;
            --secondry-background: #e3e3e3;
            --warning-color: #fff395;
            --hover-background: rgb(182 188 255);
            --border-color: rgb(0 0 0 / 15%);
            --scrollbar-track: rgb(0 0 0 / 5%);
        }
        
        *, *::after, *::before {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: var(--primary-background);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        a {
            color: #2196f3;
            text-decoration: none;
        }

        a:hover {
            color: #41abff;
        }
        
        .heart-icon::before {
            content: '♥';
            color: red;
        }

        .alert {
            position: relative;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .alert .close {
            color: inherit;
            padding: .5rem;
            position: absolute;
            right: .5rem;
            top: .3rem;
            opacity: .5;
            cursor: pointer;
        }

        .alert .close:hover {
            opacity: 1;
        }

        .alert .close::before {
            content: '×';
            font-size: 20px;
        }

        .warning {
            background-color: var(--warning-color);
        }

        .darkmode-switch {
            position: relative;
            cursor: pointer;
            display: block;
            transition: .3s justify-content;
            padding: 4px;
            width: 80px;
            height: 40px;
            border-radius: 40px;
            border: 1px solid var(--border-color);
            background-color: var(--dark-background);
            background-image: url("data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20id%3D%22SVGRepo_bgCarrier%22%20stroke-width%3D%220%22%3E%3C%2Fg%3E%3Cg%20id%3D%22SVGRepo_tracerCarrier%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3C%2Fg%3E%3Cg%20id%3D%22SVGRepo_iconCarrier%22%3E%20%3Cpath%20d%3D%22M13%206V3M18.5%2012V7M14.5%204.5H11.5M21%209.5H16M15.5548%2016.8151C16.7829%2016.8151%2017.9493%2016.5506%2019%2016.0754C17.6867%2018.9794%2014.7642%2021%2011.3698%2021C6.74731%2021%203%2017.2527%203%2012.6302C3%209.23576%205.02061%206.31331%207.92462%205C7.44944%206.05072%207.18492%207.21708%207.18492%208.44523C7.18492%2013.0678%2010.9322%2016.8151%2015.5548%2016.8151Z%22%20stroke%3D%22%2395bfb9%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3C%2Fpath%3E%20%3C%2Fg%3E%3C%2Fsvg%3E"),
                              url("data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20id%3D%22SVGRepo_bgCarrier%22%20stroke-width%3D%220%22%3E%3C%2Fg%3E%3Cg%20id%3D%22SVGRepo_tracerCarrier%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3C%2Fg%3E%3Cg%20id%3D%22SVGRepo_iconCarrier%22%3E%20%3Cpath%20d%3D%22M12%203V4M12%2020V21M4%2012H3M6.31412%206.31412L5.5%205.5M17.6859%206.31412L18.5%205.5M6.31412%2017.69L5.5%2018.5001M17.6859%2017.69L18.5%2018.5001M21%2012H20M16%2012C16%2014.2091%2014.2091%2016%2012%2016C9.79086%2016%208%2014.2091%208%2012C8%209.79086%209.79086%208%2012%208C14.2091%208%2016%209.79086%2016%2012Z%22%20stroke%3D%22%23ffb000%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3C%2Fpath%3E%20%3C%2Fg%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-size: 25px, 25px;
            background-position: 47px center, 6px center;
        }

        .darkmode-switch::before {
            position: absolute;
            left: 4px;
            content: '';
            display: block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            background-color: var(--primary-background);
            transition: .25s left ease-in-out;
        }

        .light .darkmode-switch::before {
            left: 44px;
        }

        .app {
            width: 1245px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .app header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0 1.5rem;
        }

        .app header h1 {
            display: flex;
            align-items: flex-end;
            margin: 0;
            color: var(--highlight-color);
            font-weight: 900;
        }

        .app header h1 svg {
            width: 60px;
            margin-inline-end: .5rem;
        }

        .app main form {
            max-width: 480px;
        }

        .app main form label {
            display: block;
            font-size: 14px;
            color: var(--mute-color);
            margin-bottom: .5rem;
        }

        .app main form input[type='text'],
        .app main form input[type='file'] {
            width: 100%;
            display: block;
            outline: none;
            color: inherit;
            padding: .475rem;
            margin-bottom: 1rem;
            border-radius: 3px;
            border: 1px solid var(--border-color);
            background-color: var(--secondry-background);
        }

        .app main form input[type='text']:focus,
        .app main form input[type='file']:focus {
            background-color: var(--dark-background);
            box-shadow: 0 0 4px 4px rgb(0 143 255 / 35%);
        }

        .app main form .or {
            display: block;
            text-align: center;
            font-size: 80%;
            font-weight: bold;
            color: var(--mute-color);
        }

        .app main form button {
            cursor: pointer;
            color: #ffffff;
            background-color: #2196F3;
            border: 1px solid var(--border-color);
            border-radius: 3px;
            padding: .5rem .75rem;
        }

        .app main form button:hover {
            background-color: #2E7FC0;
        }

        .app footer {
            padding: 2rem 0;
            color: var(--mute-color);
            font-size: 90%;
            font-family: monospace;
            text-align: center;
        }

        @media screen and (max-width: 1245px) {
            .app {
                width: 100%;
            }

            .app header h1 span {
                display: none;
            }
        }

        @media screen and (max-width: 510px) {
            .app main form button  {
                width: 100%;
            }
        }
    </style>
</head>
<body <?= $darkmode ? '' : 'class="light"' ?>>
    <div class="app">
        <header>
            <h1>
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="currentcolor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><defs><style>.a{fill:none;stroke:currentcolor;stroke-linecap:round;stroke-linejoin:round;}</style></defs><path class="a" d="M29.1054,42.5c2.4982-.4014,6.8851-.0414,8.66-5.2954M37.5724,18.459c-.8734-2.1281-2.52-2.998-5.612-3.176-5.5822-.3212-18.3095,1.47-22.9566,2.83C5.733,19.0708,5.73,23.0159,5.73,23.0159"></path><line class="a" x1="9.8595" y1="23.2429" x2="10.4575" y2="34.8111"></line><path class="a" d="M13.6792,34.638,13.0822,23.07l3.7183-.2a3.91,3.91,0,0,1,.4028,7.8088l-3.7182.2"></path><line class="a" x1="23.885" y1="22.4897" x2="31.4638" y2="22.0828"></line><line class="a" x1="28.3433" y1="33.8511" x2="27.7452" y2="22.2829"></line><polyline class="a" points="42.27 21.505 39.149 33.273 34.69 21.912"></polyline><path class="a" d="M20.34,16.0407,15.6578,7.9756"></path><path class="a" d="M22.11,15.772l1.8188-7.4109"></path><circle class="a" cx="15.0147" cy="6.8216" r="1.3216"></circle><circle class="a" cx="24.3426" cy="7.1057" r="1.3216"></circle></g></svg>
                <span>IPTV Web Player</span>
            </h1>
            <span class="darkmode-switch"></span>
        </header>
        <main>
            <p>IPTV Web Player for playing Live TV streams using M3U URL or M3U file.</p>
            <form action="watch.php" method="post" enctype="multipart/form-data">
                <label for="m3uurl">M3U URL</label>
                <input type="text" name="url" id="m3uurl" />
                <span class="or">OR</span>
                <label for="m3ufile">M3U File</label>
                <input type="file" name="m3ufile" id="m3ufile" />
                <button type="submit">Watch</button>
            </form>
        </main>
        <footer>
            Made by <a href="https://github.com/mkcdr" target="_blank" rel="noopener noreferrer">@mkcdr</a> with <span class="heart-icon"></span>
        </footer>
    </div>
    <script>
        document.querySelector('.darkmode-switch').addEventListener('click', () => {
            document.body.classList.toggle('light');
            expires = new Date();
            expires.setDate(expires.getDate() + 365);
            document.cookie = 'darkmode=' + (document.body.classList.contains('light') ? 0 : 1) + '; expires=' + expires.toUTCString();
        });
    </script>
</body>
</html>