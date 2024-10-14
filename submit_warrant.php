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
?>

<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Arrest Warrant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-center">Submit a New Arrest Warrant</h1>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $stmt = $pdo->prepare("INSERT INTO warrants (first_name, last_name, date_of_birth, crime_description, warrant_issued, officer_name, case_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['date_of_birth'], $_POST['crime_description'], $_POST['warrant_issued'], $_POST['officer_name'], $_POST['case_number']]);

            echo '<p class="text-green-500 text-center mb-4">Warrant successfully submitted!</p>';
        }
        ?>
        <form action="" method="POST" class="space-y-6">
            <div>
                <label for="first_name" class="block mb-1">First Name</label>
                <input type="text" name="first_name" id="first_name" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded" placeholder="Enter First Name">
            </div>
            <div>
                <label for="last_name" class="block mb-1">Last Name</label>
                <input type="text" name="last_name" id="last_name" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded" placeholder="Enter Last Name">
            </div>
            <div>
                <label for="date_of_birth" class="block mb-1">Date of Birth</label>
                <input type="date" name="date_of_birth" id="date_of_birth" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded">
            </div>
            <div>
                <label for="crime_description" class="block mb-1">Crime Description</label>
                <textarea name="crime_description" id="crime_description" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded" placeholder="Enter Crime Description"></textarea>
            </div>
            <div>
                <label for="warrant_issued" class="block mb-1">Warrant Issued Date</label>
                <input type="date" name="warrant_issued" id="warrant_issued" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded">
            </div>
            <div>
                <label for="officer_name" class="block mb-1">Officer Name</label>
                <input type="text" name="officer_name" id="officer_name" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded" placeholder="Enter Officer Name">
            </div>
            <div>
                <label for="case_number" class="block mb-1">Case Number</label>
                <input type="text" name="case_number" id="case_number" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded" placeholder="Enter Case Number">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Submit Warrant</button>
        </form>
    </div>
</body>
</html>
