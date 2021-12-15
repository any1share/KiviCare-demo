<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCAppointment;
use App\models\KCBillItem;
use App\models\KCClinicSession;
use App\models\KCClinic;
use App\models\KCPatientEncounter;
use App\models\KCAppointmentServiceMapping;

use Exception;

class KCBookAppointmentWidgetController extends KCBase {

	public $db;

	private $request;

	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->request = new KCRequest();

	}

	public function getDoctors () {
		$request_data = $this->request->getInputs();
		$doctor_role = $this->getDoctorRole();
		$table_name = $this->db->prefix . 'kc_' . 'doctor_clinic_mappings';
        $prefix = '';
        $postfix = '';
		if(isset($request_data['clinic_id']['id'])){
			$query = "SELECT `doctor_id` FROM {$table_name} WHERE `clinic_id` =".$request_data['clinic_id']['id'] ;
			$result = collect($this->db->get_results($query))->unique('doctor_id')->pluck('doctor_id');
			$users = get_users([ 'role' => $doctor_role ]);
			$users = collect($users)->whereIn('ID',$result)->values();
            $prefix_postfix = $this->db->get_var('select extra from '.$this->db->prefix.'kc_clinics where id='.$request_data['clinic_id']['id']);
		    if($prefix_postfix != null){
                $prefix_postfix = json_decode( $prefix_postfix);
                $prefix = isset($prefix_postfix->currency_prefix) ? $prefix_postfix->currency_prefix : '';
                $postfix = isset($prefix_postfix->currency_postfix) ? $prefix_postfix->currency_postfix : '';
            }
        }
		$results = [];

		// previous version dead code // 2.2.7

        // $clinic_id  = isset($request_data['clinic_id']['id']) ? $request_data['clinic_id']['id'] : 1 ;
        // $clinics_query = 'select * from '. $this->db->prefix  . 'kc_' .'clinics WHERE id='.$clinic_id ;
        // $clinic = $this->db->get_results($clinics_query);
        // $country = '' ;
        // if(count($clinic) > 0) {
        //     $country  = $clinic[0]->country;
        // }
        // $country_currency_list = kcCountryCurrencySymbolsList();
        // $country_currency = $country_currency_list[$country];

		if (count($users) > 0) {
			foreach ($users as $key => $user) {
				$image_attachment_id = get_user_meta($user->ID,'doctor_profile_image',true);
				$user_image_url = wp_get_attachment_url($image_attachment_id);
				$results[$key]['id'] = $user->ID;
				$results[$key]['display_name'] = $user->data->display_name;
				$user_data = get_user_meta($user->ID, 'basic_data', true);
				if ($user_data) {
					$user_data = json_decode($user_data);
					$results[$key]['description'] = get_user_meta($user->ID, 'doctor_description', true);
					$results[$key]['address'] = isset($user_data->address) ? $user_data->address : "";
					$results[$key]['city'] = isset($user_data->city) ? $user_data->city : "";
					$results[$key]['state'] = isset($user_data->state) ? $user_data->state : "";
					$results[$key]['country'] = isset($user_data->country) ? $user_data->country : "";
                    $results[$key]['currency'] = '';
					$results[$key]['postal_code'] = isset($user_data->postal_code) ? $user_data->postal_code : "";
					$results[$key]['timeSlot'] = isset($user_data->time_slot) ? $user_data->time_slot : "";
					$results[$key]['price'] = isset($user_data->price) ? $user_data->price : "";
					$results[$key]['gender'] = isset($user_data->gender) ? $user_data->gender : "";
					$results[$key]['qualifications'] = isset($user_data->qualifications) ? $user_data->qualifications : "";
					$results[$key]['specialties'] = isset($user_data->specialties) ? $user_data->specialties : [];
					$results[$key]['enableTeleMed'] = false;
					$results[$key]['custom_fields'] = kcGetCustomFields('appointment_module', $user->ID);
					$results[$key]['user_profile'] =$user_image_url;
					
					if (is_plugin_active($this->teleMedAddOnName())) { 

						$zoom_config_data = get_user_meta($user->ID, 'zoom_config_data', true);

						if ($zoom_config_data) {

							$zoom_config_data = json_decode($zoom_config_data);

							$enableTeleMed = false;

							if (isset($zoom_config_data->enableTeleMed) && (bool)$zoom_config_data->enableTeleMed) {
								if ($zoom_config_data->api_key !== "" && $zoom_config_data->api_secret !== "") {
									$results[$key]['enableTeleMed'] = true;
								}
							}
							
						}
					}
				}
			}
			echo json_encode([
				'status' => true,
				'message' => esc_html__('Doctor details', 'kc-lang'),
				'data' => $results,
                'prefix' => $prefix,
                'postfix' =>$postfix ,
			]);
		}else{
			echo json_encode([
				'status' => false,
				'message' => esc_html__('Doctor details', 'kc-lang'),
				'data' => []
			]);
		}

		

	}
	public function getClinic () {
        $response = apply_filters('kcpro_get_clinic_data',[]);
        echo json_encode($response);
	}

	public function getTimeSlots() {

		$formData = $this->request->getInputs();;

		$clinic_id = kcGetDefaultClinicId();

		$timeSlots = kvGetTimeSlots([
			'date' => $formData['date'],
			'doctor_id' => $formData['doctor_id'],
			'clinic_id' => $formData['clinic_id']
		], "", true);

		if (count($timeSlots)) {
			$status = true;
			$message = esc_html__('Time slots', 'kc-lang' );
		} else {
			$status = false;
			$message = esc_html__('Doctor is not available for this date', 'kc-lang' );
		}

		echo json_encode( [
			'status'      => $status,
			'message'     => $message,
			'data'     => $timeSlots,
		] );

	}

	public function saveAppointment() {
		global $wpdb;

		$formData = $this->request->getInputs();
        $post_table_name = $wpdb->prefix . 'posts';
		$clinic_session_table = $wpdb->prefix . 'kc_' . 'clinic_sessions';

		$active_domain =$this->getAllActivePlugin();
		
		try {

			if(!is_user_logged_in()) {
				throw new Exception( esc_html__('Sign in to book appointment', 'kc-lang'), 401 );
			}

            $clinic_id = isset($formData['clinic_id']['id']) ? $formData['clinic_id']['id']: kcGetDefaultClinicId();
			$userObj = wp_get_current_user();
			if($active_domain === $this->kiviCareProOnName() &&  $formData['status'] == '1'){
				$args['post_name'] = strtolower(KIVI_CARE_PREFIX.'default_event_template');
				$args['post_type'] = strtolower(KIVI_CARE_PREFIX.'gcal_tmp') ;
			
				$query = "SELECT * FROM $post_table_name WHERE `post_name` = '" . $args['post_name'] . "' AND `post_type` = '".$args['post_type']."' AND post_status = 'publish' ";
				$check_exist_post = $wpdb->get_results($query, ARRAY_A);

				$clinicData = (new KCClinic())->get_by(['id'=>$formData['visit_type'][0]['clinic_id']] ,'=',true);
				$clinicAddress = $clinicData->address.','.$clinicData->city.','.$clinicData->country;

				$appointment_day = strtolower(date('l', strtotime($formData['appointment_start_date']))) ;
				$day_short = substr($appointment_day, 0, 3);
		
				$query = "SELECT * FROM {$clinic_session_table}  
				WHERE `doctor_id` = ".$formData['doctor_id']['id']." AND `clinic_id` = ".$formData['visit_type'][0]['clinic_id']."  
				AND ( `day` = '{$day_short}' OR `day` = '{$appointment_day}') ";

				$clinic_session = collect($wpdb->get_results($query, OBJECT));
				$time_slot             = isset($clinic_session[0]->time_slot) ? $clinic_session[0]->time_slot : 15;

				$calender_title = $check_exist_post[0]['post_title'];
				$calender_content = $check_exist_post[0]['post_content'];
				
				$key  =  ['{{service_name}}','{{clinic_name}}'];
				foreach($key as $item => $value ){
					switch ($value) {
						case '{{service_name}}':
							$calender_title = str_replace($value, $formData['visit_type'][0]['name'], $calender_title);
							break;
						case '{{clinic_name}}':
							$calender_content = str_replace($value, $clinicData->name, $calender_content);
							break;
					}
				}
				$end_time             = strtotime( "+" . $time_slot . " minutes", strtotime( $formData['appointment_start_time'] ) );
				$appointment_end_time = date( 'H:i:s', $end_time );
				$formData['calender_title'] = $calender_title;
				$formData['calender_content'] = $calender_content;
				$formData['clinic_address'] =$clinicAddress;
				$formData['start'] = date("c", strtotime( $formData['appointment_start_date'].$formData['appointment_start_time']));
				$formData['end'] = date("c", strtotime( $formData['appointment_start_date'].$appointment_end_time));
			}

			if (!in_array($this->getPatientRole(),$userObj->roles)) {
				throw new Exception( esc_html__('User must be patient to book appointment', 'kc-lang'), 401 );
			}

            $clinic_session_table = $wpdb->prefix . 'kc_' . 'clinic_sessions';
            $appointment_day = strtolower(date('l', strtotime($formData['appointment_start_time']))) ;
            $day_short = substr($appointment_day, 0, 3);

            $query = "SELECT * FROM {$clinic_session_table}  WHERE `doctor_id` = ".$formData['doctor_id']['id']." AND `clinic_id` = ".$clinic_id."  AND ( `day` = '{$day_short}' OR `day` = '{$appointment_day}') ";
            $clinic_session = collect($wpdb->get_results($query, OBJECT));
            $time_slot             = isset($clinic_session[0]->time_slot) ? $clinic_session[0]->time_slot : 15;
			$end_time             = strtotime( "+" . $time_slot . " minutes", strtotime( $formData['appointment_start_time'] ) );
			$appointment_end_time = date( 'H:i:s', $end_time );
			$appointment_date     = date( 'Y-m-d', strtotime( $formData['appointment_start_date'] ) );
            $patient_id = get_current_user_id();

			// appointment shortcode condition
            $tempAppointmentData = [
                'appointment_start_date' => $appointment_date,
                'appointment_start_time' => date('H:i:s', strtotime($formData['appointment_start_time'])),
                'appointment_end_date' => $appointment_date,
                'appointment_end_time' => $appointment_end_time,
                'visit_type' => $formData['visit_type'],
                'clinic_id' => $clinic_id,
                'doctor_id' => $formData['doctor_id']['id'],
                'patient_id' => $patient_id,
                'description' => $formData['description'],
                'status' => $formData['status'],
                'created_at' => current_time('Y-m-d H:i:s')
            ];

            if(isset($formData['file']) && is_array($formData['file']) && count($formData['file']) > 0){
                $appointment_table_name = $this->db->prefix . 'kc_' . 'appointments';
                kcUpdateFields($appointment_table_name,[ 'appointment_report' => 'longtext NULL']);
                $tempAppointmentData['appointment_report'] = json_encode($formData['file']);
            }

			$patient_appointment_id = (new KCAppointment())->insert($tempAppointmentData);

            if($active_domain !== $this->kiviCareProOnName() &&  !$this->isTeleMedActive()){
                foreach ($formData['visit_type'] as $key => $value) {
                    $service = strtolower($value['name']);
                    if ($service != 'telemed') {
                        $patient_email_status = kcAppointmentPatientEmail($patient_appointment_id,kcServiceListFromRequestData($patient_appointment_id));
                    }
                }
                $doctor_email_status = kcAppointmentDoctorEmail($patient_appointment_id,kcServiceListFromRequestData($patient_appointment_id));
            }

            $notification = '';
            if (gettype($formData['visit_type']) === 'array') {
                foreach ($formData['visit_type'] as $key => $value) {
                    $service = strtolower($value['name']);

                    if ($service == 'telemed') {

                        if ($this->isTeleMedActive()) {


                            $formData['appointment_id'] = $patient_appointment_id;
							$formData['time_slot'] = $time_slot;
							
							$res_data = apply_filters('kct_create_appointment_meeting', $formData);
                            if(empty($res_data['status'])) {
                                ( new KCAppointmentServiceMapping() )->delete( [ 'appointment_id' =>  $patient_appointment_id] );
                                ( new KCAppointment() )->delete( [ 'id' =>  $patient_appointment_id] );
                                echo json_encode([
                                    'status'  => false,
                                    'message' => esc_html__('Zoom meeting not generated successfully.', 'kc-lang'),
                                ]); wp_die();
                            }
							// send zoom link
                            if(!empty($res_data) && $res_data['status'] == true) {
                                if(get_option( KIVI_CARE_PREFIX . 'woocommerce_payment') == false || get_option( KIVI_CARE_PREFIX . 'woocommerce_payment') != 'on') {
                                    $telemed_link_send = apply_filters('kct_send_zoom_link', ['appointment_id' => $patient_appointment_id ] );
                                }
                            }
                        }
                    }

                    if($patient_appointment_id) {
                        (new KCAppointmentServiceMapping())->insert([
                            'appointment_id' => $patient_appointment_id,
                            'service_id' => $value['service_id'],
                            'created_at' => current_time('Y-m-d H:i:s'),
							'status' => 1
                        ]);
                    }
                }
                if($active_domain === $this->kiviCareProOnName() || $this->isTeleMedActive()){
                    $notification = kcAppointmentSendMailBasedOnWoo($patient_appointment_id,$formData);
                }
            }

            if($patient_appointment_id) {
                $formData['id'] = $patient_appointment_id;
                if (isset($formData['custom_fields']) && $formData['custom_fields'] !== []) {
                    kvSaveCustomFields('appointment_module',$patient_appointment_id, $formData['custom_fields']);
                }
                $message = esc_html__('Appointment has been booked successfully', 'kc-lang');
                $status  = true ;
            } else {
                $message = esc_html__('Appointment has not been booked', 'kc-lang');
                $status  = false ;
            }

			if ( $formData['status'] === '2' || $formData['status'] === '4' ) {
				KCPatientEncounter::createEncounter( $patient_appointment_id );
                KCBillItem::createAppointmentBillItem($patient_appointment_id );
			}
			
			// woocommerce payment telemed addon
            $woocommerce_response  = kcWoocommerceRedirect($patient_appointment_id, $formData);
            if(!empty($patient_appointment_id) && $patient_appointment_id !== 0) {
                // hook for appointment booked
                do_action( 'kc_appointment_book', $patient_appointment_id );
            }
			if(isset($woocommerce_response['status']) && $woocommerce_response['status']) {
				if(!empty($woocommerce_response['woocommerce_cart_data'])) {
					echo json_encode($woocommerce_response); wp_die();
				}
			}

			echo json_encode([
				'status'      => (bool) $status,
				'message'     => $message,
				'data' 		  => $formData,
                'notification' =>$notification,
			] );

			wp_die();

		} catch ( Exception $e ) {

			$code    = esc_html__($e->getCode(), 'kc-lang');
			$message = esc_html__($e->getMessage(), 'kc-lang');

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => $message
			] );
		}

	}

}
