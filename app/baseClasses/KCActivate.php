<?php

namespace App\baseClasses;

use App\models\KCAppointmentServiceMapping;
use App\models\KCStaticData;
use App\models\KCService;
use WP_Upgrader ;
use WP_Error;
use App\models\KCServiceDoctorMapping;


class KCActivate extends KCBase {
	public static function activate() {

		// Migrate database and other data...
		self::migrateDatabase();

		// following function call is only for development purpose remove in production mode.
		(new self())->migratePermissions();
		(new self())->addDefaultPosts();
		(new self())->addDefaultOptions();
		(new self())->addDefaultModuleConfig();
		(new self())->addAdministratorPermission();
		(new self())->tableAlterFiled();
		
	}
	public function init() {


        add_action( 'plugins_loaded', function (){
            load_plugin_textdomain( 'kc-lang', false, dirname( KIVI_CARE_BASE_NAME ) . '/languages' );
        });

		global $wpdb;
		// Check page hook
		// Enqueue Front-end assets...
        add_filter( 'the_content', array ($this, 'kcIsPageHasShortCode'));
		if(!get_option(KIVI_CARE_PREFIX.'lang_option')) {

            //condition to check user is new user or exists user
            update_option(KIVI_CARE_PREFIX.'new_user',1);

			$lang_option = [
				'lang_option' => [
					[
						'label' => 'English',
						'id' => 'en'
					],
					[
						'label' => 'Arabic',
						'id' => 'ar'
					],
					[
						'label' => 'Greek',
						'id' => 'gr'
					],
					[
						'label' => 'Franch',
						'id' => 'fr'
					],
					[
						'label' => 'Hindi',
						'id' => 'hi'
					]
				],
			];

			add_option(KIVI_CARE_PREFIX.'lang_option', json_encode($lang_option));

		}

        //enable loco if user is new
        if(kcToCheckUserIsNew()){
            update_option(KIVI_CARE_PREFIX.'locoTranslateState',1);
        }

        // (new self())->versionUpgradePatches();
		if (isset($_REQUEST['page']) && $_REQUEST['page'] === "dashboard") {
			// Enqueue Admin-side assets...
			add_action( 'admin_enqueue_scripts', array($this,'enqueueStyles'));
			add_action( 'admin_enqueue_scripts', array($this,'enqueueScripts'));
		}

		// Enqueue Front-end assets... (Previous version dead code)
		add_action( 'wp_enqueue_scripts', array($this,'enqueueFrontStyles'),9999);
		//add_action( 'wp_enqueue_scripts', array($this,'enqueueFrontScripts'));

        // Append meta tags to header...
		add_action( 'wp_head', array($this,'appendToHeader') );
		add_action( 'admin_head', array($this,'appendToHeader') );

		// Enable Handler...
        $routes = (new KCRoutes())->routes();
        (new KCRoutesHandler($routes, 'App\\controllers\\'))->init();
		( new WidgetHandler )->init();
		(new self())->load_plugin();
		
		// Action to add option in the sidebar...
		add_action( 'admin_menu', array($this, 'adminMenu'));
        add_action( 'enqueue_block_editor_assets', array($this, 'appointmentWidgetBlock'));
        add_action( 'enqueue_block_editor_assets', array($this, 'patientDashboardWidgetBlock'));

        // Action to remove hide sidebar and top-bar...
		add_action('admin_head', array($this, 'hideSideBar'));

		// Action to set email header...
		add_filter( 'wp_mail_content_type', array($this, 'setContentType') );

		// Validate auth user...
		add_filter( 'authenticate', array($this, 'validateAuthUser'), 20, 3 );

		// Redirect user to kivi Users...
		add_filter( 'login_redirect', array($this, 'redirectUserToDashboard'), 10, 3 );

        add_filter( 'block_categories_all', array($this,'addBlockCategories'), 10, 2 );
		
		add_filter( 'woocommerce_prevent_admin_access', array($this, 'kivicare_agent_admin_access') , 20, 1 );

        // Enqueue login page script...
		add_action( 'login_enqueue_scripts', array($this, 'loginPageStyles'), 11 );

		// Hide admin bar...
		add_action('after_setup_theme', array($this, 'removeAdminBar'));

		if ( is_admin() && !get_option( 'is_kivicarepro_upgrade_lang')) {
			add_option('is_kivicarepro_upgrade_lang', 1);
			$this->updateLangFile();
		}

		// add new language translation key in 2.2.8
		if ( is_admin() && !get_option( 'is_lang_version_4')) {
			add_option('is_lang_version_4', 1);
			$this->getLangCopy();
			$this->mergeJson();
		}
        add_action( 'elementor/elements/categories_registered', 'kcElementerCategoryRegistered' );
        add_action( 'elementor/widgets/widgets_registered', 'kcAddElementorWidget');

        //add patient reminder cron job
        $appointment_reminder_data = get_option(KIVI_CARE_PREFIX . 'email_appointment_reminder',true);

        if (gettype($appointment_reminder_data) != 'boolean') {
            if( isset($appointment_reminder_data['time']) && ((isset($appointment_reminder_data['status'])
				&& $appointment_reminder_data['status'] == 'on') || (isset($appointment_reminder_data['sms_status'])
				&& $appointment_reminder_data['sms_status'] =='on') || (isset($appointment_reminder_data['whatapp_status'])
				&& $appointment_reminder_data['whatapp_status'] == 'on'))) {
                kcAddCronJob('kivicare_patient_appointment_reminder','patientAppointmentReminder');
            }
        }

		// kivicare version 2.3.0
		if ( is_admin() && !get_option( 'kivicare_version_2_3_0')) { 
			add_option('kivicare_version_2_3_0', 1);
			update_option(KIVI_CARE_PREFIX.'request_helper_status', 'off');
		}

        add_action( 'template_redirect', [ $this, 'kcEnableLocoTranslate' ] );

    }
	
	public function kcIsPageHasShortCode ($content) {
        wp_enqueue_style( 'kc_font_awesome', $this->plugin_url . 'assets/css/font-awesome-all.min.css', array(), KIVI_CARE_VERSION );
        wp_enqueue_style('kc_font_awesome');
        $get_site_logo = get_option(KIVI_CARE_PREFIX.'site_logo');
        $site_logo  = isset($get_site_logo) && $get_site_logo!= null && $get_site_logo!= '' ? wp_get_attachment_url($get_site_logo) : $this->plugin_url.'assets/images/logo-banner.png';
        if ( has_shortcode( $content, 'bookAppointment' )
            || has_shortcode( $content, 'patientDashboard' ) ) {
            wp_enqueue_style( 'kc_front_app_min_style', $this->plugin_url . 'assets/css/front-app.min.css', array(), KIVI_CARE_VERSION );
            wp_dequeue_style( 'stylesheet' );
            wp_enqueue_script( 'kc_front_js_bundle', $this->plugin_url . 'assets/js/front-app.min.js', ['jquery'], KIVI_CARE_VERSION, true );
            wp_localize_script( 'kc_front_js_bundle', 'ajaxData', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce('ajax_post'),
                'loaderImage' =>  kcPluginLoader(),
                'kiviCarePluginURL' => $this->plugin_url,
				'homePage' => get_home_url(),
                'site_logo' => $site_logo
            ) );
            wp_localize_script( 'kc_front_js_bundle', 'request_data', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce('ajax_post'),
                'kiviCarePluginURL' => $this->plugin_url,
                'loaderImage' =>  kcPluginLoader(),
				'homePage' => get_home_url(),
                'site_logo' => $site_logo
            ) );

            wp_enqueue_script( 'kc_front_js_bundle' );
            wp_enqueue_script( 'kc_custom', $this->plugin_url . 'assets/js/custom.js', ['jquery'], KIVI_CARE_VERSION, true );
            wp_localize_script( 'kc_custom', 'localize_data', array(
                "color" => get_option(KIVI_CARE_PREFIX.'theme_color')
            ) );

        }
		//deadcode previous version
		//      add_action( 'wp_enqueue_scripts', array($this,'enqueueFrontStyles'));
		//      add_action( 'wp_enqueue_scripts', array($this,'enqueueFrontScripts'));
		//		$page_object = get_queried_object();
		//    	$kiViCareShortCode = ['bookAppointment', 'patientDashboard'];
		//    	foreach ($kiViCareShortCode as $key => $value) {
		//			if(!empty($page_object) && $page_object->post_content !== null) {
		//				if (strpos($page_object->post_content, '[' . $value . ']') !== false) {
		//					add_action( 'wp_enqueue_scripts', array($this,'enqueueFrontStyles'));
		//					add_action( 'wp_enqueue_scripts', array($this,'enqueueFrontScripts'));
		//				}
		//			}
		//    	}
        return $content ;
	}
	public function load_plugin () {
		if ( is_admin() && !get_option( 'is_upgrade_2.1.5')) {
            add_option('is_upgrade_2.1.5', 0);
			require KIVI_CARE_DIR . 'app/database/kc-custom-field-data-db.php';
		}
	}
	public function tableAlterFiled(){
        global $wpdb;
        $table_patient_encounter = $wpdb->prefix . 'kc_patient_encounters';
        $new_fields = [
            'clinic_id' => 'bigint',
        ];
            
        kcUpdateFields($table_patient_encounter,$new_fields);

        $table_billing = $wpdb->prefix . 'kc_bills';
        $new_fields = [
            'clinic_id' => 'bigint',
        ];
        kcUpdateFields($table_billing,$new_fields);
    }
	public function updateLangFile(){

		$temp_file = KIVI_CARE_DIR_URI.'resources/assets/lang/temp.json';

		$dir_name = KIVI_CARE_PREFIX.'lang';
		
		$upload_dir = wp_upload_dir(); 
		$user_dirname = $upload_dir['basedir'] . '/' . $dir_name;
		$get_user_lang = get_option(KIVI_CARE_PREFIX.'lang_option');
		$data = json_decode($get_user_lang,true);
		if(!file_exists($user_dirname)) {
			wp_mkdir_p( $user_dirname ); 
		}
		foreach ($data['lang_option'] as $key => $value) {
			$current_file = KIVI_CARE_DIR_URI.'resources/assets/lang/'.$value['id'].'.json';
			$old_file  = $user_dirname.'/'.$value['id'].'.json';
			if(!file_exists($old_file)){
				$data = file_get_contents($current_file);
				file_put_contents($user_dirname.'/'.$value['id'].'.json', $data);
				chmod($old_file, 0777); 
			}else{
				$old_file_contente = file_get_contents($old_file);
				$new_file = KIVI_CARE_DIR_URI.'resources/assets/lang/en.json';
				$new_file_contente = file_get_contents($new_file);
				urlencode($old_file);
				file_put_contents($user_dirname.'/'.$value['id'].'.json', json_encode(array_merge(json_decode($new_file_contente, true),json_decode($old_file_contente, true))));
			}
		}
		if(!file_exists($temp_file)){
			$media_temp = $user_dirname.'/temp.json';
			$temp_file_data = file_get_contents($temp_file);
			file_put_contents($user_dirname.'/temp.json', $temp_file_data);
			chmod($media_temp, 0777); 
		}
	}
	public function validateAuthUser( $user ) {
		if( isset($user->data->user_status) && (int)$user->data->user_status === 4 ) {
			$error = new WP_Error();
			$error->add( 403, esc_attr__('Login has been disabled. please contact you system administrator. ') );
			return $error;
		}
		return $user;
	}
	public function removeAdminBar() {
		if (!current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
		}
	}
	public function adminMenu () {
		$site_title = get_bloginfo('name');
		add_menu_page( __( $site_title ), 'Kivicare' , kcGetPermission('dashboard'), 'dashboard/', [$this, 'adminDashboard'], $this->plugin_url . 'assets/images/sidebar-icon.svg', 99);
	}
	public function adminDashboard() {
        $langType = get_option(KIVI_CARE_PREFIX.'locoTranslateState');
		if(isKiviCareProActive() && ($langType != 1 || $langType != '1')) {
			$upload_dir = wp_upload_dir();
			$dir_name = KIVI_CARE_PREFIX.'lang';
			$user_dirname = $upload_dir['basedir'] . '/' . $dir_name;
			$current_lang_file = $user_dirname.'/temp.json';
			if(file_exists($current_lang_file) && filesize($current_lang_file) > 2000) { 
				include(KIVI_CARE_DIR . 'resources/views/kc_dashboard.php');
			} else {
				include(KIVI_CARE_DIR . 'resources/views/kc_notice.php');
			}
		} else {
			include(KIVI_CARE_DIR . 'resources/views/kc_dashboard.php');
		}
	}
	public function enqueueStyles() {
		wp_enqueue_style( 'kc_google_fonts', $this->plugin_url . 'assets/css/poppins-google-fonts.css', array(), KIVI_CARE_VERSION );
		wp_enqueue_style( 'kc_app_min_style', $this->plugin_url . 'assets/css/app.min.css' , array(), KIVI_CARE_VERSION );
		wp_enqueue_style( 'kc_font_awesome', $this->plugin_url . 'assets/css/font-awesome-all.min.css' , array(), KIVI_CARE_VERSION );
        wp_dequeue_style( 'stylesheet' );
        wp_dequeue_style( 'stylesheet' );
        wp_deregister_style('wp-admin');
    }
    public function loginPageStyles() {
	    wp_enqueue_style( 'kc_app_min_style', $this->plugin_url . 'assets/css/app.min.css', array(), KIVI_CARE_VERSION  );
    }
	public function enqueueFrontStyles() {
        wp_enqueue_style( 'kc_elementor', $this->plugin_url . 'assets/css/kcElementor.css', array(), KIVI_CARE_VERSION );
    }
	public function enqueueScripts() {
		wp_enqueue_script( 'kc_js_bundle', $this->plugin_url . 'assets/js/app.min.js', ['jquery'], KIVI_CARE_VERSION,true);
        wp_enqueue_script( 'google-platform', 'https://apis.google.com/js/api.js', array(), KIVI_CARE_VERSION,true );	
        wp_enqueue_script( 'kc_custom', $this->plugin_url . 'assets/js/custom.js', ['jquery'], KIVI_CARE_VERSION,true );

        wp_localize_script( 'kc_custom', 'localize_data', array(
            "color" => get_option(KIVI_CARE_PREFIX.'theme_color')
        ));

        wp_localize_script( 'kc_js_bundle', 'request_data', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce('ajax_post'),
            'kiviCarePluginURL' => $this->plugin_url,
            'loaderImage' =>  kcPluginLoader(),
            'homePage' => get_home_url(),
            'new_user' => kcToCheckUserIsNew() ? 'true' : 'false',
		) );
		wp_enqueue_script( 'Js_bundle' );
	}
	public function enqueueFrontScripts() {
		wp_enqueue_script( 'kc_front_js_bundle', $this->plugin_url . 'assets/js/front-app.min.js', ['jquery'], KIVI_CARE_VERSION, true );
		wp_localize_script( 'kc_front_js_bundle', 'ajaxData', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce('ajax_post'),
			'kiviCarePluginURL' => $this->plugin_url,
            'loaderImage' => kcPluginLoader()
		) );
        wp_enqueue_script( 'kc_front_js_bundle' );
        wp_enqueue_script( 'kc_custom', $this->plugin_url . 'assets/js/custom.js', ['jquery'], KIVI_CARE_VERSION, true );
        wp_localize_script( 'kc_custom', 'localize_data', array(
            "color" => get_option(KIVI_CARE_PREFIX.'theme_color')
        ) );
	}
	public function appendToHeader () {
        $prefix = KIVI_CARE_PREFIX;
        $upload_dir = wp_upload_dir();
        $dir_name = $prefix .'lang';
        $user_dirname = $upload_dir['baseurl'] . '/' . $dir_name;
        $get_config =   get_option( KIVI_CARE_PREFIX . 'google_cal_setting',true);
        if(gettype($get_config) != 'boolean'){
            $client_id = $get_config['client_id'];
        }else{
            $client_id = '';
        }
        echo '<meta name="pluginBASEURL" content="' . $this->plugin_url .'" />';
        echo '<meta name="pluginPREFIX" content="' . $this->getPluginPrefix() .'" />';
        echo '<meta name="pluginMediaPath" content="' .$user_dirname .'" />';
        echo '<meta name="google-signin-client_id" content="'.$client_id.'" />';
	}
	public function setContentType() {
		return 'text/html';
	}
	public function addAdministratorPermission () {
		$admin_permissions = kcGetAdminPermissions()->pluck('name')->toArray();
		if (count($admin_permissions)) {
			$admin_role = get_role( 'administrator' );
			foreach ($admin_permissions as $permission) {
				$admin_role->add_cap( $permission, true );
			}
		}
	}
	public function migratePermissions() {

		remove_role($this->getClinicAdminRole());
		remove_role($this->getDoctorRole());
		remove_role($this->getPatientRole());
		remove_role($this->getReceptionistRole());

		$clinic_admin_permissions = kcGetAdminPermissions()->pluck('name')->toArray();
		$doctor_permissions = kcGetDoctorPermission()->pluck('name')->toArray();
		$patient_permissions = kcGetPatientPermissions()->pluck('name')->toArray();
		$receptionist_permissions = kcGetReceptionistPermission()->pluck('name')->toArray();

		// Assign permission to Clinic admin role...
		add_role($this->getClinicAdminRole(), 'Clinic admin', array_fill_keys($clinic_admin_permissions, 1));

		// Assign permission to Doctor role...
		add_role($this->getDoctorRole(), 'Doctor', array_fill_keys($doctor_permissions, 1));

		// Assign permission to Patient role...
		add_role($this->getPatientRole(), 'Patient', array_fill_keys($patient_permissions, 1));

        // Assign permission to Receptionist role...
		add_role($this->getReceptionistRole(), 'Receptionist', array_fill_keys($receptionist_permissions, 1));

		return true ;
	}
	public function hideSideBar() {
		if(isset($_REQUEST['page']) && $_REQUEST['page'] === "dashboard") {
			echo '<style type="text/css">
					#wpcontent, #footer { margin-left: 0px !important;padding-left: 0px !important; }
					html.wp-toolbar { padding-top: 0px !important; }
					#adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter,#adminmenumain, #screen-meta { display: none !important; }
					
				</style>';
		}
	}
    public function appointmentWidgetBlock(){
        wp_enqueue_script(
            'kivi-care-appointment-widget',
            $this->plugin_url . 'assets/js/KC-appointment-block.js',
			array('wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-components',
				'wp-editor'
			)
			, KIVI_CARE_VERSION, true
        );
    }
    public function patientDashboardWidgetBlock(){
        wp_enqueue_script(
            'kivi-care-patient-dashboard-widget',
            $this->plugin_url . 'assets/js/kc-patient-dashboard-block.js',
            array('wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-editor'
            ), 
			KIVI_CARE_VERSION, true
        );
    }
    public function addBlockCategories( $categories ) {
        $category_slugs = wp_list_pluck( $categories, 'slug' );
        return array_merge( array(
            array(
                'slug'  => 'kivi-appointment-widget',
                'title' => 'KiVi Care',
            ), ),
            $categories
        );
    }
	public function redirectUserToDashboard( $redirect_to, $request, $user ) {

		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			$redirect = false;
			// check for other user roles...
			if (in_array( $this->getClinicAdminRole(), $user->roles ) ) {
				$redirect = true;
			} elseif (in_array( $this->getReceptionistRole(), $user->roles )) {
				$redirect = true;
			} elseif (in_array( $this->getDoctorRole(), $user->roles )) {
				$redirect = true;
			} elseif (in_array( $this->getPatientRole(), $user->roles )) {
				$redirect = true;
			}

			if ($redirect) {
				$redirect_to = get_admin_url() . 'admin.php?page=dashboard#/'; // Your redirect URL
			}
		}

		return $redirect_to;
	}
	public static function migrateDatabase () {
		require KIVI_CARE_DIR . 'app/database/kc-service-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-static-data-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-clinic-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-clinic-session-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-doctor-clinic-mapping-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-service-doctor-mapping-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-receptionist-clinic-mapping-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-clinic-schedule-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-medical-problem-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-prescription-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-appointment-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-appointment-service-mapping-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-patient-encounter-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-bill-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-bill-items-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-custom-field-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-custom-field-data-db.php';
		require KIVI_CARE_DIR . 'app/database/kc-medical-history-db.php';
	}
	public function addDefaultOptions () {

        $steps = $this->getSetupSteps();
		$moduleSetting = [
            $steps => 4,
			'common_setting' => [
				'patient_reminder' =>
					[
						'label' => 'Patient appointment reminder switch',
						'status' => 1
					]
			]
		];

		foreach ($moduleSetting as $key => $value) {
			add_option($key , $value);
		}

		$setup_config_name = KIVI_CARE_PREFIX . 'setup_config';
		add_option($setup_config_name, json_encode(kcGetSetupWizardOptions()));

		if (!get_option( 'is_kivicarepro_upgrade_lang')) {
			add_option('is_kivicarepro_upgrade_lang', 1);
			$this->updateLangFile();
		}

		if (!get_option( 'is_lang_version_2')) {
			add_option('is_lang_version_2', 1);
			$this->getLangCopy();
			$this->mergeJson();
		}
		   
		if(!get_option( KIVI_CARE_PREFIX . 'woocommerce_payment' )) {
			update_option( KIVI_CARE_PREFIX . 'woocommerce_payment', 'off', 'no' );
		}
		
	}
	public function addDefaultPosts () {

		$prefix = KIVI_CARE_PREFIX;

		$mail_template = $prefix.'mail_tmp' ;
        $default_email_template = [
            [
                'post_name' => $prefix.'book_appointment',
                'post_content' => '<p> Welcome to KiviCare ,</p><p> Your appointment has been booked  successfully on </p><p> {{appointment_date}}  , Time : {{appointment_time}}  </p><p> Thank you. </p>',
                'post_title' => 'Patient Appointment Booking Template',
                'post_type' => $mail_template,
                'post_status' => 'publish',
            ],
            [
                'post_name' => $prefix.'book_appointment_reminder',
                'post_content' => '<p> Welcome to KiviCare ,</p><p> You Have appointment  on </p><p> {{appointment_date}}  , Time : {{appointment_time}}  </p><p> Thank you. </p>',
                'post_title' => 'Patient Appointment Reminder',
                'post_type' => $mail_template,
                'post_status' => 'publish',
            ],
        ];

        $commonTemplate = kcCommonTemplate('mail');

        $default_email_template = array_merge($default_email_template,$commonTemplate);

        kcAddMailSmsPosts($default_email_template);

	}
	public function addDefaultModuleConfig() {

		$prefix = $this->getPluginPrefix();

		$modules = [
			'module_config' => [
				[
					'name' => 'receptionist',
					'label' => 'Receptionist',
					'status' => '1'
				],
				[
					'name' => 'billing',
					'label' => 'Billing',
					'status' => '1'
				],
				[
					'name' => 'custom_fields',
					'label' => 'Custom Fields',
					'status' => '1'
				]
			],
			'common_setting' => [],
			'notification' => []
		];

		delete_option($prefix.'modules');
		add_option( $prefix.'modules', json_encode($modules));
		
	}
	public function versionUpgradePatches () {
		require KIVI_CARE_DIR . 'app/upgrade/kc-default-value-upgrade.php';
    }
	public function kivicare_agent_admin_access( $prevent_access ) {
        if( current_user_can('read') ) $prevent_access = false;
        return $prevent_access; 
    }
	public function mergeJson(){

		//upload dir
		$upload_dir = wp_upload_dir(); 
		$dir_name = KIVI_CARE_PREFIX.'lang';
		$user_dirname = $upload_dir['basedir'] . '/' . $dir_name;

		//get latest key from the en.json
		$newEn = KIVI_CARE_DIR_URI.'resources/assets/lang/en.json';
		$enContent =  file_get_contents($newEn);
		
		//get all lang of user from the database.
		$get_user_lang = get_option(KIVI_CARE_PREFIX.'lang_option');
		$data = json_decode($get_user_lang,true); 

		//store merge data.
		$output = [];

		//merge new en content in all database file.
		foreach ($data['lang_option'] as $key => $value) {
			
			//get all file based on databse lang value.
			$all_database_file  = $user_dirname.'/'.$value['id'].'.json';
			$all_file_content = file_get_contents($all_database_file);

			urlencode($all_database_file);

			// get file value in multidimention array.
			$value_arry = json_decode($all_file_content, true);

			//merge new value in all database file.
			foreach (json_decode($enContent,true) as $key => $lang) {
				$output[$key] = array_merge($lang,!empty($value_arry[$key]) ? $value_arry[$key] : []);
			}

			//put all new keys in all file.
			file_put_contents($user_dirname.'/'.$value['id'].'.json', json_encode($output));
		}
		
		//get temp file
		$current_lang_file = $user_dirname.'/temp.json';
		if(file_exists($current_lang_file)){
			//put all temp content in below array.
			$temp_output = [];

			//get temp file value
			$get_current_lang_content = file_get_contents($current_lang_file);
			urlencode($current_lang_file);

			foreach (json_decode($enContent,true) as $key => $lang) {

				// merge all key in temp
				$temp_output[$key] = array_merge($lang,!empty($get_current_lang_content[$key]) ? $get_current_lang_content[$key] : []);
			}

			//put all key in temp file.
			file_put_contents($current_lang_file, json_encode($temp_output));
		}else{
			file_put_contents($current_lang_file, json_encode($enContent));
		}
	}
	public function getLangCopy(){
		$upload_dir = wp_upload_dir(); 
		$backup_dir_name = $upload_dir['basedir'].'/'.KIVI_CARE_PREFIX.'backup';
		$backup_folder =  $upload_dir['basedir'].'/'.KIVI_CARE_PREFIX.'backup/'.current_time('Y-m-d') .'_lang';

		$dir_name = KIVI_CARE_PREFIX.'lang';
		$user_dirname = $upload_dir['basedir'] . '/' . $dir_name;

		if(!file_exists($backup_dir_name)) {
			wp_mkdir_p( $backup_dir_name ); 
		}
		if(!file_exists($backup_folder)){
			wp_mkdir_p( $backup_folder ); 
			$get_user_lang = get_option(KIVI_CARE_PREFIX.'lang_option');
			$data = json_decode($get_user_lang,true); 
			foreach ($data['lang_option'] as $key => $value) {
				$old_file  = $user_dirname.'/'.$value['id'].'.json';
				$new = $backup_folder.'/'.$value['id'].'.json';
				$data = file_get_contents($old_file);
				file_put_contents($new,$data);
				chmod($new, 0777); 
			}
			$old_temp  = $user_dirname.'/temp.json';
			$new_temp = $backup_folder.'/temp.json';
			$temp_data = file_get_contents($old_temp);
			file_put_contents($new_temp,$temp_data);
			chmod($new_temp, 0777); 
		}
	}

   public function kcEnableLocoTranslate(){
       if(isset($_GET['kcEnableLocoTranslation'])) {
           update_option(KIVI_CARE_PREFIX.'locoTranslateState', 1);
           wp_redirect(admin_url( 'admin.php?page=dashboard' ));
       }
   }

}
