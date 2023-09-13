<?php


namespace App\Http\Controllers;


use App\Models\Risultato_singolaZO;
use Illuminate\Http\Request;

class CalculateController extends Controller
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
     * il risultato è UN AGGREGATO di tutte le ZO (e non un array con i singoli delta)
     *
     * @input $plant
     * @input calcoloConsumoEnergeticoPerHa($has)
     * */
    public static function calcoloDeltaConsumoEnergeticoPerImpianto($plant, $results)
    {
        //prendo le zone omogenee in base all'id dell'impianto
        $data = SaveToolController::getHasByPlantId($plant["id"]);

        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $arrayTOBE = $data["dataToBe"];
        for ($i = 0; $i < count($arrayASIS); $i++) {
            $haASIS = $arrayASIS[$i];

            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_has_is_id_ha"] == $haASIS["id"];
            })->first();

            $value = CalculateController::calcoloConsumoEnergeticoPerHaASIS($haASIS) - CalculateController::calcoloConsumoEnergeticoPerHaTOBE($haTOBE);

            $results = collect($results)->each(function (Risultato_singolaZO $singolo) use ($value, $haASIS){
                if($singolo->getAsisName() == $haASIS["label_ha"]){
                    $singolo->setDeltaEnergyConsumption($value);
                    return false;
                }
            });
        }

        //restituisco il risultato
        return $results;
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
     * il risultato è UN AGGREGATO di tutte le ZO (e non un array con i singoli delta)
     *
     * @input $plant
     * @input calcoloSpesaEnergeticoPerHa($has)
     * */

    public static function calcoloDeltaSpesaEnergeticaPerImpianto($plant, $results)
    {
        $data = SaveToolController::getHasByPlantId($plant["id"]);
        $energyCost = (SaveToolController::getEnergyUnitCostForInvestment(1))["energy_unit_cost"];

        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $arrayTOBE = $data["dataToBe"];
        for ($i = 0; $i < count($arrayASIS); $i++) {
            $haASIS = $arrayASIS[$i];

            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_has_is_id_ha"] == $haASIS["id"];
            })->first();

            $value = CalculateController::calcoloSpesaEnergeticaPerHaASIS($haASIS, $energyCost) - CalculateController::calcoloSpesaEnergeticaPerHaTOBE($haTOBE, $energyCost);

            $results = collect($results)->each(function (Risultato_singolaZO $singolo) use ($value, $haASIS){
               if($singolo->getAsisName() == $haASIS["label_ha"]){
                   $singolo->setDeltaEnergyExpenditure($value);
                   return false;
               }
            });
        }

        //restituisco il risultato
        return $results;
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
        $deltaImpianto = CalculateController::calcoloDeltaConsumoEnergeticoPerImpianto($plant);
        $parametriInvestimento = SaveToolController::getInvestmentById($investment["id"])["investment"];
        return $deltaImpianto / $parametriInvestimento["tep_kwh"] * $parametriInvestimento["tep_value"];
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
            $result[$i] = CalculateController::calcolaCostiManutezionePerHA($ha);
        }

        return $result;
    }

    public static function calcolaCostiManutezionePerHA($ha){
        return ($ha["lamp_cost"] + $ha["lamp_disposal"]) * CalculateController::calcolaTotaleLampadePerHA($ha);
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
            $result[$i] = CalculateController::calcolaCostiManutezionePerHA($ha);
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
            $result[$i] = CalculateController::calcolaCostoManutenzioneInfrastrutturaPerHA($ha);
        }
        return $result;
    }

    public static function calcolaCostoManutenzioneInfrastrutturaPerHA($ha){
        return $ha["infrastructure_maintenance_cost"] * CalculateController::calcolaTotaleLampadePerHA($ha);
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
            $result[0] -= CalculateController::calcolaImportoInvestimentoPerHA($ha) * $investment["share_municipality"] /100;
        }

        for($j = 1; $j <= $investment["duration_amortization"]; $j++){
            //ricavo
            for ($i = 0; $i < count($hasAsIs); $i++){
                $ha = $hasAsIs[$i];
                $result[$j] += CalculateController::calcolaCostiManutezionePerHA($ha) * (($j % $ha["maintenance_interval"]==0)? 1 : 0);
            }

            //ricavi
            $result[$j] += CalculateController::calcoloDeltaSpesaEnergeticaPerImpianto($plant);
            $result[$j] += CalculateController::calcolaIncentiviStataliPerImpiantoAndInvestimento($plant, $investment);
            //costo
            $result[$j] -= $investment["mortgage_installment"];
            $result[$j] -= $investment["fee_esco"];

            //costo
            $hasToBe = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
            for ($i = 0; $i < count($hasToBe); $i++){
                $ha = $hasToBe[$i];
                $result[$j] -= CalculateController::calcolaCostiManutezionePerHA($ha) * (($j % $ha["maintenance_interval"]==0)? 1 : 0);
            }

            //costo
            $hasToBe = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
            for ($i = 0; $i < count($hasToBe); $i++){
                $ha = $hasToBe[$i];
                $result[$j]-= CalculateController::calcolaCostoManutenzioneInfrastrutturaPerHA($ha) * (($j % $ha["infrastructure_maintenance_interval"]==0)? 1 : 0);
            }

            $result[$j]-= $investment["management_cost"];

        }

        //dd($result);
        return $result;
    }


    public static function calcolo($plant, $investment){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataAsIs"];

        //calcolo importo investimento per Impianto
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = new Risultato_singolaZO();
            $result[$i]->setAsisName($ha["label_ha"]);
        }

        CalculateController::calcoloDeltaConsumoEnergeticoPerImpianto($plant, $result);
        CalculateController::calcoloDeltaSpesaEnergeticaPerImpianto($plant, $result);

        return $result;
    }

}
