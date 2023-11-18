<?php

namespace Tests\Feature;

use App\Http\Controllers\SaveToolController;
use App\Http\Helpers\CalculateHelper;
use App\Models\Risultato_singolaZO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function PHPUnit\Framework\assertNotNull;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $ha["id"] = 1;

        $response->assertStatus(200);
        $clusters = SaveToolController::getClustersByHaId($ha["id"])["clusters"];
        assertNotNull($clusters);

        self::assertEquals($clusters[0]["label_cluster"], "CLUSTER ASIS 3 verso HA ASIS 1");
    }

    public function test_calcoloImportoInvestimentoPerHA(){

        //la funzione deve tornare un numero maggiore o uguale a zero

        $has = SaveToolController::getHasByPlantId(1);

        for($i = 0; $i < count($has["dataAsIs"]); $i++){
            self::assertGreaterThanOrEqual(0, CalculateHelper::calcolaImportoInvestimentoPerHA($has["dataAsIs"][$i]));
        }
    }

    public function test_calcoloDeltaConsumoEnergeticoPerHAS(){

        //la funzione deve tornare un numero maggiore o uguale a zero

        $has = SaveToolController::getHasByPlantId(1);

        for($i = 0; $i < count($has["dataAsIs"]); $i++){

            $haASIS = $has["dataAsIs"][$i];

            //cerco la HA TOBE associata
            $haTOBE = collect($has["dataToBe"])->filter(function ($single) use ($haASIS) {
                return $single["ref_as_is_id_ha"] == $haASIS["id"];
            })->first();

            $result[$i] = new Risultato_singolaZO();
            $result[$i]->setAsisName($haASIS["label_ha"]);
            $result[$i]->setTobeName($haTOBE["label_ha"]);
            CalculateHelper::calcoloDeltaConsumoEnergeticoPerHAS($haASIS, $haTOBE, $result);
            self::assertNotNull($result[$i]->getDeltaEnergyConsumption());
        }
    }
}
