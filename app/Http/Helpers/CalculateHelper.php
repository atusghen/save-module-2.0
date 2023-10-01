<?php


namespace App\Http\Helpers;


use App\Http\Controllers\Controller;
use App\Http\Controllers\SaveToolController;
use App\Models\Risultato_singolaZO;
use Illuminate\Http\Request;

class CalculateHelper
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
    public static function calcolaImportoInvestimentoPerHA($ha){
        $clusters = SaveToolController::getClustersByHaId($ha["id"])["clusters"];
        $sommaParziale = 0;
        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];
            $sommaParziale += ($ha["lamp_cost"] + $ha["infrastructure_maintenance_cost"] + $ha["lamp_disposal"]) * $cluster["lamp_num"];
        }

        $sommaParziale += $ha["system_renovation_cost"] + $ha["prodromal_activities_cost"] + ($ha["panel_cost"] * $ha["panel_num"]);

        return $sommaParziale;
    }

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

    public static function calcoloConsumoEnergeticoPerHaASIS($ha){

        $clusters = SaveToolController::getClustersByHaId($ha["id"])["clusters"];

        $consumoEnergeticoHa = 0;

        //sommatoria per ogni cluster
        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            $consumoEnergetico = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_light"]) * $cluster["device_num"]
                * $cluster["average_device_power"];

            $consumoEnergeticoHa += $consumoEnergetico;
        }

        return $consumoEnergeticoHa;
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

    public static function calcoloConsumoEnergeticoPerHaTOBE($ha){
        $cluster = SaveToolController::getClustersByHaId_TOBEfeatured($ha["id"])["clusters"];

        return ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_light"]) * $cluster["device_num"]
            * $cluster["average_device_power"];
    }


    /**
     * Calcolo Costi/benefici annuali in consumo energetico come Delta tra
     * la sommatoria delle ZO AS-IS e TO-BE
     * il risultato è un DELTA tra due ZO associate e non un aggregato
     *
     * @input $plant
     * @input calcoloConsumoEnergeticoPerHa($has)
     * */
    public static function calcoloDeltaConsumoEnergeticoPerHAS($haASIS, $haTOBE, $result)
    {
        $value = CalculateHelper::calcoloConsumoEnergeticoPerHaASIS($haASIS) - CalculateHelper::calcoloConsumoEnergeticoPerHaTOBE($haTOBE);

        //posiziona l'elaborazione nella cella specifica
        return collect($result)->each(function (Risultato_singolaZO $singolo) use ($value, $haASIS){
            if($singolo->getAsisName() == $haASIS["label_ha"]){
                $singolo->setDeltaEnergyConsumption($value);
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
    public static function calcoloSpesaEnergeticaPerHaASIS($ha, $costo_unitario){

        //prendo tutti i cluster che appartengono alla zona omogenea con un certo id
        $clusters = SaveToolController::getClustersByHaId($ha["id"])["clusters"];

        $spesaEnergeticaHa = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            //calcolo spesa energetica i-esimo cluster
            $spesaEnergetica = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_light"]) * $cluster["device_num"]
                * $cluster["average_device_power"] * ((float)$costo_unitario / 1000);

            //somma delle singole spese energetiche in quella generale della zona omogenea
            $spesaEnergeticaHa += $spesaEnergetica;
        }

        return $spesaEnergeticaHa;
    }

    /**
     * Calcola costi/benefici annuali in spesa energetica per ZO TOBE, essendocene una sola non ho bisogno di aggregare tutti i cluster
     *
     * @input $ha
     * @input $costo_unitario
     * */
    public static function calcoloSpesaEnergeticaPerHaTOBE($ha, $costo_unitario){
        $cluster = SaveToolController::getClustersByHaId_TOBEfeatured($ha["id"])["clusters"];

        return  ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_light"]) * $cluster["device_num"]
            * $cluster["average_device_power"] * ((float)$costo_unitario / 1000);
    }


    /**
     * Calcolo Costi/benefici annuali in spesa energetica come Delta tra
     * la sommatoria delle ZO AS-IS e TO-BE
     * il risultato è un DELTA tra due ZO associate e non un aggregato
     *
     * @input $plant
     * @input calcoloSpesaEnergeticoPerHa($has)
     * */

    public static function calcoloDeltaSpesaEnergeticaPerHAS($haASIS, $haTOBE, $energyCost, $result)
    {
        $value = CalculateHelper::calcoloSpesaEnergeticaPerHaASIS($haASIS, $energyCost) - CalculateHelper::calcoloSpesaEnergeticaPerHaTOBE($haTOBE, $energyCost);

        return collect($result)->each(function (Risultato_singolaZO $singolo) use ($value, $haASIS){
           if($singolo->getAsisName() == $haASIS["label_ha"]){
               $singolo->setDeltaEnergyExpenditure($value);
               return false;
           }
        });

    }


    /**
     * Calcola incentivi statali per impianto
     *
     * @input delta_consumo_energetico from calcoloDeltaConsumoEnergeticoPerImpianto($plant)
     * @input $investments(tep_kwh)
     * @input $investments(tep_value)
     * NO-> @input $investments(incentives_duration)
     * ricavo_incentivi = delta_consumo_energetico / kWH_TEP * valore_monetario_TEP
     * */

    public static function calcolaIncentiviStataliPerImpiantoAndInvestimento($plant, $investment)
    {
        $deltaImpianto = CalculateHelper::calcoloDeltaConsumoEnergeticoPerImpianto($plant, $investment);
        $parametriInvestimento = SaveToolController::getInvestmentById($investment["id"])["investment"];
        return $deltaImpianto / $parametriInvestimento["tep_kwh"] * $parametriInvestimento["tep_value"];
    }

    /**
     * Calcolo Costi/benefici annuali in consumoi energetica come Delta tra
     * la sommatoria delle ZO AS-IS e TO-BE
     * il risultato è UN AGGREGATO di tutte le ZO (e non un array con i singoli delta)
     *
     * @input $plant
     * @input calcoloSpesaEnergeticoPerHa($has)
     * */
    public static function calcoloDeltaConsumoEnergeticoPerImpianto($plant, $investment){
        $data = SaveToolController::getHasByPlantId($plant["id"]);
        $energyCost = (SaveToolController::getEnergyUnitCostForInvestment($investment["id"]))["energy_unit_cost"];

        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $arrayTOBE = $data["dataToBe"];
        $result = 0;
        for ($i = 0; $i < count($arrayASIS); $i++) {
            //per ogni $haASIS
            $haASIS = $arrayASIS[$i];

            //cerco la HA TOBE associata
            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_has_is_id_ha"] == $haASIS["id"];
            })->first();

            //prendo tutte le ZO AS-IS, sommatoria CU e calcolo - prendo solo la TO-BE associata e sottraggo
            $value = CalculateHelper::calcoloConsumoEnergeticoPerHaASIS($haASIS) - CalculateHelper::calcoloConsumoEnergeticoPerHaTOBE($haTOBE);

            //sommo per aggregare i risultati
            $result += $value;
        }

        //restituisco il risultato
        return $result;
    }

    /**
     * Calcolo Costi/benefici annuali in spesa energetica come Delta tra
     * la sommatoria delle ZO AS-IS e TO-BE
     * il risultato è UN AGGREGATO di tutte le ZO (e non un array con i singoli delta)
     *
     * @input $plant
     * @input calcoloSpesaEnergeticoPerHa($has)
     * */

    public static function calcoloDeltaSpesaEnergeticaPerImpianto($plant, $investment)
    {
        $data = SaveToolController::getHasByPlantId($plant["id"]);
        $energyCost = (SaveToolController::getEnergyUnitCostForInvestment($investment["id"]))["energy_unit_cost"];

        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $arrayTOBE = $data["dataToBe"];
        $result = 0;
        for ($i = 0; $i < count($arrayASIS); $i++) {
            //per ogni $haASIS
            $haASIS = $arrayASIS[$i];

            //cerco la HA TOBE associata
            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_has_is_id_ha"] == $haASIS["id"];
            })->first();

            //prendo tutte le ZO AS-IS, sommatoria CU e calcolo - prendo solo la TO-BE associata e sottraggo
            $value = CalculateHelper::calcoloSpesaEnergeticaPerHaASIS($haASIS, $energyCost) - CalculateHelper::calcoloSpesaEnergeticaPerHaTOBE($haTOBE, $energyCost);

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

    public static function calcolaTotaleLampadePerHA($ha) {
        $clusters = SaveToolController::getClustersByHaId($ha["id"])["clusters"];

        $nLampadeTot = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];
            $nLampadeTot += $cluster["lamp_num"];
        }

        return $nLampadeTot;
    }


    /**
     * calcola i costi di manutenzione dell'impianto asIs restituendo un risultato per ogni HA
     * si tratta di un costo annuale per ogni HA senza associazione
     * @output come array
     *
     * @input $has(lamp_cost)
     * @input $has(lamp_disposal)
     * @input calcolaTotaleLampadePerHA($ha)
     * */

    public static function calcolaCostiManutenzioneAsIs($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataAsIs"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = CalculateHelper::calcolaCostiManutezionePerHA($ha);
        }

        return $result;
    }

    public static function calcolaCostiManutezionePerHA($ha){
        return ($ha["lamp_cost"] + $ha["lamp_disposal"]) * CalculateHelper::calcolaTotaleLampadePerHA($ha);
    }

    /**
     * calcola i costi di manutenzione dell'impianto ToBe restituendo un risultato per ogni HA
     * @output come array
     *
     * @input $has(lamp_cost)
     * @input $has(lamp_disposal)
     * @input calcolaTotaleLampadePerHA($ha)
     * */

    public static function calcolaCostiManutenzioneToBe($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = CalculateHelper::calcolaCostiManutezionePerHA($ha);
        }

        return $result;
    }


    /**
     * calcola i costi dell'infrastruttura TOBE
     * @input $has(infrastructure_maintenance_cost)
     * @input calcolaTotaleLampadePerHA($ha)
     * */

    public static function calcolaCostoManutenzioneInfrastrutturaToBe($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = CalculateHelper::calcolaCostoManutenzioneInfrastrutturaPerHA($ha);
        }
        return $result;
    }

    public static function calcolaCostoManutenzioneInfrastrutturaPerHA($ha){
        return $ha["infrastructure_maintenance_cost"] * CalculateHelper::calcolaTotaleLampadePerHA($ha);
    }

    /**
     *
     * @input calcoloDeltaSpesaEnergeticaPerImpianto($plant)
     * @input calcolaIncentiviStataliPerImpiantoAndInvestimento($plant, $investment)
     * @input calcolaCostiManutenzioneAsIs($plant)
     * @input calcoloServiziSmart???
     * @input $investment(mortgage_installment)
     * @input $investment(share_esco)
     * @input costoGestioneOperativaServiziSmart??
     * @input calcolaCostoManutenzioneInfrastrutturaToBe($plant)
     * @input calcolaCostiManutenzioneToBe($plant)
     * @input $investment(management_cost)
     *
     *
     * */
    public static function calcoloFlussiDiCassaPerPlant($plant, $investment){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataAsIs"];

        //inizializzazione array
        $result = [];
        for($j = 0; $j <= $investment["duration_amortization"]; $j++){
            $result[$j] = 0;
        }

        //calcolo importo investimento per Impianto
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[0] -= CalculateHelper::calcolaImportoInvestimentoPerHA($ha) * $investment["share_municipality"] /100;
        }

        for($j = 1; $j <= $investment["duration_amortization"]; $j++){
            //ricavo
            for ($i = 0; $i < count($hasAsIs); $i++){
                $ha = $hasAsIs[$i];
                $result[$j] += CalculateHelper::calcolaCostiManutezionePerHA($ha) * (($j % $ha["maintenance_interval"]==0)? 1 : 0);
            }

            //ricavi
            $result[$j] += CalculateHelper::calcoloDeltaSpesaEnergeticaPerImpianto($plant, $investment);
            $result[$j] += CalculateHelper::calcolaIncentiviStataliPerImpiantoAndInvestimento($plant, $investment);
            //costo
            $result[$j] -= $investment["mortgage_installment"];
            $result[$j] -= $investment["fee_esco"];

            //costo
            $hasToBe = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
            for ($i = 0; $i < count($hasToBe); $i++){
                $ha = $hasToBe[$i];
                $result[$j] -= CalculateHelper::calcolaCostiManutezionePerHA($ha) * (($j % $ha["maintenance_interval"]==0)? 1 : 0);
            }

            //costo
            $hasToBe = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
            for ($i = 0; $i < count($hasToBe); $i++){
                $ha = $hasToBe[$i];
                $result[$j]-= CalculateHelper::calcolaCostoManutenzioneInfrastrutturaPerHA($ha) * (($j % $ha["infrastructure_maintenance_interval"]==0)? 1 : 0);
            }

            $result[$j]-= $investment["management_cost"];

        }

        //dd($result);
        return $result;
    }

    public static function calcoloVANperImpianto($cashFlow, $wacc): float
    {
        $result = 0;
        for($i=0; $i<count($cashFlow); $i++){
            $result += ($cashFlow[$i]/(1+$wacc)^$i);
        }

        return $result;
    }

    public static function calcoloTIRperImpianto($cashFlow, $amortization_override): ?float
    {
        $maxIterations = 100;
        $tolerance = 0.00001;
        $guess = 0.1;

        if($amortization_override)
            $count = $amortization_override;
        else
            $count = count($cashFlow);

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
            $dnpv = 0;

            for ($j = 0; $j < $count; $j++) {
                $npv += $cashFlow[$j] / pow(1 + $guess, $j);
                $dnpv -= $j * $cashFlow[$j] / pow(1 + $guess, $j + 1);
            }

            $newGuess = $guess - $npv / $dnpv;

            if (abs($newGuess - $guess) < $tolerance) {
                return $newGuess;
            }

            $guess = $newGuess;
        }

        return $guess;
    }

    public static function calcoloPayBackTime($flussoDiCassaTotale){
        $payBacktime = 0;
        $flussoCumulativo[0] = $flussoDiCassaTotale[0];

        for($i = 1; $i < count($flussoDiCassaTotale); $i++){
            $flussoCumulativo[$i] = $flussoDiCassaTotale[$i] + $flussoCumulativo[$i-1];
        }

        for($j = count($flussoDiCassaTotale) - 2; $j > 0; $j--){
            if($flussoCumulativo[$j] < 0){
                $payBacktime = $j;
            }
        }

        if($payBacktime > 0)
        {
            $payBacktime += abs($flussoDiCassaTotale[$j+1])/$flussoCumulativo[$j];
        }else{
            return null;
        }

        return $payBacktime;
    }

    public static function calcoloCanoneMinimo($importoInvestimento, $investment){
        $investment_ESCO = $importoInvestimento * ($investment["share_esco"]/100);
        $canoneIniziale = ($investment_ESCO) / ((1-(1+$investment["WACC"])^(-$investment["project_duration"])) /$investment["wacc"]);
        $investimentoIniziale = $importoInvestimento + $canoneIniziale;

        $ammortamento = $investimentoIniziale / $investment["project_duration"];
        $result = ($canoneIniziale - $ammortamento * $investment["taxes"] / 100) / (1- $investment["taxes"] * 100);

        return $result;
    }

    public static function calcoloCanoneMassimo($importoInvestimento, $investment){
        $investimentoIniziale_comune = $importoInvestimento * ($investment["share_municipality"] / 100);
        $ammortamento_comune = $investimentoIniziale_comune / ((1-(1+$investment["WACC"])^(-$investment["project_duration"])) /$investment["wacc"]);
        $canoneIniziale = ($investment_ESCO) / ((1-(1+$investment["WACC"])^(-$investment["project_duration"])) /$investment["wacc"]);

        $ammortamento = $investimentoIniziale / $investment["project_duration"];
        $result = ($canoneIniziale - $ammortamento * $investment["taxes"] / 100) / (1- $investment["taxes"] * 100);

        return $result;
    }


    public static function calcolo($plant, $investment){
        $has = SaveToolController::getHasByPlantId($plant["id"]);
        //creazione array delle HAS
        $arrayASIS = $has["dataAsIs"];
        $arrayTOBE = $has["dataToBe"];
        $energyCost = (SaveToolController::getEnergyUnitCostForInvestment($investment["id"]))["energy_unit_cost"];

        for ($i = 0; $i < count($arrayASIS); $i++){
            //inizializzazione oggetto di output
            $result[$i] = new Risultato_singolaZO();

            //singola HA ASIS
            $haASIS = $arrayASIS[$i];
            $result[$i]->setAsisName($haASIS["label_ha"]);

            //getting TOBE associata
            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_has_is_id_ha"] == $haASIS["id"];
            })->first();

            $result[$i]->setTobeName($haTOBE["label_ha"]);
            //fine inizializzazione oggetto di output

            //inizio calcolo
            //calcolo costo investimento
            $result[$i]->setInvestmentAmount(CalculateHelper::calcolaImportoInvestimentoPerHA($haASIS) * $investment["share_municipality"] /100);

            //Calcola costi/benefici annuali in consumo energetico
            CalculateHelper::calcoloDeltaConsumoEnergeticoPerHAS($haASIS, $haTOBE, $result);
            //Calcola costi/benefici annuali in spesa energetica
            CalculateHelper::calcoloDeltaSpesaEnergeticaPerHAS($haASIS, $haTOBE, $energyCost,$result);

            //Calcola incentivi statali
            $result[$i]->setIncentiveRevenue($result[$i]->getDeltaEnergyConsumption() / $investment["tep_kwh"] * $investment["tep_value"]);

            //Calcola costi manutenzione
            //calcolo flussi e totale costo manutenzione ASIS e TOBE
            for($j = 1; $j <= $investment["duration_amortization"]; $j++) {
                //costo
                $result_asis_maintenance_cost[$j] = CalculateHelper::calcolaCostiManutezionePerHA($haASIS) * (($j % $haASIS["maintenance_interval"] == 0) ? 1 : 0);
                $result_tobe_lamp_cost[$j] = CalculateHelper::calcolaCostiManutezionePerHA($haTOBE) * (($j % $haTOBE["maintenance_interval"] == 0) ? 1 : 0);
                $result_tobe_infrastructure_cost[$j] = CalculateHelper::calcolaCostoManutenzioneInfrastrutturaPerHA($haTOBE) * (($j % $haTOBE["maintenance_interval"] == 0) ? 1 : 0);
            }
            $result[$i]->asis_maintenance_cost = array_sum($result_asis_maintenance_cost);
            $result[$i]->tobe_maintenance_cost = array_sum($result_tobe_infrastructure_cost) + array_sum($result_tobe_lamp_cost);

            //Calcola flussi di cassa annuali
            $result[$i]->cash_flow[0] = $result[$i]->getInvestmentAmount();
            for($j = 1; $j <= $investment["duration_amortization"]; $j++) {
                //costo
                $result[$i]->cash_flow[$j] = $result[$i]->getDeltaEnergyConsumption() + $result[$i]->getIncentiveRevenue()
                    + $result_asis_maintenance_cost[$j] - $investment["mortgage_installment"]
                    - $investment["fee_esco"] - $result_tobe_lamp_cost[$j]
                    - $result_tobe_infrastructure_cost[$j] - $investment["management_cost"];
            }
        }

        //calcolo cashflow totale per calcolo VAN, TIR e Payback
        for($j = 0; $j<count($result[0]->cash_flow); $j++){
            $cashFlowTotale[$j] = 0;
        }

        for($i = 0; $i<count($result); $i++){
            for($j = 0; $j<count($result[$i]->cash_flow); $j++){
                $cashFlowTotale[$j] += $result[$i]->cash_flow[$j];
            }
        }

        //Calcola VAN e TIR
        $van = self::calcoloVANperImpianto($cashFlowTotale, $investment["wacc"]);
        $tir = self::calcoloTIRperImpianto($cashFlowTotale, null);

        //Calcola Payback Time
        $paybackTime = self::calcoloPayBackTime($cashFlowTotale);

        //Calcola Canone Minimo
        $canoneMinimo = self::calcoloCanoneMinimo($cashFlowTotale[0], $investment);


        $superResult = [ $result, $van, $tir, $cashFlowTotale, $paybackTime, $canoneMinimo];

        return $superResult;
    }

}
