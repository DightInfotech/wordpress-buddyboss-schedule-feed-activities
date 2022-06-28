<div class="buddycontainer">
	<?php
		global $wpdb;
		$pluginURl = plugins_url();
		$siteurl = get_site_url();
		$current_user = wp_get_current_user();
		$groups = groups_get_groups($args = '');	
		$uploadedImg = '';	
		$table = $wpdb->prefix . "bp_activity";
		$activity_scheduled = $wpdb->prefix . 'activity_scheduled';
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
			
    		$wpdb->update( $table, 
    			array( 
    				'user_id' => $current_user->ID,
				    'content' => $content.$uploadedImg,
				    'item_id' => $explodegroup[0],
				    'date_recorded' => $published_date,
				    'privacy' => $privacy
    			),array('id'=>$_GET['id']));   
			header('Location: ?page=dight-create-schedule-feed');
		}
		$sql = "SELECT * FROM ".$table." WHERE id = ".$_GET['id'];
        $results = $wpdb->get_results($sql);
        $item_id = '';
        $date_recorded = '';
        $content = '';
        if(!empty($results)){
        	$item_id        = $results[0]->item_id;
        	$date_recorded  = $results[0]->date_recorded;
        	$content  = $results[0]->content;
	    }
	?>
	<form method="post" action="" enctype="multipart/form-data">
		<div class="buddyfields">
			<label><?php echo esc_html( __( 'Select a group of feed', 'text_domain' ) );?></label>
			<select class="groupslist input-box" name="groupslist">
				<option value=""></option>
				<?php 
					foreach ($groups['groups'] as $value) {
						$selected = '';
						if($item_id == $value->id){
							$selected = 'selected="selected"';
						}
						echo '<option value="'.$value->id.'/'.$value->slug.'/'.$value->name.'" '.$selected.'>'.esc_html( __( $value->name, 'text_domain' ) ).'</option>';
					}
				?>
			</select>
		</div>
		<div class="buddyfields">
			<label><?php echo esc_html( __( 'Schedule of feed date', 'text_domain' ) );?></label>
			<div id='postpublishcontainer'>
				<input class="datepicker input-box" type='text' name="publish_date" id='datetimepicker' value="<?php echo esc_html( __( $date_recorded, 'text_domain' ) );?>" size='30' autocomplete="off">
			</div>
		</div>
		<div class="buddyfields">
			<label><?php echo esc_html( __( 'Content of feed', 'text_domain' ) );?></label>
			<textarea class="buddytextares input-box" id="buddytextares" name="content" rows="4" cols="50"><?php echo $content;?></textarea>
		</div>
		<div class="buddyfields">
      <div >
          <input type="file" name="files[]" multiple="multiple" />
      </div>
    </div>
		<input class="buddysubmit" type="submit" name="submit" value="Submit">
	</form>

</div>