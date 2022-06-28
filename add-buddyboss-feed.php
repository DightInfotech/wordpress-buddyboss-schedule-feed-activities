<div class="buddycontainer">
	<?php
		global $wpdb;
		$pluginURl = plugins_url();
		$siteurl = get_site_url();
		$current_user = wp_get_current_user();
		$groups = groups_get_groups($args = '');	
		$uploadedImg = '';	
		$upload_dir = dight_feed_schedule_dir.'upload/';
		if(isset($_POST['submit'])){
			$upload_dir = dight_feed_schedule_dir.'upload/';
			$allowed_types = array('jpg', 'png', 'jpeg', 'gif');	     
			$maxsize = 20 * 1024 * 1024;
			if(!empty(array_filter($_FILES['files']['name']))) {	 				
				foreach ($_FILES['files']['tmp_name'] as $key => $value) {	             
					$file_tmpname = sanitize_text_field($_FILES['files']['tmp_name'][$key]);
					$file_name = sanitize_text_field($_FILES['files']['name'][$key]);
					$file_size = sanitize_text_field($_FILES['files']['size'][$key]);
					$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
					$filepath = $upload_dir.$file_name;	 
					if(in_array(strtolower($file_ext), $allowed_types)) {	 								
						if ($file_size > $maxsize)        
							echo "Error: File size is larger than the allowed limit.";
		 
						
						if(file_exists($filepath)) {
							$filepath = $upload_dir.time().$file_name;
							 
							if( move_uploaded_file($file_tmpname, $filepath)) {
								echo "{$file_name} successfully uploaded <br />";
							}
							else {                    
								echo "Error uploading {$file_name} <br />";
							}
						}
						else {
						 
							if( move_uploaded_file($file_tmpname, $filepath)) {
								echo "{$file_name} successfully uploaded <br />";
							}
							else {                    
								echo "Error uploading {$file_name} <br />";
							}
						}
					}
					else {
						 
					}
				}
				$uploadedImg = '<img src="'.$pluginURl.'/schedule-buddyboss-feeds/upload/'.$file_name.'">';
			}
			$groupdetail = sanitize_text_field($_POST['groupslist']);
			$feed_title = sanitize_text_field($_POST['feed_title']);
			$content = $_POST['content'];
			$privacy = '';
			$explodegroup = explode('/',$groupdetail);			
			if(isset($_POST['publish_date']) && $_POST['publish_date'] != ''){
				$published_date = $_POST['publish_date'];
				$publish_date = strtotime($_POST['publish_date']);
				$current = strtotime(date('Y-m-d h:i:s'));
				if($current < $publish_date){
					$privacy = 'private';
				}else{
					$privacy = 'public';
				}
			}else{
				$published_date = date('Y-m-d h:i:s');
				$privacy = 'public';
			}
		
			$table = $wpdb->prefix . "bp_activity";
			$wpdb->insert($table, array(
				'user_id' => $current_user->ID,
				'component' => 'groups',
				'type' => 'activity_update',			    
				'action' => '<a href="'.$siteurl.'/voisines/'.$current_user->data->display_name.'/">'.$current_user->data->display_name.'</a> posted an update in the group <a href="'.$siteurl.'/groupes/'.$explodegroup[1].'/">'.$explodegroup[2].'</a>',
				'content' => $content.$uploadedImg,
				'primary_link' => $siteurl.'/voisines/'.$current_user->data->display_name.'/',
				'item_id' => $explodegroup[0],
				'secondary_item_id' => 0,
				'date_recorded' => $published_date,
				'hide_sitewide' => 0,
				'mptt_left' => 0,
				'mptt_right' => 0,
				'is_spam' => 0,
				'privacy' => $privacy,
			));
			$activity_scheduled = $wpdb->prefix . 'activity_scheduled';
			$wpdb->insert($activity_scheduled, array(
				'activity_id' => $wpdb->insert_id
			));       
			header('Location: ?page=dight-create-schedule-feed');
		}
	?>
	<form method="post" action="" enctype="multipart/form-data">
		<div class="buddyfields">
			<label><?php echo esc_html( __( 'Select a group of feed', 'text_domain' ) );?></label>
			<select class="groupslist input-box" name="groupslist">
				<option value=""></option>
				<?php 
					foreach ($groups['groups'] as $value) {
						echo '<option value="'.$value->id.'/'.$value->slug.'/'.$value->name.'">'.esc_html( __( $value->name, 'text_domain' ) ).'</option>';
					}
				?>
			</select>
		</div>
		<div class="buddyfields">
			<label><?php echo esc_html( __( 'Schedule of feed date', 'text_domain' ) );?></label>
			<div id='postpublishcontainer'>
				<input class="datepicker input-box" type='text' name="publish_date" id='datetimepicker' size='30' autocomplete="off">
			</div>
		</div>
		<div class="buddyfields">
			<label><?php echo esc_html( __( 'Content of feed', 'text_domain' ) );?></label>
			<textarea class="buddytextares input-box" id="buddytextares" name="content" rows="4" cols="50"></textarea>
		</div>
		<div class="buddyfields">
      <div >
          <input type="file" name="files[]" multiple="multiple" />
      </div>
    </div>
		<input class="buddysubmit" type="submit" name="submit" value="Submit">
	</form>		
</div>