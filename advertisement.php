<?php
/*
Plugin Name: تبلیغات وبلاگ
Plugin URI: http://www.php-press.com/
Version: 1.0.1
Description: Persian Advertisement Plugin
Author: Moeini
Author URI: http://www.php-press.com/
*/

// define('_TABLE_PREFIX','');
	
$pos = strrpos(dirname(__FILE__),DIRECTORY_SEPARATOR);
define('MS_ADS_FOLDER',substr(dirname(__FILE__),$pos+1));

function ms_ads_get_op(){
	global $table_prefix;
	if(!defined('_TABLE_PREFIX')){
		$pos = strpos($table_prefix,'_');
		return substr($table_prefix,0,$pos+1);
	} else
		return _TABLE_PREFIX;
}
function ms_ads_option() {
	global $wpdb;
	$default_options = array(
	'banner'=>Array(Array('src' =>'','href' =>'','display' =>'')),
	'theme'=>'default-left',
	'no_ads'=>'1');
	$msads_option = ms_ads_get_op().'options';
	if($value = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $msads_option WHERE option_name = 'ads_option' LIMIT 1" ) )){
		if ( is_object( $value ) ) 
			$value = $value->option_value;
		return (maybe_unserialize($value));
	} else {
		$wpdb->options = $msads_option;
		add_option('ads_option',$default_options);
		return ms_ads_option();
	}
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
function show_ads(){
	global $wpdb;
	$options = ms_ads_option();
	$blogs = explode(' ',$options['no_ads']);
	if(!in_array($wpdb->blogid,$blogs))
	{
		$id = mt_rand();
		$banner_id = select_ads($options['banner']);
		$theme = ABSPATH.'wp-content/plugins/'.MS_ADS_FOLDER.'/themes/'.$options['theme'].'/main.html';
		$main = fopen($theme, 'r');		$main = fread($main, filesize($theme));
		$main = str_replace(array('{id}','{link}','{banner}'),array($id,$options['banner'][$banner_id]['href'],$options['banner'][$banner_id]['src']),$main);
		echo $main;
	}
}
function ads_setting(){
	add_submenu_page('ms-admin.php', 'تبلیغات ','‌تبلیغات ‌',7, 'ads-setting','ads_config');
}
function ads_config(){
	global $wpdb,$blogs;
	if(is_admin() && isset($_GET['page']) && 'ads-setting' == $_GET['page']){
		if(is_admin() && isset($_POST['submit'])){
			$options['banner'] = $_POST['banner'];
			$options['no_ads'] = $_POST['no_ads'];
			$options['theme'] = $_POST['ms-ads-theme'];
			//Update Processes
			$msads_option = ms_ads_get_op().'options';
			wp_protect_special_option( $option );
			$options = sanitize_option( $msads_option, $options );
			$options = apply_filters( 'pre_update_option_ads_option', $options, ms_ads_option() );
			$options = maybe_serialize($options);
			$wpdb->update( $msads_option, array( 'option_value' => $options ), array( 'option_name' => 'ads_option' ) );
			//Update Message
			echo '<div class="updated below-h2" id="message"><p>تنظیمات به‌روز شد.</p></div>';
		}
	$options = ms_ads_option();
	$total = count($options['banner']);
?>
<script type="text/javascript">
//<![CDATA[
var index_amf_total  = <?php echo $total; ?>;
//]]>
</script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL.'/'.MS_ADS_FOLDER.'/options.js' ?>"></script>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2 style="font-family:Tahoma;font-size:20px">تنظیمات تبلیغات</h2>
	<div class="narrow">
		<form action="" method="post" style="margin: auto; width: 645px; ">
			<p id="banners">
			 <label for="banners"><p><h5>مديريت بنرها</h5></p>
			<div id="banner-0">آدرس بنر : <input name="banner[0][src]" value="<?php echo @$options['banner'][0]['src'] ?>" size="76" type="text" /> <br /> لينك بنر : &nbsp;&nbsp;<input name="banner[0][href]" value="<?php echo @$options['banner'][0]['href'] ?>" size="50" type="text" /> تعداد نمایش : <input name="banner[0][display]" value="<?php echo @$options['banner'][0]['display'] ?>" size="4" type="text" /> درصد </div><p />
					<span id="more_banner_inputs">
<?php
		for($i=1;$i<$total;$i++){
?>
						<div id="banner-<?php echo $i; ?>">آدرس بنر : <input name="banner[<?php echo $i; ?>][src]" value="<?php echo $options['banner'][$i]['src'] ?>" size="76" type="text" /> <br /> لينك بنر : &nbsp;&nbsp;<input name="banner[<?php echo $i; ?>][href]" value="<?php echo $options['banner'][$i]['href'] ?>" size="50" type="text" /> تعداد نمایش : <input name="banner[<?php echo $i; ?>][display]" value="<?php echo $options['banner'][$i]['display'] ?>" size="4" type="text" /> درصد <input class="button1" onclick="javascript:remove_banner_input('banner-<?php echo $i; ?>');" style="height: 19px;" value="حذف" type="button"></div> <p />
<?php } ?>
					</span> <p />
					<input onclick="javascript:new_banner_input();" value="افزودن بنر بیشتر" type="button" /> 
				</label>
			</p>
			<label for="no_ads"><p><h5>حذف تبليغ از وبلاگها</h5></p>
				<p>
				<textarea id="no_ads" name="no_ads" cols="68" rows="3" ><?php echo $options['no_ads']; ?></textarea>
				<br>‌‌آي‌دي وبلاگ‌ها را وارد نماييد و آن‌ها را با فاصله (Space) از يکديگر تفکيک کنيد مانند :1 2</br>
				</p>
			</label>
			<label for="theme"><p><h5>پوسته</h5></p>
				<select id="theme" name="ms-ads-theme" style="width:120px;">
<?php
	if ($handle = opendir(ABSPATH.'wp-content/plugins/'.MS_ADS_FOLDER.'/themes')) {
		while (false !== ($theme = readdir($handle))) {
			if ($theme != "." && $theme != "..") {
				$select = '';
				if($options['theme'] == $theme)
					$select = 'selected="selected"';
				printf('<option value="%s" %s > %1$s </option>\n',$theme,$select);
			}
		}
		closedir($handle);
	}
?>
				</select>
			</label>
				<p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
		</form>
	</div>
</div>
<?php
}
}
add_action('admin_menu', 'ads_setting');
add_action('wp_head','show_ads');
?>