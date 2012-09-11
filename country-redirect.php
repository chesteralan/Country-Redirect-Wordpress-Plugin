<?php
/*
Plugin Name: Country Redirect
Plugin URI: http://www.chesteralan.com/wordpress/country-redirect
Version: 1.0.0
Author: Chester Alan
Author URI: http://www.chesteralan.com/
Description: Detects Country by matching IP to GeoIP Database and redirect it to the specified URL.
*/

class CACountryRedirect {

	function activated_plugin()
	{
		$active_plugins = get_option('active_plugins');
		$key = array_search(plugin_basename(__FILE__), $active_plugins);
		if($key != 0)
		{
			$newArragement[] = $active_plugins[$key];
			unset($active_plugins[$key]);
			foreach($active_plugins as $plugin) {
				$newArragement[] = $plugin;
			}
			update_option('active_plugins', $newArragement);
		}
	}
	
	function admin_css()
	{
echo <<<CSS
	<style type="text/css">
	<!--
	#addRedirection {
		width:260px;
		padding:20px;
		background:#EEE;
		border:solid 2px #AAA;
		border-radius:10px;
		margin:10px auto;
	}
	#addRedirection label {
		font-size:14px;
		font-weight:bold;
	}
	#addRedirection select, 
	#addRedirection input {
		padding:5px;
		width:260px;
		display:block;
		margin:10px 0;
	}
	#addRedirection input[type="submit"] {
		padding:7px 0;
		font-size:16px!important;
	}
	#addRedirection select {
		padding:5px;
		height:34px;
	}
	#addRedirection h3 {
		text-align:center;
		font-size:18px;
		text-transform:uppercase;
		margin:0;
		padding-bottom:10px;
	}
	#listRedirection table {
		width:100%;
		border:solid 2px #AAA;
		border-radius:10px;
	}
	#listRedirection thead td {
		font-weight:bold;
		border-bottom:solid 1px #CCC;
		background:#DDD;
	}
	#listRedirection td {
		padding:10px;
	}
	#listRedirection tbody td {
		border-bottom:solid 1px #CCC;
	}
	#listRedirection tbody tr:hover {
		background:#EEE;
	}
	.bClose {
		position:absolute;
		right:10px;
		top:10px;
		color:red;
		text-decoration:underline;
		cursor:pointer;
	}
	-->
	</style>
CSS;
	}
	
	function admin_footjs()
	{
		wp_enqueue_script('country-redirect', plugins_url( 'script.js', __FILE__ ), array('jquery'), '1.0.0', true);
	}
	function admin_menu()
	{
		$page = add_submenu_page ( 'options-general.php', 'Country Redirect', 'Country Redirect', 'administrator', __FILE__, array('CACountryRedirect','admin_page') );
		add_action ( 'admin_init', array('CACountryRedirect','admin_init') );
		add_action( 'admin_head', array('CACountryRedirect','admin_css') );
		add_action('admin_print_scripts-' . $page , array('CACountryRedirect','admin_footjs') );
	}

	function admin_page() 
	{
	include_once("geoip.inc");
	
	$redirections = get_option('CACountryRedirections');
	
	if(! is_array ( $redirections ) ) {
		$redirections = array();
	}
	if( isset($_POST['submit']) && ($_POST['submit'] == 'Add') )
	{
		if($_POST['url'] != "") {
			$redirections[$_POST['code']] = $_POST['url'];
			update_option('CACountryRedirections', $redirections);
		}
	} elseif( isset($_POST['submit']) && ($_POST['submit'] == 'Update') )
	{
		if(isset($_POST['delete']) && ($_POST['delete'] != '')) {
			foreach($_POST['delete'] as $id) {
				unset($redirections[$id]);
			}
		}
		update_option('CACountryRedirections', $redirections);
		if(isset($_POST['homepage_only']) && ($_POST['homepage_only'] == 1)) {
			update_option('CACountryRedirections_homepage_only', '1');
		} else {
			update_option('CACountryRedirections_homepage_only', '0');
		}
	}
	
		$geoip = new GeoIP ();
		$countryCodes = $geoip->GEOIP_COUNTRY_CODE_TO_NUMBER;
		$countries = $geoip->GEOIP_COUNTRY_NAMES;
		$countryList = array ();
		foreach ( $countryCodes as $key => $value ) {
			if (!empty($value))
			{
				$countryListAll [$key] = $countries [$value];
				if( ! isset($redirections[$key]) ) {
					$countryList [$key] = $countries [$value];
				}
			}
		}
	array_multisort($countryList);

echo <<<ADMINPAGE
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Country Redirect<a href="javascript:void(0)" class="add-new-h2" id="showAddRedirection">Add Redirection</a></h2>
	<div id="addRedirection">
	<form action="" method="post">
		<h3>Add Redirection</h3>
		<label for="country_code">Country</label>
			<select name="code" id="country_code">
ADMINPAGE;
foreach($countryList as $code=>$name) {
	echo "<option value='{$code}'>{$name}</option>";
}
echo <<<ADMINPAGE
			</select>
		<label for="url">URL</label>
			<input type="text" name="url" value="" class="regular-text" />
			<input type="submit" name="submit" id="submit" class="button-primary" value="Add">
	</form>
	</div>
	<div id="listRedirection">
		<h3>Current Redirections</h3>
		<form action="" method="post">
		<table border="0" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<td width="20">Code</td>
					<td width="150">Country</td>
					<td>URL</td>
					<td align="center" width="54">Delete</td>
				</tr>
			</thead>
ADMINPAGE;
if($redirections) { 

	$homepage_only = (get_option('CACountryRedirections_homepage_only') == 1) ? 'checked="checked"' : '';
echo <<<ADMINPAGE
			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td><input {$homepage_only} type="checkbox" name="homepage_only" value="1" id="homepage_only" /><label for="homepage_only"> Check Homepage Only</label></td>
					<td align="center"><input type="submit" name="submit" id="submit" class="button-primary" value="Update"></td>
				</tr>
			</tfoot>
ADMINPAGE;
}
echo '<tbody>';
if($redirections) foreach($redirections as $key=>$url) {
echo <<<ADMINPAGE
		<tr id='r-{$key}'>
		<td>{$key}</td>
		<td>{$countryListAll[$key]}</td>
		 <td>{$url}</td>
		 <td align="center"><input type="checkbox" name="delete[]" value="{$key}" /></td>
		 </tr>
ADMINPAGE;
}
echo <<<ADMINPAGE
</tbody>
		</table>
		</form>
	</div>
	</div>
ADMINPAGE;
	}

	function admin_init()
	{
		wp_register_script( 'country-redirect', plugins_url( 'script.js', __FILE__ ) );
		register_setting ( 'catcr-settings', 'catcr_redirects' );
	}

	function is_login_page() {
		return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
	}
	
	function check_redirection()
	{
		if ( is_admin() || (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php')) ) ) {
			return false;
		}
		
		$redirections = get_option('CACountryRedirections');
		
		if( empty ($redirections) ) {
			return false;
		}
		
		$homepage_only = get_option('CACountryRedirections_homepage_only');

		if ( $homepage_only == 1 ) 
		{
			if($_SERVER['REQUEST_URI'] != '/') {
				return false;
			}
		} 
		
		$ipv = 4;
		// get ip address
		if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_address = $_SERVER["REMOTE_ADDR"];
		} else {
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		
		// get ip address version
		if(filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === TRUE) {
			$ipv = 4;
		}
		elseif(filter_var($ip_address, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6) === TRUE) {
			$ipv = 6;
		}
		
		include_once("geoip.inc");
		$geodbfile = WP_PLUGIN_DIR . "/" . dirname ( plugin_basename ( __FILE__ ) ) . "/GeoIP.dat";
		$geodb6file = WP_PLUGIN_DIR . "/" . dirname ( plugin_basename ( __FILE__ ) ) . "/GeoIPv6.dat";
		
		$countryCode = NULL;
		switch($ipv) {
			case 4:
				if(file_exists($geodbfile)) {
					$geoip = geoip_open ($geodbfile, GEOIP_STANDARD );
					$countryCode = geoip_country_code_by_addr ( $geoip, $ip_address );
					geoip_close ( $geoip );
				}
				break;
			case 6:
				if(file_exists(geodb6file)) {
					$geoip = geoip_open($geodb6file,GEOIP_STANDARD);
					$countryCode = geoip_country_code_by_addr_v6 ( $geoip, $ip_address );
					geoip_close($geoip);
				}
				break;
		}
		
		if($countryCode != NULL)
		{
			if( isset($redirections[$countryCode]) ) { 
				
		 
				if ( ( $homepage_only == 0 ) && (strpos($redirections[$countryCode], $_SERVER['SERVER_NAME']) !== false) ) {
					return false;
				}

				wp_redirect($redirections[$countryCode]);
				exit;
			}
		}
	}
} //class close

add_action ( "activated_plugin", array("CACountryRedirect", "activated_plugin"));
add_action ( 'init', array("CACountryRedirect", "check_redirection"), 1 );
add_action ( 'admin_menu', array("CACountryRedirect", "admin_menu") );