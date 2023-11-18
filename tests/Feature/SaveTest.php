<?php

namespace Tests\Feature;

use App\Http\Controllers\SaveToolController;
use App\Http\Helpers\CalculateHelper;
use App\Models\Risultato_singolaZO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

class SaveTest extends TestCase
{
    /**
     * Testing requisito 1: calcolo importo investimento per la zona omogenea scelta
     *
     * @return void
     */
    public function test_calcoloImportoInvestimentoPerHA(){

        //la funzione deve tornare un numero maggiore o uguale a zero

        $has = SaveToolController::getHasByPlantId(1);

        for($i = 0; $i < count($has["dataAsIs"]); $i++){
            self::assertGreaterThanOrEqual(0, CalculateHelper::calcolaImportoInvestimentoPerHA($has["dataAsIs"][$i]));
        }
    }

    /**
     * Testing requisito 2: delta consumo energetico tra zona omogenea AS-IS e TO-BE
     *
     * @return void
     */
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

    /**
     * Testing requisito 3: delta spesa energetica tra zona omogenea AS-IS e TO-BE
     *
     * @return void
     */
    public function test_calcoloDeltaSpesaEnergeticaPerHAS()
    {
        //verifichiamo che se uno dei parametri sono nulli, l'operazione non vada in errore
        assertNotNull(CalculateHelper::calcoloDeltaSpesaEnergeticaPerHAS(null, null, null, null));

        //verifichiamo che tutti parametri sono non nulli, allora la cella del risultato è non nulla
        $has = SaveToolController::getHasByPlantId(1);
        $arrayASIS = $has["dataAsIs"];
        $arrayTOBE = $has["dataToBe"];
        $result = [];
        for ($i = 0; $i < count($arrayASIS); $i++){
            $result[$i] = new Risultato_singolaZO();

            //singola HA ASIS
            $haASIS = $arrayASIS[$i];
            $result[$i]->setAsisName($haASIS["label_ha"]);

            //getting TOBE associata
            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_as_is_id_ha"] == $haASIS["id"];
            })->first();

            assertNotNull(CalculateHelper::calcoloDeltaSpesaEnergeticaPerHAS($haASIS, $haTOBE, 0.19, $result));
            assertNotNull($result[$i]->getDeltaEnergyExpenditure());
        }
    }

    /**
     * Testing requisito 4: Calcolo degli incentivi statali
     *
     * @return void
     */
    public function test_calcoloIncentiviStatali(){
        //controlliamo che la funzione non vada in errore con dei valori nulli
        CalculateHelper::calcoloIncentiviStatali(null, null, null);

        //controlliamo la divisione per 0 di tepKwh
        $result = new Risultato_singolaZO();
        CalculateHelper::calcoloIncentiviStatali(0, 10, $result);
        assertNotNull($result->getIncentiveRevenue());

        //controlliamo che la funzione restituisca un risultato se i parametri passati sono non nulli
        $result = new Risultato_singolaZO();
        CalculateHelper::calcoloIncentiviStatali(1000, 10, $result);
        assertNotNull($result->getIncentiveRevenue());
    }

    public function test_calcoloCostiManutenzione(){
        //controlliamo che la funzione non vada in errore con dei valori nulli
        assertNotNull(CalculateHelper::calcoloCostiManutenzione(null, null, null, null));

        //verifichiamo che tutti parametri sono non nulli, allora la cella del risultato è non nulla
        $has = SaveToolController::getHasByPlantId(1);
        $arrayASIS = $has["dataAsIs"];
        $arrayTOBE = $has["dataToBe"];
        $result = [];
        for ($i = 0; $i < count($arrayASIS); $i++) {
            $result[$i] = new Risultato_singolaZO();

            //singola HA ASIS
            $haASIS = $arrayASIS[$i];
            $result[$i]->setAsisName($haASIS["label_ha"]);

            //getting TOBE associata
            $haTOBE = collect($arrayTOBE)->filter(function ($single) use ($haASIS) {
                return $single["ref_as_is_id_ha"] == $haASIS["id"];
            })->first();

            //controlliamo che la funzione restituisca un risultato se i parametri passati sono non nulli


            $costiManutenzione = CalculateHelper::calcoloCostiManutenzione(30, $haASIS, $haTOBE, $result[$i]);

            assertNotNull($costiManutenzione["asis_maintenance_cost"]);
            assertNotNull($costiManutenzione["tobe_infrastructure_cost"]);
            assertNotNull($costiManutenzione["tobe_lamp_cost"]);
        }


    }
}
