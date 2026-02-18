<?php
if (!defined('ABSPATH')) exit;

final class PYC_Email {

    /* -------------------------
     * Common Headers
     * ------------------------- */
    private static function headers(): array {
        return [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];
    }

    /* -------------------------
     * ADMIN EMAIL
     * ------------------------- */
    public static function send_admin(array $data): void {

        $admin_email = get_option('admin_email');
        $subject = 'New Catering Enquiry – ' . ($data['occasion'] ?: 'Event');

        $menu_html   = self::render_menu_sections($data['menu'] ?? []);
        $addons_html = self::render_addons($data['addons'] ?? []);

        $html = '
        <div style="font-family:Arial;background:#f4f5f7;padding:24px">
          <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;padding:28px">

            <h2 style="margin-top:0;">New Catering Enquiry</h2>

            <h3>Event Details</h3>
            <table width="100%" cellpadding="6">
              <tr><td><strong>Occasion</strong></td><td>'.esc_html($data['occasion']).'</td></tr>
              <tr><td><strong>Date</strong></td><td>'.esc_html($data['event_date']).'</td></tr>
              <tr><td><strong>City</strong></td><td>'.esc_html($data['event_city']).'</td></tr>
              <tr><td><strong>Guests</strong></td><td>'.esc_html($data['guests']).'</td></tr>
            </table>

            <h3>Customer</h3>
            <p>
              '.esc_html($data['customer']['name'] ?? '').'<br>
              '.esc_html($data['customer']['email'] ?? '').'<br>
              '.esc_html(($data['customer']['phone']['country_code'] ?? '') . ' ' . ($data['customer']['phone']['number'] ?? '')).'
            </p>

            <h3>Selected Menu</h3>
            '.$menu_html.'

            <h3>Add-ons</h3>
            '.$addons_html.'

            <h3>Estimated Cost</h3>
            <p style="font-size:18px;font-weight:bold;">
              ₹'.esc_html($data['estimate']['min'] ?? '').' – ₹'.esc_html($data['estimate']['max'] ?? '').'
            </p>

          </div>
        </div>';

        wp_mail($admin_email, $subject, $html, self::headers());
    }

    /* -------------------------
     * USER EMAIL (NO CUSTOMER BLOCK)
     * ------------------------- */
    public static function send_user(array $data): void {

        if (empty($data['customer']['email'])) return;

        $subject = 'Your Catering Enquiry Summary';

        $menu_html   = self::render_menu_sections($data['menu'] ?? []);
        $addons_html = self::render_addons($data['addons'] ?? []);

        $html = '
        <div style="font-family:Arial;background:#f4f5f7;padding:24px">
          <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;padding:28px">

            <h2>Thank you for your catering enquiry</h2>

            <p>
              Dear '.esc_html($data['customer']['name'] ?? '').',<br>
              Below is a summary of your event plan for reference.
            </p>

            <h3>Event Details</h3>
            <table width="100%" cellpadding="6">
              <tr><td><strong>Occasion</strong></td><td>'.esc_html($data['occasion']).'</td></tr>
              <tr><td><strong>Date</strong></td><td>'.esc_html($data['event_date']).'</td></tr>
              <tr><td><strong>City</strong></td><td>'.esc_html($data['event_city']).'</td></tr>
              <tr><td><strong>Guests</strong></td><td>'.esc_html($data['guests']).'</td></tr>
            </table>

            <h3>Selected Menu</h3>
            '.$menu_html.'

            <h3>Add-ons</h3>
            '.$addons_html.'

            <h3>Estimated Cost</h3>
            <p style="font-size:18px;font-weight:bold;">
              ₹'.esc_html($data['estimate']['min'] ?? '').' – ₹'.esc_html($data['estimate']['max'] ?? '').'
            </p>

            <p style="margin-top:24px;">
              Our team will contact you shortly to finalize details.
            </p>

            <p>
              Regards,<br>
              <strong>'.esc_html(get_bloginfo('name')).'</strong>
            </p>

          </div>
        </div>';

        wp_mail($data['customer']['email'], $subject, $html, self::headers());
    }

    /* -------------------------
     * MENU RENDERER (SECTION WISE)
     * ------------------------- */
    private static function render_menu_sections(array $menu): string {
        if (empty($menu)) return '<p>No menu selected.</p>';

        $html = '';

        foreach ($menu as $section) {
            $section_post = get_post($section['section_id']);
            if (!$section_post) continue;

            $html .= '
            <div style="margin-bottom:16px;">
                <div style="font-weight:bold;font-size:15px;margin-bottom:6px;">
                    '.esc_html($section_post->post_title).'
                </div>
                <ul style="margin:0;padding-left:18px;">';

            foreach ($section['items'] as $item_id) {
                $item = get_post($item_id);
                if ($item) {
                    $html .= '<li>'.esc_html($item->post_title).'</li>';
                }
            }

            $html .= '
                </ul>
            </div>';
        }

        return $html;
    }

    /* -------------------------
     * ADD-ON LABELS
     * ------------------------- */
    private static function addon_labels(): array {
        return [
            'live_counter' => 'Live Food Counter',
            'jain_food'    => 'Jain Food Setup',
            'staff'        => 'Extra Service Staff',
            'cutlery'      => 'Premium Cutlery',
            'decor'        => 'Decoration',
        ];
    }

    private static function render_addons(array $addons): string {
        if (empty($addons)) {
            return '<p>No add-ons selected.</p>';
        }

        $labels = self::addon_labels();
        $pretty = [];

        foreach ($addons as $addon) {
            $pretty[] = esc_html($labels[$addon] ?? ucfirst(str_replace('_', ' ', $addon)));
        }

        return '<p>' . implode(', ', $pretty) . '</p>';
    }
}
