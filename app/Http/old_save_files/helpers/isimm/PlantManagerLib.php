<?php


namespace App\Http\old_save_files\helpers\isimm;

use Impianto;
use ServiziTOBE;
use SezioneTOBE;
use v_ImpiantoAsisTobe;

class PlantManagerLib extends IsimmBaseLib
{

    /*
    * [ frontend => backend ]
    */
    private static $asisParams = [
        'section_asis_alias' => 'uc1_sez_asis',
        'section_asis_tecnologia' => 'uc1_techtype',
        'section_asis_nlamp' => 'uc1_nlamp',
        'section_asis_lampprice' => 'uc1_lampprice',
        'section_asis_nquadel' => 'uc1_nquadel',
        'section_asis_cosqua' => 'uc1_cosqua',
        'section_asis_pownom' => 'uc1_pownom',
        'section_asis_facteff' => 'uc1_facteff',
        'section_asis_perdimm' => 'uc1_perdimm',
        'section_asis_oreacc' => 'uc1_oreacc',
        'section_asis_oreaccdimm' => 'uc1_oreaccdimm',
        'section_asis_intmanu' => 'uc1_intmanu',
        'section_asis_cossmal' => 'uc1_cossmal',
        'plant_guid' => 'plant_guid',
        'section_asis_origin_sc' => 'uc1_origin_sc',
        'section_asis_origin_zo' => 'uc1_origin_zo',
    ];

    private static $tobeParams = [
        'section_tobe_alias' => 'alias',
        'section_tobe_tecnologia' => 'tecnologia',
        'section_tobe_nlamp' => 'nlamp',
        'section_tobe_lampprice' => 'prezzo_lampada',
        'section_tobe_nquadel' => 'n_quadri_el',
        'section_tobe_cosqua' => 'costo_quadro',
        'section_tobe_pownom' => 'potenza_nominale',
        'section_tobe_facteff' => 'fattore_efficienza',
        'section_tobe_perdimm' => 'percentuale_dimm',
        'section_tobe_oreacc' => 'ore_accensione',
        'section_tobe_oreaccdimm' => 'ore_accensione_dimm',
        'section_tobe_intmanu' => 'intervallo_anni_manu',
        'section_tobe_cocoel' => 'costo_componente_el',
        'section_tobe_cosico' => 'costo_sistema_controllo',
        'section_tobe_cosarm' => 'costo_armatura',
        'section_tobe_cospal' => 'costo_palo',
        'section_tobe_coriim' => 'costo_rifacimento_imp',
        'section_tobe_coatpr' => 'costo_att_prodromiche',
        'section_asis_guid' => 'section_guid',
        'section_tobe_sal' => 'section_tobe_sal',
        'section_tobe_sss' => 'section_tobe_sss'
    ];


    public function parseSectionASIS($method, $doUpdate = false)
    {

        if ($doUpdate === true) {
            self::$asisParams['section_asis_guid'] = 'section_asis_guid';
        }
        return $this->elaborateRequest(self::$asisParams, $method, true);
    }


    public function parseSectionTOBE($method, $doUpdate = false)
    {

        if ($doUpdate === true) {
            self::$tobeParams['section_tobe_guid'] = 'section_tobe_guid';
        }

        $data = $this->elaborateRequest(self::$tobeParams, $method, true);


        $sal = array_pull($data, 'section_tobe_sal', []);
        $sss = array_pull($data, 'section_tobe_sss', []);

        if (is_array($sal) && is_array($sss)) {
            $data['_services'] = array_merge($sal, $sss);
        } else if (is_array($sal)) {
            $data['_services'] = $sal;
        } else if (is_array($sss)) {
            $data['_services'] = $sss;
        }


        return $data;
    }


    public function parsePlantAndSections(array $plantArr, $municipalities = NULL)
    {
        $aggregate = [];
        /* @var $plant v_ImpiantoAsisTobe */
        foreach ($plantArr as $plant) {
            $guid_tobe = $plant->getGuidTobe();
            $guid_asis = $plant->getGuidAsis();
            $guid_plant = $plant->getGuidImpianto();
            $municipality = strtoupper(array_get($municipalities, $guid_plant));

            if ($guid_asis !== NULL && $guid_tobe !== NULL && isset($aggregate[$guid_plant]['ASIS'][$guid_asis])) {

                $aggregate[$guid_plant]['ASIS'][$guid_asis]['TOBE'][$guid_tobe] = [
                    'alias' => $plant->getAliasTobe()
                ];

            } else if ($guid_asis !== NULL && $guid_tobe !== NULL && isset($aggregate[$guid_plant])) {
                $aggregate[$guid_plant]['ASIS'][$guid_asis] = [
                    'alias' => $plant->getAliasAsis(),
                    'TOBE' => [
                        $guid_tobe => [
                            'alias' => $plant->getAliasTobe()
                        ]
                    ]
                ];

            } else if ($guid_asis !== NULL && isset($aggregate[$guid_plant])) {
                $aggregate[$guid_plant]['ASIS'][$guid_asis] = [
                    'alias' => $plant->getAliasAsis(),
                ];

            } else if ($guid_plant !== NULL && $guid_asis !== NULL && $guid_tobe !== NULL) {

                $aggregate[$guid_plant] = [
                    'alias' => $plant->getAliasImpianto(),
                    'municipality' => $municipality,

                    'ASIS' => [
                        $guid_asis => [
                            'alias' => $plant->getAliasAsis(),
                            'TOBE' =>
                                [
                                    $guid_tobe => [
                                        'alias' => $plant->getAliasTobe()
                                    ]
                                ]
                        ]
                    ]
                ];

            } else if ($guid_plant !== NULL && $guid_asis !== NULL) {

                $aggregate[$guid_plant] = [
                    'alias' => $plant->getAliasImpianto(),
                    'municipality' => $municipality,

                    'ASIS' => [
                        $guid_asis => [
                            'alias' => $plant->getAliasAsis()
                        ]
                    ]
                ];

            } else if ($guid_plant !== NULL) {
                $aggregate[$guid_plant] = [
                    'alias' => $plant->getAliasImpianto(),
                    'municipality' => $municipality,
                ];
            }
        }

        return $aggregate;
    }

    public function associatePlantsMunicipalities($plants, $municipalities)
    {
        $plantGuids = [];
        /* @var $plant Impianto */
        foreach ($plants as $plant) {
            $plantGuid = $plant->getGuid();
            if (!array_key_exists($plantGuid, $plantGuids)) {
                $plantGuids[$plantGuid] = array_get($municipalities, $plant->getFkComune());
            }
        }

        return $plantGuids;
    }

    public function pairSectionTOBEAndServices(SezioneTOBE $section, array $services)
    {
        $result = $section->safeExtractWithID();
        /* @var $service ServiziTOBE */
        foreach ($services as $service) {

            if (starts_with($service->getTipo(), 'SAL_')) {
                $result ['SERVICES']['SAL'][] = $service->safeExtract();
            } else if (starts_with($service->getTipo(), 'SSS_')) {
                $result ['SERVICES']['SSS'][] = $service->safeExtract();
            }
        }

        return $result;
    }


}
