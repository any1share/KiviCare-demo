<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use Exception;
use WP_User;

class KCAuthController extends KCBase {


	public $db;

	private $request;

	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->request = new KCRequest();

	}

	public function loginPatientDetail() {

	    if(is_user_logged_in()) {

            $patient_id = get_current_user_id();

            $user = get_userdata( $patient_id );
            $image_attachment_id = get_user_meta($patient_id,'patient_profile_image',true);
			$user_image_url = wp_get_attachment_url($image_attachment_id);
            unset( $user->user_pass );

            $full_name = explode( ' ', $user->display_name );

            $user_data  = get_user_meta( $patient_id, 'basic_data', true );
            $first_name = isset( $full_name[0] ) ? $full_name[0] : "";
            $last_name  = isset( $full_name[1] ) ? $full_name[1] : "";

            $data             = (object) array_merge( (array) $user->data, (array) json_decode( $user_data ) );
            $data->first_name = $first_name;
            $data->username   = $data->user_login;
            $data->last_name  = $last_name;
            $data->user_profile =$user_image_url;
            if ( $data ) {
                echo json_encode( [
                    'status'  => true,
                    'message' => esc_html__('Patient profile data', 'kc-lang'),
                    'data'    => $data
                ] );
            } else {
                throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
            }

        } else {

            echo json_encode( [
                'status'  => false,
                'data' => '',
                'message'    => esc_html__('Patient not logged in', 'kc-lang' )
            ] );

            wp_die();

        }
    }

	public function patientLogin() {

		$parameters = $this->request->getInputs();

		try {

			$errors = kcValidateRequest( [
				'username' => 'required',
				'password' => 'required',
			], $parameters );


			if ( count( $errors ) ) {
				throw new Exception( $errors[0], 422 );
			}

            $auth_success = wp_authenticate( $parameters['username'], $parameters['password'] );

            if ( isset( $auth_success->errors ) && count( $auth_success->errors ) ) {
				throw new Exception( esc_html__( 'Incorrect username and password', 'kc-lang' ), 401 );
			}

            $user_meta = get_userdata($auth_success->data->ID);

            if($this->getPatientRole() !== $user_meta->roles[0] ) {
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html__( 'User not found user must be a patient.', 'kc-lang' ),
                ] );
                wp_die();
            }

            wp_set_current_user( $auth_success->data->ID, $auth_success->data->user_login );
            wp_set_auth_cookie( $auth_success->data->ID );
            do_action( 'wp_login', $auth_success->data->user_login, $auth_success );

            echo json_encode( [
				'status'  => true,
				'message' => esc_html__( 'Logged in successfully', 'kc-lang' ),
                'data'    => $auth_success
			] );

		} catch ( Exception $e ) {

			$code    = esc_html__( $e->getCode(), 'kc-lang' );
			$message = esc_html__( $e->getMessage(), 'kc-lang' );

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => $message
			] );
			
		}

	}

	public function patientRegister() {

		$parameters = $this->request->getInputs();

        $isEmailExist = email_exists($parameters['user_email']);

        if($isEmailExist) {
            $status = false ;
            echo json_encode([
                'status'  => (bool) $status,
                'message' => esc_html__( "Email is already exist" , 'kc-lang' )
            ]);
            wp_die();
        }

		try {

			$temp = [ 'mobile_number'  => $parameters['mobile_number'] ];

			$username = kcGenerateUsername($parameters['first_name']);

			$password = kcGenerateString(12);

			$user = wp_create_user( $username, $password, $parameters['user_email'] );

			$u               = new WP_User( $user );

			$u->display_name = $parameters['first_name'] . ' ' . $parameters['last_name'];

			wp_insert_user( $u );

			$u->set_role( $this->getPatientRole() );

			update_user_meta( $user, 'basic_data', json_encode( $temp ) );

            update_user_meta($user, 'first_name',$parameters['first_name'] );
            update_user_meta($user, 'last_name', $parameters['last_name'] );

            $auth_success = '';
            if ( $user ) {
                // hook for patient save
                do_action( 'kc_patient_save', $u->ID );

                $auth_success = wp_authenticate( $u->user_email, $u->user_pass );
                wp_set_current_user( $u->ID, $u->user_login );
                wp_set_auth_cookie(  $u->ID );
                do_action( 'wp_login',$u->user_login, $auth_success );

                $user_email_param = kcCommonNotificationUserData($u->ID,$password);
                kcSendEmail($user_email_param);
                if(kcCheckSmsOptionEnable()){
                    $sms = apply_filters('kcpro_send_sms', [
                        'type' => 'patient_register',
                        'user_data' => $user_email_param,
                    ]);
                }
            }

			if($user) {
				$status = true ;
				$message = esc_html__( "Patient registration successfully. Check your email for login credencials." , 'kc-lang' );
			} else {
				$status = false ;
				$message = esc_html__( "Patient registration not successfully." , 'kc-lang' );
			}

			echo json_encode([
				'status'  => (bool) $status,
				'message' => $message,
                 'data'    => $auth_success
			]);


		} catch ( Exception $e ) {

			$code    = esc_html__( $e->getCode(), 'kc-lang' );
			$message = esc_html__( $e->getMessage(), 'kc-lang' );

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => $message
			] );
		}
	}

    public function uploadMedicalReport(){
        $parameters = $this->request->getInputs();
        $attach = [];
        $status = false;
        if(isset($parameters['file_multi']) && $parameters['file_multi'] !== '' && $parameters['file_multi'] != null ) {
            $status =true;
            $files = $_FILES["file_multi"];
            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = array(
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    );
                    $_FILES = array("upload_file" => $file);
                    $attachment_id = media_handle_upload("upload_file", 0);
                    array_push($attach,$attachment_id);
                    if(is_wp_error($attachment_id)) {
                       foreach ($attach as $att){
                           wp_delete_attachment($att);
                       }
                        $attach = [];
                        echo json_encode([
                            'status'  => (bool) false,
                            'message' => esc_html__( "Failed to uploaded Medical report." , 'kc-lang' ),
                            'data'    => ''
                        ]);
                        die;
                    }
                }
            }
        }

        if((bool)$status) {
            echo json_encode([
                'status'  => (bool) $status,
                'message' => esc_html__( "Medical report uploaded successfully." , 'kc-lang' ),
                'data'    => $attach
            ]); die;
        } else {
            echo json_encode([
                'status'  => (bool) $status,
                'message' => esc_html__( "Medical report not uploaded successfully." , 'kc-lang' ),
                'data'    => $attach
            ]); die;
        }

        

    }

    public function changePassword() {

        $request_data = $this->request->getInputs();

        $current_user = wp_get_current_user();

        $result = wp_check_password($request_data['currentPassword'], $current_user->user_pass, $current_user->ID);

        if ($result) {

            if(isset($current_user->ID) && $current_user->ID !== null && $current_user->ID !== '') {
                wp_set_password($request_data['newPassword'], $current_user->ID);
                $status = true ;
                $message = 'Password successfully changed' ;
                wp_logout();
            } else {
                $status = false ;
                $message = 'Password change failed.' ;
            }

        } else {

            $status = false ;
            $message = 'Current password is wrong!!' ;

        }

        echo json_encode([
            'status'  => $status,
            'data' => $result,
            'message' => esc_html__($message, 'kc-lang'),
        ]);
    }

    public function logout()
    {

        wp_logout();
        echo json_encode([
            'status' => true,
            'message' => esc_html__('Logout successfully.', 'kc-lang'),
        ]);
    }

    public function forgetPassword(){

        $passwordUrl = wp_lostpassword_url();
        echo json_encode([
            'status' => true,
            'data'    => $passwordUrl
        ]);
    }
}
