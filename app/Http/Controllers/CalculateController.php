<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CalculateController extends Controller
{

    public function showImportoInvestimentoPerHA(Request $request, $id_crypted = null){

        $data = SaveToolController::getHasByPlantId(1);
        $result =  $this->importoInvestimentoPerHA($data["dataToBe"][0]);
        //dump($result);  eliminato con la view
        return view("tryCalculate")->with('data',$result);
    }

    /**
     * Questo metodo prende in input un HA id e un Investment ID e calcola la spesa energetica dell'HA dato l'investimento
     * ha quindi a disposizione tutti i parametri dell'investimento ed è la funzione guida dell'operazione di simulazione
     * dal quale recuperare l'enery unit cost
     * */
    public function showSpesaEnergeticaPerHA(Request $request, $id_crypted = null){
        $data["fields"] = config("save");

        //questa operazione bisogna eseguirla per tutte le HA di una determinata municipalità
        $data["payload"] = SaveToolController::getClustersByHaId(1);

        //recupero il parametro dall'investimento selezionato
        $energyCost = SaveToolController::getEnergyUnitCostForInvestment(1);
        $result = CalculateController::calcoloSpesaEnergeticaPerHa($data["payload"]["clusters"], $energyCost["energy_unit_cost"]);
        //$result2 = CalculateController::calcoloConsumoEnergeticoPerHa($data["payload"]["clusters"]);
        dump($result);   //si può usare al posto di dd e consente l'esecuzione del resto dello script, ma ha bisogno di una view Associata?
        return view("tryCalculate")->with('data',$result);
    }

    /**
     * @input costo_medio_lampada               -> $has[lamp_cost]
     * @input costo_infrastruttura              ->    non chiaro, forse $has[infrastructure_maintenance_cost]
     * @input costo_medio_smaltimento_lampada   -> $has[lamp_disposal]
     * @input n_lampade                         -> $cluster[lamp_num]
     * @input costo_rifacimento_imp_elett       -> $has[system_renovation_cost] colonna da aggiungere
     * @input costo_attività_prodomiche         -> $has[prodromal_activities_cost]
     * @input costo_quadro                      -> $has[panel_cost]
     * @input n_quadri_el                       -> $has[panel_num]
     *
     * */
    public static function importoInvestimentoPerHA($ha){
        $clusters = SaveToolController::getClustersByHaId(1)["clusters"];
        $sommaParziale = 0;
        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];
            $sommaParziale += ($ha["lamp_cost"] + $ha["infrastructure_maintenance_cost"] + $ha["lamp_disposal"]) * $cluster["lamp_num"];
        }

        $sommaParziale += $ha["system_renovation_cost"] + $ha["prodromal_activities_cost"] + ($ha["panel_cost"] * $ha["panel_num"]);

        return $sommaParziale;

    }


    /**
     * @input ore_acc_piena     ->$cluster[hours_full_light]
     * @input %dimm             ->$cluster[dimmering]
     * @input ore_dimm          ->$cluster[hours_dimmering_light]
     * @input n_apparecchi      ->$cluster[device_num]
     * @input potenza_m_morsett ->$cluster[average_device_power]
     *
     * calcolo parametro "Costi/benefici annuali in consumo energetico"
     * */

    public static function calcoloConsumoEnergeticoPerHa($ha){

        $clusters = SaveToolController::getClustersByHaId($ha["ha_id"])["clusters"];

        $consumoEnergeticoHa = 0;

        //sommatoria per ogni cluster
        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            $consumoEnergetico = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmering_light"]) * $cluster["device_num"]
                * $cluster["average_device_power"];

            $consumoEnergeticoHa += $consumoEnergetico;
        }

        return $consumoEnergeticoHa;

    }

    public static function calcoloDeltaConsumoEnergeticoPerImpianto($plant)
    {
        $data = SaveToolController::getHasByPlantId($plant["id"]);
        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $consumoEnergeticoASIS = 0;
        for ($i = 0; $i < count($arrayASIS); $i++) {
            $haASIS = $arrayASIS[$i];
            $consumoEnergeticoASIS += $consumoEnergeticoASIS + CalculateController::calcoloConsumoEnergeticoPerHa($haASIS);
        }

        //calcolo il consumo energetico delle HA TO-BE
        $arrayTOBE = $data["dataToBe"];
        $consumoEnergeticoTOBE = 0;
        for ($i = 0; $i < count($arrayTOBE); $i++) {
            $haTOBE = $arrayTOBE[$i];
            $consumoEnergeticoTOBE += $consumoEnergeticoTOBE + CalculateController::calcoloConsumoEnergeticoPerHa($haTOBE);
        }

        //restituisco il risultato
        return $consumoEnergeticoASIS - $consumoEnergeticoTOBE;
    }


    //calcolo parametro "Costi/benefici annuali in spesa energetica"
    public static function calcoloSpesaEnergeticaPerHa($costo_unitario){

        $clusters = SaveToolController::getClustersByHaId(1);

        $spesaEnergeticaHa = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            $spesaEnergetica = ($cluster["hours_full_light"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmering_light"]) * $cluster["device_num"]
                * $cluster["average_device_power"] * ((float)$costo_unitario[0] / 1000);

            $spesaEnergeticaHa += $spesaEnergetica;
        }

        return $spesaEnergeticaHa;

    }

    public static function calcoloDeltaSpesaEnergeticaPerImpianto($plant)
    {
        $data = SaveToolController::getHasByPlantId($plant["id"]);
        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $spesaEnergeticaASIS = 0;
        for ($i = 0; $i < count($arrayASIS); $i++) {
            $haASIS = $arrayASIS[$i];
            $spesaEnergeticaASIS += $spesaEnergeticaASIS + CalculateController::calcoloDeltaSpesaEnergeticaPerImpianto($haASIS);
        }

        //calcolo il consumo energetico delle HA TO-BE
        $arrayTOBE = $data["dataToBe"];
        $spesaEnergeticaTOBE = 0;
        for ($i = 0; $i < count($arrayTOBE); $i++) {
            $haTOBE = $arrayTOBE[$i];
            $spesaEnergeticaTOBE += $spesaEnergeticaTOBE + CalculateController::calcoloDeltaSpesaEnergeticaPerImpianto($haTOBE);
        }

        //restituisco il risultato
        return $spesaEnergeticaASIS - $spesaEnergeticaTOBE;
    }

    /**
     * calcola incentivi statali per impianto
     * @input delta_consumo_energetico from calcoloDeltaConsumoEnergeticoPerImpianto($plant)
     * @input $investments(tep_kwh)
     * @input $investments(tep_value)
     * NO-> @input $investments(incentives_duration)
     * ricavo_incentivi = delta_consumo_energetico / kWH_TEP * valore_monetario_TEP
     * */

    public static function calcolaIncentiviStataliPerImpiantoAndInvestimento($plant, $investment)
    {
        $deltaImpianto = CalculateController::calcoloDeltaConsumoEnergeticoPerImpianto($plant["id"]);
        $parametriInvestimento = SaveToolController::getInvestmentById($investment["id"]);
        return $deltaImpianto / $parametriInvestimento["tep_kwh"] * $parametriInvestimento["tep_value"];

    }

    public static function calcolaTotaleLampadePerHA($ha) {
        $clusters = SaveToolController::getClustersByHaId(1);

        $nLampadeTot = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];
            $nLampadeTot += $cluster["lamp_nuum"];
        }

        return $nLampadeTot;
    }

    /**
     * calcola i costi di manutenzione dell'impianto asIs restituendo un riisultato per ogni HA
     * @input $has(lamp_cost)
     * @input $has(lamp_disposal)
     * @input calcolaTotaleLampadePerHA($ha)
     * */

    public static function calcolaCostiManutenzioneAsIs($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataAsIs"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = ($hasAsIs["lamp_cost"] + $hasAsIs["lamp_disposal"]) * calcolaTotaleLampadePerHA($ha);
        }

        return $result;
    }

    /**
     * calcola i costi di manutenzione dell'impianto ToBe restituendo un riisultato per ogni HA
     * @input $has(lamp_cost)
     * @input $has(lamp_disposal)
     * @input calcolaTotaleLampadePerHA($ha)
     * */

    public static function calcolaCostiManutenzioneToBe($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = ($ha["lamp_cost"] + $ha["lamp_disposal"]) * calcolaTotaleLampadePerHA($ha);
        }

        return $result;
    }

    /**
     *
     * @input $has(infrastructure_maintenance_cost)
     * @input calcolaTotaleLampadePerHA($ha)
     *
     * */

    public static function calcolaCostoManutenzioneInfrastruttura($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = $ha["infrastructure_maintenance_cost"] * calcolaTotaleLampadePerHA($ha);
        }
        return $result;
    }


}
