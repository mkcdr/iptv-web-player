<?php
/**
 * IPTV Web Player
 * Watch and view IPTV channels
 * 
 * @author mkcdr
 * @copyright 2023
 */

error_reporting(0);

require_once __DIR__ . '/iptv.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    header('Location: index.php');
    exit;
}

if (isset($_FILES['m3ufile']) && $_FILES['m3ufile']['error'] == UPLOAD_ERR_OK)
{
    $m3uURL = $_FILES['m3ufile']['tmp_name'];
}
elseif (!empty($_POST['url']) && filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL))
{
    $m3uURL = $_POST['url'];
}
else
{
    header('Location: index.php');
    exit;
}

$proctime = microtime(true);
$channels = iptv_load_playlist($m3uURL);
$proctime = microtime(true) - $proctime;

$darkmode = isset($_COOKIE['darkmode']) ? ($_COOKIE['darkmode'] == '1') : true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPTV Player</title>
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet" />
    <script defer src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
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

        #channel-selector {
            display: none;
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            color: var(--highlight-color);
            width: 300px;
            height: 100px;
            line-height: 92px;
            padding: 0 1rem;
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 99;
            transform: translate(-50%, -50%);
            background-color: var(--dark-background);
            border-radius: 25px;
            border: 6px solid var(--border-color);
            box-shadow: 0 0 20px #1c5467;
        }

        .embed-16by9 {
            position: relative;
            display: block;
            width: 100%;
            padding: 0;
            overflow: hidden;
        }

        .embed-16by9::before {
            padding-top: 56.25%;
            display: block;
            content: "";
        }

        .embed-16by9 video,
        .embed-16by9 iframe {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100%;
            width: 100%;
            border: 0;
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

        .app main {
            display: flex;
            column-gap: 15px;
            flex-direction: row;
            position: relative;
        }

        .app .video-container {
            width: 720px;
        }

        .app nav {
            width: 480px;
            height: 540px;
            display: flex;
            flex-direction: column;
            background-color: var(--secondry-background);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .app nav p {
            margin: 0;
            padding: 1rem;
        }

        .app nav .channels-list-btn-toggle {
            display: none;
            position: absolute;
            right: -64px;
            top: 14px;
            padding: .5rem;
            width: 50px;
            height: 50px;
            cursor: pointer;
            opacity: .4;
            border: 0;
            border-radius: 50%;
            background-color: #e0e0e0;
        }

        .app nav .channels-list-btn-toggle:hover {
            opacity: 1;
        }

        .app nav #channel-search {
            display: none;
            border: none;
            outline: none;
            padding: 1rem;
            color: inherit;
            background-color: var(--primary-background);
        }

        .app nav.search-open #channel-search {
            display: inline-block;
        }

        .app .nav-header {
            display: flex;
            flex-direction: row;
            justify-items: stretch;
            justify-content: space-between;
        }

        .app .nav-header h2 {
            margin: 0;
            padding: 1rem;
            font-size: 1rem;
            font-weight: normal;
            text-transform: uppercase;
            color: var(--mute-color);
        }

        .app .nav-header button {
            outline: none;
            border: none;
            cursor: pointer;
            padding: 0 1rem;
            color: var(--text-color);
            background: rgb(0, 0, 0, 0);
        }

        .app .nav-header button:hover,
        .app nav.search-open .nav-header button {
            color: var(--hover-color);
            background-color: var(--primary-background);
        }

        .app .nav-header button svg {
            width: 20px;
        }

        .app nav ol {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow-y: scroll;
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
            border-top: 1px solid var(--border-color);
        }

        .app nav ol::-webkit-scrollbar {
            width: 10px;
            cursor: pointer;
        }

        .app nav ol::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
        }
        
        .app nav ol::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
        }

        .app nav ol::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-hover);
        }

        .app nav ol li a {
            outline: none;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            height: 80px;
            padding-inline: 1rem;
            justify-content: space-between;
        }

        .app nav ol li a.selected {
            background-color: var(--border-color);
            color: var(--hover-color);
        }

        .app nav ol li a:hover {
            background-color: var(--hover-background);
            color: var(--hover-color);
        }

        .app nav ol li a img {
            margin-inline-start: 0.8rem;
            max-height: 80px;
            max-width: 50px;
        }

        .app nav ol li a .channel-info {
            display: flex;
        }

        .app nav ol li a .channel-num {
            color: var(--highlight-color);
            font-weight: bold;
            margin-inline-end: 0.5rem;
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

            .app main {
                overflow: hidden;
            }

            .app .video-container {
                width: 100%;
            }

            .app nav {
                position: absolute;
                left: -50%;
                z-index: 10;
                width: 50%;
                height: 100%;
                border: 0;
                border-radius: 0;
                color: #f1f1f1;
                background-color: rgb(0 0 0 / 70%);
                overflow: visible;
                transition: .3s left ease-out;
            }

            .app nav.open {
                left: 0;
            }

            .app nav .channels-list-btn-toggle {
                display: inline-block;
            }

            .app .nav-header {
                background-color: var(--secondry-background);
            }

            .app nav ol li a {
                color: #f1f1f1;
            }

            .app nav ol li a:hover {
                color: #ffffff;
                background-color: rgb(33 150 243 / 45%);
            }

            .app nav ol li a.selected {
                color: #ffffff;
                background-color: rgb(255 255 255 / 15%);
            }
        }
    </style>
</head>
<body <?= $darkmode ? '' : 'class="light"' ?>>
    <div id="channel-selector"></div>
    <div class="app">
        <header>
            <h1>
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="currentcolor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><defs><style>.a{fill:none;stroke:currentcolor;stroke-linecap:round;stroke-linejoin:round;}</style></defs><path class="a" d="M29.1054,42.5c2.4982-.4014,6.8851-.0414,8.66-5.2954M37.5724,18.459c-.8734-2.1281-2.52-2.998-5.612-3.176-5.5822-.3212-18.3095,1.47-22.9566,2.83C5.733,19.0708,5.73,23.0159,5.73,23.0159"></path><line class="a" x1="9.8595" y1="23.2429" x2="10.4575" y2="34.8111"></line><path class="a" d="M13.6792,34.638,13.0822,23.07l3.7183-.2a3.91,3.91,0,0,1,.4028,7.8088l-3.7182.2"></path><line class="a" x1="23.885" y1="22.4897" x2="31.4638" y2="22.0828"></line><line class="a" x1="28.3433" y1="33.8511" x2="27.7452" y2="22.2829"></line><polyline class="a" points="42.27 21.505 39.149 33.273 34.69 21.912"></polyline><path class="a" d="M20.34,16.0407,15.6578,7.9756"></path><path class="a" d="M22.11,15.772l1.8188-7.4109"></path><circle class="a" cx="15.0147" cy="6.8216" r="1.3216"></circle><circle class="a" cx="24.3426" cy="7.1057" r="1.3216"></circle></g></svg>
                <span>IPTV Web Player</span>
            </h1>
            <span class="darkmode-switch"></span>
        </header>
        <div class="alert warning">
            VideoJS requires the video to be muted by default to allow Live stream, please unmute.
            <span class="close"></span>
        </div>
        <main>
            <nav id="channels-list">
                <button class="channels-list-btn-toggle">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g fill="#555555"> <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9584 1.25H12.0416C13.4108 1.24999 14.4957 1.24998 15.3621 1.33812C16.2497 1.42841 16.9907 1.61739 17.639 2.05052C18.1576 2.39707 18.6029 2.84239 18.9495 3.36104C19.3826 4.00926 19.5716 4.7503 19.6619 5.63794C19.75 6.5043 19.75 7.5892 19.75 8.9584V15.0416C19.75 16.4108 19.75 17.4957 19.6619 18.3621C19.5716 19.2497 19.3826 19.9907 18.9495 20.639C18.6029 21.1576 18.1576 21.6029 17.639 21.9495C16.9907 22.3826 16.2497 22.5716 15.3621 22.6619C14.4957 22.75 13.4108 22.75 12.0416 22.75H11.9584C10.5892 22.75 9.50431 22.75 8.63794 22.6619C7.7503 22.5716 7.00926 22.3826 6.36104 21.9495C5.84239 21.6029 5.39707 21.1576 5.05052 20.639C4.61739 19.9907 4.42841 19.2497 4.33812 18.3621C4.24998 17.4957 4.24999 16.4108 4.25 15.0416V8.95841C4.24999 7.5892 4.24998 6.5043 4.33812 5.63794C4.42841 4.7503 4.61739 4.00926 5.05052 3.36104C5.39707 2.84239 5.84239 2.39707 6.36104 2.05052C7.00926 1.61739 7.7503 1.42841 8.63794 1.33812C9.5043 1.24998 10.5892 1.24999 11.9584 1.25ZM8.78975 2.83041C8.02071 2.90865 7.55507 3.05673 7.1944 3.29772C6.83953 3.53484 6.53484 3.83953 6.29772 4.1944C6.05673 4.55507 5.90865 5.02071 5.83041 5.78975C5.75091 6.57133 5.75 7.57993 5.75 9V15C5.75 16.4201 5.75091 17.4287 5.83041 18.2102C5.90865 18.9793 6.05673 19.4449 6.29772 19.8056C6.53484 20.1605 6.83953 20.4652 7.1944 20.7023C7.55507 20.9433 8.02071 21.0914 8.78975 21.1696C9.57133 21.2491 10.5799 21.25 12 21.25C13.4201 21.25 14.4287 21.2491 15.2102 21.1696C15.9793 21.0914 16.4449 20.9433 16.8056 20.7023C17.1605 20.4652 17.4652 20.1605 17.7023 19.8056C17.9433 19.4449 18.0914 18.9793 18.1696 18.2102C18.2491 17.4287 18.25 16.4201 18.25 15V9C18.25 7.57993 18.2491 6.57133 18.1696 5.78975C18.0914 5.02071 17.9433 4.55507 17.7023 4.1944C17.4652 3.83953 17.1605 3.53484 16.8056 3.29772C16.4449 3.05673 15.9793 2.90865 15.2102 2.83041C14.4287 2.75091 13.4201 2.75 12 2.75C10.5799 2.75 9.57133 2.75091 8.78975 2.83041ZM8.25 5.5C8.25 5.08579 8.58579 4.75 9 4.75H15C15.4142 4.75 15.75 5.08579 15.75 5.5C15.75 5.91421 15.4142 6.25 15 6.25H9C8.58579 6.25 8.25 5.91421 8.25 5.5ZM12 13.25C10.7574 13.25 9.75 14.2574 9.75 15.5C9.75 16.7426 10.7574 17.75 12 17.75C13.2426 17.75 14.25 16.7426 14.25 15.5C14.25 14.2574 13.2426 13.25 12 13.25ZM8.25 15.5C8.25 13.4289 9.92893 11.75 12 11.75C14.0711 11.75 15.75 13.4289 15.75 15.5C15.75 17.5711 14.0711 19.25 12 19.25C9.92893 19.25 8.25 17.5711 8.25 15.5Z"></path> <path d="M10 9C10 9.55229 9.55229 10 9 10C8.44772 10 8 9.55229 8 9C8 8.44772 8.44772 8 9 8C9.55229 8 10 8.44772 10 9Z"></path> <path d="M13 9C13 9.55229 12.5523 10 12 10C11.4477 10 11 9.55229 11 9C11 8.44772 11.4477 8 12 8C12.5523 8 13 8.44772 13 9Z"></path> <path d="M16 9C16 9.55229 15.5523 10 15 10C14.4477 10 14 9.55229 14 9C14 8.44772 14.4477 8 15 8C15.5523 8 16 8.44772 16 9Z"></path> </g></svg>
                </button>
                <div class="nav-header">
                    <h2>(<?= $channels ? count($channels) : 0 ?>) Channels</h2>
                    <button type="button" id="search-btn-toggle">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g> <path d="M11 6C13.7614 6 16 8.23858 16 11M16.6588 16.6549L21 21M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z" stroke="currentcolor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                    </button>
                </div>
                <input type="search" name="s" id="channel-search" placeholder="Search Channels ...">
                <?php if ($channels !== false): ?>
                <?php if (!empty($channels)): ?>
                <ol>
                <?php foreach ($channels as $i => $ch): ?>
                    <li>
                        <a href="#" data-source="<?= $ch['url'] ?>" data-name="<?= $ch['name'] ?>" class="channel-btn<?= $i == 0 ? ' selected' : '' ?>">
                            <span class="channel-info">
                                <span class="channel-num"><?= ($i+1) ?></span>
                                <span class="channel-name"><?= htmlentities($ch['name']) ?></span>
                            </span>
                            <?php if (!empty($ch['tvg-logo'])) : ?>
                            <img src="<?= $ch['tvg-logo'] ?>" 
                                alt="<?= htmlentities($ch['name']) ?>"
                                onerror="this.parentNode.removeChild(this)">
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ol>
                <div></div>
                <?php else: ?>
                <p>No channels found.</p>
                <?php endif; ?>
                <?php else: ?>
                <p>Loading playlist failed!</p>
                <?php endif; ?>
            </nav>
            <div class="video-container">
                <video 
                    id="my_video_1" 
                    class="video-js vjs-4-3 vjs-default-skin"
                    muted
                    autoplay
                    controls 
                    preload="auto" 
                    width="1280"
                    height="720"
                    data-setup="{}">
                    <source src="<?= !empty($channels) ? $channels[0]['url'] : '' ?>" type="application/x-mpegURL">
                </video>
            </div>
        </main>
        <footer>
            Playlist took <?= number_format($proctime, 4) ?> seconds to load. <br>
            Made by <a href="https://github.com/mkcdr" target="_blank" rel="noopener noreferrer">@mkcdr</a> with <span class="heart-icon"></span>
        </footer>
    </div>
    <script>
        document.querySelectorAll('.close').forEach(el => {
            el.addEventListener('click', (e) => {
                e.target.parentNode.remove();
            });
        });
    </script>
    <script>
        document.querySelector('.darkmode-switch').addEventListener('click', () => {
            document.body.classList.toggle('light');
            expires = new Date();
            expires.setDate(expires.getDate() + 365);
            document.cookie = 'darkmode=' + (document.body.classList.contains('light') ? 0 : 1) + '; expires=' + expires.toUTCString();
        });
    </script>
    <script>
        document.querySelector('.channels-list-btn-toggle').addEventListener('click', () => {
            document.getElementById('channels-list').classList.toggle('open');
        });
    </script>
    <script>
        const chBtns = document.querySelectorAll('.channel-btn');
        chBtns.forEach((el) => {
            el.addEventListener('click', (evt) => {
                evt.preventDefault();
                chBtns.forEach(t => t.classList.remove('selected'));
                el.classList.add('selected');
                let source = el.dataset.source;
                let player = videojs(document.querySelector('.video-js'));
                player.src({ src: source, type: 'application/x-mpegURL' });
                player.play();
            });
        });
    </script>
    <script>
        document.getElementById('search-btn-toggle').addEventListener('click', () => {
            document.getElementById('channels-list').classList.toggle('search-open');
        });

        document.getElementById('channel-search').addEventListener('keyup', (e) => {
            let q = e.target.value;
            let ql = q.length;
            chBtns.forEach((el) => {
                if (q.localeCompare(el.dataset.name.substr(0, ql), undefined, { sensitivity: 'base' }) === 0) {
                    el.style.display = '';
                }
                else {
                    el.style.display = 'none';
                }
            });
        });
    </script>
    <script>
        var chNum = '';
        var chCounter = 0;
        var chTimeout = null;
        const chSelector = document.getElementById('channel-selector');

        window.addEventListener('keypress', e => {
            if (e.key >= '0' && e.key <= '9') {
                
                chCounter = 2;
                chNum += e.key;
                chSelector.innerHTML = chNum;

                if (!chTimeout) {
                    chSelector.style.display = 'block';
                    chTimeout = setInterval(() => {
                        if (!--chCounter) {
                            let btn = chBtns[parseInt(chNum) - 1];
                            if (btn) {
                                btn.focus();
                                btn.click();
                            }
                            chNum = '';
                            chSelector.innerHTML = '';
                            chSelector.style.display = 'none';
                            clearInterval(chTimeout);
                            chTimeout = null;
                        }
                    }, 1000);
                }   
            }
        });
    </script>
</body>
</html>