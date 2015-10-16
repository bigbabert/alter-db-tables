<?php
/**
 * DB Tables Import/Export
 *
 * @package   Alter_DB_Tables_Admin
 * @author    Alberto Cocchiara <info@altertech.it>
 * @license   GPL-2.0+
 * @link      http://altertech.it
 * @copyright 2015 AlterTech
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 *
 * @package Alter_DB_Tables_Admin
 * @author  Alberto Cocchiara <info@altertech.it>
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Alter_DB_Tables_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

        protected  $export_title = 'AT DB <x> ></x> EXPORT';

        /**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
        
        
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = Alter_DB_Tables::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->plugin_name = $plugin->get_plugin_name();
		$this->version = $plugin->get_plugin_version();
                $this->plugin_slug_exp = 'alter-db-tables-exp';
                $this->plugin_slug_imp = 'alter-db-tables-imp';
                
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		// Add the options page and menu item.
                add_action('init', array( $this, 'alter_tables_exp'));
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	/**
	 * CONVERT TABLE DATA INTO JSON FORMAT.
	 *
	 * @since    1.0.0
	 */

            function alter_tables_exp_json($extTable) {
            //Hide $wpdb object errors if has
            //ini_set( 'display_errors', false );
            //error_reporting( 0 );
            //Prepare query to get selected column
            if ($extTable) {
                if (ob_get_contents())
                    ob_clean();
                global $wpdb;
                $field = '';
                $getField = '';
                $query = "SELECT * FROM {$extTable}";
                $results = $wpdb->get_results($query, OBJECT);
                //$wpdb->print_error();
                //echo "<pre>"; print_r($results); echo "</pre>"; die; //just to see everything	
                //Set json file name
                $output_filename = $extTable . '_' . date('Ymd_His') . '.json'; # CSV FILE NAME WILL BE table_name_yyyymmdd_hhmmss.csv
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Content-Description: File Transfer');
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename=' . $output_filename);
                header('Expires: 0');
                header('Pragma: public');
                // Insert header row
                $first = true;
                $count = 0; // this is for $preJSON[] index

                foreach ($results as $key => $row) {
                    // Add table headers
                    $first = false;
                    // Cast the Object to an array  
                    $preJSON[$count] = (array) $row;
                    // increment the index
                    ++$count;
                }
                // Build JSON 
                $jSON = json_encode($preJSON);
                return $jSON;
            }
            //Die process after actions
            die();
        }

        /**
         * CONVERT TABLE DATA INTO CSV FORMAT.
         *
         * @since    1.0.0
         */
        function alter_tables_exp_csv($getTable) {
            //Hide $wpdb object errors if has
            ini_set('display_errors', false);
            error_reporting(0);
            //Prepare query to get selected column
            if ($getTable) {
                if (ob_get_contents())
                    ob_clean();
                global $wpdb;
                $field = '';
                $getField = '';
                $query = "SELECT * FROM {$getTable}";
                $results = $wpdb->get_results($wpdb->prepare($query, NULL));
                //$wpdb->print_error();
                //echo "<pre>"; print_r($results); echo "</pre>"; die; //just to see everything
                //Set csv file name
                $output_filename = $getTable . '_' . date('Ymd_His') . '.csv'; # CSV FILE NAME WILL BE table_name_yyyymmdd_hhmmss.csv
                $output_handle = @fopen('php://output', 'w');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Content-Description: File Transfer');
                header('Content-type: text/csv');
                header('Content-Disposition: attachment; filename=' . $output_filename);
                header('Expires: 0');
                header('Pragma: public');
                // Insert header row
                fputcsv($output_handle, $csv_fields);
                //Parse results to csv format
                $first = true;
                // Parse results to csv format
                foreach ($results as $row) {
                    // Add table headers
                    if ($first) {
                        $titles = array();
                        foreach ($row as $key => $val) {
                            $titles[] = $key;
                        }
                        fputcsv($output_handle, $titles);
                        $first = false;
                    }

                    $leadArray = (array) $row; // Cast the Object to an array
                    // Add row to file
                    fputcsv($output_handle, $leadArray);
                }
                //Flush DB cache and die process after actions
                echo $wpdb->flush();
                die();
            }
        }

    }

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {


		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id || strpos( $_SERVER[ 'REQUEST_URI' ], 'index.php' ) || strpos( $_SERVER[ 'REQUEST_URI' ], get_bloginfo( 'wpurl' ) . '/wp-admin/' ) ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array( 'dashicons' ), Alter_DB_Tables::VERSION );                        
}
echo '<style>x {color: #ec971f;}</style>';
}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), Alter_DB_Tables::VERSION );
                }
	}
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Settings page in the menu
		 * 
		 */
                global $pagenow;
		$this->plugin_screen_hook_suffix = add_management_page(__($this->export_title, $this->plugin_slug_exp), __($this->export_title, $this->plugin_slug_exp), 'manage_options', $this->plugin_slug, array( $this, 'display_export_admin_page' ),90);
                                                   //add_management_page(__('DB IMPORT'.'<i> ></i>', $this->plugin_slug), __('DB EXPORT'.'<i> ></i>', $this->plugin_slug), 'manage_options', $this->plugin_slug, array( $this, 'display_plugin_admin_page' ),90);
        }
	/**
	 * PROMPT TO OPEN/SAVE/EXPORT DATA OBJECT.
	 *
	 * @since    1.0.0
	 */
	public function alter_tables_exp() {
    $getTable = isset($_REQUEST['csv']) ? $_REQUEST['csv'] : '';
    $extTable = isset($_REQUEST['json']) ? $_REQUEST['json'] : '';
    if (is_admin()) {
        if ($getTable) {
            echo alter_tables_exp_csv($getTable);
            exit;
        } elseif ($extTable) {
            echo alter_tables_exp_json($extTable);
            exit;
        }
    }    
}  
	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_export_admin_page() {
            
		include_once( 'views/export.php' );
  }

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {
		return array_merge(
				array(
			'exp-settings' => '<a href="' . admin_url( 'tools.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings' ) . '</a>',
			'donate' => '<a href="https://www.eatscode.com/" target="_blank" >' . __( 'Donate', $this->plugin_slug ) . '</a>'
				), $links
		);
	}

}

        /* 
         * UPLOAD CSV FILE TO IMPORT
	 *
	 * @since    1.0.0
         * 
         * 
	 */

class at_csv_to_db {

    // Setup options variables
    protected  $import_title = 'AT DB <x> ></x> IMPORT';
    protected $option_name = 'at_csv_to_db';  // Name of the options array
    protected $data = array(// Default options values
        'jq_theme' => 'smoothness'
    );

    public function __construct() {
        // Check if is admin
        // We can later update this to include other user roles
        if (is_admin()) {
            add_action('admin_menu', array($this, 'at_csv_to_db_register'));  // Create admin menu page
            add_action('admin_init', array($this, 'at_csv_to_db_settings')); // Create settings
            register_activation_hook(__FILE__, array($this, 'at_csv_to_db_activate')); // Add settings on plugin activation
        }
    }

    public function at_csv_to_db_activate() {
        update_option($this->option_name, $this->data);
    }

    public function at_csv_to_db_register() {
        $at_csv_to_db_page = add_management_page(__($this->import_title, 'options-general.php'), __($this->import_title, 'at_csv_to_db'), 'manage_options', 'at_csv_to_db_menu_page', array($this, 'at_csv_to_db_menu_page')); // Add submenu page to "Settings" link in WP
        add_action('admin_print_scripts-' . $at_csv_to_db_page, array($this, 'at_csv_to_db_admin_scripts'));  // Load our admin page scripts (our page only)
        add_action('admin_print_styles-' . $at_csv_to_db_page, array($this, 'at_csv_to_db_admin_styles'));  // Load our admin page stylesheet (our page only)
    }

    public function at_csv_to_db_settings() {
        register_setting('at_csv_to_db_options', $this->option_name, array($this, 'at_csv_to_db_validate'));
    }

    public function at_csv_to_db_validate($input) {
        $valid = array();
        $valid['jq_theme'] = $input['jq_theme'];

        return $valid;
    }

    public function at_csv_to_db_admin_scripts() {
        wp_enqueue_script('media-upload');  // For WP media uploader
        wp_enqueue_script('thickbox');  // For WP media uploader
        wp_enqueue_script('jquery-ui-tabs');  // For admin panel page tabs
        wp_enqueue_script('jquery-ui-dialog');  // For admin panel popup alerts

        wp_enqueue_script('at_csv_to_db', plugins_url('/assets/js/at_page.js', __FILE__), array('jquery'));  // Apply admin page scripts
        wp_localize_script('at_csv_to_db', 'at_csv_to_db_pass_js_vars', array('ajax_image' => plugin_dir_url(__FILE__) . 'images/loading.gif', 'ajaxurl' => admin_url('admin-ajax.php')));
    }

    public function at_csv_to_db_admin_styles() {
        wp_enqueue_style('thickbox');  // For WP media uploader
        wp_enqueue_style('sdm_admin_styles', plugins_url('/assets/css/admin.css', __FILE__));  // Apply admin page styles
        // Get option for jQuery theme
        $options = get_option($this->option_name);
        $select_theme = isset($options['jq_theme']) ? $options['jq_theme'] : 'smoothness';
        ?><link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/<?php echo $select_theme; ?>/jquery-ui.css"><?php
        // For jquery ui styling - Direct from jquery
    }

    public function at_csv_to_db_menu_page() {

        // Set variables		
        global $wpdb;
        $error_message = '';
        $success_message = '';
        $message_info_style = '';

        //
        // If Delete Table button was pressed
        if (!empty($_POST['delete_db_button_hidden'])) {

            $del_qry = 'DROP TABLE ' . $_POST['table_select'];
            $del_qry_success = $wpdb->query($del_qry);

            if ($del_qry_success) {
                $success_message .= __('Congratulations!  The database table has been deleted successfully.', 'at_csv_to_db');
            } else {
                $error_message .= '* ' . __('Error deleting table. Please verify the table exists.', 'at_csv_to_db');
            }
        }

        if ((isset($_POST['export_to_csv_button'])) && (empty($_POST['table_select']))) {
            $error_message .= '* ' . __('No Database Table was selected to export. Please select a Database Table for exportation.', 'at_csv_to_db') . '<br />';
        }

        if ((isset($_POST['export_to_csv_button'])) && (!empty($_POST['table_select']))) {
            $this->CSV_GENERATE($_POST['table_select']);
        }

        // If button is pressed to "Import to DB"
        if (isset($_POST['execute_button'])) {

            // If the "Select Table" input field is empty
            if (empty($_POST['table_select'])) {
                $error_message .= '* ' . __('No Database Table was selected. Please select a Database Table.', 'at_csv_to_db') . '<br />';
            }
            // If the "Select Input File" input field is empty
            if (empty($_POST['csv_file'])) {
                $error_message .= '* ' . __('No Input File was selected. Please enter an Input File.', 'at_csv_to_db') . '<br />';
            }
            // Check that "Input File" has proper .csv file extension
            $ext = pathinfo($_POST['csv_file'], PATHINFO_EXTENSION);
            if ($ext !== 'csv') {
                $error_message .= '* ' . __('The Input File does not contain the .csv file extension. Please choose a valid .csv file.', 'at_csv_to_db');
            }

            // If all fields are input; and file is correct .csv format; continue
            if (!empty($_POST['table_select']) && !empty($_POST['csv_file']) && ($ext === 'csv')) {

                // If "disable auto_inc" is checked.. we need to skip the first column of the returned array (or the column will be duplicated)
                if (isset($_POST['remove_autoinc_column'])) {
                    $db_cols = $wpdb->get_col("DESC " . $_POST['table_select'], 0);
                    unset($db_cols[0]);  // Remove first element of array (auto increment column)
                }
                // Else we just grab all columns
                else {
                    $db_cols = $wpdb->get_col("DESC " . $_POST['table_select'], 0);  // Array of db column names
                }
                // Get the number of columns from the hidden input field (re-auto-populated via jquery)
                $numColumns = $_POST['num_cols'];

                // Open the .csv file and get it's contents
                if (( $fh = @fopen($_POST['csv_file'], 'r')) !== false) {

                    // Set variables
                    $values = array();
                    $too_many = '';  // Used to alert users if columns do not match

                    while (( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
                        if (count($row) == $numColumns) {  // If .csv column count matches db column count
                            $values[] = '("' . implode('", "', $row) . '")';  // Each new line of .csv file becomes an array
                        }
                    }

                    // If user elects to input a starting row for the .csv file
                    if (isset($_POST['sel_start_row']) && (!empty($_POST['sel_start_row']))) {

                        // Get row number from user
                        $num_var = $_POST['sel_start_row'] - 1;  // Subtract one to make counting easy on the non-techie folk!  (1 is actually 0 in binary)
                        // If user input number exceeds available .csv rows
                        if ($num_var > count($values)) {
                            $error_message .= '* ' . __('Starting Row value exceeds the number of entries being updated to the database from the .csv file.', 'at_csv_to_db') . '<br />';
                            $too_many = 'true';  // set alert variable
                        }
                        // Else splice array and remove number (rows) user selected
                        else {
                            $values = array_slice($values, $num_var);
                        }
                    }

                    // If there are no rows in the .csv file AND the user DID NOT input more rows than available from the .csv file
                    if (empty($values) && ($too_many !== 'true')) {
                        $error_message .= '* ' . __('Columns do not match.', 'at_csv_to_db') . '<br />';
                        $error_message .= '* ' . __('The number of columns in the database for this table does not match the number of columns attempting to be imported from the .csv file.', 'at_csv_to_db') . '<br />';
                        $error_message .= '* ' . __('Please verify the number of columns attempting to be imported in the "Select Input File" exactly matches the number of columns displayed in the "Table Preview".', 'at_csv_to_db') . '<br />';
                    } else {
                        // If the user DID NOT input more rows than are available from the .csv file
                        if ($too_many !== 'true') {

                            $db_query_update = '';
                            $db_query_insert = '';

                            // Format $db_cols to a string
                            $db_cols_implode = implode(',', $db_cols);

                            // Format $values to a string
                            $values_implode = implode(',', $values);


                            // If "Update DB Rows" was checked
                            if (isset($_POST['update_db'])) {

                                // Setup sql 'on duplicate update' loop
                                $updateOnDuplicate = ' ON DUPLICATE KEY UPDATE ';
                                foreach ($db_cols as $db_col) {
                                    $updateOnDuplicate .= "$db_col=VALUES($db_col),";
                                }
                                $updateOnDuplicate = rtrim($updateOnDuplicate, ',');


                                $sql = 'INSERT INTO ' . $_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode . $updateOnDuplicate;
                                $db_query_update = $wpdb->query($sql);
                            } else {
                                $sql = 'INSERT INTO ' . $_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode;
                                $db_query_insert = $wpdb->query($sql);
                            }

                            // If db db_query_update is successful
                            if ($db_query_update) {
                                $success_message = __('Congratulations!  The database has been updated successfully.', 'at_csv_to_db');
                            }
                            // If db db_query_insert is successful
                            elseif ($db_query_insert) {
                                $success_message = __('Congratulations!  The database has been updated successfully.', 'at_csv_to_db');
                                $success_message .= '<br /><strong>' . count($values) . '</strong> ' . __('record(s) were inserted into the', 'at_csv_to_db') . ' <strong>' . $_POST['table_select'] . '</strong> ' . __('database table.', 'at_csv_to_db');
                            }
                            // If db db_query_insert is successful AND there were no rows to udpate
                            elseif (($db_query_update === 0) && ($db_query_insert === '')) {
                                $message_info_style .= '* ' . __('There were no rows to update. All .csv values already exist in the database.', 'at_csv_to_db') . '<br />';
                            } else {
                                $error_message .= '* ' . __('There was a problem with the database query.', 'at_csv_to_db') . '<br />';
                                $error_message .= '* ' . __('A duplicate entry was found in the database for a .csv file entry.', 'at_csv_to_db') . '<br />';
                                $error_message .= '* ' . __('If necessary; please use the option below to "Update Database Rows".', 'at_csv_to_db') . '<br />';
                            }
                        }
                    }
                } else {
                    $error_message .= '* ' . __('No valid .csv file was found at the specified url. Please check the "Select Input File" field and ensure it points to a valid .csv file.', 'at_csv_to_db') . '<br />';
                }
            }
        }

        // If there is a message - info-style
        if (!empty($message_info_style)) {
            echo '<div class="info_message_dismiss">';
            echo $message_info_style;
            echo '<br /><em>(' . __('click to dismiss', 'at_csv_to_db') . ')</em>';
            echo '</div>';
        }

        // If there is an error message	
        if (!empty($error_message)) {
            echo '<div class="error_message">';
            echo $error_message;
            echo '<br /><em>(' . __('click to dismiss', 'at_csv_to_db') . ')</em>';
            echo '</div>';
        }

        // If there is a success message
        if (!empty($success_message)) {
            echo '<div class="success_message">';
            echo $success_message;
            echo '<br /><em>(' . __('click to dismiss', 'at_csv_to_db') . ')</em>';
            echo '</div>';
        }
        ?>
        <div class="wrap">

            <span class="head i_mange_coupon"><h1><?=$this->import_title?></h1></span>

            <p><?php _e('This plugin allows you to insert CSV file data into your WordPress database table. You can also', 'alter-db-tables'); $export = admin_url( "tools.php?page=alter-db-tables"); echo ' <a href="'.$export.'">'; _e('export the content of a database'.'</a> '.'.', 'alter-db-tables');?></p>                        
 
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1"><?php _e('Settings', 'alter-db-tables'); ?></a></li>
                    <li><a href="#tabs-2"><?php _e('Guide', 'alter-db-tables'); ?></a></li>
                </ul>

                <div id="tabs-1">

                    <form id="at_csv_to_db_form" method="post" action="">
                        <table class="form-table"> 

                            <tr valign="top"><th scope="row"><?php _e('Select Database Table:', 'alter-db-tables'); ?></th>
                                <td>
                                    <select id="table_select" name="table_select" value="">
                                        <option name="" value=""></option>

                                        <?php
                                        // Get all db table names
                                        global $wpdb;
                                        $sql = "SHOW TABLES";
                                        $results = $wpdb->get_results($sql);
                                        $repop_table = isset($_POST['table_select']) ? $_POST['table_select'] : null;

                                        foreach ($results as $index => $value) {
                                            foreach ($value as $tableName) {
                                                ?><option name="<?php echo $tableName ?>" value="<?php echo $tableName ?>" <?php if ($repop_table === $tableName) {
                                echo 'selected="selected"';
                            } ?>><?php echo $tableName ?></option><?php
                        }
                    }
                    ?>
                                    </select>
                                </td> 
                            </tr>
                            <tr valign="top"><th scope="row"><?php _e('Select Input File:', 'alter-db-tables'); ?></th>
                                <td>
                                    <?php $repop_file = isset($_POST['csv_file']) ? $_POST['csv_file'] : null; ?>
                                    <?php $repop_csv_cols = isset($_POST['num_cols_csv_file']) ? $_POST['num_cols_csv_file'] : '0'; ?>
                                    <input id="csv_file" name="csv_file"  type="text" size="70" value="<?php echo $repop_file; ?>" />
                                    <input id="csv_file_button" type="button" value="Upload" />
                                    <input id="num_cols" name="num_cols" type="hidden" value="" />
                                    <input id="num_cols_csv_file" name="num_cols_csv_file" type="hidden" value="" />
                                    <br><?php _e('File must end with a .csv extension.', 'alter-db-tables'); ?>
                                    <br><?php _e('Number of .csv file Columns:', 'alter-db-tables');
                            echo ' '; ?><span id="return_csv_col_count"><?php echo $repop_csv_cols; ?></span>
                                </td>
                            </tr>
                            <tr valign="top"><th scope="row"><?php _e('Select Starting Row:', 'alter-db-tables'); ?></th>
                                <td>
        <?php $repop_row = isset($_POST['sel_start_row']) ? $_POST['sel_start_row'] : null; ?>
                                    <input id="sel_start_row" name="sel_start_row" type="text" size="10" value="<?php echo $repop_row; ?>" />
                                    <br><?php _e('Defaults to row 1 (top row) of .csv file.', 'alter-db-tables'); ?>
                                </td>
                            </tr>
                            <tr valign="top"><th scope="row"><?php _e('Disable "auto_increment" Column:', 'alter-db-tables'); ?></th>
                                <td>
                                    <input id="remove_autoinc_column" name="remove_autoinc_column" type="checkbox" />
                                    <br><?php _e('Bypasses the "auto_increment" column;', 'alter-db-tables'); ?>
                                    <br><?php _e('This will reduce (for the purposes of importation) the number of DB columns by "1".', 'alter-db-tables'); ?>
                                </td>
                            </tr>
                            <tr valign="top"><th scope="row"><?php _e('Update Database Rows:', 'alter-db-tables'); ?></th>
                                <td>
                                    <input id="update_db" name="update_db" type="checkbox" />
                                    <br><?php _e('Will update exisiting database rows when a duplicated primary key is encountered.', 'alter-db-tables'); ?>
                                    <br><?php _e('Defaults to all rows inserted as new rows.', 'alter-db-tables'); ?>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <input id="execute_button" name="execute_button" type="submit" class="button-primary green" value="<?php _e('IMPORT CSV', 'alter-db-tables') ?>" />
                            <input id="delete_db_button" name="delete_db_button" type="button" class="button-primary red" value="<?php _e('DELETE TABLE', 'alter-db-tables') ?>" />
                            <input type="hidden" id="delete_db_button_hidden" name="delete_db_button_hidden" value="" />
                        </p>
                    </form>
                </div> <!-- End tab 1 -->
                <div id="tabs-2">
        <?php _e('Step 1 (Select Database Table):', 'alter-db-tables'); ?>
                    <ul>
                        <li><?php _e('All WP database tables will be queried and listed in the dropdown box.', 'alter-db-tables'); ?></li>
                        <li><?php _e('Select the table name which will be used for the query.', 'alter-db-tables'); ?></li>
                        <li><?php _e('Once the table is selected; the "Table Preview" will display the structure of the table.', 'alter-db-tables'); ?></li>
                        <li><?php _e('By structure, this means all column names will be listed in the order they appear in the database.', 'alter-db-tables'); ?></li>
                        <li><?php _e('This can be used to match the .csv file prior to execution; and verify it contains the same structure of columns.', 'alter-db-tables'); ?></li>
                    </ul>
                    <br /><br />
        <?php _e('Step 2 (Select Input File):', 'alter-db-tables'); ?>
                    <ul>
                        <li><?php _e('The option will be used to locate the file to be used for execution.', 'alter-db-tables'); ?></li>
                        <li><?php _e('A direct url to a .csv file may be entered into the text field.', 'alter-db-tables'); ?></li>
                        <li><?php _e('Alternatively, the "Upload" button may be used to initiate the WordPress uploader and manager.', 'alter-db-tables'); ?></li>
                        <li><?php _e('From here, the file can be uploaded from a computer or selected from the media library.', 'alter-db-tables'); ?></li>
                        <li><?php _e('The "Number of .csv file Columns" will populate when the Input File field contains a valid .csv file.', 'alter-db-tables'); ?></li>
                    </ul>
                    <br /><br />
        <?php _e('Step 3 (Select Starting Row):', 'at_csv_to_db'); ?>
                    <ul>
                        <li><?php _e('The .csv file will contain rows, which get converted to database table entries.', 'alter-db-tables'); ?></li>
                        <li><?php _e('This option will allow customization of the starting row of the .csv file to be used during the importation.', 'alter-db-tables'); ?></li>
                        <li><?php _e('Row 1 is always the top row of the .csv file.', 'alter-db-tables'); ?></li>
                        <li><?php _e('For example: If the .csv file contains column headers (column names), it would most likely be desirable to start with row 2 of the .csv file; preventing importation of the column names row.', 'alter-db-tables'); ?></li>
                    </ul>
                    <br /><br />
        <?php _e('Step 4 (Disable "auto_increment" Column):', 'alter-db-tables'); ?>
                    <ul>
                        <li><?php _e('This option will only become available when a database table is selected which contains an auto-incremented column.', 'alter-db-tables'); ?></li>
                        <li><?php _e('If importing a file which already has an auto-incremented column... this setting most likely will not be needed.', 'alter-db-tables'); ?></li>
                    </ul>
                    <br /><br />
        <?php _e('Step 5 (Update Database Rows):', 'alter-db-tables'); ?>
                    <ul>
                        <li><?php _e('By default, the plugin will add each .csv row as a new entry in the database.', 'alter-db-tables'); ?></li>
                        <li><?php _e('If the database uses a primary key (auto-increment) column, it will assign each new row with a new primary key value.', 'alter-db-tables'); ?></li>
                        <li><?php _e('This is typically how entries are added to a database.', 'alter-db-tables'); ?></li>
                        <li><?php _e('However; if a duplicate primary key is encountered, the import will stop at that exact point (and fail).', 'alter-db-tables'); ?></li>
                        <li><?php _e('If this option is checked, the import process will "update" this row rather than adding a new row.', 'alter-db-tables'); ?></li>
                    </ul>
                </div> <!-- End tab 2 -->
            </div> <!-- End #tabs -->
        </div> <!-- End page wrap -->

        <h3><?php _e('Table Preview:', 'alter-db-tables'); ?><input id="repop_table_ajax" name="repop_table_ajax" value="<?php _e('Reload Table Preview', 'alter-db-tables'); ?>" type="button" style="margin-left:20px;" /></h3>

        <div id="table_preview">
        </div>

        <p><?php _e('After selecting a database table from the dropdown above; the table column names will be shown.', 'alter-db-tables'); ?>
            <br><?php _e('This may be used as a reference when verifying the .csv file is formatted properly.', 'alter-db-tables'); ?>
            <br><?php _e('If an "auto-increment" column exists; it will be rendered in the color "red".', 'alter-db-tables'); ?>

            <!-- Delete table warning - jquery dialog -->
        <div id="dialog-confirm" title="<?php _e('Delete database table?', 'alter-db-tables'); ?>">
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php _e('This table will be permanently deleted and cannot be recovered. Proceed?', 'alter-db-tables'); ?></p>
        </div>

        <!-- Alert invalid .csv file - jquery dialog -->
        <div id="dialog_csv_file" title="<?php _e('Invalid File Extension', 'alter-db-tables'); ?>" style="display:none;">
            <p><?php _e('This is not a valid .csv file extension.', 'alter-db-tables'); ?></p>
        </div>

        <!-- Alert select db table - jquery dialog -->
        <div id="dialog_select_db" title="<?php _e('Database Table not Selected', 'alter-db-tables'); ?>" style="display:none;">
            <p><?php _e('First, please select a database table from the dropdown list.', 'alter-db-tables'); ?></p>
        </div>
           <?php     if ( !wp_is_mobile() ) { ?>
  <div class="alter-plugin-head"></a><span><?php $alter_name=__('DB Tables Import/Export', 'alter-db-tables'); echo $alter_name; ?><a href="http://altertech.it" target="_blank"><?php $alter_name=__(' by AlterTech ', 'alter-db-tables'); echo $alter_name; ?><img src="<?php echo plugins_url('views/img/alter-tech-logo.png', __FILE__ ) ;?>" width="50" height="50" class="alter_logo"/></a></span></div>
    <?php } else { ?>  
  <div class="alter-plugin-footer-mobile"></a><span><?php $alter_name=__('DB Tables Import/Export', 'alter-db-tables'); echo $alter_name; ?><a href="http://altertech.it" target="_blank"><?php $alter_name=__(' by AlterTech ', 'alter-db-tables'); echo $alter_name; ?><img src="<?php echo plugins_url('views/img/alter-tech-logo.png', __FILE__ ) ;?>" width="50" height="50" class="alter_logo"/></a></span></div>
  <?php } 
    }

}

$at_csv_to_db = new at_csv_to_db();

//  Ajax call for showing table column names
add_action('wp_ajax_at_csv_to_db_get_columns', 'at_csv_to_db_get_columns_callback');

function at_csv_to_db_get_columns_callback() {

    // Set variables
    global $wpdb;
    $sel_val = isset($_POST['sel_val']) ? $_POST['sel_val'] : null;
    $disable_autoinc = isset($_POST['disable_autoinc']) ? $_POST['disable_autoinc'] : 'false';
    $enable_auto_inc_option = 'false';
    $content = '';

    // Ran when the table name is changed from the dropdown
    if ($sel_val) {

        // Get table name
        $table_name = $sel_val;

        // Setup sql query to get all column names based on table name
        $sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "' . $wpdb->dbname . '" AND TABLE_NAME ="' . $table_name . '" AND EXTRA like "%auto_increment%"';

        // Execute Query
        $run_qry = $wpdb->get_results($sql);

        //
        // Begin response content
        $content .= '<table id="ajax_table"><tr>';

        // If the db query contains an auto_increment column
        if ((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
            //$content .= 'auto: '.$run_qry[0]->EXTRA.'<br />';
            //$content .= 'column: '.$run_qry[0]->COLUMN_NAME.'<br />';
            // If user DID NOT check 'disable_autoinc'; we need to add that column back with unique formatting 
            if ($disable_autoinc === 'false') {
                $content .= '<td class="auto_inc"><strong>' . $run_qry[0]->COLUMN_NAME . '</strong></td>';
            }

            // Get all column names from database for selected table
            $column_names = $wpdb->get_col('DESC ' . $table_name, 0);
            $counter = 0;

            //
            // IMPORTANT - If the db results contain an auto_increment; we remove the first column below; because we already added it above.
            foreach ($column_names as $column_name) {
                if ($counter++ < 1)
                    continue;  // Skip first iteration since 'auto_increment' table data cell will be duplicated
                $content .= '<td><strong>' . $column_name . '</strong></td>';
            }
        }
        // Else get all column names from database (unfiltered)
        else {
            $column_names = $wpdb->get_col('DESC ' . $table_name, 0);
            foreach ($column_names as $column_name) {
                $content .= '<td><strong>' . $column_name . '</strong></td>';
            }
        }
        $content .= '</tr></table><br />';
        $content .= __('Number of Database Columns:', 'at_csv_to_db') . ' <span id="column_count"><strong>' . count($column_names) . '</strong></span><br />';

        // If there is an auto_increment column in the returned results
        if ((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
            // If user DID NOT click the auto_increment checkbox
            if ($disable_autoinc === 'false') {
                $content .= '<div class="warning_message">';
                $content .= __('This table contains an "auto increment" column.', 'at_csv_to_db') . '<br />';
                $content .= __('Please be sure to use unique values in this column from the .csv file.', 'at_csv_to_db') . '<br />';
                $content .= __('Alternatively, the "auto increment" column may be bypassed by clicking the checkbox above.', 'at_csv_to_db') . '<br />';
                $content .= '</div>';

                // Send additional response
                $enable_auto_inc_option = 'true';
            }
            // If the user clicked the auto_increment checkbox
            if ($disable_autoinc === 'true') {
                $content .= '<div class="info_message">';
                $content .= __('This table contains an "auto increment" column that has been removed via the checkbox above.', 'at_csv_to_db') . '<br />';
                $content .= __('This means all new .csv entries will be given a unique "auto incremented" value when imported (typically, a numerical value).', 'at_csv_to_db') . '<br />';
                $content .= __('The Column Name of the removed column is', 'at_csv_to_db') . ' <strong><em>' . $run_qry[0]->COLUMN_NAME . '</em></strong>.<br />';
                $content .= '</div>';

                // Send additional response 
                $enable_auto_inc_option = 'true';
            }
        }
    } else {
        $content = '';
        $content .= '<table id="ajax_table"><tr><td>';
        $content .= __('No Database Table Selected.', 'at_csv_to_db');
        $content .= '<br />';
        $content .= __('Please select a database table from the dropdown box above.', 'at_csv_to_db');
        $content .= '</td></tr></table>';
    }

    // Set response variable to be returned to jquery
    $response = json_encode(array('content' => $content, 'enable_auto_inc_option' => $enable_auto_inc_option));
    header("Content-Type: application/json");
    echo $response;
    die();
}

// Ajax call to process .csv file for column count
add_action('wp_ajax_at_csv_to_db_get_csv_cols', 'at_csv_to_db_get_csv_cols_callback');

function at_csv_to_db_get_csv_cols_callback() {

    // Get file upload url
    $file_upload_url = $_POST['file_upload_url'];

    // Open the .csv file and get it's contents
    if (( $fh = @fopen($_POST['file_upload_url'], 'r')) !== false) {

        // Set variables
        $values = array();

        // Assign .csv rows to array
        while (( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
            //$values[] = '("' . implode('", "', $row) . '")';  // Each new line of .csv file becomes an array
            $rows[] = array(implode('", "', $row));
        }

        // Get a single array from the multi-array... and process it to count the individual columns
        $first_array_elm = reset($rows);
        $xplode_string = explode(", ", $first_array_elm[0]);

        // Count array entries
        $column_count = count($xplode_string);
    } else {
        $column_count = 'There was an error extracting data from the.csv file. Please ensure the file is a proper .csv format.';
    }

    // Set response variable to be returned to jquery
    $response = json_encode(array('column_count' => $column_count));
    header("Content-Type: application/json");
    echo $response;
    die();
}