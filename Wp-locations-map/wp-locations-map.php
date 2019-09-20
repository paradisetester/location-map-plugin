<?php
	/**
		* Plugin Name: WP Zipcode|Website
		* Plugin URI: paradisetechsoft.com
		* Description: This plugin is created to add different zipcode Entries with it's website and redirect to the user on click on order now button.
		* Version: 1.2
		* Author: Paradise TechSoft Solutions Pvt. Ltd.
		* Author URI: https://www.paradisetechsoft.com/
	**/
	
	
	
	/****************************************************/
	/****Create table at the time of install plugin******/
	/****************************************************/
	
	global $jal_db_version;
	$jal_db_version = '1.1';
	global $wpdb;
	global $table_name;
	global $email_table_name;
	global $pluginname;
	global $apigoogle;
	
	$pluginname = 'Wp Location';
	$table_name = $wpdb->prefix . 'locations';
	$email_table_name = $wpdb->prefix . 'locations_emails';
	$apigoogle = get_option( 'apigoogle' );
	
	if($apigoogle==''){
		$apigoogle = '';
	}
	
	
	
	if (!function_exists('create_location_table')) {
		function create_location_table() {
			global $wpdb;
			global $jal_db_version;
			global $table_name;
			global $email_table_name;
			global $apigoogle;
			
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			zip varchar(20) DEFAULT '',
			city varchar(255) DEFAULT '',
			state varchar(100) DEFAULT '',
			country varchar(50) DEFAULT '',
			lat varchar(20) DEFAULT '',
			lng varchar(20) DEFAULT '',
			latlong text() DEFAULT '',
			company_name varchar(200) DEFAULT '',
			website text(200) DEFAULT '',
			create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		
			PRIMARY KEY  (id)
			) $charset_collate;";
			
			$email_sql = "CREATE TABLE $email_table_name (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			zip varchar(20) DEFAULT '',
			email varchar(200) DEFAULT '',		
			status varchar(2) DEFAULT 1,		
			create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		
			PRIMARY KEY  (id)
			) $charset_collate;";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			dbDelta( $email_sql );
			
			$popupcolor = '#000000';
			$popuptitle = 'Enter your address';			
			add_option( 'wploc_title', $popuptitle,'','yes' ); 
			add_option( 'wploc_color', $popupcolor,'','yes' ); 
			add_option( 'apigoogle', $apigoogle,'','yes' ); 
			
			add_option( 'jal_db_version', $jal_db_version );
		}
		
		function remove_location_table() {
			global $wpdb;
			global $table_name;
			global $jal_db_version;
			$sql = "DROP TABLE IF EXISTS $table_name";
			$wpdb->query($sql);
			delete_option($jal_db_version);
		}
		
		register_activation_hook( __FILE__, 'create_location_table' );
		register_deactivation_hook( __FILE__, 'remove_location_table' );
		
	}
	
	/****************************************************/
	/****Add Popup in footer ********/
	/****************************************************/
	function html_function() {
	
		$html= '';
		global $wpdb;
		$popup_title = get_option( 'wploc_title' );
		$popup_color = get_option( 'wploc_color' );
		if($popup_title==''){
			$popup_title = 'Enter your Address';
		}
		if($popup_color==''){
			$popup_color = '#000000';
		}
		$html .='<div id="light" class="wploc_notification" style="border:1px solid '.$popup_color.'">
		<a href="#" class="popup_loc_cls">X</a>
		
		<div id="location-status"></div>
		
		<div id="email-box" style="display:none;">
			<p class="header-msg">Please notify me when iOpenHouse service is available in this area.</p>
			<form  method="post">
			<div class="inputs_outr">				
				<input  name="popup_email" id="popup_email" class="input-large" placeholder="Email Id" type="email" pattern="[a-zA-Z]{3,}@[a-zA-Z]{3,}[.]{1}[a-zA-Z]{2,}[.]{1}[a-zA-Z]{2,}[.]{1}[a-zA-Z]{2,}" required />
				<input type="text" name="zip" id="zip_code" class="input-large" placeholder="Zip code" required />
			</div>
			<button type="submit" name="add_email" id="add_email" class="button button-loading" style="
			background-color: '.$popup_color.'";>Add</button> 
			</form>
		</div>
		</div>
		<div id="fade" class="black_overlay"></div>';
		
		echo $html;
	}
	add_action('wp_footer', 'html_function');
	
	/****************************************************/
	/****Add admin menu on the wordpress backend ********/
	/****************************************************/
	
	if (!function_exists('wp_locations')) {
		add_action('admin_menu', 'wp_locations');
		
		function wp_locations() {
			add_menu_page('WP Zipcode', 'Zipcode/Website', 'manage_options', 'wp-locations', 'wp_locations_page','dashicons-location');
			
			add_submenu_page('wp-locations', 'Add Zipcode New', 'Add Zipcode New', 'manage_options', 'wplocation_add', 'wplocation_add_page');
			
			add_submenu_page('wp-locations', 'Settings', 'Settings', 'manage_options', 'settings', 'wplocation_setting_page');
			
			add_submenu_page('wp-locations', 'Zipcode Emails', 'Zipcode Emails', 'manage_options', 'wplocation_emails', 'wplocation_emails_page');
			
		}
	}
	


	/****************************/
	/****Create plugin Page******/
	/****************************/
	if (!function_exists('wp_locations_page')) {
		function wp_locations_page() {
			global $wpdb;
			global $jal_db_version;
			global $table_name;
			
			echo '<div class="tablenav top"><div class="sedate-title "><h2>Add Zipcode and Website</h2></div>  </div>
			';
			 '<form class="form-horizontal sf-column-names" action="" method="post" name="add_excel" id="location_enter">
			<div class="form-groups">
			<input type="text" name="zip" id="zip" class="input-large" placeholder="Zip code" data-role="tagsinput" required>
			<span>Used COMMA or ENTER to add multiple zipcode</span>
			</div>
			<div class="form-groups">
			<input type="text" name="website" id="website" class="input-large" placeholder="Website" required>				
			</div>
			<div class="form-groups">
			<input type="text" name="company_name" id="company_name" class="input-large" placeholder="Company Name" required>				
			</div>
			<button type="submit" id="submit" name="addpost" class="button button-loading" data-loading-text="Loading...">Add</button> 
			</form>';
			echo '<a class="button button-loading" href="'.admin_url( 'admin.php?page=wplocation_add' ).'" >Add</a>
			<button id="deletePostcodesTriger" class="button">
			Delete </button>
			<div></div>';
			
			/*******insert zip code start***************/
			if(isset($_POST["addpost"])){
				
				
				$zip=$_POST["zip"];
				$zipcode = (explode(",",$zip));
				$website=$_POST["website"];	
				$company_name=$_POST["company_name"];
				$duplicatZipCode ='';
				foreach($zipcode as $key=>$val){
					$postcodes_get = $wpdb->get_col($wpdb->prepare("SELECT zip  FROM $table_name WHERE zip = %s",$val));
					$postcodes_get_cunt = count($postcodes_get);
					
					if($postcodes_get_cunt){
						$duplicatZipCode .= $val.', ';				
						}else{	
						$format = array('%s','%s');	
						$insertData = array('zip' => $val, 'website' =>$website, 'company_name'=>$company_name, 'create_date'=>date('Y-m-d h:i:s'));
						$query = $wpdb->insert($table_name, $insertData,$format );	
						$message = 'Website added successfully';
						$code = 'updated';
					} 
				}
				
				if($duplicatZipCode){
					$message = $duplicatZipCode.' zip already exist';
					$code = 'error';
				}
				
				
			}
			
			echo '<div class="response">'; 
			if($message){
				echo '<div class="wpaas-notice notice '.$code.'">
				<p><strong>Note: &nbsp;</strong>'.$message.'</strong>.</p>
				</div>';
				
			}
			echo '</div>'; 
			/*******insert zip code end***************/
			
		
			
			/*************location list start***************/
			echo '<div class="table-responsive">
			<table class="wp-list-table widefat fixed striped posts" id="providers-zip" class="display" >
			<thead>
			<tr><th width="6%" class="no-sort"><input type="checkbox" id="del_deletePostcodes"></th>
			<th width="6%">ID</th>
			
			<th>Cities</th>
			<th>Company Name</th>
			<th>Website</th>			
			<th class="manage-column column-date sortable asc">Date</th>
			<th width="8%">Action</th></tr>
			</thead>
			<tbody id="zipcode_listing">';
			
			
			$results = $wpdb->get_results('SELECT * FROM '.$table_name.'  order by id desc');
			
			if ( $results ){  
				$i = 1;
				foreach($results as $result) {        
					echo '<tr id="row-id-'.$result->id.'">
					<td><input type="checkbox" class="deletePostcodesRow" value="'.$result->id.'"></td>
					<td >'.$i.'</td>
					
					<td>'.$result->city.'</td>
					<td>'.$result->company_name.'</td>
					<td>'.$result->website.'</td>			
					<td>'.$result->create_date.'</td>
					<td><a id="'.$result->id.'" class="btn_custom dashicons dashicons-trash" onClick="reply_click(this.id)"></a>  | <a id="'.$result->id.'" class="btn_custom dashicons dashicons-edit" href="'.admin_url( 'admin.php?page=wplocation_add&id=' ).$result->id.'"></a></td>
					</tr>';
					$i++;
				}
				}else{
				_e( 'Sorry, no location found.' );
			}
			echo '</tbody>
			</table></div><div class="wpaas-notice notice updated">
			<p><strong>Note: &nbsp;</strong>To show search zip form, add shortcode in page editor with unique ID: [ordernow id="unique_ID"]</p>
			</div>';	
		}
		
	}
	
	/***********************************************/
	/****SUBMENU FOR EMAIL CAPTURE******/
	/***********************************************/
	
	
	if (!function_exists('wplocation_emails_page')) {
		function wplocation_emails_page() {
			global $email_table_name;
			global $wpdb;
			
			/*************location list start***************/
			echo '<div class="tablenav top"><div class="sedate-title "><h2>Users Email ID with Zipcode</h2></div> </div> <div class="table-responsive">
			<table class="wp-list-table widefat fixed striped posts" id="providers-zip"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
			<thead>
			<tr ><th width="4%" class="no-sort"><input type="checkbox" id="del_deletePostcodes"></th>
			<th width="6%">ID</th>
			
			<th>Cities</th>
			<th>Email ID</th>			
			<th class="manage-column column-date sortable asc">Date</th>
			<th>Action</th></tr>
			</thead>
			<tbody>';
			
			$results = $wpdb->get_results('SELECT * FROM '.$email_table_name.' where status = 1 order by id desc');
			
			
			if ( $results ){  
				$i = 1;
				foreach($results as $result) {        
					echo '<tr id="row-id-'.$result->id.'">
					<td><input type="checkbox" class="deletePostcodesRow" value="'.$result->id.'"></td>
					<td >'.$i.'</td>
					
					<td>'.$result->city.'</td>
					<td>'.$result->email.'</td>						
					<td>'.$result->create_date.'</td>
					<td><a id="'.$result->id.'" class="btn_custom dashicons dashicons-trash" onClick="click_delete_email(this.id)"></a></td>
					</tr>';
					$i++;
				}
				}else{
				_e( 'Sorry, no zip found.' );
			}
			echo '</tbody>
			</table></div>';	
			
			
		}
	}
	


	/***********************************************/
	/****add location on map page*******************/
	/***********************************************/
	
	
	if (!function_exists('wplocation_add_page')) {
		function wplocation_add_page() {
			global $wpdb;
			global $jal_db_version;
			global $pluginname;
			global $table_name;
			/*******insert zip code start***************/
			
			
			echo '<div class="response">'; 			
			if($message){				
				echo '<div class="wpaas-notice notice '.$code.'">
				<p><strong>Note: &nbsp;</strong>'.$message.'</strong>.</p>
				</div>';
				
			}
			echo '</div>'; 
			$title = 'Add';
			if(isset($_REQUEST['id'])){
				$loc = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d",$_REQUEST['id']));
				$address = $loc->city.' '.$loc->state.' '.$loc->country.' '.$loc->zip;
						
				$title = 'Edit';				
				$latlng = json_decode($loc->latlong,true);		
				
			}	
			wp_localize_script( 'location-map', 'latlong', $latlng );		
			?>
			<div>
                    <div class="container-fluid">
					<h3><?=$title?> zipcode and website</h3>
                        <div class="col-lg-6">
							<h4 class="heading-add" style="padding-left:0;">Draw the map pin at your location.</h4>
							<div id="map"></div>
							
                            <!--div id="us5" style="width: 500px; height: 500px;"></div--->
                            <p></p>
                        </div>
                        <div class="col-lg-6">
							<h4 class="heading-add"><?=$title?> Location.</h4>
                            <div class="form container-fluid">
							<form class="form-horizontal sf-column-names" action="" method="post" name="add_excel" id="location_enters">
                                <div class="row form-group">
                                    <div class="col-sm-6">
                                        <input class="form-control" id="us5-street1" type="hidden">
                                        <input class="form-control" id="us5-id" name="id" type="hidden" value="<?=$loc->id?>">
                                        <input class="form-control" id="us5-lat" name="lat" type="hidden" value="<?=($loc->lat)?$loc->lat:'39.515900';?>">
                                        <input class="form-control" id="us5-long" name="lng" type="hidden" value="<?=($loc->lng)?$loc->lng:'-104.788840';?>">
                                        <input class="form-control" id="us5-latlong" name="latlong" type="hidden" value='<?=($loc->latlong)?$loc->latlong:'';?>'>
                                    </div>
                                </div>
								<div class="row form-group">
                                    
                                    <div class="col-sm-6">
                                        <input class="form-control" id="us5-zip"  name="zipcode" type="hidden" value="<?=$loc->zip?>">
                                    </div>
                                </div>                              
								<div class="row form-group">
                                    <label class="col-sm-4 control-label">Company Name:</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" id="us5-company"  name="company_name" type="text" value="<?=$loc->company_name?>" required>
                                    </div>
                                </div>
								<div class="row form-group">
                                    <label class="col-sm-4 control-label">Website URL:</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" id="us5-website"  type="text" name="website" value="<?=$loc->website?>" required>
                                    </div>
									
                                </div>
								<div class="row form-group">
								<input type="submit" id="submit" name="addpost" class="button button-loading add_post_btn add_location" data-loading-text="Loading..." value="<?=$title?>"> 
                                 </div>
									
                                </div>
								
								</form>
								 <div class="responsemsg"></div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                   
            
			<?php
			
			
		}
	}	
	
function getAddress($latitude,$longitude){
	global $apigoogle;
	
    if(!empty($latitude) && !empty($longitude)){
        //Send request and receive json data by address
		 $url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.$apigoogle.'&latlng='.trim($latitude).','.trim($longitude).'&sensor=false';
		 
		 $result = file_get_contents("$url");
	$json = json_decode($result);

	foreach ($json->results as $result) {
		foreach($result->address_components as $addressPart) {
		  if ((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types)))
		  $city = $addressPart->long_name;
	    	else if ((in_array('administrative_area_level_1', $addressPart->types)) && (in_array('political', $addressPart->types)))
		  $state = $addressPart->long_name;
	    	else if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types)))
		  $country = $addressPart->long_name;
		}
	}
	
	$data = array('city'=>$city,'state'=>$state,'country'=>$country);  
		
	return $data;
 
	}
}


		if (!function_exists('add_location_map')) {
		function add_location_map() {
			global $wpdb;
			global $jal_db_version;
			global $pluginname;
			global $table_name;
			 $message = '';
			 $code = 'error';
			if($_POST["website"] !='' && $_POST["company_name"] !='' && $_POST['latlong'] !=''){		 
			$latlong = $_POST['latlong'];	
			$latlongArray = explode(' ',$latlong);
			$city = array();
			$i = 1;			
			foreach($latlongArray as $val){
			$val = str_replace(',39','39',$val);
				if($val){
						$v = explode(',',$val);
						$latlng[] = array('lat'=>(float)$v[0],'lng'=>(float)$v[1]);
						$address = getAddress($v[0],$v[1]);					
						$city[] = $address['city'];
						$state[] = $address['state'];
						$country[] = $address['country'];
						
					}					
					$i++;
				}
			
				$latlngs = json_encode($latlng);

				$cities = implode(',',array_unique($city));
				$states = implode(',',array_unique($state));
				$countries = implode(',',array_unique($country));
					
	
				$zip=$_POST["zipcode"];				
				$website=$_POST["website"];	
				$company_name=$_POST["company_name"];
				
				$format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');	
				
				$insertData = array(
					'zip' => $zip, 
					'website' =>$website, 
					'company_name'=>$company_name, 
					'lat'=>$_POST['lat'], 
					'lng'=>$_POST['lng'], 
					'latlong'=>$latlngs, 
					'city'=>$cities, 								
					'state'=>$states, 
					'country'=>$countries, 
					'create_date'=>date('Y-m-d h:i:s')
				);
						

					if(isset($_POST["addpost"]) && $_POST["addpost"] == 'Add'){
								$query = $wpdb->insert($table_name, $insertData,$format );
								$message = 'location added successfully';
								$code = 'updated';						
					}
					
					if(isset($_POST["addpost"]) && $_POST["addpost"]=='Edit'){	
				
							$id=$_POST["id"];											
							$wpdb->update($table_name, $insertData, array('id'=>$id),$format);
							$message = 'location updated successfully';
							$code = 'updated';
					}			
			
			}else{
					$message = 'Please add website and company name.';
					$code = 'error';
			}
			
			
			$data = array('status'=>$code,'message'=>$message);
			echo json_encode($data); exit;
			
		}
		
		add_action('wp_ajax_add_location_map', 'add_location_map');
		add_action('wp_ajax_nopriv_add_location_map', 'add_location_map');
		
		
	}
	
	
	/***********************************************/
	/****SUBMENU FOR SETTING******/
	/***********************************************/
	
	
	if (!function_exists('wplocation_setting_page')) {
		function wplocation_setting_page() {
			global $wpdb;
			global $jal_db_version;
			global $pluginname;
			/*******insert title code start***************/
			if(isset($_POST["addtitle"])){
				
				$popuptitle=$_POST["wploc_title"];		
				$popupcolor=$_POST["wploc_color"];	
				$apigoogle=$_POST["googleapi_wploc"];			
				
				update_option( 'wploc_title', $popuptitle );
				update_option( 'wploc_color', $popupcolor );
				update_option( 'apigoogle', $apigoogle ); 
				
				$message = 'Updated successfully';
				$code = 'update';
			}
			
			$apigoogle = get_option( 'apigoogle' );	
			$searchtitle = get_option( 'wploc_title' );	
			$searchcolor = get_option( 'wploc_color' );	
			
			
			if($popup_color==''){
				$popup_color='#ff0000';
			}
			echo '<div class="tablenav top"><div class="sedate-title "><h2>'.$pluginname.' Popup Setting</h2></div>  
			<form class="form-horizontal sf-column-names" action="" method="post" name="add_wploc_title">
			
			<p>
			<label>Popup Heading</label>
			<input type="text" name="wploc_title" id="wploc_title" class="input-large" placeholder="Custom Title" value="'.$searchtitle.'"></p>				
			<p>
			<label>Popup Theme Color</label>
			<input type="color" name="wploc_color" id="wploc_color" class="input-large" placeholder="#000000" value="'.$searchcolor.'"></p>
			<p>
			<label>Google API</label>
			<input type="text" name="googleapi_wploc" id="googleapi_wploc" class="input-large" placeholder="Google API" value="'.$apigoogle.'"></p>	
			
			<button type="submit" id="addsubmit" name="addtitle" class="button button-loading" data-loading-text="Loading...">Save</button> 
			</form>
			
			
			
			<div></div>';
			
			
			echo '<div class="response">'; 
			if($message){
				
				echo '<div class="wpaas-notice notice '.$code.'">
				<p><strong>Note: &nbsp;</strong>'.$message.'</strong>.</p>
				</div>';
				
			}
			echo '</div>'; 
			
		}
	}
	
	
	/***********************************************/
	/****DELETE THE WP LOCATION FUNCTION START******/
	/***********************************************/
	
	if (!function_exists('DWPL')) {
		function DWPL()
		{
			global $wpdb;
			global $table_name;
			if($_POST['all'] == 1){
				foreach($_POST['Postcodes'] as $key=>$val){	
					$wpdb->query('DELETE  FROM '.$table_name.' WHERE id = "'.$val.'"');
				}
				}else{
				$id = $_POST['post_id'];
				$wpdb->query('DELETE  FROM '.$table_name.' WHERE id = "'.$id.'"');
			}
			$data = array('status'=>'success','message'=>'Successfully Deleted');
			echo json_encode($data); exit;
		}
		
		add_action('wp_ajax_DWPL', 'DWPL');
		add_action('wp_ajax_nopriv_DWPL', 'DWPL');
		
	}
	
	/***********************************************/
	/****DELETE THE WP LOCATION EMAIL START******/
	/***********************************************/
	
	if (!function_exists('DWPL_Email')) {
		function DWPL_Email()
		{
			global $wpdb;
			global $email_table_name;
			$id = $_POST['post_id'];
			$wpdb->query('DELETE  FROM '.$email_table_name.' WHERE id = "'.$id.'"');
			
			$data = array('status'=>'success','message'=>'Successfully Deleted');
			echo json_encode($data); exit;
		}
		
		add_action('wp_ajax_DWPL_Email', 'DWPL_Email');
		add_action('wp_ajax_nopriv_DWPL_Email', 'DWPL_Email');
		
	}
	
	
	/***********************************************/
	/****ADD JQUERY FILE IN ADMIN BACKEND **********/
	/***********************************************/
	
	if (!function_exists('location_scripts')) {
		
		function location_scripts() {
			global $apigoogle;
			global $jal_db_version;
			wp_enqueue_script( 'tagsinput-script', plugin_dir_url( __FILE__ ) . 'assets/js/bootstrap-tagsinput.min.js', array( 'jquery' ), $jal_db_version, true );
			
			
			wp_enqueue_script( 'googleapi-script',"https://maps.googleapis.com/maps/api/js?key=".$apigoogle."&libraries=places",  array(), null, true);
			
			wp_enqueue_script( 'locationpicker-script', plugin_dir_url( __FILE__ ) . 'assets/js/locationpicker.jquery.js', array( 'jquery' ), $jal_db_version, true );
			
			wp_enqueue_script( 'location-script', plugin_dir_url( __FILE__ ) . 'assets/js/wp-locations.js', array( 'jquery' ), time(), true );
			
			wp_enqueue_script( 'location-map', plugin_dir_url( __FILE__ ) . 'assets/js/wp-map.js', array( 'jquery' ), time(), true );
			
			wp_enqueue_style( 'tagsinput-style', plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap-tagsinput.css' );
			wp_enqueue_style( 'admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css' );
		}
		add_action( 'admin_enqueue_scripts', 'location_scripts' );
	}
	
function add_datatables_scripts() {
wp_register_script('datatables', 'https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js', array('jquery'), true);
wp_enqueue_script('datatables');
  
wp_register_script('datatables_bootstrap', 'https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js', array('jquery'), true);
wp_enqueue_script('datatables_bootstrap');
}
  
function add_datatables_style() {
wp_register_style('bootstrap_style', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
wp_enqueue_style('bootstrap_style');
  
wp_register_style('datatables_style', 'https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css');
wp_enqueue_style('datatables_style');
}
  
add_action('admin_enqueue_scripts', 'add_datatables_scripts');
add_action('admin_enqueue_scripts', 'add_datatables_style');
	
	if (!function_exists('location_style')) {
		
		function location_style() {
			global $apigoogle;
			wp_enqueue_script( 'popup-script', plugin_dir_url( __FILE__ ) . 'assets/js/wplocation_popup.js', array( 'jquery' ), time(), true );
			
			/*  wp_enqueue_script( 'location-map', plugin_dir_url( __FILE__ ) . 'assets/js/wp-map.js', array( 'jquery' ), time(), true ); 
			 */
			
			/* wp_enqueue_script( 'googleapi-script',"https://maps.googleapis.com/maps/api/js?key=".$apigoogle."&libraries=places&callback=initAutocomplete",  array(), null, true); */
		
			wp_localize_script( 'popup-script', 'wplocation_ajax', array( 'ajax_url' => admin_url('admin-ajax.php')) );
			wp_enqueue_style( 'location-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', false,time(),'all');
		}
		add_action( 'wp_enqueue_scripts', 'location_style' );
	}
        /*************************************************/
	/********Shortcode for search form******************/
	/*************************************************/

function form_creation($atts){

 $a = shortcode_atts( array(
        'id' => 'id_1',
    ), $atts );
$popup_title = get_option( 'wploc_title' );
		$popup_color = get_option( 'wploc_color' );
		if($popup_title==''){
			$popup_title = 'Enter your Address';
		}
		if($popup_color==''){
			$popup_color = '#000000';
		}
?>

<div class="address_filed_form" id="address_filed_form_<?=$a['id'];?>">
<div id="location-box" class="location-box">
	<div class="response_message"></div>
			<!--h3--><!--?php echo $popup_title; ?--><!--/h3-->
		<form method="post">
			<div class="inputs_outr">		
			<input type="text" name="address" id="pac-input" class="popup_address" class="input-large" placeholder="<?php echo $popup_title; ?>" required>
			
			<input type="text" name="zip" class="postal_code" class="input-large" placeholder="Zip code" >
			
			<input type="hidden" name="city" class="form_city" value="">
			<input type="hidden" name="state" class="form_state" value="" >
			<input type="hidden" name="country" class="form_country" value="">
			<input type="hidden" name="lat" class="form_lat" value="">
			<input type="hidden" name="lng" class="form_long" value="">
			<input type="hidden" name="action" class="action" value="add_redirect_script">
			</div>
			<button style="background:<?php echo $popup_color; ?>" type="button" name="searchzip" id="searchbtn_popup" class="button button-loading" 
			>Search</button> 
		</form>
		</div>
</div>

<?php
}
add_shortcode('ordernow', 'form_creation');
	
	
	/*************************************************/
	/********GET WEBSITE BY ZIPCODE******************/
	/*************************************************/
	if (!function_exists('getWebsite')) {
		function getWebsite($zipcode){
			
			global $wpdb;
			global $table_name;
			$data = array();
			$city = $_POST['city'];
		 $que = 'SELECT * FROM '.$table_name.' WHERE FIND_IN_SET("'.$city.'", city)';
	
			$postcodes_get = $wpdb->get_results($que);	
		
			$postcodes_get_cunt = count($postcodes_get);
			if($postcodes_get_cunt){
			foreach($postcodes_get as $val){
				$data[] = array(
							'website'=>$val->website,
							'company_name'=>$val->company_name,
							'latlng'=>json_decode($val->latlong,true),
						);
				
				}
						
			}  
			
			return $data;
		}
	}
	
	
	/*******************************************************/
	/********REDIRECT ON CLICK ORDER NOW BUTTON*************/
	/*******************************************************/
	
	if (!function_exists('add_redirect_script')) {
		function add_redirect_script() {
			$zipcode = $_POST['zipcode'];	
			$dataArray = getWebsite($zipcode);
			
			if($dataArray){
				$data = array('status'=>'success','message'=>'This address is serviced by <br/>connecting...','data' =>$dataArray);		
				echo json_encode($data); exit;
				}else{
				$data = array('status'=>'error','message'=>'Coming Soon!','website' =>'empty');
				echo json_encode($data); exit;
			}
			
			
		}
		
		add_action('wp_ajax_add_redirect_script', 'add_redirect_script');
		add_action('wp_ajax_nopriv_add_redirect_script', 'add_redirect_script');
		
		
	}
	
	/*******************************************************/
	/********Add EMAIL ID AND ZIPCODE IN DATABASE***********/
	/*******************************************************/
	
	
	if (!function_exists('add_email_location')) {
		function add_email_location() {
		global $email_table_name;
		global $wpdb;
		
			$zipcode = $_POST['zipcode'];	
			$email = $_POST['email'];	

			$format = array('%s','%s');	
			$insertData = array('zip' => $zipcode, 'email' =>$email,'status' =>1, 'create_date'=>date('Y-m-d h:i:s'));
			$query = $wpdb->insert($email_table_name, $insertData,$format );	
			$lastid = $wpdb->insert_id;
			
					//notify admin by email
		$to = get_option('admin_email');
		$subject = 'New Request received for service at zipcode '.$zipcode.' area.';
		$body =  'Dear Admin, <br><br>New Request received for service at zipcode '.$zipcode.' area. <br>User details : '.$email.'<br> Zipcode : '.$zipcode;
			
		$headers = array('Content-Type: text/html; charset=UTF-8','From: iopenhouse <noreply@iopenhouse.com>');
		wp_mail( $to, $subject, $body, $headers );
			
			if($lastid){
				$data = array('status'=>'success','message'=>'Email ID submit successfully, we will notify you soon.');		
				echo json_encode($data); exit;
				}else{
				$data = array('status'=>'error','message'=>'Somthing went wrong please try again.');
				echo json_encode($data); exit;
			}
			
			
		}
		
		add_action('wp_ajax_add_email_location', 'add_email_location');
		add_action('wp_ajax_nopriv_add_email_location', 'add_email_location');
		

	}	

	/***********************************************/
	/****THE WP Location search START******/
	/***********************************************/
	
	if (!function_exists('WL_Searchzip')) {
		function WL_Searchzip()
		{
			global $wpdb;
			global $table_name;
			$val = $_POST['searchval'];
			
			$results = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE zip ='.$val);
			$html = '';
			if ( $results ){
			$status = "success";  
				$i = 1;
				foreach($results as $result) {        
					$html .='<tr id="row-id-'.$result->id.'">
					<td><input type="checkbox" class="deletePostcodesRow" value="'.$result->id.'"></td>
					<td >'.$i.'</td>
					
					<td>'.$result->zip.'</td>
					<td>'.$result->company_name.'</td>
					<td>'.$result->website.'</td>			
					<td>'.$result->create_date.'</td>
					<td><a id="'.$result->id.'" class="btn_custom dashicons dashicons-trash" onClick="reply_click(this.id)"></a> | <a id="'.$result->id.'" class="btn_custom dashicons dashicons-edit" onClick="reply_click(this.id)"></a></td>
					</tr>';
					$i++;
				}
				}else{
				$html .= 'Sorry, no location found.';
				$status = "error"; 
			}
			$data = array('status'=>$status,'message'=>$html);
			echo json_encode($data); exit;
		}
		
		add_action('wp_ajax_WL_Searchzip', 'WL_Searchzip');
		add_action('wp_ajax_nopriv_WL_Searchzip', 'WL_Searchzip');
		
	}
	