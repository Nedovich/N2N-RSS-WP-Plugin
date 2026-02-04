<?php
/**
 * Debug Script to Check User Capabilities
 * Upload this to your root directory and visit it in browser.
 */

require_once 'wp-load.php';

// Simulate the user login (REPLACE 'nedimesken' WITH YOUR ACTUAL USERNAME)
$user_login = 'nedimesken'; 
$user = get_user_by( 'login', $user_login );

if ( ! $user ) {
    die( "User $user_login not found." );
}

echo "<h1>Debug Capabilities for User: " . $user->user_login . " (ID: " . $user->ID . ")</h1>";

// 1. Check Role
echo "<h3>Roles:</h3><pre>";
print_r( $user->roles );
echo "</pre>";

// 2. Check General Caps
$caps_to_check = [
    'edit_posts',
    'publish_posts',
    'edit_others_posts',
    'create_posts', // Custom cap?
];

echo "<h3>General Capabilities:</h3><ul>";
foreach ( $caps_to_check as $cap ) {
    echo "<li>$cap: " . ( $user->has_cap( $cap ) ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>' ) . "</li>";
}
echo "</ul>";

// 3. Check CPT Specific Caps
// Since we set 'capability_type' => 'post' and 'map_meta_cap' => true, 
// strictly speaking it should map to standard post caps. But let's verify.

echo "<h3>CPT 'aggregated_news' Capabilities:</h3>";
$post_type_obj = get_post_type_object( 'aggregated_news' );

if ( ! $post_type_obj ) {
    echo "<p style='color:red'>CPT 'aggregated_news' NOT FOUND/REGISTERED!</p>";
} else {
    echo "<pre>";
    print_r( $post_type_obj->cap );
    echo "</pre>";

    echo "<h4>Can User Create 'aggregated_news'?</h4>";
    // map_meta_cap logic test
    $create_cap = $post_type_obj->cap->create_posts;
    echo "<li>Checking cap '$create_cap': " . ( $user->has_cap( $create_cap ) ? '<span style="color:green">YES - ALLOWED</span>' : '<span style="color:red">NO - DENIED</span>' ) . "</li>";
}
