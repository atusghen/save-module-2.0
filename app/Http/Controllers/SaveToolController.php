<?php

namespace App\Http\Controllers;
use App\Helpers\Utilities;
use App\Http\Requests\ParamFormRequest;
use App\Models\DTO\SaveCluster;
use App\Models\DTO\SaveHA;
use App\Models\DTO\SaveInvestment;
use App\Models\DTO\SavePlant;
use App\Models\SaveAnalysisView;
use Illuminate\Http\Request;

/*
 * Questa classe contiene tutti i metodi di get dal database necessari per fare i calcoli
 * Effettuano delle query che restituiscono un risultato composto, ma non implementano alcuna logica di calcolo e sono
 * quindi agnostici
 * */

class SaveToolController extends Controller
{

    ////////////////////////////////////////////
    /// Utility methods (debug)
    ///////////////////////////////////////////

    public function readHasView(Request $request, $id_crypted = null){
        $data["fields"] = config("save");
        $data["has"] = SaveToolController::getHasByPlantId(3);
        dd($data["has"]);
        return view("has")->with('data', $data);
    }

    public function readPlantsView(Request $request, $id_crypted = null){
        $data["fields"] = config("save");
        $data["plants"] = SaveToolController::getPlantsByUser(3);
        dd($data["plants"]);
        return view("plants")->with('data', $data);
    }

    public function showFlussiDiCassaPerPlant(Request $request, $id_crypted = null){

        $plant["id"] = 1;
        $investment = (SaveToolController::getInvestmentById(1)["investment"]);
        $result =  CalculateController::calcoloFlussiDiCassaPerPlant($plant, $investment);
        dd($result);  //eliminato con la view
        return view("tryCalculate")->with('data',$result);
    }

    public function showImportoInvestimentoPerHA(Request $request, $id_crypted = null){

        $data = SaveToolController::getHasByPlantId(1);
        $result =  CalculateController::calcolaImportoInvestimentoPerHA($data["dataToBe"][0]);
        //dump($result);  eliminato con la view
        return view("tryCalculate")->with('data',$result);
    }

    public function showSpesaEnergeticaPerHA(Request $request, $id_crypted = null){
        //$data["fields"] = config("save");

        $has = SaveToolController::getHasByPlantId(1);

        //recupero il parametro dall'investimento selezionato
        $energyCost = (SaveToolController::getEnergyUnitCostForInvestment(1))["energy_unit_cost"];
        $result = CalculateController::calcoloSpesaEnergeticaPerHa(($has["dataToBe"])[0], $energyCost);
        //$result2 = CalculateController::calcoloConsumoEnergeticoPerHa($data["payload"]["clusters"]);
        //dump($energyCost);   //si può usare al posto di dd e consente l'esecuzione del resto dello script, ma ha bisogno di una view Associata?
        return view("tryCalculate")->with('data',$result);
    }

    public function showDebug(Request $request, $id_crypted = null){
        $plant["id"] = 1;
        $investment = (SaveToolController::getInvestmentById(1)["investment"]);
        $result = CalculateController::calcolo($plant, $investment);
        echo json_encode($result);
        dd($result);
        return view("tryCalculate")->with('data',$result);
    }


    ////////////////////////////////////////////////
    /// CRUD Methods
    ////////////////////////////////////////////////

    protected static function getPlantsByUser($user_id){
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

    protected static function getPlantsByMunicipality($municipality_code){
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


    public static function getHasByPlantId($plant_id){
        $result = [
            "success" => false,
            "dataAsIs" => [],
            "dataToBe" => []
        ];

        $hasASIS = SaveHA::where("plant_id",$plant_id)->where("type_ha","ASIS")->get();
        if ($hasASIS) {
            $result["dataAsIs"] = $hasASIS->toArray();
            $result["success"] = true;
        }


        $hasTOBE = SaveHA::where("plant_id",$plant_id)->where("type_ha","TOBE")->get();
        if ($hasTOBE) {
            $result["dataToBe"] = $hasTOBE->toArray();
            $result["success"] = true;
        }

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

    public static function getClustersByHaId_TOBEfeatured($ha_id){
        $result = [
            "success" => false,
            "clusters" => ""
        ];

        $clusters = SaveCluster::where("ha_id",$ha_id)->where("is_to_be_featured","1")->get();
        if ($clusters) {
            $result["clusters"] = $clusters->first();
            $result["success"] = true;
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

}
