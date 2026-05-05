<?php
/**
 * Plugin Name: AI CRM System
 * Description: Custom CRM system inside WordPress
 * Version: 1.0
 * Author: Arnaut
 */

if (!defined('ABSPATH')) exit;

// Add menu
add_action('admin_menu', function () {
    add_menu_page(
        'AI CRM',
        'AI CRM',
        'manage_options',
        'ai-crm',
        'ai_crm_dashboard',
        'dashicons-groups',
        6
    );
});

// Dashboard UI
function ai_crm_dashboard() {
    ?>
    <div class="wrap">
        <h1>AI CRM Dashboard</h1>

        <h2>Add New Lead</h2>
        <form method="post">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <select name="status">
                <option>New</option>
                <option>Contacted</option>
                <option>Closed</option>
            </select>
            <button type="submit" name="save_lead">Add Lead</button>
        </form>

        <hr>

        <h2>Leads List</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sample Client</td>
                    <td>client@email.com</td>
                    <td>New</td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}