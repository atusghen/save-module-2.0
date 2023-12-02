<?php

namespace App\Http\old_save_files\helpers\isimm;
class CashflowLib
{
    /*
     * flusso_di_cassa[] = [ -(importo_investimento), VAL_anno_1, VAL_anno_2, VAL_anno_3 ]
     */
    private $cashValues = [];

    /*
     * durata investimento (o ammortamento)
     */
    private $durataInvestimento;

    /*
     * importo investimento
     */
    private $importoInvestimento;

    /*
     * delta spesa investimento
     */
    private $deltaSpesaInvestimento;

    /*
     * ricavo incentivi statali
     */
    private $ricavoIncentiviStatali;

    /*
     * durata incentivi statali
     */
    private $durataIncentiviStatali;

    /*
     * costo manutenzione sezione AS-IS
     */
    private $costoManutenzione_ASIS;

    /*
     * durata manutenzione sezione AS-IS
     */
    private $durataManutenzione_ASIS;

    /*
     * costo manutenzione sezione TO-BE
     */
    private $costoManutenzione_TOBE;

    /*
     * durata manutenzione sezione TO-BE
     */
    private $durataManutenzione_TOBE;

    /*
     * servizi SAL e SSS
     */
    private $services = [];

    /*
     * costo gestione
     */
    private $costoGestione;


    private $WACC;

    /**
     * Cashflow constructor.
     *
     * @param $params
     */
    public function __construct(array $params = [])
    {
        $this->durataInvestimento = array_get($params, 'duration');
        $this->importoInvestimento = array_get($params, 'investmentPrice');
        $this->deltaSpesaInvestimento = array_get($params, 'deltaEnergeticCost');
        $this->ricavoIncentiviStatali = array_get($params, 'incentiveIncomeRevenue');
        $this->durataIncentiviStatali = array_get($params, 'incentiveIncomeRange');
        $this->costoManutenzione_ASIS = array_get($params, 'maintenanceCost_ASIS');
        $this->durataManutenzione_ASIS = array_get($params, 'maintenanceDuration_ASIS');
        $this->costoManutenzione_TOBE = array_get($params, 'maintenanceCost_TOBE');
        $this->durataManutenzione_TOBE = array_get($params, 'maintenanceDuration_TOBE');
        $this->costoGestione = array_get($params, 'managementPrice');
        $this->WACC = array_get($params, 'wacc');
        /* init cashValue with -(importoInvestimento) */
        $this->initCashValue();
    }

    private function initCashValue()
    {
        $this->cashValues[0] = -$this->importoInvestimento;
    }

    public function addSALService($period, $cost)
    {
        $this->services[] = new Service($period, $cost, 'SAL');
    }

    public function addSSSService($period, $cost)
    {
        $this->services[] = new Service($period, $cost, 'SSS');
    }

    public function getCashValue($index = null)
    {
        if ($index !== null) {
            return array_get($this->cashValues, $index, 0);
        }

        return $this->cashValues;
    }

    public function addCashValue($val, $round = 3)
    {
        $this->cashValues[] = round($val, $round);

        return count($this->cashValues) - 1;
    }


    public function calcCashFlow()
    {
        for ($year = 1; $year <= $this->durataInvestimento; $year++) {
            $profit = 0;
            $effort = 0;

            $profit += (float)$this->calcSommaRicavi($year);
            $effort += (float)$this->calcSommaCosti($year);

            /**    dealing with costants */
            $profit += (float)$this->deltaSpesaInvestimento;
            $effort += (float)$this->costoGestione;

            $this->addCashValue($profit - $effort);
        }

    }

    public static function calcVAN_ByCashFlowByWacc($cashflow, $wacc, $round = 3)
    {
        /*
         * n.b. WACC expressed in percent values
         */
        $wacc_absolute = (float)$wacc / 100;
        return round(MathPHP::npv($wacc_absolute, $cashflow), $round);
    }

    public static function calcTIR_ByCashFlow($cashflow, $round = 3)
    {
        return round(IRRHelper::IRR($cashflow), $round);
    }

    public function calcVAN($round = 3)
    {
        /*
         * n.b. WACC expressed in percent values
         */
        $wacc_absolute = (float)$this->WACC / 100;
        return round(MathPHP::npv($wacc_absolute, $this->cashValues), $round);
    }

    public function calcTIR($round = 3)
    {
        return round(IRRHelper::IRR($this->cashValues), $round);
    }

    /*
     * calcolo somma dei ricavi
     */
    private function calcSommaRicavi($year)
    {
        $partialSum = 0;

        if ($year <= $this->durataIncentiviStatali) {
            $partialSum += $this->ricavoIncentiviStatali;
        }

        if ($this->durataManutenzione_ASIS > 0 && $year % $this->durataManutenzione_ASIS === 0) {
            $partialSum += $this->costoManutenzione_ASIS;
        }


        /** @var Service $service */
        foreach ($this->services as $service) {
            if ($service->period >= $year) {
                $partialSum += $service->cashFlow;
            }
        }

        return $partialSum;
    }


    /*
     * calcolo somma dei costi
     */
    private function calcSommaCosti($year)
    {
        $partialSum = 0;

        if ($this->durataManutenzione_TOBE > 0 && $year % $this->durataManutenzione_TOBE === 0) {
            $partialSum += $this->costoManutenzione_TOBE;
        }

        return $partialSum;
    }

}


class Service
{

    /*
     * durata del servizio
     */
    public $period;

    /*
     * flusso di cassa del servizio;
     */
    public $cashFlow;

    /*
     * tipo del servizio;
     */
    public $type;


    /**
     * Services constructor.
     * @param $period
     * @param $cashFlow
     * @param $type
     */
    public function __construct($period, $cashFlow, $type)
    {
        $this->period = $period;
        $this->cashFlow = $cashFlow;
        $this->type = $type;
    }


}

class MathPHP
{

    /**
     * Net present value of cash flows. Cash flows are periodic starting
     * from an initial time and with a uniform discount rate.
     *
     * Similar to the =NPV() function in most spreadsheet software, except
     * the initial (usually negative) cash flow at time 0 is given as the
     * first element of the array rather than subtracted. For example,
     *   spreadsheet: =NPV(0.01, 100, 200, 300, 400) - 1000
     * is done as
     *   MathPHP::npv(0.01, [-1000, 100, 200, 300, 400])
     *
     * The basic net-present-value formula derivation:
     * https://en.wikipedia.org/wiki/Net_present_value
     *
     *  n      Rt
     *  Σ   --------
     * t=0  (1 / r)ᵗ
     *
     * Examples:
     * The net present value of 5 yearly cash flows after an initial $1000
     * investment with a 3% discount rate:
     *  npv(0.03, [-1000, 100, 500, 300, 700, 700])
     *
     * @param float $rate
     * @param array $values
     *
     * @return float
     */
    public static function npv($rate, array $values)
    {
        $result = 0;
        $totVal = count($values);

        for ($i = 0; $i < $totVal; ++$i) {
            $result += $values[$i] / (1 + $rate) ** $i;
        }
        return $result;
    }
}

class IRRHelper
{

    //Adapted from Javascript version here: https://gist.github.com/ghalimi/4591338
    //
    // Copyright (c) 2012 Sutoiku, Inc. (MIT License)
    //
    // Some algorithms have been ported from Apache OpenOffice:
    //
    /**************************************************************
     *
     * Licensed to the Apache Software Foundation (ASF) under one
     * or more contributor license agreements.  See the NOTICE file
     * distributed with this work for additional information
     * regarding copyright ownership.  The ASF licenses this file
     * to you under the Apache License, Version 2.0 (the
     * "License"); you may not use this file except in compliance
     * with the License.  You may obtain a copy of the License at
     *
     *   http://www.apache.org/licenses/LICENSE-2.0
     *
     * Unless required by applicable law or agreed to in writing,
     * software distributed under the License is distributed on an
     * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
     * KIND, either express or implied.  See the License for the
     * specific language governing permissions and limitations
     * under the License.
     *
     *************************************************************/

    public static function IRR($values, $guess = 0.1)
    {
        // Credits: algorithm inspired by Apache OpenOffice

        // Initialize dates and check that values contains at least one positive value and one negative value
        $dates = array();
        $positive = false;
        $negative = false;
        foreach ($values as $index => $value) {
            $dates[] = ($index === 0) ? 0 : $dates[$index - 1] + 365;
            if ($values[$index] > 0) $positive = true;
            if ($values[$index] < 0) $negative = true;
        }

        // Return error if values does not contain at least one positive value and one negative value
        if (!$positive || !$negative) return null;

        // Initialize guess and resultRate
        $resultRate = $guess;

        // Set maximum epsilon for end of iteration
        $epsMax = 0.0000000001;

        // Set maximum number of iterations
        $iterMax = 50;

        // Implement Newton's method
//			$newRate;
//			$epsRate;
//			$resultValue;
        $iteration = 0;
        $contLoop = true;
        while ($contLoop && (++$iteration < $iterMax)) {
            $resultValue = self::irrResult($values, $dates, $resultRate);
            $newRate = $resultRate - $resultValue / self::irrResultDeriv($values, $dates, $resultRate);
            $epsRate = abs($newRate - $resultRate);
            $resultRate = $newRate;
            $contLoop = ($epsRate > $epsMax) && (abs($resultValue) > $epsMax);
        }

        if ($contLoop) return null;

        // Return internal rate of return
        return $resultRate;
    }

    // Calculates the resulting amount
    public static function irrResult($values, $dates, $rate)
    {
        $r = $rate + 1;
        $result = $values[0];
        for ($i = 1; $i < count($values); $i++) {
            $result += $values[$i] / pow($r, ($dates[$i] - $dates[0]) / 365);
        }
        return $result;
    }

    // Calculates the first derivation
    public static function irrResultDeriv($values, $dates, $rate)
    {
        $r = $rate + 1;
        $result = 0;
        for ($i = 1; $i < count($values); $i++) {
            $frac = ($dates[$i] - $dates[0]) / 365;
            $result -= $frac * $values[$i] / pow($r, $frac + 1);
        }
        return $result;
    }

}
