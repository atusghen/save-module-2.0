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

        $data["output"] = LabController::testVanTir();
        return view('lab')->with('data', $data);
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
}


