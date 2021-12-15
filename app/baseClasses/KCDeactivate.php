<?php


namespace App\baseClasses;

class KCDeactivate {

	public static function init () {
        wp_clear_scheduled_hook("kivicare_patient_appointment_reminder");
    }
}