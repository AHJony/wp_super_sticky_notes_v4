<?php
/*
* wp_super_sticky_notes Class 
*/

if (!class_exists('wp_super_sticky_notesClass')) {
    class wp_super_sticky_notesClass{
        public $plugin_url;
        public $plugin_dir;
        public $wpdb;
        public $option_tbl; 
        
        /**Plugin init action**/ 
        public function __construct() {
            global $wpdb;
            $this->plugin_url 				= wp_super_sticky_notesURL;
            $this->plugin_dir 				= wp_super_sticky_notesDIR;
            $this->wpdb 					= $wpdb;	
            $this->option_tbl               = $this->wpdb->prefix . 'options';
            $this->super_sticky_notes_tbl   = $this->wpdb->prefix . 'super_sticky_notes';
         
            $this->init();
        }

        protected static $instance = NULL;
        public static function get_instance()
        {
            if ( NULL === self::$instance )
                self::$instance = new self;

            return self::$instance;
        }
        public function init(){

            //Backend Script
            add_action( 'admin_enqueue_scripts', array($this, 'larasoftNote_backend_script') );
            //Frontend Script
            add_action( 'wp_enqueue_scripts', array($this, 'larasoftbd_Note_frontend_script') );

            //Add Menu Options
            add_action('admin_menu', array($this, 'sticky_notes_admin_menu_function'), 9999);

            /* Add Theme Options to Admin Bar */ 
            add_action('admin_bar_menu', array($this, 'sticky_notes_admin_bar_menu_function'), 55);
            // learndash submenus
            // add_filter('learndash_submenu', array($this, 'irAddSubmenuItem'), 250);
            // add_filter('learndash_header_data', array($this, 'admin_header'), 45, 3);

            /* Send item field */ 
            add_action('wp_ajax_nopriv_sendtonotesajax', array($this, 'sendtonotesajax'));
            add_action( 'wp_ajax_sendtonotesajax', array($this, 'sendtonotesajax') );

            /* Send item to allcomment */ 
            add_action('wp_ajax_nopriv_allcommentajax', array($this, 'allcommentajax'));
            add_action( 'wp_ajax_allcommentajax', array($this, 'allcommentajax') );

            /* Send item to deletecomment */ 
            add_action('wp_ajax_nopriv_deletecommentajax', array($this, 'deletecommentajax'));
            add_action( 'wp_ajax_deletecommentajax', array($this, 'deletecommentajax') );

            /* Send item to like */ 
            add_action('wp_ajax_nopriv_likeajax', array($this, 'likeajax'));
            add_action( 'wp_ajax_likeajax', array($this, 'likeajax') );

            /* Send item to unlike */ 
            add_action('wp_ajax_nopriv_unlikeajax', array($this, 'unlikeajax'));
            add_action( 'wp_ajax_unlikeajax', array($this, 'unlikeajax') );

            // New Comment Via ajax without page reload
            add_action('wp_ajax_nopriv_newCommentViaAjax', array($this, 'newCommentViaAjax'));
            add_action( 'wp_ajax_newCommentViaAjax', array($this, 'newCommentViaAjax') );


            //note save 
            add_action('admin_init', array($this, 'notes_save_create_db'));

            //Store logininid to cookies
            add_action('init', array($this, 'storeloginidtocookies'));

            //add filter the content to append notes
            add_filter( 'the_content', array($this, 'filter_the_content_in_the_main_loop') );

            // Shortcode for frontend use
            add_shortcode( 'all-sticky-comments', array($this, 'larasoftbd_question_lists_shortcode') );

            //button
            add_action('wp_footer', array($this, 'user_button') );

            //avatar defaults
            add_filter( 'avatar_defaults', array( $this, 'mytheme_default_avatar' ), 102, 1 );

            // add_action('wp_head', array($this, 'testFunction'));

        }


        public function testFunction(){

            global $wp_roles;


            
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            echo 'user : ' . $role = $roles[0];
            echo '</br>';


            
            global $wp_roles;
            $roles = $wp_roles->get_names();
            foreach($roles as $key => $role) {
                echo 'key : ' . $key . '.   value : ' .$role . '</br>';
            }

            // $user_data = get_userdata($user);
            // $user_role_slug = $user_data->roles[0];
            // echo $user_role_name = translate_user_role($wp_roles->roles[$user_role_slug]['name']);
            // echo '</br>';



            
            global $wp_roles;

            $all_roles = $wp_roles->roles;
            echo 'jony : ';
            echo '<pre>';
            print_r($all_roles);
            echo '</pre>';
            
            $power_to_reply_ur_name = array();
            if ( get_option( 'power_to_reply_ur_name') !== false ) {
                $power_to_reply_ur_name = get_option( 'power_to_reply_ur_name');
            }
            $power_to_reply_ur_name = array_map('strtolower',$power_to_reply_ur_name);
            $power_to_reply_ur_name = str_replace(' ', '_', $power_to_reply_ur_name);
            echo '<pre>';
            print_r($power_to_reply_ur_name);
            echo '</pre>';
        }

        function add_menu_pages(){

            $user_meta = get_userdata(get_current_user_id());
            $user_roles = $user_meta->roles;
        
            if( $user_roles[0] == 'wdm_instructor' )
            {
                add_role('wdm_instructor', __(
                    'Instructor'),
                    array(
                        'read'            => true, // Allows a user to read
                        )
                 );
            }
        }


        public function newCommentViaAjax(){
            /*
            * New Comment Via ajax without page load
            */
            echo 'test omar from ajax';
            wp_die();
        }


        /*
        * its append add action line 62
        * Store Login-id to cookies
        * its save user note colors. in his Browser
        */
        public function storeloginidtocookies(){
            if(is_user_logged_in()){
                setcookie("sticky_id", get_current_user_id() ,time()+31556926 ,'/');// where 31556926 is total seconds for a year.
            }
        }

        /*
        * its append add action line 41
        * Admin Menu
        */
        function sticky_notes_admin_menu_function(){
            if( is_user_logged_in() ) {
                $user = wp_get_current_user();
                $roles = ( array ) $user->roles;
                $role = $roles[0];
                if ( $role == 'administrator' ){
                    add_menu_page( 'All Sticky Notes', 'All Sticky Notes', 'read', 'sticky-notes-admin-menu', array($this, 'submenufunction'), 'dashicons-list-view', 50 );
                }
                
                $power_to_reply_ur_name = array();
                if ( get_option( 'power_to_reply_ur_name') !== false ) {
                    $power_to_reply_ur_name = get_option( 'power_to_reply_ur_name');
                }
                $power_to_reply_ur_name = array_map('strtolower',$power_to_reply_ur_name);
                $power_to_reply_ur_name = str_replace(' ', '_', $power_to_reply_ur_name);

                if( in_array( $role ,$power_to_reply_ur_name ) ){
                    add_menu_page( 'Sticky Comments', 'Sticky Comments', 'read', 'sticky-notes-menu', array($this, 'powerreplyfunction'), 'dashicons-list-view', 50 );
                }
            }
        }

		/**
		 * Control visibility of submenu items
		 *
		 * @since 3.1.0
		 *
		 * @param array $submenu Submenu item to check.
		 * @return array $submenu
		 */
		public function irAddSubmenuItem( $submenu ) {
			if (! isset($submenu['sticky-notes-menu'])) {
				$submenu_save = $submenu;
				$submenu      = array();

				$submenu['sticky-notes-menu'] = array(
					'name'  => 'Sticky Comments',
					'cap'   => 'read',
					'link'  => 'admin.php?page=sticky-notes-menu',
					'class' => 'submenu-sticky-notes-overview',
				);

				$submenu = array_merge($submenu, $submenu_save);
			}

			return $submenu;
		}

		/**
		 * Filter the admin header data. We don't want to show the header panel on the Overview page.
		 *
		 * @since 3.0
		 * @param array $header_data Array of header data used by the Header Panel React app.
		 * @param string $menu_key The menu key being displayed.
		 * @param array $menu_items Array of menu/tab items.
		 *
		 * @return array $header_data.
		 */
		public function admin_header( $header_data = array(), $menu_key = '', $menu_items = array() ) {
			// Clear out $header_data if we are showing our page.
			if ( $menu_key === 'admin.php?page=sticky-notes-menu' ) {
				$header_data = array();
			}

			return $header_data;
		}


        // its append add action line 44
        // Add Theme Options to Admin Bar Menu
        // https://heera.it/customize-admin-menu-bar-in-wordpress
        function sticky_notes_admin_bar_menu_function() {

            global $wp_admin_bar;
            global $post;

            if(!isset($post->ID)){
                return;
            }

            $restrict_ur_name = array();
            if ( get_option( 'restrict_ur_name') !== false ) {
            $restrict_ur_name = get_option( 'restrict_ur_name');
            }
            $restrict_ur_name = array_map('strtolower',$restrict_ur_name);
            $restrict_ur_name = str_replace(' ', '_', $restrict_ur_name);
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            $role = $roles[0];
            if( in_array( $role ,$restrict_ur_name ) ){
                return false;
            }
            $href_admin = ( $role == 'administrator' ) ? '-admin-' : '-';

            $oldCommentUrl = get_the_permalink( get_option( 'allcommentpage', 1 ) );

            $wp_admin_bar->add_menu( array(
                'id'        => 'admin_bar_custom_menu',
                'title'     => '<span class="ab-icon"></span>'.__( 'Sticky Notes', 'some-textdomain' ),
                'href'      => '#'
            ) );
            $wp_admin_bar->add_menu( array(
                'parent'    => 'admin_bar_custom_menu',
                'id'        => 'note_new_comment',
                'title'     => __( 'New Comment', 'some-textdomain' ),
                'href'      => get_the_permalink( $post->ID ) . '?note=1',
              
            ) );
            $wp_admin_bar->add_menu( array(
                'parent'    => 'admin_bar_custom_menu',
                'id'        => 'note_old_comments',
                'title'     => __( 'Old Comments', 'some-textdomain' ),
                'href'      => $oldCommentUrl
            ) );
            $wp_admin_bar->add_menu( array(
                'id'        => 'admin_bar_custom_menu2',
                'title'     => '<span class="ab-icon"></span>'.__( 'All Sticky Notes', 'some-textdomain' ),
                'href'      => 'admin.php?page=sticky-notes' . $href_admin . 'menu'
            ) );

        }

        /*
        * its append add action line 36
        * Appointment backend Script
        */
        function larasoftNote_backend_script($hook){
            if( $hook != 'toplevel_page_sticky-notes-admin-menu' && $hook != 'toplevel_page_sticky-notes-menu' ) return false;

            global $wpdb;
            $table_name = $wpdb->prefix . 'super_sticky_notes';
            $top_pages = $this->wpdb->get_results( "SELECT `page_id`, `title`, COUNT(`page_id`) AS num_shoes FROM $table_name GROUP BY `page_id` ORDER BY num_shoes DESC LIMIT 10", OBJECT);
            $top_page = json_decode(json_encode($top_pages), true);

            $top_page_title = array();
            $top_page_num_shoes = array();
            $count = 1;
            foreach($top_page as $single){
                $top_page_title[$count] = $single['title'];
                $top_page_num_shoes[$count] = $single['num_shoes'];
                $count++;
            }

            $top_users = $this->wpdb->get_results( "SELECT user_id, COUNT(user_id) AS num_shoes FROM $table_name GROUP BY user_id ORDER BY num_shoes DESC LIMIT 10", OBJECT);
            $top_users_name = array();
            $top_users_comment = array();
            $counter = 1;
            foreach($top_users as $single){
                $user = get_user_by('id', $single->user_id);
                $top_users_name[$counter] =  $user->user_nicename;
                $top_users_comment[$counter] = $single->num_shoes;
                $counter++;
            }

            $all_user_comment_likes_unlikes = $this->wpdb->get_results( "SELECT `id`, `user_id`, `comment_user_like`, `comment_admin_like`, `comment_user_unlike`, `comment_admin_unlike` FROM $table_name", OBJECT);
            $top_user_like = array();
            $top_user_unlike = array();
            $top_admin_like = array();
            $top_admin_unlike = array();
            foreach($all_user_comment_likes_unlikes as $single_like_comment){
                $top_user_likes = $single_like_comment->comment_user_like;
                $top_user_likess = explode(',', $top_user_likes);
                $top_user_like[$single_like_comment->id] = count(array_filter($top_user_likess));

                $top_user_unlikes = $single_like_comment->comment_user_unlike;
                $top_user_unlikess = explode(',', $top_user_unlikes);
                $top_user_unlike[$single_like_comment->id] = count(array_filter($top_user_unlikess));
                
                $top_admin_likes = $single_like_comment->comment_admin_like;
                $top_admin_likess = explode(',', $top_admin_likes);
                $top_admin_like[$single_like_comment->id] = count(array_filter($top_admin_likess));

                $top_admin_unlikes = $single_like_comment->comment_admin_unlike;
                $top_admin_unlikess = explode(',', $top_admin_unlikes);
                $top_admin_unlike[$single_like_comment->id] = count(array_filter($top_admin_unlikess));
            }


            if(count($top_user_like) > 0) $top_user_like_ids = array_keys($top_user_like, max($top_user_like));
            if(count($top_user_unlike) > 0) $top_user_unlike_ids = array_keys($top_user_unlike, max($top_user_unlike));
            if(count($top_admin_like) > 0) $top_admin_like_ids = array_keys($top_admin_like, max($top_admin_like));
            if(count($top_admin_unlike) > 0) $top_admin_unlike_ids = array_keys($top_admin_unlike, max($top_admin_unlike));

            $top_user_like_note = $this->wpdb->get_results( "SELECT `user_id`, `note_values`, `comment_user_like` FROM $table_name WHERE `id` = $top_user_like_ids[0]", OBJECT);
            $top_user_like_note = json_decode(json_encode($top_user_like_note), true);

            $top_user_unlike_note = $this->wpdb->get_results( "SELECT `user_id`, `note_values`, `comment_user_unlike` FROM $table_name WHERE `id` = $top_user_unlike_ids[0]", OBJECT);
            $top_user_unlike_note = json_decode(json_encode($top_user_unlike_note), true);

            foreach($top_user_like_note as $single){
                $user = get_user_by('id', $single['user_id']);
                $top_user_like_name =  $user->user_nicename;
                $top_user_like_values =  $single['note_values'];
                $top_user_likes =  $single['comment_user_like'];
                $top_user_likes = explode(',', $top_user_likes);
                $top_user_likess = count(array_filter($top_user_likes));
            }
            foreach($top_user_unlike_note as $single){
                $user = get_user_by('id', $single['user_id']);
                $top_user_unlike_name =  $user->user_nicename;
                $top_user_unlike_values =  $single['note_values'];
                $top_user_unlikes =  $single['comment_user_unlike'];
                $top_user_unlikes = explode(',', $top_user_unlikes);
                $top_user_unlikess = count(array_filter($top_user_unlikes));
            }
            $user_name = array();
            array_push($user_name, $top_user_like_name, $top_user_unlike_name);
            $user_comment = array();
            array_push($user_comment, $top_user_like_values, $top_user_unlike_values);
            $user_like_unlike = array();
            array_push($user_like_unlike, $top_user_likess, $top_user_unlikess);

            wp_enqueue_style( 'dataTableCSS', 'https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.css', array(), true, 'all' );
            wp_enqueue_style( 'fontawesomeCSS', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), true, 'all' );
            wp_enqueue_style( 'larasoftNoteCSS', $this->plugin_url . 'asset/css/note_backend.css', array(), true, 'all' );
            
            
            wp_enqueue_script( 'amchartsjsCore', 'https://cdn.amcharts.com/lib/4/core.js', array(), time(), true);
            wp_enqueue_script( 'amchartsjs', 'https://cdn.amcharts.com/lib/4/charts.js', array(), time(), true);
            
            wp_enqueue_script( 'amchartsjsAnimated', 'https://cdn.amcharts.com/lib/4/themes/animated.js', array(), time(), true);
            
            wp_enqueue_script( 'dataTableJS', 'https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.js', array(), time(), true);
            wp_enqueue_script( 'larasoftNote', $this->plugin_url . 'asset/js/ls_note_backend.js', array(), true );
            //ajax
            wp_localize_script( 'larasoftNote', 'notesAjax',
            array(
                'ajax' => admin_url( 'admin-ajax.php' ),
                'top_page_title' => $top_page_title,
                'top_page_num_shoes' => $top_page_num_shoes,
                'top_users_name' => $top_users_name,
                'top_users_comment' => $top_users_comment,
                'user_name' => $user_name,
                'user_comment' => $user_comment,
                'user_like_unlike' => $user_like_unlike,
                )
            );

            //Core media script
            wp_enqueue_media();
            wp_enqueue_editor();

            // Your custom js file
            wp_register_script( 'media-lib-uploader-js', plugins_url( 'media-lib-uploader.js' , __FILE__ ), array('jquery') );

        }

        /*
        * its append add action line 38
        * Appointment frontend Script
        * And we send All note value in javascript from ajax.
        */
        function larasoftbd_Note_frontend_script(){
            global $post;
            global $wp;

            $noteoptions = array();
            if(isset($_COOKIE['noteoptions'])):
            $noteoptions = json_decode(stripcslashes($_COOKIE['noteoptions']));
            endif;

          
            $current_user_id = get_current_user_id();
            $current_page_id = $post->ID;
            $current_page_url = get_permalink( $current_page_id );
            $page_author_id = get_post_field( 'post_author', $current_page_id );

            $status = (isset($_REQUEST['note']) && $_REQUEST['note'] == 1) ? 'active' : '';

            $table_name = $this->super_sticky_notes_tbl;
            $table_users = $this->wpdb->prefix . 'users';
            $ary = "SELECT ssn.*, u.`user_nicename`, DATE_FORMAT(ssn.`insert_time`,'%d/%m/%Y') AS `insert_time`, DATE_FORMAT(ssn.`note_repliedOn`,'%d/%m/%Y') AS note_repliedOn FROM $table_name ssn";
            $ary .= " LEFT JOIN $table_users u ON u.`ID`=ssn.`user_id`";
            $ary .= $this->wpdb->prepare(" WHERE ssn.`page_id` = %d", $current_page_id);
            if(get_option( 'visitor_allowed', 0 ) != 1) $ary .= $this->wpdb->prepare(" AND `user_id` = %s", $current_user_id);

            $note_values = $this->wpdb->get_results($ary, OBJECT);
             
            // $page_users = $this->$wpdb->get_results( "SELECT `user_id` FROM $table_name WHERE `page_id` = $current_page_id",  OBJECT);
           
            $next_conv_allowed = $note_values;

            
            $next_conv_alloweds = array();
            $note_date = array();
            $replay_date = array();
            $note_user = array();
            $notes = array();
            $note_user_avatar_url = array();

            $note_admin_name = array();
            $note_reply_admin_avatar_url = array();

            foreach($note_values as $single){

               
                $user = get_user_by('id', $single->user_id);
                $next_conv_alloweds[$single->id] = $single->next_conv_allowed;
                $note_date[$single->id] = date('d/m/Y', strtotime($single->insert_time));
                $replay_date[$single->id] = date('d/m/Y', strtotime($single->note_repliedOn));
                $note_user[$single->id] = $user->user_nicename;
                $notes[$single->id] = $single;
                $note_user_avatar_url[$single->user_id] = esc_url( get_avatar_url( $single->user_id ) );

                $admin_user = get_user_by('id', $single->note_reply_admin_role);

                $note_admin_name[$single->id] = ( $admin_user->roles[0] == 'administrator') ? 'Admin' : $admin_user->user_nicename;
                $note_reply_admin_avatar_url[$single->id] = esc_url( get_avatar_url( $single->note_reply_admin_role ) );
            }

            $note_admin_avatar_url = '';
            if ( get_option( 'wp_ssn_note_admin_avatar') !== false ) {
                $note_admin_avatar_url = get_option('wp_ssn_note_admin_avatar');
            }
            //echo 'wp_ssn_note_admin_avatar : ' . $note_admin_avatar_url;

            /*
            * Private Comments Allowed / not
            */
            $private_comment = false;
            if(in_array($post->ID, get_option( 'allow_private_for_post', array() ))){
                $private_comment = get_option( 'private_comment', 1 );
            }
            if(in_array('all', get_option( 'allow_private_for_post', array() ))){
                $private_comment = get_option( 'private_comment', 1 );
            }
            $categorys = wp_get_post_categories( $post->ID );
            $haveValue = array_intersect($categorys, get_option( 'allow_private_for_categori', array()));
            if(count($haveValue) > 0){
                $private_comment = get_option( 'private_comment', 1 );
            }

            $restrict_ur_name = array();
            if ( get_option( 'restrict_ur_name') !== false ) {
                $restrict_ur_name = get_option( 'restrict_ur_name');
            }
            $user_restrict_alert = '';
            $restrict_ur_name = array_map('strtolower',$restrict_ur_name);
            $restrict_ur_name = str_replace(' ', '_', $restrict_ur_name);

            if ( is_user_logged_in() ){
                $user = wp_get_current_user();
                $roles = ( array ) $user->roles;
                $role = $roles[0];
                if( in_array( $role ,$restrict_ur_name ) ){
                    $user_restrict_alert = 'your_restricted';
                }
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'super_sticky_notes';

            $user_comment_likes_unlikes = $this->wpdb->get_results( "SELECT `id`, `user_id`, `comment_user_like`, `comment_admin_like`, `comment_user_unlike`, `comment_admin_unlike` FROM $table_name WHERE `page_id` = $current_page_id", OBJECT);
            $comment_user_like = array();
            $comment_user_unlike = array();
            $comment_admin_like = array();
            $comment_admin_unlike = array();
            
            $user_active_like = array();
            $user_active_unlike = array();
            $admin_active_like = array();
            $admin_active_unlike = array();
            foreach($user_comment_likes_unlikes as $single){
                $user_like = $single->comment_user_like;
                $user_likes = explode(',', $user_like);
                $comment_user_like[$single->id] = count(array_filter($user_likes));
                $user_unlike = $single->comment_user_unlike;
                $user_unlikes = explode(',', $user_unlike);
                $comment_user_unlike[$single->id] = count(array_filter($user_unlikes));
                $admin_like = $single->comment_admin_like;
                $admin_likes = explode(',', $admin_like);
                $comment_admin_like[$single->id] = count(array_filter($admin_likes));
                $admin_unlike = $single->comment_admin_unlike;
                $admin_unlikes = explode(',', $admin_unlike);
                $comment_admin_unlike[$single->id] = count(array_filter($admin_unlikes));
            }

            $all_user_comment_likes_unlikes = $this->wpdb->get_results( "SELECT `id`, `user_id`, `comment_user_like`, `comment_admin_like`, `comment_user_unlike`, `comment_admin_unlike` FROM $table_name", OBJECT);
            $top_user_like = array();
            $top_user_unlike = array();
            $top_admin_like = array();
            $top_admin_unlike = array();
            
            foreach($all_user_comment_likes_unlikes as $single_like_comment){
                $top_user_likes = $single_like_comment->comment_user_like;
                $top_user_likess = explode(',', $top_user_likes);
                $top_user_like[$single_like_comment->id] = count(array_filter($top_user_likess));

                $top_user_unlikes = $single_like_comment->comment_user_unlike;
                $top_user_unlikess = explode(',', $top_user_unlikes);
                $top_user_unlike[$single_like_comment->id] = count(array_filter($top_user_unlikess));
                
                $top_admin_likes = $single_like_comment->comment_admin_like;
                $top_admin_likess = explode(',', $top_admin_likes);
                $top_admin_like[$single_like_comment->id] = count(array_filter($top_admin_likess));

                $top_admin_unlikes = $single_like_comment->comment_admin_unlike;
                $top_admin_unlikess = explode(',', $top_admin_unlikes);
                $top_admin_unlike[$single_like_comment->id] = count(array_filter($top_admin_unlikess));
            }

            if(count($top_user_like) > 0) $top_user_like_ids = array_keys($top_user_like, max($top_user_like));
            if(count($top_user_unlike) > 0) $top_user_unlike_ids = array_keys($top_user_unlike, max($top_user_unlike));
            if(count($top_admin_like) > 0) $top_admin_like_ids = array_keys($top_admin_like, max($top_admin_like));
            if(count($top_admin_unlike) > 0) $top_admin_unlike_ids = array_keys($top_admin_unlike, max($top_admin_unlike));
            $top_user_like_id = (count($top_user_like) > 0) ? implode(",", $top_user_like_ids) : '';
            $top_user_unlike_id = (count($top_user_unlike) > 0) ? implode(",",$top_user_unlike_ids) : '';
            $top_admin_like_id = (count($top_admin_like) > 0) ? implode(",",$top_admin_like_ids) : '';
            $top_admin_unlike_id = (count($top_admin_unlike) > 0) ? implode(",",$top_admin_unlike_ids) : '';


            $wssn_restrict_number = 20;
            if ( get_option( 'wssn_restrict_number', 20 ) !== false ){
                $wssn_restrict_number = get_option('wssn_restrict_number', 20);
            }

            $rich_text_editor = array();
            if ( get_option( 'rich_text_editor') !== false ) {
                $rich_text_editor = get_option( 'rich_text_editor');
            }

            wp_enqueue_style( 'larasoftbd_NotetCSS', $this->plugin_url . 'asset/css/note_frontend.css', array(), true, 'all' );
            
            // Add the styles first, in the <head> (last parameter false, true = bottom of page!)
            wp_enqueue_style('qtip', $this->plugin_url . 'asset/qtip_asset/jquery.qtip.min.css', null, false, false);

            // Not using imagesLoaded? :( Okay... then this.
            //we used qtip here.
            wp_enqueue_script('qtipjs', $this->plugin_url . 'asset/qtip_asset/jquery.qtip.min.js', array(), time(), true);
            wp_enqueue_script('larasoftbd_NoteJS', $this->plugin_url . 'asset/js/ls_note_frontend.js', array('jquery'), time(), false);
            // wp_enqueue_script('tinymceJS', '//cdn.tinymce.com/4/tinymce.min.js', array('jquery'), time(), true);
            wp_enqueue_editor();
            //ajax
            wp_localize_script( 'larasoftbd_NoteJS', 'notesAjax', 
                array(
                    'ajax' => admin_url( 'admin-ajax.php' ),
                    'current_page_id' => $post->ID,
                    'page_author_id' => $page_author_id,
                    'user_id' => $current_user_id,
                    'title' => get_the_title(),
                    'login_status' => (is_user_logged_in()) ? 'login':'logout',
                    'nottopcolor' => (isset($noteoptions->topoption)) ? $noteoptions->topoption : '',
                    'notetextbg' => (isset($noteoptions->texteditorbg)) ? $noteoptions->texteditorbg : '',
                    'submitorreply' => $next_conv_alloweds,
                    'priv' => __('Make Private', 'notes'),
                    'private_comment' => $private_comment,
                    'status' => $status,
                    'replay_date' => $replay_date,
                    'note_date' => $note_date,
                    'current_page_url' => $current_page_url,
                    'notes' => $notes,
                    'priv_message' => __('Saved your comments list.', 'notes'),
                    'note_user' => $note_user,
                    'note_user_avatar_url' => $note_user_avatar_url,
                    'note_admin_avatar_url' => $note_admin_avatar_url,
                    'login_alert' => __('Please login to comment', 'notes'),
                    'restrict_alert' => __('Sorry, the administrator did not allow you to comment.', 'notes'),
                    'user_restrict_alert' => $user_restrict_alert,
                    'comment_user_like' => $comment_user_like,
                    'comment_user_unlike' => $comment_user_unlike,
                    'comment_admin_like' => $comment_admin_like,
                    'comment_admin_unlike' => $comment_admin_unlike,
                    'user_active_like' => $user_active_like,
                    'user_active_unlike' => $user_active_unlike,
                    'admin_active_like' => $admin_active_like,
                    'admin_active_unlike' => $admin_active_unlike,
                    'wssn_restrict_number' => $wssn_restrict_number,
                    'top_user_like_id' => $top_user_like_id,
                    'top_user_unlike_id' => $top_user_unlike_id,
                    'top_admin_like_id' => $top_admin_like_id,
                    'top_admin_unlike_id' => $top_admin_unlike_id,
                    'rich_text_editor' => $rich_text_editor,
                    'note_admin_name' => $note_admin_name,
                    'note_reply_admin_avatar_url' => $note_reply_admin_avatar_url,
                )
            );
            
        }

        // its append add action line 59
        // sql data save queries 
        // all note and our all data save this db.
        function notes_save_create_db() {

            $charset_collate = $this->wpdb->get_charset_collate();

            $note_table = $this->super_sticky_notes_tbl;
            // $this->wpdb->query("DROP TABLE $note_table");

            $sql = "CREATE TABLE $note_table ( 
                id INT(20) NOT NULL AUTO_INCREMENT,
                user_id INT(20) NOT NULL,
                page_id VARCHAR(200) NOT NULL,
                page_author_id VARCHAR(200) NOT NULL,
                parent_class VARCHAR(50) NOT NULL,
                current_Class VARCHAR(50) NOT NULL,
                note_position INT(20) NOT NULL,
                note_values TEXT NOT NULL,
                insert_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                title VARCHAR(100) NOT NULL, 
                note_status VARCHAR(20) NOT NULL,
                note_reply_admin_role INT(20) NOT NULL,
                note_reply TEXT NOT NULL,
                note_repliedOn VARCHAR(20) NOT NULL,
                next_conv_allowed INT(5) NOT NULL,
                comment_user_like TEXT NOT NULL,
                comment_admin_like TEXT NOT NULL,
                comment_user_unlike TEXT NOT NULL,
                comment_admin_unlike TEXT NOT NULL,
                parent_id INT(20) NOT NULL,
                desable INT(5) NOT NULL DEFAULT 0,
                priv INT(5) NOT NULL DEFAULT 0,
                UNIQUE KEY id (id)
                ) $charset_collate;";
        
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            // DROP TABLE `gym_super_sticky_notes`
        }

        // its append add action line 50
        // ajax get all comment page id save
        function allcommentajax(){
            $page_id = get_option( 'allcommentpage', 1 );
            $page_url = get_permalink( $page_id );
            echo json_encode(
                array(
                    'message' => 'success',
                    'page_url' => $page_url
                )
            );
            die();           
        }

        // its append add action line 54
        // delete comment ajax
        function deletecommentajax(){
           $position = $_POST['position'];
           $table_name = $this->super_sticky_notes_tbl;
           $this->wpdb->delete( $table_name, [ 'note_position' => $position] );

            echo json_encode(
                array(
                    'message' => 'success',
                    'position' => $position
                )
            );
            die();           
        }

        function likeajax(){
            if(!is_user_logged_in()){
                return false;
            }
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'super_sticky_notes';
            
            $parntid    = $_POST['parntid'];
            $user_id    = $_POST['user_id'];
            $parntreply = $_POST['parntreply'];

            $comment_user_like = array();
            $comment_user_likes = $wpdb->get_col( "SELECT `comment_user_like` FROM $table_name WHERE `id` = $parntid" );
            $comment_user_like = explode(',', $comment_user_likes[0]);

            $comment_admin_like = array();
            $comment_admin_likes = $wpdb->get_col( "SELECT `comment_admin_like` FROM $table_name WHERE `id` = $parntid" );
            $comment_admin_like = explode(',', $comment_admin_likes[0]);

            $comment_user_unlike = array();
            $comment_user_unlikes = $wpdb->get_col( "SELECT `comment_user_unlike` FROM $table_name WHERE `id` = $parntid" );
            $comment_user_unlike = explode(',', $comment_user_unlikes[0]);
            
            $comment_admin_unlike = array();
            $comment_admin_unlikes = $wpdb->get_col( "SELECT `comment_admin_unlike` FROM $table_name WHERE `id` = $parntid" );
            $comment_admin_unlike = explode(',', $comment_admin_unlikes[0]);

            $sms = '';
            if($parntreply == 'user'){
                if(in_array( $user_id ,$comment_user_like )){
                    $sms = 'user its exsest';
                    unset($comment_user_like[array_search($user_id, $comment_user_like)]);
                    $comment_user_like = implode(",",$comment_user_like);
                    $wpdb->update( $table_name,
                    array(
                            'comment_user_like' => $comment_user_like
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s'),
                    array('%d')
                    );
                }else{
                    $sms = 'user its not exsest';
                    unset($comment_user_unlike[array_search($user_id, $comment_user_unlike)]);
                    $comment_user_unlike = implode(",",$comment_user_unlike);

                    array_push($comment_user_like, $user_id);
                    $comment_user_like = implode(",",$comment_user_like);
                    $wpdb->update( $table_name,
                    array(
                            'comment_user_like' => $comment_user_like,
                            'comment_user_unlike' => $comment_user_unlike
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s', '%s'),
                    array('%d')
                    );
                }
            }else{
                if(in_array( $user_id ,$comment_admin_like )){
                    $sms = 'admin its exsest';
                    unset($comment_admin_like[array_search($user_id, $comment_admin_like)]);
                    $comment_admin_like = implode(",",$comment_admin_like);
                    $wpdb->update( $table_name,
                    array(
                            'comment_admin_like' => $comment_admin_like
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s'),
                    array('%d')
                    );
                }else{
                    $sms = 'admin its not exsest';
                    unset($comment_admin_unlike[array_search($user_id, $comment_admin_unlike)]);
                    $comment_admin_unlike = implode(",",$comment_admin_unlike);

                    array_push($comment_admin_like, $user_id);
                    $comment_admin_like = implode(",",$comment_admin_like);
                    $wpdb->update( $table_name,
                    array(
                            'comment_admin_like'  => $comment_admin_like,
                            'comment_admin_unlike'=> $comment_admin_unlike
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s', '%s'),
                    array('%d')
                    );
                }
            }

            

            echo json_encode(
                array(
                'message'                   => 'success',
                    'comment_user_like'     => $comment_user_like,
                    'comment_admin_like'    => $comment_admin_like,
                    'parntreply'            => $parntreply,
                    'sms'                   => $sms
                )
            );
            die();           
        }
        function unlikeajax(){
            if(!is_user_logged_in()){
                return false;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'super_sticky_notes';

            $parntid    = $_POST['parntid'];
            $user_id    = $_POST['user_id'];
            $parntreply = $_POST['parntreply'];

            $comment_user_unlike = array();
            $comment_user_unlikes = $wpdb->get_col( "SELECT `comment_user_unlike` FROM $table_name WHERE `id` = $parntid" );
            $comment_user_unlike = explode(',', $comment_user_unlikes[0]);
            
            $comment_admin_unlike = array();
            $comment_admin_unlikes = $wpdb->get_col( "SELECT `comment_admin_unlike` FROM $table_name WHERE `id` = $parntid" );
            $comment_admin_unlike = explode(',', $comment_admin_unlikes[0]);

            $comment_user_like = array();
            $comment_user_likes = $wpdb->get_col( "SELECT `comment_user_like` FROM $table_name WHERE `id` = $parntid" );
            $comment_user_like = explode(',', $comment_user_likes[0]);

            $comment_admin_like = array();
            $comment_admin_likes = $wpdb->get_col( "SELECT `comment_admin_like` FROM $table_name WHERE `id` = $parntid" );
            $comment_admin_like = explode(',', $comment_admin_likes[0]);
            
            $sms = '';
            if($parntreply == 'user'){
                if(in_array( $user_id ,$comment_user_unlike )){
                    $sms = 'user its exsest';
                    unset($comment_user_unlike[array_search($user_id, $comment_user_unlike)]);
                    $comment_user_unlike = implode(",",$comment_user_unlike);
                    $wpdb->update( $table_name,
                    array(
                            'comment_user_unlike' => $comment_user_unlike
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s'),
                    array('%d')
                    );
                }else{
                    $sms = 'user its not exsest';
                    unset($comment_user_like[array_search($user_id, $comment_user_like)]);
                    $comment_user_like = implode(",",$comment_user_like);

                    array_push($comment_user_unlike, $user_id);
                    $comment_user_unlike = implode(",",$comment_user_unlike);
                    $wpdb->update( $table_name,
                    array(
                            'comment_user_unlike' => $comment_user_unlike,
                            'comment_user_like' => $comment_user_like
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s', '%s'),
                    array('%d')
                    );
                }
            }else{
                if(in_array( $user_id ,$comment_admin_unlike )){
                    $sms = 'admin its exsest';
                    unset($comment_admin_unlike[array_search($user_id, $comment_admin_unlike)]);
                    $comment_admin_unlike = implode(",",$comment_admin_unlike);
                    $wpdb->update( $table_name,
                    array(
                            'comment_admin_unlike' => $comment_admin_unlike
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s'),
                    array('%d')
                    );
                }else{
                    $sms = 'admin its not exsest';
                    unset($comment_admin_like[array_search($user_id, $comment_admin_like)]);
                    $comment_admin_like = implode(",",$comment_admin_like);

                    array_push($comment_admin_unlike, $user_id);
                    $comment_admin_unlike = implode(",",$comment_admin_unlike);
                    $wpdb->update( $table_name,
                    array(
                            'comment_admin_unlike'  => $comment_admin_unlike,
                            'comment_admin_like'  => $comment_admin_like
                        ),
                    array(
                        'id'=> $parntid
                    ),
                    array('%s', '%s'),
                    array('%d')
                    );
                }
            }

            echo json_encode(
                array(
                    'message'   => 'success',
                    'parntid'   => $parntid,
                    'user_id'   => $user_id,
                    'parntreply'=> $parntreply
                )
            );
            die();           
        }

        /*
        * its append add action line 46
        * Send Code as Sold
        * This action work when hover on a item from New Code
        * All this sold store in a option as json
        * get ajax note all data and save 
        */
        function sendtonotesajax(){

            $auto_approving_ur_name = array();
            if ( get_option( 'auto_approving_ur_name') !== false ) {
                $auto_approving_ur_name = get_option( 'auto_approving_ur_name');
            }
            $auto_approving_ur_name = array_map('strtolower',$auto_approving_ur_name);
            $auto_approving_ur_name = str_replace(' ', '_', $auto_approving_ur_name);
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            $role = $roles[0];
            $status = ( in_array( $role ,$auto_approving_ur_name ) ) ? 'Approved' : '';

            /**
             * insert data start
             * all values
            */
            $position = $_POST['position'];
            $current_page_id = $_POST['current_page_id'];
            $page_author_id = $_POST['page_author_id'];
            $parentClass = $_POST['parentClass'];
            $current_Class = $_POST['currentClass'];
            $user_id = $_POST['user_id'];
            $text_content = $_POST['text_content'];
            $text_content = str_replace('\"', '', $text_content);
            $title = $_POST['title'];
            $priv = $_POST['priv'];
            $next_conv_allowed = 0;

            $reply_user_info = get_userdata($user_id);
            $reply_user_name = $reply_user_info->display_name;

            $table_name = $this->super_sticky_notes_tbl;

            $user_id_and_user_note = $this->wpdb->get_results( "SELECT `user_id`, `note_values` FROM $table_name WHERE `page_id` = $current_page_id AND `parent_class` = '$parentClass' AND `current_Class` = '$current_Class' AND `note_position` = $position  ", OBJECT);
            $user_id_and_user_note = json_decode(json_encode($user_id_and_user_note), true);

            foreach($user_id_and_user_note as $user_note){
                $note_user_id = $user_note['user_id'];
                $note_values = $user_note['note_values'];
                $user_info = get_userdata($note_user_id);
                $user_email = $user_info->user_email;
                $to = $user_email;
                $subject = $reply_user_name . ' has replied on your comment';
                $body = 'Your Question : ' . $note_values . '.</br>' .
                        $reply_user_name . ' replied : ' . $text_content . '.</br>';
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $mail = wp_mail( $to, $subject, $body, $headers );
            }

            if($page_author_id != ''){
                $page_author_info = get_userdata($page_author_id);
                $page_author_email = $page_author_info->user_email;
                $to = $page_author_email;
                $subject = $reply_user_name . ' has comment on your page';
                $body = $reply_user_name . ' comment : ' . $text_content . '.</br>';
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $mail = wp_mail( $to, $subject, $body, $headers );
            }

            $j = 'no';
               $insert = $this->wpdb->insert( 
                    $table_name, 
                    array(
                        'user_id' => $user_id,
                        'page_id' => $current_page_id,
                        'page_author_id' => $page_author_id,
                        'parent_class' => $parentClass,
                        'current_Class' => $current_Class,
                        'note_position' => $position,
                        'note_values' => $text_content,
                        'title' => $title,
                        'next_conv_allowed' => $next_conv_allowed,
                        'priv' => $priv,
                        'note_status' => $status
                    ),
                    array('%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%s')
                );
                //insert data end
                
                if($insert){
                    $j = 'yes';
                    $lastData = $this->wpdb->get_row('SELECT * FROM '.$table_name.' WHERE id='.$this->wpdb->insert_id);
                    $userDetails = get_user_by( 'id', $lastData->user_id );
                    $lastData->user_nicename = $userDetails->user_nicename;
                }
                 
            // }

            echo json_encode(
                array(
                    'message' => $j,
                    'priv' => $priv,
                    'insert' => $lastData
                ));
            die();

        }
        
        /**
         * its append add filter line 65
         * filter all content and Insert all note in content
         * we filter all content and gave names one by one p-class0
         * https://www.php.net/manual/en/domdocument.loadhtml.php
         * then we append all note in the content
         * https://www.php.net/manual/en/function.libxml-use-internal-errors.php
        */
        function filter_the_content_in_the_main_loop( $content ) {

            $content = preg_replace("/<br\W*?\/>/", "<div class='br-replace'></div><p>", $content);
            $content = preg_replace("/<b\W*?\/>/", "<div class='b-replace'></div><p>", $content);
            $content = preg_replace('/<td.*?>(.*?)<\/td>/i', '<td><p>$1</td></p>', $content);

            // global $wpdb;
            // $table_name = $wpdb->prefix . 'super_sticky_notes';
            // $top_users = $this->wpdb->get_results( "SELECT user_id, COUNT(user_id) AS num_shoes FROM $table_name GROUP BY user_id ORDER BY num_shoes DESC LIMIT 10", OBJECT);
            // $top_users_ids = array();
            // foreach($top_users as $single_id){
            //     $top_users_id = $single_id->user_id;
            //     array_push($top_users_ids, $top_users_id);
            // }

                $current_page_id = get_the_ID();
                $user_id = (isset($_COOKIE['sticky_id'])) ? $_COOKIE['sticky_id'] :  get_current_user_id();
                $table_name = $this->super_sticky_notes_tbl;
                
               
                $show_values = array();
                $approved = 'Approved';

                $qrry = $this->wpdb->prepare("SELECT `current_Class` FROM $table_name WHERE (`page_id` = %d AND `note_status` = '%s')", $current_page_id, $approved);
                if(is_user_logged_in()) $qrry .= $this->wpdb->prepare(" OR (`priv` = %d AND `page_id` = %d AND `user_id` = %d)", 1, $current_page_id, $user_id);
                if(get_option( 'visitor_allowed', 0 ) != 1) $qrry .= $this->wpdb->prepare(" AND `user_id` = %d", $user_id);
                $qrry .= " GROUP BY `current_Class`";
                
                
                $all_current_Class = $this->wpdb->get_results($qrry, OBJECT);

                if(!isset($_REQUEST['note']) && $all_current_Class)
                {

                    $all_current_Classs = array();
                    foreach ($all_current_Class as $value)
                    { 
                        $all_current_Classs[] = $value->current_Class;
                    }

                    libxml_use_internal_errors(true);
                    $content = '
                    <meta charset="UTF-8">
                    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
                    <div class="supper_sticky_note">' . $content . '</div>';
                    $DOM = new DOMDocument();
                    $DOM->loadHTML($content);
                    libxml_clear_errors();
                    $list = $DOM->getElementsByTagName('p');
                    $i = 0;

                    foreach($list as $p){
                        $p->setAttribute('class', 'p-class'.$i++);
                        $p->setAttribute('onClick', 'commentEvent(this);');
                    }
                    $DOM=$DOM->saveHTML();
                    $content = $DOM;

                    // Add new data
                    $doc = new DOMDocument();
                    $doc->loadHTML($content);
                    $xpath = new DOMXPath($doc);
                   
                    for ($x = 0; $x < count($all_current_Classs); $x++) {

                        $classname= $all_current_Classs[$x];
                        $elements = $xpath->query("//p[@class='$classname']");
                        $real_values = $elements->item(0)->nodeValue;
                        $approved = 'Approved';

                        $qry = $this->wpdb->prepare("SELECT `note_position` FROM $table_name WHERE (`current_Class` = %s AND `page_id` = %d AND `note_status` = %s)",$classname, $current_page_id, $approved); 
                        if(is_user_logged_in()) $qry .= $this->wpdb->prepare(" OR (`current_Class` = %s AND `page_id` = %d AND `priv`=%d AND `user_id`=%d)", $classname, $current_page_id, 1, $user_id);
                        if(get_option( 'visitor_allowed', 0 ) != 1) $qry .= $this->wpdb->prepare(" AND `user_id` = %d", $user_id);
                        $qry .= " GROUP BY `note_position` ORDER BY `note_position`";

                        $all_note_position = $this->wpdb->get_results($qry, OBJECT);
                        
                        $all_note_positions = array();
                        foreach ($all_note_position as $note_position)
                        { 
                            $all_note_positions[] = $note_position->note_position;
                        }

                        $real_values_in_array = array();
                        $real_values_in_array = str_split($real_values, 1);

                        $my_html = 0;
                        foreach ($all_note_positions as $single_positions) {
                            $ary = $this->wpdb->prepare("SELECT `id`, `note_position`, `parent_class`, `user_id`, `next_conv_allowed` 
                            FROM $table_name 
                            WHERE `current_Class` = %s 
                            AND (`page_id` = %d 
                            AND `note_position` = %d 
                            AND `note_status` = %s) 
                            OR (`page_id` = %d AND `note_position` = %d AND `priv`=%d AND `user_id`=%d)", 
                            $classname, $current_page_id, $single_positions, $approved, $current_page_id, $single_positions, 1, $user_id);
                            if(get_option( 'visitor_allowed', 0 ) != 1) $ary .= " AND `user_id` = $user_id";

                            $data_id = $this->wpdb->get_results($ary, OBJECT);
                            $data_ids = json_decode(json_encode($data_id), true);


                            

                            $data_idd = array();
                            $dataActive = '';
                            $parent_class = '';
                            foreach($data_ids as $k => $singleid){
                                array_push($data_idd, $singleid['id']);
                                $parent_class = $singleid['parent_class'];
                                if($singleid['user_id'] == get_current_user_id() && $singleid['next_conv_allowed'] == 1) $dataActive = 'allowed';
                            }
                            $data_idd = implode(',',$data_idd);

                            $top_user = '';
                            // foreach($data_ids as $single_id){
                            //     if(in_array( $single_id['user_id'] ,$top_users_ids )) $top_user = 'top-user';
                            // }

                            $single_position = $single_positions + $my_html;

                            $actual_value_text = array_slice($real_values_in_array, 0, $single_position, true) +
                            array("my_html'.$my_html.'" => "<sub data-current='$classname' data-parent='$parent_class' data-id='$data_idd' data-position='$single_positions' class='note-question ".$dataActive." '><span class='note-question-icon-button old ".$top_user."'></span></sub>") +
                            array_slice($real_values_in_array, $single_position, count($real_values_in_array) - 1, true) ;

                            $real_values_in_array = $actual_value_text;
                            $my_html++;
                        }
                        $actual_value_texts = implode( $actual_value_text );
                        // $show_values[$classname] = $actual_value_texts;
                        $elements->item(0)->nodeValue = '';
                        $f = $doc->createDocumentFragment();
                        $f->appendXML($actual_value_texts);
                        $elements->item(0)->appendChild($f);
                    }

                    //$DOM = utf8_decode($doc->saveHTML());
                    //$DOM = $doc->saveHTML($doc->documentElement); 
                    $DOM=$doc->saveHTML();
                    $content = $DOM;

                    
                }
                else{
                    libxml_use_internal_errors(true);
                    $content = '
                    <meta charset="UTF-8">
                    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
                    <div class="supper_sticky_note">' . $content . '</div>';
                    $DOM = new DOMDocument();
                    $DOM->loadHTML($content);
                    libxml_clear_errors();
                    $list = $DOM->getElementsByTagName('p');
                    $i = 0;
                    foreach($list as $p){
                        $p->setAttribute('class', 'p-class'.$i++);
                        $p->setAttribute('onClick', 'commentEvent(this);');
                    }
                    $DOM=$DOM->saveHTML();
                    $content = $DOM;
                    
                }
        
            return $content;
        }

        // its append line 677
        // update Settings
        public function updateSettings($data){
            foreach($data as $k => $sd) update_option( $k, $sd );
        }
        

        /**
         * its append add menu line 98
         * submenu function
         * its admin side function
         * admin control section.. admin reply section.
        */
        public static function submenufunction(){

            
            echo $plugin_settings = get_option('eat_admin_theme_settings');

            // wp_enqueue_style( 'bootstrap_css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css', array(), time(), 'all' );
            // wp_enqueue_script( 'bootstrap_js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array('jquery'), time(), true );
            

            if (isset($_POST['rich_text_editor'])){
                $rich_text_editor = $_POST['rich_text_editor'];
                update_option( 'rich_text_editor', $rich_text_editor);
            }
            if (isset($_POST['wssn_restrict_number'])){

                $wssn_restrict_number = $_POST['wssn_restrict_number'];
                update_option( 'wssn_restrict_number', $wssn_restrict_number);
            }
            
            if (isset($_POST['wp_ssn_user_avatar'])){

                $icon_user = $_POST['wp_ssn_user_avatar'];
                update_option( 'wp_ssn_user_avatar', $icon_user);
            }
            if (isset($_POST['wp_ssn_note_admin_avatar'])){

                $icon_admin = $_POST['wp_ssn_note_admin_avatar'];
                update_option( 'wp_ssn_note_admin_avatar', $icon_admin);
            }

            

            global $wpdb;

            if (isset($_POST['status_message']))
            {
                $status = $_POST['status'];
                $status_id = $_POST['status_message'];

                $table_name = $wpdb->prefix . 'super_sticky_notes';
                if($status == 'delete'){
                    $delete = $this->wpdb->delete(
                        $table_name,
                        array('id' => $status_id),
                        array('%d')        
                    );
                }else{
                    $wpdb->update( $table_name,
                    array(
                            'note_status' => $status
                        ),
                    array(
                        'id'=> $status_id
                    ),
                    array('%s'),
                    array('%d')
                    );
                }
            }
            if (isset($_POST['note_reply_ids']))
            {
                $status_ids = $_POST['note_reply_ids'];
                $note_reply_admin_role = $_POST['note_reply_admin_role'];
                $note_reply = $_POST['note_reply_text'];
                $next_conv_allowed = $_POST['next_conv_allowed'];
                $note_repliedOn_date = date("Y-m-d");

                $table_name = $wpdb->prefix . 'super_sticky_notes';
                $user_id_and_user_note = $this->wpdb->get_results( "SELECT `user_id`, `note_values` FROM $table_name WHERE `id` = $status_ids ", OBJECT);
                $user_id_and_user_note = json_decode(json_encode($user_id_and_user_note), true);
                $note_user_id = $user_id_and_user_note[0]['user_id'];
                $note_values = $user_id_and_user_note[0]['note_values'];
                $user_info = get_userdata($note_user_id);
                $user_email = $user_info->user_email;
                $next_con = ($next_conv_allowed == 1) ? 'Yes' : 'No';

                $to = $user_email;

                $subject = 'Congratulations Admin has replied to your comment.';
                $body = '<p><strong>Your Question :</strong> ' . $note_values . '.</br></p>' .
                        '<p><strong>Admin Reply :</strong> ' . $note_reply . '.</br></p>' .
                        '<p>Next Conversation : ' . $next_con . '.</br></p>';
                $headers = array('Content-Type: text/html; charset=UTF-8');


                $mail = wp_mail( $to, $subject, $body, $headers );

                $table_name = $wpdb->prefix . 'super_sticky_notes';
                $wpdb->update( $table_name,
                array(
                        'note_reply_admin_role' => $note_reply_admin_role,
                        'note_reply' => $note_reply,
                        'next_conv_allowed' => $next_conv_allowed,
                        'note_repliedOn' => $note_repliedOn_date
                    ),
                array(
                    'id'=> $status_ids
                ),
                array( '%d', '%s', '%d', '%s' ),
                array( '%d' )
                );
            }
            if (isset($_POST['visitor_allowed']))
            {
                $visitor_allowed = $_POST['visitor_allowed'];

                    $option_name = 'visitor_allowed' ;
                    $new_value = 'red';
                    if ( get_option( $option_name ) !== false ) {
                        update_option( $option_name, $visitor_allowed );
                    } else {
                        $deprecated = null;
                        $autoload = 'no';
                        add_option( $option_name, $visitor_allowed, $deprecated, $autoload );
                    }
            }

            if(isset($_POST['private_comment'])){
                update_option( 'private_comment', $_POST['private_comment'] );
            }

            //submit auto approving user roles name code 
            if(isset($_POST['auto_approving_ur_name'])  ){
                update_option( 'auto_approving_ur_name', $_POST['auto_approving_ur_name'] );
            }
            //submit restrict user roles name code 
            if(isset($_POST['restrict_ur_name'])  ){
                update_option( 'restrict_ur_name', $_POST['restrict_ur_name'] );
            }
            //submit Allows roles for power to reply roles name code 
            if(isset($_POST['power_to_reply_ur_name'])  ){
                update_option( 'power_to_reply_ur_name', $_POST['power_to_reply_ur_name'] );
            }

            if(isset($_POST['allcommentpage'])) $this->updateSettings($_POST);

            ?>
            <div class="super-sticky-notes">
                <div class="sticky-setting-title"><div class=setting-icon><h1><?php _e('Sticky Question Settings', 'wp_super_sticky_notes'); ?></h1></div></div>
                <div class="sticky-top-bar">
                    <div class="tab">
                        <button class="tablinks active" onclick="openTab(event, 'all')"><?php _e('All', 'wp_super_sticky_notes'); ?></button><div class="tab-icons"></div>
                        <button class="tablinks" onclick="openTab(event, 'approved')"><?php _e('Approved', 'wp_super_sticky_notes'); ?></button><div class="tab-icons"></div>
                        <button class="tablinks" onclick="openTab(event, 'disapproved')"><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></button><div class="tab-icons"></div>
                        <button class="tablinks" onclick="openTab(event, 'toppage')"><?php _e('Analytics/Reports', 'wp_super_sticky_notes'); ?></button><div class="tab-icons"></div>
                        <button class="tablinks" onclick="openTab(event, 'settings')"><?php _e('Settings', 'wp_super_sticky_notes'); ?></button>
                    </div>
                </div>

                <div id="all" class="tabcontent" style="display:block;" >
                    <div class="table-responsive">
                        <table class="table sticky-notes-data-table jquerydatatable">
                            <thead>
                                <tr class="note-heading-wrapper">
                                    <th><?php _e('User', 'wp_super_sticky_notes'); ?></th>
                                    <th><?php _e('Asked Question', 'wp_super_sticky_notes'); ?></th>
                                    <th><?php _e('Page/Post', 'wp_super_sticky_notes'); ?></th>
                                    <th><?php _e('AskedOn', 'wp_super_sticky_notes'); ?></th>
                                    <th><?php _e('Status', 'wp_super_sticky_notes'); ?></th>
                                    <th><?php _e('Action', 'wp_super_sticky_notes'); ?></th>
                                    <th><?php _e('Reply', 'wp_super_sticky_notes'); ?></th>
                                    <th><?php _e('RepliedOn', 'wp_super_sticky_notes'); ?></th>
                                </tr>
                            </thead>
                            <?php
                                $table_name = $wpdb->prefix . 'super_sticky_notes';
                                $qry = $this->wpdb->prepare("SELECT * FROM $table_name ssn WHERE ssn.`priv` != %d ORDER BY ssn.`insert_time` DESC", 1);
                                $all_valus_notes = $this->wpdb->get_results($qry, OBJECT);                   
                                $all_valus_notes = json_decode(json_encode($all_valus_notes), true);

                                
                                
                                
                                ?>

                            <tbody>
                            <?php foreach ($all_valus_notes as $note_values){ 
                                $note_values_note = $note_values['note_values'];
                                ?>
                        <tr>
                                <td><?php 
                                    $author_obj = get_user_by('id', $note_values['user_id']); 
                                    echo $author_obj->data->user_nicename; ?></td>
                                <td><?php echo $note_values_note; ?></td>
                                <td class="note-title"><a href="<?php echo get_permalink($note_values['page_id']); ?>" target="_blank"><?php echo $note_values['title']; ?></a></td>
                                <td><?php echo $note_values['insert_time']; ?></td>
                                <td class="note-status-view">
                                    <?php if($note_values['note_status'] == ''){?>
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <button class="approved" value="Approved" name="status"><?php _e('Approve', 'wp_super_sticky_notes'); ?></button>
                                            <button class="disapproved" value="Disapproved" name="status"><?php _e('Disapprove', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }elseif($note_values['note_status'] == 'Approved'){ ?>
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <span class="approved"><?php _e('Approved', 'wp_supper_sticky'); ?></span>
                                            <button class="disapprovedd" value="delete" name="status"><?php _e('Delete', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }elseif($note_values['note_status'] == 'Disapproved'){ ?> 
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <span class="disapprovedd"><?php _e('Disapproved', 'wp_supper_sticky'); ?></span>
                                            <button class="disapprovedd" value="delete" name="status"><?php _e('Delete', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }?> 
                                </td>
                                <td>
                                    <?php if($note_values['note_status'] == 'Disapproved'){?>
                                        <div class="disapproved-reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></div>
                                    <?php }else{
                                            $current_id = $note_values['id'];
                                        ?>
                                        <button class="reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></button>
                                            <div class="modal-overlay">
                                            <div class="modal">
                                                <a class="close-modal">
                                                <svg viewBox="0 0 20 20">
                                                    <path fill="#000000" d="M15.898,4.045c-0.271-0.272-0.713-0.272-0.986,0l-4.71,4.711L5.493,4.045c-0.272-0.272-0.714-0.272-0.986,0s-0.272,0.714,0,0.986l4.709,4.711l-4.71,4.711c-0.272,0.271-0.272,0.713,0,0.986c0.136,0.136,0.314,0.203,0.492,0.203c0.179,0,0.357-0.067,0.493-0.203l4.711-4.711l4.71,4.711c0.137,0.136,0.314,0.203,0.494,0.203c0.178,0,0.355-0.067,0.492-0.203c0.273-0.273,0.273-0.715,0-0.986l-4.711-4.711l4.711-4.711C16.172,4.759,16.172,4.317,15.898,4.045z"></path>
                                                </svg>
                                                </a>
                                                <div class="modal-content">

                                                    <h3><?php _e('Write your reply', 'wp_super_sticky_notes'); ?></h3>
                                                    <div class="note-reply-page">
                                                        <form method="POST">
                                                            <input type="hidden" name="note_reply_ids" value="<?php echo $current_id; ?>" />
                                                            <input type="hidden" name="note_reply_admin_role" value="<?php echo get_current_user_id(); ?>" />
                                                            <!-- <input type="text" name="note_reply_title" placeholder="Title"> -->
                                                            <textarea name="note_reply_text" id="note_reply_text" placeholder="Write your reply.." style="height:200px"><?php echo $note_values['note_reply']; ?></textarea>
                                                            
                                                            <div class="next-conversation">
                                                                <p><?php _e('Next Conversation Allowed', 'wp_super_sticky_notes'); ?></p>
                                                                <label class="switch">
                                                                    <?php $checked = ($note_values['next_conv_allowed'] == 1) ? 'checked' : ''; ?>
                                                                    <input type="hidden" name="next_conv_allowed" value="0" />
                                                                    <input type="checkbox" name="next_conv_allowed" value="1" <?php echo $checked; ?>/>
                                                                    <span class="slider round"></span>
                                                                </label>
                                                                <!-- <p class="checked-message"></p> -->
                                                            </div>
                                                            <input type="submit" class="note-reply" value="Reply">
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                            </div>
                                    <?php }?>  
                                </td>
                                <td class="note-class-view"><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_reply']; } ?></td>
                                <td><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_repliedOn']; } ?></td>
                            </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="approved" class="tabcontent">
                    <div class="table-responsive">
                        <table class="table sticky-notes-data-table jquerydatatable">
                            <thead>
                            <tr class="note-heading-wrapper">
                                <th><?php _e('User', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Asked Question', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Page/Post', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('AskedOn', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Disapprove Comment', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Action', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Reply', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('RepliedOn', 'wp_super_sticky_notes'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $table_name = $this->wpdb->prefix . 'super_sticky_notes';
                                $qry = $this->wpdb->prepare("SELECT * FROM $table_name WHERE `note_status` = %s", 'Approved');
                                $all_valus_notes = $wpdb->get_results($qry, OBJECT);                   
                                $all_valus_notes = json_decode(json_encode($all_valus_notes), true);
                                
                                foreach ($all_valus_notes as $note_values){
                            ?>
                            <tr>
                                <td><?php $author_obj = get_user_by('id', $note_values['user_id']); echo $author_obj->data->user_nicename; ?></td>
                                <td><?php echo $note_values['note_values']; ?></td>
                                <td class="note-title"><a href="<?php echo get_permalink($note_values['page_id']); ?>" target="_blank"><?php echo $note_values['title']; ?></a></td>
                                <td><?php echo $note_values['insert_time']; ?></td>
                                <td class="note-status-view">
                                    <?php if($note_values['note_status'] == ''){?>
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <button class="approved" value="Approved" name="status"><?php _e('Approved', 'wp_super_sticky_notes'); ?></button>
                                            <button class="disapproved" value="Disapproved" name="status"><?php _e('Disapprove', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }elseif($note_values['note_status'] == 'Approved'){ ?>
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <button class="disapprovedd" value="Disapproved" name="status"><?php _e('Disapprove', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }elseif($note_values['note_status'] == 'Disapproved'){ ?> 
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <button class="approvedd" value="Approved" name="status"><?php _e('Approve', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }?> 
                                </td>
                                <td>
                                    <?php if($note_values['note_status'] == 'Disapproved'){?>
                                        <div class="disapproved-reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></div>
                                    <?php }else{
                                        $current_id = $note_values['id'];
                                        ?>
                                        <button class="reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></button>
                                            <div class="modal-overlay">
                                            <div class="modal">
                                                <a class="close-modal">
                                                <svg viewBox="0 0 20 20">
                                                    <path fill="#000000" d="M15.898,4.045c-0.271-0.272-0.713-0.272-0.986,0l-4.71,4.711L5.493,4.045c-0.272-0.272-0.714-0.272-0.986,0s-0.272,0.714,0,0.986l4.709,4.711l-4.71,4.711c-0.272,0.271-0.272,0.713,0,0.986c0.136,0.136,0.314,0.203,0.492,0.203c0.179,0,0.357-0.067,0.493-0.203l4.711-4.711l4.71,4.711c0.137,0.136,0.314,0.203,0.494,0.203c0.178,0,0.355-0.067,0.492-0.203c0.273-0.273,0.273-0.715,0-0.986l-4.711-4.711l4.711-4.711C16.172,4.759,16.172,4.317,15.898,4.045z"></path>
                                                </svg>
                                                </a>
                                                <div class="modal-content">

                                                    <h3><?php _e('Write your reply', 'wp_super_sticky_notes'); ?></h3>
                                                    <div class="note-reply-page">
                                                        <form method="POST">
                                                            <input type="hidden" name="note_reply_ids" value="<?php echo $current_id; ?>" />
                                                            <input type="hidden" name="note_reply_admin_role" value="<?php echo get_current_user_id(); ?>" />
                                                            <!-- <input type="text" name="note_reply_title" placeholder="Title"> -->
                                                            <textarea name="note_reply_text" id="note_reply_text" placeholder="Write your reply.." style="height:200px"><?php echo $note_values['note_reply']; ?></textarea>
                                                            <div class="next-conversation">
                                                                <p><?php _e('Next Conversation Allowed', 'wp_super_sticky_notes'); ?></p>
                                                                <label class="switch">
                                                                    <?php $checked = ($note_values['next_conv_allowed'] == 1) ? 'checked' : ''; ?>
                                                                    <input type="hidden" name="next_conv_allowed" value="0" />
                                                                    <input type="checkbox" name="next_conv_allowed" value="1" <?php echo $checked; ?>/>
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </div>
                                                            <input type="submit" class="note-reply" value="Reply">
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                            </div>
                                    <?php }?>  
                                </td>
                                <td class="note-class-view"><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_reply']; } ?></td>
                                <td><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_repliedOn']; } ?></td>
                            </tr>
                            <?php
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div id="disapproved" class="tabcontent">
                    <div class="table-responsive">
                        <table class="table sticky-notes-data-table jquerydatatable">
                        <thead>
                            <tr class="note-heading-wrapper">
                                <th><?php _e('User', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Asked Question', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Page/Post', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('AskedOn', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Approve Comment', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Action', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Reply', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('RepliedOn', 'wp_super_sticky_notes'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $table_name = $this->super_sticky_notes_tbl;
                                $ary = $this->wpdb->prepare("SELECT * FROM $table_name WHERE `note_status` = %s", 'Disapproved');
                                $all_valus_notes = $this->wpdb->get_results($ary, OBJECT);                   
                                $all_valus_notes = json_decode(json_encode($all_valus_notes), true);
                                
                                foreach ($all_valus_notes as $note_values){
                            ?>
                            <tr>
                                <td><?php $author_obj = get_user_by('id', $note_values['user_id']); echo $author_obj->data->user_nicename; ?></td>
                                <td><?php echo $note_values['note_values']; ?></td>
                                <td class="note-title"><a href="<?php echo get_permalink($note_values['page_id']); ?>" target="_blank"><?php echo $note_values['title']; ?></a></td>
                                <td><?php echo $note_values['insert_time']; ?></td>
                                <td class="note-status-view">
                                    <?php if($note_values['note_status'] == ''){?>
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <button class="approved" value="Approved" name="status"><?php _e('Approve', 'wp_super_sticky_notes'); ?></button>
                                            <button class="disapproved" value="Disapproved" name="status"><?php _e('Disapprove', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }elseif($note_values['note_status'] == 'Approved'){ ?>
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <button class="disapprovedd" value="Disapproved" name="status"><?php _e('Disapprove', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }elseif($note_values['note_status'] == 'Disapproved'){ ?> 
                                        <form method="POST">
                                            <input type="hidden" name="status_message" value="<?php echo $note_values['id']; ?>" />
                                            <button class="approvedd" value="Approved" name="status"><?php _e('Approve', 'wp_super_sticky_notes'); ?></button>
                                        </form>
                                    <?php }?> 
                                </td>
                                <td>
                                    <?php if($note_values['note_status'] == 'Disapproved'){?>
                                        <div class="disapproved-reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></div>
                                    <?php }else{
                                            $current_id = $note_values['id'];
                                        ?>
                                        <button class="reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></button>
                                            <div class="modal-overlay">
                                            <div class="modal">
                                                <a class="close-modal">
                                                <svg viewBox="0 0 20 20">
                                                    <path fill="#000000" d="M15.898,4.045c-0.271-0.272-0.713-0.272-0.986,0l-4.71,4.711L5.493,4.045c-0.272-0.272-0.714-0.272-0.986,0s-0.272,0.714,0,0.986l4.709,4.711l-4.71,4.711c-0.272,0.271-0.272,0.713,0,0.986c0.136,0.136,0.314,0.203,0.492,0.203c0.179,0,0.357-0.067,0.493-0.203l4.711-4.711l4.71,4.711c0.137,0.136,0.314,0.203,0.494,0.203c0.178,0,0.355-0.067,0.492-0.203c0.273-0.273,0.273-0.715,0-0.986l-4.711-4.711l4.711-4.711C16.172,4.759,16.172,4.317,15.898,4.045z"></path>
                                                </svg>
                                                </a>
                                                <div class="modal-content">

                                                    <h3><?php _e('Write your reply', 'wp_super_sticky_notes'); ?></h3>
                                                    <div class="note-reply-page">
                                                        <form method="POST">
                                                            <input type="hidden" name="note_reply_ids" value="<?php echo $current_id; ?>" />
                                                            <input type="hidden" name="note_reply_admin_role" value="<?php echo get_current_user_id(); ?>" />
                                                            <!-- <input type="text" name="note_reply_title" placeholder="Title"> -->
                                                            <textarea name="note_reply_text" id="note_reply_text" placeholder="Write your reply.." style="height:200px"><?php echo $note_values['note_reply']; ?></textarea>
                                                            <div class="next-conversation">
                                                                <p><?php _e('Next Conversation Allowed', 'wp_super_sticky_notes'); ?></p>
                                                                <label class="switch">
                                                                    <?php $checked = ($note_values['next_conv_allowed'] == 1) ? 'checked' : ''; ?>
                                                                    <input type="hidden" name="next_conv_allowed" value="0" />
                                                                    <input type="checkbox" name="next_conv_allowed" value="1" <?php echo $checked; ?>/>
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </div>
                                                            <input type="submit" class="note-reply" value="Reply">
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                            </div>
                                    <?php }?>  
                                </td>
                                <td class="note-class-view"><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_reply']; } ?></td>
                                <td><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_repliedOn']; } ?></td>
                            </tr>
                            <?php
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                
                <div id="toppage" class="tabcontent">
                    <div class="wpssn-top-wap">
                         <div class="chart-title">
                            <h2 class="text-center mb-0"><?php _e('Top ten posts/pages that comments with numbers', 'sticky-note'); ?></h2>
                         </div>
                        <div class="top-ten-page mb-5" id="chartContainer"></div>

                        <div class="chart-title">
                            <h2 class="text-center mb-0"><?php _e('Top ten users with highest comments', 'sticky-note'); ?></h2>
                        </div>
                        <div id="topTenUser" class="top-ten-user mb-5 mt-5"></div>

                        <div class="chart-title">
                            <h2 class="text-center mb-0"><?php _e('Most liked and disliked comments', 'sticky-note'); ?></h2>
                        </div>

                        <div id="toptenLikeUnlike" class="top-ten-like-unlike"></div>

                    </div>
                </div>


                <!-- Settings -->
                <div id="settings" class="tabcontent">
                    <div class="settingsInner">
                        <form id="settingsForm" method="post" action="">
                            <div class="table-responsive">
                            <table class="table sticky-notes-data-table">
                                <tbody>
                                    <tr>
                                        <th class="text-left"><?php _e('Button position', 'wp_super_sticky_notes' ); ?></th>
                                        <td class="text-left">
                                            <?php $selected = get_option( 'buttonposition' );
                                            //echo $selected;
                                            ?>
                                            <select name="buttonposition" class="form-control" id="buttonposition">
                                                    <option <?php if ($selected == 'top_left' ) echo 'selected' ; ?> value="top_left"><?php _e('Top Left', 'wp_super_sticky_notes' ); ?></option>
                                                    <option <?php if ($selected == 'top_middle' ) echo 'selected' ; ?> value="top_middle"><?php _e('Top Middle', 'wp_super_sticky_notes' ); ?></option>
                                                    <option <?php if ($selected == 'top_right' ) echo 'selected' ; ?> value="top_right"><?php _e('Top Right', 'wp_super_sticky_notes' ); ?></option>
                                                    <option <?php if ($selected == 'middle_left' ) echo 'selected' ; ?> value="middle_left"><?php _e('Middle Left', 'wp_super_sticky_notes' ); ?></option>
                                                    <option <?php if ($selected == 'middle_right' ) echo 'selected' ; ?> value="middle_right"><?php _e('Middle Right', 'wp_super_sticky_notes' ); ?></option>
                                                    <option <?php if ($selected == 'bottom_left' ) echo 'selected' ; ?> value="bottom_left"><?php _e('Bottom Left', 'wp_super_sticky_notes' ); ?></option>
                                                    <option <?php if ($selected == 'bottom_middle' ) echo 'selected' ; ?> value="bottom_middle"><?php _e('Bottom Middle', 'wp_super_sticky_notes' ); ?></option>
                                                    <option <?php if ($selected == 'bottom_right' ) echo 'selected' ; ?> value="bottom_right"><?php _e('Bottom Right', 'wp_super_sticky_notes' ); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left"><?php _e('All Comment\'s page', 'wp_super_sticky_notes' ); ?></th>
                                        <td class="text-left">
                                            <?php $allpages = get_all_page_ids(); ?>
                                            <select name="allcommentpage" class="form-control" id="allcommentpage">
                                                <?php foreach( $allpages as $sp):
                                                    $selected = (get_option( 'allcommentpage') == $sp ) ? 'selected' : '';
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $sp; ?>"><?php echo get_the_title($sp); ?></option>
                                                <?php endforeach; ?>

                                            </select>
                                        </td>
                                    </tr>

                                    <!-- Private comment on all pages and posts -->
                                    <tr>
                                        <th class="text-left"><?php _e('Allow private comment on pages & posts', 'wp_super_sticky_notes' ); ?></th>
                                        <td class="text-left">
                                            <?php 
                                            $post_ids = get_posts(array(
                                                'post_type'     => 'any', //Your arguments
                                                'posts_per_page'=> -1,
                                                'fields'        => 'ids', // Only get post IDs
                                            ));
                                            $dbposts = get_option( 'allow_private_for_post' );

                                            ?>
                                            <select name="allow_private_for_post[]" multiple class="form-control" id="allow_private_for_post">
                                                <option <?php echo (in_array('all', $dbposts)) ? 'selected':''; ?> value="all"><?php _e('All', 'wp_super_sticky_notes'); ?></option>
                                                <?php foreach( $post_ids as $sp):
                                                    $selected = ( in_array($sp, $dbposts) ) ? 'selected' : '';
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $sp; ?>"><?php echo get_the_title($sp); ?></option>
                                                <?php endforeach; ?>

                                            </select>
                                        </td>
                                    </tr>

                                    <!-- Private comment on all pages and posts -->
                                    <tr>
                                        <th class="text-left"><?php _e('Allow private comment on Categorie\'s', 'wp_super_sticky_notes' ); ?></th>
                                        <td class="text-left">
                                            <?php 
                                            $cats = get_terms();
                                            $dbcates = get_option( 'allow_private_for_categori');
                                            ?>
                                            <select name="allow_private_for_categori[]" multiple class="form-control" id="allow_private_for_categori">
                                                <?php   foreach( $cats as $sc):
                                                    if($sc->slug != 'uncategorized'):
                                                    $selected = ( in_array($sc->term_id, $dbcates)) ? 'selected' : '';
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $sc->term_id; ?>"><?php echo $sc->name; ?></option>
                                                    <?php endif; endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <!-- Admin can tick for auto-approving comments for some roles  -->
                                    <tr>
                                        <th class="text-left"><?php _e('Allow auto-approving comments for user roles', 'wp_super_sticky_notes' ); ?></th>
                                        <td class="text-left">
                                            <div class="wssn-ks-cb-div">
                                                <ul class="ks-cboxtags">
                                                    <li>
                                                        <input type="checkbox" class="wssn-hidden" name="auto_approving_ur_name[]" id="checkbox_auto_approving" value="checkbox" checked/>
                                                    </li>
                                                    <?php 
                                                    global $wp_roles;
                                                    $roles = $wp_roles->get_names();
                                                    $auto_approving_ur_name = array();
                                                    if ( get_option( 'auto_approving_ur_name' ) !== false ) {
                                                        $auto_approving_ur_name = get_option( 'auto_approving_ur_name');
                                                    }
                                                    foreach($roles as $key => $role) {

                                                        $checked = ( in_array( $key ,$auto_approving_ur_name ) ) ? 'checked' : '';
                                                    ?>
                                                        <li>
                                                            <input type="checkbox" name="auto_approving_ur_name[]" id="checkbox_auto_approving_<?php echo str_replace(' ', '_', $role) ?>" value="<?php echo $key ?>" <?php echo $checked; ?>/>
                                                            <label for="checkbox_auto_approving_<?php echo str_replace(' ', '_', $role) ?>"><?php echo $role ?></label>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Admin can restrict comments to only one or several roles  automatically -->
                                    <tr>
                                        <th class="text-left"><?php _e('Restrict comments for user roles', 'wp_super_sticky_notes' ); ?></th>
                                        <td class="text-left">
                                            <div class="wssn-ks-cb-div">
                                                <ul class="ks-cboxtags">
                                                    <li>
                                                        <input type="checkbox" class="wssn-hidden" name="restrict_ur_name[]" id="checkbox_restrict_comments" value="checkbox" checked/>
                                                    </li>
                                                    <?php 
                                                    global $wp_roles;
                                                    $roles = $wp_roles->get_names();

                                                    $restrict_ur_name = array();
                                                    if ( get_option( 'restrict_ur_name' ) !== false ) {
                                                        $restrict_ur_name = get_option( 'restrict_ur_name');
                                                    }
                                                    foreach($roles as $key => $role) {

                                                        $checked = ( in_array( $key ,$restrict_ur_name ) ) ? 'checked' : '';
                                                        
                                                    ?>
                                                        <li>
                                                            <input type="checkbox" name="restrict_ur_name[]" id="checkbox_restrict_comments_<?php echo str_replace(' ', '_', $role) ?>" value="<?php echo $key ?>" <?php echo $checked; ?>/>
                                                            <label for="checkbox_restrict_comments_<?php echo str_replace(' ', '_', $role) ?>"><?php echo $role ?></label>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Admin can allows roles for power to reply to only one or several roles  automatically -->
                                    <tr>
                                        <th class="text-left"><?php _e('Allows roles for power to reply', 'wp_super_sticky_notes' ); ?></th>
                                        <td class="text-left">
                                            <div class="wssn-ks-cb-div">
                                                <ul class="ks-cboxtags">
                                                    <li>
                                                        <input type="checkbox" class="wssn-hidden" name="power_to_reply_ur_name[]" id="checkbox_power_to_reply" value="checkbox" checked/>
                                                    </li>
                                                    <?php 
                                                    global $wp_roles;
                                                    $roles = $wp_roles->get_names();
                                                    // $roles = array( 'Author', 'Contributor', 'Tutor Instructor', 'Teacher' );
                                                    $power_to_reply_ur_name = array();
                                                    if ( get_option( 'power_to_reply_ur_name' ) !== false ) {
                                                        $power_to_reply_ur_name = get_option( 'power_to_reply_ur_name');
                                                    }
                                                    foreach($roles as $key => $role) {

                                                        $checked = ( in_array( $key ,$power_to_reply_ur_name ) ) ? 'checked' : '';
                                                        
                                                    ?>
                                                        <li>
                                                            <input type="checkbox" name="power_to_reply_ur_name[]" id="checkbox_power_to_reply_<?php echo str_replace(' ', '_', $role) ?>" value="<?php echo $key ?>" <?php echo $checked; ?>/>
                                                            <label for="checkbox_power_to_reply_<?php echo str_replace(' ', '_', $role) ?>"><?php echo $role ?></label>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr> 
                                        <th class="text-left"><?php _e('Upload note admin avatar', 'wp_super_sticky_notes'); ?></th>
                                        <td class="text-left">
                                            <?php 
                                            if ( get_option( 'wp_ssn_note_admin_avatar' ) !== false ){
                                                $wp_ssn_note_admin_avatar = get_option('wp_ssn_note_admin_avatar');
                                            }else{
                                                $wp_ssn_note_admin_avatar = $this->plugin_url . 'asset/css/images/user_avatar.png';
                                            }
                                                echo '<img src="'. $wp_ssn_note_admin_avatar .'" height="42" width="42">';
                                                echo '<input type="hidden" id="image-admin-url" type="text" name="wp_ssn_note_admin_avatar" value="'. $wp_ssn_note_admin_avatar .'"/>';
                                            ?>

                                            <input id="upload-admin-button" type="button" class="button wp-ssn-button" value="Upload Image" />
                                        </td>
                                    </tr>
                                    <tr> 
                                        <th class="text-left"><?php _e('Upload user default avatar', 'wp_super_sticky_notes'); ?></th>
                                        <td class="text-left">
                                            <!-- <form method = "post"> -->
                                                <?php 
                                                if ( get_option( 'wp_ssn_user_avatar' ) !== false ){
                                                    $wp_ssn_user_avatar = get_option('wp_ssn_user_avatar');
                                                }else{
                                                    $wp_ssn_user_avatar = $this->plugin_url . 'asset/css/images/user_avatar.png';
                                                }
                                                    echo '<img src="'. $wp_ssn_user_avatar .'" height="42" width="42">';
                                                    echo '<input type="hidden" id="image-user-url" type="text" name="wp_ssn_user_avatar" value="'. $wp_ssn_user_avatar .'"/>';
                                                ?>

                                                <input id="upload-button" type="button" class="button wp-ssn-button" value="Upload Image" />
                                                <!-- <input type="submit" class="image_up_b" value="Submit" />
                                            </form> -->
                                            <div class="wp-ssn-note"><?php echo sprintf('Click <a href="%s">here</a> for Select your default avatar.', admin_url( '/options-discussion.php' )) ?></div>
                                        </td>
                                    </tr>
                                    <tr>
                                    <?php
                                        $wssn_restrict_number = 20;
                                        if ( get_option( 'wssn_restrict_number', 20 ) !== false ){
                                        $wssn_restrict_number = get_option('wssn_restrict_number', 20);
                                    }?>
                                        <th class="text-left"><?php _e('Restrict Number of Words in the comments', 'wp_super_sticky_notes'); ?></th>
                                        <td class="text-left"><input type="number" class="wssn-restrict-number" name="wssn_restrict_number" id="wssn_restrict_number" min="0" value="<?php echo $wssn_restrict_number;?>"/></td>
                                    </tr>
                                    <tr>
                                        <th class="text-left"><?php _e('Allow Rich text editor', 'wp_super_sticky_notes'); ?></th>
                                        <td class="text-left">
                                            <div class="next-conversation">
                                                <label class="switch">
                                                    <?php $checked = ( get_option( 'rich_text_editor' ) == 1) ? 'checked' : ''; ?>
                                                    <input type="hidden" name="rich_text_editor" value="0" />
                                                    <input type="checkbox" class="checbox-visitor" name="rich_text_editor" value="1" <?php echo $checked; ?>/>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left"><?php _e('All Comment\'s Shortcode', 'wp_super_sticky_notes'); ?></th>
                                        <td class="text-left"><?php echo '[all-sticky-comments]'; ?></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td class="text-left"><input type="submit" class="submit-settings button button-primary text-right" value="<?php _e('Submit', 'wp_super_sticky_notes'); ?>"></td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="visitors-conversation">
                    <form method="POST">
                        <div class="visitor-conversation">
                            <p><?php _e('Visitor can see the conversation ?', 'wp_super_sticky_notes'); ?></p>
                            <label class="switch">
                                <?php $checked = ( get_option( 'visitor_allowed' ) == 1) ? 'checked' : ''; ?>
                                <input type="hidden" name="visitor_allowed" value="0" />
                                <input type="checkbox" class="checbox-visitor" onChange="submit();" name="visitor_allowed" value="1" <?php echo $checked; ?>/>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <!-- Allow private comment -->
                        <div class="visitor-conversation">
                            <p style="margin-left:60px;"><?php _e('Allow Private Commment ?', 'wp_super_sticky_notes'); ?></p>
                            <label class="switch">
                                <?php $checked = ( get_option( 'private_comment' ) == 1) ? 'checked' : ''; ?>
                                <input type="hidden" name="private_comment" value="0" />
                                <input type="checkbox" class="checbox-visitor" onChange="submit();" name="private_comment" value="1" <?php echo $checked; ?>/>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </form>
                </div>


            </div>
            <?php
        } // Admin page

        //Power to reply function page
        function powerreplyfunction(){
           
            global $wpdb;

            if (isset($_POST['status_message']))
            {
                $status = $_POST['status'];
                $status_id = $_POST['status_message'];

                $table_name = $wpdb->prefix . 'super_sticky_notes';
                if($status == 'delete'){
                    $delete = $this->wpdb->delete(
                        $table_name,
                        array('id' => $status_id),
                        array('%d')        
                    );
                }else{
                    $wpdb->update( $table_name,
                    array(
                            'note_status' => $status
                        ),
                    array(
                        'id'=> $status_id
                    ),
                    array('%s'),
                    array('%d')
                    );
                }
            }
            if (isset($_POST['note_reply_ids']))
            {
                $status_ids = $_POST['note_reply_ids'];
                $note_reply_admin_role = $_POST['note_reply_admin_role'];
                $note_reply = $_POST['note_reply_text'];
                $next_conv_allowed = $_POST['next_conv_allowed'];
                $note_repliedOn_date = date("Y-m-d");

                $table_name = $wpdb->prefix . 'super_sticky_notes';
                $user_id_and_user_note = $this->wpdb->get_results( "SELECT `user_id`, `note_values` FROM $table_name WHERE `id` = $status_ids ", OBJECT);
                $user_id_and_user_note = json_decode(json_encode($user_id_and_user_note), true);
                $note_user_id = $user_id_and_user_note[0]['user_id'];
                $note_values = $user_id_and_user_note[0]['note_values'];
                $user_info = get_userdata($note_user_id);
                $user_email = $user_info->user_email;
                $next_con = ($next_conv_allowed == 1) ? 'Yes' : 'No';

                $to = $user_email;

                $subject = 'Congratulations Instructor has replied to your comment.';
                $body = '<p><strong>Your Question :</strong> ' . $note_values . '.</br></p>' .
                        '<p><strong>Instructor Reply :</strong> ' . $note_reply . '.</br></p>' .
                        '<p>Next Conversation : ' . $next_con . '.</br></p>';
                $headers = array('Content-Type: text/html; charset=UTF-8');


                $mail = wp_mail( $to, $subject, $body, $headers );

                $table_name = $wpdb->prefix . 'super_sticky_notes';
                $wpdb->update( $table_name,
                array(
                        'note_reply_admin_role' => $note_reply_admin_role,
                        'note_reply' => $note_reply,
                        'next_conv_allowed' => $next_conv_allowed,
                        'note_repliedOn' => $note_repliedOn_date
                    ),
                array(
                    'id'=> $status_ids
                ),
                array( '%d', '%s', '%d', '%s' ),
                array( '%d' )
                );
            }

            ?>
            <div class="super-sticky-notes">
                <div class="sticky-setting-title"><div class=setting-icon><h1><?php _e('Comments Table', 'wp_super_sticky_notes'); ?></h1></div></div>
                <div id="all" class="tabcontent" style="display:block;" >
                    <div class="table-responsive">
                    <table class="table sticky-notes-data-table jquerydatatable">
                        <thead>
                            <tr class="note-heading-wrapper">
                                <th><?php _e('User', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Asked Question', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Page/Post', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('AskedOn', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Action', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('Reply', 'wp_super_sticky_notes'); ?></th>
                                <th><?php _e('RepliedOn', 'wp_super_sticky_notes'); ?></th>
                            </tr>
                        </thead>
                        <?php
                            $current_user_id = get_current_user_id();
                            $table_name = $wpdb->prefix . 'super_sticky_notes';
                            $qry = $this->wpdb->prepare("SELECT * FROM $table_name ssn WHERE ssn.`priv` != %d AND ssn.`page_author_id` = $current_user_id ORDER BY ssn.`insert_time` DESC", 1);
                            $all_valus_notes = $this->wpdb->get_results($qry, OBJECT);                   
                            $all_valus_notes = json_decode(json_encode($all_valus_notes), true);

                            ?>

                        <tbody>
                        <?php foreach ($all_valus_notes as $note_values){ 
                            $note_values_note = $note_values['note_values'];
                            ?>
                       <tr>
                            <td><?php 
                                $author_obj = get_user_by('id', $note_values['user_id']); 
                                echo $author_obj->data->user_nicename; ?></td>
                            <td><?php echo $note_values_note; ?></td>
                            <td class="note-title"><a href="<?php echo get_permalink($note_values['page_id']); ?>" target="_blank"><?php echo $note_values['title']; ?></a></td>
                            <td><?php echo $note_values['insert_time']; ?></td>
                            <td>
                                <?php if($note_values['note_status'] == 'Disapproved'){?>
                                    <div class="disapproved-reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></div>
                                <?php }else{
                                        $current_id = $note_values['id'];
                                    ?>
                                    <button class="reply"><?php _e('REPLY', 'wp_super_sticky_notes'); ?></button>
                                        <div class="modal-overlay">
                                        <div class="modal">
                                            <a class="close-modal">
                                            <svg viewBox="0 0 20 20">
                                                <path fill="#000000" d="M15.898,4.045c-0.271-0.272-0.713-0.272-0.986,0l-4.71,4.711L5.493,4.045c-0.272-0.272-0.714-0.272-0.986,0s-0.272,0.714,0,0.986l4.709,4.711l-4.71,4.711c-0.272,0.271-0.272,0.713,0,0.986c0.136,0.136,0.314,0.203,0.492,0.203c0.179,0,0.357-0.067,0.493-0.203l4.711-4.711l4.71,4.711c0.137,0.136,0.314,0.203,0.494,0.203c0.178,0,0.355-0.067,0.492-0.203c0.273-0.273,0.273-0.715,0-0.986l-4.711-4.711l4.711-4.711C16.172,4.759,16.172,4.317,15.898,4.045z"></path>
                                            </svg>
                                            </a>
                                            <div class="modal-content">

                                                <h3><?php _e('Write your reply', 'wp_super_sticky_notes'); ?></h3>
                                                <div class="note-reply-page">
                                                    <form method="POST">
                                                        <input type="hidden" name="note_reply_ids" value="<?php echo $current_id; ?>" />
                                                        <input type="hidden" name="note_reply_admin_role" value="<?php echo get_current_user_id(); ?>" />
                                                        <!-- <input type="text" name="note_reply_title" placeholder="Title"> -->
                                                        <textarea name="note_reply_text" id="note_reply_text" placeholder="Write your reply.." style="height:200px"><?php echo $note_values['note_reply']; ?></textarea>
                                                        
                                                        <div class="next-conversation">
                                                            <p><?php _e('Next Conversation Allowed', 'wp_super_sticky_notes'); ?></p>
                                                            <label class="switch">
                                                                <?php $checked = ($note_values['next_conv_allowed'] == 1) ? 'checked' : ''; ?>
                                                                <input type="hidden" name="next_conv_allowed" value="0" />
                                                                <input type="checkbox" name="next_conv_allowed" value="1" <?php echo $checked; ?>/>
                                                                <span class="slider round"></span>
                                                            </label>
                                                            <!-- <p class="checked-message"></p> -->
                                                        </div>
                                                        <input type="submit" class="note-reply" value="Reply">
                                                    </form>
                                                </div>

                                            </div>
                                        </div>
                                        </div>
                                <?php }?>  
                            </td>
                            <td class="note-class-view"><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_reply']; } ?></td>
                            <td><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><p><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></p></div> <?php }else{ echo $note_values['note_repliedOn']; } ?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * its append add shortcode line 68
         * User question lists. user can see his all notes
         * its user side function
         * its append sortcode 
         * https://developer.wordpress.org/reference/functions/add_shortcode/
        */
        public function larasoftbd_question_lists_shortcode(){
            ob_start();
            require_once($this->plugin_dir . 'template/user_question_lists.php');
            $output = ob_get_clean();
            return $output;
            wp_reset_query();
        }

        /**
         * its append add action line 71
         * User add note button function
         * This function only works when the user is logged in and the admin gives him permission
         * its append sortcode 
         * https://wordpress.stackexchange.com/questions/191523/my-add-action-wp-footer-method-is-not-calling
        */
        function user_button(){
            if(!is_user_logged_in()){
                return false;
            }
            $restrict_ur_name = array();
            if ( get_option( 'restrict_ur_name') !== false ) {
                $restrict_ur_name = get_option( 'restrict_ur_name');
            }
            $restrict_ur_name = array_map('strtolower',$restrict_ur_name);
            $restrict_ur_name = str_replace(' ', '_', $restrict_ur_name);
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            $role = $roles[0];
            if( in_array( $role ,$restrict_ur_name ) ){
                return false;
            }

            global $post;
            $oldCommentUrl = get_the_permalink( get_option( 'allcommentpage', 1 ) );
            $button_position_class = get_option( 'buttonposition' );
        ?>
            
            <div id="successMsgSticky" style="display:none;">
                <div class="messageInner">
                    <h5 class="text-center w-100">
                        <span><?php _e('Thank you! Your comments submitted for moderation.', 'wp_super_sticky_notes'); ?></span>
                    </h5>
                </div>
            </div>

            <div class="sticky_note-user-button <?php echo $button_position_class;?>">
                <div id="stickeyItems">
                    <ul class="user-button-ul" style="display:none">
                        <li class="deleteCo">
                        <span class="userHideSticky">
                            <img src="<?php echo $this->plugin_url; ?>/asset/css/images/close.png" alt="Close Icon">
                        </span>
                        </li>
                        <li class="note-new-comment">
                            <a class="commentLink" href="<?php echo get_the_permalink( $post->ID ) . '?note=1' ?>"><?php _e('New Comment', 'wp_super_sticky_notes'); ?></a>
                        </li>
                        <li class="note-old-comments">
                            <a class="commentLink" href="<?php echo $oldCommentUrl; ?>"><?php _e('Old Comment', 'wp_super_sticky_notes'); ?></a>
                        </li>
                    </ul>
                </div>
                <div class="sticky-notes-user">
                    <div class="innericon">
                        <img src="<?php echo $this->plugin_url; ?>/asset/css/images/speech-bubble-32.png" alt="icon">
                    </div>
                </div>
            </div>

        <?php
        }

        
        /**
         * its append add filter line 74
         * User defaults avatar change
         * This function allows the user defaults avatar to customize
         * https://wordpress.stackexchange.com/questions/107915/change-the-default-avatar-admin-option-via-functions-php
        */
        function mytheme_default_avatar( $avatar_defaults ) 
        {

            $myavatar = get_option('wp_ssn_user_avatar');
            $avatar_defaults[$myavatar] = "Crunchify Avatar";
            return $avatar_defaults;
            
        }

        
    } // End Class
} // End Class check if exist / not
?>