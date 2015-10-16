<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Alter_DB_Tables
 * @author    Alberto Cocchiara <info@altertech.it>
 * @license   GPL-2.0+
 * @link      http://altertech.it
 * @copyright 2015 AlterTech
 */
?>
<div class="wrap">
	<div id="tabs">
		<div id="tabs-1"><div id="wrapper">
        <div class="titlebg" id="plugin_title">
            <span class="head i_mange_coupon"><h1><?=$this->export_title?></h1></span>
            <p><?php _e('This plugin allows you to export DB data into CSV or JSON format file. You can also', 'alter-db-tables'); $import = admin_url( 'tools.php?page=at_csv_to_db_menu_page'); echo ' <a href="'.$import.'">'; _e('import the content in to database'.'</a> '.'.', 'alter-db-tables');?></p>                        
        </div>
        <div id="page">			
            <div id="export-columns">
                <div id="export-column1">
                    <table cellspacing="0" class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th><?=_e('ID', $this->plugin_slug)?></th>
                                <th><?=_e('NAME', $this->plugin_slug)?></th>
                                <th colspan="2"><?=_e('ACTIONS', $this->plugin_slug)?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            global $wpdb;
                            $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;                            
                            $limit = 6;
                            $offset = ( $pagenum - 1 ) * $limit;
                            // build query
                            $allTables = $wpdb->get_results("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='$wpdb->dbname' LIMIT $offset, $limit", OBJECT);
                            //echo "<pre>"; print_r($allTables); echo "</pre>"; die; //just to see everything
                            $p = 0;
                            $c = true;
                            foreach ($allTables AS $tableName) {
                                $p = $p + 1;
                                ?>
                            <?php echo '<tr' . (($c = !$c) ? ' class="odd"' : ' class="even"') . ">"; ?>
                            <td><?php _e($p); ?></td>
                            <td><?php echo $tableName->TABLE_NAME; ?></td>
                            <td><a class="button button-large green" href="tools.php?page=<?php echo $this->plugin_slug ?>&csv=<?php echo $tableName->TABLE_NAME; ?>"><?php _e('EXPORT CSV', $this->plugin_slug); ?></a></td>
                            <td><a class="button button-large green" id="exp" href="tools.php?page=<?php echo $this->plugin_slug ?>&json=<?php echo $tableName->TABLE_NAME; ?>"><?php _e('EXPORT JSON', $this->plugin_slug); ?></a></td>
                            </tr>
    <?php }   ?>

                        </tbody>
                    </table>
                    <?php
 $limit = 28;
$total = $wpdb->get_var("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '$wpdb->dbname'");
$num_of_pages = ceil( $total / $limit);
$page_links = paginate_links( array(
    'base' => add_query_arg( 'pagenum', '%#%' ),
    'format' => '',
    'prev_text' => __( '&laquo;', 'aag' ),
    'next_text' => __( '&raquo;', 'aag' ),
    'total' => $num_of_pages,
    'current' => $pagenum
) );
 
if ( $page_links ) {
    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em auto">' . $page_links . '</div></div>';
}
 
echo '</div>';
?>
                </div>
            </div>
        </div>
    </div></div>
            <div id="tabs-2">                
            </div>
   <?php     if ( !wp_is_mobile() ) { ?>
  <div class="alter-plugin-head"></a><span><?php $alter_name=__('DB Tables Import/Export', $this->plugin_slug); echo $alter_name; ?><a href="http://altertech.it" target="_blank"><?php $alter_name=__(' by AlterTech ', $this->plugin_slug); echo $alter_name; ?><img src="<?php echo plugins_url('img/alter-tech-logo.png', __FILE__ ) ;?>" width="50" height="50" class="alter_logo"/></a></span></div>
    <?php } else { ?>  
  <div class="alter-plugin-footer-mobile"></a><span><?php $alter_name=__('DB Tables Import/Export', $this->plugin_slug); echo $alter_name; ?><a href="http://altertech.it" target="_blank"><?php $alter_name=__(' by AlterTech ', $this->plugin_slug); echo $alter_name; ?><img src="<?php echo plugins_url('img/alter-tech-logo.png', __FILE__ ) ;?>" width="50" height="50" class="alter_logo"/></a></span></div>
  <?php } ?>
        </div>
</div>