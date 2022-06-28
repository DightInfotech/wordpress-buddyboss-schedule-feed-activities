<?php
/*
Plugin Name:  Schedule Buddyboss Feeds
Plugin URI:   http://dightinfotech.com/
Description:  This plugin helps us to create,edit,delete activity feeds from admin. 
Version:      1.0.0
Author:       Dight Infotech - Surjeet, Sandeep
Author URI:   http://dightinfotech.com/
License:      
License URI:  
Text Domain:  
Domain Path:  
*/

define( 'dight_feed_schedule_path', plugins_url(__FILE__ ) ); 
define( 'dight_feed_schedule_dir', plugin_dir_path(__FILE__ ) ); 
//include( dight_feed_schedule_dir . 'function.php');
/*============== Create main menu and submenus ==============*/
add_action( 'admin_menu', 'dight_create_schedule_feed_info_menu' ); 
 if( !function_exists("dight_create_schedule_feed_info_menu") ) { 
    function dight_create_schedule_feed_info_menu(){  
        $page_title = 'BuddyBoss Schedule Feeds';   
        $menu_title = 'BuddyBoss Schedule Feeds';   
        $capability = 'manage_options';   
        $menu_slug  = 'dight-create-schedule-feed';   
        $function   = 'dight_create_schedule_feed';  
        $icon_url   = 'dashicons-media-code';   
        $position   = 4;    
        add_menu_page( $page_title,$menu_title,$capability,$menu_slug,$function,$icon_url,$position ); 
        add_submenu_page($menu_slug, "Add Feeds", "Add Feeds", 0, "dight-feed-submenu-slug", "dightfeedSubmenuPageFunction");
        add_submenu_page('', "Edit Feeds", "Edit Feeds", 0, "dight-edit-activity-feed", "dight_edit_feed");
        add_action( 'admin_init', 'dight_update_buddyboss_create_feed_info' );  
    }
}  
/*============== end ==============*/


/*============== Edit an activity ==============*/
function dight_edit_feed(){
    include( dight_feed_schedule_dir . 'edit_activity.php');
}
/*============== end ==============*/


/*====== Activation hook to create adition data table  =====*/
register_activation_hook(__FILE__, 'dight_info_activityhook');
function dight_info_activityhook(){ 
    global $wpdb; 
    $db_table_name = $wpdb->prefix . 'activity_scheduled'; 
    $charset_collate = $wpdb->get_charset_collate();
    if($wpdb->get_var( "show tables like '$db_table_name'" ) != $db_table_name ) 
    {
        $sql = "CREATE TABLE $db_table_name (
                id int(11) NOT NULL auto_increment,
                `activity_id` int(11) DEFAULT NULL,
                UNIQUE KEY id (id)
        ) $charset_collate;";
           require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
           dbDelta( $sql );
           add_option( 'test_db_version', $test_db_version );
    }
}
/*====== end  =====*/

/*====== To add JS and CSS files =====*/
function dightbuddybossfeedAssets() {
    wp_register_style('buddybossfeeddatepickercss', plugins_url('assets/css/jquery.datetimepicker.min.css',__FILE__ ));
    wp_enqueue_style('buddybossfeeddatepickercss');
    wp_register_style('buddybossfeedcss', plugins_url('assets/css/buddyfeed.css',__FILE__ ));
    wp_enqueue_style('buddybossfeedcss');
    wp_register_style('jquerydataTables', plugins_url('assets/css/jquery.dataTables.min.css',__FILE__ ));
    wp_enqueue_style('jquerydataTables');
	wp_register_style('bootstrapmin', plugins_url('assets/css/bootstrap.min.css',__FILE__ ));
    wp_enqueue_style('bootstrapmin');
	
    wp_register_script( 'buddybossfeeddatepickerjs', plugins_url('assets/js/jquery.datetimepicker.js',__FILE__ ));
    wp_enqueue_script('buddybossfeeddatepickerjs');
    wp_register_script( 'jquerydataTablesjs', plugins_url('assets/js/jquery.dataTables.min.js',__FILE__ ));
    wp_enqueue_script('jquerydataTablesjs');	
	wp_register_script( 'tinymcemin', plugins_url('assets/js/tinymce.min.js',__FILE__ ));
    wp_enqueue_script('tinymcemin');
	wp_register_script( 'bootstrapmin', plugins_url('assets/js/bootstrap.min.js',__FILE__ ));
    wp_enqueue_script('bootstrapmin');	
    wp_register_script( 
        'ajaxHandle', 
        plugins_url('assets/js/customfeeds.js', __FILE__), 
        array(), 
        false, 
        true 
    );
    wp_enqueue_script( 'ajaxHandle' );
    wp_localize_script( 
        'ajaxHandle', 
        'ajax_object', 
        array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) 
    );

}
add_action( 'admin_init','dightbuddybossfeedAssets');
/*====== end add JS and CSS files =====*/

/*====== To delete an activity feed  =====*/
add_action( "wp_ajax_dightdeleteactivityfeed", "dightdeleteactivityfeed" );
add_action( "wp_ajax_nopriv_dightdeleteactivityfeed", "dightdeleteactivityfeed" );
function dightdeleteactivityfeed(){
    global $wpdb;     
    $table = $wpdb->prefix . "bp_activity";
    $feedid = $_POST['feedid'];
    $wpdb->delete( $table, array( 'id' => $feedid ) );
    
}
/*====== End delete an activity feed  =====*/

/*====== Cron for schedule activity feed  =====*/
add_action('init', 'dightschedulecronrun');
function dightschedulecronrun(){
	global $wpdb;
	$table = $wpdb->prefix . "bp_activity";
	$records =  $wpdb->get_results( 'SELECT * FROM '.$table.' WHERE secondary_item_id = NULL OR secondary_item_id = 0 AND privacy = "private"');
	$dt = new DateTime("now", new DateTimeZone('Europe/Paris'));

	$current = strtotime($dt->format('Y-m-d H:i:s'));
	foreach ($records as $record) {		
		$publish_date = strtotime($dt->format($record->date_recorded));
		if($current >= $publish_date){
			$wpdb->update($table,array( 'privacy' =>'public'), array( 'id' => $record->id), array( '%s'), array( '%d','%d' ) );
		}
	}
}
/*====== End Cron for schedule activity feed  =====*/

/*====== To add new activity feed  =====*/
function dightfeedSubmenuPageFunction(){
    include( dight_feed_schedule_dir . 'add-buddyboss-feed.php');
}
/*====== end  =====*/
if( !function_exists("dight_update_buddyboss_create_feed_info") ) { 
    function dight_update_buddyboss_create_feed_info() {   
        register_setting( 'dight_buddyboss-create-feed-info-settings', 'dight_schedule_post_info_feed' ); 
    } 
} 
/*====== List of activity feed  =====*/ 
if( !function_exists("dight_create_schedule_feed") ) { 
    function dight_create_schedule_feed(){ 
    global $wpdb;     
        $groups = groups_get_groups($args = '');   
        
        $groupDetail = [];
        foreach ($groups['groups'] as $value) {
            $groupDetail[$value->id] = $value->name;
        }
        
        $table = $wpdb->prefix . "bp_activity";
        $activity_scheduled = $wpdb->prefix . 'activity_scheduled';
        $records =  $wpdb->get_results( 'SELECT '.$table.'.*, '.$activity_scheduled.'.activity_id FROM '.$table.' INNER JOIN  '.$activity_scheduled.' ON '.$table.'.id = '.$activity_scheduled.'.activity_id ORDER BY '.$table.'.date_recorded DESC');
		
        ?>  

        <h1 class="addnewfeed"><a href="?page=dight-feed-submenu-slug"><?php echo esc_html( __( 'Add New Feed', 'text_domain' ) );?></a></h1>  

        <h1 class="addnewfeed"><?php echo esc_html( __( 'List of Activity Feeds', 'text_domain' ) );?></h1>
        <table id="activityfeedlist" class="display" style="width:98%">
            <thead>
                <tr>
                    <th><?php echo esc_html( __( 'SNo.', 'text_domain' ) );?></th>                    
                    <th><?php echo esc_html( __( 'Group Name', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Type', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Content', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Status', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Created', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Action', 'text_domain' ) );?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sno = 1;
                if(!empty($records)){
                    foreach($records as $rows){
                ?>
                    <tr>
                        <td><?php echo $sno;?></td>                        
                        <td>
                            <?php 
                                if (array_key_exists($rows->item_id, $groupDetail)) {
                                    echo esc_html( __( $groupDetail[$rows->item_id], 'text_domain' ) );
                                }
                            ?>                            
                        </td>
                        <td><?php echo esc_html( __( $rows->type, 'text_domain' ) );?></td>
                        <td><?php echo implode(' ', array_slice(explode(' ', $rows->content), 0, 5));?></td>
                        <td>
                            <?php 
                                if($rows->privacy == 'public'){
                                    echo esc_html( __( 'Posted', 'text_domain' ) );
                                }else{
                                    echo esc_html( __( 'Scheluded', 'text_domain' ) );
                                }
                            ?>
                        </td>
                        <td><?php echo esc_html( __( $rows->date_recorded, 'text_domain' ) );?></td>
                        <td><a href="?page=dight-edit-activity-feed&id=<?php echo esc_html( __( $rows->id, 'text_domain' ) );?>"><?php echo esc_html( __( 'Edit', 'text_domain' ) );?></a> | <a href="" class="deleteactivityfeed" rel="<?php echo $rows->id;?>"><?php echo esc_html( __( 'Delete', 'text_domain' ) );?></a></td>
                    </tr>  
                <?php
                        $sno++;
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th><?php echo esc_html( __( 'SNo.', 'text_domain' ) );?></th>                    
                    <th><?php echo esc_html( __( 'Group Name', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Type', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Content', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Status', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Created', 'text_domain' ) );?></th>
                    <th><?php echo esc_html( __( 'Action', 'text_domain' ) );?></th>
                </tr>
            </tfoot>
        </table>        
       <script type="text/javascript">
           jQuery(document).ready(function() {
                jQuery('#activityfeedlist').DataTable( {
                    "pagingType": "full_numbers"
                } );
            } );
       </script>
    <?php } 
} 
/*====== End  =====*/ 

/*====== To add plugin option data  =====*/ 
if( !function_exists("dight_schedule_post_info_feed") ) {   
    function dight_schedule_post_info_feed($content)   {     
        $extra_info = get_option('dight_schedule_post_info_feed');     
        return $content . $extra_info;   
    } 
} 
/*====== End  =====*/  
add_filter('the_content', 'dight_schedule_post_info_feed');