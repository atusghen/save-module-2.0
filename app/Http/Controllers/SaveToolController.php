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

    public function readView(Request $request, $id_crypted = null){
        $data["fields"] = config("save");
        return view("savetool")->with('data', $data);
    }

    private static function getTest($user_id){
        $result = [
            "success" => true,
            "data" => []
        ];

        $user_id = Auth::user()->id;
        $plants = SavePlant::where("user_id",$user_id)->get();
        if ($plants) {
            $result["data"] = $plants->toArray();
        }

        return $result;
    }



}
