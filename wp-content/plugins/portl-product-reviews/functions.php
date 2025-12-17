<?php

// include css / js files
function custom_admin_styles_scripts($hook) {
    if (strpos($hook, 'product-reviews') === false) {   // Load only on this plugin pages
        return;
    }
    wp_enqueue_style(
        'admin-style',
        plugins_url('assets/style/style.css', __FILE__),
        array(),
        monks_version(),
        'all'
    );

    wp_enqueue_script(
        'monks-main',
        plugins_url('assets/js/main.js', __FILE__),
        array('jquery'),
        monks_version(),
        true
    );

}
add_action('admin_enqueue_scripts', 'custom_admin_styles_scripts',1);

// Utility function to get products list for dropdown
function get_products_list() {
    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];
    $products = get_posts($args);
    return $products;
}

// backend validation for media files and upload to folder
function validate_and_upload_media($field_key, $type = 'image', $max_files = 1, $count_existing_files = 0) {
    $media_urls = [];

    if (!isset($_FILES[$field_key])) {
        return [];
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $raw_files = $_FILES[$field_key];
    $normalized_files = [];

    // Normalize files whether single or multiple upload
    if (is_array($raw_files['name'])) {
        foreach ($raw_files['name'] as $i => $name) {
            if (empty($name)) continue;
            $normalized_files[] = [
                'name'     => sanitize_file_name($raw_files['name'][$i]),
                'type'     => $raw_files['type'][$i],
                'tmp_name' => $raw_files['tmp_name'][$i],
                'error'    => $raw_files['error'][$i],
                'size'     => $raw_files['size'][$i],
            ];
        }
    } else {
        if (!empty($raw_files['name'])) {
            $normalized_files[] = [
                'name'     => sanitize_file_name($raw_files['name']),
                'type'     => $raw_files['type'],
                'tmp_name' => $raw_files['tmp_name'],
                'error'    => $raw_files['error'],
                'size'     => $raw_files['size'],
            ];
        }
    }

    if (empty($normalized_files)) {
        return [];
    }

    // Allowed MIME types and size limits
    $allowed_mime_types = ($type === 'image') ? [
        'image/jpeg' => 1 * 1024 * 1024,
        'image/png'  => 1 * 1024 * 1024,
        'image/webp' => 1 * 1024 * 1024,
    ] : [
        'video/mp4'  => 7 * 1024 * 1024,
        'video/webm' => 7 * 1024 * 1024,
        'video/ogg'  => 7 * 1024 * 1024,
    ];

    // File count check
    $total_added_files = count($normalized_files) + $count_existing_files;
    if ($total_added_files > $max_files) {
        return new WP_Error(
            'too_many_files',
            "You can upload a maximum of $max_files " . ($type === 'image' ? 'image(s)' : 'video(s)') . ". You are trying to add total $total_added_files file(s)"
        );
    }

    // Validation
    $invalid_files = [];

    foreach ($normalized_files as $file) {
        $file_type = $file['type'];
        $file_size = $file['size'];
        $file_name = $file['name'];

        if ($file['error'] !== 0) {
            $invalid_files[] = "$file_name (Upload error)";
            continue;
        }

        if (!isset($allowed_mime_types[$file_type])) {
            $invalid_files[] = "$file_name (Invalid file type)";
            continue;
        }

        if ($file_size > $allowed_mime_types[$file_type]) {
            $invalid_files[] = "$file_name (File too large)";
        }
    }

    if (!empty($invalid_files)) {
        return new WP_Error(
            'invalid_files',
            'File validation failed: ' . implode(', ', $invalid_files)
        );
    }

    // Upload
    foreach ($normalized_files as $file) {
        $uploaded = wp_handle_upload($file, ['test_form' => false]);

        if (!isset($uploaded['error']) && isset($uploaded['url'])) {
            $media_urls[] = esc_url_raw($uploaded['url']);
        }
    }

    return $media_urls;
}


// Reusable delete media function
function delete_uploaded_file_by_url($file_url) {
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'];
    $base_url = $upload_dir['baseurl'];

    $relative_path = str_replace($base_url, '', $file_url);
    $full_path = $base_dir . $relative_path;

    if (file_exists($full_path)) {
        return unlink($full_path);  // returns true or false
    }
    return false;
}

function validate_file_urls($urls, $allowed_extensions = []) {
    if (count($urls) < 1) return true; // no links is valid

    foreach ($urls as $url) {
        $url = trim($url);
        if (empty($url)) continue;

        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) return false;

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_extensions)) {
            return false;
        }
    }
    return true;
}

