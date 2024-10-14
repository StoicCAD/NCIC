<?php

include('database.php'); // Adjust the path to your database connection file

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

// Get the user's Discord ID and profile picture from the session
$discord_id = $_SESSION['discord_user']['id'] ?? null;
$profile_picture = null;

// Check if the user is logged in and retrieve their profile picture from the database
if ($discord_id) {
    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $profile_picture = $user['profile_picture'] ?? null;
}
?>

<!-- Navbar -->
<nav class="bg-gray-800 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex-grow text-center">
            <div class="text-lg font-semibold text-white">NCIC System</div>
        </div>
        <div class="relative">
            <button id="profile-dropdown" class="flex items-center focus:outline-none">
                <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile" class="w-8 h-8 rounded-full border-2 border-gray-600">
            </button>
            <div id="dropdown-menu" class="hidden absolute right-0 bg-gray-900 text-white shadow-lg rounded mt-2 w-48 z-10">
                <div class="py-2">
                    <a href="settings.php" class="block px-4 py-2 text-left hover:bg-gray-700 transition">Settings</a>
                    <a href="logout.php" class="block px-4 py-2 text-left hover:bg-gray-700 transition">Logout</a>
                    <div class="border-t border-gray-600 my-1"></div>
                    <div class="flex flex-col">
                        <a href="dashboard.php" class="block px-4 py-2 text-left hover:bg-gray-700 transition">Home</a>
                        <a href="submit_warrant.php" class="block px-4 py-2 text-left hover:bg-gray-700 transition">Submit Warrant</a>
                        <a href="search_warrant.php" class="block px-4 py-2 text-left hover:bg-gray-700 transition">Search Warrant Database</a>

                        <?php if (!empty($allowed_roles)): // Check if the user has allowed roles ?>
                            <div class="border-t border-gray-600 my-1"></div>
                            <a href="roles.php" class="block px-4 py-2 text-left hover:bg-gray-700 transition">Manage Roles</a>
                            <a href="users.php" class="block px-4 py-2 text-left hover:bg-gray-700 transition">Manage Users</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Dropdown functionality
    const profileDropdown = document.getElementById('profile-dropdown');
    const dropdownMenu = document.getElementById('dropdown-menu');

    profileDropdown.addEventListener('click', () => {
        dropdownMenu.classList.toggle('hidden');
    });

    // Close the dropdown if clicked outside
    window.addEventListener('click', (event) => {
        if (!profileDropdown.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
</script>
