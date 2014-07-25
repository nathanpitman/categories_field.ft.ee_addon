<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package     ExpressionEngine
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license     http://expressionengine.com/user_guide/license.html
 * @link        http://expressionengine.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * nf_categories_field Extension
 *
 * @package     ExpressionEngine
 * @subpackage  Addons
 * @category    Extension
 * @author      Nathan Pitman
 * @link
 */

class Nf_categories_field_ext {

    public $settings        = array();
    public $description     = 'Ensures that fields using Categories Field are updated when a category is deleted.';
    public $docs_url        = 'http://www.ninefour.co.uk/labs/';
    public $name            = 'Categories Field';
    public $settings_exist  = 'n';
    public $version         = '1.0';

    private $EE;

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
    public function __construct($settings = '')
    {
        $this->EE =& get_instance();
        $this->settings = $settings;
    }// ----------------------------------------------------------------------

    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see http://codeigniter.com/user_guide/database/index.html for
     * more information on the db class.
     *
     * @return void
     */
    public function activate_extension()
    {
        // Setup custom settings in this array.
        $this->settings = array();

        $hooks = array(
            'category_delete' => 'category_delete'
        );

        foreach ($hooks as $hook => $method)
        {
            $data = array(
                'class'     => __CLASS__,
                'method'    => $method,
                'hook'      => $hook,
                'settings'  => serialize($this->settings),
                'version'   => $this->version,
                'enabled'   => 'y'
            );

            $this->EE->db->insert('extensions', $data);
        }

    }

    // ----------------------------------------------------------------------

    /**
     * category_delete
     *
     * @param
     * @return
     */
    public function category_delete($cat_ids) {

        /*

        // Get fields
        $this->EE->db->select('field_id');
        $this->EE->db->where('field_type','nf_categories_field');
        $fields = $this->EE->db->get('channel_fields')->result();

        // Loop over nf_categories_field fields
        foreach($fields AS $field) {

            // Search on entries for each deleted
            // category in this field
            foreach($cat_ids AS $cat_id) {

                $this->EE->db->select('entry_id, field_id_'.$field);
                $this->EE->db->like('field_id_'.$field, '|'.$cat_id.'|');
                $fields = $this->EE->db->get('channel_fields')->result();

            }

        }

        echo('<pre>');
        print_r($cat_ids);
        echo('</pre>');
        exit;

        */

    }

    // ----------------------------------------------------------------------

    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    function disable_extension()
    {
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }

    // ----------------------------------------------------------------------

    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return  mixed   void on update / false if none
     */
    function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }
    }

    // ----------------------------------------------------------------------
}

/* End of file ext.nf_categories_field.php */
/* Location: /system/expressionengine/third_party/nf_categories_field/ext.nf_categories_field.php */