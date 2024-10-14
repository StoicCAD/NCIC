<?php
session_start();

// Database connection (make sure to include your database connection here)
include('database.php'); // Adjust the path to your database connection file
include('config.php'); // Include the config file

// Access the required guild ID
$requiredGuildId = REQUIRED_GUILD_ID;

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
?>

<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCIC - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold mb-6 text-center">NCIC Arrest Warrant Dashboard</h1>
        <div class="flex justify-center space-x-4">
            <a href="submit_warrant.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                Submit Arrest Warrant
            </a>
            <a href="search_warrant.php" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">
                Search Warrant Database
            </a>
        </div>
    </div>
</body>
</html>
