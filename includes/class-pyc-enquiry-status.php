<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PYC_Enquiry_Status
{
    public function register(): void
    {
        $labels = [
            'name'          => 'Enquiry Status',
            'singular_name' => 'Status',
        ];

        $args = [
            'labels'            => $labels,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'hierarchical'      => false,
            'rewrite'           => false,
        ];

        register_taxonomy(
            'pyc_enquiry_status',
            ['catering_enquiry'],
            $args
        );
    }

    public function register_default_terms(): void
    {
        $terms = [
            'New',
            'Contacted',
            'Quoted',
            'Confirmed',
            'Lost',
        ];

        foreach ($terms as $term) {
            if (!term_exists($term, 'pyc_enquiry_status')) {
                wp_insert_term($term, 'pyc_enquiry_status');
            }
        }
    }

    public function assign_default_status(int $post_id): void
    {
        if (get_post_type($post_id) !== 'catering_enquiry') {
            return;
        }

        $current_terms = wp_get_object_terms($post_id, 'pyc_enquiry_status');

        if (!empty($current_terms)) {
            return;
        }

        wp_set_object_terms($post_id, 'New', 'pyc_enquiry_status');
    }
}
