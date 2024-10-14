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

// Fetch user roles from the session
$user_roles = $_SESSION['discord_user']['roles'];

// Fetch the permlevel for the user's roles
$permlevel = 0; // Default to 0 (no permissions)

foreach ($user_roles as $role_id) {
    $stmt = $pdo->prepare("SELECT permlevel FROM roles WHERE role_id = ?");
    $stmt->execute([$role_id]);
    $role_permlevel = $stmt->fetchColumn();
    
    // Update permlevel to the highest found
    if ($role_permlevel !== false && $role_permlevel > $permlevel) {
        $permlevel = $role_permlevel;
    }
}

// Check if the user has any of the allowed roles (if necessary)
$stmt = $pdo->prepare("SELECT role_id FROM roles");
$stmt->execute();
$allowed_roles_data = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch only role_id

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
    <title>Search Warrants</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Search for Warrants</h1>
        <form action="" method="GET" class="space-y-4">
            <input type="text" name="search" placeholder="Enter First or Last Name" required class="w-full px-4 py-2 bg-gray-800 text-white border border-gray-700 rounded">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">Search</button>
        </form>
        
        <?php
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            $stmt = $pdo->prepare("SELECT * FROM warrants WHERE first_name LIKE ? OR last_name LIKE ?");
            $stmt->execute(["%$search%", "%$search%"]);
            $results = $stmt->fetchAll();

            if ($results) {
                echo '<table class="min-w-full table-auto mt-6 text-gray-400">';
                echo '<thead><tr><th>Case Number</th><th>Name</th><th>Mugshot</th><th>Date of Birth</th><th>Crime</th><th>Issued Date</th><th>Status</th>';
                if ($permlevel >= 3) { // Check if permlevel is 3 or higher
                    echo '<th>Actions</th>'; // Only show Actions if permlevel >= 3
                }
                echo '</tr></thead>';
                echo '<tbody>';
                foreach ($results as $row) {
                    echo "<tr><td>{$row['case_number']}</td><td>{$row['first_name']} {$row['last_name']}</td>";
                    // Display the mugshot
                    echo '<td><img src="'.htmlspecialchars($row['mugshot']).'" alt="Mugshot of '.$row['first_name'].' '.$row['last_name'].'" class="w-16 h-16 rounded-full"></td>';
                    echo "<td>{$row['date_of_birth']}</td><td>{$row['crime_description']}</td><td>{$row['warrant_issued']}</td><td>{$row['status']}</td>";
                    
                    // Show edit and delete options if permlevel >= 3
                    if ($permlevel >= 3) {
                        echo '<td>';
                        // Edit Form
                        echo '<form action="" method="POST" class="inline-block">';
                        echo '<input type="hidden" name="id" value="'.$row['id'].'">';
                        echo '<input type="text" name="status" placeholder="New Status (active, served, expired)" class="bg-gray-800 text-white border border-gray-700 rounded px-2" required>';
                        echo '<button type="submit" name="edit" class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-2 rounded">Edit</button>';
                        echo '</form>';
                        
                        // Delete Form
                        echo '<form action="" method="POST" class="inline-block ml-2">';
                        echo '<input type="hidden" name="id" value="'.$row['id'].'">';
                        echo '<button type="submit" name="delete" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Delete</button>';
                        echo '</form>';
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-red-500 mt-4">No warrants found.</p>';
            }
        }

        // Handle edit and delete requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['edit'])) {
                $id = $_POST['id'];
                $new_status = $_POST['status'];

                // Validate the new status against allowed enum values
                $allowed_statuses = ['active', 'served', 'expired'];
                if (in_array($new_status, $allowed_statuses)) {
                    // Update the warrant's status
                    $update_stmt = $pdo->prepare("UPDATE warrants SET status = ? WHERE id = ?");
                    $update_stmt->execute([$new_status, $id]);

                    echo '<p class="text-green-500 mt-4">Warrant updated successfully.</p>';
                } else {
                    echo '<p class="text-red-500 mt-4">Invalid status value. Please use active, served, or expired.</p>';
                }
            }

            if (isset($_POST['delete'])) {
                $id = $_POST['id'];

                // Delete the warrant
                $delete_stmt = $pdo->prepare("DELETE FROM warrants WHERE id = ?");
                $delete_stmt->execute([$id]);

                echo '<p class="text-red-500 mt-4">Warrant deleted successfully.</p>';
            }
        }
        ?>
    </div>
</body>
</html>
