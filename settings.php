<?php
session_start();

// Database connection (make sure to include your database connection here)
include('database.php'); // Adjust the path to your database connection file
include('config.php'); // Include the config file

// Replace with your actual guild ID
$requiredGuildId = '1016505005903204434';

// Check if the user is logged in and their Discord information is set in the session
if (!isset($_SESSION['discord_user']) || 
    $_SESSION['discord_user']['guild_id'] !== $requiredGuildId) {
    
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Fetch allowed roles from the database
$stmt = $pdo->prepare("SELECT role_id FROM roles");
$stmt->execute();
$allowed_roles_data = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch only role_id

// Check if the user has any of the allowed roles
$user_roles = $_SESSION['discord_user']['roles'];
$has_allowed_role = !empty(array_intersect($user_roles, $allowed_roles_data));

if (!$has_allowed_role) {
    echo "You do not have permission to access this application.";
    exit;
}

// Get the user's Discord ID from the session
$discord_id = $_SESSION['discord_user']['id'];

// Handle form submission for updating profile picture
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $profile_picture = $_POST['profile_picture'] ?? null;

        // Update the user's profile picture in the database
        if ($profile_picture) {
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE discord_id = ?");
            $stmt->execute([$profile_picture, $discord_id]);
            $message = "Profile picture updated successfully!";
        }
    }

    // Handle account deletion
    if (isset($_POST['delete'])) {
        // Delete the user from the database
        $stmt = $pdo->prepare("DELETE FROM users WHERE discord_id = ?");
        $stmt->execute([$discord_id]);
        
        // Clear the session and redirect to login
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Fetch current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE discord_id = ?");
$stmt->execute([$discord_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <?php include('navbar.php'); ?> <!-- Include the navbar -->
    
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold mb-6">Settings</h1>

        <?php if (isset($message)): ?>
            <div class="bg-green-500 text-white p-2 rounded mb-4"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-4">
            <div class="mb-4">
                <label for="profile_picture" class="block mb-2">Profile Picture URL</label>
                <input type="text" id="profile_picture" name="profile_picture" 
                       value="<?= htmlspecialchars($user['profile_picture']) ?>" 
                       class="border border-gray-600 bg-gray-800 p-2 w-full">
            </div>
            <button type="submit" name="update" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                Update Profile Picture
            </button>
        </form>

        <div class="bg-red-500 text-white p-4 rounded mb-4">
            <p>Warning: Deleting your account is permanent and cannot be undone.</p>
            <form method="POST">
                <button type="submit" name="delete" class="bg-red-700 hover:bg-red-800 text-white py-2 px-4 rounded">
                    Delete My Account
                </button>
            </form>
        </div>
    </div>
</body>
</html>
