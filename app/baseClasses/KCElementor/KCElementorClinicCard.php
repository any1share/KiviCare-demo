<?php

use App\baseClasses\KCBase;

class KCElementorClinicCard extends \Elementor\Widget_Base {

    public function get_name() {
        return 'kivicare-clinic-card';
    }

    public function get_title() {
        return __( 'Kivicare Clinic List', 'kc-lang' );
    }

    public function get_icon() {
        return 'fa fa-user-md';
    }

    public function get_categories() {
        return [ 'kivicare-widget-category' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'iq_kivicare_clinic_card_shortcode',
            [
                'label' => __( 'Kivicare Clinic Card', 'kc-lang' ),
            ]
        );

        $this->add_control(
            'iq_kivivare_clinic_per_page',
            [
                'label' => __('Clinic per page ', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 2,
                'min' => 0
            ]
        );


        $this->add_control(
            'iq_kivivare_clinic_gap_between_card',
            [
                'label' => esc_html__( 'Hide Space Between Doctors', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
            ]
        );

        $this->add_control(
            'iq_kivivare_clinic_image',
            [
                'label' => esc_html__( 'Profile Image', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
            ]
        );
        
        $this->commonControl($this,'name','clinic');
        $this->commonControl($this,'speciality','clinic');
        $this->commonControl($this,'number','clinic');
        $this->commonControl($this,'email','clinic');
        $this->commonControl($this,'address','clinic');

        $this->commonControl($this,'administrator','clinic');
        $this->commonControl($this,'admin_number','clinic');
        $this->commonControl($this,'admin_email','clinic');

        $this->end_controls_section();
        
        $this->start_controls_section('iq_kivicare_card_style_sections',
            [
                'label' => esc_html__('Card style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'iq_card_background',
                'label' => esc_html__('Card Background', 'kc-lang'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .kivicare-doctor-card',
            ]
        );
    
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'iq_card_box_shadow',
                'label' => esc_html__('Card Box Shadow', 'kc-lang'),
                'selector' => '{{WRAPPER}} .kivicare-doctor-card',
            ]
        );
    
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'iq_card_border',
                'label' => esc_html__('Card Border', 'kc-lang'),
                'selector' => '{{WRAPPER}} .kivicare-doctor-card',
            ]
        );
    
        $this->add_control(
            'iq_card_border_radius',
            [
                'label' => esc_html__('Card Border Radius', 'kc-lang'),
                'size_units' => ['px', '%', 'em'],
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};overflow:hidden;',
                ],
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_label_color',
            [
                'label' => esc_html__('Left Side Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .column::before' => 'background-color: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('iq_kivicare_image_style_sections',
            [
                'label' => esc_html__('Image style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'iq_kivivare_clinic_image' => 'yes'
                ],
            ]
        );


        $this->add_control(
            'iq_kivicare_doctor_image_height',
            [
                'label' => esc_html__('Image Height', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_clinic_image' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'height: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_image_width',
            [
                'label' => esc_html__('Image width', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_clinic_image' => 'yes'
                ],
                'min' => 0,
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'width: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_image_border',
            [
                'label' => esc_html__('Image Border', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_clinic_image' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'border: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_image_border_radius',
            [
                'label' => esc_html__('Image Border Radius', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_clinic_image' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'border-radius: {{VALUE}}%;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_image_border_style',
            [
                'label' => esc_html__('Image Border style', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'condition'=>[
                    'iq_kivivare_clinic_image' => 'yes'
                ],
                'options' => [
                    'solid' => esc_html__('solid', 'kc-lang'),
                    'dashed' => esc_html__('dashed', 'kc-lang'),
                    'dotted' => esc_html__('dotted', 'kc-lang'),
                    'double' => esc_html__('double', 'kc-lang'),
                    'groove' => esc_html__('groove', 'kc-lang'),
                    'ridge' => esc_html__('ridge', 'kc-lang'),
                    'inset' => esc_html__('inset', 'kc-lang'),
                    'outset' => esc_html__('outset', 'kc-lang'),
                    'none' => esc_html__('none', 'kc-lang'),
                    'hidden' => esc_html__('hidden', 'kc-lang'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'border-style: {{VALUE}};',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_image_border_color',
            [
                'label' => esc_html__('Image Border Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition'=>[
                    'iq_kivivare_clinic_image' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'border-color: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();

        $this->fontStyleControl($this,'name');
        $this->fontStyleControl($this,'speciality');
        $this->fontStyleControl($this,'number');
        $this->fontStyleControl($this,'email');
        $this->fontStyleControl($this,'address');
        // $this->fontStyleControl($this,'administrator');
        $this->fontStyleControl($this,'admin_number');
        $this->fontStyleControl($this,'admin_email');

        $this->start_controls_section('iq_kivicare_administrator_style_sections',
            [
                'label' => esc_html__(ucfirst('administrator').' style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'iq_kivivare_clinic_administrator' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_administrator-label-color',
            [
                'label' => esc_html__('Label Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition'=>[
                    'iq_kivivare_clinic_administrator' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_administrator-label' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'iq_kivicare_doctor_administrator-label-typography',
                'label' => esc_html__('Label Typography', 'kc-lang'),
                'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .iq_kivicare_doctor_administrator-label',
                'condition' => [
                    'iq_kivivare_clinic_administrator' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_administrator-label-margin',
            [
                'label' => esc_html__('Label Margin', 'kc-lang'),
                'size_units' => ['px', '%', 'em'],
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'condition' => [
                    'iq_kivivare_clinic_administrator' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_administrator-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_administrator-label-padding',
            [
                'label' => esc_html__('Label Padding', 'kc-lang'),
                'size_units' => ['px', '%', 'em'],
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'condition' => [
                    'iq_kivivare_clinic_administrator' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_administrator-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_administrator-label-align',
            [
                'label' => esc_html__('Label Alignment', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'left' => esc_html__('Left', 'kc-lang'),
                    'center' => esc_html__('Center', 'kc-lang'),
                    'right' => esc_html__('Right', 'kc-lang')
                ],
                'condition' => [
                    'iq_kivivare_clinic_administrator' => 'yes'
                ],    
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_administrator-label' => 'text-align: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('iq_kivicare_book_appointment_button',
            [
                'label' => esc_html__('Book appointment Button style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    // 'iq_kivivare_doctor_session' => 'yes'
                ],
            ]
        );
        kcElementorAllCommonController($this,'clinic_clinic');

        $this->end_controls_section();
        
    }
    protected function commonControl($this_ele,$type,$userType){
        $this_ele->add_control(
            'iq_kivivare_'. $userType .'_'.$type,
            [
                'label' => esc_html__( $type === 'email' ? ucfirst($type).' ID' : ucfirst($type), 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
            ]
        );

        $value = '';
        switch($type){
            case 'name':
                case 'speciality' : 
                $value = '';
                break;
            case 'email': 
                $value =  ucfirst($type).' ID';
                break;
            case 'session': 
                $value =  'Schedule Appointment';
                break;
            case 'number':
                case 'admin_number': 
                $value =  'Contact No';
                break;
            case 'admin_email': 
                $value =  ucfirst('email').' ID';
                break;
            default:
                $value = ucfirst($type);
                break;
        }

        $this_ele->add_control(
            'iq_kivivare_'. $userType .'_'.$type.'_label',
            [
                'label' => esc_html__( 'label ', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => $value,
                'condition'=>[
                    'iq_kivivare_'. $userType .'_'.$type => 'yes'
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
    }

    protected function fontStyleControl($this_ele,$type){
        // $type = $userType === 'clinic' ? $type : $type.'_clinic';
        $this_ele->start_controls_section('iq_kivicare_'.$type.'_style_sections',
            [
                'label' => esc_html__(ucfirst($type).' style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-label-color',
            [
                'label' => esc_html__('Label Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition'=>[
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-label' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this_ele->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'iq_kivicare_doctor_'.$type.'-label-typography',
                'label' => esc_html__('Label Typography', 'kc-lang'),
                'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-label',
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ]
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-label-margin',
            [
                'label' => esc_html__('Label Margin', 'kc-lang'),
                'size_units' => ['px', '%', 'em'],
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-label-padding',
            [
                'label' => esc_html__('Label Padding', 'kc-lang'),
                'size_units' => ['px', '%', 'em'],
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-label-align',
            [
                'label' => esc_html__('Label Alignment', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'left' => esc_html__('Left', 'kc-lang'),
                    'center' => esc_html__('Center', 'kc-lang'),
                    'right' => esc_html__('Right', 'kc-lang')
                ],
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],    
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-label' => 'text-align: {{VALUE}};',
                ]
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-value-color',
            [
                'label' => esc_html__('Value Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition'=>[
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-value' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this_ele->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'iq_kivicare_doctor_'.$type.'-value-typography',
                'label' => esc_html__('Value Typography', 'kc-lang'),
                'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-value',
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ]
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-value-margin',
            [
                'label' => esc_html__('Value Margin', 'kc-lang'),
                'size_units' => ['px', '%', 'em'],
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-value' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-value-padding',
            [
                'label' => esc_html__('Value Padding', 'kc-lang'),
                'size_units' => ['px', '%', 'em'],
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-value' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-value-align',
            [
                'label' => esc_html__('Value Alignment', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'left' => esc_html__('Left', 'kc-lang'),
                    'center' => esc_html__('Center', 'kc-lang'),
                    'right' => esc_html__('Right', 'kc-lang')
                ],
                'condition' => [
                    'iq_kivivare_clinic_'.$type => 'yes'
                ],    
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_'.$type.'-value' => 'text-align: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $setting = $this->get_settings_for_display();
        ?>
        <?php
        if (count(kcClinicListElementor(0,5)) == 0) {
            ?>
            <div class="elementor-shortcode"> <?php echo _e('No Clinic Found', 'kc-lang'); ?></div>
            <?php
        } else {

            $kcbase = new KCBase();
            $active_domain =$kcbase->getAllActivePlugin();
            $perPage = $setting['iq_kivivare_clinic_per_page'] != null ? $setting['iq_kivivare_clinic_per_page'] : 1 ;
            $pageUrl=$pageUrl1 = get_permalink(get_the_ID() );
            $pageNo = 0;
            if(isset($_GET['clinic_page'])){
                $pageNo = $_GET['clinic_page'] ;
            }
            
            $query2 = parse_url($pageUrl, PHP_URL_QUERY);
            if ($query2) {
                $pageUrl .= '&clinic_page=';
                $pageUrl .=  $pageNo + 1;
            } else {
                $pageUrl .= '?clinic_page=' ;
                $pageUrl .=  $pageNo + 1;
            }
            
            $query3 = parse_url($pageUrl1, PHP_URL_QUERY);
            if ($query3) {
                $pageUrl1 .= '&clinic_page=';
                $pageUrl1 .= $pageNo - 1;
            } else {
                $pageUrl1 .= '?clinic_page=';
                $pageUrl1 .= $pageNo - 1;
            }
            
            $clinics = kcClinicListElementor($pageNo,$perPage);
            $nextPageClinicCount = count(kcClinicListElementor($pageNo+1,$perPage));
             ?>
             <div>
                 <?php
            foreach ($clinics as $key => $clinic){
                $id = $clinic->id;
                $image_attachment_id = $clinic->profile_image;
                $user_info = get_userdata($id);
                $user_image_url = wp_get_attachment_url($image_attachment_id);
                $clinic_name = $clinic->name;
                $clinic_admin_data = get_user_meta($clinic->clinic_admin_id, 'basic_data', true);
                $clinic_admin_data = json_decode($clinic_admin_data);

                $appointmentPageUrl = kcGetAppointmentPageUrl();
                $query = parse_url($appointmentPageUrl, PHP_URL_QUERY);
                if ($query) {
                    $appointmentPageUrl .= '&clinic_id=' . $id;
                } else {
                    $appointmentPageUrl .= '?clinic_id=' . $id;
                }
                ?>
                <div class="kivicare-doctor-card body">
                <div class="column">
                    <div class="image">
                        <?php if($setting['iq_kivivare_clinic_image'] === 'yes'){ ?>
                            <img src="<?php echo !empty($user_image_url) ? $user_image_url : KIVI_CARE_DIR_URI.'/assets/images/doctor-avatar-3.jpeg' ; ?>"  class="img kivicare-doctor-avtar">
                        <?php }?>
                    </div>
                    <div class="details">
                            <?php if ($setting['iq_kivivare_clinic_name'] === 'yes') { ?>
                                <div class="header">
                                    <div class="">
                                        <h1 class="iq_kivicare_doctor_name-label heading1"><?php echo $setting['iq_kivivare_clinic_name_label']; ?></h1>
                                        <h1 class="iq_kivicare_doctor_name-value heading1"><?php echo $clinic_name;?></h1>
                                    </div>
                                    <div class="">
                                    <?php if($setting['iq_kivivare_clinic_speciality'] === 'yes'){?>
                                        <h4 class="heading4 iq_kivicare_doctor_speciality-label"><?php echo $setting['iq_kivivare_clinic_speciality_label']; ?></h4>
                                            <?php 
                                            if(!empty($clinic->specialties) && is_array(json_decode($clinic->specialties))){
                                                    ?>
                                                     <h4 class="heading4 iq_kivicare_doctor_speciality-value"><?php echo  collect(json_decode($clinic->specialties))->pluck('label')->implode(', '); ?></h4>
                                                    <?php
                                                }
                                                ?>
                                        <?php 
                                    } 
                                    ?>
                                            </div>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_clinic_number'] === 'yes') {
                                ?>
                                <div class="flex-container numup">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_number-label heading3"><?php echo $setting['iq_kivivare_clinic_number_label']; ?></h3>
                                    </div>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_number-value paragraph"><?php echo !empty($clinic->telephone_no) ? $clinic->telephone_no : '' ; ?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_clinic_email'] === 'yes') {
                                ?>
                                <div class="flex-container">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_email-label heading3"><?php echo $setting['iq_kivivare_clinic_email_label']; ?></h3>
                                    </div>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_email-value paragraph"><?php echo !empty($clinic->email) ? $clinic->email : '' ;?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_clinic_address'] === 'yes') {
                                ?>
                                <div class="flex-container clinic-address">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_address-label heading3"><?php echo $setting['iq_kivivare_clinic_address_label']; ?></h3>
                                    </div>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_address-value paragraph"><?php echo (!empty($clinic->address) ? $clinic->address : '') ;?></p>
                                        <p class="iq_kivicare_doctor_address-value paragraph"><?php echo (!empty($clinic->city) ? $clinic->city : '') .(!empty($clinic->city) ? ', ':''). (!empty($clinic->country) ? $clinic->country : '') .(!empty($clinic->country) ? ', ':'') ;?></p>
                                        <p class="iq_kivicare_doctor_address-value paragraph"><?php echo (!empty($clinic->postal_code) ? $clinic->postal_code : '') ;?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_clinic_administrator'] === 'yes') {
                                ?>
                                <div class="appoin">
                                    <span class="schedule iq_kivicare_doctor_session-label iq_kivicare_doctor_administrator-label"><?php echo $setting['iq_kivivare_clinic_administrator_label']; ?></span>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_clinic_admin_number'] === 'yes') {
                                ?>
                                <div class="flex-container numup">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_admin_number-label heading3"><?php echo $setting['iq_kivivare_clinic_admin_number_label']; ?></h3>
                                    </div>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_admin_number-value paragraph"><?php echo !empty($clinic_admin_data->mobile_number) ? $clinic_admin_data->mobile_number : '' ; ?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_clinic_admin_email'] === 'yes') {
                                ?>
                                <div class="flex-container">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_admin_email-label heading3"><?php echo $setting['iq_kivivare_clinic_admin_email_label']; ?></h3>
                                    </div>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_admin_email-value paragraph"><?php echo !empty($clinic_admin_data->user_email) ? $clinic_admin_data->user_email : '' ;?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        <div class="book">
                            <a href="<?php echo $appointmentPageUrl ;?>" target="_blank" >
                                <button type="button" class="book_button appointment_button" >Book Appointment</button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                <?php echo $setting['iq_kivivare_clinic_gap_between_card'] === 'yes' ? '' : "<br>" ; ?>
        <?php
            }?>
            <div class="kivi-pagination">
                    <a  href="<?php echo $pageUrl1 ;?>"  >
                        <input  style="<?php echo $pageNo > 0 || !kcNotInPreviewmode() ? '' : 'display:none;' ;?>" class="iq_kivicare_next_previous book_button" type="button" name="next" value="<?php echo esc_html__('Previous','kc-lang') ?>">
                    </a>
                <a href="<?php echo $pageUrl;?>" >
                    <input style="<?php echo $nextPageClinicCount > 0 || !kcNotInPreviewmode() ? '' : 'display:none;' ; ?>" class="iq_kivicare_next_previous book_button" type="button" name="next" value="<?php echo esc_html__('Next','kc-lang') ?>">
                </a>
            </div>
        </div>
            <?php 
        }
}

}
// Register widget
\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \KCElementorClinicCard() );