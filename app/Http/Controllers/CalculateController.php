<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CalculateController extends Controller
{

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
        dump($result);   //si può usare al posto di dd e consente l'esecuzione del resto dello script, ma ha bisogno di una view Associata?
        return view("tryCalculate")->with('data',$result);
    }


    //calcolo parametro "Costi/benefici annuali in spesa energetica"
    public static function calcoloSpesaEnergeticaPerHa($clusters, $costo_unitario){

        $spesaEnergeticaHa = 0;

        for ($i = 0; $i < count($clusters); $i++) {
            $cluster = $clusters[$i];

            $spesaEnergetica = ($cluster["hours_full_lighting"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_lighting"]) * $cluster["lamp_num"]
                * $cluster["average_device_power"] * ((float)$costo_unitario[0] / 1000);

            $spesaEnergeticaHa += $spesaEnergetica;
        }

        return $spesaEnergeticaHa;

    }

    /**
     * @input costo_medio_lampada               -> $has[lamp_cost]
     * @input costo_infrastruttura              ->    non chiaro, forse $has[infrastructure_maintenance_cost]
     * @input costo_medio_smaltimento_lampada   -> $has[lamp_disposal]
     * @input n_lampade                         -> sum($clusters[lamp_num])
     * @input costo_rifacimento_imp_elett       -> $has[system_renovation_cost] colonna da aggiungere
     * @input costo_attività_prodomiche         -> $has[prodromal_activities_cost]
     * @input costo_quadro                      -> $has[panel_cost]
     * @input n_quadri_el                       -> sum($clusters[device_num])
     *
     * */
    public function importoInvestimentoHA($ha){
        $data["cluster"] = SaveToolController::getClustersByHaId(1);

    }


    public function deltaSpesaEnergetica($spesaEnergeticaHaAsIs, $spesaEnergeticaHaToBe){

        return $spesaEnergeticaHaAsIs - $spesaEnergeticaHaToBe;

    }

    //calcolo parametro "Costi/benefici annuali in consumo energetico"
    public function calcoloConsumoEnergeticoPerHa($result){

        $consumoEnergeticoHa = 0;

        for ($i = 0; $i < count($result["clusters"]); $i++) {
            $cluster = $result["clusters"][$i];

            $consumoEnergetico = ($cluster->hours_full_lighting + (1 - ($cluster->dimmering / 100)) * $cluster->hours_dimmer_lighting) * $cluster->lamp_num
                * $cluster->average_device_power;

            $consumoEnergeticoHa += $consumoEnergetico;
            dd($consumoEnergetico);
        }

        dd($consumoEnergeticoHa);

    }


    public function deltaConsumoEnergetico($consumoEnergeticoHaAsIs, $consumoEnergeticoHaToBe){

        return $consumoEnergeticoHaAsIs - $consumoEnergeticoHaToBe;

    }
}
