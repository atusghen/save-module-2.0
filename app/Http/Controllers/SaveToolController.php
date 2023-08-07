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
        $data["has"] = SaveToolController::getHas(3);
        dd($data["has"]);
        return view("has")->with('data', $data);
    }

    public function readPlantsView(Request $request, $id_crypted = null){
        $data["fields"] = config("save");
        $data["plants"] = SaveToolController::getPlants(3);
        dd($data["plants"]);
        return view("plants")->with('data', $data);
    }

    private static function getPlants($user_id){
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

    private static function getHas($user_id){
        $result = [
            "success" => true,
            "data" => []
        ];

        $has = SaveHA::where("user_id",$user_id)->get();
        if ($has) {
            $result["data"] = $has->toArray();
        }

        return $result;
    }


}
