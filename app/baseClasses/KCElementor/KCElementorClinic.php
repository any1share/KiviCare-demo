<?php

use App\baseClasses\KCBase;

class KCElementorClinic extends \Elementor\Widget_Base {

    public function get_name() {
        return 'kivicare-clinic';
    }

    public function get_title() {
        return __( 'Kivicare Clinic', 'kc-lang' );
    }

    public function get_icon() {
        return 'fas fa-hospital';
    }

    public function get_categories() {
        return [ 'kivicare-widget-category' ];
    }

    protected function _register_controls() {
        $type = 'clinic';
        $this->start_controls_section(
            'iq_kivicare_clinic_section_shortcode',
            [
                'label' => __( 'Kivicare '.ucfirst($type), 'kc-lang' ),
            ]
        );

        $this->add_control(
            'iq_kivivare_'. $type .'_id',
            [
                'label' => __(ucfirst($type), 'kc-lang' ),
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
            'iq_kivivare_'.$type.'_image',
            [
                'label' => esc_html__( 'Profile Image', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
            ]
        );

        $this->commonControl($this,'name',$type);
        $this->commonControl($this,'number',$type);
        $this->commonControl($this,'email',$type);
        $this->commonControl($this,'address',$type);
        $this->commonControl($this,'speciality',$type);
        $this->commonControl($this,'session',$type);
        $this->commonControl($this,'description',$type);
        $this->add_control(
            'iq_kivivare_'.$type.'_admin_hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );
        $this->add_control(
            'iq_kivivare_'.$type.'_admin_enable',
            [
                'label' => esc_html__( 'Clinic Admin', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
            ]
        );
        $this->add_control(
            'iq_kivivare_'.$type.'_admin_title',
            [
                'label' => esc_html__( 'Title ', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' =>esc_html__('Clinic Admin Info','kc-lang') ,
                'condition'=>[
                    'iq_kivivare_'.$type.'_admin_enable' => 'yes'
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->add_control(
            'iq_kivivare_'.$type.'_admin_image',
            [
                'label' => esc_html__( 'Profile Image', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
                'condition' =>[
                    'iq_kivivare_'.$type.'_admin_enable' => 'yes'
                ]
            ]
        );
        $this->commonControl($this,'name','clinic_admin');
        $this->commonControl($this,'number','clinic_admin');
        $this->commonControl($this,'email','clinic_admin');
        $this->end_controls_section();

        $this->start_controls_section('iq_kivicare_'.$type.'_style_sections',
            [
                'label' => esc_html__('Doctor style', 'kc-lang'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE
            ]
        );

        $this->add_control(
            'iq_kivicare_'.$type.'_card_border',
            [
                'label' => esc_html__('Card Border', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 0,
                'selectors' => [
                    '{{WRAPPER}} .card' => 'border: {{VALUE}}px;',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_'.$type.'_card_border_color',
            [
                'label' => esc_html__('Card Border Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .card' => 'border-color: {{VALUE}};',
                ]
            ]
        );

        $this->add_control(
            'iq_kivicare_'.$type.'_label_color',
            [
                'label' => esc_html__('Font Color', 'kc-lang'),
                'type' => \Elementor\Controls_Manager::COLOR,
                //'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .personalinfo' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();
    }
    protected function commonControl($this_ele,$type,$userType){

        $condition = [ ];
        $condition2=[ 'iq_kivivare_'. $userType .'_'.$type => 'yes'];
        if($userType === 'clinic_admin'){
            $condition = [
                'iq_kivivare_clinic_admin_enable' => 'yes'
            ];
            $condition2 = [
                'iq_kivivare_'. $userType .'_'.$type => 'yes',
                'iq_kivivare_clinic_admin_enable' => 'yes'
            ];
        }
        $this_ele->add_control(
            'iq_kivivare_'. $userType .'_'.$type,
            [
                'label' => esc_html__( $type === 'email' ? ucfirst($type).' ID' : ucfirst($type), 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => esc_html__( 'Hide', 'elementor' ),
                'label_on' => esc_html__( 'Show', 'elementor' ),
                'condition' =>$condition
            ]
        );

        $this_ele->add_control(
            'iq_kivivare_'. $userType .'_'.$type.'_label',
            [
                'label' => esc_html__( 'label ', 'kc-lang' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => $type === 'email' ? ucfirst($type).' ID' : ucfirst($type),
                'condition'=>$condition2,
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

    protected function render()
    {
        $setting = $this->get_settings_for_display();
        $type = 'clinic';
        $admin_type = 'clinic_admin';
        ?>
        <style>
            .icon{
                display: flex;
                /* align-items:center; */
            }
            .card{
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
                max-width:auto;
                margin: auto;
                padding: 1.5rem;

            }
            .profile{
                display: flex;
                flex-wrap: wrap;
            }
            .avtar{
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
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                margin-left: 0.5rem;
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
                .avtar{
                    width: 100%;
                }
            }
        </style>

        <?php
        if (!empty($setting['iq_kivivare_'.$type.'_id']) && $setting['iq_kivivare_'.$type.'_id'] == 'default') {
            ?>
            <div class="elementor-shortcode"> <?php echo _e('No Clinic Found', 'kc-lang'); ?></div>
            <?php
        }
        else {

            $id = $setting['iq_kivivare_'.$type.'_id'];
            if(kcClinicDetail($id)){
                $clinic_data = kcClinicDetail($id);
                $user_image_url = wp_get_attachment_url($clinic_data->profile_image);
                $admin_data = get_user_meta($clinic_data->clinic_admin_id, 'basic_data', true);
                $admin_data = json_decode($admin_data);
                //$admin_info = get_userdata($clinic_data->clinic_admin_id);
                $description = get_user_meta($clinic_data->clinic_admin_id, 'clinic_description', true);
                global $wpdb;
                $data =  $wpdb->get_row("SELECT ID FROM {$wpdb->posts} WHERE post_status='publish' and post_content LIKE '%[bookAppointment]%'", ARRAY_N);
                $appointmentPageUrl = '';
                if($data != null ){
                    $appointmentPageUrl = get_permalink(isset($data[0]) ? $data[0] : 0  );
                }
                $query = parse_url($appointmentPageUrl, PHP_URL_QUERY);
                if ($query) {
                    $appointmentPageUrl .= '?clinic_id='.$id;
                } else {
                    $appointmentPageUrl .= '?clinic_id='.$id;
                }
                ?>
                <div ><?php ?>
                    <div class="card">
                        <div class="profile">
                            <?php if($setting['iq_kivivare_'.$type.'_image'] === 'yes'){ ?>
                                <div >
                                    <img src="<?php echo $user_image_url; ?>"  class="avtar">
                                </div>
                            <?php }?>
                            <div>
                                <?php if ($setting['iq_kivivare_'.$type.'_name'] === 'yes') { ?>
                                    <h4 class="personalinfo"><?php echo $setting['iq_kivivare_'.$type.'_name_label']; ?></h4>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->name) ? $clinic_data->name : '';?></p>
                                    <?php
                                }
                                if ($setting['iq_kivivare_'.$type.'_email'] === 'yes') {
                                    ?>
                                    <h4 class="personalinfo content"><?php echo $setting['iq_kivivare_'.$type.'_email_label']; ?></h4>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->email) ? $clinic_data->email : '' ;?></p>
                                    <?php
                                }
                                if ($setting['iq_kivivare_'.$type.'_number'] === 'yes') {
                                    ?>
                                    <h4 class="personalinfo content"><?php echo $setting['iq_kivivare_'.$type.'_number_label']; ?></h4>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->telephone_no) ? $clinic_data->telephone_no : '' ; ?></p>
                                    <?php
                                }
                                if ($setting['iq_kivivare_'.$type.'_address'] === 'yes') {
                                    ?>
                                    <h4 class="personalinfo content"><?php echo $setting['iq_kivivare_'.$type.'_address_label']; ?></h4>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->address) ? $clinic_data->address : ''  ?></p>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->city) ? $clinic_data->city : ''  ?></p>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->state) ? $clinic_data->state : ''  ?></p>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->country) ? $clinic_data->country : ''  ?></p>
                                    <p class="personalinfo"><?php echo !empty($clinic_data->postal_code) ? $clinic_data->postal_code : ''  ?></p>
                                <?php } ?>
                            </div>
                        </div>
                        <div>
                            <?php if($setting['iq_kivivare_'.$type.'_speciality'] === 'yes'){?>
                                <div>
                                    <div class="icon">
                                        <!--                                --><?php //\Elementor\Icons_Manager::render_icon( $setting['iq_kivivare_'.$type.'_speciality_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                        <div class="session">
                                            <h4 class="personalinfo "><?php echo $setting['iq_kivivare_'.$type.'_speciality_label']; ?></h4>
                                            <div>
                                                <ul class="list">

                                                    <?php
                                                    if(!empty($clinic_data->specialties) && is_array(json_decode($clinic_data->specialties)) > 0){
                                                        foreach (json_decode($clinic_data->specialties) as $special){?>
                                                            <li><?php echo !empty($special->label) ? $special->label : '' ; ?></li>
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
                            if ($setting['iq_kivivare_'.$type.'_session'] === 'yes') { ?>
                                <div>
                                    <div class="icon">
                                        <!--                                    --><?php //\Elementor\Icons_Manager::render_icon( $setting['iq_kivivare_'.$type.'_session_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                        <div class="session">
                                            <h4 class="personalinfo "><?php echo $setting['iq_kivivare_'.$type.'_session_label']; ?></h4>
                                            <p class="personalinfo">Mon, Tues, Wed, Thus, Fri, Sat, Sun</p>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            if ($setting['iq_kivivare_'.$type.'_description'] === 'yes') {
                                ?>
                                <div>
                                    <div class="icon">
                                        <div class="session">
                                            <h4 class="personalinfo content"><?php echo $setting['iq_kivivare_'.$type.'_description_label']; ?></h4>
                                            <p class="personalinfo"><?php echo $description; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            ?>
                        </div>
                        <?php if ($setting['iq_kivivare_'.$type.'_admin_enable'] === 'yes') {
                            ?>
                            <div>
                                <div>
                                    <h3> <?php echo $setting['iq_kivivare_'.$type.'_admin_title'];?></h3>
                                </div>
                            <div class="profile">
                                <?php if($setting['iq_kivivare_'.$admin_type.'_image'] === 'yes'){ ?>
                                    <div >
                                        <img src="<?php echo !empty($admin_data->profile_image) ?  wp_get_attachment_url($admin_data->profile_image) : '' ; ?>"  class="avtar">
                                    </div>
                                <?php }?>
                                <div>
                                    <?php if ($setting['iq_kivivare_'.$admin_type.'_name'] === 'yes') { ?>
                                        <h4 class="personalinfo"><?php echo $setting['iq_kivivare_'.$admin_type.'_name_label']; ?></h4>
                                        <p class="personalinfo"><?php echo (!empty($admin_data->first_name) ? $admin_data->first_name : '') . ' '. (!empty($admin_data->last_name) ? $admin_data->last_name : '') ;?></p>
                                        <?php
                                    }
                                    if ($setting['iq_kivivare_'.$admin_type.'_email'] === 'yes') {
                                        ?>
                                        <h4 class="personalinfo content"><?php echo $setting['iq_kivivare_'.$admin_type.'_email_label']; ?></h4>
                                        <p class="personalinfo"><?php echo !empty($admin_data->user_email) ? $admin_data->user_email : '' ;?></p>
                                        <?php
                                    }
                                    if ($setting['iq_kivivare_'.$admin_type.'_number'] === 'yes') {
                                        ?>
                                        <h4 class="personalinfo content"><?php echo $setting['iq_kivivare_'.$admin_type.'_number_label']; ?></h4>
                                        <p class="personalinfo"><?php echo !empty($admin_data->mobile_number) ? $admin_data->mobile_number : '' ; ?></p>
                                        <?php
                                    } ?>
                                </div>
                            </div>
                            </div>
                            <?php
                        } ?>
                        <div>
                            <a href="<?php echo $appointmentPageUrl ;?>" target="_blank" >
                                <input type="button" value="Book Appointment"  style="color: black;" >
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }else{
                echo esc_html__('Select Clinic Is Not Available','kc-lang');
            }
        }

    }
}

// Register widget
\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \KCElementorClinic() );
