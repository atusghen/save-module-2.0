<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CalculateController extends Controller
{

    //calcolo parametro "Costi/benefici annuali in spesa energetica"

    public static function calcoloSpesaEnergeticaPerHa($result, $costo_unitario){

        $spesaEnergeticaHa = 0;

        for ($i = 0; $i < count($result); $i++) {
            $cluster = $result[$i];

            $spesaEnergetica = ($cluster["hours_full_lighting"] + (1 - ($cluster["dimmering"] / 100)) * $cluster["hours_dimmer_lighting"]) * $cluster["lamp_num"]
                * $cluster["average_device_power"] * ((float)$costo_unitario[0] / 1000);

            $spesaEnergeticaHa += $spesaEnergetica;
        }

        return $spesaEnergeticaHa;

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


    public function readHasView(Request $request, $id_crypted = null){
        $data["fields"] = config("save");
        $data["has"] = SaveToolController::getHas(3);
        dd($data["has"]);
        return view("has")->with('data', $data);
    }
}
