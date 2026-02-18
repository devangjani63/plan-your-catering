<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PYC_Menu_Items
{
    
        public function __construct() {
            add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
            add_action('save_post_pyc_menu_item', [$this, 'save_meta']);
        }
        
        public function register(): void
        {
            
            
            $labels = [
        'name'               => 'PYC Menu Items',
        'singular_name'      => 'PYC Menu Item',
        'menu_name'          => 'PYC Menu Items',
        'add_new'            => 'Add Item',
        'add_new_item'       => 'Add New Item',
        'edit_item'          => 'Edit Item',
        'new_item'           => 'New Item',
        'view_item'          => 'View Item',
        'search_items'       => 'Search Items',
        'not_found'          => 'No items found',
        'not_found_in_trash' => 'No items found in Trash',
        ];

        $args = [
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_position' => 27,
            'menu_icon'     => 'dashicons-carrot',
            'supports'      => ['title'],
            'rewrite'       => false,
        ];

        register_post_type('pyc_menu_item', $args);
    }

    public function add_meta_boxes(): void
{
    add_meta_box(
        'pyc_menu_item_meta',
        'Menu Item Details',
        [$this, 'render_meta_box'], // ✅ MATCHES METHOD
        'pyc_menu_item',
        'side',
        'default'
    );
}

    public function render_meta_box($post): void
    {
        wp_nonce_field('pyc_menu_item_save', 'pyc_menu_item_nonce');

        $section_id = get_post_meta($post->ID, '_pyc_section_id', true);
        $weight     = get_post_meta($post->ID, '_pyc_item_weight', true);

        $sections = get_posts([
            'post_type'   => 'pyc_menu_section',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ]);
        ?>
        <p>
            <label for="pyc_section_id"><strong>Menu Section</strong></label><br>
            <select name="pyc_section_id" id="pyc_section_id" required>
                <option value="">Select Section</option>
                <?php foreach ($sections as $section): ?>
                    <option value="<?php echo esc_attr($section->ID); ?>"
                        <?php selected($section_id, $section->ID); ?>>
                        <?php echo esc_html($section->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="pyc_item_weight"><strong>Item Weight</strong></label><br>
            <select name="pyc_item_weight" id="pyc_item_weight" required>
                <option value="">Select Weight</option>
                <option value="1" <?php selected($weight, '1'); ?>>1 (Simple)</option>
                <option value="2" <?php selected($weight, '2'); ?>>2 (Medium)</option>
                <option value="3" <?php selected($weight, '3'); ?>>3 (Heavy)</option>
            </select>
        </p>
        <?php
    }
    
    
        
        
    public function save_meta(int $post_id): void
{
    // Verify nonce (single source of truth)
    if (
        !isset($_POST['pyc_menu_item_nonce']) ||
        !wp_verify_nonce($_POST['pyc_menu_item_nonce'], 'pyc_menu_item_save')
    ) {
        return;
    }

    // Autosave protection
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Ensure correct post type
    if (get_post_type($post_id) !== 'pyc_menu_item') {
        return;
    }

    // Save section relationship
    if (isset($_POST['pyc_section_id'])) {
        update_post_meta(
            $post_id,
            '_pyc_section_id',
            intval($_POST['pyc_section_id'])
        );
    }

    // Save item weight
    if (isset($_POST['pyc_item_weight'])) {
        update_post_meta(
            $post_id,
            '_pyc_item_weight',
            sanitize_text_field($_POST['pyc_item_weight'])
        );
    }
}

    
    
}
