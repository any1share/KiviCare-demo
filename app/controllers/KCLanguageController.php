<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use WP_User;

class KCLanguageController extends KCBase
{
    /**
     * @var KCRequest
     */
    private $request;

    public function __construct()
    {
        $this->request = new KCRequest();
    }
    
    public function updateLang(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_update_language', [
			'user_id' => $request_data['user_id'],
			'lang' => $request_data['lang'],
        ]);
        echo json_encode($response);
    }
    public function getPrescriptionPrint(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_get_prescription_print', [
			'encounter_id' => $request_data['id'],
        ]);
        echo json_encode($response);
	}
    public function updateThemeColor(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_change_themecolor', [
			'color' => $request_data['color'],
        ]);
        echo json_encode($response);
    }
    public function updateRTLMode(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_change_mode', [
			'mode' => $request_data['rtl'],
        ]);
        echo json_encode($response);
    }
    public function uploadLogo(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_upload_logo', [
			'site_logo' => $request_data['site_logo'],
        ]);
        echo json_encode($response);
    }

    public function uploadLoader(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_upload_loader', [
            'site_loader' => $request_data['site_loader'],
        ]);
        echo json_encode($response);
    }

    public function saveSmsConfig(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_save_sms_config', [
			'config_data' => $request_data,
        ]);
        echo json_encode($response);
    }
    public function saveWhatsAppConfig(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_save_whatsapp_config', [
			'config_data' => $request_data,
        ]);
        echo json_encode($response);
    }
    public function editConfig(){
        $response = apply_filters('kcpro_edit_sms_config', [
            'current_user' => get_current_user_id(),
        ]);
        echo json_encode($response);
    }
    public function editWhatsAppConfig(){
        $response = apply_filters('kcpro_edit_whatsapp_config', [
            'current_user' => get_current_user_id(),
        ]);
        echo json_encode($response);
    }
    public function uploadPatientReport(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_upload_patient_report', [
            'upload_data' => $request_data 
        ]);
        echo json_encode($response);

    }
    public function getPatientReport(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_get_patient_report', [
            'pid'=>$request_data['patinet']
        ]);
        echo json_encode($response);
    }
    public function viewPatientReport(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_view_patient_report', [
            'pid'=>$request_data['patient_id'],
            'docid'=>$request_data['doc_id']
        ]);
        echo json_encode($response);
    }
    public function deletePatientReport(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_delete_patient_report', [
            'report_id'=>$request_data['id'],
        ]);
        echo json_encode($response);
    }
    public function getUserClinic(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_get_user_clinic', [
            'requestData'=>$request_data
        ]);
        echo json_encode($response);
    }
    public function getJosnFile(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_get_json_file_data', [
            'fileUrl'=> $request_data['filePath'],
            'currentFile'=> $request_data['current']
        ]);
        echo json_encode($response);
    }
    public function saveJsonData(){
        $request_data = $this->request->getInputs();
        if(count($request_data['data']) == 0) {
            $upload_dir = wp_upload_dir();
            $dir_name = KIVI_CARE_PREFIX.'lang';
            $user_dirname = $upload_dir['basedir'] . '/' . $dir_name;
            $current_file = $user_dirname.'/temp.json';
            $request_data['data'] = json_decode(file_get_contents($current_file), TRUE);
        }
        $response = apply_filters('kcpro_save_json_file_data', [
            'jsonData'=> $request_data['data'],
            'filename'=>$request_data['file_name'],
            'langName'=>$request_data['langTitle']
        ]);
        echo json_encode($response);
    }
    public function unableSMSConfig(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_unable_sms_config', [
            'current_user' => get_current_user_id(),
            'status' =>$request_data['state']
        ]);
        echo json_encode($response);
    }
    public function getAllLang(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_get_all_lang', []);
        echo json_encode($response);
    }
    public function saveLocoTranslate(){
        $request_data = $this->request->getInputs();
        $status = update_option(KIVI_CARE_PREFIX.'locoTranslateState', $request_data['locoState']);
        if($status){
            $response = [
                'status' => true,
                'message' => esc_html__('Loco Translation Setting Saved Successfully', 'kc-lang'),
            ];
        }else{
            $response = [   
                'status' => false,
                'message' => esc_html__('Loco Translation Setting Update Failed', 'kc-lang'),
            ];
        }
        echo json_encode($response);
    }
    public function getLocoTranslate(){
        $request_data = $this->request->getInputs();
        $status = get_option(KIVI_CARE_PREFIX.'locoTranslateState', $request_data['locoState']);
        $response = [
            'status' => true,
            'data' => $status === 1 || $status === '1' ? 1 : 0,
            'message' => esc_html__('Loco Translation Setting data', 'kc-lang'),
        ];
        echo json_encode($response);
        die;
    }

    public function iUnderstand(){
        $request_data = $this->request->getInputs();
        $status = update_option(KIVI_CARE_PREFIX.'i_understnad_loco_translate', true);
        $response = [
            'status' => true,
            'data' => $status === 1 || $status === '1' ? 1 : 0,
            'message' => esc_html__('Loco Translation Setting data', 'kc-lang'),
        ];
        echo json_encode($response);
        die;
    }

    public function getiUnderstand() {
        $request_data = $this->request->getInputs();
        $status = get_option(KIVI_CARE_PREFIX.'i_understnad_loco_translate');
        if(!empty($status)) {
            $response = [
                'status' => true,
                'data' => $status === 1 || $status === '1' ? 1 : 0,
                'message' => esc_html__('Loco Translation Setting data', 'kc-lang'),
            ];
            echo json_encode($response);
            die;
        } else {
            $response = [
                'status' => false,
                'data' => $status === 1 || $status === '1' ? 1 : 0,
                'message' => esc_html__('Loco Translation Setting data', 'kc-lang'),
            ];
            echo json_encode($response);
            die;
        }
    }

    public function patientReportMail(){
        $request_data = $this->request->getInputs();
        $status = false;
        $message = esc_html__('Report Failed To Send', 'kc-lang');
        $default_email_template = [
            [
                'post_name' => KIVI_CARE_PREFIX.'patient_report',
                'post_content' => '<p> Welcome to KiviCare ,</p><p> Find your Report in attachment </p><p> Thank you. </p>',
                'post_title' => 'Patient Report',
                'post_type' => KIVI_CARE_PREFIX.'mail_tmp',
                'post_status' => 'publish',
            ],
        ];
        kcAddMailSmsPosts($default_email_template);

        if(isset($request_data['patient_id']) && !empty($request_data['patient_id']) ){

            global $wpdb;
              $patient_report = collect($wpdb->get_results("select * from ".$wpdb->prefix.'kc_patient_medical_report where patient_id='.$request_data['patient_id']))->pluck('upload_report')->toArray();
              if(!empty($patient_report) && count($patient_report) > 0){
                  $user_email = $wpdb->get_var('select user_email from '.$wpdb->prefix.'users where ID='.$request_data['patient_id']);
                  $patient_report = array_map(function ($v){
                      return get_attached_file($v);
                  },$patient_report);
                  $data = [
                      'user_email' => $user_email != null ? $user_email : '',
                      'attachment_file' => $patient_report,
                      'attachment' => true,
                      'email_template_type' => 'patient_report'
                  ];

                  $status = kcSendEmail($data);
                  $message = $status ? esc_html__('Report Send Successfully', 'kc-lang') : esc_html__('Report Failed To Send', 'kc-lang');
              }
        }
        $response = [
            'status' => $status,
            'message' => $message,
        ];
        echo json_encode($response);
    }
}
