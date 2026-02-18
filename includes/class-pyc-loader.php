<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PYC_Loader
{
    public function run(): void
    {
        $this->register_enquiry_cpt();
        $this->register_enquiry_status();
        $this->register_menu_sections();
        $this->register_menu_items();
        $this->register_shortcode();
        
        add_action('admin_post_pyc_submit_enquiry', [$this, 'handle_enquiry_submission']);
        add_action('admin_post_nopriv_pyc_submit_enquiry', [$this, 'handle_enquiry_submission']);
        add_action('add_meta_boxes', [$this, 'add_enquiry_meta_boxes']);
        add_action('add_meta_boxes', [$this, 'add_enquiry_payload_meta_box']);
        add_filter('manage_pyc_enquiry_posts_columns', [$this, 'enquiry_columns']);
        add_action('manage_pyc_enquiry_posts_custom_column', [$this, 'render_enquiry_columns'], 10, 2);
        add_filter('manage_edit-pyc_enquiry_sortable_columns', [$this, 'sortable_enquiry_columns']);
       


    }

    private function register_enquiry_status(): void
    {
        require_once PYC_PLUGIN_DIR . 'includes/class-pyc-enquiry-status.php';

        $status = new PYC_Enquiry_Status();

        add_action('init', [$status, 'register']);
        add_action('init', [$status, 'register_default_terms']);
        add_action('save_post', [$status, 'assign_default_status']);
    }

    private function register_menu_sections(): void
    {
        require_once PYC_PLUGIN_DIR . 'includes/class-pyc-menu-sections.php';

        $sections = new PYC_Menu_Sections();

        add_action('init', [$sections, 'register']);
        add_action('add_meta_boxes', [$sections, 'add_meta_boxes']);
        add_action('save_post', [$sections, 'save_meta']);
    }

    private function register_menu_items(): void
    {
        require_once PYC_PLUGIN_DIR . 'includes/class-pyc-menu-items.php';

        $items = new PYC_Menu_Items();

        add_action('init', [$items, 'register']);
        add_action('add_meta_boxes', [$items, 'add_meta_boxes']);
        add_action('save_post', [$items, 'save_meta']);
    }
    private function register_shortcode(): void
{
    require_once PYC_PLUGIN_DIR . 'includes/class-pyc-shortcode.php';

    $shortcode = new PYC_Shortcode();
    add_action('init', [$shortcode, 'register']);
}

private function register_enquiry_cpt(): void
{
    require_once PYC_PLUGIN_PATH . 'includes/class-pyc-email.php';
    $labels = [
        'name'               => 'Enquiries',
        'singular_name'      => 'Enquiry',
        'menu_name'          => 'Enquiries',
        'add_new'            => 'Add Enquiry',
        'add_new_item'       => 'Add New Enquiry',
        'edit_item'          => 'View Enquiry',
        'new_item'           => 'New Enquiry',
        'view_item'          => 'View Enquiry',
        'search_items'       => 'Search Enquiries',
        'not_found'          => 'No enquiries found',
        'not_found_in_trash' => 'No enquiries found in Trash',
    ];

    $args = [
        'labels'          => $labels,
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_position'   => 26,
        'menu_icon'       => 'dashicons-email-alt',
        'supports'        => ['title'],
        'rewrite'         => false,
    ];

    register_post_type('pyc_enquiry', $args);
}

public function handle_enquiry_submission(): void
{
    error_log('PYC handler hit');
    if (!isset($_POST['pyc_payload'])) {
        wp_die('Invalid enquiry submission.');
    }

    $payload = json_decode(stripslashes($_POST['pyc_payload']), true);

    if (!$payload || !is_array($payload)) {
        wp_die('Invalid enquiry data.');
    }

    // Debug log (IMPORTANT FOR NOW)
    error_log('PYC Enquiry Payload: ' . print_r($payload, true));

    $title = 'Enquiry - ' . ($payload['occasion'] ?? 'Event') . ' - ' . date('Y-m-d');

    $enquiry_id = wp_insert_post([
    'post_type'   => 'pyc_enquiry',
    'post_title'  => $title,
    'post_status' => 'publish',
]);

if (is_wp_error($enquiry_id)) {
    wp_die('Could not save enquiry.');
}

// STEP C — Save lead details (structured)
if (!empty($payload['customer'])) {

    update_post_meta(
        $enquiry_id,
        '_pyc_customer_name',
        sanitize_text_field($payload['customer']['name'] ?? '')
    );

    update_post_meta(
        $enquiry_id,
        '_pyc_customer_email',
        sanitize_email($payload['customer']['email'] ?? '')
    );

    update_post_meta(
        $enquiry_id,
        '_pyc_customer_phone_cc',
        sanitize_text_field($payload['customer']['phone']['country_code'] ?? '+91')
    );

    update_post_meta(
        $enquiry_id,
        '_pyc_customer_phone',
        sanitize_text_field($payload['customer']['phone']['number'] ?? '')
    );
}

// Save full payload (snapshot)
update_post_meta($enquiry_id, '_pyc_enquiry_payload', $payload);

        // STEP D — Prepare email data (safe read-only)
        $email_data = [
            'enquiry_id' => $enquiry_id,
            'occasion'   => $payload['occasion'] ?? '',
            'event_date' => $payload['event_date'] ?? '',
            'event_city' => $payload['event_city'] ?? '',
            'guests'     => $payload['guests'] ?? '',
            'customer'   => $payload['customer'] ?? [],
            'menu'       => $payload['menu'] ?? [],
            'addons'     => $payload['addons'] ?? [],
            'estimate'   => $payload['estimate'] ?? [],
        ];
        
       PYC_Email::send_admin($email_data);
        PYC_Email::send_user($email_data);


    // Temporary redirect
    wp_redirect(home_url());
    exit;
}

public function add_enquiry_meta_boxes(): void
{
    add_meta_box(
        'pyc_enquiry_lead_details',
        'Lead Details',
        [$this, 'render_enquiry_lead_meta_box'],
        'pyc_enquiry',
        'side',
        'default'
    );
}

public function render_enquiry_lead_meta_box($post): void
{
    $name  = get_post_meta($post->ID, '_pyc_customer_name', true);
    $email = get_post_meta($post->ID, '_pyc_customer_email', true);
    $cc    = get_post_meta($post->ID, '_pyc_customer_phone_cc', true);
    $phone = get_post_meta($post->ID, '_pyc_customer_phone', true);
    ?>

    <p>
        <strong>Name:</strong><br>
        <?php echo esc_html($name ?: '—'); ?>
    </p>

    <p>
        <strong>Email:</strong><br>
        <?php echo esc_html($email ?: '—'); ?>
    </p>

    <p>
        <strong>Phone:</strong><br>
        <?php
        if ($phone) {
            echo esc_html(trim($cc . ' ' . $phone));
        } else {
            echo '—';
        }
        ?>
    </p>

    <?php
}
public function enquiry_columns(array $columns): array
{
    unset($columns['date']);

    $columns['customer'] = 'Customer';
    $columns['occasion'] = 'Occasion';
    $columns['guests']   = 'Guests';
    $columns['estimate'] = 'Estimate';
    $columns['date']     = 'Date';

    return $columns;
}

public function render_enquiry_columns(string $column, int $post_id): void
{
    $payload = get_post_meta($post_id, '_pyc_enquiry_payload', true);

    switch ($column) {

        case 'customer':
            $name  = get_post_meta($post_id, '_pyc_customer_name', true);
            $phone = get_post_meta($post_id, '_pyc_customer_phone', true);
            echo esc_html(trim($name . ' / ' . $phone));
            break;

        case 'occasion':
            echo esc_html($payload['occasion'] ?? '—');
            break;

        case 'guests':
            echo esc_html($payload['guests'] ?? '—');
            break;

        case 'estimate':
            if (!empty($payload['estimate'])) {
                echo '₹' . number_format($payload['estimate']['min'])
                   . ' – ₹' . number_format($payload['estimate']['max']);
            } else {
                echo '—';
            }
            break;
    }
}

public function sortable_enquiry_columns(array $columns): array
{
    $columns['guests'] = 'guests';
    return $columns;
}

public function add_enquiry_payload_meta_box(): void
{
    add_meta_box(
        'pyc_enquiry_payload',
        'Enquiry Details',
        [$this, 'render_enquiry_payload_meta_box'],
        'pyc_enquiry',
        'normal',
        'high'
    );
}

public function render_enquiry_payload_meta_box($post): void
{
    $payload = get_post_meta($post->ID, '_pyc_enquiry_payload', true);

    if (empty($payload) || !is_array($payload)) {
        echo '<p>No enquiry data available.</p>';
        return;
    }

    echo '<style>
        .pyc-row { margin-bottom:8px; }
        .pyc-label { font-weight:600; width:140px; display:inline-block; }
        .pyc-section { margin-top:18px; }
    </style>';

    /* Event */
    echo '<div class="pyc-section"><h3>Event Details</h3>';
    $this->pyc_row('Occasion', $payload['occasion'] ?? '—');
    $this->pyc_row('Date', $payload['event_date'] ?? '—');
    $this->pyc_row('City', $payload['event_city'] ?? '—');
    $this->pyc_row('Guests', $payload['guests'] ?? '—');
    echo '</div>';

    /* Menu */
    echo '<div class="pyc-section"><h3>Menu Selected</h3>';
    if (!empty($payload['menu'])) {
        foreach ($payload['menu'] as $section) {
            $section_post = get_post($section['section_id']);
            if (!$section_post) continue;

            echo '<strong>' . esc_html($section_post->post_title) . '</strong><ul>';
            foreach ($section['items'] as $item_id) {
                $item = get_post($item_id);
                if ($item) {
                    echo '<li>' . esc_html($item->post_title) . '</li>';
                }
            }
            echo '</ul>';
        }
    } else {
        echo '<p>No menu selected.</p>';
    }
    echo '</div>';

    /* Add-ons */
    echo '<div class="pyc-section"><h3>Add-ons</h3>';
    echo !empty($payload['addons'])
        ? esc_html($this->pretty_addons($payload['addons']))
        : 'No add-ons selected.';
    echo '</div>';

    /* Estimate */
    if (!empty($payload['estimate'])) {
        echo '<div class="pyc-section"><h3>Estimate</h3>';
        echo '<strong>₹' . number_format($payload['estimate']['min'])
           . ' – ₹' . number_format($payload['estimate']['max']) . '</strong>';
        echo '</div>';
    }
}

private function pyc_row(string $label, string $value): void
{
    echo '<div class="pyc-row">
        <span class="pyc-label">' . esc_html($label) . ':</span>
        <span>' . esc_html($value) . '</span>
    </div>';
}

private function pretty_addons(array $addons): string
{
    if (empty($addons)) {
        return 'No add-ons selected';
    }

    $labels = [
        'live_counter' => 'Live Food Counter',
        'jain_food'    => 'Jain Food Setup',
        'staff'        => 'Extra Service Staff',
        'cutlery'      => 'Premium Cutlery',
        'decor'        => 'Decoration',
    ];

    $out = [];

    foreach ($addons as $addon) {
        $out[] = $labels[$addon] ?? ucwords(str_replace('_', ' ', $addon));
    }

    return implode(', ', $out);
}





}
