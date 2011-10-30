<?php
/*
Plugin Name: MS ADS
Plugin URI: http://www.php-press.com/
Version: 1.4
Description: Persian Advertisement Plugin
Author: Moeini
Author URI: http://www.php-press.com/
*/
load_muplugin_textdomain( 'ms-ads', 'ms-ads-languages' );
if ( ! function_exists( 'is_ssl' ) ) {
	function is_ssl() {
		   if ( isset($_SERVER['HTTPS']) ) {
			if ( 'on' == strtolower($_SERVER['HTTPS']) )
			 return true;
			if ( '1' == $_SERVER['HTTPS'] )
			 return true;
		   } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		   }
		   return false;
	}
}
function ms_ads_content_url(){
	static $msads_content_url;
	if(!isset($msads_content_url)) {
		 if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
		  $msads_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
		 } else {
		  $msads_content_url = get_option( 'siteurl' );
		 }
		 $msads_content_url .= '/wp-content/mu-plugins';
	}
	return $msads_content_url;
}
$msads_menu_seting = version_compare( $wp_version, '3.1', '>=' ) ? array('network_admin_menu','settings') : array('admin_menu','ms-admin');
function ms_ads_option() {
	$default_options = array(
	'banner'=>array(array('src' =>'','href' =>'','display' =>'','description'=>'','title'=>'')),
	'theme'=>'default-left',
	'no_ads'=>'1',
	'location' => 'header'
	);
	return get_option('ads_option',$default_options);
}
function select_ads($banner){
	foreach((array) $banner as $num=>$array)
		 $Weight[$num] = $array['display'];
    $sum =0;
    for($i=0;$i<count($Weight);$i++)
    	$sum+=$Weight[$i];
    $ShowAd = rand(0, $sum - 1);
    for($i=0;$i<count($Weight);$i++)
    {
    	if($ShowAd<=$Weight[$i])
    	{
    		$ShowAd=$i;
    		break;
    	}
    	else
    		$ShowAd-=$Weight[$i];
    }
    return $ShowAd;
}
function ms_get_ads(){
	global $wpdb;
	$options = ms_ads_option();
	$blogs = explode(' ',$options['no_ads']);
	if(!in_array($wpdb->blogid,$blogs)) {
		$id = mt_rand();
		$banner_id = select_ads($options['banner']);
		$theme = ABSPATH.'wp-content/mu-plugins/ms-ads-themes/'.$options['theme'].'/main.html';
		if(!file_exists($theme))
			return ;
		$main = fopen($theme, 'r');		$main = fread($main, filesize($theme));
		$main = str_replace(array('{id}','{link}','{banner}','{description}','{title}'),array($id,$options['banner'][$banner_id]['href'],$options['banner'][$banner_id]['src'],$options['banner'][$banner_id]['description'],$options['banner'][$banner_id]['title']),$main);
		return $main;
	}
}
function show_ads_above_content($posts) {
	static $check_first;
	if(!isset($check_first)) {
		echo ms_get_ads();
		$check_first = true;
	}
	return $posts;
}
function show_ms_get_ads() {
	echo ms_get_ads();
}
function ms_show_ads() {
$options = ms_ads_option();
	if(strtolower($options['location']) == 'content') {
		add_action('the_post','show_ads_above_content');
	} else {
	 add_action('wp_head','show_ms_get_ads');
	}
}
function ads_setting(){
	global $msads_menu_seting;
	add_submenu_page($msads_menu_seting[1].'.php', __('Advertisement','ms-ads'),__('Advertisement','ms-ads'),7, 'ads-setting','ads_config');
}
function ads_config(){
	global $wpdb,$blogs;
	if(is_super_admin() && isset($_GET['page']) && 'ads-setting' == $_GET['page']){
		if(isset($_POST['submit'])){
			function _array_esc_sql($value){
				return esc_sql($value);
			}
			$options['banner'] = array();
			foreach((array)$_POST['banner'] as $id=>$values)
				$options['banner'][] = array_map('_array_esc_sql',((array)$values));
			$options['no_ads'] = esc_sql($_POST['no_ads']);
			$options['theme'] = esc_sql($_POST['ms-ads-theme']);
			$options['location'] = esc_sql($_POST['location']);
			//Update Processes
				echo '<div class="updated below-h2" id="message"><p>';
			if(update_option('ads_option',$options)) 
				_e('Options updated','ms-ads');
			else
				echo '<span style="color:red;">'.__('Error updating options','ms-ads').'</span>';
			echo '</p></div>';
			//Update Message
		}
	$options = ms_ads_option();
	$total = count($options['banner']);
?>
<script type="text/javascript">
//<![CDATA[
var index_amf_total  = <?php echo intval($total); ?>;
var _Banner_URL  = "<?php _e('Banner URL:','ms-ads'); ?>";
var Banner_Link  = "<?php _e('Banner Link:','ms-ads'); ?>";
var Number_of_views  = "<?php _e('Number of views:','ms-ads'); ?>";
var _Percent  = "<?php _e('Percent','ms-ads'); ?>";
var Banner_title  = "<?php _e('Banner title:','ms-ads'); ?>";
var Banner_Description  = "<?php _e('Banner Description:','ms-ads'); ?>";
var _Remove  = "<?php _e('Remove','ms-ads'); ?>";
//]]>
</script>
<script type="text/javascript" src="<?php echo ms_ads_content_url().'/options.js' ?>"></script>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2 style="font-family:Tahoma;font-size:20px"><?php _e('Advertisement Options','ms-ads') ?></h2>
	<div class="narrow">
		<form action="" method="post" style="margin: auto; width: 645px; ">
			<p id="banners">
			 <label for="banners"><p><h5><?php _e('Banners management','ms-ads') ?></h5></p>
			<div id="banner-0"><?php _e('Banner URL:','ms-ads') ?><input name="banner[0][src]" value="<?php echo isset($options['banner'][0]['src']) ? esc_attr($options['banner'][0]['src']) : ''; ?>" size="76" type="text" /> <br /> <?php _e('Banner Link:','ms-ads') ?> &nbsp;&nbsp;<input name="banner[0][href]" value="<?php echo isset($options['banner'][0]['href']) ? esc_attr($options['banner'][0]['href']) : ''; ?>" size="50" type="text" /> <?php _e('Number of views:','ms-ads') ?><input name="banner[0][display]" value="<?php echo isset($options['banner'][0]['display']) ? esc_attr($options['banner'][0]['display']) : ''; ?>" size="4" type="text" /> <?php _e('Percent','ms-ads') ?><br /> <?php _e('Banner title:','ms-ads') ?> &nbsp;&nbsp;<input name="banner[0][title]" value="<?php echo isset($options['banner'][0]['title']) ? esc_attr($options['banner'][0]['title']) : ''; ?>" size="50" type="text" /><br /> <?php _e('Banner Description:','ms-ads') ?> &nbsp;&nbsp;<input name="banner[0][description]" value="<?php echo isset($options['banner'][0]['description']) ? esc_attr($options['banner'][0]['description']) : ''; ?>" size="50" type="text" />
			</div><p />

			<span id="more_banner_inputs">
<?php
		for($i=1;$i<$total;$i++){
?>
			<div id="banner-<?php echo $i; ?>"><?php _e('Banner URL:','ms-ads') ?><input name="banner[<?php echo $i; ?>][src]" value="<?php echo isset($options['banner'][$i]['src']) ? esc_attr($options['banner'][$i]['src']) : ''; ?>" size="76" type="text" /> <br /> <?php _e('Banner Link:','ms-ads') ?> &nbsp;&nbsp;<input name="banner[<?php echo $i; ?>][href]" value="<?php echo isset($options['banner'][$i]['href']) ? esc_attr($options['banner'][$i]['href']) : ''; ?>" size="50" type="text" /> <?php _e('Number of views:','ms-ads') ?><input name="banner[<?php echo $i; ?>][display]" value="<?php echo isset($options['banner'][$i]['display']) ? esc_attr($options['banner'][$i]['display']) : ''; ?>" size="4" type="text" /> <?php _e('Percent','ms-ads') ?> <br /> <?php _e('Banner title:','ms-ads') ?> &nbsp;&nbsp;<input name="banner[<?php echo $i; ?>][title]" value="<?php echo isset($options['banner'][$i]['title']) ? esc_attr($options['banner'][$i]['title']) : ''; ?>" size="50" type="text" /><br /> <?php _e('Banner Description:','ms-ads') ?> &nbsp;&nbsp;<input name="banner[<?php echo $i; ?>][description]" value="<?php echo isset($options['banner'][$i]['description']) ? esc_attr($options['banner'][$i]['description']) : ''; ?>" size="50" type="text" />
			</div><p />
<?php } ?>
					</span> <p />
					<input onclick="javascript:new_banner_input();" value="<?php _e('Add more banner','ms-ads') ?>" type="button" /> 
				</label>
			</p>
			<label for="no_ads"><p><h5><?php _e('Remove blogs advertising','ms-ads') ?></h5></p>
				<p>
				<textarea id="no_ads" name="no_ads" cols="68" rows="3" ><?php echo esc_textarea($options['no_ads']); ?></textarea>
				<br>ùù<?php _e('Enter Blog ID and separated with (Space), like: 1 2 8','ms-ads') ?></br>
				</p>
			</label>
			<label for="theme"><p><h5><?php _e('Theme','ms-ads') ?></h5></p>
				<select id="theme" name="ms-ads-theme" style="width:120px;">
<?php
	$dir = ABSPATH.'wp-content/mu-plugins/ms-ads-themes';
	if(is_dir($dir)) {
		if ($handle = opendir($dir)) {
			while (false !== ($theme = readdir($handle))) {
				if (in_array($theme,array('.','..')))
					continue;
				$select = $options['theme'] == $theme ?  ' selected="selected"' : '';
				printf('<option value="%s"%s> %1$s </option>\n',$theme,$select);
			}
			closedir($handle);
		}
	}
?>
				</select>
			</label>
			<label for="location"><p><h5><?php _e('Show location:','ms-ads') ?></h5></p>
				<select id="location" name="location" style="width:120px;">
<?php	$location = strtolower($options['location']); ?>
					<option value="header"<?php if($location == 'header') echo ' selected="selected"'; ?>> <?php _e('Header') ?> </option>
					<option value="content"<?php if($location == 'content') echo ' selected="selected"'; ?>> <?php _e('Above content','ms-ads') ?> </option>
				</select>
			</label>
				<p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
		</form>
	</div>
</div>
<?php
}
}
add_action($msads_menu_seting[0], 'ads_setting');
add_action('init','ms_show_ads');
?>