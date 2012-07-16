<?php
/*
Plugin Name: MS ADS
Plugin URI: http://www.php-press.com/
Version: 1.6
Description: Persian Advertisement Plugin
Author: Moeini
Author URI: http://www.php-press.com/
*/

MS_ADS::Run();
class MS_ADS {
	public $text_domain = 'ms-ads';
	public $content_show_ad = false;
	public $blog_id = 0;

	static function Run() {
		global $ms_ads_instance;
		if(! $ms_ads_instance instanceof MS_ADS) {
			$ms_ads_instance = new MS_ADS();
			$ms_ads_instance->_Start();
		}
	}
	public function _Start() {
		
		$this->blog_id = &$GLOBALS['wpdb']->blogid;
		$this->apply_filters();
	}
	public function add_menu() {
		add_submenu_page( 'settings.php', __('Advertisement',$this->text_domain), __('Advertisement',$this->text_domain), 'manage_network_options', $this->text_domain,array($this,'admin_page') );
	}
	public function apply_filters() {
		add_action('muplugins_loaded',array(&$this,'load_textdomain'));
		add_action('network_admin_menu', array(&$this,'add_menu'));
		$location = $this->get_options('location');
		if(strtolower($location) == 'content') {
			add_action('the_post',array(&$this,'content_show_ad'));
		} else {
			add_action('wp_head',array(&$this,'show'));
		}
	}
	function show_ads_above_content($posts) {
		if(! $this->content_show_ad) {
			$this->show();
			$this->content_show_ad = true;
		}
		return $posts;
	}
	public function load_textdomain() {
		load_muplugin_textdomain( $this->text_domain, 'ms-ads-languages' );
	}
	public function get_options($option_key = '') {
		global $wpdb;
		$default_args = array(
				'banner'=>array(array('src' =>'','href' =>'','display' =>'','description'=>'','title'=>'')),
				'theme'=>'default-left',
				'no_ads'=>'1',
				'location' => 'header'
		);
		$t = $wpdb->options;
		$wpdb->options = $wpdb->base_prefix . 'options';
		$options = get_option('ads_option',$default_args);
		$wpdb->options = $t;
		
		if(empty($option_key))
			return $options;
		if(isset($options[$option_key]))
			return $options[$option_key];
	}
	public function get_random_ad($banners = false){
		if(empty($banners)) {
			$banners = $this->get_options('banner');
		}
		foreach((array) $banners as $num=>$array)
		 $Weight[$num] = $array['display'];
		$sum =0;
		for($i=0;$i<count($Weight);$i++)
			$sum+=$Weight[$i];
		$ShowAd = rand(0, $sum - 1);
		for($i=0;$i<count($Weight);$i++) {
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

	public function show(){
		$options = $this->get_options();
		global $wpdb;
		$blogs = &$options['no_ads'];
		if(!is_array($blogs)) {
			$blogs = explode(' ',$blogs);
		}
		if(!in_array($this->blog_id,$blogs)) {
			$id = 'ms_ads_' . mt_rand();
			$banner_id = $this->get_random_ad($options['banner']);

			$theme = WPMU_PLUGIN_DIR.'/ms-ads-themes/'.$options['theme'].'/main.html';
			
			if(!file_exists($theme))
				return ;
			
			$main = fopen($theme, 'r');		$main = fread($main, filesize($theme));
			$main = str_replace(array('{id}','{link}','{banner}','{description}','{title}'),array($id,$options['banner'][$banner_id]['href'],$options['banner'][$banner_id]['src'],$options['banner'][$banner_id]['description'],$options['banner'][$banner_id]['title']),$main);
			echo $main;
		}
	}

	function admin_page(){
		if(!is_super_admin())
			return false;
		if(isset($_POST['submit'])){

			$options['banner'] = array();
			foreach((array)$_POST['banner'] as $id=>$values)
				$options['banner'][] = array_map('esc_sql',((array)$values));
			$options['no_ads'] = sanitize_text_field($_POST['no_ads']);
			$options['theme'] = sanitize_text_field($_POST['ms-ads-theme']);
			$options['location'] = sanitize_text_field($_POST['location']);
			//Update Processes
			echo '<div class="updated below-h2" id="message"><p>';
			if(update_option('ads_option',$options))
				_e('Options updated',$this->text_domain);
			else
				echo '<span style="color:red;">'.__('Error updating options',$this->text_domain).'</span>';
			echo '</p></div>';
			//Update Message

			wp_cache_delete('ads_option','options');
		}
		$options = $this->get_options();
		$total = count($options['banner']);
		?>
<script type="text/javascript">
	//<![CDATA[
	var index_amf_total  = <?php echo intval($total); ?>;
	var _Banner_URL  = "<?php _e('Banner URL:',$this->text_domain); ?>";
	var Banner_Link  = "<?php _e('Banner Link:',$this->text_domain); ?>";
	var Number_of_views  = "<?php _e('Number of views:',$this->text_domain); ?>";
	var _Percent  = "<?php _e('Percent',$this->text_domain); ?>";
	var Banner_title  = "<?php _e('Banner title:',$this->text_domain); ?>";
	var Banner_Description  = "<?php _e('Banner Description:',$this->text_domain); ?>";
	var _Remove  = "<?php _e('Remove',$this->text_domain); ?>";
	//]]>
	</script>
<script
	type="text/javascript"
	src="<?php echo WPMU_PLUGIN_URL.'/options.js' ?>"></script>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br />
	</div>
	<h2 style="font-family: Tahoma; font-size: 20px">
		<?php _e('Advertisement Options',$this->text_domain) ?>
	</h2>
	<div class="narrow">
		<form action="" method="post" style="margin: auto; width: 645px;">
			<p id="banners">
			
			
			<h5>
				<?php _e('Banners management',$this->text_domain) ?>
			</h5>

			<div id="banner-0">
				<?php _e('Banner URL:',$this->text_domain) ?>
				<input name="banner[0][src]"
					value="<?php echo isset($options['banner'][0]['src']) ? esc_attr($options['banner'][0]['src']) : ''; ?>"
					size="76" type="text" /> <br />
				<?php _e('Banner Link:',$this->text_domain) ?>
				&nbsp;&nbsp;<input name="banner[0][href]"
					value="<?php echo isset($options['banner'][0]['href']) ? esc_attr($options['banner'][0]['href']) : ''; ?>"
					size="50" type="text" />
				<?php _e('Number of views:',$this->text_domain) ?>
				<input name="banner[0][display]"
					value="<?php echo isset($options['banner'][0]['display']) ? esc_attr($options['banner'][0]['display']) : ''; ?>"
					size="4" type="text" />
				<?php _e('Percent',$this->text_domain) ?>
				<br />
				<?php _e('Banner title:',$this->text_domain) ?>
				&nbsp;&nbsp;<input name="banner[0][title]"
					value="<?php echo isset($options['banner'][0]['title']) ? esc_attr($options['banner'][0]['title']) : ''; ?>"
					size="50" type="text" /><br />
				<?php _e('Banner Description:',$this->text_domain) ?>
				&nbsp;&nbsp;<input name="banner[0][description]"
					value="<?php echo isset($options['banner'][0]['description']) ? esc_attr($options['banner'][0]['description']) : ''; ?>"
					size="50" type="text" />
			</div>
			<p />

			<div id="more_banner_inputs">
				<?php
				for($i=1;$i<$total;$i++){
					?>
				<div id="banner-<?php echo $i; ?>">
					<?php _e('Banner URL:',$this->text_domain) ?>
					<input name="banner[<?php echo $i; ?>][src]"
						value="<?php echo isset($options['banner'][$i]['src']) ? esc_attr($options['banner'][$i]['src']) : ''; ?>"
						size="76" type="text" /> <br />
					<?php _e('Banner Link:',$this->text_domain) ?>
					&nbsp;&nbsp;<input name="banner[<?php echo $i; ?>][href]"
						value="<?php echo isset($options['banner'][$i]['href']) ? esc_attr($options['banner'][$i]['href']) : ''; ?>"
						size="50" type="text" />
					<?php _e('Number of views:',$this->text_domain) ?>
					<input name="banner[<?php echo $i; ?>][display]"
						value="<?php echo isset($options['banner'][$i]['display']) ? esc_attr($options['banner'][$i]['display']) : ''; ?>"
						size="4" type="text" />
					<?php _e('Percent',$this->text_domain) ?>
					<br />
					<?php _e('Banner title:',$this->text_domain) ?>
					&nbsp;&nbsp;<input name="banner[<?php echo $i; ?>][title]"
						value="<?php echo isset($options['banner'][$i]['title']) ? esc_attr($options['banner'][$i]['title']) : ''; ?>"
						size="50" type="text" /><br />
					<?php _e('Banner Description:',$this->text_domain) ?>
					&nbsp;&nbsp;<input name="banner[<?php echo $i; ?>][description]"
						value="<?php echo isset($options['banner'][$i]['description']) ? esc_attr($options['banner'][$i]['description']) : ''; ?>"
						size="50" type="text" />
				</div>
				<p />
				<?php } ?>
			</div>
			<p />
			<input onclick="javascript:new_banner_input();"
				value="<?php _e('Add more banner',$this->text_domain) ?>" type="button" />

			<h5>
				<?php _e('Remove blogs advertising',$this->text_domain) ?>
			</h5>
			<p>
				<textarea id="no_ads" name="no_ads" cols="68" rows="3"><?php echo esc_textarea($options['no_ads']); ?></textarea>
				<br> 
				<?php _e('Enter Blog ID and separated with (Space), like: 1 2 8',$this->text_domain) ?>
			</p>
			<p>
			
			
			<h5>
				<?php _e('Theme',$this->text_domain) ?>
			</h5>
			<select id="theme" name="ms-ads-theme" style="width: 120px;">
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

			<h5>
				<?php _e('Show location:',$this->text_domain) ?>
			</h5>
			<select id="location" name="location" style="width: 120px;">
				<?php	$location = strtolower($options['location']); ?>
				<option value="header"
				<?php if($location == 'header') echo ' selected="selected"'; ?>>
					<?php _e('Header') ?>
				</option>
				<option value="content"
				<?php if($location == 'content') echo ' selected="selected"'; ?>>
					<?php _e('Above content',$this->text_domain) ?>
				</option>
			</select>
			<p class="submit">
				<input type="submit" name="submit"
					value="<?php _e('Update options &raquo;'); ?>" />
			</p>
		</form>
	</div>
</div>
<?php
	}
}