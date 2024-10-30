<?php
/* 
Plugin Name: Image To Break Point 
Plugin URI: 
Description: Image to Break Point resizes images for different breakpoints and loads only if the resolution allows. This plugin uses the library "response.js" from Ryan Van Etten and can also be coupled with the plugin "Multiple Post Thumbnails" from Chris scott order to choose an image according to screen resolution.
Version: 1.0.0
Author: Djib
Author URI: http://www.djib.me
*/  

if (!class_exists('Imagetobreakpoint')) :
    
    class Imagetobreakpoint
    {   
        const ITBP_METABOXES_TITLE = 'Désactiver ITBP pour les résolutions ci-dessous :';

        private $tab_restricted_post_types = array(
            'attachment',
            'nav_menu_item',
            'revision'
        );
    
        public function __construct()  
        {              
            add_action('admin_head', array(&$this, 'admin_start'));
            add_action('admin_menu', array(&$this, 'addConfigMenu'));
            add_action('init', array(&$this, 'init'));
        }
        
        public function admin_start()
        {         
            if(!in_array(get_post_type(), $this->tab_restricted_post_types))
                $this->add_metaboxes();
        }

        public function init()
        {            
            $this->add_multiPostThumbnails();
            $this->add_image_size();
            $this->add_scripts();
            $this->add_filters();

            add_action('save_post', array(&$this, 'itbp_save_metaboxes'));
        }

        public function add_image_size()
        {
            add_image_size('choose_responsive_320', 480, 0, true);
            add_image_size('choose_responsive_480', 640, 0, true);
            add_image_size('choose_responsive_640', 960, 0, true);
            add_image_size('choose_responsive_960', 1024, 0, true);
            add_image_size('choose_responsive_1024', 1200, 0, true);
            add_image_size('choose_responsive_1200', 2000, 0, true);
        }

        public function add_scripts()
        {
            if(!is_admin())
            {
                wp_enqueue_script('itbp_response', plugins_url('js/response.min.js' , __FILE__ ), array('jquery'), false, true);
                wp_enqueue_script('itbp', plugins_url('js/imagetobreakpoint.js' , __FILE__ ), array('jquery'), false, true);
            }
            else
                wp_enqueue_style('itbp', plugins_url('css/imagetobreakpoint.css' , __FILE__ ));
        }

        public function add_filters()
        {
            add_filter('post_thumbnail_html', array(&$this, 'all_responsive_img'), 10);
            //add_filter('image_send_to_editor', array(&$this, 'all_responsive_img'), 10);
        }

        public function all_responsive_img($html) 
        {
            $post = &get_post();
            $post_ID = $post->ID;

            $html = preg_replace( '/(width|height)="\d*"\s/', "", $html );

            $classes = 'img-responsive'; 
            if ( preg_match('/<img.*? class=".*?">/', $html) ) 
                $html = preg_replace('/(<img.*? class=".*?)(".*?>)/', '$1 ' . $classes . '$2', $html);
            else 
                $html = preg_replace('/(<img.*?)>/', '$1 class="' . $classes . '" >', $html);

            $default = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'thumbnail');
            $choose_responsive_320 = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'choose_responsive_320');
            $choose_responsive_480 = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'choose_responsive_480');
            $choose_responsive_640 = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'choose_responsive_640');
            $choose_responsive_960 = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'choose_responsive_960');
            $choose_responsive_1024 = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'choose_responsive_1024');
            $choose_responsive_1200 = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'choose_responsive_1200');

            if (class_exists('MultiPostThumbnails')) : 

                $multi_320 = wp_get_attachment_image_src(MultiPostThumbnails::get_post_thumbnail_id('', 'multi_320', $post_ID), 'choose_responsive_320');
                $choose_responsive_320 = (empty($multi_320)) ? $choose_responsive_320 : $multi_320;

                $multi_480 = wp_get_attachment_image_src(MultiPostThumbnails::get_post_thumbnail_id('', 'multi_480', 79), 'choose_responsive_480');
                $choose_responsive_480 = (empty($multi_480)) ? $choose_responsive_480 : $multi_480;

                $multi_640 = wp_get_attachment_image_src(MultiPostThumbnails::get_post_thumbnail_id('', 'multi_640', $post_ID), 'choose_responsive_640');
                $choose_responsive_640 = (empty($multi_640)) ? $choose_responsive_640 : $multi_640;

                $multi_960 = wp_get_attachment_image_src(MultiPostThumbnails::get_post_thumbnail_id('', 'multi_960', $post_ID), 'choose_responsive_960');
                $choose_responsive_960 = (empty($multi_960)) ? $choose_responsive_960 : $multi_960;

                $multi_1024 = wp_get_attachment_image_src(MultiPostThumbnails::get_post_thumbnail_id('', 'multi_1024', $post_ID), 'choose_responsive_1024');
                $choose_responsive_1024 = (empty($multi_1024)) ? $choose_responsive_1024 : $multi_1024;

                $multi_1200 = wp_get_attachment_image_src(MultiPostThumbnails::get_post_thumbnail_id('', 'multi_1200', $post_ID), 'choose_responsive_1200');
                $choose_responsive_1200 = (empty($multi_1200)) ? $choose_responsive_1200 : $multi_1200;

                //var_dump(MultiPostThumbnails::the_post_thumbnail(get_post_type($post_ID), 'multi_480'));
                //var_dump(MultiPostThumbnails::get_post_thumbnail_id(get_post_type($post_ID), 'multi_480', $post_ID));
            endif;

            $choose_responsive_320 = (preg_match('`itbp_no_320`', $html)) ? $default : $choose_responsive_320;
            $choose_responsive_480 = (preg_match('`itbp_no_480`', $html)) ? $default : $choose_responsive_480;
            $choose_responsive_640 = (preg_match('`itbp_no_640`', $html)) ? $default : $choose_responsive_640;
            $choose_responsive_960 = (preg_match('`itbp_no_960`', $html)) ? $default : $choose_responsive_960;
            $choose_responsive_1024 = (preg_match('`itbp_no_1024`', $html)) ? $default : $choose_responsive_1024;
            $choose_responsive_1200 = (preg_match('`itbp_no_1200`', $html)) ? $default : $choose_responsive_1200;

            $choose_responsive_320 = (get_post_meta($post_ID, 'itbp_metaboxes_320', true) == "1") ? $default : $choose_responsive_320;
            $choose_responsive_480 = (get_post_meta($post_ID, 'itbp_metaboxes_480', true) == "1") ? $default : $choose_responsive_480;
            $choose_responsive_640 = (get_post_meta($post_ID, 'itbp_metaboxes_640', true) == "1") ? $default : $choose_responsive_640;
            $choose_responsive_960 = (get_post_meta($post_ID, 'itbp_metaboxes_960', true) == "1") ? $default : $choose_responsive_960;
            $choose_responsive_1024 = (get_post_meta($post_ID, 'itbp_metaboxes_1024', true) == "1") ? $default : $choose_responsive_1024;
            $choose_responsive_1200 = (get_post_meta($post_ID, 'itbp_metaboxes_1200', true) == "1") ? $default : $choose_responsive_1200;

            if(!preg_match('`choose_responsive_all`', $html)) :

                $html = preg_replace('/(<img.*?)>/', '$1 data-min-width-318="' . $choose_responsive_320[0] . '" >', $html);
                $html = preg_replace('/(<img.*?)>/', '$1 data-min-width-480="' . $choose_responsive_480[0] . '" >', $html);
                $html = preg_replace('/(<img.*?)>/', '$1 data-min-width-640="' . $choose_responsive_640[0] . '" >', $html);
                $html = preg_replace('/(<img.*?)>/', '$1 data-min-width-960="' . $choose_responsive_960[0] . '" >', $html);
                $html = preg_replace('/(<img.*?)>/', '$1 data-min-width-1024="' . $choose_responsive_1024[0] . '" >', $html);
                $html = preg_replace('/(<img.*?)>/', '$1 data-min-width-1200="' . $choose_responsive_1200[0] . '" >', $html);

            endif;

            return $html;
        }

        public function add_metaboxes()
        {
            add_meta_box('itbp_metaboxes', self::ITBP_METABOXES_TITLE, array(&$this, 'itbp_metaboxes'), $v, 'normal', 'core');
        }

        public function itbp_metaboxes($post)
        {
            $itbp_metaboxes_320 = get_post_meta($post->ID, 'itbp_metaboxes_320', true);
            $itbp_metaboxes_480 = get_post_meta($post->ID, 'itbp_metaboxes_480', true);
            $itbp_metaboxes_640 = get_post_meta($post->ID, 'itbp_metaboxes_640', true);
            $itbp_metaboxes_960 = get_post_meta($post->ID, 'itbp_metaboxes_960', true);
            $itbp_metaboxes_1024 = get_post_meta($post->ID, 'itbp_metaboxes_1024', true);
            $itbp_metaboxes_1200 = get_post_meta($post->ID, 'itbp_metaboxes_1200', true);
            ?>

            <label class="itbp_checkbox">
                <input type="hidden" name="itbp_metaboxes_320" value="0" />
                <input type="checkbox" name="itbp_metaboxes_320" id="itbp_metaboxes_320" value="1" <?php echo ($itbp_metaboxes_320 == "1") ? 'checked': ''; ?> />
                320px
            </label>
            <label class="itbp_checkbox">
                <input type="hidden" name="itbp_metaboxes_480" value="0" />
                <input type="checkbox" name="itbp_metaboxes_480" id="itbp_metaboxes_480" value="1" <?php echo ($itbp_metaboxes_480 == "1") ? 'checked': ''; ?> />
                480px
            </label>
            <label class="itbp_checkbox">
                <input type="hidden" name="itbp_metaboxes_640" value="0" />
                <input type="checkbox" name="itbp_metaboxes_640" id="itbp_metaboxes_640" value="1" <?php echo ($itbp_metaboxes_640 == "1") ? 'checked': ''; ?> />
                640px
            </label>
            <label class="itbp_checkbox">
                <input type="hidden" name="itbp_metaboxes_960" value="0" />
                <input type="checkbox" name="itbp_metaboxes_960" id="itbp_metaboxes_960" value="1" <?php echo ($itbp_metaboxes_960 == "1") ? 'checked': ''; ?> />
                960px
            </label>
            <label class="itbp_checkbox">
                <input type="hidden" name="itbp_metaboxes_1024" value="0" />
                <input type="checkbox" name="itbp_metaboxes_1024" id="itbp_metaboxes_1024" value="1" <?php echo ($itbp_metaboxes_1024 == "1") ? 'checked': ''; ?> />
                1024px
            </label>
            <label class="itbp_checkbox">
                <input type="hidden" name="itbp_metaboxes_1200" value="0" />
                <input type="checkbox" name="itbp_metaboxes_1200" id="itbp_metaboxes_1200" value="1" <?php echo ($itbp_metaboxes_1200 == "1") ? 'checked': ''; ?> />
                1200px
            </label>
            <?php
        }

        public function itbp_save_metaboxes($post_ID)
        {
            if(isset($_POST['itbp_metaboxes_320'])) update_post_meta($post_ID, 'itbp_metaboxes_320', $_POST['itbp_metaboxes_320']);
            if(isset($_POST['itbp_metaboxes_480'])) update_post_meta($post_ID, 'itbp_metaboxes_480', $_POST['itbp_metaboxes_480']);
            if(isset($_POST['itbp_metaboxes_640'])) update_post_meta($post_ID, 'itbp_metaboxes_640', $_POST['itbp_metaboxes_640']);
            if(isset($_POST['itbp_metaboxes_960'])) update_post_meta($post_ID, 'itbp_metaboxes_960', $_POST['itbp_metaboxes_960']);
            if(isset($_POST['itbp_metaboxes_1024'])) update_post_meta($post_ID, 'itbp_metaboxes_1024', $_POST['itbp_metaboxes_1024']);
            if(isset($_POST['itbp_metaboxes_1200'])) update_post_meta($post_ID, 'itbp_metaboxes_1200', $_POST['itbp_metaboxes_1200']);
        }

        public function add_multiPostThumbnails()
        {
            if (class_exists('MultiPostThumbnails')) 
            {
                new MultiPostThumbnails(
                    array(
                        'label' => 'Image To Break Point > 320px',
                        'id' => 'multi_320',
                        'post_type' => get_post_type()
                    )
                );
                new MultiPostThumbnails(
                    array(
                        'label' => 'Image To Break Point > 480px',
                        'id' => 'multi_480',
                        'post_type' => get_post_type()
                    )
                );
                new MultiPostThumbnails(
                    array(
                        'label' => 'Image To Break Point > 640px',
                        'id' => 'multi_640',
                        'post_type' => get_post_type()
                    )
                );
                new MultiPostThumbnails(
                    array(
                        'label' => 'Image To Break Point > 960px',
                        'id' => 'multi_960',
                        'post_type' => get_post_type()
                    )
                );
                new MultiPostThumbnails(
                    array(
                        'label' => 'Image To Break Point > 1024px',
                        'id' => 'multi_1024',
                        'post_type' => get_post_type()
                    )
                );
                new MultiPostThumbnails(
                    array(
                        'label' => 'Image To Break Point > 1200px',
                        'id' => 'multi_1200',
                        'post_type' => get_post_type()
                    )
                );
            }
        }
        
        function addConfigMenu()
        {
            add_options_page('imagetobreakpoint', 'Image To Break Point', 10, __FILE__, array(&$this, 'initConfigPage'));
        }
        
        function initConfigPage()
        {
            ?>
            <div class="wrap">
                <?php screen_icon(); ?>
                <h2>Image To Break Point</h2>
            </div>
            <div class="wrap">  

                
            </div>  
            <?php
        }
    }  

    $itbp = new Imagetobreakpoint();
    $itbp->add_multiPostThumbnails();


endif;
?>
