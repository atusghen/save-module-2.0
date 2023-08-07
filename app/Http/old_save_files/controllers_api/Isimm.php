<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Isimm extends MY_Controller implements IsimmController {

    /* @var $isimm_model Isimm_model */
    public $isimm_model ;

    /* @var $session CI_Session */
    public $session;

	public function __construct() {
		parent::__construct();
		$this->verify_login();

		//Load template settings
		$this->init_template();

        $this->load->model('enea/isimm_model');
		$this->load->library('session');
        $this->load->language('isimm');

        $this->data['breadcrumbs'] = [];
        $this->data['page_title'] = $this->lang->line('ISIMM');
        $this->data['page_subtitle'] = '';

	}



	public function index()
    {
		$this->data['hyphotesis'] = $this->getAllHypothesis();

		$this->fullRender('enea/isimm/index');

	}

    public function content()
    {
        $this->data['UC1_TechType'] = self::getLampTechType();
        $this->data['UC1_ServiceType'] = ['SAL', 'SSS'];
        $this->data['UC1_SALType'] = ['SAL 1', 'SAL 2', 'SAL 3'];
        $this->data['UC1_SSSType'] = ['SSS 1 ', 'SSS 2', 'SSS 3', 'SSS 4', 'SSS 5', 'SSS 6', 'SSS 7', 'SSS 8', 'SSS 9', 'SSS 10', 'SSS 11' ];
        /* SECTION */
        $this->addFormElem('UC1_FormPlant', '_nlamp', 'N. lampade', '', true);
        $this->addFormElem('UC1_FormPlant', '_lampprice', 'Costo lampada', '', true);
        $this->addFormElem('UC1_FormPlant', '_nquadel', 'N. quadri elettrici', 0);
        $this->addFormElem('UC1_FormPlant', '_cosqua', 'Costo quadro', 0);
        $this->addFormElem('UC1_FormPlant', '_pownom', 'Potenza nominale', '', true);
        $this->addFormElem('UC1_FormPlant', '_facteff', 'Fattore efficienza', 1, true);
        $this->addFormElem('UC1_FormPlant', '_perdimm', 'Percentuale dimmering', 100);
        $this->addFormElem('UC1_FormPlant', '_oreacc', 'Ore accensione piena', 4168, true);
        $this->addFormElem('UC1_FormPlant', '_oreaccdimm', 'Ore accensione dimmering', 0, true);
        $this->addFormElem('UC1_FormPlant', '_intmanu', 'Intervallo anni manutenzione');
        /* AS-IS */
        $this->addFormElem('UC1_FormASIS', '_cossmal', 'Costo smaltimento lampada', 0);
        /*  TO-BE   */
        $this->addFormElem('UC1_FormTOBE', '_cocoel', 'Costo componente elettronica', 0);
        $this->addFormElem('UC1_FormTOBE', '_cosico', 'Costo sistema di controllo', 0);
        $this->addFormElem('UC1_FormTOBE', '_cosarm', 'Costo armatura', 0);
        $this->addFormElem('UC1_FormTOBE', '_cospal', 'Costo palo', 0);
        $this->addFormElem('UC1_FormTOBE', '_coriim', 'Costo rifacimento impianto', 0);
        $this->addFormElem('UC1_FormTOBE', '_coatpr', 'Costo attività prodromiche', 0);
        $this->addFormElem('UC1_FormSAL', '_sal_cosin', 'Costo installazione', '', true);
        $this->addFormElem('UC1_FormSAL', '_sal_durser', 'Durata servizio', 0);
        $this->addFormElem('UC1_FormSAL', '_sal_fluca', 'Flusso di cassa', 0);
        $this->addFormElem('UC1_FormSSS', '_sss_cosin', 'Costo installazione', '', true);
        $this->addFormElem('UC1_FormSSS', '_sss_durser', 'Durata servizio', 0);
        $this->addFormElem('UC1_FormSSS', '_sss_fluca', 'Flusso di cassa', 0);

        $this->data['forms'] = array(
            array('form' => $this->parser->parse('enea/isimm/form-asis', $this->data, TRUE)),
            array('form' => $this->parser->parse('enea/isimm/form-tobe', $this->data, TRUE)),
        );

        $this->add_asset('css_page', 'assets/plugins/sweetalert/lib/sweet-alert.css');

        $this->add_asset('js_page', 'assets/plugins/jquery-validation/localization/messages_it.js');
        $this->add_asset('js_page', 'assets/plugins/sweetalert/lib/sweet-alert.min.js');
        $this->add_asset('js_page', 'assets/js/enea/isimm/form-validation.js');
        $this->add_asset('js_page', 'assets/js/enea/isimm/form-wizard.js');

		$options = '';
        $js_init = 'Isimms.init('.json_encode($options).');';
        $this->data['js_class_init'] = array(
            array(
                'func' => $js_init
            )
        );

        $this->fullRender('enea/isimm/content');
    }


	public function get_recap($plantGuid , $sectionGuid , $investmentGuid ){


		/*
		 * PLANT
		 */
		$plantDetail = $this->getPlantDetail($plantGuid);
		$this->filterArray($plantDetail, ['guid','name'] );

		$sectionIndex = 0;
		/*
		 * ASIS (section guid = plant guid)
		 */
		$sectionASISDetail = $this->getSectionDetail_ASIS($plantGuid);
		$this->filterArray($sectionASISDetail, ['guid','hour_on','efficiency_factor','hour_on_dimmering','lamp_price','n_cabinet','n_lamp','percent_dimmering','pow_nom','tech_type','year_maintenance_range','plant_name','disposal_lamp_price']);
		$sectionsDetail[$sectionIndex]['ASIS'] = $sectionASISDetail;

		/*
		 * TOBE
		 */
		$sectionTOBEDetail = $this->getSectionDetail_TOBE($sectionGuid);
		$sectionsDetail[$sectionIndex]['TOBE'] = $sectionTOBEDetail;
		$sectionIndex++; //@todo usare realmente l'indice di sezione; qui è fittizio

		$investmentDetail = $this->getInvestmentDetailBySection($investmentGuid);

		$this->filterArray( $investmentDetail, ['guid','name','investment_price','unit_energy_cost','wacc','delta_energy_cost','delta_energy_consumption','incentive_duration','kwh_tep','tep_price','management_cost','created_at','depreciation_duration','cash_flow','van','tir','payback_time','project_duration','share_financed','financing_price','annual_fee_min','annual_fee_max','taxes'] );


		$this->data['RecapLib'] = ['plant' => $plantDetail, 'sections' => $sectionsDetail, 'investment' => $investmentDetail];

		$inputParams = array(
			'sez_asis_guid'        => array_get($sectionsDetail, '0.ASIS.guid', 0),
			'sez_tobe_guid'        => array_get($sectionsDetail, '0.TOBE.GENERIC.section_guid', 0),
			'investment_da'        => array_get($investmentDetail, 'depreciation_duration', 0),
			'wacc'                 => array_get($investmentDetail, 'wacc', 0),
			'investment_di'        => array_get($investmentDetail, 'incentive_duration', 0),
			'investment_cue'       => array_get($investmentDetail, 'unit_energy_cost', 0),
			'investment_vmtep'     => array_get($investmentDetail, 'tep_price', 0),
			'investment_kwhtep'    => array_get($investmentDetail, 'kwh_tep', 0),
			'investment_cg'        => array_get($investmentDetail, 'management_cost', 0),
			'investment_price'     => array_get($investmentDetail, 'investment_price', 0),
			'financial_share'      => array_get($investmentDetail, 'share_financed', 0),
			'taxes'                => array_get($investmentDetail, 'taxes', 0),
			'energetic_cost_delta' => array_get($investmentDetail, 'delta_energy_cost', 0)
        );
        $recap_init  = 'Isimms.loadRecapTableVANTIR(' . json_encode($inputParams) . ');';
        $recap_init .= 'Isimms.loadRecapGraphPaybackTime(' . json_encode($inputParams) . ');';
        $recap_init .= 'Isimms.loadRecapGraphFee(' . json_encode($inputParams) . ');';
        log_message('error', $recap_init);
        $this->data['js_class_init'] = array(
            array(
                'func' => $recap_init
            )
        );

		$this->fullRender('enea/isimm/recap');
	}


	public static function getLampTechType(){
		return [
			'ALO'   => 'Alogena',
			'IOM'	=> 'Ioduri metallici',
			'SAP'   => 'Sodio alta pressione',
			'SBP'   => 'Sodio Bassa Pressione',
			'LED'   => 'LED',
			'VAM'	=> 'Vapori Mercurio',
			'INC'	=> 'Incandescenza',
			'FLC'	=> 'Fluorescenza compatta',
			'FLT'	=> 'Fluorescenza tubolare'
		];
	}


    /*
     * LOGIC
     */

    public function filterArray(&$array, $allowedKeys ){
		$result = [];

		if ($array === null) {
			$array = [];
			return;
		}

    	array_walk($array, function ($value, $key) use ($allowedKeys, &$result){

    		if (in_array($key, $allowedKeys, true)){
				$result [$key] = $value;
			}

		});

		$array = $result;
	}

    public function getAllHypothesis(){

		$userId = $this->session->userdata('id');
		$hps = $this->isimm_model->getAllHypothesisByUser($userId);
		$investment = [];

		foreach ($hps as $hp) {

			$investment[] = [
				'data'            => array_get($hp, 'created_at', 0),
				'investment_name' => array_get($hp, 'investment_name', 0),
				'investment_guid' => array_get($hp, 'investment_guid', 0),

				'plant_guid' => array_get($hp, 'plant_guid', 0),

				'sections' => [
					'tobe' => [
						'name' => array_get($hp, 'hp_name', 0),
						'guid' => array_get($hp, 'section_guid', 0),
					],
					'asis' => [
						'name' => array_get($hp, 'plant_name', 0),
						'guid' => array_get($hp, 'plant_guid', 0),
					]
				]
			];

		}

		return $investment;

	}


    public function addFormElem($formId, $elemId, $label, $placeholder = '', $required = false, $formType = 'number', array $formCol = ['col-sm-3', 'col-sm-7'] )
    {

        ($required === true) ? $addonClass = 'required' : $addonClass = '';
        $elemId = strtolower(explode('_',$formId)[0].$elemId ) ;

        $this->data[$formId][] = [
            'label'         => $label,
            'id'            => $elemId,
            'addon-class'   => $addonClass,
            'placeholder'   => $placeholder,
            'type'          => $formType,
            'label-col'     => $formCol[0],
            'input-col'     => $formCol[1],
        ];
    }

    public function fullRender($fullPath, array $options = [] ){

        $this->add_asset('css_page', 'assets/css/enea/isimm/isimm.css');
        $this->add_asset('js_plugins', 'assets/plugins/highcharts/highcharts.src.js');
        $this->add_asset('js_plugins', 'assets/plugins/jQuery-Smart-Wizard/js/jquery.smartWizard.js');
        $this->add_asset('js_page', 'assets/js/enea/isimm/isimm.js');

        $this->render($fullPath);
    }



    public function getInvestmentDetailBySection($guid){

    	return $this->isimm_model->getInvestmentByGuid($guid);
	}


    public function getPlantDetail($plantGuid)
	{
		$userId = $this->session->userdata('id');
		return $this->isimm_model->getPlantByGuid($plantGuid, $userId);
	}

    public function  getSectionDetail_TOBE($sectionGuid){

		/* param name => param label */
		$allowedParams['GENERIC'] = [
			'hp_name'                => 'Ipotesi riqualificazione',
			'uuid'                   => 'uuid',
			'section_guid'           => 'guid',
			'hour_on'                => 'Ore accensione piena',
			'efficiency_factor'      => 'Fattore efficienza',
			'hour_on_dimmering'      => 'Ore accensione dimmering',
			'lamp_price'             => 'Costo lampada',
			'n_cabinet'              => 'N. quadri elettrici',
			'n_lamp'                 => 'N. lampade',
			'percent_dimmering'      => 'Percentuale dimmering',
			'pow_nom'                => 'Potenza nominale',
			'tech_type'              => 'Tipo tecnologia',
			'year_maintenance_range' => 'Intervallo anni manutenzione',

			'cabinet_price'                  => 'Costo quadro',
			'electrical_plant_rebuild_price' => 'Costo rifacimento impianto',
			'electronic_components_price'    => 'Costo componente elettronica',
			'frame_price'                    => 'Costo armatura',
			'pole_price'                     => 'Costo palo',
			'preparatory_activity_price'     => 'Costo attività prodromiche',
			'system_control_price'           => 'Costo sistema di controllo',

		];

		$allowedParams['SSS'] = [
			'sss_type'          => 'type',
			'sss_install_price' => 'Costo installazione',
			'sss_duration'      => 'Durata',
			'sss_cash_flow'     => 'Flusso di cassa',
		];


		$allowedParams['SAL'] = [
			'sal_type'          => 'type',
			'sal_install_price' => 'Costo installazione',
			'sal_duration'      => 'Durata',
			'sal_cash_flow'     => 'Flusso di cassa',
		];

		//@todo vericare compatibilità con sezioni multiple
		$tobeParams = $this->isimm_model->getSectionByGuid_tobe($sectionGuid);

		$filtered = $this->splitTOBEParams($tobeParams, $allowedParams);


		return $filtered;

	}

    public function getSectionDetail_ASIS($sectionGuid){
		return $this->isimm_model->getSectionByGuid_asis($sectionGuid);
	}

    public function splitTOBEParams($tobeParams, $allowed){

		$filtered = [];
		$genericBuffKey = '';

		foreach ($tobeParams as $tobe) {
			$uiid = explode( '-', $tobe['uuid']);
			$genericBuffKey = $uiid[0].'-'.$uiid[1].'-'.$uiid[2];
			$salKey = $genericBuffKey.'-'.$uiid[3];
			$sssKey = $genericBuffKey.'-'.$uiid[4];
			$buff = [];

			$allowedParams = $allowed['GENERIC'];
			$allowedParams_SAL = $allowed['SAL'];
			$allowedParams_SSS = $allowed['SSS'];

			array_walk($tobe, function ($value, $key) use ($allowedParams, $allowedParams_SAL, $allowedParams_SSS, $genericBuffKey, &$buff) {

				if ( array_key_exists($key, $allowedParams)){
					if ( $key === 'uuid') $value = $genericBuffKey;
					$buff['GENERIC'][ $key ] = $value;
				}
				if ( array_key_exists($key, $allowedParams_SAL) ){
					$buff['SAL'][ $key ] = $value;
				}
				if ( array_key_exists($key, $allowedParams_SSS)){
					$buff['SSS'][ $key ] = $value;
				}

			});

			if ( !array_key_exists('GENERIC', array_get($filtered, $genericBuffKey, [])) )
			{
				$filtered[$genericBuffKey]['GENERIC'] = $buff['GENERIC'];
			}

			if ( !array_key_exists($salKey,  array_get($filtered, $genericBuffKey.'SAL', [])) && array_get($buff, 'SAL.sal_type', null) !== null )
			{
				$filtered[$genericBuffKey]['SAL'][$salKey] = $buff['SAL'];
			}
			else if (!array_key_exists('SAL', $filtered[$genericBuffKey])){
				$filtered[$genericBuffKey]['SAL']= [];
			}

			if ( !array_key_exists($sssKey, array_get($filtered, $genericBuffKey.'SSS', [])) && array_get($buff, 'SSS.sss_type', null) !== null)
			{
				$filtered[$genericBuffKey]['SSS'][$sssKey] = $buff['SSS'];
			}
			else if (!array_key_exists('SSS', $filtered[$genericBuffKey])){
				$filtered[$genericBuffKey]['SSS']= [];
			}
		}
		return array_get($filtered, $genericBuffKey, []);
	}
}
