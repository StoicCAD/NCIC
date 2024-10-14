<?php
// Discord OAuth credentials
define('DISCORD_CLIENT_ID', ''); // Replace with your actual client ID
define('DISCORD_CLIENT_SECRET', '-'); // Replace with your actual client secret
define('DISCORD_REDIRECT_URI', 'https://yourdomain.com/login.php');
define('DISCORD_SCOPES', 'identify guilds');

// Required guild and role IDs
define('REQUIRED_GUILD_ID', ''); // Replace with your actual guild ID
define('REQUIRED_ROLE_IDS', [
    'ROLE_ID_1',
    'ROLE_ID_2',
    'ROLE_ID_3' // Replace with actual role IDs This will be for LOGIN.PHP
]);

// Your bot token (keep this secure)
define('BOT_TOKEN', ''); // Use your bot token
?>
