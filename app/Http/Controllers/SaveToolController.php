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
use App\Http\Helpers\CalculateHelper;

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

        $plant = SaveToolController::getPlantById(2)["plant"];
        $investment = (SaveToolController::getInvestmentById(1)["investment"]);+

        $flussoDiCassa = CalculateHelper::calcoloFlussiDiCassaPerHA($plant, $investment, null);
        $result =  CalculateHelper::calcoloFlussiDiCassaPerPlant($flussoDiCassa);
        dd($result);  //eliminato con la view
        return view("tryCalculate")->with('data',$result);
    }

    public function showImportoInvestimentoPerHA(Request $request, $id_crypted = null){

        $data = SaveToolController::getHasByPlantId(1);
        $result =  CalculateHelper::calcolaImportoInvestimentoPerHA($data["dataToBe"][0]);
        //dump($result);  eliminato con la view
        return view("tryCalculate")->with('data',$result);
    }

    public function showSpesaEnergeticaPerHA(Request $request, $id_crypted = null){
        //$data["fields"] = config("save");

        $has = SaveToolController::getHasByPlantId(1);

        //recupero il parametro dall'investimento selezionato
        $energyCost = (SaveToolController::getEnergyUnitCostForInvestment(1))["energy_unit_cost"];
        $result = CalculateHelper::calcoloSpesaEnergeticaPerHa(($has["dataToBe"])[0], $energyCost);
        //$result2 = CalculateController::calcoloConsumoEnergeticoPerHa($data["payload"]["clusters"]);
        //dump($energyCost);   //si può usare al posto di dd e consente l'esecuzione del resto dello script, ma ha bisogno di una view Associata?
        return view("tryCalculate")->with('data',$result);
    }

    public function showDebug(Request $request, $id_crypted = null){
        $plant = SaveToolController::getPlantById(1)["plant"];
        $investment = (SaveToolController::getInvestmentById(1)["investment"]);
        $result = CalculateHelper::calcoloPilota($plant, $investment);
        echo json_encode($result);
        dd($result);
        return view("tryCalculate")->with('data',$result);
    }

//127.0.0.1:8000/VanETir?plantId=1&investmentId=1&wacc[]=3&wacc[]=5&wacc[]=7&amortization_duration[]=12&amortization_duration[]=24&amortization_duration[]=36
    public function calcoloVanETir(Request $request, $id_crypted = null){
        $result = []; $result["success"] = false; $result["data"] = [];
        if($request->has('wacc') && $request->has('amortization_duration') && $request->has('plantId') && $request->has('investmentId'))
        {
            $plant = SaveToolController::getPlantById($request->plantId)["plant"];
            $investment = (SaveToolController::getInvestmentById($request->investmentId)["investment"]);

            for ($i = 0; $i < count($request->amortization_duration); $i++) {
                $flussoDiCassa[$i] = CalculateHelper::calcoloFlussiDiCassaPerHA($plant, $investment, $request->amortization_duration[$i]);
                $flussiDiCassaTotali[$i] =  CalculateHelper::calcoloFlussiDiCassaPerPlant($flussoDiCassa[$i]);
                $investment_amount = CalculateHelper::calcolaImportoInvestimentoPerPlant($flussoDiCassa[$i]);

                for ($j = 0; $j < count($request->wacc); $j++) {
                    $result["data"]["van"][$i][$j] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali[$i], $request->wacc[$j]);
                }
                $result["data"]["tir"][$i] = CalculateHelper::calcoloTIRperImpianto($flussiDiCassaTotali[$i], $investment_amount);
            }

            $result["success"] = true;
        }
        else
        {
            $result["data"]["van"] = 0; $result["data"]["tir"] = 0;
        }

        dd($result);
        return view("tryCalculate")->with('data',$result);
    }



    ////////////////////////////////////////////////
    /// CRUD Methods
    ////////////////////////////////////////////////

    protected static function getPlantById($plant_id){
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
