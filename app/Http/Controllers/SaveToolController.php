<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ParamFormRequest;
use App\Helpers\Utilities;
use App\Models\SavePlant;
use App\Models\SaveHA;
use App\Models\SaveCluster;
use App\Models\SaveInvestment;
use App\Models\SaveAnalysisView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class SaveToolController extends Controller
{

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

    protected static function getPlantsByUser($user_id){
        $result = [
            "success" => true,
            "data" => []
        ];

        $plants = SavePlant::where("user_id",$user_id)->get();
        if ($plants) {
            $result["data"] = $plants->toArray();
        }

        return $result;
    }

    protected static function getPlantsByMunicipality($municipality_code){
        $result = [
            "success" => true,
            "data" => []
        ];

        $plants = SavePlant::where("municipality_code",$municipality_code)->get();
        if ($plants) {
            $result["data"] = $plants->toArray();
        }

        return $result;
    }


    protected static function getHasByPlantId($plant_id){
        $result = [
            "success" => true,
            "dataAsIs" => [],
            "dataToBe" => []
        ];

        $hasASIS = SaveHA::where("plant_id",$plant_id)->where("type","ASIS")->get();
        if ($hasASIS) {
            $result["dataAsIs"] = $hasASIS->toArray();
        }


        $hasTOBE = SaveHA::where("plant_id",$plant_id)->where("type","TOBE")->get();
        if ($hasTOBE) {
            $result["dataToBe"] = $hasTOBE->toArray();
        }

        return $result;
    }


    public static function getClustersByHaId($ha_id){
        $result = [
            "success" => true,
            "clusters" => []
        ];

        $clusters = SaveCluster::where("ha_id",$ha_id)->get();
        if ($clusters) {
            $result["clusters"] = $clusters->toArray();
        }

        return $result;
    }

    public static function getEnergyUnitCostForInvestment($investment_id)
    {
        $result = [
            "success" => true,
            "energy_unit_cost" => []
        ];

        $energy_unit_cost=SaveInvestment::where("id",$investment_id)->get(['energy_unit_cost']);
        if ($energy_unit_cost) {
            $result["energy_unit_cost"] = $energy_unit_cost->toArray();
        }

        return $result;
    }

    public static function getInvestmentById($investment_id)
    {
        $result = [
            "success" => true,
            "investment" => []
        ];

        $investment = SaveInvestment::where("id",$investment_id)->get();
        if ($investment) {
            $result["investment"] = $investment->toArray();
        }

        return $result;

    }


}
