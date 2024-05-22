<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    Url_Redirect_Tracking
 * @subpackage Url_Redirect_Tracking/admin/partials
 */

global $wpdb;
$table_name = $wpdb->prefix . 'tl_urls';

// Handle form submission for adding or editing URL
if ($_POST['action'] == 'add_url') {
    $url = sanitize_text_field($_POST['url']);
    $redirect = sanitize_text_field($_POST['redirect']);
    if ($_POST['id']) {
        // Edit existing URL
        $wpdb->update($table_name, array(
            'url' => $url,
            'redirect' => $redirect,
            'updated_at' => current_time('mysql')
        ), array('id' => intval($_POST['id'])));
    } else {
        // Add new URL
        $wpdb->insert($table_name, array(
            'url' => $url,
            'redirect' => $redirect,
            'clicks' => 0,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ));
    }
}

// Handle URL deletion
if ($_GET['action'] == 'delete' && $_GET['id']) {
    $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
    // Redirect to remove query params
    wp_redirect(remove_query_arg(array('action', 'id')));
    exit();
}

// Handle bulk delete action
Url_Redirect_Tracking_Utils::write_log($_REQUEST);
if ($_POST['bulk_action'] == 'delete' && !empty($_POST['url_ids'])) {

    Url_Redirect_Tracking_Utils::write_log('inside bulk_delete');
    foreach ($_POST['url_ids'] as $url_id) {
        $wpdb->delete($table_name, array('id' => intval($url_id)));
    }
    // Redirect to remove query params
    wp_redirect(remove_query_arg(array('bulk_action')));
    exit();
}

// Handle number of URLs per page
$limit = isset($_POST['urls_per_page']) ? intval($_POST['urls_per_page']) : (isset($_GET['urls_per_page']) ? intval($_GET['urls_per_page']) : 20);
if ($limit <= 0) $limit = 20; // Ensure the limit is a positive number
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $limit;

// Sorting parameters
$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'id';
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
$order = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';

// Fetch URLs with pagination and sorting
$urls = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $sort_by $order LIMIT %d OFFSET %d", $limit, $offset));
$total_urls = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$total_pages = ceil($total_urls / $limit);

// Fetch URL for editing if edit action is triggered
$edit_url = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['id']) {
    $edit_url = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));
}
?>

<div class="wrap">
    <h1>URLs</h1>
    <form method="post">
        <input type="hidden" name="action" value="add_url">
        <input type="hidden" name="id" value="<?php echo $edit_url ? esc_attr($edit_url->id) : ''; ?>">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="url">URL</label></th>
                <td><input name="url" type="text" id="url" value="<?php echo $edit_url ? esc_attr($edit_url->url) : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="redirect">Redirect URL</label></th>
                <td><input name="redirect" type="text" id="redirect" value="<?php echo $edit_url ? esc_attr($edit_url->redirect) : ''; ?>" class="regular-text"></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" class="button-primary" value="<?php echo $edit_url ? 'Update URL' : 'Add URL'; ?>"></p>
    </form>

    <form method="post">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value="-1">Bulk actions</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" id="doaction" class="button action" value="Apply">
            </div>
            <div class="alignright actions">
                <label for="urls_per_page">URLs per page:</label>
                <input type="number" name="urls_per_page" id="urls_per_page" value="<?php echo esc_attr($limit); ?>" min="1" max="100">
                <input type="submit" class="button" value="Apply">
            </div>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all">
                    </td>
                    <th>URL</th>
                    <th>Redirect</th>
                    <th><a href="<?php echo add_query_arg(array('sort_by' => 'clicks', 'order' => ($sort_by == 'clicks' && $order == 'ASC') ? 'DESC' : 'ASC')); ?>">Clicks</a></th>
                    <th><a href="<?php echo add_query_arg(array('sort_by' => 'created_at', 'order' => ($sort_by == 'created_at' && $order == 'ASC') ? 'DESC' : 'ASC')); ?>">Created At</a></th>
                    <th><a href="<?php echo add_query_arg(array('sort_by' => 'updated_at', 'order' => ($sort_by == 'updated_at' && $order == 'ASC') ? 'DESC' : 'ASC')); ?>">Updated At</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($urls)) : ?>
                    <?php foreach ($urls as $url) : ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="url_ids[]" value="<?php echo $url->id; ?>">
                            </th>
                            <td><?php echo esc_html($url->url); ?></td>
                            <td><?php echo esc_html($url->redirect); ?></td>
                            <td><?php echo $url->clicks; ?></td>
                            <td><?php echo $url->created_at; ?></td>
                            <td><?php echo $url->updated_at; ?></td>
                            <td>
                                <a href="<?php echo add_query_arg(array('action' => 'edit', 'id' => $url->id)); ?>">Edit</a> |
                                <a href="<?php echo add_query_arg(array('action' => 'delete', 'id' => $url->id)); ?>" onclick="return confirm('Are you sure you want to delete this URL?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8">No URLs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="tablenav bottom alignright">
            <div class="tablenav-pages">
                <?php
                $pagination_args = array(
                    'base' => add_query_arg(array('paged' => '%#%', 'sort_by' => $sort_by, 'order' => $order, 'urls_per_page' => $limit)),
                    'format' => '',
                    'total' => $total_pages,
                    'current' => $page,
                    'show_all' => false,
                    'end_size' => 1,
                    'mid_size' => 2,
                    'prev_next' => true,
                    'prev_text' => __('&laquo; Previous'),
                    'next_text' => __('Next &raquo;'),
                    'type' => 'plain',
                );
                echo paginate_links($pagination_args);
                ?>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('cb-select-all').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('input[name="url_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});
</script>

