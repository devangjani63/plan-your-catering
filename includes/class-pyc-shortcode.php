<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PYC_Shortcode
{
    public function register(): void
    {
        add_shortcode('plan_catering', [$this, 'render']);
    }

    public function render(): string
    {
        $this->enqueue_assets();

        $menu_items_by_section = $this->get_menu_items_grouped();

        wp_localize_script(
            'pyc-plan-catering',
            'PYC_MENU_ITEMS',
            $menu_items_by_section
        );

        ob_start();
        ?>
        <div id="pyc-plan-catering" class="pyc-container">

            <div class="pyc-header">
                <h2>Plan Your Catering</h2>
                <p class="pyc-step-indicator">
                  Step <span>1</span> of <span>7</span>
                </p>
            </div>

            <!-- STEP 1 -->
            <div class="pyc-step" data-step="1">
                <h3>Event Details</h3>
                <div class="pyc-field">
                    <label>Occasion *</label>
                    <input type="text" name="occasion" data-required="1">
                </div>
                <div class="pyc-field">
                    <label>Event Date *</label>
                    <input type="date" name="event_date" data-required="1">
                </div>
                <div class="pyc-field">
                    <label>Event City *</label>
                    <input type="text" name="event_city" data-required="1">
                </div>
            </div>

            <!-- STEP 2 -->
            <div class="pyc-step" data-step="2">
                <h3>Guests</h3>
                <div class="pyc-field">
                    <label>Number of Guests *</label>
                    <input type="number" name="guests" min="1" data-required="1">
                </div>
            </div>

            <!-- STEP 3 -->
            <div class="pyc-step" data-step="3">
                <h3>Select Menu Sections</h3>

                <div class="pyc-menu-sections">
                    <?php
                    $sections = get_posts([
                        'post_type'      => 'pyc_menu_section',
                        'posts_per_page' => -1,
                        'meta_key'       => '_pyc_section_order',
                        'orderby'        => 'meta_value_num',
                        'order'          => 'ASC',
                        'meta_query'     => [
                            [
                                'key'   => '_pyc_section_active',
                                'value' => 1,
                            ],
                        ],
                    ]);

                    if (empty($sections)) :
                    ?>
                        <p class="pyc-debug">No menu sections available.</p>
                    <?php
                    else :
                        foreach ($sections as $section) :
                    ?>
                            <button
                                type="button"
                                class="pyc-menu-section-btn"
                                data-section-id="<?php echo esc_attr($section->ID); ?>">
                                <?php echo esc_html($section->post_title); ?>
                            </button>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>

            <!-- STEP 4: MENU SUMMARY -->
            <div class="pyc-step" data-step="4">
                <h3>Selected Menu</h3>
            
                <div id="pyc-menu-summary">
                    <p class="pyc-debug">No menu items selected.</p>
                </div>
            </div>

            <!-- STEP 5: ADD-ONS -->
            <div class="pyc-step" data-step="5">
                <h3>Add-ons</h3>
            
                <div id="pyc-addons">
                    <!-- JS will render add-ons here -->
                </div>
            </div>

           <!-- STEP 6: LEAD DETAILS -->
        <div class="pyc-step" data-step="6">
        
          <h3>Your Details</h3>
        
          <div class="pyc-field">
            <label>Your Name *</label>
            <input type="text" name="customer_name" data-required="1">
          </div>
        
          <div class="pyc-field">
            <label>Email *</label>
            <input type="email" name="customer_email" data-required="1">
          </div>
        
          <div class="pyc-field">
            <label>Mobile Number *</label>
            <div class="pyc-phone-wrap">
              <select name="country_code" data-required="1" data-country-select>
                <?php
                $country_codes = [
                    ['code' => '+91', 'label' => 'India', 'digits' => 10],
                    ['code' => '+1',  'label' => 'United States', 'digits' => 10],
                    ['code' => '+44', 'label' => 'United Kingdom', 'digits' => 10],
                    ['code' => '+61', 'label' => 'Australia', 'digits' => 9],
                    ['code' => '+971','label' => 'UAE', 'digits' => 9],
                ];
                foreach ($country_codes as $c):
                ?>
                  <option
                    value="<?php echo esc_attr($c['code']); ?>"
                    data-digits="<?php echo esc_attr($c['digits']); ?>"
                    <?php selected($c['code'], '+91'); ?>
                  >
                    <?php echo esc_html($c['code'] . ' (' . $c['label'] . ')'); ?>
                  </option>
                <?php endforeach; ?>
                </select>

              <input
                type="tel"
                name="customer_phone"
                placeholder="10-digit number"
                data-required="1"
              >
            </div>
          </div>
        
        </div>
        
        <!-- STEP 7: ESTIMATE -->
        <div class="pyc-step" data-step="7">
        
          <h3>Estimated Cost</h3>
        
          <div id="pyc-estimate-box">
            <p class="pyc-debug">Estimate will appear here.</p>
          </div>
        
          <p class="pyc-note">
            * This is an approximate range. Final pricing depends on confirmation.
          </p>
        
        </div>
                      
          <div class="pyc-navigation">
              <button type="button" class="pyc-btn pyc-btn-secondary">Back</button>
              <button type="button" class="pyc-btn pyc-btn-primary">Submit</button>
            </div>

        <!-- MENU ITEMS MODAL -->
        <div id="pyc-menu-modal" class="pyc-modal" aria-hidden="true">
            <div class="pyc-modal-content">
                <div class="pyc-modal-header">
                    <h3 id="pyc-modal-title"></h3>
                    <button type="button" class="pyc-modal-close">×</button>
                </div>
                <div class="pyc-modal-body">
                    <ul id="pyc-modal-items"></ul>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function enqueue_assets(): void
    {
        wp_enqueue_style(
            'pyc-plan-catering',
            PYC_PLUGIN_URL . 'public/css/plan-catering.css',
            [],
            PYC_VERSION
        );

        wp_enqueue_script(
            'pyc-plan-catering',
            PYC_PLUGIN_URL . 'public/js/plan-catering.js',
            [],
            PYC_VERSION,
            true
        );
        
        wp_localize_script(
        'pyc-plan-catering',
        'pyc_vars',
        [
            'post_url' => admin_url('admin-post.php'),
        ]
      );

    }

    private function get_menu_items_grouped(): array
    {
        $items = get_posts([
            'post_type'      => 'pyc_menu_item',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        $grouped = [];

        foreach ($items as $item) {
            $section_id = get_post_meta($item->ID, '_pyc_section_id', true);
            if (!$section_id) {
                continue;
            }

            if (!isset($grouped[$section_id])) {
                $grouped[$section_id] = [];
            }

            $grouped[$section_id][] = [
            'id'     => $item->ID,
            'title'  => $item->post_title,
            'weight' => get_post_meta($item->ID, '_pyc_item_weight', true) ?: '2',
            ];
                }

        return $grouped;
    }
}
