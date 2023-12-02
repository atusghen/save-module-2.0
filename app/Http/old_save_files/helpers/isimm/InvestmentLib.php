<?php


namespace App\Http\old_save_files\helpers\isimm;

use CashflowLib;
use InvestimentoParametri;
use ServiziTOBE;
use SezioneASIS;
use SezioneTOBE;

class InvestmentLib extends IsimmBaseLib
{

    public function __construct()
    {
        parent::__construct();
        $this->CI->load->library('isimm/CashflowLib');
    }

    private static $investmentParams = [
        'municipality_name',
        'investment_guid',
        'investment_name',
        'costo_unitario_energia',
        'durata_incentivi',
        'kwh_per_tep',
        'valore_monetario',
        'costo_gestione',
        'durata_ammortamento',
        'durata_progetto',
        'imposte',
        'quota_finanziata',
        'costo_finanziamento',
        'wacc',
        'wacc_ce' => 'wacc_costo_capitale_proprio',
        'wacc_e' => 'wacc_capitale_proprio',
        'wacc_d' => 'wacc_capitale_debito',
        'wacc_cd' => 'wacc_costo_capitale_debito',
        'wacc_t' => 'wacc_aliquota_fiscale_imposte_redditi',
        'quota_comune',
        'quota_istituto_credito',
        'rata_mutuo',
        'quota_esco',
        'canone_esco'
    ];

    private static $calcInvestmentParams = [
        'municipality_name',
        'plant_guid',
        'hp_alias',
        'investment_guid',
        'sections'
    ];


    public function checkUserMunicipality($municipalityName, $municipalities)
    {
        $this->normalizeMunicipalities($municipalities);
        $validRequest = in_array(strtoupper($municipalityName), $municipalities, true);

        if ($validRequest !== true) {
            $this->responseWithStatus(false);
        }
    }

    public function parseInvestmentParams()
    {
        return $this->elaborateRequest(self::$investmentParams, 'post', true);
    }

    public function parseCalcInvestmentParams()
    {
        return $this->elaborateRequest(self::$calcInvestmentParams, 'post');
    }

    public function calcPartialInvestment(array $sections, InvestimentoParametri $investmentParams)
    {
        $result = [];
        /* @var $tobeSect SezioneTOBE */
        /* @var $asisSect SezioneASIS */
        foreach ($sections as $section) {

            $tobeSect = array_get($section, 'tobe');
            $asisSect = array_get($section, 'asis');

            $tobeSect = array_shift($tobeSect);
            $asisSect = array_shift($asisSect);

            /* calcolo costo investimento */
            $importo_investimento = $this->calcolaCostoInvestimento($tobeSect, $asisSect);
            /* calcolo costi/benefici annuali in SPESA energetica */
            $delta_spesa_energetica = $this->calcolaCostiBeneficiSpesaEnergetica_deltaSezioni($tobeSect, $asisSect, $investmentParams->getCostoUnitarioEnergia());
            /* calcolo costi/benefici annuali in CONSUMO energetico, ($costo_unitario_energia = 1) */
            $delta_consumo_energetico = $this->calcolaCostiBeneficiConsumoEnergetico_deltaSezioni($tobeSect, $asisSect);
            /* calcolo incentivi statali */
            $ricavo_incentivi = $this->calcolaIncentiviStatali($delta_consumo_energetico, $investmentParams->getKwhPerTep(), $investmentParams->getValoreMonetarioTep());
            $maintenance_costs = $this->calcolaCostiManutenzione($tobeSect, $asisSect);

            $cash_flow_yearly = $this->calc_cashflow(
                (float)$investmentParams->getDurataAmmortamento(),
                (float)$importo_investimento,
                (float)$delta_spesa_energetica,
                (float)$ricavo_incentivi,
                (float)$investmentParams->getDurataIncentivi(),
                $maintenance_costs,
                (float)$tobeSect->getIntervalloAnniManutenzione(),
                (float)$asisSect->getIntervalloAnniManutenzione(),
                (float)$investmentParams->getCostoGestione(),
                (float)$investmentParams->getCostoFinanziamento(),
                (float)$investmentParams->getWacc()
            );

            $fee_min = $this->calcolaCanoneMinimo($importo_investimento, $investmentParams->getQuotaFinanziata(), $investmentParams->getWacc() / 100, $investmentParams->getDurataProgetto(), $investmentParams->getImposte());
            $fee_max = $this->calcolaCanoneMassimo($importo_investimento, $investmentParams->getQuotaFinanziata(), $investmentParams->getWacc() / 100, $investmentParams->getDurataProgetto(), $delta_spesa_energetica);


            $result [] = [
                'sections' => $section,
                'importo_investimento' => $importo_investimento,
                'delta_consumo_energetico' => $delta_consumo_energetico,
                'delta_spesa_energetica' => $delta_spesa_energetica,
                'maintenance_costs' => $maintenance_costs,
                'cash_flow_yearly' => $cash_flow_yearly,
                'ricavo_incentivi' => $ricavo_incentivi,
                'fee_min' => $fee_min,
                'fee_max' => $fee_max

            ];
        }
        return $result;
    }

    public function calcTotalInvestment(array $partialInvestment, InvestimentoParametri $investmentParams)
    {
        $cashFlowList = $this->extractCashFlowByPartialInvestment($partialInvestment);
        $cashFlowTotal = $this->sumCashFlows($cashFlowList, $investmentParams->getDurataAmmortamento(), $investmentParams->getCostoFinanziamento());
        $cashFlowTotalSerialized = serialize($cashFlowTotal);

        $feeList = $this->extractFeeByPartialInvestment($partialInvestment);
        $feeTotal = $this->sumFeeMinMax($feeList);

        $payback_time = $this->calc_payback_time($cashFlowTotal, $investmentParams->getDurataAmmortamento());

//			$new_cash_flow = new CashflowLib();
        $VAN = CashflowLib::calcVAN_ByCashFlowByWacc($cashFlowTotal, $investmentParams->getWacc());
        $TIR = CashflowLib::calcTIR_ByCashFlow($cashFlowTotal);

        return [
            'cash_flow' => $cashFlowTotalSerialized,
            'fee_min_tot' => array_get($feeTotal, 'min'),
            'fee_max_tot' => array_get($feeTotal, 'max'),
            'van' => $VAN,
            'tir' => $TIR,
            'payback_time' => $payback_time
        ];
    }


    public function recapPaybackTime(array $sections, InvestimentoParametri $investmentParams, $costoUnitarioEnergiaMin, $costoUnitarioEnergiaMax)
    {
        $payback_time_list = [];
        $costo_unitario_energia_list = $this->getCostoUnitarioMinMax($costoUnitarioEnergiaMin, $costoUnitarioEnergiaMax);
        foreach ($costo_unitario_energia_list as $costo_unitario_energia) {

            $cash_flow_yearly = [];
            /* @var $tobeSect SezioneTOBE */
            /* @var $asisSect SezioneASIS */
            foreach ($sections as $section) {

                $tobeSect = array_get($section, 'tobe');
                $asisSect = array_get($section, 'asis');
                $tobeSect = array_shift($tobeSect);
                $asisSect = array_shift($asisSect);

                /* calcolo costo investimento */
                $importo_investimento = $this->calcolaCostoInvestimento($tobeSect, $asisSect);
                /* calcolo costi/benefici annuali in CONSUMO energetico, ($costo_unitario_energia = 1) */
                $delta_consumo_energetico = $this->calcolaCostiBeneficiConsumoEnergetico_deltaSezioni($tobeSect, $asisSect);
                /* calcolo incentivi statali */
                $ricavo_incentivi = $this->calcolaIncentiviStatali($delta_consumo_energetico, $investmentParams->getKwhPerTep(), $investmentParams->getValoreMonetarioTep());
                $maintenance_costs = $this->calcolaCostiManutenzione($tobeSect, $asisSect);

                /* calcolo costi/benefici annuali in SPESA energetica */
                $delta_spesa_energetica = $this->calcolaCostiBeneficiSpesaEnergetica_deltaSezioni($tobeSect, $asisSect, $costo_unitario_energia);
                $cash_flow_yearly[] = $this->calc_cashflow(
                    (float)$investmentParams->getDurataAmmortamento(),
                    (float)$importo_investimento,
                    (float)$delta_spesa_energetica,
                    (float)$ricavo_incentivi,
                    (float)$investmentParams->getDurataIncentivi(),
                    $maintenance_costs,
                    (float)$tobeSect->getIntervalloAnniManutenzione(),
                    (float)$asisSect->getIntervalloAnniManutenzione(),
                    (float)$investmentParams->getCostoGestione(),
                    (float)$investmentParams->getCostoFinanziamento(),
                    (float)$investmentParams->getWacc()
                );
            }

            $cashFlowTotal = $this->sumCashFlows($cash_flow_yearly, $investmentParams->getDurataAmmortamento(), $investmentParams->getCostoFinanziamento());

            $payback_time = $this->calc_payback_time($cashFlowTotal, $investmentParams->getDurataAmmortamento());

            $payback_time_list[] = [
                'x' => $costo_unitario_energia,
                'y' => $payback_time
            ];
        }
        return $payback_time_list;
    }

    public function recapFeeMinMax(array $sections, InvestimentoParametri $investmentParams, $durataProgettoMin, $durataProgettoMax, $imposte, $quotaFinanziata)
    {
        $feeTotal = [];
        $durata_progetto_list = $this->getDurataProgettoMinMax($durataProgettoMin, $durataProgettoMax);

        foreach ($durata_progetto_list as $durata_progetto) {
            $feeList = [];
            /* @var $tobeSect SezioneTOBE */
            /* @var $asisSect SezioneASIS */
            foreach ($sections as $section) {

                $tobeSect = array_get($section, 'tobe');
                $asisSect = array_get($section, 'asis');
                $tobeSect = array_shift($tobeSect);
                $asisSect = array_shift($asisSect);

                /* calcolo costo investimento */
                $importo_investimento = $this->calcolaCostoInvestimento($tobeSect, $asisSect);
                /* calcolo costi/benefici annuali in SPESA energetica */
                $delta_spesa_energetica = $this->calcolaCostiBeneficiSpesaEnergetica_deltaSezioni($tobeSect, $asisSect, $investmentParams->getCostoUnitarioEnergia());

                $fee_min = $this->calcolaCanoneMinimo($importo_investimento, $quotaFinanziata, $investmentParams->getWacc() / 100, $durata_progetto, $imposte);
                $fee_max = $this->calcolaCanoneMassimo($importo_investimento, $quotaFinanziata, $investmentParams->getWacc() / 100, $durata_progetto, $delta_spesa_energetica);

                $feeList ['min'] [] = $fee_min;
                $feeList ['max'] [] = $fee_max;
            }

            $feeTotal[] = [
                'x' => $durata_progetto,
                'y' => $this->sumFeeMinMax($feeList)
            ];
        }

        return $feeTotal;
    }

    public function recapVANTIR(array $sections, InvestimentoParametri $investmentParams, array $WACC_list, array $duration_list)
    {
        $result = [];

        foreach ($duration_list as $durata_ammortamento) {
            $cashFlowTotal = [];
            foreach ($WACC_list as $WACC) {

                $cash_flow_yearly = [];
                /* @var $tobeSect SezioneTOBE */
                /* @var $asisSect SezioneASIS */
                foreach ($sections as $section) {

                    $tobeSect = array_get($section, 'tobe');
                    $asisSect = array_get($section, 'asis');
                    $tobeSect = array_shift($tobeSect);
                    $asisSect = array_shift($asisSect);

                    /* calcolo costo investimento */
                    $importo_investimento = $this->calcolaCostoInvestimento($tobeSect, $asisSect);
                    /* calcolo costi/benefici annuali in CONSUMO energetico, ($costo_unitario_energia = 1) */
                    $delta_consumo_energetico = $this->calcolaCostiBeneficiConsumoEnergetico_deltaSezioni($tobeSect, $asisSect);
                    /* calcolo incentivi statali */
                    $ricavo_incentivi = $this->calcolaIncentiviStatali($delta_consumo_energetico, $investmentParams->getKwhPerTep(), $investmentParams->getValoreMonetarioTep());
                    $maintenance_costs = $this->calcolaCostiManutenzione($tobeSect, $asisSect);

                    /* calcolo costi/benefici annuali in SPESA energetica */
                    $delta_spesa_energetica = $this->calcolaCostiBeneficiSpesaEnergetica_deltaSezioni($tobeSect, $asisSect, $investmentParams->getCostoUnitarioEnergia());
                    $cash_flow_yearly[] = $this->calc_cashflow(
                        (float)$durata_ammortamento,
                        (float)$importo_investimento,
                        (float)$delta_spesa_energetica,
                        (float)$ricavo_incentivi,
                        (float)$investmentParams->getDurataIncentivi(),
                        $maintenance_costs,
                        (float)$tobeSect->getIntervalloAnniManutenzione(),
                        (float)$asisSect->getIntervalloAnniManutenzione(),
                        (float)$investmentParams->getCostoGestione(),
                        (float)$investmentParams->getCostoFinanziamento(),
                        (float)$WACC
                    );

                }

                $cashFlowTotal = $this->sumCashFlows($cash_flow_yearly, $durata_ammortamento, $investmentParams->getCostoFinanziamento());

                $VAN = CashflowLib::calcVAN_ByCashFlowByWacc($cashFlowTotal, $WACC);
                $result['t'][$durata_ammortamento]['r'][$WACC]['VAN'] = round($VAN, 3);;

            }
            /*
             * TIR does not change for different WACC values
             */
            $TIR = (float)CashflowLib::calcTIR_ByCashFlow($cashFlowTotal) * 100;
            if ($TIR < 0 || $TIR > 100) {
                $TIR = 0;
            }
            $result['t'][$durata_ammortamento]['TIR'] = round($TIR, 3);
        }
        return $result;
    }


    private function getCostoUnitarioMinMax($min, $max)
    {
        $costo_unitario_energia_list = [];
        $step = ((float)$max - (float)$min) / 10;

        for ($i = 0; $i < 10; $i++) {
            $costo_unitario_energia_list [] = (float)$min;
            $min = (float)$min + $step;
        }
        $costo_unitario_energia_list[] = (float)$max;
        return $costo_unitario_energia_list;
    }

    private function getDurataProgettoMinMax($duration_min, $duration_max)
    {
        $durata_progetto_list = [];
        $step = ((int)$duration_max - (int)$duration_min) / 10;

        for ($i = 0; $i < 10; $i++) {
            $durata_progetto_list [] = (int)$duration_min;
            $duration_min += $step;
        }
        $durata_progetto_list[] = (int)$duration_max;
        return $durata_progetto_list;
    }

    private function calc_payback_time($cashFlow, $durata_ammortamento)
    {


        $payback_time = 0;
        $flusso_cumulativo[0] = $cashFlow[0];

        /*
         * calcolo flusso cumulativo
         */
        for ($i = 1; $i < $durata_ammortamento + 1; $i++) {
            $flusso_cumulativo[$i] = $cashFlow[$i] + $flusso_cumulativo[$i - 1];
        }

        /*
         * ultimo flusso di cassa cumulativo negativo
         */
        for ($i = $durata_ammortamento + 1; $i > 0; $i--) {
            if (isset($flusso_cumulativo[$i])) {
                if ($flusso_cumulativo[$i] < 0) {
                    $payback_time = $i;
                    break;
                }
            }
        }

        if ($payback_time > 0 && isset($cashFlow[$payback_time + 1]) && (int)$cashFlow[$payback_time + 1] !== 0) {
            $payback_time += (abs($flusso_cumulativo[$payback_time]) / $cashFlow[$payback_time + 1]);
        } else {
            $payback_time = 0;
        }

        return $payback_time;
    }

    private function calcolaCanoneMinimo($importo_investimento, $quota_finanziata, $wacc, $durata_progetto, $imposte)
    {
        $result = 0;
        $investimento_iniziale = $importo_investimento * ($quota_finanziata / 100);

        if ((float)$wacc !== 0 && (int)$durata_progetto !== 0) {
            if ((floatval($wacc) > 0) || (floatval($wacc) < 0)) {
                $canone_iniziale = $investimento_iniziale / ((1 - ((1 + $wacc) ** (-$durata_progetto))) / $wacc);
                $ammortamento = $investimento_iniziale / $durata_progetto;

                if ((1 - $imposte * 100) !== 0) {
                    $result = ($canone_iniziale - $ammortamento * $imposte / 100) / (1 - $imposte / 100);
                }
            }


        }
        return round($result, 3);
    }

    private function calcolaCanoneMassimo($importo_investimento, $quota_finanziata, $wacc, $durata_progetto, $delta_spesa_energica)
    {
        $result = 0;
        if ((1 - $quota_finanziata / 100) !== 0) {
            $investimento_iniziale_comune = $importo_investimento * (1 - $quota_finanziata / 100);
            if ((floatval($wacc) > 0) || (floatval($wacc) < 0)) {
                $canone_van_nullo = $investimento_iniziale_comune / ((1 - ((1 + $wacc) ** (-$durata_progetto))) / $wacc);
                $result = $delta_spesa_energica - $canone_van_nullo;
            }
        }
        return round($result, 3);
    }

    private function extractCashFlowByPartialInvestment(array $partialInvestment)
    {
        $cashFlows = [];
        foreach ($partialInvestment as $inv) {
            $cashFlows [] = array_get($inv, 'cash_flow_yearly');
        }
        return $cashFlows;
    }

    private function extractFeeByPartialInvestment(array $partialInvestment)
    {
        $fees = [];
        foreach ($partialInvestment as $inv) {
            $fees ['min'][] = array_get($inv, 'fee_min');
            $fees ['max'][] = array_get($inv, 'fee_max');
        }
        return $fees;
    }

    private function calcolaCostoInvestimento(SezioneTOBE $sezTOBE, SezioneASIS $sezASIS)
    {

        $partialSum = (float)$sezTOBE->getCostoLampada();
        $partialSum += (float)$sezTOBE->getCostoCompElettronica();
        $partialSum += (float)$sezTOBE->getCostoSistemaControllo();
        $partialSum += (float)$sezTOBE->getCostoArmatura();
        $partialSum += (float)$sezTOBE->getCostoPalo();
        $partialSum += (float)$sezASIS->getCostoSmaltimentoLampada();

        $result = ($partialSum * (int)$sezTOBE->getNLampade())
            + (float)$sezTOBE->getCostoRifacimentoImpiantoElett()
            + (float)$sezTOBE->getCostoAttivitaProdromiche()
            + ($sezTOBE->getNQuadriEl() * $sezTOBE->getCostoQuadro());

        $services = $sezTOBE->getServices();
        /* @var $service ServiziTOBE */
        foreach ($services as $service) {
            if (is_numeric($service->getCostoInstallazione())) {
                $result += (float)$service->getCostoInstallazione();
            }
        }

        return $result;
    }

    /* calcolo costi/benefici annuali in CONSUMO energetico, ($costo_unitario_energia = 1) */
    private function calcolaCostiBeneficiConsumoEnergetico_deltaSezioni(SezioneTOBE $sezTOBE, SezioneASIS $sezASIS)
    {
        $cost_benefit_price_tobe = $this->calcolaCostiBeneficiSpesaEnergeticaAnnuali($sezTOBE, 1);
        $cost_benefit_price_asis = $this->calcolaCostiBeneficiSpesaEnergeticaAnnuali($sezASIS, 1);

        return $cost_benefit_price_asis - $cost_benefit_price_tobe;
    }

    private function calcolaCostiBeneficiSpesaEnergetica_deltaSezioni(SezioneTOBE $sezTOBE, SezioneASIS $sezASIS, $energy_cost_unit)
    {
        $cost_benefit_price_tobe = $this->calcolaCostiBeneficiSpesaEnergeticaAnnuali($sezTOBE, $energy_cost_unit);
        $cost_benefit_price_asis = $this->calcolaCostiBeneficiSpesaEnergeticaAnnuali($sezASIS, $energy_cost_unit);

        return $cost_benefit_price_asis - $cost_benefit_price_tobe;
    }


    private function calcolaCostiBeneficiSpesaEnergeticaAnnuali($sez, $energy_cost_unit)
    {

        /* @var $sez SezioneTOBE SezioneASIS */
        if ((float)$energy_cost_unit > 0) {
            if ((float)$sez->getFattoreEfficienzaImpianto() > 0) {
                $partial_A = (float)$sez->getPotenzaNominale() * (float)$sez->getOreAccensione() / (float)$sez->getFattoreEfficienzaImpianto();
                $partial_B = (float)$sez->getPotenzaNominale() * (1 - ((float)$sez->getPercentualeDimmering() / 100)) * (float)$sez->getOreAccensioneDimmering() / (float)$sez->getFattoreEfficienzaImpianto();

            } else {
                $partial_A = 0;
                $partial_B = 0;
            }

            return (($partial_A + $partial_B) / 1000) * ((int)$sez->getNLampade() * (float)$energy_cost_unit);
        }

        return 0;
    }

    private function calcolaIncentiviStatali($energetic_consumption_delta, $kwh_pet, $valore_monetario_tep)
    {
        if ((int)$kwh_pet !== 0) {
            return (float)$energetic_consumption_delta / (float)$kwh_pet * (float)$valore_monetario_tep;
        }

        return 0;
    }

    private function calcolaCostiManutenzione(SezioneTOBE $sezTOBE, SezioneASIS $sezASIS)
    {

        $maintenance_cost_ASIS = ((float)$sezASIS->getCostoLampada()) * (int)$sezASIS->getNLampade();
        $maintenance_cost_TOBE = ((float)$sezTOBE->getCostoLampada() + (float)$sezTOBE->getCostoArmatura() + (float)$sezTOBE->getCostoCompElettronica()) * (int)$sezTOBE->getNLampade();

        $maintenance = [
            'asis' => $maintenance_cost_ASIS,
            'asis_duration' => $sezASIS->getIntervalloAnniManutenzione(),
            'tobe' => $maintenance_cost_TOBE,
            'tobe_duration' => $sezTOBE->getIntervalloAnniManutenzione(),
        ];
        return $maintenance;
    }

    private function calc_cashflow($duration, $investmentPrice, $deltaEnergeticCost, $incentiveIncomeRevenue, $incentiveIncomeRange, $maintenanceCosts, $maintenanceDuration_TOBE, $maintenanceDuration_ASIS, $managementPrice, $financingPrice, $wacc)
    {
        $maintenanceCost_ASIS = array_get($maintenanceCosts, 'asis', 0);
        $maintenanceCost_TOBE = array_get($maintenanceCosts, 'tobe', 0);

        $cashFlowParams = compact(
            'duration',
            'investmentPrice',
            'deltaEnergeticCost',
            'incentiveIncomeRevenue',
            'incentiveIncomeRange',
            'maintenanceCost_ASIS',
            'maintenanceDuration_ASIS',
            'maintenanceCost_TOBE',
            'maintenanceDuration_TOBE',
            'managementPrice',
            'financingPrice',
            'wacc');

        $new_cash_flow = new CashflowLib($cashFlowParams);
        $new_cash_flow->calcCashFlow();
        return $new_cash_flow->getCashValue();

    }

    private function sumCashFlows(array $cashFlowList, $durataInvestimento, $costoFinaziamento)
    {
        $cashFlowResult = [];

        for ($year = 0; $year <= $durataInvestimento; $year++) {
            $cashFlowPerYear = 0;
            foreach ($cashFlowList as $cashFlow) {
                $cashFlowPerYear += $cashFlow[$year];
            }
            $cashFlowResult [$year] = $cashFlowPerYear;
            if ($year > 0) {
                $cashFlowResult [$year] -= (float)$costoFinaziamento;
            }
        }
        return $cashFlowResult;
    }

    private function sumFeeMinMax(array $feeList)
    {
        $feeMin = array_sum(array_get($feeList, 'min', []));
        $feeMax = array_sum(array_get($feeList, 'max', []));

        return [
            'min' => $feeMin,
            'max' => $feeMax
        ];
    }
}
