<?php

namespace App\Controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCService;
use App\models\KCServiceDoctorMapping;
use Automattic\WooCommerce\Blocks\RestApi;
use App\models\KCReceptionistClinicMapping;
use App\models\KCClinic;
use Exception;
use WP_User;
class KCServiceController extends KCBase {

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

		$request_data      = $this->request->getInputs();
		$condition         = '';
		$service_table     = $this->db->prefix . 'kc_' . 'services';
		$static_data_table = $this->db->prefix . 'kc_' . 'static_data';
		$service_doctor_mapping  = $this->db->prefix . 'kc_' . 'service_doctor_mapping' ;
		$users_table       = $this->db->prefix . 'users';
		$clinic_doctor_mapping = $this->db->prefix.'kc_doctor_clinic_mappings';
		$active_domain =$this->getAllActivePlugin();
        $login_user = wp_get_current_user();

		$service_count_query = "SELECT count(*) AS count FROM {$service_table}";
		$service_types_query = " SELECT * FROM  {$static_data_table} WHERE type = 'service_type'";

		$services_types = $this->db->get_results( $service_types_query, OBJECT );

		$services_types = collect( $services_types );

		$services_types = $services_types->unique( 'value' );

		$total_services = $this->db->get_results( $service_count_query, OBJECT );

		if ( isset($request_data['searchKey']) && $request_data['searchKey'] !== '' && isset($request_data['searchValue']) && $request_data['searchValue'] !== '') {
			$condition = " WHERE {$service_table}.{$request_data['searchKey']}  LIKE  '%{$request_data['searchValue']}%' ";
		}

		$doctor_condition = "" ;
        $zoom_config_data = "" ;
		$clinic_condition = "";
		$clinic_join = "";

		if(isset($login_user->roles[0]) && $this->getDoctorRole() === $login_user->roles[0]) {
            
			$doctor_condition = " AND {$service_doctor_mapping}.doctor_id = {$login_user->data->ID} " ;

			$zoom_config_data = get_user_meta($login_user->data->ID, 'zoom_config_data', true);

        	$zoom_config_data = json_decode($zoom_config_data);

        } else if(isset($request_data['doctor_id'])) {
		
			$doctor_condition = " AND {$service_doctor_mapping}.doctor_id = " . $request_data['doctor_id'] ;

            $zoom_config_data = get_user_meta($request_data['doctor_id'], 'zoom_config_data', true);

            $zoom_config_data = json_decode($zoom_config_data);

        }elseif(isset($login_user->roles[0]) && $this->getClinicAdminRole() === $login_user->roles[0]){
			if($active_domain === $this->kiviCareProOnName()){
				$clinic_join = " JOIN  {$clinic_doctor_mapping} ON {$clinic_doctor_mapping}.doctor_id = {$service_doctor_mapping}.doctor_id";
				$clinic =  (new KCClinic())->get_by([ 'clinic_admin_id' => get_current_user_id()]);
				if(isset($clinic[0]->id)) {
					$clinic_id = $clinic[0]->id ;
				}
				$clinic_condition = " AND {$clinic_doctor_mapping}.clinic_id = " . $clinic_id ;
			}
			else{
				$clinic_id = kcGetDefaultClinicId();
			}
		}elseif(isset($login_user->roles[0]) && $this->getReceptionistRole() === $login_user->roles[0]){
			if($active_domain === $this->kiviCareProOnName()){
				$clinic_join = " JOIN  {$clinic_doctor_mapping} ON {$clinic_doctor_mapping}.doctor_id = {$service_doctor_mapping}.doctor_id";
				$clinic =  (new KCReceptionistClinicMapping())->get_by([ 'receptionist_id' => get_current_user_id()]);
				if(isset($clinic[0]->clinic_id)) {
					$clinic_id = $clinic[0]->clinic_id ;
				}
				$clinic_condition = " AND {$clinic_doctor_mapping}.clinic_id = " . $clinic_id ;
			}
			else{
				$clinic_id = kcGetDefaultClinicId();
			} 
		}
	
		$active_services = '' ;

		// get active services condition
		if(isset($request_data['module_type']) && $request_data['module_type'] === 'appointment_form') {
			$active_services = " AND {$service_table}.status = '1' ";
		}
	
		if(isset($zoom_config_data->enableTeleMed) && strval($zoom_config_data->enableTeleMed) == 'true') {
			$query = "SELECT {$service_doctor_mapping}.*, {$service_table}.name AS name, {$service_table}.type AS service_type, {$users_table}.display_name AS doctor_name  FROM {$service_doctor_mapping}
					JOIN {$service_table}
					ON {$service_doctor_mapping}.service_id = {$service_table}.id
					JOIN {$users_table}
					ON {$users_table}.ID = {$service_doctor_mapping}.doctor_id
					$clinic_join
					WHERE 0 = 0  {$doctor_condition}  {$clinic_condition} {$active_services} ORDER BY {$service_table}.id  DESC" ;
		}else{
			$query = "  SELECT {$service_doctor_mapping}.*, {$service_table}.name AS name, {$service_table}.type AS service_type, {$users_table}.display_name AS doctor_name  FROM {$service_doctor_mapping}
			JOIN {$service_table}
			ON {$service_doctor_mapping}.service_id = {$service_table}.id
			JOIN {$users_table}
			ON {$users_table}.ID = {$service_doctor_mapping}.doctor_id
			$clinic_join
			WHERE 0 = 0 {$doctor_condition} {$clinic_condition} {$active_services} AND {$service_table}.type != 'system_service' ORDER BY {$service_table}.id  DESC" ;
		}


		$services = $this->db->get_results( $query, OBJECT );

		$services = collect( $services );

		$services = $services->map( function ( $services ) use ( $services_types ) {
			$services->service_type = isset( $services->service_type ) ? str_replace( '_', ' ', $services->service_type) : "";
			return $services;
		} );

		if (isset($request_data['searchKey']) && $request_data['searchKey'] !== '' && isset($request_data['searchValue']) && $request_data['searchKey'] !== '' ) {
			$total_rows = count( $services );
		} else {
			$total_rows = $total_services[0]->count;
		}

		if ( ! count( $services ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => esc_html__('No services found', 'kc-lang'),
				'data'    => []
			] );
			wp_die();
		}

		echo json_encode( [
			'status'     => true,
			'message'    => esc_html__('Service list', 'kc-lang'),
			'data'       => $services,
			'total_rows' => $total_rows
		] );

	}

	public function save() {

		global $wpdb;

		if ( ! kcCheckPermission( 'service_add' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have a permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();

		$temp = [
			'name'   => $request_data['name'],
			'price'  => $request_data['price'],
			'type'   => $request_data['type']['id'],
			'status' => $request_data['status']['id'],
		];
		$static_data_table =  $wpdb->prefix . 'kc_static_data';
		$query = "SELECT * FROM {$static_data_table} WHERE id = '{$request_data['type']['id']}' " ;
		$result = $wpdb->get_row($query);

		if(!empty($result)) {
			$temp['type'] = $result->value ;
		}

		$service = new KCService();

		$service_doctor_mapping = new KCServiceDoctorMapping();

		if (!isset( $request_data['id'])) {

			$temp['created_at'] = current_time( 'Y-m-d H:i:s' );
			$service_id = $service->insert( $temp );
		
            if ($service_id) {
				$user_id = get_current_user_id();
				$userObj = new WP_User($user_id);
				if($userObj->roles[0] == 'kiviCare_doctor') {
					$service_mapping_data = [
                        'service_id' => $service_id,
                        'clinic_id'  => kcGetDefaultClinicId(),
						'doctor_id'  => $user_id,
						'charges'    => $request_data['price'],
						'status'     => $request_data['status']['id'],
                    ];

					$service_doctor_mapping->insert($service_mapping_data);
					// hook for service add
					do_action( 'kc_service_add', $service_mapping_data );
				} else {
					foreach ($request_data['doctor_id'] as $key => $val) {
						$service_mapping_data = [
							'service_id' => $service_id,
							'clinic_id'  => kcGetDefaultClinicId(),
							'doctor_id'  => $val['id'],
							'charges'    => $request_data['price'],
							'status'     => $request_data['status']['id'],
                    	] ;
                        $service_doctor_mapping->insert($service_mapping_data);
						// hook for service add
						do_action( 'kc_service_add', $service_mapping_data );
                	}
				}
            }

			$message            = esc_html__('Service has been saved successfully', 'kc-lang');

		} else {

			$user = wp_get_current_user();

            $service->update([
				'name' => $request_data['name']
			], array('id' => $request_data['service_id']));
			
			$appointments_service_table = $this->db->prefix . 'kc_service_doctor_mapping';
            $service_data = $this->db->get_results('select id from '.$appointments_service_table.' where service_id='.$request_data['service_id'] );
			if($service_data != null && count($service_data) >0 ){
				foreach ($service_data as $s){
					$product_id = $this->getProductIdOfService($s->id);
					if($product_id != null &&  get_post_status( $product_id )){
						$my_post = array(
							'ID'           => $product_id,
							'post_title'   => $request_data['name'],
							);
						wp_update_post( $my_post );
					}
				}
			}

			$service_mapping_update_data  = [
				'service_id' => $request_data['service_id'],
				'doctor_id' => $request_data['doctor_id']['id'],
				'charges'    => $request_data['price'],
                'status'    => $request_data['status']['id'],
			];

            $service_doctor_mapping->update($service_mapping_update_data, array('id' => $request_data['id']));

			$service_mapping_update_data  = [
				'id' => $request_data['id'],
				'service_id' => $request_data['service_id'],
				'doctor_id' => $request_data['doctor_id']['id'],
				'charges'    => $request_data['price'],
                'status'    => $request_data['status']['id'],
			];

			do_action( 'kc_service_update', $service_mapping_update_data);

            $product_id = $this->getProductIdOfService($request_data['id']);
            if($product_id != null &&  get_post_status( $product_id )){
                update_post_meta($product_id,'_price', $request_data['price']);
                update_post_meta($product_id,'_sale_price', $request_data['price']);
            }
            $service->update( $temp, array( 'id' => $request_data['id'] ) );
            $message = esc_html__('Service has been updated successfully', 'kc-lang');

		}

		echo json_encode( [
			'status'  => true,
			'message' => esc_html__($message, 'kc-lang')
		] );

	}

	public function edit() {

		
		if ( ! kcCheckPermission( 'service_edit' ) || ! kcCheckPermission( 'service_view' ) ) {
			
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

			$edit_id = $request_data['id'];
			$service_table     = $this->db->prefix . 'kc_' . 'services';
			$static_data_table = $this->db->prefix . 'kc_' . 'static_data';
			$service_doctor_mapping  = $this->db->prefix . 'kc_' . 'service_doctor_mapping' ;
			$users_table       = $this->db->prefix . 'users';
			
			$query = " SELECT {$service_doctor_mapping}.id AS mapping_id, {$service_table}.id AS service_id, 
                              {$service_doctor_mapping}.*, {$service_table}.* , {$users_table}.display_name AS doctor_name, 
                              {$service_doctor_mapping}.charges AS doctor_charges, 
                              {$service_doctor_mapping}.status AS mapping_status
                       FROM  {$service_doctor_mapping} 
					   JOIN  {$users_table} ON {$users_table}.ID = {$service_doctor_mapping}.doctor_id  
					   JOIN  {$service_table} ON {$service_table}.id = {$service_doctor_mapping}.service_id  
					   WHERE {$service_doctor_mapping}.id = {$edit_id} ";


			$service = $this->db->get_results( $query, OBJECT );

			if ( count( $service ) ) {

				$service = $service[0];

				$service_category_query = "SELECT * FROM  {$static_data_table} WHERE value = '{$service->type}' LIMIT 1 " ;

                $service_category = $this->db->get_results( $service_category_query, OBJECT );

                $status =  new \stdClass();
				$status->id =  0 ;
				$status->label = 'Inactive' ;
				
				if((int) $service->mapping_status === 1) {
					$status->id = 1 ;
					$status->label = 'Active' ;
				}

				$temp = [
					'id'     => $service->mapping_id,
					'service_id' => $service->service_id,
					'name'   => $service->name,
					'price'  => $service->doctor_charges,
                    'doctor_id' =>  [
						'id' 	=>  $service->doctor_id,
						'label' =>  $service->doctor_name
					],
					'type'   => [
						'id'    => $service_category[0]->type,
						'label' => $service_category[0]->label
					],
					'status' => $status,
				];

                echo json_encode( [
					'status'  => true,
					'message' => esc_html__('Service data', 'kc-lang'),
					'data'    => $temp
				] );

			} else {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}


		} catch ( Exception $e ) {
			$code    = $e->getCode();
			$message = $e->getMessage();
			header( "Status: $code $message" );
			echo json_encode( [
				'status'  => false,
				'message' => $e->getMessage()
			] );
		}
	}

	public function delete() {

		if ( ! kcCheckPermission( 'service_delete' ) ) {
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

            $service_doctor_mapping = new KCServiceDoctorMapping();

            $product_id = $this->getProductIdOfService($request_data['id']);
            if($product_id != null && get_post_status( $product_id )){
				do_action( 'kc_woocoomerce_service_delete', $product_id );
                wp_delete_post($product_id);
            }

			$id = $request_data['id'];
			$results = $service_doctor_mapping->delete( [ 'id' => $id ] );

			if ( $results ) {
				do_action( 'kc_service_delete', $id);
				echo json_encode( [
					'status'  => true,
					'message' => esc_html__('Service has been deleted successfully', 'kc-lang'),
				] );
			} else {
				throw new Exception( esc_html__('Service delete failed', 'kc-lang'), 400 );
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

	public function clinicService () {
	    $table = $this->db->prefix  . 'kc_' . 'services' ;
        $query = "SELECT `id`, `type`, `name`, `price` FROM {$table} " ;
        $services = $this->db->get_results( $query, OBJECT );
        echo json_encode([
            'status' => true,
            'message' => esc_html__('Clinic service list', 'kc-lang'),
            'data' => $services
        ]);
    }

	public function getProductIdOfService($id){
		$product_id = '';
		$appointments_service_table =  $this->db->prefix . 'kc_service_doctor_mapping';
		$data = $this->db->get_var('select extra from '.$appointments_service_table.' where id='.$id);
        if($data != null){
			$data = json_decode($data);
            $product_id = $data->product_id;
		}	
		return $product_id;
	}
}


