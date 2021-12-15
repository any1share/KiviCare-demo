<?php

namespace App\Controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCPatientEncounter;
use App\models\KCPrescription;
use Exception;

class KCPatientPrescriptionController extends KCBase {

	public $db;

	/**
	 * @var KCRequest
	 */
	private $request;

	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->request = new KCRequest();

	}

	public function index() {

		if ( ! kcCheckPermission( 'prescription_list' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();

		if ( ! isset( $request_data['encounter_id'] ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__('Encounter not found', 'kc-lang'),
				'data'    => []
			] );
			wp_die();
		}

		$encounter_id       = $request_data['encounter_id'];
		$prescription_table = $this->db->prefix . 'kc_' . 'prescription';

		$query = "SELECT * FROM  {$prescription_table} WHERE encounter_id = {$encounter_id}";

		$prescriptions = collect( $this->db->get_results( $query, OBJECT ) )->map( function ( $data ) {
			$data->name = [
				'id'    => $data->name,
				'label' => $data->name
			];
            $data->frequency = [
                'id'    => $data->frequency,
                'label' => $data->frequency
            ];
			return $data;
		} );

		$total_rows = count( $prescriptions );

		if ( ! count( $prescriptions ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__('No prescription found', 'kc-lang'),
				'data'    => []
			] );
			wp_die();
		}

		echo json_encode( [
			'status'     => true,
			'message'    => esc_html__('Prescription records', 'kc-lang'),
			'data'       => $prescriptions,
			'total_rows' => $total_rows
		] );
	}

	public function save() {

		if ( ! kcCheckPermission( 'prescription_add' ) ) {
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
			'encounter_id' => 'required',
			'name'         => 'required',
			'frequency'    => 'required',
			'duration'     => 'required',
		];

		$errors = kcValidateRequest( $rules, $request_data );

		if ( count( $errors ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__($errors[0], 'kc-lang')
			] );
			die;
		}

		$patient_encounter = ( new KCPatientEncounter )->get_by( [ 'id' => $request_data['encounter_id'] ], '=', true );
		$patient_id        = $patient_encounter->patient_id;

		if ( empty( $patient_encounter ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__("No encounter found", 'kc-lang')
			] );
			die;
		}

		$temp = [
			'encounter_id' => $request_data['encounter_id'],
			'patient_id'   => $patient_id,
			'name'         => $request_data['name']['id'],
			'frequency'    => $request_data['frequency']['id'],
			'duration'     => $request_data['duration'],
			'instruction'  => $request_data['instruction'],
		];

		$prescription = new KCPrescription();

		if ( ! isset( $request_data['id'] ) ) {

			$temp['created_at'] = current_time( 'Y-m-d H:i:s' );
			$temp['added_by']   = get_current_user_id();
			$prescription_id    = $prescription->insert( $temp );
			$message            = esc_html__('Prescription has been saved successfully', 'kc-lang');

		} else {
			$prescription_id = $request_data['id'];
			$status          = $prescription->update( $temp, array( 'id' => $request_data['id'] ) );
			$message         = esc_html__('Prescription has been updated successfully', 'kc-lang');
		}

		$data = $prescription->get_by( [ 'id' => $prescription_id ], '=', true );
		$data->name = [
			'id'    => $data->name,
			'label' => $data->name
		];
		$data->frequency = [
			'id'    => $data->frequency,
			'label' => $data->frequency
		];

		echo json_encode( [
			'status'  => true,
			'message' => $message,
			'data'    => $data
		] );

	}

	public function edit() {

		if ( ! kcCheckPermission( 'prescription_edit' ) || ! kcCheckPermission( 'prescription_view' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();

		try {

			if ( ! isset( $request_data['id'] ) ) {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}

			$id = $request_data['id'];

			$prescription_table = $this->db->prefix . 'kc_' . 'prescription';

			$query = "SELECT * FROM  {$prescription_table} WHERE id = {$id}";

			$prescription = $this->db->get_results( $query, OBJECT );

			if ( count( $prescription ) ) {
				$prescription = $prescription[0];

				$temp = [
					'id'           => $prescription->id,
					'patient_id'   => $prescription->patient_id,
					'encounter_id' => $prescription->encounter_id,
					'title'        => $prescription->title,
					'notes'        => $prescription->notes,
					'added_by'     => $prescription->added_by,
				];


				echo json_encode( [
					'status'  => true,
					'message' => esc_html__('Prescription record', 'kc-lang'),
					'data'    => $temp
				] );
			} else {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}


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

	public function delete() {

		if ( ! kcCheckPermission( 'prescription_delete' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();

		try {

			if ( ! isset( $request_data['id'] ) ) {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}

			$id = $request_data['id'];

			$results = ( new KCPrescription() )->delete( [ 'id' => $id ] );

			if ( $results ) {
				echo json_encode( [
					'status'  => true,
					'message' => esc_html__('Prescription has been deleted successfully', 'kc-lang'),
				] );
			} else {
				throw new Exception( esc_html__('Prescription delete failed', 'kc-lang'), 400 );
			}


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

	public function mailPrescription(){
        $request_data = $this->request->getInputs();
        $precription_table = $this->db->prefix.'kc_prescription';
        $encounter_table = $this->db->prefix.'kc_patient_encounters';
        $status = false;
        $message = esc_html__('Failed To Send Mail', 'kc-lang');
        $default_email_template = [
            [
                'post_name' => KIVI_CARE_PREFIX.'book_prescription',
                'post_content' => '<p> Welcome to KiviCare ,</p><p> You Have Medicine Prescription on </p><p> Clinic : {{clinic_name}}</p><p>Doctor : {{doctor_name}}</p><p>Prescription :{{prescription}} </p><p> Thank you. </p>',
                'post_title' => 'Patient Prescription Reminder',
                'post_type' => KIVI_CARE_PREFIX.'mail_tmp',
                'post_status' => 'publish',
            ],
        ];
        kcAddMailSmsPosts($default_email_template);
        if(isset($request_data['encounter_id']) && $request_data['encounter_id'] != ''){
              $results = $this->db->get_results("SELECT pre.* ,enc.*
                                                 FROM {$precription_table} AS pre 
                                                 JOIN {$encounter_table} AS enc ON enc.id=pre.encounter_id WHERE pre.encounter_id={$request_data['encounter_id']}");

              if($results != null){
                  $doctor_id = collect($results)->pluck('doctor_id')->unique('doctor_id')->toArray();
                  $patient_id = collect($results)->pluck('patient_id')->unique('patient_id')->toArray();
                  $clinic_id = collect($results)->pluck('clinic_id')->unique('clinic_id')->toArray();
                  $doctor_data = isset($doctor_id[0]) ? get_user_by('ID',$doctor_id[0]) : '';
                  $patient_data = isset($patient_id[0]) ? get_user_by('ID',$patient_id[0]) : '';
                  $clinic_data = isset($clinic_id[0]) ? kcClinicDetail($clinic_id[0]) : '';
                  ob_start();
                  ?>
                  <table style="border: 1px solid black; width:100%" >
                      <tr>
                          <th style="border: 1px solid black;"><?php echo esc_html__('NAME','kc-lang'); ?></th>
                          <th style="border: 1px solid black;"><?php echo esc_html__('FREQUENCY','kc-lang'); ?></th>
                          <th style="border: 1px solid black;"><?php echo esc_html__('DAYS','kc-lang'); ?></th>
                      </tr>
                  <?php
                 foreach ($results as $temp){
                     ?>
                     <tr>
                         <td style="border: 1px solid black;"><?php echo !empty($temp->name) ?$temp->name : '' ; ?></td>
                         <td style="border: 1px solid black;"><?php echo !empty($temp->frequency) ?$temp->frequency : ''; ?></td>
                         <td style="border: 1px solid black;"><?php echo !empty($temp->duration) ?$temp->duration : ''; ?></td>
                     </tr>
                     <?php
                 }
                  ?>
                  </table>
                  <?php
                 $data = ob_get_clean();
                 $email_data = [
                     'user_email' => isset($patient_data->user_email) ? $patient_data->user_email:'',
                     'doctor_name' => isset($doctor_data->display_name) ? $doctor_data->display_name:'',
                     'clinic_name' => isset($clinic_data->name) ? $clinic_data->name:'',
                     'prescription' => $data,
                     'email_template_type' => 'book_prescription'
                 ];
                 $status = kcSendEmail($email_data);
                 $message = $status ? esc_html__('Prescription send to successfully.', 'kc-lang') : esc_html__('Failed To Send Mail', 'kc-lang');
              }
        }

        echo json_encode( [
            'status'  => $status,
            'message' => $message
        ] );
        die;
    }
}
