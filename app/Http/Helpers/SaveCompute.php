<?php
namespace App\Http\Helpers;


use App\Models\DTO\SaveCluster;
use App\Models\DTO\SaveHA;
use App\Models\DTO\SaveInvestment;
use App\Models\DTO\SavePlant;
use App\Models\ResultSingleHA;
use App\Models\SavePlantView;
use MathPHP;

class SaveCompute
{

    /**
     * Calcola Costo Investimento sommando l'importo di ogni ZO
     *
     * @input costo_medio_lampada               -> $has[lamp_cost]
     * @input costo_infrastruttura              ->    non chiaro, forse $has[infrastructure_maintenance_cost]
     * @input costo_medio_smaltimento_lampada   -> $has[lamp_disposal]
     * @input n_lampade                         -> $cluster[lamp_num]
     * @input costo_rifacimento_imp_elett       -> $has[system_renovation_cost] colonna da aggiungere
     * @input costo_attività_prodomiche         -> $has[prodromal_activities_cost]
     * @input costo_quadro                      -> $has[panel_cost]
     * @input n_quadri_el                       -> $has[panel_num]
     *
     * Utilizzato solo per le HAS TOBE
     * */




    /**
     * Calcolo Costi/benefici annuali in consumo energetico della ZO
     * sommando i valori degli Cluster che gli appartengono (per HAS ASIS che hanno una lista di cluster ASIS associati)
     *
     * @input ore_acc_piena     ->$cluster[hours_full_light]
     * @input %dimm             ->$cluster[dimmering]
     * @input ore_dimm          ->$cluster[hours_dimmering_light]
     * @input n_apparecchi      ->$cluster[device_num]
     * @input potenza_m_morsett ->$cluster[average_device_power]
     * */

    public static function computeHaAsisEnergyConsumption($ha){

        $clusters = SaveCompute::getClustersByHaId($ha["id"])["clusters"];

        $energyConsumptionHa = 0;

        //sommatoria per ogni cluster
        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            $energyConsumption = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmering_light"]) * $cluster["device_num"]
                * $cluster["average_device_power"];

            $energyConsumptionHa += $energyConsumption;
        }

        return $energyConsumptionHa;
    }

    /**
     * Calcolo Costi/benefici annuali in consumo energetico della ZO TOBE
     * essendo singola non ha bisogno di un calcolo ricorsivo
     *
     * @input ore_acc_piena     ->$cluster[hours_full_light]
     * @input %dimm             ->$cluster[dimmering]
     * @input ore_dimm          ->$cluster[hours_dimmering_light]
     * @input n_apparecchi      ->$cluster[device_num]
     * @input potenza_m_morsett ->$cluster[average_device_power]
     * */

    public static function computeHaTobeEnergyConsumption($ha){
        $result = 0;
        $resultQuery = SaveCompute::getClustersByHaId_TOBEfeatured($ha["id"]);
        if ($resultQuery["success"] === true) {
            $cluster = $resultQuery["data"];
            $result = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmering_light"]) * $cluster["device_num"] * $cluster["average_device_power"];
        }
        return $result;
    }


    /**
     * Calcolo Costi/benefici annuali in consumo energetico come Delta tra
     * la sommatoria delle ZO AS-IS e TO-BE
     * il risultato è un DELTA tra due ZO associate e non un aggregato
     *
     * @input $plant
     * @input calcoloConsumoEnergeticoPerHa($has)
     * */
    public static function computeDeltaAsisEnergyConsumption($haASIS, $haTOBE, $result)
    {
        $value = SaveCompute::computeHaAsisEnergyConsumption($haASIS) - SaveCompute::computeHaTobeEnergyConsumption($haTOBE);

        //posiziona l'elaborazione nella cella specifica
        return collect($result)->each(function (ResultSingleHA $single) use ($value, $haASIS){
            if($single->getAsisName() == $haASIS["label_ha"]){
                $single->setDeltaEnergyConsumption($value);
                return false;
            }
        });
    }


    /**
     * Calcola costi/benefici annuali in spesa energetica per ZO ASIS aggregando tutti i suoi cluster
     *
     * @input $ha
     * @input $costo_unitario
     * */
    public static function computeHaAsisEnergyExpenditure($ha, $unit_cost){

        //prendo tutti i cluster che appartengono alla zona omogenea con un certo id
        $clusters = SaveCompute::getClustersByHaId($ha["id"])["clusters"];

        $energyExpenditureHa = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            //calcolo spesa energetica i-esimo cluster
            $energyExpenditure = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmering_light"]) * $cluster["device_num"] * $cluster["average_device_power"] * ((float)$unit_cost / 1000);

            //somma delle singole spese energetiche in quella generale della zona omogenea
            $energyExpenditureHa += $energyExpenditure;
        }

        return $energyExpenditureHa;
    }

    /**
     * Calcola costi/benefici annuali in spesa energetica per ZO TOBE, essendocene una sola non ho bisogno di aggregare tutti i cluster
     *
     * @input $ha
     * @input $costo_unitario
     * */
    public static function computeHaTobeEnergyExpenditure($ha, $unit_cost){
        $result = 0;
        $resultQuery = SaveCompute::getClustersByHaId_TOBEfeatured($ha["id"]);
        if ($resultQuery["success"] === true) {
            $cluster = $resultQuery["data"];
            $result = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmering_light"]) * $cluster["device_num"] * $cluster["average_device_power"] * ((float)$unit_cost / 1000);
        }
        return $result;
    }


    /**
     * Calcolo Costi/benefici annuali in spesa energetica come Delta tra
     * la sommatoria delle ZO AS-IS e TO-BE
     * il risultato è un DELTA tra due ZO associate e non un aggregato
     *
     * @input $plant
     * @input calcoloSpesaEnergeticoPerHa($has)
     * */

    public static function computeDeltaAsisEnergyExpenditure($haASIS, $haTOBE, $energyCost, $result)
    {
        $value = SaveCompute::computeHaAsisEnergyExpenditure($haASIS, $energyCost) - SaveCompute::computeHaTobeEnergyExpenditure($haTOBE, $energyCost);

        return collect($result)->each(function (ResultSingleHA $single) use ($value, $haASIS){
           if($single->getAsisName() == $haASIS["label_ha"]){
               $single->setDeltaEnergyExpenditure($value);
               return false;
           }
        });

    }
    /**
     * Calcolo Costi/benefici annuali in spesa energetica come Delta tra
     * la sommatoria delle ZO AS-IS e TO-BE
     * il risultato è UN AGGREGATO di tutte le ZO (e non un array con i singoli delta)
     *
     * @input $plant
     * @input calcoloSpesaEnergeticoPerHa($has)
     * */

    public static function computeDeltaPlantEnergyExpenditure($plant, $investment)
    {
        $data = SaveCompute::getHasByPlantId($plant["id"]);
        $energyCost = (SaveCompute::getEnergyUnitCostForInvestment($investment["id"]))["energy_unit_cost"];

        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $arrayTOBE = $data["dataToBe"];
        $result = 0;
        for ($i = 0; $i < count($arrayASIS); $i++) {
            //per ogni $haASIS
            $haASIS = $arrayASIS[$i];

            //cerco la HA TOBE associata
            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_as_is_id_ha"] == $haASIS["id"];
            })->first();

            //prendo tutte le ZO AS-IS, sommatoria CU e calcolo - prendo solo la TO-BE associata e sottraggo
            $value = SaveCompute::computeHaAsisEnergyExpenditure($haASIS, $energyCost) - SaveCompute::computeHaTobeEnergyExpenditure($haTOBE, $energyCost);

            //sommo per aggregare i risultati
            $result += $value;
        }

        //restituisco il risultato
        return $result;
    }


    /**
     * calcola il totale lampade per HA in base ai cluster
     *
     * @input $ha
     * */

    public static function computeHaTotalLamp($ha) {
        $clusters = SaveCompute::getClustersByHaId($ha["id"])["clusters"];

        $nLampTot = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];
            $nLampTot += $cluster["lamp_num"];
        }

        return $nLampTot;
    }

    public static function computeHaManteinanceCost($ha){
        return ($ha["lamp_cost"] + $ha["lamp_disposal"]) * SaveCompute::computeHaTotalLamp($ha);
    }

    public static function computeHaInfrastructureManteinanceCost($ha){
        return $ha["infrastructure_maintenance_cost"] * SaveCompute::computeHaTotalLamp($ha);
    }

    public static function computePlantVan($cashFlow, $wacc, $round = 3): float
    {

        $wacc_absolute = (float)$wacc / 100;

        $result = 0;
        $totVal = count($cashFlow);

        for ($i = 0; $i < $totVal; $i++) {
            $result += $cashFlow[$i] / ((1 + $wacc_absolute)**$i);
        }
        return round ( $result, $round);
    }

    public static function computePlantTir($cashFlow, $investment_amount): ?float
    {
        $maxIterations = 100;
        $tolerance = 0.00001;
        $guess = 0.1;

        $count = count($cashFlow);

        $cashFlow[0] = $investment_amount;

        $positive = false;
        $negative = false;
        for ($i = 0; $i < $count; $i++) {
            if ($cashFlow[$i] > 0) {
                $positive = true;
            } else {
                $negative = true;
            }
        }

        if (!$positive || !$negative) {
            return null;
        }

        $guess = ($cashFlow == 0) ? 0.1 : $guess;

        for ($i = 0; $i < $maxIterations; $i++) {
            $npv = 0;
            $dnpv = 0.00;

            for ($j = 0; $j < $count; $j++) {
                $npv += $cashFlow[$j] / pow(1 + $guess, $j);
                $dnpv -= $j * $cashFlow[$j] / pow(1 + $guess, $j + 1);
            }

            if($dnpv != 0)
                $newGuess = $guess - ($npv / $dnpv);
            else
                return 0;

            if (abs($newGuess - $guess) < $tolerance) {
                return $newGuess;
            }

            $guess = $newGuess;
        }

        return $guess;
    }

    public static function computePayBackTime($cashFlowTotal, $amortization_duration){
        $payback_time = 0;
        $cumulateFlow[0] = $cashFlowTotal[0];

        /*
         * calcolo flusso cumulativo
         */
        for ($i = 1; $i < $amortization_duration + 1; $i++) {
            $cumulateFlow[$i] = $cashFlowTotal[$i] + $cumulateFlow[$i - 1];
        }

        /*
         * ultimo flusso di cassa cumulativo negativo
         */
        for ($i = $amortization_duration + 1; $i > 0; $i--) {
            if (isset($cumulateFlow[$i])) {
                if ($cumulateFlow[$i] < 0) {
                    $payback_time = $i;
                    break;
                }
            }
        }

        if ($payback_time > 0 && isset($cashFlowTotal[$payback_time + 1]) && (int)$cashFlowTotal[$payback_time + 1] !== 0) {
            $payback_time += (abs($cashFlowTotal[$payback_time + 1]) / $cumulateFlow[$payback_time]);
        } else
        {$payback_time = 0;}

        return ($payback_time > 0)? $payback_time : 0;
    }

    public static function computeFeeMin($investmentAmount, $investment, $feeDuration, $taxes, $financedQuote){
        if(!$feeDuration)
            $feeDuration = $investment["project_duration"];
        if(!$taxes)
            $taxes = $investment["taxes"];
        if(!$financedQuote)
            $financedQuote = $investment["share_esco"];

        $wacc_absolute = floatval($investment["wacc"] / 100);
        $investment_ESCO = $investmentAmount * ($financedQuote/100);
        $inital_fee = ($investment_ESCO) / ((1-((1+$wacc_absolute)**(-$feeDuration))) /$wacc_absolute);

        $amortization = $investment_ESCO / $feeDuration;
        $result = ($inital_fee - $amortization * $taxes / 100) / (1- $taxes / 100);
        if($result > 0)
            return $result;
        else
            return 0;
    }

    public static function computeFeeMax($plant, $investmentAmount, $investment, $feeDuration, $financedQuote){
        if(!$feeDuration)
            $feeDuration = $investment["project_duration"];
        if(!$financedQuote)
            $financedQuote = $investment["share_esco"];

        $wacc_absolute = floatval($investment["wacc"] / 100);
        $common_initial_investment = $investmentAmount * ($financedQuote/ 100);
        $common_amortization = $common_initial_investment / ((1-((1+$wacc_absolute)**(-$feeDuration))) /$wacc_absolute);
        $result = self::computeDeltaPlantEnergyExpenditure($plant, $investment) - ($common_amortization - $investment["mortgage_installment"]);
        if($result > 0)
            return $result;
        else
            return 0;
    }

    public static function computeHaCashFLow($plant, $investment, $durationAmortization_override, $energy_unit_cost_override) {
        $has = SaveCompute::getHasByPlantId($plant["id"]);
        //creazione array delle HAS
        $arrayASIS = $has["dataAsIs"];
        $arrayTOBE = $has["dataToBe"];

        $result = [];

        if($energy_unit_cost_override)
            $energyCost = $energy_unit_cost_override;
        else
            //prendo il costo unitario dell'energia per l'investimento (inserito dall'utente
            $energyCost = (SaveCompute::getEnergyUnitCostForInvestment($investment["id"]))["energy_unit_cost"];

        for ($i = 0; $i < count($arrayASIS); $i++){
            //inizializzazione oggetto di output
            $result[$i] = new ResultSingleHA();

            //singola HA ASIS
            $haASIS = $arrayASIS[$i];
            $result[$i]->setAsisName($haASIS["label_ha"]);

            //getting TOBE associata
            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_as_is_id_ha"] == $haASIS["id"];
            })->first();

            $result[$i]->setTobeName($haTOBE["label_ha"]);
            //fine inizializzazione oggetto di output

            //inizio calcolo
            //calcolo costo investimento
            $result[$i]->setInvestmentAmount(SaveCompute::computeHaInvestmentAmount($haTOBE) * $investment["share_municipality"] /100);

            //Calcola costi/benefici annuali in consumo energetico
            SaveCompute::computeDeltaAsisEnergyConsumption($haASIS, $haTOBE, $result);
            //Calcola costi/benefici annuali in spesa energetica
            SaveCompute::computeDeltaAsisEnergyExpenditure($haASIS, $haTOBE, $energyCost, $result);

            //Calcola incentivi statali
            $result[$i]->setIncentiveRevenue(
                ($result[$i]->getDeltaEnergyConsumption() > 0)?
                ($result[$i]->getDeltaEnergyConsumption() / $investment["tep_kwh"]) * $investment["tep_value"] : 0);

            //Calcola costi manutenzione
            //calcolo flussi e totale costo manutenzione ASIS e TOBE
            ($durationAmortization_override) ? $durationAmortization = $durationAmortization_override : $durationAmortization = $investment["duration_amortization"];
            for($j = 1; $j <= $durationAmortization; $j++) {
                //costo
                $result_asis_maintenance_cost[$j] = SaveCompute::computeHaManteinanceCost($haASIS) * (($j % $haASIS["lamp_maintenance_interval"] == 0) ? 1 : 0);
                $result_tobe_lamp_cost[$j] = SaveCompute::computeHaManteinanceCost($haTOBE) * (($j % $haTOBE["lamp_maintenance_interval"] == 0) ? 1 : 0);
                $result_tobe_infrastructure_cost[$j] = SaveCompute::computeHaInfrastructureManteinanceCost($haTOBE) * (($j % $haTOBE["lamp_maintenance_interval"] == 0) ? 1 : 0);
            }
            $result[$i]->asis_maintenance_cost = array_sum($result_asis_maintenance_cost);
            $result[$i]->tobe_maintenance_cost = array_sum($result_tobe_infrastructure_cost) + array_sum($result_tobe_lamp_cost);

            //Calcola flussi di cassa annuali
            $result[$i]->cash_flow[0] = - $result[$i]->getInvestmentAmount();
            for($j = 1; $j <= $durationAmortization; $j++) {
                //costo
                $result[$i]->cash_flow[$j] = + $result[$i]->getDeltaEnergyExpenditure()
                    + $result_asis_maintenance_cost[$j] - $investment["mortgage_installment"]
                    - $investment["fee_esco"] - $result_tobe_lamp_cost[$j]
                    - $result_tobe_infrastructure_cost[$j] - $investment["management_cost"];
            }

            for($j = 1; $j <= $investment["incentives_duration"]; $j++){
                $result[$i]->cash_flow[$j] += $result[$i]->getIncentiveRevenue();
            }
        }

        return $result;
    }

    public static function computePlantCashFLow($resultSingleHa){

        //calcolo totali
        $cashFlowTotale = [];

        if (!(isset($resultSingleHa[0]))) {
            return $cashFlowTotale;
        }
        //iniziializzazione array
        for($j = 0; $j<count($resultSingleHa[0]->cash_flow); $j++) {
            $cashFlowTotale[$j] = 0;
        }

        //calcolo cashflow totale per calcolo VAN, TIR e Payback
        for($i = 0; $i<count($resultSingleHa); $i++){
            for($j = 0; $j<count($resultSingleHa[$i]->cash_flow); $j++){
                $cashFlowTotale[$j] += $resultSingleHa[$i]->cash_flow[$j];
            }
        }

        return $cashFlowTotale;
    }

    public static function computePlantInvestmentAmountTotal($resultsSingleHa){
        $result = 0;
        for($i = 0; $i<count($resultsSingleHa); $i++){
            $result += $resultsSingleHa[$i]->getInvestmentAmount();
        }
        return $result;
    }

    public static function computePlantAsisManteinanceCost($resultsSingleHa){
        $result = 0;
        for($i = 0; $i<count($resultsSingleHa); $i++){
            $result += $resultsSingleHa[$i]->getAsisMaintenanceCost();
        }
        return $result;
    }

    public static function computePlantTobeManteinanceCost($resultsSingleHa){
        $result = 0;
        for($i = 0; $i<count($resultsSingleHa); $i++){
            $result += $resultsSingleHa[$i]->getTobeMaintenanceCost();
        }
        return $result;
    }

    public static function computePlantIncentiveContribution($resultsSingleHa){
        $result = 0;
        for($i = 0; $i<count($resultsSingleHa); $i++){
            $result += $resultsSingleHa[$i]->getIncentiveRevenue();
        }
        return $result;
    }

    public static function computeDeltaPlantEnergyExpenditureTotal($resultsSingleHa){
        $result = 0;
        for($i = 0; $i<count($resultsSingleHa); $i++){
            $result += $resultsSingleHa[$i]->getDeltaEnergyExpenditure();
        }
        return $result;
    }

    public static function computeDeltaPlantEnergyConsumptionTotal($resultsSingleHa){
        $result = 0;
        for($i = 0; $i<count($resultsSingleHa); $i++){
            $result += $resultsSingleHa[$i]->getDeltaEnergyConsumption();
        }
        return $result;
    }

    public static function emulatePlantsOutput($id_plant) {

        $plantObj = SavePlantView::where("id",$id_plant)->first();
        $data = [
            "municipality"  => $plantObj["municipality"],
            "plants" => [
                [
                    "asis_name" => "Garibaldi",
                    "tobe_name" => "Garibaldi to be",
                    "investment_amount"         => 11382,
                    "asis_maintenance_cost"     => 2321.54,
                    "tobe_maintenance_cost"     => 5980,
                    "incentive_revenue"         => 164.70,
                    "delta_energy_expenditure"  => 1673.44,
                    "delta_energy_consumption"  => 8807.55,
                    "cash_flow"                 => [
                        -11382,
                        1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,
                        1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14,1838.14
                    ],
                ],
                [
                    "asis_name" => "Verdi",
                    "tobe_name" => "Verdi to be",
                    "investment_amount"         => 17500,
                    "asis_maintenance_cost"     => 4464.50,
                    "tobe_maintenance_cost"     => 11500,
                    "incentive_revenue"         => 351.17,
                    "delta_energy_expenditure"  => 3568,
                    "delta_energy_consumption"  => 18778.97,
                    "cash_flow"                 => [
                        -17500,
                        3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,
                        3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18,3919.18
                    ],
                ]
            ],
            "total" => [
                "investment_amount"         => 0,
                "asis_maintenance_cost"     => 0,
                "tobe_maintenance_cost"     => 0,
                "incentive_revenue"         => 0,
                "delta_energy_expenditure"  => 0,
                "delta_energy_consumption"  => 0,
                "cash_flow"                 => [0,
                    0,0,0,0,0,0,0,0,0,0,
                    0,0,0,0,0,0,0,0,0,0
                ]
            ],
            "financement" => [
                "van"           => 88580.08,
                "tir"           => 23,
                "payback_time"  => 3.9,
                "fee_min"       => 1295.55,
                "fee_max"       => 4877.52
            ]
        ];

        $no_sum = ["asis_name","tobe_name"];

        foreach ($data["plants"] as $indexPlant => $plant) {
            foreach ($plant as $keyPlant => $valuePlant) {
                if (in_array($keyPlant,$no_sum)) {
                    continue;
                }
                if ($keyPlant == "cash_flow") {
                    foreach ($valuePlant as $indexCash => $valueCash) {
                        $data["total"][$keyPlant][$indexCash] = $data["total"][$keyPlant][$indexCash] + $valueCash;
                    }
                } else {
                    $data["total"][$keyPlant] = $data["total"][$keyPlant] + $valuePlant;
                }
            }
        }

        return $data;
    }

    public static function computePlantsOutput($plantInvestmentInput){
        $result = [
            "success"   => false,
            "municipality" => [],
            "plants" => [],
            "total" => [],
            "financement" => []
        ];

        $plant      = SaveCompute::getPlantById($plantInvestmentInput["plant_id"])["plant"];
        $investment = SaveCompute::getInvestmentById($plantInvestmentInput["investment_id"])["investment"];



        $result["municipality"] = $plant["label_plant"];
        $result["plants"] = self::computeHaCashFLow($plant, $investment, null, null);

        //calcolo totali
        $cashFlowTotale = self::computePlantCashFLow($result["plants"]);
        $result["total"]["cash_flow"] = $cashFlowTotale;
        $result["total"]["investment_amount"] = self::computePlantInvestmentAmountTotal($result["plants"]);
        $result["total"]["asis_maintenance_cost"] = self::computePlantAsisManteinanceCost($result["plants"]);
        $result["total"]["tobe_maintenance_cost"] = self::computePlantTobeManteinanceCost($result["plants"]);
        $result["total"]["incentive_revenue"] = self::computePlantIncentiveContribution($result["plants"]);
        $result["total"]["delta_energy_expenditure"] = self::computeDeltaPlantEnergyExpenditureTotal($result["plants"]);
        $result["total"]["delta_energy_consumption"] = self::computeDeltaPlantEnergyConsumptionTotal($result["plants"]);

        //calcolo sommatorie parametri dell'investimento
        //Calcola VAN e TIR
        $result["financement"]["van"] = self::computePlantVan($cashFlowTotale, $investment["wacc"]);
        $result["financement"]["tir"] = self::computePlantTir($cashFlowTotale, $result["total"]["investment_amount"]);

        //Calcola Payback Time
        $result["financement"]["payback_time"] = self::computePayBackTime($cashFlowTotale, $investment["duration_amortization"]);
        //Calcola Canone Minimo
        $result["financement"]["fee_min"] = self::computeFeeMin($result["total"]["investment_amount"], $investment, null, null, null);
        //Calcola Canone Massimo
        $result["financement"]["fee_max"] = self::computeFeeMax($plant, $result["total"]["investment_amount"], $investment, null, null);

        $result["success"] = true;
        return $result;
    }

    public static function computeVanTir($input){
        $result = [
            "success"   => false,
            "data"      => [
                "van" => [],
                "tir" => []
            ]
        ];

        $plant      = SaveCompute::getPlantById($input["plant_id"])["plant"];
        $investment = SaveCompute::getInvestmentById($input["investment_id"])["investment"];

        for ($i = 0; $i < count($input["amortization_duration"]); $i++) {
            $flussoDiCassa[$i]          = SaveCompute::computeHaCashFLow($plant, $investment, $input["amortization_duration"][$i], null);
            $flussiDiCassaTotali[$i]    = SaveCompute::computePlantCashFLow($flussoDiCassa[$i]);
            $investment_amount          = SaveCompute::computePlantInvestmentAmountTotal($flussoDiCassa[$i]);

            for ($j = 0; $j < count($input["wacc"]); $j++) {
                $result["data"]["van"][$i][$j] = SaveCompute::computePlantVan($flussiDiCassaTotali[$i], $input["wacc"][$j]);
            }
            $result["data"]["tir"][$i] = SaveCompute::computePlantTir($flussiDiCassaTotali[$i], $investment_amount);
        }

        $result["success"] = true;

        // $result["data"] = [
        //     "tir" => [21.7,23,23.1],
        //     "van" => [
        //         [42149.74,34112.35,27353.97],
        //         [78149.74,59112.35,44353.97],
        //         [105149.74,74112.35,53353.97]
        //     ]
        // ];

        return $result;
    }

    public static function computePayBack($input) {
        $result = [
            "success"   => true,
            "data"      => []
        ];

        $plant      = SaveCompute::getPlantById($input["plant_id"])["plant"];
        $investment = SaveCompute::getInvestmentById($input["investment_id"])["investment"];

        $min_energy_cost = $input["min_energy_cost"];
        $max_energy_cost = $input["max_energy_cost"];
        $delta_energy = $max_energy_cost - $min_energy_cost;

        for($j = 0; $j < $input["points"] + 1; $j++){
            $iter_energy_unit_cost = $min_energy_cost + ($delta_energy / $input["points"]) * $j;
            $flussiDiCassa = SaveCompute::computeHaCashFLow($plant, $investment, null, $iter_energy_unit_cost);
            $cashFlowTotale = SaveCompute::computePlantCashFLow($flussiDiCassa);
            $result["data"][$j] = array($iter_energy_unit_cost, SaveCompute::computePayBackTime($cashFlowTotale, $investment["duration_amortization"]));
        }

        //$result["data"] =  [[0.1, 7], [0.2, 6], [0.3, 5], [0.4, 4.5], [0.5, 4],[0.6, 3], [0.8, 2], [0.9, 1], [1, 0]];

        return $result;
    }

    public static function computeFee($input) {

        $result = [
            "success"   => true,
            "data"      => []
        ];

        $plant          = SaveCompute::getPlantById($input["plant_id"])["plant"];
        $investment     = SaveCompute::getInvestmentById($input["investment_id"])["investment"];

        $investmentAmount = SaveCompute::computePlantInvestmentAmount($plant, $input["financed_quote"]);

        $result["data"] = [];  $result["data"]["fee_min"] = [];
        $count = 0;

        for($i = floatval($input["min_fee_duration"]); $i <= floatval($input["max_fee_duration"]) + 0.001; $i += floatval(($input["max_fee_duration"] - $input["min_fee_duration"]) / 10) ) {
            if($i + floatval(($input["max_fee_duration"] - $input["min_fee_duration"]) / 10) > floatval($input["max_fee_duration"]) + 0.001){
                $i = $input["max_fee_duration"];
            }
            $result["data"]["fee_min"][$count] = array(floatval($i), SaveCompute::computeFeeMin($investmentAmount, $investment, intval($i), $input["taxes"], $input["financed_quote"]));
            $result["data"]["fee_max"][$count] = array(floatval($i), SaveCompute::computeFeeMax($plant, $investmentAmount, $investment, intval($i), $input["financed_quote"]));
            $count++;
        }

        $result["success"] = true;

        // $result["data"] = [
        //     "fee_min" => [[0, 2500], [1, 2250], [2, 2000], [3, 1500], [4, 1300],[5, 1290], [6, 1280], [7, 1260], [8, 1250]],
        //     "fee_max" => [[0, 4500], [1, 4520], [2, 4540], [3, 4500], [4, 4600],[5, 4700], [6, 5000], [7, 5250], [8, 5500]],
        // ];

        return $result;
    }

    public static function getClustersByHaId($ha_id){
        $result = [
            "success" => false,
            "clusters" => []
        ];

        $clusters = SaveCluster::where("ha_id",$ha_id)->get();
        if ($clusters) {
            $result["clusters"] = $clusters->toArray();
            $result["success"] = true;
        }

        return $result;
    }

    public static function getHasByPlantId($plant_id){
        $result = [
            "success" => false,
            "dataAsIs" => [],
            "dataToBe" => []
        ];

        $hasASIS = SaveHA::where("plant_id",$plant_id)->where("type_ha","as_is")->get();
        if ($hasASIS) {
            $result["dataAsIs"] = $hasASIS->toArray();
            $result["success"] = true;
        }


        $hasTOBE = SaveHA::where("plant_id",$plant_id)->where("type_ha","to_be")->get();
        if ($hasTOBE) {
            $result["dataToBe"] = $hasTOBE->toArray();
            $result["success"] = true;
        }

        return $result;
    }

    public static function getClustersByHaId_TOBEfeatured($ha_id){
        $result = [
            "success"   => false,
            "data"      => null
        ];

        $clusters = SaveCluster::where("ha_id",$ha_id)->where("is_to_be_featured","1")->first();
        if ($clusters) {
            $result["success"] = true;
            $result["data"] = $clusters;
        }

        return $result;
    }

    public static function getEnergyUnitCostForInvestment($investment_id)
    {
        $result = [
            "success" => false,
            "energy_unit_cost" => 0.0
        ];

        $energy_unit_cost=SaveInvestment::firstWhere("id",$investment_id)->value('energy_unit_cost');
        if ($energy_unit_cost) {
            $result["energy_unit_cost"] = (float)$energy_unit_cost;
            $result["success"] = true;
        }

        return $result;
    }

    public static function getInvestmentById($investment_id)
    {
        $result = [
            "success" => false,
            "investment" => ""
        ];

        $investment = SaveInvestment::where("id",$investment_id)->get();
        if ($investment) {
            $result["investment"] = $investment->first();
            $result["success"] = true;
        }

        return $result;

    }

    public static function computeHaInvestmentAmount($ha){
        $clusters = SaveCompute::getClustersByHaId($ha["id"])["clusters"];
        $partialSum = 0;
        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];
            $partialSum += ($ha["lamp_cost"] + $ha["infrastructure_maintenance_cost"] + $ha["lamp_disposal"]) * $cluster["lamp_num"];
        }

        $partialSum += $ha["system_renovation_cost"] + $ha["prodromal_activities_cost"] + ($ha["panel_cost"] * $ha["panel_num"]);

        return $partialSum;
    }

    public static function computePlantInvestmentAmount($plant, $financedQuote)
    {
        $has = SaveCompute::getHasByPlantId($plant["id"]);
        //creazione array delle HAS
        $arrayTOBE = $has["dataToBe"];
        $result = 0;
        for ($i = 0; $i < count($arrayTOBE); $i++){
            $haTOBE = $arrayTOBE[$i];
            $result += SaveCompute::computeHaInvestmentAmount($haTOBE) * $financedQuote /100;
        }
        return $result;
    }

    public static function getPlantById($plant_id){
        $result = [
            "success" => false,
            "plant" => ""
        ];

        $plant = SavePlant::where("id",$plant_id)->get();
        if ($plant) {
            $result["plant"] = $plant->first();
            $result["success"] = true;
        }

        return $result;
    }

    public static function getPlantsByUser($user_id){
        $result = [
            "success" => false,
            "data" => []
        ];

        $plants = SavePlant::where("user_id",$user_id)->get();
        if ($plants) {
            $result["data"] = $plants->toArray();
            $result["success"] = true;
        }

        return $result;
    }

    public static function getPlantsByMunicipality($municipality_code){
        $result = [
            "success" => false,
            "data" => []
        ];

        $plants = SavePlant::where("municipality_code",$municipality_code)->get();
        if ($plants) {
            $result["data"] = $plants->toArray();
            $result["success"] = true;
        }

        return $result;
    }

}
