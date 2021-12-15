<?php

namespace App\Controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCAppointment;
use App\models\KCClinicSession;
use App\models\KCPatientEncounter;
use App\models\KCAppointmentServiceMapping;
use App\models\KCBillItem;
use App\models\KCClinic;
use  App\models\KCReceptionistClinicMapping;
use DateTime;
use Exception;

class KCAppointmentController extends KCBase {

	public $db;

	private $request;

	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->request = new KCRequest();

	}

	public function index() {

		if ( ! kcCheckPermission( 'appointment_list' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();
		$users_table        = $this->db->prefix . 'users';
		$appointments_table = $this->db->prefix . 'kc_' . 'appointments';
		$clinics_table      = $this->db->prefix . 'kc_' . 'clinics';
		$static_data        = $this->db->prefix . 'kc_' . 'static_data';
		$start_date         = $request_data['start_date'];
		$end_date           = $request_data['end_date'];
		$appointments_service_table = $this->db->prefix . 'kc_' . 'appointment_service_mapping';
		$service_table = $this->db->prefix . 'kc_' . 'services';
		$query = "
			SELECT {$appointments_table}.*,
		       doctors.display_name  AS doctor_name,
		       patients.display_name AS patient_name,
		       static_data.label AS type_label,
		       {$clinics_table}.name AS clinic_name
			FROM  {$appointments_table}
		       LEFT JOIN {$users_table} doctors
		              ON {$appointments_table}.doctor_id = doctors.id
		       LEFT JOIN {$users_table} patients
		              ON {$appointments_table}.patient_id = patients.id
		       LEFT JOIN {$clinics_table}
		              ON {$appointments_table}.clinic_id = {$clinics_table}.id
		       LEFT JOIN {$static_data} static_data
		              ON {$appointments_table}.visit_type = static_data.value
            WHERE {$appointments_table}.appointment_start_date > '{$start_date}' AND {$appointments_table}.appointment_start_date < '{$end_date}' ";

		$user = wp_get_current_user();

		if ( in_array( $this->getDoctorRole(), $user->roles ) ) {
			$query .= " AND {$appointments_table}.doctor_id = " . $user->ID;
		} elseif ( in_array( $this->getPatientRole(), $user->roles ) ) {
			$query .= " AND {$appointments_table}.patient_id = " . $user->ID;
		}

		$appointments     = $this->db->get_results( $query, OBJECT );
		$new_appointments = [];

		if ( count( $appointments ) ) {
			foreach ( $appointments as $key => $appointment ) {


				$zoom_config_data = get_user_meta($appointment->doctor_id, 'zoom_config_data', true);
				$get_service =  "SELECT {$appointments_table}.id,{$service_table}.name AS service_name,{$service_table}.id AS service_id FROM {$appointments_table}
				LEFT JOIN {$appointments_service_table} ON {$appointments_table}.id = {$appointments_service_table}.appointment_id JOIN {$service_table} 
				ON {$appointments_service_table}.service_id = {$service_table}.id WHERE 0 = 0";
				$new_appointments[ $key ]['id']                     = $appointment->id;
				$new_appointments[ $key ]['date']                   = $appointment->appointment_start_date . ' ' . $appointment->appointment_start_time;
				$new_appointments[ $key ]['endDate']                = $appointment->appointment_end_date . ' ' . $appointment->appointment_end_time;
				$new_appointments[ $key ]['appointment_start_date'] = $appointment->appointment_start_date;
				$new_appointments[ $key ]['appointment_start_time'] = date( 'h:i A', strtotime( $appointment->appointment_start_time ) );
				$new_appointments[ $key ]['visit_type']             = $appointment->visit_type;
				$new_appointments[ $key ]['description']            = $appointment->description;
				$new_appointments[ $key ]['title']                  = ($this->getLoginUserRole() === $this->getPatientRole()) ? $appointment->doctor_name : $appointment->patient_name;
				$new_appointments[ $key ]['clinic_id']              = [
					'id'    => $appointment->clinic_id,
					'label' => $appointment->clinic_name
				];
				$new_appointments[ $key ]['doctor_id']              = [
					'id'    => $appointment->doctor_id,
					'label' => $appointment->doctor_name
				];
				$new_appointments[ $key ]['patient_id']             = [
					'id'   => $appointment->patient_id,
					'label' => $appointment->patient_name
				];
				$new_appointments[ $key ]['clinic_name']            = $appointment->clinic_name;
				$new_appointments[ $key ]['doctor_name']            = $appointment->doctor_name;
				$new_appointments[ $key ]['status']                 = $appointment->status;
				$services = collect( $this->db->get_results( $get_service, OBJECT ) )->where('id', $appointment->id);
				$service_array=[];
				foreach ($services as $service) {
					$service_array[] =  $service->service_name;
				}
				$str = implode (", ", $service_array);
				$new_appointments[ $key ]['all_services']= $str;
				$new_appointments[ $key ]['color'] = '#3490dc';
				if ( $appointment->status === '0' ) {
					$new_appointments[ $key ]['color'] = '#f5365c';
				} elseif ($appointment->status === '3') {
					$new_appointments[ $key ]['color'] = '#2dce89';
				}
			}
		}

		// Remove duplicate array...
		$tempArr          = array_unique( array_column( $new_appointments, 'id' ) );
		$new_appointments = array_values( array_intersect_key( $new_appointments, $tempArr ) );
		if ( ! count( $appointments ) ) {

			echo json_encode( [
				'status'  => false,
				'message' => esc_html__('No appointments found', 'kc-lang'),
				'data'    => []
			] );
			wp_die();
		}

		echo json_encode( [
			'status'  => true,
			'message' => esc_html__('Appointment list','kc-lang'),
			'data'    => $new_appointments
		] );
	}

	public function save() {

		global $wpdb;
		$table_name =  $wpdb->prefix . 'users';
		$active_domain =$this->getAllActivePlugin();
		$process_status = [];

		if ( ! kcCheckPermission( 'appointment_add' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();
		$rules = [
			'appointment_start_date' => 'required',
			'appointment_start_time' => 'required',
			'clinic_id'              => 'required',
			'doctor_id'              => 'required',
			'patient_id'             => 'required',
			'status'                 => 'required',

		];

		$message = [
			'status'     => esc_html__('Status is required', 'kc-lang'),
			'patient_id' => esc_html__('Patient is required','kc-lang'),
			'clinic_id'  => esc_html__('Clinic is required','kc-lang'),
			'doctor_id'  => esc_html__('Doctor is required','kc-lang'),
		];

		$errors = kcValidateRequest( $rules, $request_data, $message );

		if ( count( $errors ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__($errors[0], 'kc-lang')
			] );
			die;
		}

        $clinic_session_table = $wpdb->prefix . 'kc_' . 'clinic_sessions';
        $appointment_day = strtolower(date('l', strtotime($request_data['appointment_start_date']))) ;
        $day_short = substr($appointment_day, 0, 3);
        $query = "SELECT * FROM {$clinic_session_table}  WHERE `doctor_id` = ".$request_data['doctor_id']['id']." AND `clinic_id` = ".$request_data['clinic_id']['id']."  AND ( `day` = '{$day_short}' OR `day` = '{$appointment_day}') ";
        $clinic_session = collect($wpdb->get_results($query, OBJECT));
		$time_slot             = isset($clinic_session[0]->time_slot) ? $clinic_session[0]->time_slot : 15;
		$end_time             = strtotime( "+" . $time_slot . " minutes", strtotime( $request_data['appointment_start_time'] ) );
		$appointment_end_time = date( 'H:i:s', $end_time );
		$appointment_date     = date( 'Y-m-d', strtotime( $request_data['appointment_start_date'] ) );
		$is_woocommerce_payment_enable = false ;

		$temp = [
			'appointment_start_date' => $appointment_date,
			'appointment_start_time' => date( 'H:i:s', strtotime( $request_data['appointment_start_time'] ) ),
			'appointment_end_date'   => $appointment_date,
			'appointment_end_time'   => $appointment_end_time,
			'clinic_id'              => $request_data['clinic_id']['id'],
			'doctor_id'              => $request_data['doctor_id']['id'],
			'patient_id'             => $request_data['patient_id']['id'],
			'description'            => $request_data['description'],
			'status'                 => $request_data['status'],
		];

        if(isset($request_data['file']) && is_array($request_data['file']) && count($request_data['file']) > 0){
            $appointment_table_name = $this->db->prefix . 'kc_' . 'appointments';
            kcUpdateFields($appointment_table_name,[ 'appointment_report' => 'longtext NULL']);
            $temp['appointment_report'] = json_encode($request_data['file']);
        }

		$appointment = new KCAppointment();
        //$temp['visit_type'] = str_replace(" ","_", $request_data['visit_type']['id']);
		if ( isset( $request_data['id'] ) && $request_data['id'] !== "" ) {
			$appointment_id = $request_data['id'];
			$appointment->update( $temp, array( 'id' => $request_data['id'] ) );
			( new KCAppointmentServiceMapping() )->delete( [ 'appointment_id' => $appointment_id ] );
			(new KCPatientEncounter())->update([
				'encounter_date' => $appointment_date,
				'patient_id'             => $request_data['patient_id']['id'],
				'doctor_id'              => $request_data['doctor_id']['id'],
				'clinic_id'              => $request_data['clinic_id']['id'],
				'description'            => $request_data['description'],
			], ['appointment_id' => $appointment_id]);
            if (isset($request_data['custom_fields_data']) && $request_data['custom_fields_data'] !== []) {
                kvSaveCustomFields('appointment_module',$appointment_id, $request_data['custom_fields_data']);
            }
			$message = esc_html__('Appointment has been updated successfully', 'kc-lang');
			$msg_reminder_table = $wpdb->prefix . "kc_appointment_reminder_mapping";
			$temp = [
				'sms_status' => 0,
				'email_status' => 0,
				'whatsapp_status'=> 0
			];
			$wpdb->update($msg_reminder_table, $temp, ['id' => $request_data['id']]);
            if(!empty($appointment_id) && $appointment_id !== 0) {
                // hook for appointment update
                do_action( 'kc_appointment_update', $appointment_id );
            }

		} else {

			$temp['created_at'] = current_time('Y-m-d H:i:s');
			$appointment_id = $appointment->insert( $temp );

			// if appointment is not successfully created. (WP Error handle) 
			if(is_wp_error($appointment_id) || $appointment_id == false || $appointment_id == 0 ) {
				$message = esc_html__('Appointment is not successfully booked.','kc-lang');
				echo json_encode([
					'status'  => false,
					'message' => esc_html__($message, 'kc-lang'),
				]); 
				wp_die();
			}
            $message = esc_html__('Appointment is Successfully booked.','kc-lang');
			// ********* Dead code from previous version. ********
			//$query = "SELECT * FROM {$table_name} WHERE `ID` = '{$request_data["patient_id"]["id"]}' ";
			//$doctor_query = "SELECT * FROM {$table_name} WHERE `ID` = '{$request_data["doctor_id"]["id"]}'";
			// $patient_data = $wpdb->get_results($query, OBJECT);
			// $doctor_data = $wpdb->get_results($doctor_query, OBJECT);

			// get appointment patient detail.
			if(!empty($request_data) && (isset($request_data['patient_id']['id']))) {
				$patient_data = get_user_by('ID', $request_data['patient_id']['id']); 
				if(!empty($patient_data)) {
					$patient_data = $patient_data->data; 
				} else {
					$message = 'Patient not found.';
					echo json_encode([
						'status'  => false,
						'message' => esc_html__($message, 'kc-lang'),
					]); 
					wp_die();
				}
			}

			// get appointment doctor detail.
			if(!empty($request_data) && (isset($request_data['doctor_id']['id']))) {
				$doctor_data = get_user_by('ID', $request_data['doctor_id']['id']);
				if(!empty($doctor_data)) {
					$doctor_data = $doctor_data->data;  
				} else {
					$message = 'Doctor not found.';
					echo json_encode([
						'status'  => false,
						'message' => esc_html__($message, 'kc-lang'),
					]); 
					wp_die();
				}
			}

			$doctor_name = $doctor_data->display_name;
			// email send
			if($request_data['status'] == 1) {
				// Telemed appointment booking no need to send normal appointment booking email 
				if($active_domain !== $this->kiviCareProOnName() &&  !$this->isTeleMedActive()){
					foreach ($request_data['visit_type'] as $key => $value) {
						$service = strtolower($value['name']);
						if ($service != 'telemed') {
                            $patient_email_status = kcAppointmentPatientEmail($appointment_id,kcServiceListFromRequestData($request_data));
						}
					}
                    $doctor_email_status = kcAppointmentDoctorEmail($appointment_id,kcServiceListFromRequestData($request_data));
				}
			}
			if (isset($request_data['custom_fields']) && $request_data['custom_fields'] !== []) {
                kvSaveCustomFields('appointment_module',$appointment_id, $request_data['custom_fields']);
            }
		}

		if ( $request_data['status'] == '2' || $request_data['status'] == '4' ) {
			KCPatientEncounter::createEncounter($appointment_id);
			KCBillItem::createAppointmentBillItem($appointment_id);
		}
        $notification = '';
		if (gettype($request_data['visit_type']) === 'array') {

			foreach ($request_data['visit_type'] as $key => $value) {

			    $service = strtolower($value['name']);

				// generate zoom link request (Telemed AddOn filter) 
			    if ($service == 'telemed') {

                    if ($this->isTeleMedActive()) {

                        $request_data['appointment_id'] = $appointment_id;
                        $request_data['time_slot'] = $time_slot;

                        $res_data = apply_filters('kct_create_appointment_meeting', $request_data);
						// if zoom meeting is not created successfully
						if(empty($res_data['status'])) {
                            ( new KCAppointmentServiceMapping() )->delete( [ 'appointment_id' =>  $appointment_id] );
                            ( new KCAppointment() )->delete( [ 'id' =>  $appointment_id] );
							echo json_encode([
								'status'  => false,
								'message' => esc_html__('Zoom meeting not generated successfully.', 'kc-lang'),
							]); wp_die();
						}
						$process_status['telemed']['status'] = false ;
						$process_status['telemed']['message'] = $res_data['message'];

						// Handle Invalid zoom link access token
						if(!empty($res_data) && $res_data['status'] == true) {

							$process_status['telemed']['status'] = true ;
							$process_status['telemed']['message'] = $res_data['message'];
							$process_status['telemed']['join_url'] = $res_data['data']['join_url'];

							if(get_option( KIVI_CARE_PREFIX . 'woocommerce_payment') == false || get_option( KIVI_CARE_PREFIX . 'woocommerce_payment') != 'on') {
								$telemed_link_send = apply_filters('kct_send_zoom_link', ['appointment_id' => $appointment_id ] );
							}else{
                                //send email if woocommerce enable and status of order is completed
                                if(isset( $request_data['id'] ) && $request_data['id'] != ""
                                    && ($active_domain === $this->kiviCareProOnName() || $this->isTeleMedActive())){
                                    if(kcAppointmentWoocommerceOrderStatus($appointment_id) || $this->getLoginUserRole() !== $this->getPatientRole()){
                                        $telemed_link_send = apply_filters('kct_send_zoom_link', ['appointment_id' => $appointment_id ] );
                                    }
                                }
                            }

							// Email zoom link send status 
							$process_status['telemed']['link_send_status'] = $telemed_link_send ;

						} 

                    }
                }
				if(!empty($appointment_id) && $appointment_id !== 0){
					(new KCAppointmentServiceMapping())->insert([
						'appointment_id' => $appointment_id,
						'service_id' => $value['service_id'],
						'created_at' => current_time('Y-m-d H:i:s'),
						'status'=> 1
					]);
				}
			}
			
            if($active_domain === $this->kiviCareProOnName() || $this->isTeleMedActive()){
                $notification = kcAppointmentSendMailBasedOnWoo($appointment_id,$request_data);
            }else{
                $doctor_email_status = kcAppointmentDoctorEmail($appointment_id,kcServiceListFromRequestData($request_data));
                $patient_email_status = kcAppointmentPatientEmail($appointment_id,kcServiceListFromRequestData($request_data));
                $notification = [
                    'doctor_email_status' => $doctor_email_status,
                    'patient_email_status' => $patient_email_status
                ];
            }
		}

        if(!empty($appointment_id) && $appointment_id !== 0) {
            // hook for appointment booked
            do_action( 'kc_appointment_book', $appointment_id );
        }

        // woocommerce payment or  telemed cart page addon
		if(empty($request_data['id'])) {
			$woocommerce_response  = kcWoocommerceRedirect($appointment_id, $request_data);
			if(isset($woocommerce_response['status']) && $woocommerce_response['status']) {
				if(!empty($woocommerce_response['woocommerce_cart_data'])) {
					echo json_encode($woocommerce_response); wp_die();
				}
			}
		}

		if(!empty($appointment_id) && $appointment_id !== 0) {

			echo json_encode([
				'status'  => true,
				'message' => esc_html__($message, 'kc-lang'),
                'notification' =>$notification,
			]); 
		} else {
			$message = 'Appointment is not successfully booked.';
			echo json_encode([
				'status'  => false,
				'message' => esc_html__($message, 'kc-lang'),
                'notification' =>$notification,
			]); 
		}
        wp_die();
		
	}

	public function delete() {

		if ( ! kcCheckPermission( 'appointment_delete' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access','kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();
		$appointment_service_mapping_table = $this->db->prefix . 'kc_' . 'appointment_service_mapping';
		$active_domain =$this->getAllActivePlugin();

		try {

			if ( ! isset( $request_data['id'] ) ) {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}

			if (is_plugin_active($this->teleMedAddOnName())) {
				apply_filters('kct_delete_appointment_meeting', $request_data);
			}

			$id = $request_data['id'];

			if(isset($request_data['id'])) {
				$count_query      = "SELECT count(*) AS count from {$appointment_service_mapping_table} WHERE appointment_id = {$request_data['id']} ";
				$appointment_count = $this->db->get_results( $count_query, OBJECT );
				if(isset($appointment_count[0]->count) && $appointment_count[0]->count > 0 && $appointment_count[0]->count!= null  ){


                    ( new KCAppointmentServiceMapping() )->delete( [ 'appointment_id' => $id ] );
					
					//APPIONTMENT CANCEL EMAIL
					$appointmentData   = ( new KCAppointment() )->get_by(['id' => $id ], '=', true);
					
					$emailStatus = kcAppointmentCancelMail($appointmentData);
				}
				if($active_domain === $this->kiviCareProOnName()){
					apply_filters('kcpro_remove_appointment_event', ['appoinment_id'=>$id]);
				}

                // hook for appointment before cancelled
                do_action( 'kc_appointment_cancel', $id );
				$results = ( new KCAppointment() )->delete( [ 'id' => $id ] );
			}
			if ( $results ) {
                // hook for appointment after cancelled
				echo json_encode( [
					'status'  => true,
					'message' => esc_html__('Appointment has been deleted successfully', 'kc-lang'),
				] );
			} else {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}

		} catch ( Exception $e ) {

			$code    = $e->getCode();
			$message = $e->getMessage();

			$code = esc_html__($code, 'kc-lang');
			$message = esc_html__($message, 'kc-lang');

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => esc_html__($e->getMessage(), 'kc-lang')
			] );
		}
	}

	public function updateStatus() {

		if ( ! kcCheckPermission( 'appointment_edit' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}


		$request_data = $this->request->getInputs();

		$rules  = [
			'appointment_id'     => 'required',
			'appointment_status' => 'required',

		];
		$errors = kcValidateRequest( $rules, $request_data );

		if ( count( $errors ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__($errors[0], 'kc-lang')
			] );
			die;
		}


		try {

            do_action( 'kc_appointment_status_update', $request_data['appointment_id'] , $request_data['appointment_status'] );

			( new KCAppointment() )->update( [ 'status' => $request_data['appointment_status'] ], array( 'id' => $request_data['appointment_id'] ) );

			if ( (string) $request_data['appointment_status'] === '2' || (string)$request_data['appointment_status'] === '4' ) {
				KCPatientEncounter::createEncounter( $request_data['appointment_id'] );
				KCBillItem::createAppointmentBillItem($request_data['appointment_id']);
			}
			if ((string)$request_data['appointment_status'] === '3' || (string)$request_data['appointment_status'] === '0' ) {
				KCPatientEncounter::closeEncounter( $request_data['appointment_id'] );
			}

			echo json_encode( [
				'status'  => true,
				'message' => esc_html__('Appointment status has been updated successfully', 'kc-lang')
			] );


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

	public function getAppointmentSlots() {

		$request_data = $this->request->getInputs();

		if($this->getLoginUserRole() == 'kiviCare_doctor') {
			$request_data['doctor_id'] = get_current_user_id();
		}

		$rules = [
			'date'      => 'required',
			'clinic_id' => 'required',
			'doctor_id' => 'required',

		];

		$message = [
			'clinic_id' => esc_html__('Clinic is required', 'kc-lang'),
			'doctor_id' => esc_html__('Doctor is required', 'kc-lang'),
		];

		$errors = kcValidateRequest( $rules, $request_data, $message );

		if ( count( $errors ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__($errors[0], 'kc-lang')
			] );
			die;
		}

		try {

			$user_id = get_current_user_id();

			if(is_plugin_active($this->kiviCareProOnName())){
				if($this->getLoginUserRole() == 'kiviCare_receptionist') {
					$receptionis_id = get_current_user_id();
					$clinic_id =  (new KCReceptionistClinicMapping())->get_by([ 'receptionist_id' => $user_id]);
					$request_data['clinic_id'] = $clinic_id[0]->clinic_id;
				}
				if($this->getLoginUserRole() == 'kiviCare_clinic_admin'){
					$clinic_id = (new KCClinic())->get_by([ 'clinic_admin_id' => $user_id]);
					$request_data['clinic_id'] = $clinic_id[0]->id;
				}
			}

			$slots = kvGetTimeSlots( $request_data );
			echo json_encode( [
				'status'  => true,
				'message' => esc_html__('Appointment slots', 'kc-lang'),
				'data'    => $slots
			] );
			
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

	public function getAppointmentQueue() {
		global $wpdb;
		$request_data = $this->request->getInputs();
		$active_domain =$this->getAllActivePlugin();
		$appointments_table = $this->db->prefix . 'kc_' . 'appointments';
		$appointments_service_table = $this->db->prefix . 'kc_' . 'appointment_service_mapping';
		$service_table = $this->db->prefix . 'kc_' . 'services';

		$filterData = isset( $request_data['filterData'] ) ? $request_data['filterData'] : [];
		$users_table   = $this->db->prefix . 'users';
		$clinics_table = $this->db->prefix . 'kc_' . 'clinics';
		$static_data   = $this->db->prefix . 'kc_' . 'static_data';

        $data_filter = "";
		if (isset( $request_data['start']) && isset( $request_data['end'])) {
            $start_date = ( new DateTime( $request_data['start'] ) )->format( 'Y-m-d' );
            $end_date = ( new DateTime( $request_data['end'] ) )->format( 'Y-m-d' );
    		$data_filter  = " AND {$appointments_table}.appointment_start_date BETWEEN '{$start_date}' AND '{$end_date}' "  ;
        } elseif (isset($request_data['filterData']['date']) && $request_data['filterData']['date']!= null && (isset($request_data['filterData']['status'])) && $request_data['filterData']['status'] !== 'all') {
			if(isset($request_data['filterData']['status']) && $request_data['filterData']['status'] == 1){
				$date = ( new DateTime( $request_data['filterData']['date'] ) )->format( 'Y-m-d' );
				$data_filter  = " AND {$appointments_table}.appointment_start_date >= '{$date}' " ;
			 }
        }
        elseif((isset($request_data['filterData']['status'])) && $request_data['filterData']['status'] == 'past'){
            $data_filter  = " AND {$appointments_table}.appointment_start_date < CURDATE() " ;
        } elseif (isset($request_data['filterData']['date']) && $request_data['filterData']['date']!= null) {
            $date = ( new DateTime( $request_data['filterData']['date'] ) )->format( 'Y-m-d' );
            $data_filter  = " AND {$appointments_table}.appointment_start_date = '{$date}' " ;
        } else {
            $data_filter = '';
        }

		$query = " SELECT {$appointments_table}.*,
		       doctors.display_name  AS doctor_name,
		       patients.display_name AS patient_name,
		       static_data.label AS type_label,
		       {$clinics_table}.name AS clinic_name
			FROM  {$appointments_table}
		       LEFT JOIN {$users_table} doctors
		              ON {$appointments_table}.doctor_id = doctors.id
		       LEFT JOIN {$users_table} patients
		              ON {$appointments_table}.patient_id = patients.id
		       LEFT JOIN {$clinics_table}
		              ON {$appointments_table}.clinic_id = {$clinics_table}.id
		       LEFT JOIN {$static_data} static_data
		              ON {$appointments_table}.visit_type = static_data.value
			WHERE 0 = 0 " . $data_filter;

		$user = wp_get_current_user();
		
		if(!current_user_can('administrator')) {
			if(in_array( $this->getClinicAdminRole(), $user->roles )){
				$user_id = get_current_user_id();
				$clinic_id =  (new KCClinic())->get_by([ 'clinic_admin_id' => $user_id]);
				if(isset($clinic_id[0]->id)) {
					$clinic = $clinic_id[0]->id ;
					$query .= " AND {$appointments_table}.clinic_id = " . $clinic;
				}
			}elseif(in_array( $this->getReceptionistRole(), $user->roles )){
				if($active_domain === $this->kiviCareProOnName()){
					$clinic =  (new KCReceptionistClinicMapping())->get_by([ 'receptionist_id' => get_current_user_id()]);
					if(isset($clinic[0]->clinic_id)) {
						$clinic_id = $clinic[0]->clinic_id ;
					}
				}
				else{
                    $clinic_id = kcGetDefaultClinicId();
				}
				$query .= " AND {$appointments_table}.clinic_id = " . $clinic_id;
			}
		}

		if ( in_array( $this->getDoctorRole(), $user->roles ) ) {
			$query .= " AND {$appointments_table}.doctor_id = " . $user->ID;
		} elseif ( in_array( $this->getPatientRole(), $user->roles ) ) {
			$query .= " AND {$appointments_table}.patient_id = " . $user->ID;
		}

		if ( isset( $filterData['patient_id']['id'] ) && $filterData['patient_id']['id'] !== null ) {
			$query = $query . " AND {$appointments_table}.patient_id = " . $filterData['patient_id']['id'];
		}

		if ( isset( $request_data['filterData']['visit_type'] ) && $request_data['filterData']['visit_type']['id'] !== null ) {
			$query = $query . " AND {$appointments_table}.visit_type = '{$request_data['filterData']['visit_type']['id']}' ";
		}

		if(isset($request_data['filterData']['clinic_id']['id']) && $active_domain === $this->kiviCareProOnName() ){
			if($request_data['filterData']['clinic_id']['id'] == 0){
				$query = $query;

			}else{
				$query = $query . " AND {$appointments_table}.clinic_id = '{$request_data['filterData']['clinic_id']['id']}' ";
			}
		}

        if ( isset( $filterData['status'] )  ) {

            if (!empty($filterData['status']) && (int)$filterData['status'] === -1 ) {
                $time  = current_time('H:i:s');
				$query = $query . " ORDER BY  {$appointments_table}.appointment_start_time ASC";
			
			} elseif (!empty($filterData['status']) && $filterData['status'] === "all" ) {
                $query = $query . " ORDER BY  {$appointments_table}.appointment_start_time ASC";

			} elseif (!empty($filterData['status']) && $filterData['status'] == "past") {

                $query = $query . " ORDER BY {$appointments_table}.appointment_start_time DESC";

            } elseif (isset($filterData['status']['value'])) {
                $query = $query . " AND {$appointments_table}.status = {$filterData['status']['value']} ORDER BY {$appointments_table}.appointment_start_time ASC";
            } else {
				if(!empty($filterData['status']) && $filterData['status'] == 1) {
					$query = $query . " AND {$appointments_table}.status  IN(1,4) ORDER BY {$appointments_table}.appointment_start_time ASC";
				}else{
					$query = $query . " AND {$appointments_table}.status = {$filterData['status']} ORDER BY {$appointments_table}.appointment_start_time ASC";
				}
			}
		}else{
            $query = $query . " ORDER BY  {$appointments_table}.appointment_start_time ASC";
        }

		// if(isset($filterData['limit']) && isset($filterData['offset'])) {
		// 	$query = $query . " LIMIT " . $filterData['limit'] . " OFFSET " . $filterData['offset'];
		// }	

		$appCollection = collect( $this->db->get_results( $query, OBJECT ) )->unique( 'id' );

		$encounters = collect([]);

		if (count($appCollection)) {

			$appointment_ids = $appCollection->pluck('id')->implode(',');
			$encounter_table = $this->db->prefix . 'kc_' . 'patient_encounters';
			$encounter_query = " SELECT * FROM $encounter_table WHERE appointment_id IN ($appointment_ids) ";
			$encounters = collect($this->db->get_results($encounter_query, OBJECT));

			$zoom_mappings = apply_filters('kct_get_meeting_list', [
				'appointment_ids' => $appointment_ids
			]);

			if (isset($zoom_mappings['appointment_ids'])) {
				$zoom_mappings = collect([]);
			}
			
			$appointments = $appCollection->map( function ( $appointment ) use ($encounters, $zoom_mappings) {
				$appointments_table = $this->db->prefix . 'kc_' . 'appointments';
				$appointments_service_table = $this->db->prefix . 'kc_' . 'appointment_service_mapping';
				$service_table = $this->db->prefix . 'kc_' . 'services';
	            $service_doctor_table = $this->db->prefix . 'kc_' . 'service_doctor_mapping';
				$zoom_config_data = get_user_meta($appointment->doctor_id, 'zoom_config_data', true);
				$get_service =  "SELECT {$appointments_table}.id,{$service_table}.name AS service_name,{$service_table}.id AS service_id,{$service_doctor_table}.charges FROM {$appointments_table}
				LEFT JOIN {$appointments_service_table} ON {$appointments_table}.id = {$appointments_service_table}.appointment_id JOIN {$service_table} 
				ON {$appointments_service_table}.service_id = {$service_table}.id JOIN {$service_doctor_table} ON {$service_doctor_table}.service_id ={$service_table}.id and {$service_doctor_table}.doctor_id={$appointment->doctor_id}  WHERE 0 = 0";
				$enableTeleMed = false;
				if ($zoom_config_data) {
					$zoom_config_data = json_decode($zoom_config_data);
					if (isset($zoom_config_data->enableTeleMed) && (bool)$zoom_config_data->enableTeleMed) {
						if ($zoom_config_data->api_key !== "" && $zoom_config_data->api_secret !== "") {
							$enableTeleMed = true;
						}
					}
				}
				$appointment->appointment_start_time = date( 'h:i A', strtotime( $appointment->appointment_start_time ) );
				$appointment->appointment_end_time   = date( 'h:i A', strtotime( $appointment->appointment_end_time ) );
				$appointment->clinic_id  = [
					'id'    => $appointment->clinic_id,
					'label' => $appointment->clinic_name
				];
				$appointment->doctor_id  = [
					'id'    => $appointment->doctor_id,
					'label' => $appointment->doctor_name,
					'enableTeleMed' => $enableTeleMed
				];
				$appointment->patient_id = [
					'id'   => $appointment->patient_id,
					'label' => $appointment->patient_name
				];
			
				$appointment->encounter = $encounters->where('appointment_id', $appointment->id)->first();
	
				$zoom_data = $zoom_mappings->where('appointment_id', $appointment->id)->first();
				$appointment->zoom_data = $zoom_data;
	
				$video_consultation = false;


				if (!empty($zoom_data)) {
					$video_consultation = true;
				}

                $appointment->clinic_prefix ='';
                $appointment->clinic_postfix = '';
                if(isset($appointment->clinic_id['id']) && !empty($appointment->clinic_id['id'])){
                    $prefix_postfix =$this->db->get_var('select extra from '.$this->db->prefix.'kc_clinics where id='.$appointment->clinic_id['id']);
                    if($prefix_postfix != null){
                        $prefix_postfix = json_decode( $prefix_postfix);
                        $appointment->clinic_prefix = isset($prefix_postfix->currency_prefix) ? $prefix_postfix->currency_prefix : '';
                        $appointment->clinic_postfix = isset($prefix_postfix->currency_postfix) ? $prefix_postfix->currency_postfix : '';
                    }
                }
				$appointment->video_consultation = $video_consultation;
				$services = collect( $this->db->get_results( $get_service, OBJECT ) )->where('id', $appointment->id);
				$service_array=$service_list=[];
                $service_charges = '';
				foreach ($services as $service) {
					$service_array[] =  $service->service_name;
					$service_list[] = [
						'service_id'    => $service->service_id,
						'name' => $service->service_name,
                        'charges' => $service->charges
					];
                    $service_charges += (int)$service->charges;
				}
                $appointment->all_service_charges =$service_charges;
				$str = implode (", ", $service_array);
				$appointment->all_services = $str;
				$appointment->visit_type_old = $appointment->visit_type ;
				$appointment->visit_type = $service_list;
				$appointment->custom_fields = kcGetCustomFields('appointment_module', $appointment->id,$appointment->doctor_id['id']);
				if( $appointment->status == '1'){
					global $wpdb;
					$post_table_name = $wpdb->prefix . 'posts';
					$clinic_session_table = $wpdb->prefix . 'kc_' . 'clinic_sessions';
			
					$args['post_name'] = strtolower(KIVI_CARE_PREFIX.'default_event_template');
					$args['post_type'] = strtolower(KIVI_CARE_PREFIX.'gcal_tmp') ;
				
					$query = "SELECT * FROM $post_table_name WHERE `post_name` = '" . $args['post_name'] . "' AND `post_type` = '".$args['post_type']."' AND post_status = 'publish' ";
					$check_exist_post = $wpdb->get_results($query, ARRAY_A);
		
					$clinicData = (new KCClinic())->get_by(['id'=>$appointment->clinic_id['id']] ,'=',true);
					$clinicAddress = $clinicData->address.','.$clinicData->city.','.$clinicData->country;
		
					$appointment_day = strtolower(date('l', strtotime($appointment->appointment_start_date))) ;
					$day_short = substr($appointment_day, 0, 3);
					
					$query = "SELECT * FROM {$clinic_session_table}  
					WHERE `doctor_id` = ".$appointment->doctor_id['id']." AND `clinic_id` = ".$appointment->clinic_id['id']."  
					AND ( `day` = '{$day_short}' OR `day` = '{$appointment_day}') ";
		
					$clinic_session = collect($wpdb->get_results($query, OBJECT));
					
					$time_slot             = isset($clinic_session[0]->time_slot) ? $clinic_session[0]->time_slot : 15;
					$end_time             = strtotime( "+" . $time_slot . " minutes", strtotime($appointment->appointment_start_time ) );
		
					$appointment_end_time = date( 'H:i:s', $end_time );
					$calender_title = $check_exist_post[0]['post_title'];
					$calender_content = $check_exist_post[0]['post_content'];
					$key  =  ['{{service_name}}','{{clinic_name}}'];
					foreach($key as $item => $value ){
						switch ($value) {
							case '{{service_name}}':
								$calender_title = str_replace($value, $appointment->all_services, $calender_title);
								break;
							case '{{clinic_name}}':
								$calender_content = str_replace($value, $clinicData->name, $calender_content);
								break;
						}
					}
                  if($appointment->appointment_report != null && $appointment->appointment_report != ''){
                      if(is_array(json_decode($appointment->appointment_report)) && count(json_decode($appointment->appointment_report)) > 0)
                      $appointment->appointment_report = array_map(function ($v){
                           return wp_get_attachment_url($v);
                      },json_decode($appointment->appointment_report));
                  }
				  $appointment->calender_title = $calender_title;
				  $appointment->calender_content = $calender_content;
				  $appointment->clinic_address =$clinicAddress;
				  $appointment->start = date("c", strtotime( $appointment->appointment_start_date.$appointment->appointment_start_time));
				  $appointment->end =  date("c", strtotime( $appointment->appointment_start_date.$appointment_end_time));

				}
				$date = date("Y-m-d H:i:s" ,strtotime(date("c", strtotime( $appointment->appointment_start_date.$appointment->appointment_start_time))));
				$current_date = current_time("Y-m-d H:i:s");
				if ($date > $current_date) {
					$appointment->isEditAble = (bool)true;
				} else {
					$appointment->isEditAble = (bool)false;
				}
				return $appointment;
	
			} )->sortBy('appointment_start_time')->sortBy('appointment_start_date')->values();

		} else {
			$appointments = [];
		}
		
		echo json_encode( [
			'status'  => true,
			'message' => esc_html__('Appointments', 'kc-lang'),
			'data'    => $appointments,
			'nextPage' => (!empty($request_data['page']) ? $request_data['page'] + 1 :  1 ),
		] );

	}

	public function allAppointment() {

		$request_data = $this->request->getInputs();
		$condition    = '';
		$appointments_table = $this->db->prefix . 'appointments';

		$filterData = isset( $request_data['filterData'] ) ? $request_data['filterData'] : [];

		$users_table   = $this->db->prefix . 'users';
		$static_data_table  = $this->db->prefix . 'static_data';

		if ( $request_data['searchKey'] && $request_data['searchValue']) {
			$condition = " WHERE {$request_data['searchKey']} LIKE  '%{$request_data['searchValue']}%' ";
		}

		$query = "
			SELECT {$appointments_table}.*,
		       doctors.display_name  AS doctor_name,
		       patients.display_name AS patient_name
		       
			FROM  {$appointments_table}
		       LEFT JOIN {$users_table} doctors
		            ON {$appointments_table}.doctor_id = doctors.id
		       LEFT JOIN {$users_table} patients
		            ON {$appointments_table}.patient_id = patients.id
		       {$condition}  
		       ORDER BY {$appointments_table}.appointment_start_date DESC LIMIT {$request_data['limit']} OFFSET {$request_data['offset']}";

		$appointments = collect( $this->db->get_results( $query, OBJECT ) )->unique( 'id' );

		$appointment_count_query = "SELECT count(*) AS count FROM {$appointments_table}";

		$total_appointment = $this->db->get_results( $appointment_count_query, OBJECT );

		if ( $request_data['searchKey'] && $request_data['searchValue'] ) {
			$total_rows = count( $appointments );
		} else {
			$total_rows = $total_appointment[0]->count;
		}

		if ( $total_rows < 0 ) {
			echo json_encode( [ 		
				'status'  => false,
				'message' => esc_html__('No appointment found', 'kc-lang'),
				'data'    => []
			] );
			wp_die();
		}

		$visit_type_data =  $appointments->pluck('visit_type')->unique()->implode("','");

		$static_data_query = " SELECT * FROM $static_data_table WHERE value IN ('$visit_type_data') ";

		$static_data = collect($this->db->get_results( $static_data_query, OBJECT ))->pluck('label','value')->toArray();

		foreach ($appointments as $key => $appointment) {
			$appointment->type_label = $static_data[$appointment->visit_type];
		}

		echo json_encode( [
			'status'  => true,
			'message' => esc_html__('Appointments', 'kc-lang'),
			'data'    => $appointments,
			'total_rows' => $total_rows
		] );
	}
	public function resendZoomLink(){
		$request_data = $this->request->getInputs();
		$res_data = apply_filters('kct_send_resend_zoomlink', $request_data);
	}

    public function restrictAppointmentSave(){
        $request_data = $this->request->getInputs();
        $message = esc_html__('Failed to update', 'kc-lang');
        $status =false;
        if(isset($request_data['pre_book']) && isset($request_data['post_book'])){
            if((int)$request_data['pre_book'] < 0 && (int)$request_data['post_book'] < 0 ){
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html__('Pre or Post Book Days Must Be Greater then Zero ', 'kc-lang'),
                ] );
                die;
            }
            update_option(KIVI_CARE_PREFIX .'restrict_appointment',['post' => $request_data['post_book'] ,'pre' =>$request_data['pre_book']]);
            $status = true;
            $message = esc_html__('Appointment restrict days saved successfully', 'kc-lang');
        }
        echo json_encode( [
            'status'  => $status,
            'message' => $message,
        ] );
        die;
    }

    public function restrictAppointmentEdit(){
       $data = get_option(KIVI_CARE_PREFIX . 'restrict_appointment',true);
       if(gettype($data) != 'boolean'){
           $temp = [
               'pre_book' => isset($data['pre']) && !empty($data['pre']) ? $data['pre'] : 0,
               'post_book' => isset($data['post']) && !empty($data['post']) ? $data['post'] : 365,
           ];
       }else{
           $temp = [
               'pre_book' =>  0,
               'post_book' => 365,
           ];
       }
        echo json_encode( [
            'status'  =>  true,
            'data' => $temp,
        ] );
        die;
    }

    public function getMultifileUploadStatus(){
        $data = get_option(KIVI_CARE_PREFIX . 'multifile_appointment',true);

        if(gettype($data) != 'boolean'){
            $temp = $data;
        }else{
            $temp = 'off';
        }

        echo json_encode( [
            'status'  => true,
            'data' => $temp,
        ] );
    }

    public function saveMultifileUploadStatus(){
        $request_data = $this->request->getInputs();
        $message = esc_html__('Failed to update', 'kc-lang');
        $status =false;
        if(isset($request_data['status']) && !empty($request_data['status']) ){
            $table_name = $this->db->prefix . 'kc_' . 'appointments';
            kcUpdateFields($table_name,[ 'appointment_report' => 'longtext NULL']);
            update_option(KIVI_CARE_PREFIX . 'multifile_appointment',$request_data['status']);
            $message = esc_html__('File Upload In Appointment Status Saved', 'kc-lang');
            $status = true;
        }
        echo json_encode( [
            'status'  => $status ,
            'message' => $message,
        ] );
        die;
    }

    public function appointmentReminderNotificatioSave(){
        $request_data = $this->request->getInputs();
        $message = esc_html__('Failed to update', 'kc-lang');
        $status =false;
        if(isset($request_data['status']) && !empty($request_data['status']) && isset($request_data['time'])){
            update_option(KIVI_CARE_PREFIX . 'email_appointment_reminder',[
                "status" =>$request_data['status'],
                "time" =>$request_data['time'],
                "sms_status"=>isset($request_data['sms_status']) ? $request_data['sms_status'] : 'off' ,
                "whatapp_status" => isset($request_data['whatapp_status']) ? $request_data['whatapp_status'] : 'off' ]
            );
            $message = esc_html__('Email Appointment Reminder Setting Saved', 'kc-lang');
            $status = true;
            if($request_data['status'] == 'off' && isset($request_data['sms_status']) && $request_data['sms_status'] == 'off' && isset($request_data['whatapp_status']) && $request_data['whatapp_status'] == 'off' ){
                wp_clear_scheduled_hook("kivicare_patient_appointment_reminder");
            }
        }
        echo json_encode( [
            'status'  => $status,
            'message' => $message,
        ] );
        die;
    }

    public function getAppointmentReminderNotification(){
        $data = get_option(KIVI_CARE_PREFIX . 'email_appointment_reminder',true);
        $active_domain =$this->getAllActivePlugin();
        $default_email_template = [
            [
                'post_name' => KIVI_CARE_PREFIX.'book_appointment_reminder',
                'post_content' => '<p> Welcome to KiviCare ,</p><p> You Have appointment  on </p>  <p> Doctor: {{doctor_name}} </p> <p> {{appointment_date}}  , Time : {{appointment_time}}  </p><p> Thank you. </p>',
                'post_title' => 'Patient Appointment Reminder',
                'post_type' => KIVI_CARE_PREFIX.'mail_tmp',
                'post_status' => 'publish',
            ],
            [
                'post_name' => KIVI_CARE_PREFIX.'book_appointment_reminder',
                'post_content' => '<p> Welcome to KiviCare ,</p><p> You Have appointment  on </p> <p> Doctor: {{doctor_name}} </p> <p> {{appointment_date}}  , Time : {{appointment_time}}  </p><p> Thank you. </p>',
                'post_title' => 'Patient Appointment Reminder',
                'post_type' => KIVI_CARE_PREFIX.'sms_tmp',
                'post_status' => 'publish',
            ]
        ];
        kcAddMailSmsPosts($default_email_template);

        //create table
        require KIVI_CARE_DIR . 'app/database/kc-appointment-reminder-db.php';

        if(gettype($data) != 'boolean'){
            $temp = $data;
            if( $active_domain !== $this->kiviCareProOnName()){
                if(is_array($temp ) ){
                    $temp['sms_status'] = 'off';
                    $temp['whatapp_status'] = 'off';
                }
            }
        }else{
            $temp = ["status" => 'off',"sms_status"=> 'off',"time" => '24',"whatapp_status" => 'off'];
        }

        echo json_encode( [
            'status'  => true,
            'data' => $temp,
        ] );
    }
}
