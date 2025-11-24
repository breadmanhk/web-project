<?php
/**
 * Sign out script
 * Destroys the user's session, clears the session cookie, and redirects
 * back to the site home page.
 */

// Start the session (required to access session data)
session_start();

// Unset all of the session variables.
$_SESSION = [];

// Finally, destroy the session.
session_destroy();

// Redirect user back to the homepage (adjust path as needed)
header('Location: ../../index.php');
exit();
?>