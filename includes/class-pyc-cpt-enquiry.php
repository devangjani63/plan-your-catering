<?php
error_log('PYC_CPT_Enquiry FILE LOADED');
if (!defined('ABSPATH')) {
    exit;
}

final class PYC_CPT_Enquiry
{
    public function __construct()
    {
        // Meta boxes must be hooked early and unconditionally
       add_action('add_meta_boxes_pyc_enquiry', [$this, 'add_readonly_meta_box']);
    }

    public function register(): void
    {
        error_log('PYC_CPT_Enquiry register() called');
        $labels = [
            'name'          => 'Catering Enquiries',
            'singular_name' => 'Catering Enquiry',
            'menu_name'     => 'Enquiries',
            'edit_item'     => 'View Enquiry',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_position'=> 25,
            'menu_icon'    => 'dashicons-clipboard',
            'supports'     => ['title'],
            'rewrite'      => false,
        ];

        register_post_type('pyc_enquiry', $args);
    }

    public function add_readonly_meta_box(): void
    {
        error_log('PYC_CPT_Enquiry register() called');
        add_meta_box(
            'pyc_enquiry_details',
            'Enquiry Details',
            [$this, 'render_readonly_meta_box'],
            'pyc_enquiry',
            'normal',
            'high'
        );
    }

    public function render_readonly_meta_box($post): void
    {
        error_log('render_readonly_meta_box called for post ' . $post->ID);
        if ($post->post_type !== 'pyc_enquiry') {
            return;
        }

        $payload = get_post_meta($post->ID, '_pyc_enquiry_payload', true);

        if (empty($payload) || !is_array($payload)) {
            echo '<p>No enquiry data available.</p>';
            return;
        }

        echo '<style>
            .pyc-admin-box h3 { margin: 20px 0 8px; }
            .pyc-row { margin-bottom: 6px; }
            .pyc-label { font-weight:600; width:140px; display:inline-block; }
            .pyc-section { margin-bottom:12px; }
        </style>';

        echo '<div class="pyc-admin-box">';

        /* Event */
        echo '<h3>Event Details</h3>';
        $this->row('Occasion', $payload['occasion'] ?? '');
        $this->row('Date', $payload['event_date'] ?? '');
        $this->row('City', $payload['event_city'] ?? '');
        $this->row('Guests', (string)($payload['guests'] ?? ''));

        /* Customer */
        if (!empty($payload['customer'])) {
            echo '<h3>Customer Details</h3>';
            $this->row('Name', $payload['customer']['name'] ?? '');
            $this->row('Email', $payload['customer']['email'] ?? '');
            $this->row(
                'Phone',
                ($payload['customer']['phone']['country_code'] ?? '') . ' ' .
                ($payload['customer']['phone']['number'] ?? '')
            );
        }

        /* Menu */
        echo '<h3>Menu Selected</h3>';
        if (!empty($payload['menu'])) {
            foreach ($payload['menu'] as $section) {
                $section_post = get_post($section['section_id']);
                if (!$section_post) continue;

                echo '<div class="pyc-section">';
                echo '<strong>' . esc_html($section_post->post_title) . '</strong>';
                echo '<ul>';

                foreach ($section['items'] as $item_id) {
                    $item = get_post($item_id);
                    if ($item) {
                        echo '<li>' . esc_html($item->post_title) . '</li>';
                    }
                }

                echo '</ul></div>';
            }
        } else {
            echo '<p>No menu selected.</p>';
        }

        /* Add-ons */
        echo '<h3>Add-ons</h3>';
        echo !empty($payload['addons'])
            ? '<p>' . esc_html($this->pretty_addons($payload['addons'])) . '</p>'
            : '<p>No add-ons selected.</p>';

        /* Estimate */
        if (!empty($payload['estimate'])) {
            echo '<h3>Estimate</h3>';
            echo '<p><strong>₹' .
                number_format((int)$payload['estimate']['min']) .
                ' – ₹' .
                number_format((int)$payload['estimate']['max']) .
                '</strong></p>';
        }

        echo '</div>';
    }

    private function row(string $label, string $value): void
    {
        echo '<div class="pyc-row">
            <span class="pyc-label">' . esc_html($label) . ':</span>
            <span>' . esc_html($value) . '</span>
        </div>';
    }

    private function pretty_addons(array $addons): string
    {
        $labels = [
            'live_counter' => 'Live Food Counter',
            'jain_food'    => 'Jain Food Setup',
            'staff'        => 'Extra Service Staff',
            'cutlery'      => 'Premium Cutlery',
            'decor'        => 'Decoration',
        ];

        $out = [];
        foreach ($addons as $addon) {
            $out[] = $labels[$addon] ?? ucfirst(str_replace('_', ' ', $addon));
        }

        return implode(', ', $out);
    }
}
