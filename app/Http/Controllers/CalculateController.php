<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

/**
 *DOMANDE:
 * 1) quando bisogna calcolare un delta tra un dato di zona omogenea AS-IS e TO-BE, ad esempio il delta spesa energetica, come
 * sappiamo che una zona AS-IS corrisponde a una certa zona TO-BE per fare la differenza (delta) tra le due? Oppure
 * sommiamo tutte le zone AS-IS tra di loro e tutte le TO-BE tra loro (ovviamente dello stesso impianto)
 * e poi facciamo la differenza tra i due totali?
 *
 * 2) Cosa significano alcuni attributi del db: lamp_num (da noi inteso come: "Numero Lampade totali del CU"),
 * device_num (da noi inteso come: "Numero Apparecchi/lampioni") della tabella save_clusters,
 * panel_num (da noi inteso come: "Numero quadri") della tabella save_has
 *
 * 3) Abbiamo aggiunto dei campi nel db, vanno bene?
 */

class CalculateController extends Controller
{

    public function showFlussiDiCassaPerPlant(Request $request, $id_crypted = null){

        $plant["id"] = 1;
        $investment = (SaveToolController::getInvestmentById(1)["investment"]);
        $result =  $this->calcoloFlussiDiCassaPerPlant($plant, $investment);
        //dump($result);  eliminato con la view
        return view("tryCalculate")->with('data',$result);
    }

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
        //$data["fields"] = config("save");

        $has = SaveToolController::getHasByPlantId(1);

        //recupero il parametro dall'investimento selezionato
        $energyCost = SaveToolController::getEnergyUnitCostForInvestment(1)["energy_unit_cost"];
        $result = CalculateController::calcoloSpesaEnergeticaPerHa(($has["dataToBe"])[0], $energyCost);
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
     * calcolo parametro "Costi/benefici annuali in consumo energetico"
     * @input ore_acc_piena     ->$cluster[hours_full_light]
     * @input %dimm             ->$cluster[dimmering]
     * @input ore_dimm          ->$cluster[hours_dimmering_light]
     * @input n_apparecchi      ->$cluster[device_num]
     * @input potenza_m_morsett ->$cluster[average_device_power]
     * */

    public static function calcoloConsumoEnergeticoPerHa($ha){

        $clusters = SaveToolController::getClustersByHaId($ha["id"])["clusters"];

        $consumoEnergeticoHa = 0;

        //sommatoria per ogni cluster
        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            $consumoEnergetico = ($cluster["hours_full_lighting"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_lighting"]) * $cluster["device_num"]
                * $cluster["average_device_power"];

            $consumoEnergeticoHa += $consumoEnergetico;
        }

        return $consumoEnergeticoHa;

    }

    /**
     * @input $plant
     * @input calcoloConsumoEnergeticoPerHa($haASIS)
     * */
    public static function calcoloDeltaConsumoEnergeticoPerImpianto($plant)
    {
        //prendo le zone omogenee in base all'id dell'impianto
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

    /**
     * @input $ha
     * @input $costo_unitario
     * */
    //calcolo parametro "Costi/benefici annuali in spesa energetica"
    public static function calcoloSpesaEnergeticaPerHa($ha, $costo_unitario){

        //prendo tutti i cluster che appartengono alla zona omogenea con un certo id
        $clusters = SaveToolController::getClustersByHaId($ha["id"])["clusters"];

        $spesaEnergeticaHa = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            //calcolo spesa energetica i-esimo cluster
            $spesaEnergetica = ($cluster["hours_full_lighting"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_lighting"]) * $cluster["device_num"]
                * $cluster["average_device_power"] * ((float)$costo_unitario[0] / 1000);

            //somma delle singole spese energetiche in quella generale della zona omogenea
            $spesaEnergeticaHa += $spesaEnergetica;
        }

        return $spesaEnergeticaHa;

    }

    /**
     * @input $plant
     *
     * */

    public static function calcoloDeltaSpesaEnergeticaPerImpianto($plant)
    {
        $data = SaveToolController::getHasByPlantId($plant["id"]);
        $energyCost = SaveToolController::getEnergyUnitCostForInvestment(1)["energy_unit_cost"];
        //calcolo il consumo energetico delle HA AS-IS
        $arrayASIS = $data["dataAsIs"];
        $spesaEnergeticaASIS = 0;
        for ($i = 0; $i < count($arrayASIS); $i++) {
            $haASIS = $arrayASIS[$i];
            $spesaEnergeticaASIS += CalculateController::calcoloSpesaEnergeticaPerHa($haASIS, $energyCost);
        }

        //calcolo il consumo energetico delle HA TO-BE
        $arrayTOBE = $data["dataToBe"];
        $spesaEnergeticaTOBE = 0;
        for ($i = 0; $i < count($arrayTOBE); $i++) {
            $haTOBE = $arrayTOBE[$i];
            $spesaEnergeticaTOBE += CalculateController::calcoloSpesaEnergeticaPerHa($haTOBE, $energyCost);
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
        $deltaImpianto = CalculateController::calcoloDeltaConsumoEnergeticoPerImpianto($plant);
        $parametriInvestimento = SaveToolController::getInvestmentById($investment["id"])["investment"];
        return $deltaImpianto / $parametriInvestimento["tep_kwh"] * $parametriInvestimento["tep_value"];

    }

    /**
     * calcola il totale lampade per HA in base ai cluster
     * @input $ha
     *
     * */

    public static function calcolaTotaleLampadePerHA($ha) {
        $clusters = SaveToolController::getClustersByHaId(1)["clusters"];

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
     * @input $has(lamp_cost)
     * @input $has(lamp_disposal)
     * @input calcolaTotaleLampadePerHA($ha)
     * */

    public static function calcolaCostiManutenzioneAsIs($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataAsIs"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = ($hasAsIs["lamp_cost"] + $hasAsIs["lamp_disposal"]) * CalculateController::calcolaTotaleLampadePerHA($ha);
        }

        return $result;
    }

    public static function calcolaCostiManutezionePerHA($ha){
        return ($ha["lamp_cost"] + $ha["lamp_disposal"]) * CalculateController::calcolaTotaleLampadePerHA($ha);
    }

    /**
     * calcola i costi di manutenzione dell'impianto ToBe restituendo un risultato per ogni HA
     * @input $has(lamp_cost)
     * @input $has(lamp_disposal)
     * @input calcolaTotaleLampadePerHA($ha)
     * */

    public static function calcolaCostiManutenzioneToBe($plant){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
        $result = [];
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result[$i] = ($ha["lamp_cost"] + $ha["lamp_disposal"]) * CalculateController::calcolaTotaleLampadePerHA($ha);
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
            $result[$i] = $ha["infrastructure_maintenance_cost"] * CalculateController::calcolaTotaleLampadePerHA($ha);
        }
        return $result;
    }

    public static function calcolaCostoManutenzioneInfrastrutturaPerHA($ha){
        return $ha["infrastructure_maintenance_cost"] * CalculateController::calcolaTotaleLampadePerHA($ha);
    }


    /**
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
     * altri inpui di ciclo
     *
     * */
    public static function calcoloFlussiDiCassaPerPlant($plant, $investment){
        $hasAsIs = SaveToolController::getHasByPlantId($plant["id"])["dataAsIs"];
        $result = 0;
        //ricavo
        for ($i = 0; $i < count($hasAsIs); $i++){
            $ha = $hasAsIs[$i];
            $result += CalculateController::calcolaCostiManutezionePerHA($ha) * ($investment["duration_amortization"] / floor($ha["maintenance_interval"]) );
        }

        //ricavi
        $result += CalculateController::calcoloDeltaSpesaEnergeticaPerImpianto($plant);
        $result += CalculateController::calcolaIncentiviStataliPerImpiantoAndInvestimento($plant, $investment);
        //costo
        $result -= $investment["mortgage_installment"] *  $investment["duration_amortization"];
        $result -= $investment["mortgage_installment"] *  $investment["fee_esco"];

        //costo
        $hasToBe = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
        for ($i = 0; $i < count($hasToBe); $i++){
            $ha = $hasToBe[$i];
            $result -= CalculateController::calcolaCostiManutezionePerHA($ha) * ($investment["duration_amortization"] / floor($ha["maintenance_interval"]) );
        }

        //costo
        $hasToBe = SaveToolController::getHasByPlantId($plant["id"])["dataToBe"];
        for ($i = 0; $i < count($hasToBe); $i++){
            $ha = $hasToBe[$i];
            $result -= CalculateController::calcolaCostoManutenzioneInfrastrutturaPerHA($ha) * ($investment["duration_amortization"] / floor($ha["maintenance_interval"]) );
        }

        $result -= $investment["management_cost"];

        return $result;
    }


}
