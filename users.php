<?php
session_start();

// Database connection (make sure to include your database connection here)
include('database.php'); // Adjust the path to your database connection file
include('config.php'); // Include the config file

// Required guild ID
$requiredGuildId = '1016505005903204434';

// Check if the user is logged in and their Discord information is set in the session
if (!isset($_SESSION['discord_user']) || 
    $_SESSION['discord_user']['guild_id'] !== $requiredGuildId) {
    
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Get user roles from the session
$user_roles = $_SESSION['discord_user']['roles'];

// Prepare an SQL query to check if any of the user's roles have a permlevel of 3 or higher
$placeholders = implode(',', array_fill(0, count($user_roles), '?')); // Create placeholders for the IN clause
$sql = "SELECT role_id, permlevel FROM roles WHERE role_id IN ($placeholders)";

$stmt = $pdo->prepare($sql);
$stmt->execute($user_roles);
$roles_with_perm = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch both role_id and permlevel

// Check if the user has a role with permlevel 3 or higher
$allowed_roles = array_filter($roles_with_perm, function($role) {
    return $role['permlevel'] >= 3;
});

// Print roles and their permlevels in the console
echo '<script>';
echo 'console.log("User Roles and Permission Levels:");';
foreach ($roles_with_perm as $role) {
    echo 'console.log("Role ID: ' . $role['role_id'] . ', Permission Level: ' . $role['permlevel'] . '");';
}
echo '</script>';

// Conditional message display
if (empty($allowed_roles)) {
    echo "You do not have permission to access this application.";
    exit;
}

// Handle user edits and actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $profile_picture = $_POST['profile_picture'];

        // Update user in the database
        $update_stmt = $pdo->prepare("UPDATE users SET username = ?, profile_picture = ? WHERE id = ?");
        $update_stmt->execute([$username, $profile_picture, $user_id]);

        // Redirect to prevent form resubmission
        header('Location: users.php');
        exit;
    }

    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        // Delete user from the database
        $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->execute([$user_id]);

        // Redirect to prevent form resubmission
        header('Location: users.php');
        exit;
    }

    if (isset($_POST['add_user'])) {
        $username = $_POST['new_username'];
        $discord_id = $_POST['new_discord_id'];
        $profile_picture = $_POST['new_profile_picture'];

        // Insert new user into the database
        $insert_stmt = $pdo->prepare("INSERT INTO users (username, discord_id, profile_picture) VALUES (?, ?, ?)");
        $insert_stmt->execute([$username, $discord_id, $profile_picture]);

        // Redirect to prevent form resubmission
        header('Location: users.php');
        exit;
    }
}

// Fetch all users for display
$all_users_stmt = $pdo->query("SELECT * FROM users");
$all_users = $all_users_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCIC - Users Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold mb-6 text-center">Users Management</h1>
        <?php if (!empty($allowed_roles)): ?>
            <table class="min-w-full bg-gray-800 mb-6">
                <thead>
                    <tr>
                        <th class="py-2">User ID</th>
                        <th class="py-2">Username</th>
                        <th class="py-2">Discord ID</th>
                        <th class="py-2">Profile Picture</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td class="py-2"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($user['discord_id']); ?></td>
                            <td class="py-2"><img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="w-10 h-10 rounded-full"></td>
                            <td class="py-2">
                                <form method="POST" action="users.php" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="bg-gray-700 text-white border border-gray-600 rounded px-2">
                                    <input type="text" name="profile_picture" value="<?php echo htmlspecialchars($user['profile_picture']); ?>" class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-1/3">
                                    <button type="submit" name="edit_user" class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-2 rounded">Save</button>
                                    <button type="submit" name="delete_user" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Form to Add New User -->
            <h2 class="text-2xl mb-4">Add New User</h2>
            <form method="POST" action="users.php" class="flex space-x-4 mb-6">
                <input type="text" name="new_username" placeholder="Username" required class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-1/4">
                <input type="text" name="new_discord_id" placeholder="Discord ID" required class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-1/4">
                <input type="text" name="new_profile_picture" placeholder="Profile Picture URL" class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-1/4">
                <button type="submit" name="add_user" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">Add User</button>
            </form>
        <?php else: ?>
            <p class="text-center text-red-500">You do not have permission to access this application.</p>
        <?php endif; ?>
    </div>
</body>
</html>
