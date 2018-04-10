<?php

/**
 * Front end checkout page.
 *
 * @since 1.0.0
 * @author Fahri Rusliyadi <fahri.rusliyadi@gmail.com>
 */
class Fr_Address_Book_for_WooCommerce_Frontend_Checkout {
    /**
     * Register actions and filters with WordPress.
     * 
     * @since 1.0.0
     */
    public function init() {
        add_action('woocommerce_before_checkout_billing_form', array($this, 'on_woocommerce_before_checkout_billing_form'));
        add_action('woocommerce_before_checkout_shipping_form', array($this, 'on_woocommerce_before_checkout_shipping_form'));
    }
    
    /**
     * <code>woocommerce_before_checkout_billing_form</code> action handler.
     * 
     * @since 1.0.0
     * @param WC_Checkout $checkout
     */
    public function on_woocommerce_before_checkout_billing_form($checkout) {
        if (!wc()->customer->get_id()) {
            return;
        }
        
        $this->enqueue_scripts();
        $this->display_select_address_field('billing');
        wp_nonce_field('fabfw_save', 'fabfw_save');
    }
    
    /**
     * <code>woocommerce_before_checkout_shipping_form</code> action handler.
     * 
     * @since 1.0.0
     * @param WC_Checkout $checkout
     */
    public function on_woocommerce_before_checkout_shipping_form($checkout) {
        if (!wc()->customer->get_id()) {
            return;
        }
                
        $this->display_select_address_field('shipping');
    }
    
    /**
     * Enqueue scripts.
     * 
     * @since 0.1.10
     */
    private function enqueue_scripts() {
        fr_address_book_for_woocommerce()->Asset->enqueue_style('fabfw_front_end', 'assets/css/frontend.min.css', array(), FR_ADDRESS_BOOK_FOR_WOOCOMMERCE_VERSION);
        fr_address_book_for_woocommerce()->Asset->enqueue_script('fabfw_select_address', 'assets/js/select-address.min.js', array('jquery'), FR_ADDRESS_BOOK_FOR_WOOCOMMERCE_VERSION, true);
        wp_localize_script('fabfw_select_address', 'fabfw_select_address', array(
            'addresses' => fr_address_book_for_woocommerce()->Customer->get_addresses(),
        ));
    }

    /**
     * Display select address field.
     * 
     * @since 1.0.0
     * @param string $type Address type (billing|shipping).
     */
    private function display_select_address_field($type) {    
        $field_options  = array();
        $meta_addresses = wc()->customer->get_meta("fabfw_address", false);
        
        foreach ($meta_addresses as $meta_data) {
            $field_options[$meta_data->id] = wc()->countries->get_formatted_address($meta_data->value);
        }
        
        $field_options['new']   = sprintf('<a class="button">%s</a>', __('New Address', 'fr-address-book-for-woocommerce'));
        $field_args             = array(
                                    'label'     => __('Address book', 'fr-address-book-for-woocommerce'),
                                    'type'      => 'radio',
                                    'options'   => $field_options,
                                );
        $field_option_keys      = array_keys($field_options);
        $saved_address_id       = wc()->customer->get_meta("fabfw_address_{$type}_id");
        $saved_address_id       = isset($field_options[$saved_address_id]) 
                                ? $saved_address_id 
                                // Use the first saved address.
                                : reset($field_option_keys);
        
        echo '<div class="fabfw-select-address-container">';
                                
        if ($meta_addresses) {
            woocommerce_form_field("fabfw_address_{$type}_id", $field_args, $saved_address_id);
        } 
        // Hide the field if no addresses saved yet.
        else {
            echo "<input type='hidden' name='fabfw_address_{$type}_id' value='new'>";
        }
        
        echo '</div>';
    }
}
