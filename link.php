<?php

// Check if the 'k' parameter is set and not empty.
if (isset($_GET['k']) && !empty($_GET['k'])) {
    
    // Sanitize the input to prevent potential security issues.
    // Use filter_input for a more secure and robust way to get and sanitize input.
    $k_value = filter_input(INPUT_GET, 'k', FILTER_SANITIZE_URL);

    // Build the new URL for redirection.
    // Ensure you use a valid, sanitized URL string.
    $redirect_url = "index.php?k=" . urlencode($k_value);

    // Perform a server-side redirect using the Location header.
    // Use a 301 (Moved Permanently) or 302 (Found) status code depending on your needs.
    // A 302 is typically used for temporary redirects.
    header("Location: " . $redirect_url, true, 302);
    
    // It's crucial to call exit() or die() after a header redirect
    // to stop the script from executing further and prevent
    // any content from being sent to the browser.
    exit();

} else {
    
    /* 
        If 'k' is not set or is empty, handle the error gracefully.
        Redirect to a default page or show a friendly error message.
    */
    // header("Location: error.php?message=missing_parameter", true, 400); // Bad Request
    // exit();

    // Or, for a simple error message:
    die("Error: Key is missing. Invalid Request.");
}

?>