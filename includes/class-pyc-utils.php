<?php
if (!defined('ABSPATH')) exit;

final class PYC_Utils {

    public static function country_codes(): array {
        return [
            ['code' => '+91', 'label' => 'India', 'digits' => 10],
            ['code' => '+1',  'label' => 'United States', 'digits' => 10],
            ['code' => '+44', 'label' => 'United Kingdom', 'digits' => 10],
            ['code' => '+61', 'label' => 'Australia', 'digits' => 9],
            ['code' => '+971','label' => 'UAE', 'digits' => 9],
            ['code' => '+65', 'label' => 'Singapore', 'digits' => 8],
            ['code' => '+60', 'label' => 'Malaysia', 'digits' => 9],
            ['code' => '+81', 'label' => 'Japan', 'digits' => 10],
            ['code' => '+49', 'label' => 'Germany', 'digits' => 10],
            ['code' => '+86', 'label' => 'China', 'digits' => 11],
            // extend safely anytime
        ];
    }
}
