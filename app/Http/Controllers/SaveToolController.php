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

        $plant["id"] = 2;
        $investment = (SaveToolController::getInvestmentById(1)["investment"]);
        $result =  CalculateHelper::calcoloFlussiDiCassaPerPlant($plant, $investment);
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

    public function calcoloVanETir(Request $request, $id_crypted = null){
        $result = []; $result["success"] = false; $result["data"] = [];
        if($request->has('wacc') && $request->has('amortization_duration'))
        {
            $plant["id"] = 2;
            $investment = (SaveToolController::getInvestmentById(1)["investment"]);
            $amortizationDurationArray = [$request->amortization_duration[0], $request->amortization_duration[1], $request->amortization_duration[2]];
            $waccArray = [$request->wacc[0], $request->wacc[1], $request->wacc[2]];

            $flussoDiCassa1 = CalculateHelper::calcoloFlussiDiCassaPerHA($plant, $investment, $amortizationDurationArray[0]);
            $flussoDiCassa2 = CalculateHelper::calcoloFlussiDiCassaPerHA($plant, $investment, $amortizationDurationArray[1]);
            $flussoDiCassa3 = CalculateHelper::calcoloFlussiDiCassaPerHA($plant, $investment, $amortizationDurationArray[2]);

            $flussiDiCassaTotali1 =  CalculateHelper::calcoloFlussiDiCassaPerPlant($flussoDiCassa1);
            $flussiDiCassaTotali2 =  CalculateHelper::calcoloFlussiDiCassaPerPlant($flussoDiCassa2);
            $flussiDiCassaTotali3 =  CalculateHelper::calcoloFlussiDiCassaPerPlant($flussoDiCassa3);

            $result["data"]["van"][0][0] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali1, $waccArray[0]);
            $result["data"]["van"][0][1] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali1, $waccArray[1]);
            $result["data"]["van"][0][2] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali1, $waccArray[2]);

            $result["data"]["van"][1][0] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali2, $waccArray[0]);
            $result["data"]["van"][1][1] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali2, $waccArray[1]);
            $result["data"]["van"][1][2] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali2, $waccArray[2]);

            $result["data"]["van"][2][0] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali3, $waccArray[0]);
            $result["data"]["van"][2][1] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali3, $waccArray[1]);
            $result["data"]["van"][2][2] = CalculateHelper::calcoloVANperImpianto($flussiDiCassaTotali3, $waccArray[2]);

 //           $result["data"]["tir"][0] = CalculateHelper::calcoloTIRperImpianto($flussiDiCassaTotali, $amortizationDurationArray[0]);
//            $result["data"]["tir"][1] = CalculateHelper::calcoloTIRperImpianto($flussiDiCassaTotali, $amortizationDurationArray[1]);
//            $result["data"]["tir"][2] = CalculateHelper::calcoloTIRperImpianto($flussiDiCassaTotali, $amortizationDurationArray[2]);
            $result["success"] = true;
        }
        else
            $waccArray = 0; $amortizationDurationArray = 0;
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
