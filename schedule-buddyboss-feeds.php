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

