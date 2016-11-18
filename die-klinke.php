<?php
/**
 * Plugin Name: Die Klinke
 * Description: Related posts Ajax loader plugin.
 * Version: 1.0.0
 * Author: Dejan Batanjac
 * Author URI: https://programming-review.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       die-klinke
 * Domain Path:       /languages
 *
 *
 * Resources:
 * https://wpseek.com/
 * https://wordpress.stackexchange.com
 * https://stackexchange.com
 * https://codex.wordpress.org/
 * https://validator.w3.org/nu/#textarea for checking the HTML5 valid code
 * https://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
 *
 * NOTE: Namespaces are used not so often in WordPress from unknown resaon.
 * Checked many good plugins and many plugin authors. I provided them as a namespace wrapper.
 *
 * TODO: More Unit test cases.
 * TODO: Check the plugin with different plugins in place.
 * TODO: Move the class into the `/src` folder. Leave only the bootstrap 'die-klinke.php' file that loads all needed artefacts.
 *
 * Using namespaces wrapper may be smart but the one drawback is we need to backslash
 * all the WordPress classes \WP_Query
 *
 * @since 1.0.0
 * @link https://programming-review.com
 * @package WW
 */


namespace DK_20161118;

// Incliding the version checker.
define( 'DK_20161118_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DK_20161118_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require  DK_20161118_PLUGIN_DIR . 'src/version-checker.php';
require  DK_20161118_PLUGIN_DIR . 'src/helper.php';


  /**
   * Main class that should be instantiated and used to set our actions and filters.
   * All actions are in the constructor.
   * @since  1.0.0
   */
  class Die_Klinke {

      /**
       * Most important for initialization.
       * @since  1.0.0
       */

      public function __construct() {

          add_action( 'wp_enqueue_scripts', array( $this, 'add_dk_scripts') );

          // "dk_add_related" custom ajax action sufix for both the non auth
          // and authenticated user.
          add_action( 'wp_ajax_dk_add_related', array( $this, 'insert_related' ) );
          add_action( 'wp_ajax_nopriv_dk_add_related', array( $this, 'insert_related' ) );

          // We need this to append the post content.
          add_filter( 'the_content', array( $this, 'append_posts_only' ) );
          add_action( 'plugins_loaded', array( $this, 'init_die_klinke_textdomain' ) );

      }

      /**
       * This method is crutial for importing the i18n to our plugin. We later use this for translating.
       * @since  1.0.0
       * @return void Implicit void.
       *
       * We have translation de_DE for now.
       * There is a tricks I saw in Yoast's WordPress Seo plugin
       * with l`oad_muplugin_textdomain()`
       *
       * This function works on WordPress MU. Tested.
       *
       * TODO: Check why Yoast WordPress SEO used `load_muplugin_textdomain()`?
       */
      public function init_die_klinke_textdomain() {
        load_plugin_textdomain( 'die-klinke', false, basename( DK_20161118_PLUGIN_DIR ) . '/languages' );
      }


      /**
       * Adding the specific anchor whee it reads like "See related articles"
       * @since  1.0.0
       * @param  string $content The existing post content
       * @return string          The new post content
       */
      public function append_posts_only( $content ) {
        // Haven't found is needed to add into the anchor href="javascript:void(0);"
        global $post;
        $post_id = $post->ID;
        $r = $this->get_ids_of_related_posts( $post_id );

        // Case we are on a post, and there are similar posts.
        if ( is_single() && ! empty( $r ) ) {
          $plus = '<a id="similar3">' . __( 'See related articles', 'die-klinke' ) . '</a>' .
          '<div class="similar3container"></div>';
          return $content . $plus;
        } else {
          // If not simlilar posts don't add the anchor.
          return $content;
        }
      }

      /**
       * Returns only the id's as an array if possible, else returns 0.
       * @since  1.0.0
       * @param  integer $id Post ID where we need to find similar posts
       * @return array     The array of ID's
       *
       *
       * We will call this function from at least two places:
       * - (1) inside the Ajax call, and
       * - (2) inside `append_posts_only` because we will not even show 'See related articles'
       * if there are no articles.
       *
       * Use the `meta_query` part to ensure we allways show posts with the featured image.
       * http://wordpress.stackexchange.com/questions/89202/query-posts-only-with-featured-image
       *
       * Possible randomize the results if you uncomment orderby rand part.
       */
      public function get_ids_of_related_posts( $id ) {

        $args = array (
        'post_status'     => 'publish',
        'post_type'       => array( 'post' ),
        'posts_per_page'  => 3, // Need three id's.
        'fields'          => 'ids',
        'category__in'    => wp_get_post_categories( $id ),
        'post__not_in'    => array( $id ),
        //'orderby'         => 'rand', // Possible nice option.
        'order'           => 'DESC',
        'meta_query'      => array(
          array(
           'key'          => '_thumbnail_id',
           'compare'      => 'EXISTS'  ),
         )
        );

        /** The Query like:
        SELECT SQL_CALC_FOUND_ROWS wp_posts.ID FROM wp_posts INNER JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id) INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id ) WHERE 1=1 AND wp_posts.ID NOT IN (13007) AND ( wp_term_relationships.term_taxonomy_id IN (17) ) AND ( wp_postmeta.meta_key = '_thumbnail_id' ) AND wp_posts.post_type = 'post' AND ((wp_posts.post_status = 'publish')) GROUP BY wp_posts.ID ORDER BY wp_posts.post_date DESC LIMIT 0, 3 */
        $query = new \WP_Query( $args );

        if ( empty( $query->posts ) ) {
          return 0;
        } else {
          return $query->posts;
        }

      }// End function().



      /**
       * Getting post artifacts out of the loop.
       * @since  1.0.0
       * @param  integer $id Post ID where we need to find artefacts
       * @return array     An arrya with the following keys: `permalink`, `title`, `thumbnail`, `post_excerpt`
       */
      public function get_post_requested_artefacts( $id ) {

        // Imporant to decode the URL since we may deal with German characters,
        // and we don't know what is in the database.
        // %C3%A4hnliche-artike => ähnliche-artikel
        $permalink       = urldecode( get_permalink( $id ) );
        $title           = get_the_title( $id );

        // We should have the thumbnail but in case no thumbnail should be empty.
        $thumbnail       = get_the_post_thumbnail( $id, 'post-thumbnail' );

        // Since the preview shold be from the content and text only we don't need shortcodes.
        $post_excerpt    = strip_shortcodes( wp_strip_all_tags( get_post_field( 'post_content',  $id ) ) );
        $post_excerpt    = Helper::fix_line_endings( $post_excerpt );

        // Make sure we get only 150 characters and ellipses at the end.
        $post_excerpt    = substr( $post_excerpt, 0, 150 ) . '...';

        // After some tests on different encodings added this.
        // http://stackoverflow.com/questions/3198532/jquery-ajax-call-messes-up-character-encoding
        $post_excerpt    = mb_convert_encoding( $post_excerpt, 'UTF-8', 'UTF-8' );

        return array( 'permalink'    => $permalink,
                      'title'        => $title,
                      'thumbnail'    => $thumbnail,
                      'post_excerpt' => $post_excerpt );

      }// End function().


      /**
       * Getting the HTML5 excerpt for a single post.
       * @since  1.0.0
       * @param  integer $id The Post ID we are interested to get the excerpt.
       * @return html     One HTML5 excerpt containgint the title, default thumbnail and post excerpt.
       *
       * It is always good to separate the View part from the actual logic.
       * This function should create valid HTML5 excerpt.
       *
       * Creates the HTML5 post excerpts based on artefacts such as title, featured
       * image, post_excerpt,  permalink per a single post
       */
      public function get_html5_excerpt( $id ) {

        $r = $this->get_post_requested_artefacts( $id );

        $excerpt  = '<a class="title" href="' . esc_url( $r['permalink'] ) . '">' . esc_html( $r['title'] ) . '</a>';
        $excerpt .= '<a class="thumb" href="' . esc_url( $r['permalink'] ) . '">' . $r['thumbnail'] . '</a>';
        $excerpt .= '<div class="text" >' . esc_attr( $r['post_excerpt'] ) . '</div><hr><br>';

        // Nothing to localize in here as per design...
        printf( $excerpt );

      }


      /**
       * Caller for Ajax action for both authenticated and non authenticated user.
       * @since  1.0.0
       * @return string   Ajax return.
       *
       * NOTE: We used the `empty()` PHP method http://php.net/manual/en/function.empty.php
       *
       * Since PHP 5.5.0+  `empty()` now supports expressions, rather than only variables.
       * I like more to use `empty()` over the `isset()` in the method design.
       *
       * NOTE: We ensured using Postman REST client in case of "bad" cals it returns 0
       * that is normal for WordPress
       */
      public function insert_related() {

        if ( ! empty( $_POST['action'] ) && 'dk_add_related' == $_POST['action'] ) {

          // This function will exit if nonce is bad.
          check_ajax_referer( 'dk-ajax-nonce', 'nonce' );

          // Validate this is integer.
          $post_id = intval( $_POST['post_id'] );

          // Get similar posts and `print` them.
          $post_ids = $this->get_ids_of_related_posts( $post_id );
          foreach ( $post_ids as $id ) {
            $this->get_html5_excerpt( $id );
          }
          wp_die();

        } else { // By no mean we should get in here, but let it be.
          printf( __( 'An error occured.', 'die-klinke' ) );
          wp_die();
        }
      }



      /**
       * Script injector function
       * @since  1.0.0
       * @return void  Implicit void.
       *
       * We will enter this function only if `is_single()` in other words we add custom script only for posts.
       *
       * http://api.jquery.com/jquery.ajax/
       * https://digwp.com/2011/09/using-instead-of-jquery-in-wordpress/
       *
       */
      public function add_dk_scripts() {

        if ( is_single() ) {

          wp_enqueue_style( 'dk-script', DK_20161118_PLUGIN_URL . 'css/dk-default.css', null, '2016-11-11', 'all' );

          wp_enqueue_script( 'dk-script', DK_20161118_PLUGIN_URL . 'js/dk-ajax.js', 'jquery', '2016-11-11', true );

          // We know for sure WordPress uses jquery-core
          $ajaxurl = admin_url( 'admin-ajax.php' );
          global $post;
          $post_id = $post->ID;
          $nonce = wp_create_nonce( 'dk-ajax-nonce' );
          $params  = array( 'ajaxurl' => $ajaxurl, 'nonce' => $nonce,  'post_id' => $post_id );

          wp_localize_script( 'dk-script', 'dk_vars', $params );

        }

      } // End function().

  } // End class{}.

  new Die_Klinke;
