<?php if ( ! defined('EXT')) exit('Invalid file request');

// Get config file
require(PATH_THIRD.'nf_categories_field/config.php');


/**
 * Categories Field Fieldtype
 *
 * @package        nf_categories_field
 * @author         Nathan Pitman <nathan@ninefour.co.uk>
 * @copyright      Copyright (c) 2014, Nine Four Ltd
 */
class Nf_categories_field_ft extends EE_Fieldtype {

    /**
     * Info array
     *
     * @var array
     */
    public $info = array('name' => NF_CF_NAME, 'version' => NF_CF_VERSION);
    static $cache = array('includes' => array());
    var $has_array_data = TRUE;  // Required if you want tag pairs!

    /**
     * Builds the default settings
     * @param Array $data Data array from display_settings or display_cell_settings
     * @return Array $data variable merged with default settings
     */
    private function _default_settings($data)
    {
        ee()->lang->loadfile('nf_categories_field');

        return array_merge(
            array(
                'groups' => array(),
                'filter' => 'y',
                'filter_placeholder' => lang('nf_categories_field_filter_placeholder_default'),
                'filter_exclude_parents' => 'n',
                'delimiter' => '|',
                'wrapper' => '',
                'mute_unassigned_cats' => 'n',
                'auto_assign_parents' => 'y',
                'primary_cat' => 'n',
                'sync_cats' => 'y'
            ),
            (array) $data
        );
    }

    /**
     * Display Field Settings
     * @param Array $data Field settings
     */
    function display_settings($data)
    {
        $data = $this->_default_settings($data);

        ee()->lang->loadfile('nf_categories_field');

        // Category groups
        ee()->table->add_row(
            lang('nf_categories_field_groups', 'nf_categories_field_groups'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_groups_instructions') . '</i>',
            $this->_build_category_checkboxes($data, 'groups')
        );

        // Categories filter
        ee()->table->add_row(
            lang('nf_categories_field_filter', 'nf_categories_field_filter'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_filter_instructions') . '</i>',
            $this->_build_radios($data, 'filter')
        );
        // Categories filter placeholder
        ee()->table->add_row(
            lang('nf_categories_field_filter_placeholder', 'nf_categories_field_filter_placeholder'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_filter_placeholder_instructions') . '</i>',
            $this->_build_input($data, 'filter_placeholder')
        );
        // Categories filter placeholder
        ee()->table->add_row(
            lang('nf_categories_field_filter_exclude_parents', 'nf_categories_field_filter_exclude_parents'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_filter_exclude_parents_instructions') . '</i>',
            $this->_build_radios($data, 'filter_exclude_parents')
        );

        // Fields delimiter
        ee()->table->add_row(
            lang('nf_categories_field_delimiter', 'nf_categories_field_delimiter'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_delimiter_instructions') . '</i>',
            $this->_build_input($data, 'delimiter')
        );

        // Fields wrapper
        ee()->table->add_row(
            lang('nf_categories_field_wrapper', 'nf_categories_field_wrapper'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_wrapper_instructions') . '</i>',
            $this->_build_input($data, 'wrapper')
        );

        // Auto Assign Parents
        ee()->table->add_row(
            lang('nf_categories_field_auto_assign_parents', 'nf_categories_field_auto_assign_parents'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_auto_assign_parents_instructions') . '</i>',
            $this->_build_radios($data, 'auto_assign_parents')
        );

        // Primary Category
        ee()->table->add_row(
            lang('nf_categories_field_primary_cat', 'nf_categories_field_primary_cat'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_primary_cat_instructions') . '</i>',
            $this->_build_radios($data, 'primary_cat')
        );

        // Sync Categories
        ee()->table->add_row(
            lang('nf_categories_field_sync_cats', 'nf_categories_field_sync_cats'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_sync_cats_instructions') . '</i>',
            $this->_build_radios($data, 'sync_cats')
        );

        // Mute Unassigned Categories (Only applies when sync cats is disabled)
        ee()->table->add_row(
            lang('nf_categories_field_mute_unassigned_cats', 'nf_categories_field_mute_unassigned_cats'). '<br/>'
            . '<i class="instruction_text">' . lang('nf_categories_field_mute_unassigned_cats_instructions') . '</i>',
            $this->_build_radios($data, 'mute_unassigned_cats')

        );

    }

    /**
     * Builds a string of category checkboxes
     * @param Array $data Data array from display_settings or display_cell_settings
     * @return String String of checkbox fields
     */
    private function _build_category_checkboxes($data, $name)
    {
        // Get list of category groups
        $site_id = ee()->config->item('site_id');
        $category_groups = ee()->db->select("group_id, group_name")
            ->get_where('category_groups', array("site_id" => $site_id));

        // Build checkbox list
        $checkboxes = '';
        $category_group_settings = $data['groups'];
        foreach ($category_groups->result_array() as $index => $row)
        {
            // Determine checked or not
            $checked = (is_array($category_group_settings)
                AND is_numeric(array_search($row['group_id'], $category_group_settings))) ? TRUE : FALSE;

            // Build checkbox
            $checkboxes .= "<p><label>";
            $checkboxes .= form_checkbox("nf_categories_field[{$name}][]", $row["group_id"], $checked);
            $checkboxes .= " " . $row['group_name'];
            $checkboxes .= "</label></p>";
        }

        return $checkboxes;
    }

    /**
     * Builds a string of yes/no radio buttons
     */
    private function _build_radios($data, $name = 'multi')
    {
        $radio_yes = form_radio(
            "nf_categories_field[{$name}]",
            'y',
            ($data[$name] == 'y'),
            "id='nf_categories_field_{$name}_y'"
        );
        $radio_no = form_radio(
            "nf_categories_field[{$name}]",
            'n',
            ($data[$name] == 'n'),
            "id='nf_categories_field_{$name}_n'"
        );

        return $radio_yes
            . NL . lang('yes', "nf_categories_field_{$name}_y")
            . NBS . NBS . NBS . NBS . NBS . NL
            . $radio_no
            . NL . lang('no', "nf_categories_field_{$name}_n");
    }

    /**
     * Builds a string of yes/no radio buttons
     */
    private function _build_input($data, $name)
    {
        $input = form_input(
            "nf_categories_field[{$name}]",
            $data[$name]
        );

        return $input;
    }

    // Save Settings --------------------------------------------------------------------

    /**
     * Save Field Settings
     */
    function save_settings($settings)
    {
        $settings = array_merge(ee()->input->post('nf_categories_field'), $settings);

        $settings['field_show_fmt'] = 'n';
        $settings['field_type'] = 'nf_categories_field';

        return $settings;
    }

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

        $this->EE->lang->loadfile('nf_categories_field');

        // Load CSS & JS
        $this->_include_theme_css('nf_categories_field.css');
        $this->_include_theme_js('nf_categories_field.js');

        static $rows;
        $entry_id = NULL;
        $base_cats = array();
        $state = "";
        $selected_primary_input = "";

        // Load helper
        $this->EE->load->helper('form');

        // Field name depending on Matrix cell or not
        $field_name = $cell ? $this->cell_name : $this->field_name;

        // Get the rows from the DB
        if (isset($this->settings['groups']))
        {
            // Get Cats using the EE API
            ee()->load->library('api');
            ee()->api->instantiate('channel_categories');
            $query = ee()->api_channel_categories->category_tree($this->settings['groups'],$data,'c');

            if (ee()->input->get('entry_id')) {
                $this->EE->db->select('cat_id');
                $this->EE->db->where('entry_id',ee()->input->get('entry_id'));
                $base_cats_query = $this->EE->db->get('category_posts');
                foreach($base_cats_query->result() AS $base_cat) {
                    $base_cats[] = $base_cat->cat_id;
                }
                unset($base_cats_query);
            }

            $out = form_hidden($field_name);
            $out .= '<div class="nf_category_field">';
            if ($this->settings['filter']=='y') {
                $out .= '<div class="nf_category_field_filter"><input type="text" class="filter" placeholder="'.lang('nf_categories_field_filter_placeholder').'"><a class="current"><span class="count"></span></a></div>';
            }

            $indent = 0;
            $current_group_id = 0;
            $current_parent_id = 0;

            foreach($query AS $row)
            {

                $class = "category";
                $selected_primary = NULL;
                $selected_primary_label = "";

                if (($current_group_id != $row[2]) AND (count($this->settings['groups'])>1)) {
                    // This is a new group, show the title
                    $this->EE->db->select('group_name');
                    $this->EE->db->where('group_id',$row[2]);
                    $cat_group = $this->EE->db->get('category_groups')->row();
                    $rows .= '<legend><div>'.$cat_group->group_name.'</div></legend>';
                    $current_group_id = $row[2];
                    unset($cat_group);
                }

                if (is_array($data)) {
                    // If validation on the publish form fires the returned data is an array
                    $selected = in_array($row[0], $data) ? 1 : 0;
                    // Find primary category
                    foreach($data AS $data_row) {
                        if (substr( $data_row, 0, 1 ) === "p") {
                            $selected_primary = ltrim($data_row,'p');
                        }
                    }
                } else {
                    // Otherwise it's a string
                    if ($this->settings['sync_cats']=='y') {
                        $selected = in_array($row[0], $base_cats) ? 1 : 0;
                        // Find primary category
                        foreach ($base_cats as $data_row) {
                            if (substr( $data_row, 0, 1 ) === "p") {
                                $selected_primary = ltrim($data_row,'p');
                            }
                        }
                    } else {
                        $selected = in_array($row[0], explode($this->settings['delimiter'],$data)) ? 1 : 0;
                        // Find primary category
                        foreach (explode($this->settings['delimiter'],$data) as $data_row) {
                            if (substr( $data_row, 0, 1 ) === "p") {
                                $selected_primary = ltrim($data_row,'p');
                            }
                        }
                    }

                }

                $class .= $row[6] ? " child" : " parent";
                if ($this->settings['mute_unassigned_cats']=='y') {
                    $class .= in_array($row[0], $base_cats) ? " highlight" : " muted";
                }
                if ($this->settings['filter_exclude_parents']=='y' AND $row[6]) {
                    $class .= " exclude";
                }

                // Primary Category?
                if ($this->settings['primary_cat']=='y') {
                    $selected_primary = ($row[0] == $selected_primary) ? 1 : 0;
                    if ($selected_primary) {
                        $selected_primary_label = '<span class="label">Primary Category</span>';
                    }
                    $selected_primary_input = form_radio($field_name.'[]', 'p'.$row[0], $selected_primary);
                }

                $out .= '<label class="'.$class.'">'
                    .   form_checkbox($field_name.'[]', $row[0], $selected)
                    .   NBS .'<span>'. $row[1] . $selected_primary_label . '</span>' . $selected_primary_input . '</span></label>';
            }

            $out .= '</div>';
            if ($this->settings['sync_cats']=='y') {
                $out .= '<p>'.lang('nf_categories_field_syncs_publish_note').'</p>';
            }
            return $out;

        } else {
            $error = '<div class="notice">'.lang('nf_categories_field_no_cat_groups_assigned').'</div>';
            return $error;
        }

    }

    public function save($data) {

        if (is_array($data)) {

            $new_cats = array();
            $trash_cats = array();
            $primary_cat = NULL;

            // Find our primary cat (if one is set)
            foreach($data AS $selected_cat) {
                // Ooh, look here, that's our primary cat
                if (substr( $selected_cat, 0, 1 ) === "p") {
                    $primary_cat =  $selected_cat;
                }
            }

            foreach ($this->settings['groups'] as $category_group_id) {

                $this->EE->db->select('cat_id, parent_id');
                $this->EE->db->where('group_id', $category_group_id);
                $cats = $this->EE->db->get('categories')->result();

                foreach($cats AS $cat) {

                    // If this is one of our selected cats
                    if (in_array($cat->cat_id, $data)) {

                        $new_cats[] = $cat->cat_id;

                        // If this cat has a parent, assign it!
                        if ($cat->parent_id AND $this->settings['auto_assign_parents']) {
                            $new_cats[] = $cat->parent_id;
                        }

                    } else {

                        // Queue for removal from exp_category_posts
                        $trash_cats[] = $cat->cat_id;

                    }

                }

            }

            // Remove any dupes
            $new_cats = array_unique($new_cats);
            // Re-insert our primary cat at the start of the array
            if ($primary_cat) {
                array_unshift($new_cats, $primary_cat);
            }

            // To a pipe delimited string
            $data = implode($this->settings['delimiter'], $new_cats);

            if (!empty($this->settings['wrapper'])) {
                $wrap = $this->settings['wrapper'];
                $data = (string)$wrap.$data.$wrap;
            }

        }

        return $data;
    }

    public function post_save($data) {

        $selected_cats = array_filter(explode($this->settings['delimiter'], $data)); // array_filter removes empty nodes

        // Just to be sure...
        if ($this->settings['sync_cats']=='y') {

            // Get currently assigned cats
            $this->EE->db->select('cat_id');
            $this->EE->db->where('entry_id', $this->settings['entry_id']);
            $current_cats_array = $this->EE->db->get('category_posts')->result_array();
            foreach($current_cats_array AS $current_cat) {
                $current_cats[] = $current_cat['cat_id'];
            }

            // Trash cats is current cats minus any selected cats
            $trash_cats = array_diff($current_cats, $selected_cats);

            // Delete trash rows
            foreach($trash_cats as $trash_cat){
                $this->EE->db->delete('category_posts', array('entry_id' => $this->settings['entry_id'], 'cat_id' => $trash_cat));
            }

            // Insert new rows
            foreach($selected_cats as $selected_cat){
                // make sure this isn't in there already!
                if(!in_array($selected_cat, $current_cats)){
                    $this->EE->db->insert('category_posts', array(
                        'entry_id' => $this->settings['entry_id'],
                        'cat_id' => $selected_cat
                    ));
                }
            }

        }

    }

    /**
     * Replace tag
     *
     * @access    public
     * @param    field data
     * @param    field parameters
     * @param    data between tag pairs
     * @return    replacement text
     *
     */

    // {field_name}{/field_name}
    function replace_tag($data, $params = array(), $tagdata = FALSE) {

        // Establish Settings
        $settings = (isset($this->settings['nf_categories_field'])) ? $this->settings['nf_categories_field'] : $this->settings;
        $settings = $this->_default_settings($settings);

        // Explode the category string to an array
        $categories = array_filter(explode($settings['delimiter'], $data));
        // Remove the primary category (if set)
        if (substr( $categories[0], 0, 1 ) === "p") {
            unset($categories[0]);
        }

        // If there's no tagdata then return
        if (empty($tagdata))
        {
            return is_array($categories) ? implode($settings['delimiter'], $categories) : $categories;
        }

        // Loop over the tag pair for each category, parsing the {category_id} tags
        $parsed = ee()->TMPL->parse_variables(
            $tagdata,
            $this->_get_category_data($categories)
        );

        // Backspace parameter
        if (isset($params['backspace']) && $params['backspace'])
        {
            $parsed = substr($parsed, 0, -$params['backspace']);
        }

        return $parsed;
    }

    // {field_name:primary_id}
    function replace_primary_id($data, $params = array(), $tagdata = FALSE)
    {

        // Establish Settings
        $settings = (isset($this->settings['nf_categories_field'])) ? $this->settings['nf_categories_field'] : $this->settings;
        $settings = $this->_default_settings($settings);

        $primary_cat_id = FALSE;
        $categories = array_filter(explode($settings['delimiter'], $data));

        if (substr( $categories[0], 0, 1 ) === "p") {
            $primary_cat_id = ltrim($categories[0],'p');
        }

        return $primary_cat_id;
    }

    // {field_name:primary_name}
    function replace_primary_name($data, $params = array(), $tagdata = FALSE)
    {

        // Establish Settings
        $settings = (isset($this->settings['nf_categories_field'])) ? $this->settings['nf_categories_field'] : $this->settings;
        $settings = $this->_default_settings($settings);

        $primary_cat_name = FALSE;
        $categories = array_filter(explode($settings['delimiter'], $data));

        if (substr( $categories[0], 0, 1 ) === "p") {
            $primary_cat_id = ltrim($categories[0],'p');
            $primary_cat = $this->_get_category_data(array($primary_cat_id));

            if ($primary_cat[0]) {
                $primary_cat_name = $primary_cat[0]['category_name'];
            }
        }

        return $primary_cat_name;
    }

    /**
     * Given a list of category IDs, returns
     * @param  Array $cat_ids Array of category IDs
     * @return Array          Array of data read for the parser containing
     *                        category IDs, names, and url_titles
     */
    private function _get_category_data($cat_ids)
    {
        // Pull in category data and map it
        ee()->load->model('category_model');
        $category_query = ee()->db->where_in('cat_id', $cat_ids)
            ->get('categories')
            ->result_array();
        $category_data = array();
        foreach ($category_query as $data)
        {
            $category_data[$data['cat_id']] = $data;
        }

        // Create the array for parsing
        $parse = array();
        foreach ($cat_ids as $category_id)
        {
            $parse[] = array(
                'category_id' => $category_id,
                'category_parent_id' => $category_data[$category_id]['parent_id'],
                'category_name' => $category_data[$category_id]['cat_name'],
                'category_url_title' => $category_data[$category_id]['cat_url_title'],
                'category_description' => $category_data[$category_id]['cat_description'],
                'category_image' => $category_data[$category_id]['cat_image']
            );
        }

        return $parse;
    }

    // --------------------------------------------------------------------

    /**
     * Display Field on Publish
     *
     * @access    public
     * @param    existing data
     * @return    field html
     *
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


    /**
     * Include CSS theme to CP header
     *
     * @param string CSS file naname
     * @return string
     */
    public function _include_theme_css($file, &$r = FALSE) {
        if (!in_array($file, self::$cache['includes'])) {
            self::$cache['includes'][] = $file;

            $to_add = '<link rel="stylesheet" type="text/css" href="'.URL_THIRD_THEMES.'nf_categories_field/css/'.$file.'" />';

            if (REQ == 'CP')
                $this->EE->cp->add_to_head($to_add);
            else {
                $r .= $to_add;
            }
        }
    }
    /**
     * Include JS theme to CP header
     *
     * @param string JS file naname
     * @return string
     */
    public function _include_theme_js($file, &$r = FALSE) {
        if (!in_array($file, self::$cache['includes'])) {
            self::$cache['includes'][] = $file;

            $to_add = '<script type="text/javascript" src="'.URL_THIRD_THEMES.'nf_categories_field/js/'.$file.'"></script>';

            if (REQ == 'CP')
                $this->EE->cp->add_to_foot($to_add);
            else {
                $r .= $to_add;
            }
        }
    }

}

// End of file ft.nf_categories_field.php