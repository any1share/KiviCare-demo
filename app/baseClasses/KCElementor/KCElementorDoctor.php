<?php

use App\baseClasses\KCBase;

class KCElementorDoctor extends \Elementor\Widget_Base {

    public function get_name() {
        return 'kivicare-doctor';
    }

    public function get_title() {
        return __( 'Kivicare Doctor', 'kc-lang' );
    }

    public function get_icon() {
        return 'fa fa-user-md';
    }

    public function get_categories() {
        return [ 'kivicare-widget-category' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'iq_kivicare_section_shortcode',
            [
                'label' => __( 'Kivicare Doctor', 'kc-lang' ),
            ]
        );

        $this->add_control(
            'iq_kivivare_doctor_id',
            [
                'label' => __('Doctor', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => kcGetUserForElementor('all','doctor'),
                'default' => kcGetUserForElementor('first','doctor'),
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

//        $control = (array) $this;
//        $doctor_id = isset( $control["\0Elementor\Controls_Stack\0data"]['settings']['iq_kivivare_doctor_id'] ) ? $control["\0Elementor\Controls_Stack\0data"]['settings']['iq_kivivare_doctor_id'] : [];
        $this->add_control(
            'iq_kivivare_doctor_clinic_id',
            [
                'label' => __('Select Clinic', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => kcClinicForElementor('all'),
                'default' => kcClinicForElementor('first'),
                'dynamic' => [
                    'active' => true,
                ],
                'description' => esc_html__('selected Doctor', 'kc-lang' ),
                'label_block' => true
            ]
        );

        $this->add_control(
            'iq_kivivare_doctor_image',
            [
                'label' => esc_html__( 'Profile Image', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
            ]
        );

        $this->commonControl($this,'name','doctor');
        $this->commonControl($this,'number','doctor');
        $this->commonControl($this,'email','doctor');
        $this->commonControl($this,'address','doctor');
        $this->commonControl($this,'speciality','doctor');
        $this->commonControl($this,'session','doctor');
        $this->commonControl($this,'qualification','doctor');
        $this->commonControl($this,'description','doctor');

        $this->end_controls_section();

        $this->start_controls_section('iq_kivicare_card_style_sections',
            [
                'label' => esc_html__('card style', 'kc-lang'),
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
                'label' => esc_html__('Card Font Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                //'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .personalinfo' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('iq_kivicare_image_style_sections',
            [
                'label' => esc_html__('Image style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
                ],
            ]
        );


        $this->add_control(
            'iq_kivicare_doctor_image_height',
            [
                'label' => esc_html__('Image Height', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 200,
                'min' => 0,
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
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
                'default' => 250,
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
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
                'default' => 0,
                'min' => 0,
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
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
                'default' => 0,
                'min' => 0,
                'max' => 50,
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
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
                'default' => 'none',
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
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
                'default' => '#000000',
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'border-color: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();

        $this->fontStyleControl($this,'name');
        $this->fontStyleControl($this,'email');
        $this->fontStyleControl($this,'number');
        $this->fontStyleControl($this,'address');
        $this->fontStyleControl($this,'speciality');
        $this->fontStyleControl($this,'session');
        $this->fontStyleControl($this,'qualification');
        $this->fontStyleControl($this,'description');

//        $this->start_controls_section('iq_kivicare_book_button',
//            [
//                'label' => esc_html__('Book Appointment style', 'kc-lang'),
//                'tab' => \Elementor\Controls_Manager::TAB_STYLE
//            ]
//        );
//
//
//
//        $this->end_controls_section();


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

        $this_ele->add_control(
            'iq_kivivare_'. $userType .'_'.$type.'_label',
            [
                'label' => esc_html__( 'label ', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => $type === 'email' ? ucfirst($type).' ID' : ucfirst($type),
                'condition'=>[
                    'iq_kivivare_'. $userType .'_'.$type => 'yes'
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        if(in_array($type,['session','description','qualification','speciality'])){

            switch ($type){
                case 'session':
                    $icon = 'fa fa-clock-o';
                    break;
                case 'description':
                    $icon = 'fa fa-address-card-o';
                    break;
                case 'qualification':
                    $icon = 'fa fa-graduation-cap';
                    break;
                case 'speciality':
                    $icon = 'fa fa-user-md';
                    break;
            }

            $this_ele->add_control(
                'iq_kivivare_'. $userType .'_'.$type.'_icon',
                [
                    'label' => esc_html__( 'Icon', 'lc-lang' ),
                    'type' => \Elementor\Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'skin' => 'inline',
                    'default' => [
                        'value' => $icon,
                        'library' => 'fa-solid',
                    ],
                    'label_block' => false,
                ]
            );
        }
    }

    protected function fontStyleControl($this_ele,$type){
        $this_ele->start_controls_section('iq_kivicare_'.$type.'_style_sections',
            [
                'label' => esc_html__($type.' font style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'iq_kivivare_doctor_'.$type => 'yes'
                ],
            ]
        );

        $this_ele->add_control(
            'iq_kivicare_doctor_'.$type.'-label-color',
            [
                'label' => esc_html__('Label Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'condition'=>[
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                'default' => 'left',
                'options' => [
                    'left' => esc_html__('Left', 'kc-lang'),
                    'center' => esc_html__('Center', 'kc-lang'),
                    'right' => esc_html__('Right', 'kc-lang')
                ],
                'condition' => [
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                'default' => '#000000',
                'condition'=>[
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                    'iq_kivivare_doctor_'.$type => 'yes'
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
                'default' => 'left',
                'options' => [
                    'left' => esc_html__('Left', 'kc-lang'),
                    'center' => esc_html__('Center', 'kc-lang'),
                    'right' => esc_html__('Right', 'kc-lang')
                ],
                'condition' => [
                    'iq_kivivare_doctor_'.$type => 'yes'
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
        <style>
            .icon{
                /* display: flex; */
                /* align-items:center; */
            }
            .kivicare-doctor-card{
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
                max-width:auto;
                margin: auto;
                padding: 1.5rem;

            }
            /* .kivicare-doctor-profile{
                display: flex;
                flex-wrap: wrap;
            } */
            .kivicare-doctor-avtar{
                height: 150px;
                width: 250px;
                min-width: 250px;
                margin-right: 1rem;
            }
            .personalinfo{
                margin: 0px;
            }
            .content{
                margin-top: 1rem;
            }
            .session{
                /* display: flex; */
                /* flex-direction: column; */
                /* align-items: flex-start; */
                /* margin-left: 0.5rem; */
                width: 100%;
            }

            .list li {
                float: left;
                list-style-position: inside;
                list-style-type: disc;
                margin-right: 1em;
            }

            @media screen and (max-width: 600px) {
                .list li {
                    float: none;
                    list-style-position: outside;
                    margin: 0;
                }
                .kivicare-doctor-avtar{
                    width: 100%;
                }
            }
        </style>

        <?php
        if (!empty($setting['iq_kivivare_doctor_id']) && $setting['iq_kivivare_doctor_id'] == 'default') {
            ?>
            <div class="elementor-shortcode"> <?php echo _e('No Doctor Found', 'kc-lang'); ?></div>
            <?php
        } else {

            $id = $setting['iq_kivivare_doctor_id'];
            kcClinicForDoctorElementor($id);
            $kcbase = new KCBase();
            $active_domain =$kcbase->getAllActivePlugin();
            $clinic_id = $active_domain === $kcbase->kiviCareProOnName() ? $setting['iq_kivivare_doctor_clinic_id'] : kcGetDefaultClinicId();
            $image_attachment_id = get_user_meta($id,'doctor_profile_image',true);
            $user_info = get_userdata($id);
            $user_image_url = wp_get_attachment_url($image_attachment_id);
            $user_data = get_user_meta($id, 'basic_data', true);
            $user_data = json_decode($user_data);
            $first_name = get_user_meta($id, 'first_name', true);
            $last_name = get_user_meta($id, 'last_name', true);
            $description = get_user_meta($id, 'doctor_description', true);
            global $wpdb;
            $data =  $wpdb->get_row("SELECT ID FROM {$wpdb->posts} WHERE post_status='publish' and post_content LIKE '%[bookAppointment]%'", ARRAY_N);
            $appointmentPageUrl = '';
            if($data != null ){
                $appointmentPageUrl = get_permalink(isset($data[0]) ? $data[0] : 0  );
            }
            $query = parse_url($appointmentPageUrl, PHP_URL_QUERY);
            if ($query) {
                $appointmentPageUrl .= '&doctor_id=' . $id.'&clinic_id='.$clinic_id;
            } else {
                $appointmentPageUrl .= '?doctor_id=' . $id.'&clinic_id='.$clinic_id;
            }
            ?>
            <div ><?php ?>
                <div class="kivicare-doctor-card">
                    <div class="kivicare-doctor-profile">
                        <?php if($setting['iq_kivivare_doctor_image'] === 'yes'){ ?>
                        <div >
                            <img src="<?php echo $user_image_url; ?>"  class="kivicare-doctor-avtar">
                        </div>
                        <?php }?>
                        <div>
                            <?php if ($setting['iq_kivivare_doctor_name'] === 'yes') { ?>
                                <h4 class="iq_kivicare_doctor_name-label"><?php echo $setting['iq_kivivare_doctor_name_label']; ?></h4>
                                <p class="iq_kivicare_doctor_name-value"><?php echo $first_name .' '. $last_name;?></p>
                                <?php
                            }
                            if ($setting['iq_kivivare_doctor_email'] === 'yes') {
                                ?>
                                <h4 class="iq_kivicare_doctor_email-label"><?php echo $setting['iq_kivivare_doctor_email_label']; ?></h4>
                                <p class="iq_kivicare_doctor_email-value"><?php echo !empty($user_info->user_email) ? $user_info->user_email : '' ;?></p>
                                <?php
                            }
                            if ($setting['iq_kivivare_doctor_number'] === 'yes') {
                                ?>
                                <h4 class="iq_kivicare_doctor_number-label"><?php echo $setting['iq_kivivare_doctor_number_label']; ?></h4>
                                <p class="iq_kivicare_doctor_number-value"><?php echo !empty($user_data->mobile_number) ? $user_data->mobile_number : '' ; ?></p>
                                <?php
                            }
                            if ($setting['iq_kivivare_doctor_address'] === 'yes') {
                                ?>
                                <h4 class="iq_kivicare_doctor_address-label"><?php echo $setting['iq_kivivare_doctor_address_label']; ?></h4>
                                <p class="iq_kivicare_doctor_address-value"><?php echo !empty($user_data->address) ? $user_data->address : ''  ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <div>
                      <?php if($setting['iq_kivivare_doctor_speciality'] === 'yes'){?>
                        <div>
                            <div class="icon">
<!--                                --><?php //\Elementor\Icons_Manager::render_icon( $setting['iq_kivivare_doctor_speciality_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                <div class="session">
                                    <h4 class="iq_kivicare_doctor_speciality-label"><?php echo $setting['iq_kivivare_doctor_speciality_label']; ?></h4>
                                    <div>
                                        <ul class="list">
                                            <?php if(!empty($user_data->specialties) && is_array($user_data->specialties)){
                                                foreach ($user_data->specialties as $special){?>
                                                     <li class="iq_kivicare_doctor_speciality-value"><?php echo !empty($special->label) ? $special->label : '' ; ?></li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                           </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php }
                            if ($setting['iq_kivivare_doctor_session'] === 'yes') { ?>
                            <div>
                                <div class="icon">
<!--                                    --><?php //\Elementor\Icons_Manager::render_icon( $setting['iq_kivivare_doctor_session_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                    <div class="session">
                                        <h4 class="iq_kivicare_doctor_session-label"><?php echo $setting['iq_kivivare_doctor_session_label']; ?></h4>
                                        <p class="iq_kivicare_doctor_session-value">Mon, Tues, Wed, Thus, Fri, Sat, Sun</p>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        if ($setting['iq_kivivare_doctor_qualification'] === 'yes') {
                            ?>
                            <div>
                                <div class="icon">
                                    <div class="session">
<!--                                        --><?php //\Elementor\Icons_Manager::render_icon( $setting['iq_kivivare_doctor_qualification_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                        <h4 class="iq_kivicare_doctor_qualification-label"><?php echo $setting['iq_kivivare_doctor_qualification_label']; ?></h4>
                                        <?php if(!empty($user_data->qualifications) && is_array($user_data->qualifications) ){
                                            foreach ($user_data->qualifications as $qual){
                                            ?>
                                        <p class="iq_kivicare_doctor_qualification-value"><?php echo $qual->university; ?></p>
                                        <p class="iq_kivicare_doctor_qualification-value"> <?php echo $qual->degree; ?></p>
                                        <p class="iq_kivicare_doctor_qualification-value"><?php echo esc_html__('Year:-','kc-lang').' ' .$qual->year; ?></p>
                                      <?php }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        if ($setting['iq_kivivare_doctor_description'] === 'yes') {
                            ?>
                            <div>
                                <div class="icon">
                                    <div class="session">
                                        <h4 class="iq_kivicare_doctor_description-label"><?php echo $setting['iq_kivivare_doctor_description_label']; ?></h4>
                                        <p class="iq_kivicare_doctor_description-value"><?php echo $description; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div>
                        <a href="<?php echo $appointmentPageUrl ;?>" target="_blank" >
                            <input type="button" value="Book Appointment"  style="color: black;" >
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }

    }
}

// Register widget
// \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \KCElementor() );
\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \KCElementorDoctor() );
