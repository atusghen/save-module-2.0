<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Helpers\Utilities;
use App\Helpers\CtsIp;
use App\Models\Municipality;
use Illuminate\Support\Facades\DB;
use App\Models\DynamicData;
use App\Models\CensusTechSheet;
use Carbon\Carbon;
use App\Helpers\WidgetBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use App\Http\Helpers\SaveCompute;

class LabController extends Controller
{
    public function index()
    {
        $data["output"] = LabController::testAltreModalita();
        return view('lab')->with('data', $data);
    }

    private static function testPilota(){
        $plantInvestmentInput = [
            "plant_id"              => 9,
            "investment_id"         => 2,
        ];
        $result = SaveCompute::computePlantsOutput($plantInvestmentInput);

        return $result;
    }


    private static function testVanTir() {
        $vanTirInput = [
            "wacc"                  => [3,5,7],
            "amortization_duration" => [12,24,36],
            "plant_id"              => 9,
            "investment_id"         => 2,
        ];
        $result = SaveCompute::computeVanTir($vanTirInput);

        return $result;
    }

    private static function testPayBack() {
        $payback = [
            "min_energy_cost"           => 0.1,
            "max_energy_cost"           => 0.28,
            "plant_id"              => 9,
            "investment_id"         => 2,
            "points"                => 10
        ];
        $result = SaveCompute::computePayBack($payback);

        return $result;
    }

    private static function testAltreModalita() {
        $payback = [
            "min_fee_duration"      =>12,
            "max_fee_duration"      =>33,
            "taxes"                 =>35.78,
            "financed_quote"        =>75.90,
            "plant_id"              => 9,
            "investment_id"         => 2,
            "points"                => 10
        ];
        $result = SaveCompute::computeFee($payback);

        return $result;
    }
}


