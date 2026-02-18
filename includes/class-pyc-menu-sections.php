<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PYC_Menu_Sections
{
    public function register(): void
    {
        $labels = [
        'name'               => 'PYC Menu Sections',
        'singular_name'      => 'PYC Menu Section',
        'menu_name'          => 'PYC Menu Sections',
        'add_new'            => 'Add Section',
        'add_new_item'       => 'Add New Section',
        'edit_item'          => 'Edit Section',
        'new_item'           => 'New Section',
        'view_item'          => 'View Section',
        'search_items'       => 'Search Sections',
        'not_found'          => 'No sections found',
        'not_found_in_trash' => 'No sections found in Trash',
        ];

        $args = [
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_position' => 26,
            'menu_icon'     => 'dashicons-list-view',
            'supports'      => ['title'],
            'rewrite'       => false,
        ];

        register_post_type('pyc_menu_section', $args);
    }

    public function add_meta_boxes(): void
    {
        add_meta_box(
            'pyc_menu_section_settings',
            'Section Settings',
            [$this, 'render_meta_box'],
            'pyc_menu_section',
            'normal',
            'default'
        );
    }

    public function render_meta_box($post): void
    {
        wp_nonce_field('pyc_menu_section_save', 'pyc_menu_section_nonce');

        $active = get_post_meta($post->ID, '_pyc_section_active', true);
        $order  = get_post_meta($post->ID, '_pyc_section_order', true);

        if ($active === '') {
            $active = 1;
        }

        if ($order === '') {
            $order = 10;
        }
        ?>
        <p>
            <label>
                <input type="checkbox" name="pyc_section_active" value="1"
                    <?php checked($active, 1); ?>>
                <strong>Active</strong>
            </label>
        </p>

        <p>
            <label for="pyc_section_order"><strong>Display Order</strong></label><br>
            <input type="number"
                   name="pyc_section_order"
                   id="pyc_section_order"
                   value="<?php echo esc_attr($order); ?>"
                   step="10"
                   min="0"
                   style="width: 80px;">
        </p>

        <p style="color:#666;font-size:13px;">
            Lower numbers appear first. Use gaps like 10, 20, 30.
        </p>
        <?php
    }

    public function save_meta(int $post_id): void
    {
        if (!isset($_POST['pyc_menu_section_nonce']) ||
            !wp_verify_nonce($_POST['pyc_menu_section_nonce'], 'pyc_menu_section_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (get_post_type($post_id) !== 'pyc_menu_section') {
            return;
        }

        $active = isset($_POST['pyc_section_active']) ? 1 : 0;
        $order  = isset($_POST['pyc_section_order'])
            ? intval($_POST['pyc_section_order'])
            : 10;

        update_post_meta($post_id, '_pyc_section_active', $active);
        update_post_meta($post_id, '_pyc_section_order', $order);
    }
}
