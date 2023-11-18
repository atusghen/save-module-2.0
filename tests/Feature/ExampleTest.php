<?php

namespace Tests\Feature;

use App\Http\Controllers\SaveToolController;
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
}
