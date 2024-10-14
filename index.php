<?php
session_start();

// Database connection (make sure to include your database connection here)
include('database.php'); // Adjust the path to your database connection file

// Fetch all active warrants
$stmt = $pdo->prepare("SELECT * FROM warrants WHERE status = 'active'");
$stmt->execute();
$active_warrants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCIC - Most Wanted</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'Price Down';
            src: url('fonts/pricedown bl.otf') format('opentype'); /* Adjust the path if necessary */
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg); /* Center and rotate */
            font-family: 'Price Down'; /* Use the custom font */
            font-size: 3rem; /* Adjust font size as needed */
            color: rgba(255, 0, 0, 0.3); /* Red color with transparency */
            pointer-events: none; /* Allow clicks to go through the watermark */
            white-space: nowrap; /* Prevents wrapping */
            overflow: hidden; /* Hides overflow */
            text-align: center; /* Centers the text */
        }
        .relative-container {
            position: relative; /* Needed to position the watermark correctly */
            overflow: hidden; /* Ensures the watermark stays within the card */
        }
        .image-wrapper {
            position: relative;
            height: 250px; /* Fixed height for the image wrapper */
        }
        img {
            width: 100%; /* Full width of the container */
            height: 100%; /* Full height of the container */
            object-fit: cover; /* Maintain aspect ratio and fill the container */
            border-radius: 0.5rem; /* Rounded corners */
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <!-- Navbar -->
    <nav class="bg-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-lg font-semibold">NCIC System</div>
            <div class="space-x-4">
                <a href="index.php" class="hover:text-blue-400">Home</a>
                <a href="dashboard.php" class="hover:text-blue-400">Login</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold mb-6 text-center">Most Wanted</h1>
        <p class="text-center mb-4">Here are the active warrants.</p>

        <?php if (!empty($active_warrants)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($active_warrants as $warrant): ?>
                    <div class="bg-gray-800 p-4 rounded-lg shadow-md">
                        <div class="image-wrapper relative-container">
                            <img src="<?php echo htmlspecialchars($warrant['mugshot']); ?>" alt="Mugshot of <?php echo htmlspecialchars($warrant['first_name'] . ' ' . $warrant['last_name']); ?>">
                            <div class="watermark">WANTED</div> <!-- Watermark text -->
                        </div>
                        <h2 class="text-xl font-bold mt-2"><?php echo htmlspecialchars($warrant['first_name'] . ' ' . $warrant['last_name']); ?></h2>
                        <p><strong>Case Number:</strong> <?php echo htmlspecialchars($warrant['case_number']); ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($warrant['date_of_birth']); ?></p>
                        <p><strong>Crime:</strong> <?php echo htmlspecialchars($warrant['crime_description']); ?></p>
                        <p><strong>Issued Date:</strong> <?php echo htmlspecialchars($warrant['warrant_issued']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($warrant['status']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-red-500 mt-4 text-center">No active warrants found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
