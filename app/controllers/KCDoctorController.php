<?php

namespace App\Controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCAppointment;
use App\models\KCDoctorClinicMapping;
use App\models\KCClinicSession;
use App\models\KCClinicSchedule;
use App\models\KCServiceDoctorMapping;
use App\models\KCReceptionistClinicMapping;
use App\models\KCService;
use App\models\KCClinic;
use App\controllers\KCServiceController;

use DateTime;
use Exception;
use WP_User;

class KCDoctorController extends KCBase
{

    public $db;

    private $request;

    public function __construct()
    {

        global $wpdb;

        $this->db = $wpdb;

        $this->request = new KCRequest();

    }

    public function index()
    {

        $permission_message = esc_html__('You don\'t have a permission to access', 'kc-lang');
        $table_name = $this->db->prefix . 'kc_' . 'doctor_clinic_mappings';

        if (!kcCheckPermission('doctor_list')) {
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();
        $doctorsCount = get_users([
            'role' => $this->getDoctorRole(),
        ]);

        $doctorsCount = count($doctorsCount);

        $args['role'] = $this->getDoctorRole();
        $args['number'] = $request_data['limit'];
        $args['offset'] = (isset($request_data['offset']) ? $request_data['offset'] : 0 );
        $args['search_columns'] = (isset($request_data['offset'])? [$request_data['searchKey']] : '');
        $args['search'] = (isset($request_data['searchValue'])? '*' . $request_data['searchValue'] . '*' : '' ) ;
        $args['orderby'] = 'ID';
        $args['order'] = 'DESC';

        if (current_user_can('administrator')) {
            $doctors = get_users($args);

        } else {
            $user_id = get_current_user_id();
            switch ($this->getLoginUserRole()) {
                case 'kiviCare_receptionist':
                    $clinic_id = (new KCReceptionistClinicMapping())->get_by(['receptionist_id' => $user_id]);
                    $query = "SELECT DISTINCT `doctor_id` FROM {$table_name} WHERE `clinic_id` =" . $clinic_id[0]->clinic_id;

                    break;
                case 'kiviCare_clinic_admin':
                    $clinic_id = (new KCClinic())->get_by(['clinic_admin_id' => $user_id]);
                    $query = "SELECT DISTINCT `doctor_id` FROM {$table_name} WHERE `clinic_id` =" . $clinic_id[0]->id;

                    break;
                default:
                    # code...
                    break;
            }

            $result = collect($this->db->get_results($query))->pluck('doctor_id');
            $doctors = get_users($args);
            $doctors = collect($doctors)->whereIn('ID', $result)->values();
        }

        if (!count($doctors)) {
            echo json_encode([
                'status' => false,
                'message' => esc_html__('No doctors found', 'kc-lang'),
                'data' => []
            ]);
            wp_die();
        }

        $data = [];
        $doctor_name = [];
        foreach ($doctors as $key => $doctor) {

            $user_meta = get_user_meta($doctor->ID, 'basic_data', true);
            $table = $this->db->prefix . 'kc_' . 'doctor_clinic_mappings';
            $table_clinic = $this->db->prefix . 'kc_' . 'clinics';
            $clinic = "SELECT (SELECT name FROM {$table_clinic} WHERE id= doctor.clinic_id ) as label FROM {$table} as `doctor` WHERE doctor_id =" . $doctor->ID;
            $clinics = collect($this->db->get_results($clinic))->pluck('label')->values()->implode(",");
            $data[$key]['ID'] = $doctor->ID;
            $data[$key]['display_name'] = $doctor->data->display_name;
            $data[$key]['user_email'] = $doctor->data->user_email;
            $data[$key]['user_status'] = $doctor->data->user_status;
            $data[$key]['user_registered'] = $doctor->data->user_registered;
            // $data[$key]['clinic_id'] = $clinic_mapping[0]->clinic_id;
            $data[$key]['clinic_name'] = $clinics;
            if(!empty($user_meta)) {
                $basic_data = json_decode($user_meta);
                if(!empty($basic_data)) { 
                    $data[$key]['mobile_number'] = $basic_data->mobile_number;
                    $data[$key]['gender'] = $basic_data->gender;
                    $data[$key]['dob'] = $basic_data->dob;
                    $data[$key]['address'] = $basic_data->address;
                    $data[$key]['specialties'] = $basic_data->specialties;
                }
            }
        }

        echo json_encode([
            'status' => true,
            'message' => esc_html__('Doctors list', 'kc-lang'),
            'data' => $data,
            'total_rows' => $doctorsCount
        ]);
    }

    public function save()
    {

        $active_domain = $this->getAllActivePlugin();

        global $wpdb;

        $permission_message = esc_html__('You don\'t have a permission to access', 'kc-lang');

        $is_permission = false;

        if (kcCheckPermission('doctor_add') || kcCheckPermission('doctor_profile')) {
            $is_permission = true;
        }

        if (!$is_permission) {
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();
        $request_data['specialties'] = json_decode(stripslashes($request_data['specialties']));
        $request_data['qualifications'] = json_decode(stripslashes($request_data['qualifications']));
        $request_data['clinic_id'] = json_decode(stripslashes( $request_data['clinic_id']));
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'user_email' => 'required|email',
            'mobile_number' => 'required',
            //'dob' => 'required',
            'gender' => 'required',
        ];

        $errors = kcValidateRequest($rules, $request_data);

        $username = kcGenerateUsername($request_data['first_name']);

        $password = kcGenerateString(12);

        $service_doctor_mapping = new KCServiceDoctorMapping();

        if (!empty(count($errors))) {
            echo json_encode([
                'status' => false,
                'message' => esc_html__($errors[0], 'kc-lang')
            ]);
            die;
        }
     
        $temp = [
            'mobile_number' => $request_data['mobile_number'],
            'gender' => $request_data['gender'],
            'dob' => $request_data['dob'],
            'address' => $request_data['address'],
            'city' => $request_data['city'],
            'state' => kcCheckEmpty($request_data,'state'),
            // previous version dead code
            // 'state' => $request_data['state'],
            'country' => $request_data['country'],
            'postal_code' => $request_data['postal_code'],
            'qualifications' => $request_data['qualifications'],
            'price_type' => $request_data['price_type'],
            'price' => $request_data['price'],
            'no_of_experience' => $request_data['no_of_experience'],
            'video_price' => isset($request_data['video_price']) ? $request_data['video_price'] : 0,
            'specialties' => $request_data['specialties'],
            'time_slot' => $request_data['time_slot']
        ];

        if (isset($request_data['price_type']) && $request_data['price_type'] === "range") {
            $temp['price'] = $request_data['minPrice'] . '-' . $request_data['maxPrice'];
        }

        if (!isset($request_data['ID'])) {
            $user = wp_create_user($username, $password, $request_data['user_email']);
            $u = new WP_User($user);
            $u->display_name = $request_data['first_name'] . ' ' . $request_data['last_name'];
            wp_insert_user($u);

            $u->set_role($this->getDoctorRole());

            $user_id = $u->ID;

            if ($this->getLoginUserRole() == 'kiviCare_receptionist') {
                $receptionis_id = get_current_user_id();
                $clinic_id = (new KCReceptionistClinicMapping())->get_by(['receptionist_id' => $receptionis_id]);
                $request_data['clinic_id'] = $clinic_id[0]->clinic_id;
            }
            if($this->getLoginUserRole() == 'kiviCare_clinic_admin'){
                $clinic = (new KCClinic())->get_by([ 'clinic_admin_id' => get_current_user_id()]);
                $request_data['clinic_id'] = $clinic[0]->id;
            }

            // Insert Doctor Clinic mapping...
            $doctor_mapping = new KCDoctorClinicMapping;
            if ($active_domain === $this->kiviCareProOnName()) {
            //   $request_data['clinic_id'] = str_replace('\\', '', $request_data['clinic_id']);
                if (gettype($request_data['clinic_id']) === 'array') {
                    foreach ($request_data['clinic_id'] as $value) {
                        $new_temp = [
                            'doctor_id' => $user_id,
                            'clinic_id' => $value->id,
                            'owner' => 0,
                            'created_at' => current_time('Y-m-d H:i:s')
                        ];
                        $doctor_mapping->insert($new_temp);
                    }
                }else{
                    $new_temp = [
                        'doctor_id' => $user_id,
                        'clinic_id' => isset($request_data['clinic_id']) ? $request_data['clinic_id'] : 1,
                        'owner' => 0,
                        'created_at' => current_time('Y-m-d H:i:s')
                    ];
                    $doctor_mapping->insert($new_temp);
                }
            } else {
                $new_temp = [
                    'doctor_id' => $user_id,
                    'clinic_id' => kcGetDefaultClinicId(),
                    'owner' => 0,
                    'created_at' => current_time('Y-m-d H:i:s')
                ];

                $doctor_mapping->insert($new_temp);
            }
            
            update_user_meta($user, 'first_name', $request_data['first_name']);
			update_user_meta($user, 'last_name', $request_data['last_name']);
            update_user_meta($user, 'basic_data', json_encode($temp, JSON_UNESCAPED_UNICODE));
            if(isset($request_data['description'])){
                update_user_meta($user, 'doctor_description',$request_data['description'] );
            }

            $request_data['custom_fields'] = json_decode(stripslashes( $request_data['custom_fields']));

            if (isset($request_data['custom_fields']) && $request_data['custom_fields'] !== []) {

                kvSaveCustomFields('doctor_module', $user, $request_data['custom_fields']);
            }

            // telemed service charges
            if(isset($request_data['enableTeleMed'])) {

                $data['type'] = 'telemed';

                $telemed_service_id = getServiceId($data);

                if (isset($telemed_service_id[0])) {

                    $telemed_Service = $telemed_service_id[0]->id;

                } else {

                    $service_data = new KCService;
                    $services = [
                        'type' => 'system_service',
                        'name' => 'telemed',
                        'price' => 0,
                        'status' => 1,
                        'created_at' => current_time('Y-m-d H:i:s')
                    ];
                    $telemed_Service = $service_data->insert($services);

                }

                $service_doctor_mapping->insert([
                    'service_id' => $telemed_Service,
                    'clinic_id' => kcGetDefaultClinicId(),
                    'doctor_id' => $user_id,
                    'charges' => $temp['video_price']
                ]);

                apply_filters('kct_save_zoom_configuration', [
                    'user_id' => $user_id,
                    'enableTeleMed' => $request_data['enableTeleMed'],
                    'api_key' => $request_data['api_key'],
                    'api_secret' => $request_data['api_secret']
                ]);

            }

            $user_email_param = kcCommonNotificationUserData($user_id,$password);

            kcSendEmail($user_email_param);
            if(kcCheckSmsOptionEnable()){
                $sms = apply_filters('kcpro_send_sms', [
                    'type' => 'doctor_registration',
                    'user_data' => $user_email_param,
                ]);
            }

            $message = 'Doctor has been saved successfully';

        } else {

            $usersss = email_exists($request_data['user_email']);


			if(!empty($usersss)) {
				if($request_data['ID'] != $usersss ) {
					echo json_encode([
						'status'  => false,
						'message' => esc_html__('Doctor email already exist.', 'kc-lang')
					]); die;
				}
			}

            // echo '<pre>';
            // echo $request_data['ID'] ; 'sadsasdsdasd';
            // print_r($usersss); die;

            $doctor_mapping = new KCDoctorClinicMapping;

            wp_update_user(
                array(
                    'ID' => $request_data['ID'],
                //  'user_login' => $request_data['username'],
                    'user_email' => $request_data['user_email'],
                    'display_name' => $request_data['first_name'] . ' ' . $request_data['last_name']
                )
            );

            $user_id = $request_data['ID'];
            if ($active_domain === $this->kiviCareProOnName()) {
               if(gettype($request_data['clinic_id']) === 'array'){
                   (new KCDoctorClinicMapping())->delete(['doctor_id' => $request_data['ID']]);
                   foreach ($request_data['clinic_id'] as $value) {
                       $new_temp = [
                           'doctor_id' => $user_id,
                           'clinic_id' => $value->id,
                           'owner' => 0,
                           'created_at' => current_time('Y-m-d H:i:s')
                       ];
                       $doctor_mapping->insert($new_temp);
                   }
               }
            }

            update_user_meta($request_data['ID'], 'first_name',$request_data['first_name'] );
			update_user_meta($request_data['ID'], 'last_name', $request_data['last_name'] );
            update_user_meta($request_data['ID'], 'basic_data', json_encode($temp, JSON_UNESCAPED_UNICODE));
            if(isset($request_data['description'])){
                update_user_meta($request_data['ID'], 'doctor_description',$request_data['description'] );
            }

            // hook for doctor update
            do_action( 'kc_doctor_update', $user_id );

            if(isset($request_data['enableTeleMed'])) {

                $data['type'] = 'telemed';

                $telemed_service_id = getServiceId($data);

                if (isset($telemed_service_id[0])) {

                    $telemed_Service = $telemed_service_id[0]->id;

                } else {

                    $service_data = new KCService;

                    $services = [
                        'type' => 'system_service',
                        'name' => 'telemed',
                        'price' => 0,
                        'status' => 1,
                        'created_at' => current_time('Y-m-d H:i:s')
                    ];

                    $telemed_Service = $service_data->insert($services);
                }

                $service = new KCServiceController();
               
                $doctor_telemed_service = $service_doctor_mapping->get_by(['service_id' => $telemed_Service, 'doctor_id' => $user_id]);
                
                if (!empty($doctor_telemed_service) > 0) {

                    $woo_product_id = $service->getProductIdOfService($doctor_telemed_service[0]->id);
                    if($woo_product_id != null &&  get_post_status( $woo_product_id )){
                        update_post_meta($woo_product_id,'_price', $request_data['video_price']);
                        update_post_meta($woo_product_id,'_sale_price', $request_data['video_price']);
                    }
                    $service_doctor_mapping->update(['charges' => $temp['video_price']], [
                        'service_id' => $telemed_Service,
                        // 'clinic_id' => kcGetDefaultClinicId(),
                        'doctor_id' => $user_id
                    ]);
                } else {
                    $service_doctor_mapping->insert([
                        'service_id' => $telemed_Service,
                        // 'clinic_id' => kcGetDefaultClinicId(),
                        'doctor_id' => $user_id,
                        'charges' => $temp['video_price']
                    ]);
                }

                apply_filters('kct_save_zoom_configuration', [
                    'user_id' => $user_id,
                    'enableTeleMed' => $request_data['enableTeleMed'],
                    'api_key' => $request_data['api_key'],
                    'api_secret' => $request_data['api_secret'],
                    'zoom_id'=>$request_data['zoom_id']
                ]);

            }
            
            // $request_data['custom_fields'] = json_decode(stripslashes( $request_data['custom_fields']));
            // dd($request_data['custom_fields']);

            if (isset($request_data['custom_fields']) && $request_data['custom_fields'] !== []) {
                $request_data['custom_fields'] = json_decode(stripslashes( $request_data['custom_fields']));
                kvSaveCustomFields('doctor_module', $request_data['ID'], $request_data['custom_fields']);
            }
            $message = 'Doctor has been updated successfully';

        }

        if ($user_id) {
            // hook for doctor save
            do_action( 'kc_doctor_save', $user_id );
            $user_table_name = $wpdb->prefix . 'users';
            $user_status = $request_data['user_status'];
            $wpdb->update($user_table_name, ['user_status' => $user_status], ['ID' => $user_id]);
        }

        if($request_data['profile_image'] != '' && isset($request_data['profile_image']) && $request_data['profile_image'] != null ) {
            $attachment_id = media_handle_upload('profile_image', 0);
            if(!is_wp_error($attachment_id)) {
                update_user_meta( $user_id, 'doctor_profile_image',  $attachment_id  );
            } else {
                if(gettype($request_data['profile_image']) != 'string') {
                    echo json_encode([
                        'status' => false,
                        'message' => esc_html__('Failed to upload profile image.', 'kc-lang')
                    ]); die;
                }
            }
        }

        if (!empty($user->errors)) {
            echo json_encode([
                'status' => false,
                'message' => esc_html__($user->get_error_message() ? $user->get_error_message() : 'Doctor data save operation has been failed', 'kc-lang')
            ]);
        } else {
            echo json_encode([
                'status' => true,
                'message' => esc_html__($message, 'kc-lang')
            ]);
        }
    }

    public function edit()
    {
        global $wpdb;
        $is_permission = false;

        $permission_message = esc_html__('You don\'t have a permission to access', 'kc-lang');

        if (kcCheckPermission('doctor_edit') || kcCheckPermission('doctor_view') || kcCheckPermission('doctor_profile')) {
            $is_permission = true;
        }

        if (!$is_permission) {
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();
        $table_name = collect((new KCClinic)->get_all());

        
        try {

            if (!isset($request_data['id'])) {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }


            $id = $request_data['id'];
            $image_attachment_id = get_user_meta($id,'doctor_profile_image',true);
            $user_image_url = wp_get_attachment_url($image_attachment_id);
            $clinics = collect((new KCDoctorClinicMapping)->get_by(['doctor_id' => $id]))->pluck('clinic_id')->toArray();
            $clinics = $table_name->whereIn('id', $clinics);

            $user = get_userdata($id);
            unset($user->user_pass);

            $full_name = explode(' ', $user->display_name);

            $user_data = get_user_meta($id, 'basic_data', true);
            $first_name = get_user_meta($id, 'first_name', true);
            $last_name = get_user_meta($id, 'last_name', true);
            $description = get_user_meta($id, 'doctor_description', true);

            $data = (object)array_merge((array)$user->data, (array)json_decode($user_data));
            $data->first_name = $first_name;
            $data->username = $data->user_login;
            $data->description = !empty($description) ? $description : '';
            $data->last_name = $last_name;

            $telemed_charges = getDoctorTelemedServiceCharges($id);
            if(!empty($telemed_charges)) {
                $data->video_price = $telemed_charges;
            }

            foreach ($clinics as $d) {
                $list[] = [
                    'id' => $d->id,
                    'label' => $d->name,
                ];
            }
            $data->clinic_id = $list;
            if (isset($data->price_type)) {
                if ($data->price_type === "range") {
                    $price = explode("-", $data->price);
                    $data->minPrice = isset($price[0]) ? $price[0] : 0;
                    $data->maxPrice = isset($price[1]) ? $price[1] : 0;
                    $data->price = 0;
                }
            } else {
                $data->price_type = "range";
            }

            if($this->isTeleMedActive()) {

                $config_data = apply_filters('kct_get_zoom_configuration', [
                    'user_id' => $id,
                ]);

                if (isset($config_data['status']) && $config_data['status']) {
                    $data->enableTeleMed =$config_data['data']->enableTeleMed;
                    $data->api_key = $config_data['data']->api_key;
                    $data->api_secret = $config_data['data']->api_secret;
                    $data->zoom_id = $config_data['data']->zoom_id;
                }
            }
           
            $custom_filed = kcGetCustomFields('doctor_module', $id);
            $data->user_profile =$user_image_url;
            if ($data) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Doctor data',
                    'id' => $id,
                    'user_data' => $user_data,
                    'data' => $data,
                    'custom_filed'=>$custom_filed
                ]);

            } else {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }


        } catch (Exception $e) {

            $code = esc_html__($e->getCode(), 'kc-lang');
            $message = esc_html__($e->getMessage(), 'kc-lang');

            header("Status: $code $message");

            echo json_encode([
                'status' => false,
                'message' => $message
            ]);
        }
    }

    public function delete()
    {

        $permission_message = esc_html__('You don\'t have a permission to access', 'kc-lang');

        if (!kcCheckPermission('doctor_delete')) {
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();

        try {

            if (!isset($request_data['id'])) {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }

            $id = $request_data['id'];

            if (is_plugin_active($this->teleMedAddOnName())) {
                apply_filters('kct_delete_patient_meeting', ['doctor_id' => $id]);
            }

            // hook for doctor delete
            do_action( 'kc_doctor_delete', $id );

            (new KCClinicSchedule())->delete(['module_id' => $id, 'module_type' => 'doctor']);
            (new KCClinicSession())->delete(['doctor_id' => $id]);
            (new KCDoctorClinicMapping())->delete(['doctor_id' => $id]);
            (new KCAppointment())->delete(['doctor_id' => $id]);
            delete_user_meta($id, 'basic_data');
            delete_user_meta($id, 'first_name');
            delete_user_meta($id, 'last_name');
            $results = wp_delete_user($id);
            if ($results) {
                echo json_encode([
                    'status' => true,
                    'message' => esc_html__('Doctor has been deleted successfully', 'kc-lang'),
                ]);
            } else {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }


        } catch (Exception $e) {

            $code = esc_html__($e->getCode(), 'kc-lang');
            $message = esc_html__($e->getMessage(), 'kc-lang');

            header("Status: $code $message");

            echo json_encode([
                'status' => false,
                'message' => $message
            ]);
        }
    }

    public function updateOldAppointment($user_id, $new_time_slot)
    {
        $active_time_slot = kcGetDoctorTimeSlot($user_id);

        if ($active_time_slot !== $new_time_slot) {

            $appointments_table = $this->db->prefix . 'kc_' . 'appointments';

            $query = "SELECT * FROM $appointments_table WHERE `doctor_id` = '$user_id' AND `status` = 1 AND `appointment_start_date` >= '" . current_time('Y-m-d') . "'";

            $appointments = $this->db->get_results($query, OBJECT);

            $appointmentObj = new KCAppointment();

            if (count($appointments)) {
                foreach ($appointments as $appointment) {
                    $slots = kvGetTimeSlots([
                        'date' => $appointment->appointment_start_date,
                        'clinic_id' => $appointment->clinic_id,
                        'doctor_id' => $appointment->doctor_id
                    ], $new_time_slot);
                    if (count($slots)) {
                        foreach ($slots as $slotArray) {
                            foreach ($slotArray as $key => $slot) {

                                $slotStartTime = DateTime::createFromFormat('H:i', $slot['time']);
                                $slotEndTime = DateTime::createFromFormat('H:i', $slotArray[$key + 1]['time']);
                                $appointmentStartTime = DateTime::createFromFormat('H:i:s', $appointment->appointment_start_time);

                                if ($appointmentStartTime > $slotStartTime && $appointmentStartTime < $slotEndTime) {
                                    $diffOne = $appointmentStartTime->diff($slotStartTime);
                                    $diffTwo = $appointmentStartTime->diff($slotEndTime);

                                    if ($diffOne->i <= $diffTwo->i) {
                                        $appointment_start_time = $slotStartTime->format('H:i:s');
                                    } else {

                                        $appointment_start_time = $slotEndTime->format('H:i:s');
                                    }

                                    $appointment_end_time = date('H:i:s', strtotime("+" . $new_time_slot . " minutes", strtotime($appointment_start_time)));

                                    $appointmentObj->update([
                                        'appointment_start_time' => $appointment_start_time,
                                        'appointment_end_time' => $appointment_end_time
                                    ], ['id' => $appointment->id]);
                                }

                            }
                        }
                    }
                }
            }

        }
    }

    public function getDoctorWorkdays(){
        $request_data = $this->request->getInputs();
        $doctor_clinic_session = $this->db->prefix.'kc_clinic_sessions';
         $days = [1 => 'sun', 2 => 'mon', 3 =>'tue', 4 => 'wed', 5 => 'thu', 6 => 'fri', 7 => 'sat'];
        $results = [];
        $status = false;
        if(isset($request_data['clinic_id']) && $request_data['clinic_id'] != '' &&
            isset($request_data['doctor_id']) && $request_data['doctor_id'] != ''){
            $login_user = wp_get_current_user();
            
            if(!empty($login_user) && !empty($login_user->roles)) {
                if($this->getDoctorRole() === $login_user->roles[0]) {
                    $request_data['doctor_id'] = get_current_user_id() ;
                }
            } 
            //  $request_data['doctor_id'] = $this->getDoctorRole() === $login_user->roles[0] ? get_current_user_id() : $request_data['doctor_id'];
            $results = collect($this->db->get_results('select day from '.$doctor_clinic_session.' where doctor_id='.$request_data['doctor_id'].' and clinic_id='.$request_data['clinic_id']))->pluck('day')->toArray();
            if(count($results) > 0){
               $results =array_values(array_unique($results));
               $results = array_diff(array_values($days),$results);
               $results = array_map(function ($v) use ($days){
                 $v = array_search($v,$days);
                 return $v;
               },$results);
               $results = array_values($results);
               $status = true;
            }
            else{
                $results = array_map(function ($v) use ($days){
                    $v = array_search($v,$days);
                    return $v;
                  },$days);
                $results = array_values($results);
               $status = true;
            }
        }
        echo json_encode([
            'status' => $status,
             'data' => $results
        ]);
    }

    public function getDoctorWorkdayAndSession(){
        $request_data = $this->request->getInputs();
        $doctors_sessions = doctorWeeklyAvailability(['clinic_id'=>$request_data['clinic_id'],'doctor_id'=>$request_data['doctor_id']]);
        echo json_encode([
            'data' => $doctors_sessions,
            'status' => true
        ]);
    }
}
