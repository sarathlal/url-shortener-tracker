<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://tinylab.dev
 * @since      1.0.0
 *
 * @package    URL_Shortener_Tracker
 * @subpackage URL_Shortener_Tracker/admin/partials
 */



global $wpdb;
$table_name = $wpdb->prefix . 'tl_urls';

// Pagination and Sorting parameters
$limit = isset($_POST['urls_per_page']) ? intval($_POST['urls_per_page']) : (isset($_GET['urls_per_page']) ? intval($_GET['urls_per_page']) : 20);
if ($limit <= 0) $limit = 20; // Ensure the limit is a positive number
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $limit;

$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'id';
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
$order = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';

// Fetch URLs with pagination and sorting
$cache_key = "urls_{$sort_by}_{$order}_{$limit}_{$offset}";
$urls = wp_cache_get($cache_key, 'url_shortener_tracker');

if ($urls === false) {
    $urls = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $sort_by $order LIMIT %d OFFSET %d", $limit, $offset));
    wp_cache_set($cache_key, $urls, 'url_shortener_tracker', 3600);
}

$total_urls = wp_cache_get('total_urls', 'url_shortener_tracker');
if ($total_urls === false) {
    $total_urls = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    wp_cache_set('total_urls', $total_urls, 'url_shortener_tracker', 3600);
}

$total_pages = ceil($total_urls / $limit);

// Fetch URL for editing if edit action is triggered and nonce is verified
$edit_url = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
    if (!wp_verify_nonce($_GET['_wpnonce'], 'edit_url_' . intval($_GET['id']))) {
        wp_die('Nonce verification failed');
    }
    $edit_url_cache_key = "edit_url_" . intval($_GET['id']);
    $edit_url = wp_cache_get($edit_url_cache_key, 'url_shortener_tracker');
    if ($edit_url === false) {
        $edit_url = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));
        wp_cache_set($edit_url_cache_key, $edit_url, 'url_shortener_tracker', 3600);
    }
}
?>

<div class="wrap">
    <h1>URLs</h1>

    <?php if (isset($_GET['notice'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo wp_kses_post(urldecode($_GET['notice'])); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo wp_kses_post(urldecode($_GET['error'])); ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="add_url">
        <input type="hidden" name="id" value="<?php echo $edit_url ? esc_attr($edit_url->id) : ''; ?>">
        <?php wp_nonce_field('add_edit_url', 'add_edit_url_nonce'); ?>
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
        <?php wp_nonce_field('bulk_action', 'bulk_action_nonce'); ?>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value="-1">Bulk actions</option>
                    <option value="delete">Delete</option>
                    <option value="export">Export to CSV</option>
                </select>
                <input type="submit" id="doaction" class="button action" value="Apply">
            </div>
            <div class="alignleft actions">
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
                    <th><a href="<?php echo esc_url(add_query_arg(array('sort_by' => 'id', 'order' => ($sort_by == 'id' && $order == 'ASC') ? 'DESC' : 'ASC'))); ?>">ID</a></th>
                    <th>URL</th>
                    <th>Redirect</th>
                    <th><a href="<?php echo esc_url(add_query_arg(array('sort_by' => 'clicks', 'order' => ($sort_by == 'clicks' && $order == 'ASC') ? 'DESC' : 'ASC'))); ?>">Clicks</a></th>
                    <th><a href="<?php echo esc_url(add_query_arg(array('sort_by' => 'created_at', 'order' => ($sort_by == 'created_at' && $order == 'ASC') ? 'DESC' : 'ASC'))); ?>">Created At</a></th>
                    <th><a href="<?php echo esc_url(add_query_arg(array('sort_by' => 'updated_at', 'order' => ($sort_by == 'updated_at' && $order == 'ASC') ? 'DESC' : 'ASC'))); ?>">Updated At</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($urls)) : ?>
                    <?php foreach ($urls as $url) : ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="url_ids[]" value="<?php echo esc_attr($url->id); ?>">
                            </th>
                            <td><?php echo esc_html($url->id); ?></td>
                            <td><?php echo esc_html($url->url); ?></td>
                            <td><?php echo esc_html($url->redirect); ?></td>
                            <td><?php echo esc_html($url->clicks); ?></td>
                            <td><?php echo esc_html($url->created_at); ?></td>
                            <td><?php echo esc_html($url->updated_at); ?></td>
                            <td>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'edit', 'id' => $url->id)), 'edit_url_' . $url->id)); ?>">Edit</a> |
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $url->id)), 'delete_url_' . $url->id)); ?>" onclick="return confirm('Are you sure you want to delete this URL?');">Delete</a>
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

        <div class="tablenav bottom">
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
                if(1 < $total_pages){
                    echo wp_kses_post(paginate_links($pagination_args));
                }
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

window.addEventListener('load', function() {
    const url = new URL(window.location);
    if (url.searchParams.has('notice') || url.searchParams.has('error')) {
        url.searchParams.delete('notice');
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url.toString());
    }
});
</script>
