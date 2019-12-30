<?php
/*
 * Plugin Name: Skfunc
 * Plugin URI: https://komish.com/
 * Description: 小宮用の関数ライブラリ
 * Author: Komiya Shuuichi
 * version: 0.1
 * Author URI: http://komish.com/
 */

add_filter('wp_head', function(){
if (!is_preview() && is_singular()){
?>
<script>
function getCanonicalUrl(){
	var links=document.getElementsByTagName("link");
	for(var i=0;i<links.length;i++){
		if (links[i].rel){
			if (links[i].rel.toLowerCase()=="canonical"){
				return links[i].href;
			}
		}
	}
	return '';
}
var crUrl=location.href;
var cnUrl = getCanonicalUrl();
if (cnUrl != '' && crUrl.indexOf(cnUrl)==-1){
	location.href=cnUrl;
}
</script>
<?php
}
}, 99);

if (!function_exists('gtm_data_vars')):
    function gtm_data_vars($the_content) {
        if ( is_amp() === true) {   //amp判定
            $atag_regex = '/<a( .+?)?\shref=[\'|"](.*?)[\'|"].*?>(.*?)<\/a>/';  //アンカータグとhref属性値・リンク文字列取得用の正規表現
            if (preg_match_all($atag_regex, $the_content, $as)) {
                for ($i = 0; $i < count($as[0]); ++$i) {
                    $gtm_tag_str = ' data-vars-href="' . $as[2][$i] . '" data-vars-linktext="'. strip_tags($as[3][$i]) .'">' . $as[3][$i] . '</a>'; //アンカータグ再構築用変数
                    $the_content = str_replace($as[0][$i], substr($as[0][$i], 0, strcspn($as[0][$i], '>')) . $gtm_tag_str, $the_content);   //アンカータグ再構築
                }
            }
            return $the_content;
        } else {
            return $the_content;
        }
    }
endif;
        
if (!function_exists('wp_loaded_add_gtm_data_vars')):
    function wp_loaded_add_gtm_data_vars() {
        ob_start('gtm_data_vars');
    }
endif;

add_action('wp_loaded', 'wp_loaded_add_gtm_data_vars', 1);

function get_category_tree($parent, $param=null){
	$categories = get_categories('parent=' . $parent . '&'. $param);
	echo '<ul>';
	foreach ( $categories as $term ) {
		echo '<li><a href="' . get_term_link( $term->term_id, 'category' ) . '">' . $term->name . '('. $term->count . ')</a>';
		$children = get_term_children($term->term_id, 'category');
		if (!empty($children)){
			get_category_tree($term->term_id);
		}else{
			echo '</li>';
		}
	}
	echo '</ul>';
}

// add to move the comment text field to the bottom in WordPress 4.4 12/12/2015
function wp34731_move_comment_field_to_bottom( $fields ) {
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	
	return $fields;
}
add_filter( 'comment_form_fields', 'wp34731_move_comment_field_to_bottom' );
// End 12/12/2015

// コメントからEmailとウェブサイトを削除
function my_comment_form_remove($arg) {
	$arg['url'] = '';
	$arg['email'] = '';
	return $arg;
}
add_filter('comment_form_default_fields', 'my_comment_form_remove');
 
// 「メールアドレスが公開されることはありません」を削除
function my_comment_form_before( $defaults){
	$defaults['comment_notes_before'] = '';
	return $defaults;
}
add_filter( "comment_form_defaults", "my_comment_form_before");
 
// 「HTMLタグと属性が使えます…」を削除
function my_comment_form_after($args){
	$args['comment_notes_after'] = '';
	return $args;
}
add_filter("comment_form_defaults","my_comment_form_after");

function close_page_comment( $open, $post_id ) {
    $post = get_post( $post_id );
    if ( $post && $post->post_type == 'page' ) {
        return false;
    }
    return $open;
}
add_filter( 'comments_open', 'close_page_comment', 10, 2 );

function is_mobile() {
   return preg_match(
	'{iPhone|iPod|(?:Android.+?Mobile)|BlackBerry9500|BlackBerry9530|BlackBerry9520|BlackBerry9550|BlackBerry9800|Windows Phone|webOS|(?:Firefox.+?Mobile)|Symbian|incognito|webmate|dream|CPUCAKE}', 
   $_SERVER['HTTP_USER_AGENT']);
}

function is_sk_ktai() {
	if ( function_exists('is_ktai') ) {
		return is_ktai();
	} else {
		return false;
	}
}

/*
	for sales letter short code
*/

function attr_func( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'class' => 'default',
	), $atts ) );

	return '<span class="' . $class. '">' . do_shortcode( $content) . '</span>';
}
add_shortcode('attr', 'attr_func');


/*************************************************************/

function is_ad_enabled(){
	global $g_ad_enabled;
	
	if ($g_ad_enabled){
		$value = get_post_meta(get_the_ID(), 'sk_no_adsense', true); 
		if (empty($value)){
			return true;
		}
		return false;
	}else{
		return false;
	}
}

function is_no_adsense(){
	$value = get_post_meta(get_the_ID(), 'sk_no_adsense', true); 
	if (empty($value)){
		return false;
	}
	return true;
}

function sk_get_ad( $ad_type, $ad_name = '') {
//	if ( $ad_type == 'adsense' )
//		return;

	$filename = STYLESHEETPATH . DIRECTORY_SEPARATOR . 'ad'. DIRECTORY_SEPARATOR;
	
	if ( $ad_name == '' ) {
		$filename .= 'ad';
	} else {
		$filename .= $ad_type . DIRECTORY_SEPARATOR;
	}

	$filename .= $ad_name . '.php';
	if ( file_exists( $filename) ) {
		$text = file_get_contents( $filename);
	} else {
		$text = '';
	}
	return $text;
}

function sk_get_the_ad( $ad_type, $ad_name = '') {
	echo sk_get_ad( $ad_type, $ad_name );
}

function sk_get_access_analy_google() {
	global $g_is_localhost, $g_domain_name, $g_analy_g_acount;
	
	if ( $g_is_localhost ) {
		return;
	}
	
	if ( $g_domain_name === 'komish.com' ) {
/*
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-4079996-8</script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', '<?php echo $g_analy_g_acount;?>', {
		'linker': {
		'domains': ['komish.com', 'plus.komish.com', 'www.paypal.com', 'www.infocart.jp', '17auto.biz/komish/', 'ex-pa.jp'] 
		}
	});
</script>
*/
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $g_analy_g_acount;?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', '<?php echo $g_analy_g_acount;?>', {
		'linker': {
		'domains': ['komish.com', 'plus.komish.com', 'www.paypal.com', 'www.infocart.jp', '17auto.biz/komish/', 'ex-pa.jp'] 
		}
	});
</script>
<script type="text/javascript">jQuery(function() {  
    jQuery("a").click(function(e) {        
        var ahref = jQuery(this).attr('href');
		if (ahref.indexOf("komish.com") != -1 || ahref.indexOf("http") == -1 ) {
			gtag('event', 'click', {
			  'event_category' : 'internal-link',
			  'event_label' : ahref
			 });
		}else{ 
			gtag('event', 'click', {
			  'event_category' : 'external-link',
			  'event_label' : ahref
			 });
		}
	});
});
</script>
<?php
/*
セカンダリ用
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-4079996-8"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'UA-4079996-8', {
		'linker': {
		'accept_incoming': true
		}
	});
</script>
*/

	} else if ( $g_domain_name === 'plus.komish.com') {
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-4079996-23"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'UA-4079996-23', {
		'linker': {
		'domains': ['komish.com', 'plus.komish.com', 'www.infocart.jp', '17auto.biz/komish/'] 
		}
	});
</script>
<script type="text/javascript">jQuery(function() {  
    jQuery("a").click(function(e) {        
        var ahref = jQuery(this).attr('href');
		if (ahref.indexOf("plus.komish.com") != -1 || ahref.indexOf("http") == -1 ) {
			gtag('event', 'click', {
			  'event_category' : 'internal-link',
			  'event_label' : ahref
			 });
		}else{ 
			gtag('event', 'click', {
			  'event_category' : 'external-link',
			  'event_label' : ahref
			 });
		}
	});
});
</script>
<?php
	}
}

function sk_get_johnson_box( $atts, $content = null ) {
	return '<div class="johnson-box">' . do_shortcode( $content ) . '</div>';
}
add_shortcode('johnson', 'sk_get_johnson_box');

// http://www.mag2.com/m/0000279189.html

function sk_get_admlmg() {
	return '<p>メルマガ読者の方は合わせてお読み下さい。</p><p>今日のメルマガ配信は終わっているため、今登録してもこの記事を読むことはできません。</p><p><a href="/checklist-detail?bmg=' . get_the_date('ymd') . '&amp;p=c">それでも次回のメルマガ専用記事を読みたい人はこちらから登録して下さい。</a></p>';
}
add_shortcode('admlmg', 'sk_get_admlmg');

function sk_get_my_malmag_info() {
	return '<p><strong><a href="/checklist-detail?bmg=' . get_the_date('ymd') . '&amp;p=c">メールマガジンの登録がまだの方はこちらから登録して下さい。</a></strong></p>';
}
add_shortcode('malmag', 'sk_get_my_malmag_info');

function sk_get_pccontent( $atts, $content = null ) {
	if ( is_mobile() || is_sk_ktai() ) {
		return "";
	} else {
		$content = do_shortcode( $content );
		return $content;
	}
}
add_shortcode('pccontent', 'sk_get_pccontent');

function sk_get_ktaicontent( $atts, $content = null ) {
	if ( is_mobile() ||  is_sk_ktai() ) {
		$content = do_shortcode( $content );
		return $content;
	} else {
		return "";
	}
}
add_shortcode('ktaicontent', 'sk_get_ktaicontent');

function get_random_ad($fname) {
	if ( $fname === null ) 
		return;
	$var_name = $fname . '_link';
	$class_name = 'random_ad_'. $fname;

	echo '<div id="' . $class_name . '"></div>';
?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri() . '/ad/', $fname; ?>.ad" charset="utf-8"></script><script type="text/javascript" language="javascript">num = Math.floor( Math.random() * <? echo $var_name; ?>.length );document.getElementById("<?php echo $class_name; ?>").innerHTML = <? echo $var_name; ?>[num];</script>
<?php
}

function get_random_ad2() {
?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri() ?>/js/ad_code.js" charset="utf-8"></script>
<?php
	echo '<div id="random_ad"></div>';
?>
<script type="text/javascript" language="javascript">
num = Math.floor( Math.random() * ad_link.length );
document.getElementById("random_ad").innerHTML = ad_link[num];
</script>
<?php
}

function sk_excerpt_more($more) {
	return  '... <a href="'. esc_url( get_permalink() ) . '">続きを読む <span class="meta-nav">&rarr;</span></a>';
}
add_filter('excerpt_more', 'sk_excerpt_more');

function sk_remove_more_jump_link($link) { 
	$offset = strpos($link, '#more-');
	if ($offset) {
		$end = strpos($link, '"',$offset);
	}
	if ($end) {
		$link = substr_replace($link, '', $offset, $end-$offset);
	}
	return $link;
}
add_filter('the_content_more_link', 'sk_remove_more_jump_link');

//本文中の<!--more-->タグをアドセンスに置換
/*
function replace_more_tag($the_content){
    //広告（AdSense）タグを記入
	$ad = sk_get_ad('adsense', 'mg_single_content_in_res');
	$the_content = preg_replace( '/(<p>)?<span id="more-([0-9]+?)"><\/span>(.*?)(<\/p>)?/i', "$ad$0", $the_content );

	return $the_content;
}
add_filter('the_content', 'replace_more_tag');
*/


add_filter('body_class','sk_body_class_adapt',20);

function sk_body_class_adapt( $classes ) {
	// Apply 'sales-letter' class to form_page.php body
	if ( is_page_template( 'page-templates/sales-letter.php' ) || 
			is_page_template( 'templates/sales-letter.php' ) ){
		$classes[] = 'sales-letter';
		$classes[] = 'column-narrow';
		$classes[] = 'content-only';
	}
		
	if ( is_page_template( 'page-templates/sales-letter-full.php' ) ||
			is_page_template( 'templates/sales-letter-full.php' ) ){
		$classes[] = 'content-only';
		$classes[] = 'column-narrow';
		$classes[] = 'no-sidebar';
		$classes[] = 'sales-letter-full';
		// no-scrollable-sidebar
	}
		
	if ( is_page_template( 'page-templates/law.php' ) ||
			is_page_template( 'templates/law.php' ) ){
		$classes[] = 'law';
		$classes[] = 'column-narrow';
		$classes[] = 'content-only';
	}
	
	if ( is_page_template( 'page-templates/education.php' ) ||
			is_page_template( 'templates/education.php' ) ){
		$classes[] = 'education';
		$classes[] = 'column-narrow';
		$classes[] = 'content-only';
	}
	
	if ( is_page_template( 'page-templates/user-info.php' ) ||
			is_page_template( 'page-templates/user-info.php' ) ){
		$classes[] = 'user-info';
		$classes[] = 'column-narrow';
		$classes[] = 'content-only';
	}
	
	return $classes;
}

function sk_get_custom_field( $atts ) {
	extract( shortcode_atts( array(
		'name' => '',
		), $atts));

	return get_post_meta(get_the_ID(), $name, true); 
}
add_shortcode('customval', 'sk_get_custom_field');

function sk_get_custom_field_array( $atts ) {
	extract( shortcode_atts( array(
		'name' => '',
		), $atts));

	return get_post_meta(get_the_ID(), $name, false); 
}

function sk_get_url_param($param) {

	$val = (isset($_GET[$param]) && $_GET[$param] != "") ? $_GET[$param] : null;
	if ( $val === null ) {
		$val = '';
	} else {
		$val = htmlspecialchars($val, ENT_QUOTES);
	}
	return $val;
}

function sk_get_post_param($param) {

	$val = (isset($_POST[$param]) && $_POST[$param] != "") ? $_POST[$param] : null;
	if ( $val === null ) {
		$val = '';
	} else {
		$val = htmlspecialchars($val, ENT_QUOTES);
	}
	return $val;
}

function sk_get_cookie_param($param) {
	$val = (isset($_COOKIE[$param]) && $_COOKIE[$param] != "") ? $_COOKIE[$param] : null;
	if ( $val === null ) {
		$val = '';
	} else {
		$val = htmlspecialchars($val, ENT_QUOTES);
	}
	return $val;
}

function sk_set_widelist($atts, $content = null) {
	return '<div class="widelist">'.do_shortcode( $content ).'</div>';
}
add_shortcode('widelist', 'sk_set_widelist');

function sk_set_checklist($atts, $content = null) {
    extract( shortcode_atts( array(
    	'color' => 'blue'
        ), $atts ));

	if ( $color == 'red' )
		return '<div class="checklist_red">'.do_shortcode( $content ) .'</div>';
	else
		return '<div class="checklist_blue">'.do_shortcode( $content ).'</div>';
	
}
add_shortcode('checklist', 'sk_set_checklist');

/*
function sk_get_product_list($atts, $content = null) {
	return '<ul>' . 
		 wp_list_pages('child_of=900&depth=1&title_li=&sort_column=ID&sort_order=DESC') .
		 '</ul>';
}
add_shortcode('products', 'sk_get_product_list');
*/

function sk_get_page_list($atts, $content = null) {
    extract( shortcode_atts( array(
    	'depth' => '0'
        ), $atts ));

	return '<ul>' . 
		 wp_list_pages('depth=' . $depth . '&child_of=' . get_the_ID() . '&title_li=&echo=0&post_type=page&page_status=publish&sort_column=menu_order&sort_order=ASC') .
		 '</ul>';
}	
add_shortcode('pagelist', 'sk_get_page_list');


function sk_single_page_custom_menu($atts, $content = null) {
    extract(shortcode_atts(array(  
        'menu'            => '', 
        'container'       => 'div', 
        'container_class' => '', 
        'container_id'    => '', 
        'menu_class'      => 'menu', 
        'menu_id'         => '',
        'echo'            => true,
        'fallback_cb'     => 'wp_page_menu',
        'before'          => '',
        'after'           => '',
        'link_before'     => '',
        'link_after'      => '',
        'depth'           => 0,
        'walker'          => '',
        'theme_location'  => ''), 
        $atts));
  
  
    return wp_nav_menu( array( 
        'menu'            => $menu, 
        'container'       => $container, 
        'container_class' => $container_class, 
        'container_id'    => $container_id, 
        'menu_class'      => $menu_class, 
        'menu_id'         => $menu_id,
        'echo'            => false,
        'fallback_cb'     => $fallback_cb,
        'before'          => $before,
        'after'           => $after,
        'link_before'     => $link_before,
        'link_after'      => $link_after,
        'depth'           => $depth,
        'walker'          => $walker,
        'theme_location'  => $theme_location));
}
add_shortcode("cmenu", "sk_single_page_custom_menu");

function custom_search( $search ) {
	global $g_domain_name;
	
	if ( $g_domain_name === 'komish.com' ) {
		if ( is_search() && ! is_admin() ) {
			$search .= " AND post_type = 'post'";
		}
	}
	return $search;
}

add_filter( 'posts_search', 'custom_search' );

function sk_get_twitter_widget($atts, $content = null) {
	if (is_mobile()) {
		return '<a class="twitter-timeline" href="https://twitter.com/shukomiya?ref_src=twsrc%5Etfw" data-lang="ja" data-width="300" data-height="440">Tweets by shukomiya</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
	}else{
		return '<a class="twitter-timeline" href="https://twitter.com/shukomiya?ref_src=twsrc%5Etfw" data-lang="ja" data-width="80%" data-height="600">Tweets by shukomiya</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
	}

}	
add_shortcode('twitter-widget', 'sk_get_twitter_widget');


?>