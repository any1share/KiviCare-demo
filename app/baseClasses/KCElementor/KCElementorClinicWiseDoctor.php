<?php

use App\baseClasses\KCBase;

class KCElementorClinicWiseDoctor extends \Elementor\Widget_Base {
    public function get_name() {
        return 'kivicare-clinic-wise-doctor';
    }

    public function get_title() {
        return __( 'Kivicare Doctor List', 'kc-lang' );
    }

    public function get_icon() {
        return 'fas fa-hospital';
    }

    public function get_categories() {
        return [ 'kivicare-widget-category' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'iq_kivicare_clinic_wise_doctor_section_shortcode',
            [
                'label' => __( 'Kivicare Clinic Wise Doctor', 'kc-lang' ),
            ]
        );
        
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
                'label_block' => true
            ]
        );

        $this->add_control(
            'iq_kivivare_doctor_par_page',
            [
                'label' => __('Doctor per page ', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 0
            ]
        );

        $this->add_control(
            'iq_kivivare_doctor_gap_between_card',
            [
                'label' => esc_html__( 'Hide Space Between Doctors', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
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
        $this->commonControl($this,'speciality','doctor');
        $this->commonControl($this,'number','doctor');
        $this->commonControl($this,'email','doctor');
        $this->commonControl($this,'qualification','doctor');
        $this->commonControl($this,'session','doctor');

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
                    '{{WRAPPER}} .column-doctor::before' => 'background-color: {{VALUE}};',
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
                'condition'=>[
                    'iq_kivivare_doctor_image' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .kivicare-doctor-avtar' => 'border-color: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();
        $userType = 'doctor';
        $this->fontStyleControl($this,'name',$userType);
        $this->fontStyleControl($this,'speciality',$userType);
        $this->fontStyleControl($this,'number',$userType);
        $this->fontStyleControl($this,'email',$userType);
        $this->fontStyleControl($this,'qualification',$userType);
        $this->fontStyleControl($this,'session',$userType);

        $this->start_controls_section('iq_kivicare_session_container_style_sections',
            [
                'label' => esc_html__('Session Container style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_container_height',
            [
                'label' => esc_html__('Container Height', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-container' => 'height: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_cell_height',
            [
                'label' => esc_html__('Cell Height', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell' => 'height: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_cell_width',
            [
                'label' => esc_html__('Cell width', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell' => 'width: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'iq_kivicare_doctor_session_cell_background',
                'label' => esc_html__('Cell Background', 'kc-lang'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .iq_kivicare_doctor_session-cell',
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_cell_border',
            [
                'label' => esc_html__('Cell Border', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell' => 'border: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_cell_border_radius',
            [
                'label' => esc_html__('Cell Border Radius', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell' => 'border-radius: {{VALUE}}%;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_cell_border_style',
            [
                'label' => esc_html__('Cell Border style', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
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
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell' => 'border-style: {{VALUE}};',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_cell_border_color',
            [
                'label' => esc_html__('Cell Border Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell' => 'border-color: {{VALUE}};',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_font_title_color',
            [
                'label' => esc_html__('Title Font Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell_title' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'iq_kivicare_doctor_session_font_title_typography',
                'label' => esc_html__('Title Font Typography', 'kc-lang'),
                'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .iq_kivicare_doctor_session-cell_title',
                'condition' => [
                    'iq_kivivare_doctor_session' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_doctor_session_font_value_color',
            [
                'label' => esc_html__('Font Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition'=>[
                    'iq_kivivare_doctor_session' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iq_kivicare_doctor_session-cell_value' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'iq_kivicare_doctor_session_font_value_typography',
                'label' => esc_html__('Font Typography', 'kc-lang'),
                'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .iq_kivicare_doctor_session-cell_value',
                'condition' => [
                    'iq_kivivare_doctor_session' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('iq_kivicare_book_appointment_button',
            [
                'label' => esc_html__('Appointment Book Button style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition'=>[
                    // 'iq_kivivare_doctor_session' => 'yes'
                ],
            ]
        );
        kcElementorAllCommonController($this,'clinic_doctor');

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
                    $value =  'Contact No';
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

        protected function fontStyleControl($this_ele,$type,$userType){
            $type = $userType === 'doctor' ? $type : $type.'_clinic';
            $this_ele->start_controls_section('iq_kivicare_'.$type.'_style_sections',
                [
                    'label' => esc_html__(ucfirst($type).' style', 'kc-lang'),
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
        <?php
        if (!empty($setting['iq_kivivare_doctor_id']) && $setting['iq_kivivare_doctor_id'] == 'default') {
            ?>
            <div class="elementor-shortcode"> <?php echo _e('No Doctor Found', 'kc-lang'); ?></div>
            <?php
        } else {

            $kcbase = new KCBase();
            $active_domain =$kcbase->getAllActivePlugin();
            $clinic_id = $active_domain === $kcbase->kiviCareProOnName() ? $setting['iq_kivivare_doctor_clinic_id'] : kcGetDefaultClinicId();
            $perPage = $setting['iq_kivivare_doctor_par_page'] != null ? $setting['iq_kivivare_doctor_par_page'] : 1 ;
            $pageUrl = $pageUrl1 = get_permalink(get_the_ID());
            $pageNo = 0;
            if(isset($_GET['doctor_page'])){
                $pageNo = $_GET['doctor_page'] ;
            }
            
            $query2 = parse_url($pageUrl, PHP_URL_QUERY);
            if ($query2) {
                $pageUrl .= '&doctor_page=';
                $pageUrl .=  $pageNo + 1;
            } else {
                $pageUrl .= '?doctor_page=' ;
                $pageUrl .=  $pageNo + 1;
            }
            
            $query3 = parse_url($pageUrl1, PHP_URL_QUERY);
            if ($query3) {
                $pageUrl1 .= '&doctor_page=';
                $pageUrl1 .= $pageNo - 1;
            } else {
                $pageUrl1 .= '?doctor_page=';
                $pageUrl1 .= $pageNo - 1;
            }
            
            $doctors = kcDoctorForClinicElementor($clinic_id,$pageNo,$perPage);
            
            $nextPageDoctorCount = count(kcDoctorForClinicElementor($clinic_id,$pageNo+1,$perPage));
             ?>
             <div>
                 <?php
            foreach ($doctors as $key => $doctor){
                $id = $doctor->doctor_id;
                $doctors_sessions = doctorWeeklyAvailability(['clinic_id'=>$clinic_id,'doctor_id'=>$id]);
                $image_attachment_id = get_user_meta($id,'doctor_profile_image',true);
                $user_info = get_userdata($id);
                $user_image_url = wp_get_attachment_url($image_attachment_id);
                $user_data = get_user_meta($id, 'basic_data', true);
                $user_data = json_decode($user_data);
                $first_name = get_user_meta($id, 'first_name', true);
                $last_name = get_user_meta($id, 'last_name', true);
                $description = get_user_meta($id, 'doctor_description', true);
                $appointmentPageUrl = kcGetAppointmentPageUrl();
                $query = parse_url($appointmentPageUrl, PHP_URL_QUERY);
                if ($query) {
                    $appointmentPageUrl .= '&doctor_id=' . $id.'&clinic_id='.$clinic_id;
                } else {
                    $appointmentPageUrl .= '?doctor_id=' . $id.'&clinic_id='.$clinic_id;
                }
                ?>
                <div class="kivicare-doctor-card body">
                <div class="column-doctor">
                    <div class="image">
                        <?php if($setting['iq_kivivare_doctor_image'] === 'yes'){ ?>
                            <img src="<?php echo !empty($user_image_url) ? $user_image_url : KIVI_CARE_DIR_URI.'/assets/images/doctor-avatar-3.jpeg' ; ?>"  class="img kivicare-doctor-avtar">
                        <?php }?>
                    </div>
                    <div class="details">
                            <?php if ($setting['iq_kivivare_doctor_name'] === 'yes') { ?>
                                <div class="header">
                                    <div>
                                        <h1 class="iq_kivicare_doctor_name-label heading1"><?php echo $setting['iq_kivivare_doctor_name_label']; ?></h1>
                                        <h1 class="iq_kivicare_doctor_name-value heading1"><?php echo $first_name .' '. $last_name;?></h1>
                                    </div>
                                    <div>
                                    <?php if($setting['iq_kivivare_doctor_speciality'] === 'yes'){?>
                                        <h4 class="heading4 iq_kivicare_doctor_speciality-label"><?php echo $setting['iq_kivivare_doctor_speciality_label']; ?></h4>
                                            <?php if(!empty($user_data->specialties) && is_array($user_data->specialties)){
                                                    ?>
                                                     <h4 class="heading4 iq_kivicare_doctor_speciality-value"><?php echo  collect($user_data->specialties)->pluck('label')->implode(', ') ; ?></h4>
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
                            if ($setting['iq_kivivare_doctor_number'] === 'yes') {
                                ?>
                                <div class="flex-container numup">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_number-label heading3"><?php echo $setting['iq_kivivare_doctor_number_label']; ?></h3>
                                    </div>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_number-value paragraph"><?php echo !empty($user_data->mobile_number) ? $user_data->mobile_number : '' ; ?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_doctor_email'] === 'yes') {
                                ?>
                                <div class="flex-container">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_email-label heading3"><?php echo $setting['iq_kivivare_doctor_email_label']; ?></h3>
                                    </div>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_email-value paragraph"><?php echo !empty($user_info->user_email) ? $user_info->user_email : '' ;?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            if ($setting['iq_kivivare_doctor_qualification'] === 'yes') {
                                ?>
                                <div class="flex-container">
                                    <div class="detail-header">
                                        <h3 class="iq_kivicare_doctor_qualification-label heading3"><?php echo $setting['iq_kivivare_doctor_qualification_label']; ?></h3>
                                    </div>
                                    <?php if(!empty($user_data->qualifications) && is_array($user_data->qualifications) ){
                                        foreach ($user_data->qualifications as $qual){
                                    ?>
                                    <div class="detail-data">
                                        <p class="iq_kivicare_doctor_qualification-value paragraph"><?php echo $qual->degree.', ' .$qual->university.', '.$qual->year; ?></p>
                                    </div>
                                    <?php }
                                        } ?>
                                </div>
                            <?php 
                            } 
                            ?>
                    <div class="">
                        <?php
                            if ($setting['iq_kivivare_doctor_session'] === 'yes') { ?>
                            <div class="appoin">
                                <span class="schedule iq_kivicare_doctor_session-label"><?php echo $setting['iq_kivivare_doctor_session_label']; ?></span>
                            </div>
                            <div class="grid-container iq_kivicare_doctor_session-container">
                                <?php 
                                    if(!empty($doctors_sessions) && is_array($doctors_sessions) && count($doctors_sessions) > 0){
                                        foreach($doctors_sessions as $key => $value){
                                ?>
                                <div class="iq_kivicare_doctor_session-cell">
                                    <p class="paragraph iq_kivicare_doctor_session-cell_title day"><?php echo isset($value[0]['day']) ? ucfirst($value[0]['day']) : ''  ?></p>
                                    <?php if(isset($value[0]['start_time'])){ ?>
                                        <p class="paragraph iq_kivicare_doctor_session-cell_value time">
                                            <?php echo (isset($value[0]['start_time']) ? esc_html__('Morning : ','kc-lang').date('H:i ' ,strtotime($value[0]['start_time'])) : '' ) .' - '. (isset($value[0]['end_time']) ? date('H:i ' ,strtotime($value[0]['end_time'])) : '')?>
                                        </p>
                                    <?php } 
                                    if(isset($value[1]['start_time'])){ ?> 
                                        <p class="paragraph iq_kivicare_doctor_session-cell_value time">
                                            <?php echo (isset($value[1]['start_time']) ? esc_html__('Evening : ','kc-lang').date('H:i ' ,strtotime($value[1]['start_time'])) : '' ) .' - '. (isset($value[1]['end_time']) ? date('H:i ' ,strtotime($value[1]['end_time'])) : '')?>
                                        </p>
                                    <?php } ?>
                                </div>
                                <?php 
                                } }else{
                                    $weekdays = array(
                                        'Mon' => esc_html__('Mon','kc-lang'),
                                        'Tue'=> esc_html__('Tue','kc-lang'),
                                        'Wed'=> esc_html__('Wed','kc-lang'),
                                        'Thu'=> esc_html__('Thu','kc-lang'),
                                        'Fri'=> esc_html__('Fri','kc-lang'),
                                        'Sat'=> esc_html__('Sat','kc-lang'),
                                        'Sun'=> esc_html__('Sun','kc-lang')
                                    );
                                    foreach($weekdays as $days){
                                        ?>
                                            <div class="iq_kivicare_doctor_session-cell">
                                                <p class="paragraph iq_kivicare_doctor_session-cell_title day"><?php echo $days; ?></p>
                                                <p class="paragraph iq_kivicare_doctor_session-cell_value time"><?php echo  esc_html__('Morning : ','kc-lang').esc_html__('NA ','kc-lang') ?></p>
                                                <p class="paragraph iq_kivicare_doctor_session-cell_value time"><?php echo  esc_html__('Evening : ','kc-lang').esc_html__('NA ','kc-lang') ?></p>
                                            </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        <?php }
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
         <?php echo $setting['iq_kivivare_doctor_gap_between_card'] === 'yes' ? '' : "<br>" ; ?>
        <?php
            }?>
            <div class="kivi-pagination">
                    <a  href="<?php echo $pageUrl1 ;?>"  >
                        <input  style="<?php echo $pageNo > 0 || !kcNotInPreviewmode() ? '' : 'display:none;' ;?>" class="iq_kivicare_next_previous book_button" type="button" name="next" value="<?php echo esc_html__('Previous','kc-lang') ?>">
                    </a>
                <a href="<?php echo $pageUrl;?>" >
                    <input style="<?php echo $nextPageDoctorCount > 0  || !kcNotInPreviewmode() ? '' : 'display:none;' ; ?>" class="iq_kivicare_next_previous book_button" type="button" name="next" value="<?php echo esc_html__('Next','kc-lang') ?>">
                </a>
            </div>
        </div>
            <?php 
        }
    }
}

// Register widget
\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \KCElementorClinicWiseDoctor() );