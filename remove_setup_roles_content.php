<?php
// Path to the setup_roles.php file
$file_path = 'setup_roles.php'; // Adjust the path if needed

// Clear the content of the setup_roles.php file
file_put_contents($file_path, '<?php' . PHP_EOL . 'exit;' . PHP_EOL);

// Redirect back to the dashboard
header('Location: dashboard.php');
exit;
