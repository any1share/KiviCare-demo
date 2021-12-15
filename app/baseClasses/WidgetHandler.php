<?php

namespace App\baseClasses;

class WidgetHandler extends KCBase {

	public function init() {
        add_shortcode('bookAppointment', [$this, 'bookAppointmentWidget']);
        add_shortcode('patientDashboard', [$this, 'patientDashboardWidget']);
    }

    public function bookAppointmentWidget ($param) {
        $doctor_id = 0;
        $clinic_id = 0;
        $service_id = -1;
        if(isset($param['doctor_id']) && !empty($param['doctor_id'])){
            $doctor_id = isset($param['doctor_id']) ? $param['doctor_id'] : 0;
        }elseif(isset($_GET['doctor_id']) && !empty($_GET['doctor_id'])){
            $doctor_id = $_GET['doctor_id'];
        }

        if(isset($param['clinic_id']) && !empty($param['clinic_id'])){
            $clinic_id = isset($param['clinic_id']) ? $param['clinic_id'] : 0;
        }elseif(isset($_GET['clinic_id']) && !empty($_GET['clinic_id'])){
            $clinic_id = $_GET['clinic_id'];
        }

        if(isset($param['service_id']) && !empty($param['service_id'])){
            $service_id = isset($param['service_id']) ? $param['service_id'] : -1;
        }
        if(is_user_logged_in()) {
            $user_id = get_current_user_id();
        } else {
            $user_id = 0;
        }
        ob_start();
        echo "<div id='app' class='kivi-care-appointment-booking-container kivi-widget' >
        <book-appointment-widget v-bind:user_id='$user_id' v-bind:doctor_id='$doctor_id' v-bind:clinic_id='$clinic_id' v-bind:service_id='$service_id' >
        </book-appointment-widget></div>";
        return ob_get_clean();
    }

    public function patientDashboardWidget () {
        ob_start();
        echo "<div id='app' class='kivi-care-patient-dashboard-container kivi-widget' ><patient-dashboard-widget></patient-dashboard-widget></div>";
        return ob_get_clean();
    }

}


