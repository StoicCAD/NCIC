<?php
session_start();
// Database connection (make sure to include your database connection here)
include('database.php'); // Adjust the path to your database connection file

// Check if the user is logged in
if (!isset($_SESSION['discord_user'])) {
    header('Location: index.php'); // Redirect to the login page if not logged in
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_id = trim($_POST['role_id']);
    $role_name = trim($_POST['role_name']);
    $permlevel = (int)$_POST['permlevel'];

    // Validate inputs
    if (!empty($role_id) && !empty($role_name) && ($permlevel >= 1 && $permlevel <= 3)) {
        // Insert role into the database
        $stmt = $pdo->prepare("INSERT INTO roles (role_id, role_name, permlevel) VALUES (?, ?, ?)");
        if ($stmt->execute([$role_id, $role_name, $permlevel])) {
            echo "<script>alert('Role created successfully!');</script>";
        } else {
            echo "<script>alert('Error creating role. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Please fill in all fields correctly.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Roles</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmNavigation(event) {
            event.preventDefault(); // Prevent the default action
            if (confirm("Once you leave this page, all content will be removed and you won't be able to return. Do you want to continue?")) {
                // Redirect to the remove content script
                window.location.href = 'remove_setup_roles_content.php';
            }
        }
    </script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto mt-10">
        <h1 class="text-3xl font-bold mb-6 text-center">Setup Roles</h1>
        <form method="POST" class="bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label for="role_id" class="block text-gray-300 text-sm font-bold mb-2">Role ID:</label>
                <input type="text" name="role_id" id="role_id" required class="shadow appearance-none border border-gray-700 rounded w-full py-2 px-3 text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-700">
            </div>
            <div class="mb-4">
                <label for="role_name" class="block text-gray-300 text-sm font-bold mb-2">Role Name:</label>
                <input type="text" name="role_name" id="role_name" required class="shadow appearance-none border border-gray-700 rounded w-full py-2 px-3 text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-700">
            </div>
            <div class="mb-4">
                <label for="permlevel" class="block text-gray-300 text-sm font-bold mb-2">Permission Level:</label>
                <select name="permlevel" id="permlevel" required class="shadow appearance-none border border-gray-700 rounded w-full py-2 px-3 text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-700">
                    <option value="">Select Permission Level</option>
                    <option value="1">1 - Low</option>
                    <option value="2">2 - Medium</option>
                    <option value="3">3 - High</option>
                </select>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Create Role
                </button>
            </div>
        </form>
        <a href="dashboard.php" onclick="confirmNavigation(event)" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
            Back to Dashboard
        </a>
    </div>
</body>
</html>
