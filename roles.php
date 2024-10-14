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

// Handle role edits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_role'])) {
        $role_id = $_POST['role_id'];
        $role_name = $_POST['role_name'];
        $permlevel = $_POST['permlevel'];

        // Update role in the database
        $update_stmt = $pdo->prepare("UPDATE roles SET role_name = ?, permlevel = ? WHERE role_id = ?");
        $update_stmt->execute([$role_name, $permlevel, $role_id]);

        // Redirect to prevent form resubmission
        header('Location: roles.php');
        exit;
    }

    if (isset($_POST['delete_role'])) {
        $role_id = $_POST['role_id'];

        // Delete role from the database
        $delete_stmt = $pdo->prepare("DELETE FROM roles WHERE role_id = ?");
        $delete_stmt->execute([$role_id]);

        // Redirect to prevent form resubmission
        header('Location: roles.php');
        exit;
    }

    if (isset($_POST['add_role'])) {
        $role_id = $_POST['new_role_id'];
        $role_name = $_POST['new_role_name'];
        $permlevel = $_POST['new_permlevel'];

        // Insert new role into the database
        $insert_stmt = $pdo->prepare("INSERT INTO roles (role_id, role_name, permlevel) VALUES (?, ?, ?)");
        $insert_stmt->execute([$role_id, $role_name, $permlevel]);

        // Redirect to prevent form resubmission
        header('Location: roles.php');
        exit;
    }
}

// Fetch all roles for display
$all_roles_stmt = $pdo->query("SELECT * FROM roles");
$all_roles = $all_roles_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCIC - Roles Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold mb-6 text-center">Roles Management</h1>
        <?php if (!empty($allowed_roles)): ?>
            <table class="min-w-full bg-gray-800 mb-6">
                <thead>
                    <tr>
                        <th class="py-2">Role ID</th>
                        <th class="py-2">Role Name</th>
                        <th class="py-2">Permission Level</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_roles as $role): ?>
                        <tr>
                            <td class="py-2"><?php echo htmlspecialchars($role['role_id']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($role['role_name']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($role['permlevel']); ?></td>
                            <td class="py-2">
                                <form method="POST" action="roles.php" class="inline">
                                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role['role_id']); ?>">
                                    <input type="text" name="role_name" value="<?php echo htmlspecialchars($role['role_name']); ?>" class="bg-gray-700 text-white border border-gray-600 rounded px-2">
                                    <input type="number" name="permlevel" value="<?php echo htmlspecialchars($role['permlevel']); ?>" min="1" max="3" class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-16">
                                    <button type="submit" name="edit_role" class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-2 rounded">Save</button>
                                    <button type="submit" name="delete_role" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Form to Add New Role -->
            <h2 class="text-2xl mb-4">Add New Role</h2>
            <form method="POST" action="roles.php" class="flex space-x-4 mb-6">
                <input type="text" name="new_role_id" placeholder="Role ID" required class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-1/4">
                <input type="text" name="new_role_name" placeholder="Role Name" required class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-1/4">
                <input type="number" name="new_permlevel" placeholder="Permission Level" min="1" max="3" required class="bg-gray-700 text-white border border-gray-600 rounded px-2 w-16">
                <button type="submit" name="add_role" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">Add Role</button>
            </form>
        <?php else: ?>
            <p class="text-center text-red-500">You do not have permission to access this application.</p>
        <?php endif; ?>
    </div>
</body>
</html>
