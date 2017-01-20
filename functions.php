<?php
/*
Author: Eddie Machado
URL: http://themble.com/bones/

This is where you can drop your custom functions or
just edit things like thumbnail sizes, header images,
sidebars, comments, etc.
*/

// LOAD BONES CORE (if you remove this, the theme will break)
require_once 'library/bones.php';

// CUSTOMIZE THE WORDPRESS ADMIN (off by default)
// require_once( 'library/admin.php' );

/*********************
LAUNCH BONES
Let's get everything up and running.
*********************/

function bones_ahoy()
{

//Allow editor style.
    add_editor_style(get_stylesheet_directory_uri().'/library/css/editor-style.css');

// let's get language support going, if you need it
// load_theme_textdomain( 'bonestheme', get_template_directory() . '/library/translation' );

// USE THIS TEMPLATE TO CREATE CUSTOM POST TYPES EASILY
//require_once 'library/custom-post-type.php';

        // launching operation cleanup
        add_action('init', 'bones_head_cleanup');
        // A better title
        add_filter('wp_title', 'rw_title', 10, 3);
        // remove WP version from RSS
        add_filter('the_generator', 'bones_rss_version');
        // remove pesky injected css for recent comments widget
        add_filter('wp_head', 'bones_remove_wp_widget_recent_comments_style', 1);
        // clean up comment styles in the head
        add_action('wp_head', 'bones_remove_recent_comments_style', 1);
        // clean up gallery output in wp
        add_filter('gallery_style', 'bones_gallery_style');

        // enqueue base scripts and styles
        add_action('wp_enqueue_scripts', 'bones_scripts_and_styles', 999);
// ie conditional wrapper

// launching this stuff after theme setup
    bones_theme_support();

    // adding sidebars to Wordpress (these are created in functions.php)
    add_action('widgets_init', 'bones_register_sidebars');

    // cleaning up random code around images
    add_filter('the_content', 'bones_filter_ptags_on_images');
    // cleaning up excerpt
    add_filter('excerpt_more', 'bones_excerpt_more');
} /* end bones ahoy */

// let's get this party started
add_action('after_setup_theme', 'bones_ahoy');

/************* OEMBED SIZE OPTIONS *************/
if (!isset($content_width)) {
    $content_width = 960;
}

/************* THUMBNAIL SIZE OPTIONS *************/

// Thumbnail sizes
add_image_size('480', 720, 480, false);
add_image_size('720', 1080, 720, false);
add_image_size('1080', 1920, 1080, false);

/*
to add more sizes, simply copy a line from above
and change the dimensions & name. As long as you
upload a "featured image" as large as the biggest
set width or height, all the other sizes will be
auto-cropped.

To call a different size, simply change the text
inside the thumbnail function.

For example, to call the 300 x 100 sized image,
we would use the function:
<?php the_post_thumbnail( 'bones-thumb-300' ); ?>
for the 600 x 150 image:
<?php the_post_thumbnail( 'bones-thumb-600' ); ?>

You can change the names and dimensions to whatever
you like. Enjoy!
*/

 add_filter('image_size_names_choose', 'bones_custom_image_sizes');

 function bones_custom_image_sizes($sizes)
 {
     return array_merge($sizes, array(
     'bones-thumb-480' => __('480'),
     'bones-thumb-720' => __('720'),
     'bones-thumb-1080' => __('1080'),
     ));
 }

/*
The function above adds the ability to use the dropdown menu to select
the new images sizes you have just created from within the media manager
when you add media to your content blocks. If you add more image sizes,
duplicate one of the lines in the array and name it according to your
new image size.
*/

/************* THEME CUSTOMIZE *********************/

/*
A good tutorial for creating your own Sections, Controls and Settings:
http://code.tutsplus.com/series/a-guide-to-the-wordpress-theme-customizer--wp-33722

Good articles on modifying the default options:
http://natko.com/changing-default-wordpress-theme-customization-api-sections/
http://code.tutsplus.com/tutorials/digging-into-the-theme-customizer-components--wp-27162

To do:
- Create a js for the postmessage transport method
- Create some sanitize functions to sanitize inputs
- Create some boilerplate Sections, Controls and Settings
*/

function bones_theme_customizer($wp_customize)
{
    // $wp_customize calls go here.
//
// Uncomment the below lines to remove the default customize sections

// $wp_customize->remove_section('title_tagline');
// $wp_customize->remove_section('colors');
// $wp_customize->remove_section('background_image');
// $wp_customize->remove_section('static_front_page');
// $wp_customize->remove_section('nav');

// Uncomment the below lines to remove the default controls
// $wp_customize->remove_control('blogdescription');

// Uncomment the following to change the default section titles
// $wp_customize->get_section('colors')->title = __( 'Theme Colors' );
// $wp_customize->get_section('background_image')->title = __( 'Images' );
}

add_action('customize_register', 'bones_theme_customizer');

/************* ACTIVE SIDEBARS ********************/

// Sidebars & Widgetizes Areas
function bones_register_sidebars()
{
    register_sidebar(array(
    'id' => 'sidebar1',
    'name' => __('Sidebar 1', 'bonestheme'),
    'description' => __('The first (primary) sidebar.', 'bonestheme'),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h4 class="widgettitle">',
    'after_title' => '</h4>',
    ));

/*
to add more sidebars or widgetized areas, just copy
and edit the above sidebar code. In order to call
your new sidebar just use the following code:

Just change the name to whatever your new
sidebar's id is, for example:

register_sidebar(array(
'id' => 'sidebar2',
'name' => __( 'Sidebar 2', 'bonestheme' ),
'description' => __( 'The second (secondary) sidebar.', 'bonestheme' ),
'before_widget' => '<div id="%1$s" class="widget %2$s">',
'after_widget' => '</div>',
'before_title' => '<h4 class="widgettitle">',
'after_title' => '</h4>',
));

To call the sidebar in your template, you can just copy
the sidebar.php file and rename it to your sidebar's name.
So using the above example, it would be:
sidebar-sidebar2.php

*/
} // don't remove this bracket!


/************* COMMENT LAYOUT *********************/

// Comment Layout
function bones_comments($comment, $args, $depth)
{
    $GLOBALS['comment'] = $comment;
    ?>
<div id="comment-<?php comment_ID();
    ?>" <?php comment_class('cf');
    ?>>
<article  class="cf">
<header class="comment-author vcard">
<?php
/*
this is the new responsive optimized comment image. It used the new HTML5 data-attribute to display comment gravatars on larger screens only. What this means is that on larger posts, mobile sites don't have a ton of requests for comment images. This makes load time incredibly fast! If you'd like to change it back, just replace it with the regular wordpress gravatar call:
echo get_avatar($comment,$size='32',$default='<path_to_url>' );
*/
?>
<?php // custom gravatar call ?>
<?php
// create variable
$bgauthemail = get_comment_author_email();
    ?>
<img data-gravatar="http://www.gravatar.com/avatar/<?php echo md5($bgauthemail);
    ?>?s=40" class="load-gravatar avatar avatar-48 photo" height="40" width="40" src="<?php echo get_template_directory_uri();
    ?>/library/images/nothing.gif" />
<?php // end custom gravatar call ?>
<?php printf(__('<cite class="fn">%1$s</cite> %2$s', 'bonestheme'), get_comment_author_link(), edit_comment_link(__('(Edit)', 'bonestheme'), '  ', '')) ?>
<time datetime="<?php echo comment_time('Y-m-j');
    ?>"><a href="<?php echo htmlspecialchars(get_comment_link($comment->comment_ID)) ?>"><?php comment_time(__('F jS, Y', 'bonestheme'));
    ?> </a></time>

</header>
<?php if ($comment->comment_approved == '0') : ?>
<div class="alert alert-info">
<p><?php _e('Your comment is awaiting moderation.', 'bonestheme') ?></p>
</div>
<?php endif;
    ?>
<section class="comment_content cf">
<?php comment_text() ?>
</section>
<?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
</article>
<?php // </li> is added by WordPress automatically ?>
<?php

} // don't remove this bracket!


/*
This is a modification of a function found in the
twentythirteen theme where we can declare some
external fonts. If you're using Google Fonts, you
can replace these fonts, change it in your scss files
and be up and running in seconds.
*/
function bones_fonts()
{
    wp_enqueue_style(
        'googleFonts',
        'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic'
    );
}

add_action('wp_enqueue_scripts', 'bones_fonts');

//Page Slug Body Class -aca
function add_slug_body_class($classes)
{
    global $post;
    if (isset($post)) {
        $classes[] = $post->post_name;
    }

    return $classes;
}
add_filter('body_class', 'add_slug_body_class');

/*****
Woo
*****/
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);

function my_theme_wrapper_start()
{
    echo '<main id="main">';
}

function my_theme_wrapper_end()
{
    echo '</main>';
}

add_action('after_setup_theme', 'woocommerce_support');
function woocommerce_support()
{
    add_theme_support('woocommerce');
}
/*****
End Woo
*****/

// social menu
function register_my_menu()
{
    register_nav_menu('social-menu', __('Social Menu'));
}
add_action('init', 'register_my_menu');

// turn off jetpack og: stuff
add_filter('jetpack_enable_opengraph', '__return_false', 99);

// Custom Header -aca
$args = array(
'flex-width' => true,
'flex-height' => true,
'default-image' => get_template_directory_uri().'/library/images/nothing.gif',
);
add_theme_support('custom-header', $args);

// Flexible Video -aca
function bones_responsive_embed($html, $url, $attr, $post_ID)
{
    $return = '<div class="video-container">'.$html.'</div>';

    return $return;
}
add_filter('embed_oembed_html', 'bones_responsive_embed', 10, 4);

function bones_hide_youtube_related_videos($data, $url, $args = array())
{
    $data = preg_replace('/(youtube\.com.*)(\?feature=oembed)(.*)/', '$1?'.apply_filters('hyrv_extra_querystring_parameters', 'wmode=transparent&amp;').'rel=0$3', $data);

    return $data;
}
add_filter('oembed_result', 'bones_hide_youtube_related_videos', 10, 3);

/*
* Remove the WordPress Logo from the WordPress Admin Bar
*/
function remove_wp_logo()
{
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
}
add_action('wp_before_admin_bar_render', 'remove_wp_logo');

/*
* Remove the Comment Bubble from the WordPress Admin Bar
*/
function remove_comment_bubble()
{
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}
add_action('wp_before_admin_bar_render', 'remove_comment_bubble');

/*
* Add Links to My Sites Sub-Menus: Log Out, Media, Links, Pages, Appearance, Plugins, Users, Tools and Settings
*/
function add_mysites_link()
{
    global $wp_admin_bar;
    foreach ((array) $wp_admin_bar->user->blogs as $blog) {
        $menu_id = 'blog-'.$blog->userblog_id;
/* Add a Log Out Link */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-logout',
        'title' => __('Log Out'),
        'href' => get_home_url($blog->userblog_id, '/wp-login.php?action=logout'), ));
/* Media Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-media',
        'title' => __('Media Admin'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/upload.php'), ));
/* Links Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-links',
        'title' => __('Links Admin'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/link-manager.php'), ));
/* Pages Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-pags',
        'title' => __('Pages Admin'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/edit.php?post_type=page'), ));
/* Appearance Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-appearance',
        'title' => __('Appearance'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/themes.php'), ));
/* Plugin Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-plugins',
        'title' => __('Plugin Admin'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/plugins.php'), ));
/* Users Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-users',
        'title' => __('Users Admin'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/users.php'), ));
 /* Tools Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-tools',
        'title' => __('Tools Admin'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/tools.php'), ));
/* Settings Admin */
        $wp_admin_bar->add_menu(array(
        'parent' => $menu_id,
        'id' => $menu_id.'-settings',
        'title' => __('Settings Admin'),
        'href' => get_home_url($blog->userblog_id, '/wp-admin/options-general.php'), ));
    }
}
add_action('wp_before_admin_bar_render', 'add_mysites_link');

/*
Add All Pages & Posts to Admin Bar -aca
*/
add_action( 'admin_bar_menu', 'social_media_links', 900 );
function social_media_links($wp_admin_bar)
{

    $args = array(
        'id'     => 'posts_pages',
        'title' =>  'All Posts & Pages',
        'meta'   => array( 'class' => 'first-toolbar-group' ),
    );
    $wp_admin_bar->add_node( $args );


            $args = array();

            array_push($args,array(
                'id'        =>  'allposts',
                'title'     =>  'Posts',
                'href'      =>  '/wp-admin/edit.php',
                'parent'    =>  'posts_pages',
            ));


            array_push($args,array(
                'id'        => 'allpages',
                'title'     =>  'Pages',
                'href'      =>  '/wp-admin/edit.php?post_type=page',
                'parent'    => 'posts_pages',
            ));

            array_push($args,array(
                'id'        =>  'allmedia',
                'title'     =>  'Media Library',
                'href'      =>  '/wp-admin/upload.php',
                'parent'    =>  'posts_pages',
            ));

            sort($args);
            for($a=0;$a<sizeOf($args);$a++)
            {
                $wp_admin_bar->add_node($args[$a]);
            }



}
/*
END Add All Pages & Posts to Admin Bar -aca
*/

/*
*
* Callback function to filter the MCE settings
*/

function my_mce_before_init_insert_formats( $init_array ) {

// Define the style_formats array

$style_formats = array(
// Each array child is a format with it's own settings
array(
'title' => '1/1',
'block' => 'div',
'classes' => 'm-all t-all d-all cf',
'wrapper' => true,
),
array(
'title' => '1/2',
'block' => 'div',
'classes' => 'm-all t-1of2 d-1of2',
'wrapper' => true,
),
array(
'title' => '1/3',
'block' => 'div',
'classes' => 'm-all t-1of3 d-1of3',
'wrapper' => true,
),
array(
'title' => '1/4',
'block' => 'div',
'classes' => 'm-all t-1of4 d-1of4',
'wrapper' => true,
),
array(
'title' => '1/5',
'block' => 'div',
'classes' => 'm-all t-1of5 d-1of5',
'wrapper' => true,
),
array(
'title' => '1/6',
'block' => 'div',
'classes' => 'm-all t-1of6 d-1of6',
'wrapper' => true,
),
array(
'title' => '1/7',
'block' => 'div',
'classes' => 'm-all t-1of7 d-1of7',
'wrapper' => true,
),
array(
'title' => '1/8',
'block' => 'div',
'classes' => 'm-all t-1of8 d-1of8',
'wrapper' => true,
),
array(
'title' => '1/9',
'block' => 'div',
'classes' => 'm-all t-1of d-1of9',
'wrapper' => true,
),
array(
'title' => '1/10',
'block' => 'div',
'classes' => 'm-all t-1of10 d-1of10',
'wrapper' => true,
),
array(
'title' => '1/11',
'block' => 'div',
'classes' => 'm-all t-1of11 d-1of11',
'wrapper' => true,
),
array(
'title' => '1/12',
'block' => 'div',
'classes' => 'm-all t-1of12 d-1of12',
'wrapper' => true,
),

);
// Insert the array, JSON ENCODED, into 'style_formats'
$init_array['style_formats'] = json_encode( $style_formats );

return $init_array;

}
// Attach callback to 'tiny_mce_before_init'
add_filter( 'tiny_mce_before_init', 'my_mce_before_init_insert_formats' );

add_filter( 'auto_update_plugin', '__return_true' );

/* DON'T DELETE THIS CLOSING TAG */ ?>
