<?php if ( ! defined('EXT')) exit('Invalid file request');

// Get config file
require(PATH_THIRD.'categories_field/config.php');


/**
 * Categories Field Fieldtype
 *
 * @package        categories_field
 * @author         Nathan Pitman <nathan@ninefour.co.uk>
 * @copyright      Copyright (c) 2014, Nine Four Ltd
 */
class Categories_field_ft extends EE_Fieldtype {

    /**
     * Info array
     *
     * @var array
     */
    public $info = array(
        'name'         => NF_CF_NAME,
        'version'      => NF_CF_VERSION
    );

    // --------------------------------------------------------------------

    /**
     * Displays the field
     *
     * @access     private
     * @param      string
     * @param      bool
     * @return     string
     */
    private function _display_field($data, $cell = FALSE)
    {

        $mute_unassigned_cats = TRUE;

        $file = 'categories_field/styles/categories_field.css';
        $css = '<link rel="stylesheet" type="text/css" href="'.URL_THIRD_THEMES.$file.'" />';
        $this->EE->cp->add_to_foot($css);

        static $rows;
        $entry_id = NULL;
        $base_cats = array();
        $state = "";

        // Load helper
        $this->EE->load->helper('form');

        // Field name depending on Matrix cell or not
        $field_name = $cell ? $this->cell_name : $this->field_name;

        // Get the rows from the DB
        if (is_null($rows))
        {
            // Get Cats using the EE API
            ee()->load->library('api');
            ee()->api->instantiate('channel_categories');
            $query = ee()->api_channel_categories->category_tree(1,$data,'c');

            if (ee()->input->get('entry_id')) {
                $this->EE->db->select('cat_id');
                $this->EE->db->where('entry_id',ee()->input->get('entry_id'));
                $base_cats_query = $this->EE->db->get('category_posts');
                foreach($base_cats_query->result() AS $base_cat) {
                    $base_cats[] = $base_cat->cat_id;
                }
                unset($base_cats_query);
            }

            /*if ($cell) {

                // Prep data
                if(!is_array($data)){
                    $data = explode('|', $data);
                }

                // Init rows
                $rows = array('' => 'Select a Category...');

                foreach ($query AS $row)
                {
                    if ($row[6]) {
                        $rows[$row[0]] = " - ".$row[1];
                    } else {
                        $rows[$row[0]] = $row[1];
                    }
                }

                return form_dropdown($field_name, $rows, $data);

            } else {*/

                $rows = form_hidden($field_name, 'n');
                $rows .= '<div class="nf_category_field">';

                foreach($query AS $row)
                {
                    $selected = in_array($row[0], explode('|',$data)) ? 1 : 0;
                    $position = $row[6] ? "child" : "parent";
                    if ($mute_unassigned_cats) {
                        $state = in_array($row[0], $base_cats) ? "highlight" : "muted";
                    }

                    $rows .= '<label class="'.$state.' '.$position.'">'
                        .   form_checkbox($field_name.'[]', $row[0], $selected)
                        .   NBS .'<span>'. $row[1]
                        . '</span></label> ';
                }

                $rows .= '</div>';
                return $rows;

            //}
        }

    }

    public function save($data) {

        $auto_assign_parents = TRUE;

        if (is_array($data)) {

            // Auto assign category parents?
            if ($auto_assign_parents) {
                foreach($data AS $cat_id) {
                    // Look up each category in the DB
                    // If it has a parent assign it
                    $this->EE->db->select('parent_id');
                    $this->EE->db->where('cat_id',$cat_id);
                    $this->EE->db->where('cat_id !=',0);
                    $query = $this->EE->db->get('categories');
                    if ($query->num_rows()==1) {
                        $data[] = $query->row()->parent_id;
                    }
                }
            }

            // Remove dupes
            $data = array_unique($data);
            // To pipe delimited string
            $data = implode('|', $data);

        }

        return $data;
    }

    // --------------------------------------------------------------------

    /**
     * Displays the field in publish form
     *
     * @access     private
     * @param      string
     * @return     string
     */
    public function display_field($field_data)
    {
        return $this->_display_field($field_data);
    }

    /**
     * Displays the field in matrix
     *
     * @param   string
     * @return  string
     */
    /*public function display_cell($cell_data)
    {
        return $this->_display_field($cell_data, TRUE);
    }*/

    /**
     * Displays the field in Low Variables
     *
     * @param   string
     * @return  string
     */
    public function display_var_field($var_data)
    {
        return $this->_display_field($var_data);
    }

    /**
     * Displays the field in Grid
     *
     * @param   string
     * @return  string
     */
    public function grid_display_field($grid_data)
    {
        return $this->_display_field($grid_data);
    }

    // --------------------------------------------------------------------

    /**
     * Allow in Grid
     *
     * @param   string
     * @return  bool
     */
    public function accepts_content_type($name)
    {
        return in_array($name, array(
            'channel',
            'grid',
            'low_variables'
        ));
    }
}

// End of file ft.categories_field.php