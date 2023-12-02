<?php

use App\Http\old_save_files\helpers\isimm\RecapLib;

defined('BASEPATH') OR exit('No direct script access allowed');

class nisimm extends MY_Controller {

    private $municipalities;

    /* @var $session CI_Session */
    public $session;

    public $pairDetail;

    /* @var $nInvestmentModel nInvestmentModel */
    public $nInvestmentModel ;

    /* @var $nRecapModel nRecapModel */
    public $nRecapModel;
    /* @var $RecapLib RecapLib */
    public $RecapLib;

    public function __construct()
    {
        parent::__construct();
        $this->verify_login();

        //Load template settings
        $this->init_template();

        $this->load->library('session');
        $this->load->language('isimm');

        $this->data['breadcrumbs'] = [];
        $this->data['page_title'] = $this->lang->line('ISIMM');
        $this->data['page_subtitle'] = '';

        $this->municipalities = $this->session->userdata('municipalities');

        $this->load->helper('url');
        $this->load->library('pdf');
        $this->load->add_package_path(APPPATH.'libraries/pChart/');
        $this->load->library('pchart');

    }


    private function fullRender($fullPath, array $options = [])
    {
        $this->add_asset('css_plugins', 'assets/plugins/jstree/dist/themes/default/style.css');
        $this->add_asset('css_plugins', 'assets/plugins/sweetalert/lib/sweet-alert.css');
        $this->add_asset('css_plugins', 'assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css');
        $this->add_asset('css_plugins', 'assets/plugins/DataTables/media/css/DT_bootstrap.css');

        $this->add_asset('css_page', 'assets/css/enea/isimm/isimm.css');

        $this->add_asset('js_plugins', 'assets/plugins/highcharts/highcharts.src.js');
        $this->add_asset('js_plugins', 'assets/plugins/jQuery-Smart-Wizard/js/jquery.smartWizard.js');
        $this->add_asset('js_plugins', 'assets/plugins/DataTables/media/js/jquery.dataTables.js');
        $this->add_asset('js_plugins', 'assets/plugins/DataTables/extensions/date-euro.js');
        $this->add_asset('js_plugins', 'assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js');
        $this->add_asset('js_plugins', 'assets/plugins/sweetalert/lib/sweet-alert.min.js');
        $this->add_asset('js_plugins', 'assets/plugins/jstree/dist/jstree.min.js');
        $this->add_asset('js_plugins', 'assets/js/ui-treeview.js');
        $this->add_asset('js_plugins', 'assets/plugins/jquery-maskmoney/jquery-maskMoney.js');

        $this->add_asset('js_page', 'assets/js/enea/isimm/valutazioni/isimm-valutazioni.js');

        $this->render($fullPath);
    }


    public function index()
    {
        $this->load->model('enea/isimm/nRecapModel');
        $this->load->lib_custom('isimm/RecapLib');

        if (is_array($this->municipalities))
        {
            $municipalitiesId = implode(', ', array_keys($this->session->userdata('municipalities')));
        }
        else
        {
            $municipalitiesId = 0;
        }

        $hps = $this->nRecapModel->getHypothesisByMunicipality($municipalitiesId);
        $this->data['storico_ipotesi'] = $this->RecapLib->hypothesisByMunicipality($hps, $this->municipalities);

        $datatables_init = "";
        $i = 0;
        foreach ($this->data['storico_ipotesi'] as $comune => $ipotesi_list) {
            $i++;
            $datatables_init .= "
                $('#storico-valutazioni-" . $i . "-table').DataTable({
                    columnDefs: [
                        { targets: [0], orderable: true,  searchable: true,  visible: true, type: 'date-euro', className: 'nowrap' },   // Data
                        { targets: [1], orderable: true,  searchable: true,  visible: true, className: 'nowrap' },                      // Valutazione
                        { targets: [2], orderable: true,  searchable: true,  visible: true, className: 'nowrap' },                      // Impianto
                        { targets: [3], orderable: true,  searchable: true,  visible: true, },                                          // Sezioni
                        { targets: [4], orderable: false, searchable: false, visible: true, },                                          // Comandi
                    ]
                });";
        }
        $this->data['js_class_init'] = array(
            array(
                'func' => $datatables_init
            )
        );

        $this->fullRender('enea/isimm/valutazioni/index');
    }


    public function content()
    {
        $this->add_init("IsimmStep1.init();");

        $this->fullRender('enea/isimm/valutazioni/content');
    }

    public function recap($hp_guid, $out = 'recap')
    {
        $this->load->model('enea/isimm/nRecapModel');
        $this->load->model('enea/isimm/nSectionModel');
        $this->load->lib_custom('isimm/RecapLib');

        $hypothesisList = $this->nRecapModel->getHypothesisRecap($hp_guid);
        if (count($hypothesisList)===0) {
            redirect('isimm');
        }
        $allPairsDetail = $this->RecapLib->extractGuidsInHypothesis($hypothesisList);
        $hypothesis = $this->nRecapModel->getHypothesisByGuid( $allPairsDetail ['hp'] );
        $investmentParamsData = $this->nRecapModel->getInvestmentParamsByHypothesis( $hypothesis->getFkInvestimentoParametri() );
        $investmentTotalData = $this->nRecapModel->getTotalInvestmentByHypothesis( $hypothesis->getId());
        $investmentPartialData = $this->nRecapModel->getSectionPairsByHypothesis( $hypothesis->getId());

        $sectionsASIS = $this->nRecapModel->getSectionsASISByGuids( $allPairsDetail['asis'] );
        $sectionsTOBE = $this->nRecapModel->getSectionsTOBEByGuids( $allPairsDetail['tobe'] );
        $plant = $this->nRecapModel->getPlantsByGuids( $allPairsDetail['plant'] );

        $pairDetail = array_pull($allPairsDetail,'pairs.'.$hypothesis->getGuid());

        $pairDetail['investment_params'] = $investmentParamsData;
        $pairDetail['investment_total'] = $investmentTotalData;
        $pairDetail['hypothesis'] = $hypothesis;
        $pairDetail['plant'] = $plant;
        $pairDetail['sections'] = $this->RecapLib->pairPlantSectionPartialInvestmentData($pairDetail['sections'], $sectionsASIS, $sectionsTOBE, $investmentPartialData);
        $pairDetail['municipality_name'] = $this->municipalities[$plant->getFkComune()];

        $this->data['recap'] = $pairDetail;
        $this->data['out'] = $out;

        if ($out == 'download') {

            return ($pairDetail);
        } else {
            $this->add_init("IsimmRecap.init();");
            $this->fullRender('enea/isimm/valutazioni/recap');
        }


    }

    public function impianti()
    {
        $this->add_init("IsimmImpianti.init();");

        $this->fullRender('enea/isimm/valutazioni/impianti');
    }

    public function investimenti()
    {
        $this->add_init("IsimmInvestimenti.init();");

        $this->fullRender('enea/isimm/valutazioni/investimenti');
    }

 /** DOWNLOAD */


    public function calcRecapPaybacktime($inputData){

        $this->InvestmentLib->checkUserMunicipality($inputData['municipality_name'], $this->municipalities);

        $sectionsPairData = $this->nSectionModel->getGenericMultipleSectionsByGuid($inputData['sections']);
        $investmentData = $this->nInvestmentModel->getInvestmentParams($inputData['investment_guid']);

        $paybackTimeList = $this->InvestmentLib->recapPaybackTime($sectionsPairData, $investmentData, $inputData['cost_unit_energy_min'], $inputData['cost_unit_energy_max']  );

        return $paybackTimeList;
    }

    public function calcRecapFee($inputData){
        $this->InvestmentLib->checkUserMunicipality($inputData['municipality_name'], $this->municipalities);

        $sectionsPairData = $this->nSectionModel->getGenericMultipleSectionsByGuid($inputData['sections']);
        $investmentData = $this->nInvestmentModel->getInvestmentParams($inputData['investment_guid']);

        $feeList = $this->InvestmentLib
            ->recapFeeMinMax(
                $sectionsPairData,
                $investmentData,
                array_get($inputData, 'duration_min', 0),
                array_get($inputData, 'duration_max', 0),
                array_get($inputData, 'taxes', 0),
                array_get($inputData, 'quota_finanziata', 0)
            );

        return $feeList;
    }


    public function calcRecapVANTIR($inputData){

        $taxes = [
            't1' => $inputData['t1'],
            't2' => $inputData['t2'],
            't3' => $inputData['t3']
        ];

        $duration = [
            'r1' => $inputData['r1'],
            'r2' => $inputData['r2'],
            'r3' => $inputData['r3']
        ];

        $this->InvestmentLib->checkUserMunicipality($inputData['municipality_name'], $this->municipalities);

        $sectionsPairData = $this->nSectionModel->getGenericMultipleSectionsByGuid($inputData['sections']);
        $investmentData = $this->nInvestmentModel->getInvestmentParams($inputData['investment_guid']);

        $VAN_TIR = $this->InvestmentLib->recapVANTIR($sectionsPairData, $investmentData, $taxes, $duration  );

        return $VAN_TIR;
    }


    public function download_recap(){

        $hp_guid = $this->input->post('dl_guid');

        $recap              = $this->recap($hp_guid, 'download');
        $investment_params  = $recap['investment_params'];
        $sections           = [];

        for ($i=0; $i < count($recap['sections']); $i++) {
            $section = $recap['sections'][$i];
            $section_asis_id = $section['asis']->getGuid();
            $section_tobe_id = $section['tobe']->getGuid();
            $section_asis_tobe = ['asis' => $section_asis_id, 'tobe' => $section_tobe_id];
            array_push($sections,$section_asis_tobe);
        }

        $inputData = [
            'hp_guid'               => $hp_guid,
            'investment_guid'       => $investment_params->getGuid(),
            'municipality_name'     => $this->input->post('dl_municipality_name'),
            'cost_unit_energy_min'  => floatval(str_replace(",",".",$this->input->post('dl_cost_unit_energy_min'))),
            'cost_unit_energy_max'  => floatval(str_replace(",",".",$this->input->post('dl_cost_unit_energy_max'))),
            't1'                   => floatval(str_replace(",",".",$this->input->post('dl_t1'))),
            't2'                   => floatval(str_replace(",",".",$this->input->post('dl_t2'))),
            't3'                   => floatval(str_replace(",",".",$this->input->post('dl_t3'))),
            'r1'                   => intval($this->input->post('dl_r1')),
            'r2'                   => intval($this->input->post('dl_r2')),
            'r3'                   => intval($this->input->post('dl_r3')),
            'sections'              => $sections,
            'duration_min'	        => intval($this->input->post('dl_duration_min')),
            'duration_max'          => intval($this->input->post('dl_duration_max')),
            'taxes'                 => floatval(str_replace(",",".",$this->input->post('dl_taxes'))),
            'quota_finanziata'      => floatval(str_replace(",",".",$this->input->post('dl_quota_finanziata'))),
        ];



        $paybackTimeList    = $this->calcRecapPaybacktime($inputData);
        $feeList            = $this->calcRecapFee($inputData);
        $vantir             = $this->calcRecapVANTIR($inputData);

        $page = 'recap';

        $chartDataPayBack = [
            'y_label' => 'PayBack (anni)',
            'x' => [],
            'y' => [[]]
        ];
        for ($i=0; $i < count($paybackTimeList); $i++) {
            $x = $paybackTimeList[$i]['x'];
            $y = $paybackTimeList[$i]['y'];
            array_push($chartDataPayBack['x'],$x);
            array_push($chartDataPayBack['y'][0],$y);
        }

        $chartDataFeeList = [
            'y_label' => 'Canone Annuo',
            'x' => [],
            'y' => [[],[]]
        ];
        for ($i=0; $i < count($feeList); $i++) {
            $x = $feeList[$i]['x'];
            $y1 = $feeList[$i]['y']['min'];
            $y2 = $feeList[$i]['y']['max'];
            array_push($chartDataFeeList['x'],$x);
            array_push($chartDataFeeList['y'][0],$y1);
            array_push($chartDataFeeList['y'][1],$y2);
        }

        $chartPayBack = $this->chartRender($chartDataPayBack);
        $chartFeeList = $this->chartRender($chartDataFeeList);
        $base_url = base_url();


        $type_pell   = pathinfo($base_url.'assets/images/enea/logo_pell_mini.jpg', PATHINFO_EXTENSION);
        $type_enea   = pathinfo($base_url.'assets/images/enea/logo_enea_mini.jpg', PATHINFO_EXTENSION);
        $data_pell   = file_get_contents($base_url.'assets/images/enea/logo_pell_mini.jpg');
        $data_enea   = file_get_contents($base_url.'assets/images/enea/logo_enea_mini.jpg');

        $base64_pell = 'data:image/' . $type_pell . ';base64,' . base64_encode($data_pell);
        $base64_enea = 'data:image/' . $type_enea . ';base64,' . base64_encode($data_enea);

        $logo = [
            'pell' => $base64_pell,
            'enea' => $base64_enea
        ];

        $data = [
           'base_url'   => $base_url,
           'input'      => $inputData,
           'recap'      => $recap,
           'pbtl'       => $paybackTimeList,
           'feelist'    => $feeList,
           'vantir'     => $vantir,
           'chartPayBack'      => $chartPayBack,
           'chartFeeList'      => $chartFeeList,
           'logo'       => $logo
        ];

        $mode = 0;
        if ($mode === 0) {
           $this->load->view('enea/downloadpdf/'.$page, $data, FALSE);
        }
        else {
           $html = $this->load->view('enea/downloadpdf/'.$page, $data, TRUE);
           $this->pdf->loadHtml($html);
           $this->pdf->setPaper('A4', 'portrait');
           $this->pdf->render();
           $ts = time();
           $this->pdf->stream("PELL_ISIMM_".date('Y_m_d_H_i_s', $ts), array("Attachment"=>1));
        }


     }


     public function chartRender($chartData){


        $PDA = $this->pchart->pData();

        $PDA->addPoints($chartData['y'][0], "Y_Data1");
        if (count($chartData['y']) == 2) {
            $PDA->addPoints($chartData['y'][1], "Y_Data2");
        }
        $PDA->setAxisName(0,$chartData['y_label']);

        $PDA->addPoints($chartData['x'],"X_Data");
        $PDA->setSerieDescription("X_Data","Costo Energia (€/KWh)");
        $PDA->setAbscissa("X_Data");

        $serieSettingsA= array("R"=>12,"G"=>102,"B"=>220,"Alpha"=>100);
        $PDA->setPalette("Y_Data1",$serieSettingsA);

        if (count($chartData['y']) == 2) {
            $serieSettingsB = array("R"=>245,"G"=>49,"B"=>27,"Alpha"=>100);
            $PDA->setPalette("Y_Data2",$serieSettingsB);
        }


        $PDI = $this->pchart->pImage(700,260,$PDA);

        $PDI->setFontProperties(array("FontName"=>APPPATH.'libraries/pChart/fonts/verdana.ttf',"FontSize"=>9));
        $PDI->setGraphArea(60,10,640,240);
        $PDI->drawScale(array("GridR"=>160,"GridG"=>160,"GridB"=>160));
        $PDI->drawLineChart();

        $currentImage   = time();
        $imgPath        =    APPPATH."libraries/pChart/tmp/".$currentImage.".png";
        $PDI->render($imgPath);
        $type   = pathinfo($imgPath, PATHINFO_EXTENSION);
        $data   = file_get_contents($imgPath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        unlink($imgPath);

        return $base64;

    }

}


