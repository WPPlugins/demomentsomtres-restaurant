<?php
/**
 * @package DeMomentSomTres Restaurant
 */
/*
 * Plugin Name: DeMomentSomTres Restaurant
 * Plugin URI: http://www.demomentsomtres.com/en/wordpress-plugins/demomentsomtres-restaurant/
 * Description: DeMomentSomTres Restaurants creates a custom type to represent restaurant lists and menus and show them using shortcodes and menu entries.
 * Version: 2.1
 * Author: DeMomentSomTres
 * Author URI: http://www.DeMomentSomTres.com
 * License: GPLv2 or later
 * Text Domain: demomentsomtres-restaurant
 * Domain Path: /languages
 */

/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

require_once (dirname(__FILE__) . '/lib/class-tgm-plugin-activation.php');

$demomentsomtres_restaurant = new DeMomentSomTresRestaurant;

class DeMomentSomTresRestaurant {

	const OPTIONS = 'dms3-restaurant';
	const POSTTYPE = 'dish-list';
	const EXPIRYDATE = "expiry-date";
	const MENULIFE = "menu-life";
	const TEMPLATES = "number-of-templates";
	const TEMPLATETITLE = "title";
	const TEMPLATECONTENT = "content";
	const OLDEXPIRYDATE = "demomentsomtres-restaurant-expiry-date";

	private $pluginURL;
	private $pluginPath;
	private $langDir;
	private $printScripts = false;

	function __construct() {
		$this -> pluginURL = plugin_dir_url(__FILE__);
		$this -> pluginPath = plugin_dir_path(__FILE__);
		$this -> langDir = dirname(plugin_basename(__FILE__)) . '/languages';

		add_action('plugins_loaded', array($this, 'plugin_loaded'));
		add_action('tgmpa_register', array($this, 'required_plugins'));
		add_action('init', array($this, 'posttypes'), 1);
		add_action('tf_create_options', array($this, "titan"));
		add_action("do_meta_boxes", array($this, "do_meta_boxes"));
		add_action("admin_enqueue_scripts", array($this, "admin_enqueue_scripts"));
		add_action('admin_head', array($this, 'insert_buttons'));
		// add_action("wp_footer",array($this,"wp_footer"));
		add_action("wp_ajax_dms3_restaurant_update", array($this, "webserviceUpgrade"));
		add_action("wp_ajax_nopriv_dms3_restaurant_update", array($this, "webserviceUpgrade"));
		add_shortcode('eco', array($this, 'shortcode_eco'));
		add_shortcode('veg', array($this, 'shortcode_veg'));
		add_shortcode('cel', array($this, 'shortcode_cel'));
		add_shortcode('P', array($this, 'shortcode_p'));
		add_shortcode('demomentsomtres-restaurant-dish-list', array($this, 'shortcode_dish_list'));
		add_action('the_content', array($this, 'content_expired_filter'));
	}

	function plugin_loaded() {
		load_plugin_textdomain("demomentsomtres-restaurant", false, $this -> langDir);
	}

	function required_plugins() {
		$plugins = array( array('name' => 'Titan Framework', 'slug' => 'titan-framework', 'required' => true), );
		tgmpa($plugins);
	}

	function titan() {
		$titan = TitanFramework::getInstance(self::OPTIONS);
		$panel = $titan -> createAdminPanel(array("name" => __("Settings","demomentsomtres-restaurant"), "title" => __("DeMomentSomTres Restaurant Settings","demomentsomtres-restaurant"), "icon" => "dashicons-calendar-alt", "parent" => "edit.php?post_type=" . self::POSTTYPE, ));
		$panel -> createOption(array("type" => "heading", "name" => __("More information","demomentsomtres-restaurant"), "desc" => "<a href='//www.demomentsomtres.com/en/wordpress-plugins/demomentsomtres-restaurant/' target='_blank'>Plugin page</a>", ));
		$main = $panel -> createTab(array("name" => __("Main settings","demomentsomtres-restaurant"), ));
		$main -> createOption(array("type" => "number", "name" => __("Menu Life","demomentsomtres-restaurant"), "desc" => __("Days that a menu will be alive before getting expired, by default","demomentsomtres-restaurant"), "id" => self::MENULIFE, "default" => 1, ));
		$main -> createOption(array("type" => "number", "name" => __("Number of templates","demomentsomtres-restaurant"), "desc" => __("Number of templates that you want to mantain.","demomentsomtres-restaurant") . "<br/><strong>" . __("WARNING:","demomentsomtres-restaurant") . "</strong>" . __("If you decrease this number, templates can be lost.","demomentsomtres-restaurant"), "id" => self::TEMPLATES, "default" => 1, ));
		$main -> createOption(array("type" => "save", "save" => __("Save","demomentsomtres-restaurant"), "use_reset" => false, ));
		$templates = $panel -> createTab(array("name" => __("Templates","demomentsomtres-restaurant"), ));
		$ntemp = $titan -> getOption(self::TEMPLATES);
		if ($ntemp == 0) :
			$ntemp = 1;
		endif;
		$templates -> createOption(array("type" => "heading", "name" => __("Instructions","demomentsomtres-restaurant"), ));
		$templates -> createOption(array("type" => "note", "id" => "note1", "name" => __("Add a new template","demomentsomtres-restaurant"), "desc" => __("Go to main settings and increase the number of templates","demomentsomtres-restaurant"), ));
		$templates -> createOption(array("type" => "note", "id" => "note2", "name" => __("Delete last template","demomentsomtres-restaurant"), "desc" => __("Go to main settings and decrease the number of templates","demomentsomtres-restaurant"), ));
		$templates -> createOption(array("type" => "note", "id" => "note3", "name" => __("Delete a template not at bottom","demomentsomtres-restaurant"), "desc" => __("Not implemented. You can copy content and title of previous templates and delete the last one.","demomentsomtres-restaurant"), ));
		for ($i = 1; $i <= $ntemp; $i++) :
			$templates -> createOption(array("type" => "heading", "name" => __("Template #","demomentsomtres-restaurant") . $i, ));
			$templates -> createOption(array("type" => "text", "name" => __("Title","demomentsomtres-restaurant"), "id" => self::TEMPLATETITLE . "-" . $i, ));
			$templates -> createOption(array("type" => "editor", "name" => __("Content","demomentsomtres-restaurant"), "id" => self::TEMPLATECONTENT . "-" . $i, "lang" => "html", ));
		endfor;
		$templates -> createOption(array("type" => "save", "save" => __("Save","demomentsomtres-restaurant"), "use_reset" => false, ));
		$update = $panel -> createTab(array("name" => __("Upgrade","demomentsomtres-restaurant"), ));
		$update -> createOption(array("type" => "note", "id" => "note4", "name" => __("Upgrade procedure","demomentsomtres-restaurant"), "desc" => __("If you were using a version of this plugin prior to 2.0 you should perform this upgrade.","demomentsomtres-restaurant") . "<br/>" . __("It will update database fields in order to make them compatible with 2.0.","demomentsomtres-restaurant") . "<br/>" . "<strong>" . __("Don't forget to previously backup your database.","demomentsomtres-restaurant") . "</strong>", ));
		$update -> createOption(array("type" => "ajax-button", "id" => "dms3_restaurant_update", "action" => "dms3_restaurant_update", "label" => __("Start Upgrade","demomentsomtres-restaurant"), "wait_label" => __("Upgrade in progress","demomentsomtres-restaurant"), "success_label" => __("Upgrade successfull","demomentsomtres-restaurant"), "error_label" => __("Upgrade failed","demomentsomtres-restaurant"), "class" => "button-primary", ));
		$metaBox = $titan -> createMetaBox(array("name" => __("Menu","demomentsomtres-restaurant"), "desc" => __("Menu specific data","demomentsomtres-restaurant"), "post_type" => array(self::POSTTYPE), "context" => "side", "priority" => "high", ));
		$metaBox -> createOption(array("type" => "date", "name" => __("Expiry date","demomentsomtres-restaurant"), "desc" => __("Dish list will be valid until the end of the selected day","demomentsomtres-restaurant"), "id" => self::EXPIRYDATE, "default" => self::default_expiry_date(), ));
	}

	public static function menu_life() {
		$titan = TitanFramework::getInstance(self::OPTIONS);
		$value = $titan -> getOption(self::MENULIFE);
		return $value;
	}

	function default_expiry_date() {
		$day = new DateTime();
		$day -> add(new DateInterval('P' . self::menu_life() . 'D'));
		$expiry = $day -> format('U');
		return $expiry;
	}

	function do_meta_boxes() {

	}

	function posttypes() {
		$labels = array('name' => _x('Menu Types', 'taxonomy general name',"demomentsomtres-restaurant"), 'singular_name' => _x('Menu Type', 'taxonomy singular name',"demomentsomtres-restaurant"), 'search_items' => __('Search Type',"demomentsomtres-restaurant"), 'all_items' => __('All Types',"demomentsomtres-restaurant"), 'parent_item' => __('Parent Type',"demomentsomtres-restaurant"), 'parent_item_colon' => __('Parent Type:',"demomentsomtres-restaurant"), 'edit_item' => __('Edit Type',"demomentsomtres-restaurant"), 'update_item' => __('Update Type',"demomentsomtres-restaurant"), 'add_new_item' => __('Add New Type',"demomentsomtres-restaurant"), 'new_item_name' => __('New Type Name',"demomentsomtres-restaurant"), );
		register_taxonomy('dish-list-type', '', array('hierarchical' => true, 'labels' => $labels));
		register_post_type(self::POSTTYPE, array('labels' => array('name' => _x('Menus', 'Post Type General Name',"demomentsomtres-restaurant"), 'singular_name' => _x('Menu', 'Post Type Singular Name',"demomentsomtres-restaurant"), 'menu_name' => __('Restaurant',"demomentsomtres-restaurant"), 'name_admin_bar' => __('Restaurant',"demomentsomtres-restaurant"), 'archives' => __('Restaurant menus',"demomentsomtres-restaurant"), 'parent_item_colon' => __('Parent menu:',"demomentsomtres-restaurant"), 'all_items' => __('All menus',"demomentsomtres-restaurant"), 'add_new_item' => __('Add new menu',"demomentsomtres-restaurant"), 'add_new' => __('Add New',"demomentsomtres-restaurant"), 'new_item' => __('New Menu',"demomentsomtres-restaurant"), 'edit_item' => __('Edit Menu',"demomentsomtres-restaurant"), 'update_item' => __('Update Menu',"demomentsomtres-restaurant"), 'view_item' => __('View Menu',"demomentsomtres-restaurant"), 'search_items' => __('Search Menu',"demomentsomtres-restaurant"), 'not_found' => __('Not found',"demomentsomtres-restaurant"), 'not_found_in_trash' => __('Not found in Trash',"demomentsomtres-restaurant"), 'featured_image' => __('Featured Image',"demomentsomtres-restaurant"), 'set_featured_image' => __('Set featured image',"demomentsomtres-restaurant"), 'remove_featured_image' => __('Remove featured image',"demomentsomtres-restaurant"), 'use_featured_image' => __('Use as featured image',"demomentsomtres-restaurant"), 'insert_into_item' => __('Insert into Menu',"demomentsomtres-restaurant"), 'uploaded_to_this_item' => __('Uploaded to this menu',"demomentsomtres-restaurant"), 'items_list' => __('Menus list',"demomentsomtres-restaurant"), 'items_list_navigation' => __('Menus list navigation',"demomentsomtres-restaurant"), 'filter_items_list' => __('Filter menus list',"demomentsomtres-restaurant"), ), 'public' => true, 'show_in_nav_menus' => true, 'menu_position' => 15, 'taxonomies' => array('dish-list-type'), 'rewrite' => array('slug' => 'restaurant'), 'query_var' => true, 'has_archive' => true, 'supports' => array('title', 'editor', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'thumbnail', 'author', 'page-attributes')));
	}

	function admin_enqueue_scripts($hook) {
		wp_enqueue_script('dms3Restaurant', $this -> pluginURL . 'js/demomentsomtres-restaurant.js');
		$titan = TitanFramework::getInstance(self::OPTIONS);
		$ntemp = $titan -> getOption(self::TEMPLATES);
		$translation_array = array();
		$template_array = array();
		for ($i = 1; $i <= $ntemp; $i++) :
			$titol = $titan -> getOption(self::TEMPLATETITLE . "-" . $i);
			$content = $titan -> getOption(self::TEMPLATECONTENT . "-" . $i);
			$template_array[] = array('text' => $titol, 'value' => $content, );
		endfor;
		$translation_array["templates"] = $template_array;
		$translation_array["title"] = __("Add template","demomentsomtres-restaurant");
		wp_localize_script('dms3Restaurant', 'dms3Restaurant', $translation_array);
	}

	/**
	 * Checks if restaurant buttons are required
	 * @since 1.0
	 * @global string $current_screen
	 */
	function insert_buttons() {
		global $current_screen;
		if ((!current_user_can('edit_posts') && !current_user_can('edit_pages')) && get_user_option('rich_editing')) :
			return;
		endif;
		if ($current_screen -> post_type == "dish-list") :
			if (get_user_option('rich_editing')) :
				add_filter('mce_buttons', array($this, 'register_buttons'));
				add_filter("mce_external_plugins", array($this, "add_tinymce_plugin"));
			endif;
		endif;
	}

	/**
	 * Registers buttons to TinyMCE
	 * @param array $buttons
	 * @return array
	 * @since 1.0
	 */
	function register_buttons($buttons) {
		array_push($buttons, "dms3RestaurantEco", "dms3RestaurantVeg", "dms3RestaurantCel", "dms3RestaurantPrice", "dms3RestaurantTemplate");
		return $buttons;
	}

	/**
	 * Adds Load the TinyMCE plugin : editor_plugin.js
	 * @param array $plugin_array
	 * @return type
	 * @since 1.0
	 */
	function add_tinymce_plugin($plugin_array) {
		$plugin_array['dms3Restaurant'] = plugins_url('mce_plugins/editor_plugin.js', __FILE__);
		return $plugin_array;
	}

	function wp_footer() {
		/*
		 if($this->printScripts):
		 wp_enqueue_script("dms3okko");
		 endif;
		 */
	}

	/**
	 * Manages dish list shortcode
	 * @since 0.1
	 * @param array $attr
	 * @param string $content
	 * @return string the Content with the dishlist
	 */
	function shortcode_dish_list($attr) {
		if (!isset($attr['type'])) :
			return '';
		endif;
		$type = $attr['type'];
		$count = (!isset($attr['count'])) ? 1 : $attr['count'];
		$empty = (!isset($attr['empty'])) ? "" : $attr['empty'];
		$emptyurl = (!isset($attr['emptyurl'])) ? "" : $attr['emptyurl'];
		$prefix = (!isset($attr['prefix'])) ? "" : $attr['prefix'];
		$suffix = (!isset($attr['suffix'])) ? "" : $attr['suffix'];
		$classes = (!isset($attr['classes'])) ? "" : " " . $attr['classes'];
		$hiddenTitles = in_array('hidden_titles', $attr) ? true : false;
		//$hiddenTitles = (!isset($attr['hidden_titles'])) ? false : true;
		$blogRef = (!isset($attr['blog_id'])) ? FALSE : $attr['blog_id'];
		$titleFormat = (!isset($attr['title_format'])) ? 'h3' : $attr['title_format'];
		$cssid = (!isset($attr["id"])) ? "" : " id='" . $attr["id"] . "' ";
		$allDishLists = $this->get_dish_lists($type, $count, $blogRef);
		$output = '';
		if (count($allDishLists) == 0) :
			if ($emptyurl == "") :
				$output .= $empty;
			else :
				$output .= "<a href='$emptyurl' title='$empty'>$empty</a>";
			endif;
		else :
			foreach ($allDishLists as $dishList) :
				$output .= "<div class='demomentsomtres-dish-list$classes'$cssid>";
				if (!$hiddenTitles) :
					$output .= "<$titleFormat class=\"demomentsomtres-dish-list-title\">";
					$output .= $prefix;
					$output .= $dishList -> post_title;
					$output .= $suffix;
					$output .= "</$titleFormat>";
				endif;
				$output .= wpautop($dishList -> post_content);
				$output .= "</div>";
			endforeach;
		endif;
		//  echo '<pre style="/*display:none;*/">';
		//  print_r($attr);
		//  echo "Hidden Titles: ";
		//	print_r($hiddenTitles);
		//	echo "<br/>";
		//  print_r($allDishLists);
		//  echo '</pre>';
		return do_shortcode($output);
	}

	function shortcode_eco() {
		$output = '<i class="icon-eco">' . __('Ecological',"demomentsomtres-restaurant") . '</i>';
		return $output;
	}

	function shortcode_veg() {
		$output = '<i class="icon-veg">' . __('Vegetarian',"demomentsomtres-restaurant") . '</i>';
		return $output;
	}

	/**
	 *
	 * @since 1.4
	 */
	function shortcode_cel() {
		$output = '<i class="icon-cel">' . __('Gluten Free',"demomentsomtres-restaurant") . '</i>';
		return $output;
	}

	function shortcode_p($attr) {
		$output = '<span class="price">' . $attr[0] . '</span>';
		//$output='<pre>'.print_r($attr,true).'</pre>';
		return $output;
	}

	/**
	 * Modifies content post to insert the expiry date
	 * @global mixed $post
	 * @param text $content
	 * @return string
	 * @since 1.1.0
	 */
	function content_expired_filter($content) {
		global $post;
		if (($post -> post_type == 'dish-list') && in_the_loop()) :
			$newText = $this -> expired_message($post -> ID);
		else :
			$newText = '';
		endif;
		$content = $newText . $content;
		return $content;
	}

	/**
	 * Generates a message to add to the dish list if it is expired
	 * @param integer $postid the post id
	 * @return string the message to attach
	 * @since 1.1.0
	 */
	function expired_message($postid) {
		if (self::is_expired($postid)) :
			$expiredMessage = __("Expired since", "demomentsomtres-restaurant") . " ";
			return '<span class="demomentsomtres-restaurant-expired">' . $expiredMessage . self::pretty_expiry_date($postid) . '</span>';
		else :
			return '';
		endif;
	}

	/**
	 * Checks if the post is expired
	 * @param integer $postid the post to test
	 * @return boolean
	 * @since 1.1.0
	 */
	public static function is_expired($postid) {
		$expiry_date = self::get_expiry_date($postid);
		if ($expiry_date) :
			$now = strtotime(date('Y-m-d'));
			return ($now > $expiry_date);
		else :
			return false;
		endif;
	}

	/**
	 * Get the expiry date based on postid
	 * @param integer $postid the post to test
	 * @return boolean
	 * @since 1.1.0
	 */
	public static function get_expiry_date($postid) {
		$titan = TitanFramework::getInstance(self::OPTIONS);
		$value = $titan -> getOption(self::EXPIRYDATE);
		return $value;
	}

	/**
	 * Formats the expiry date to be beautiful
	 * @param integer $postid   the post to consider
	 * @return string the date ready to be printed
	 * @since 1.1.0
	 */
	public static function pretty_expiry_date($postid) {
		$dateFormat = get_option('date_format');
		$dateDB = self::get_expiry_date($postid);
		$date = strtotime($dateDB);
		$text = date_i18n($dateFormat, $date);
		return $text;
	}

	public static function get_dish_lists($type, $count = 1, $blog = false) {
		$queryArgs = array('post_type' => self::POSTTYPE, 'orderby' => 'date', 'order' => 'DESC', 'posts_per_page' => $count, 'tax_query' => array( array('taxonomy' => 'dish-list-type', 'field' => 'id', 'terms' => $type, ), ), 'meta_query' => array( array('key' => self::OPTIONS."_".self::EXPIRYDATE, 'value' => strtotime(date('Y-m-d')), 'compare' => '>=', ), ), );
		if ($blog && is_multisite()) ://MQB 20151104+
			switch_to_blog($blog);
		endif;
		$newQuery = new WP_Query();
		$newQuery -> query($queryArgs);
		if ($blog && is_multisite()) ://MQB 20151105+
			restore_current_blog();
		endif;
		return $newQuery -> posts;
	}

	function webserviceUpgrade() {
		global $wpdb;

		$query = "select a.post_id,a.meta_value from {$wpdb->prefix}postmeta a left join (select post_id from {$wpdb->prefix}postmeta where meta_key='" . self::OPTIONS . "_" . self::EXPIRYDATE . "') b on a.post_id=b.post_id where a.meta_key='" . self::OLDEXPIRYDATE . "' and b.post_id is null";
		$results = $wpdb -> get_results($query);
		if ($results) :
			foreach ($results as $n => $r) :
				$dia = strtotime($r -> meta_value);
				$wpdb -> insert("{$wpdb->prefix}postmeta", array("post_id" => $r -> post_id, "meta_key" => self::OPTIONS . "_" . self::EXPIRYDATE, "meta_value" => $dia, ));
			endforeach;
		endif;
		$success = true;
		if ($success) :
			wp_send_json_success($data);
		else :
			wp_send_json_error($data);
		endif;
	}

}
?>