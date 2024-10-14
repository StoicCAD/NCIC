<?php
session_start();
// Database connection (make sure to include your database connection here)
include('database.php'); // Adjust the path to your database connection file
include('config.php'); // Include the config file

// Step 1: Redirect to Discord OAuth if we don't have a code
if (!isset($_GET['code'])) {
    $oauth_url = "https://discord.com/api/oauth2/authorize?client_id=" . DISCORD_CLIENT_ID . "&redirect_uri=" . urlencode(DISCORD_REDIRECT_URI) . 
        "&response_type=code&scope=" . DISCORD_SCOPES;
    header("Location: $oauth_url");
    exit;
}

// Step 2: Exchange the authorization code for an access token
$code = $_GET['code'];
$token_url = 'https://discord.com/api/oauth2/token';
$data = [
    'client_id' => DISCORD_CLIENT_ID,
    'client_secret' => DISCORD_CLIENT_SECRET,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => DISCORD_REDIRECT_URI,
];

$options = [
    'http' => [
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data),
    ],
];

$context = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);
$response_data = json_decode($response, true);

// Check if we got an access token
if (isset($response_data['access_token'])) {
    // Step 3: Use the access token to get user information
    $access_token = $response_data['access_token'];
    $user_url = 'https://discord.com/api/v10/users/@me';
    $user_options = [
        'http' => [
            'header' => "Authorization: Bearer {$access_token}\r\n",
            'method' => 'GET',
        ],
    ];
    $user_context = stream_context_create($user_options);
    $user_response = file_get_contents($user_url, false, $user_context);
    $user_data = json_decode($user_response, true);

    // Step 4: Get the user's guilds and check roles
    $guilds_url = 'https://discord.com/api/v10/users/@me/guilds';
    $guilds_options = [
        'http' => [
            'header' => "Authorization: Bearer {$access_token}\r\n",
            'method' => 'GET',
        ],
    ];
    $guilds_context = stream_context_create($guilds_options);
    $guilds_response = file_get_contents($guilds_url, false, $guilds_context);
    $guilds_data = json_decode($guilds_response, true);

    // Check if the user is in your required guild
    $user_in_guild = false;
    $user_roles = [];

    foreach ($guilds_data as $guild) {
        if ($guild['id'] === REQUIRED_GUILD_ID) {
            $user_in_guild = true;

            // Get roles from the guild
            $guild_roles_url = "https://discord.com/api/v10/guilds/" . REQUIRED_GUILD_ID . "/members/{$user_data['id']}";
            $guild_roles_options = [
                'http' => [
                    'header' => "Authorization: Bot " . BOT_TOKEN . "\r\n",
                    'method' => 'GET',
                ],
            ];
            $guild_roles_context = stream_context_create($guild_roles_options);
            $guild_roles_response = file_get_contents($guild_roles_url, false, $guild_roles_context);
            $guild_roles_data = json_decode($guild_roles_response, true);

            if (isset($guild_roles_data['roles'])) {
                $user_roles = $guild_roles_data['roles'];
            }
            break;
        }
    }

    // Check if the user has one of the required role IDs
    $has_required_role = !empty(array_intersect($user_roles, REQUIRED_ROLE_IDS));

    // Store user information in session
    $_SESSION['discord_user'] = [
        'id' => $user_data['id'],
        'username' => $user_data['username'],
        'guild_id' => REQUIRED_GUILD_ID,
        'roles' => $user_roles,
    ];

    // Prepare to insert user data into the users table
    $username = $user_data['username'];
    $discord_id = $user_data['id'];
    $profile_picture = $user_data['avatar'] ? "https://cdn.discordapp.com/avatars/$discord_id/{$user_data['avatar']}.png" : null;

    // Check if the user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);

    if ($stmt->rowCount() == 0) {
        // User does not exist, insert new user
        $insert_stmt = $pdo->prepare("INSERT INTO users (username, discord_id, profile_picture) VALUES (?, ?, ?)");
        $insert_stmt->execute([$username, $discord_id, $profile_picture]);
    }

    // Check if there are already roles in the roles table
    $role_count_stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $role_count = $role_count_stmt->fetchColumn();

    // Redirect to setup_roles.php only if there are no roles
    if ($role_count == 0) {
        // No roles found, prompt to set up the roles
        echo "<script>
                alert('You are the first user! Please set up the roles in the database.');
                window.location.href = 'setup_roles.php'; // Redirect to the setup roles page
              </script>";
        exit;
    }

    // Redirect to the home page if roles exist
    header('Location: dashboard.php');
    exit;
} else {
    echo "Error during the OAuth process.";
}
?>
