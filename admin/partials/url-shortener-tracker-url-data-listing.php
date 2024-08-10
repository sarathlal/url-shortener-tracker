<?php

/**
 * Provide a admin area view for the URL data listing
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

$url_id = isset($_GET['url_id']) ? intval($_GET['url_id']) : 0;
if (!$url_id) {
    echo '<div class="notice notice-error"><p>Invalid URL ID.</p></div>';
    return;
}

$table_name = $wpdb->prefix . 'tl_url_data';

// Pagination and Sorting parameters
$limit = isset($_POST['data_per_page']) ? intval($_POST['data_per_page']) : (isset($_GET['data_per_page']) ? intval($_GET['data_per_page']) : 20);
if ($limit <= 0) $limit = 20; // Ensure the limit is a positive number
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $limit;

$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'id';
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
$order = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';

// Fetch URL data with pagination and sorting
$cache_key = "url_data_{$url_id}_{$sort_by}_{$order}_{$limit}_{$offset}";
$url_data = wp_cache_get($cache_key, 'url_shortener_tracker');

if ($url_data === false) {
    $url_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE url_id = %d ORDER BY $sort_by $order LIMIT %d OFFSET %d", $url_id, $limit, $offset));
    wp_cache_set($cache_key, $url_data, 'url_shortener_tracker', 3600);
}

$total_data = wp_cache_get('total_data_' . $url_id, 'url_shortener_tracker');
if ($total_data === false) {
    $total_data = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE url_id = %d", $url_id));
    wp_cache_set('total_data_' . $url_id, $total_data, 'url_shortener_tracker', 3600);
}

$total_pages = ceil($total_data / $limit);

?>

<div class="wrap">
    <h1>URL Data for URL ID <?php echo esc_html($url_id); ?></h1>

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
                <label for="data_per_page">Entries per page:</label>
                <input type="number" name="data_per_page" id="data_per_page" value="<?php echo esc_attr($limit); ?>" min="1" max="100">
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
                    <th>IP Address</th>
                    <th>Referrer</th>
                    <th>User Agent</th>
                    <th>Query String</th>
                    <th><a href="<?php echo esc_url(add_query_arg(array('sort_by' => 'timestamp', 'order' => ($sort_by == 'timestamp' && $order == 'ASC') ? 'DESC' : 'ASC'))); ?>">Timestamp</a></th>
                    <th>Language</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($url_data)) : ?>
                    <?php foreach ($url_data as $data) : ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="data_ids[]" value="<?php echo esc_attr($data->id); ?>">
                            </th>
                            <td><?php echo esc_html($data->id); ?></td>
                            <td><?php echo esc_html($data->ip_address); ?></td>
                            <td><?php echo esc_html($data->referrer); ?></td>
                            <td><?php echo esc_html($data->user_agent); ?></td>
                            <td><?php echo esc_html($data->query_string); ?></td>
                            <td><?php echo esc_html($data->timestamp); ?></td>
                            <td><?php echo esc_html($data->language); ?></td>
                            <td>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete_data', 'data_id' => $data->id, 'url_id' => $url_id)), 'delete_data_' . $data->id)); ?>" onclick="return confirm('Are you sure you want to delete this data entry?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9">No data found for this URL.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $pagination_args = array(
                    'base' => add_query_arg(array('paged' => '%#%', 'sort_by' => $sort_by, 'order' => $order, 'data_per_page' => $limit)),
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
    const checkboxes = document.querySelectorAll('input[name="data_ids[]"]');
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
