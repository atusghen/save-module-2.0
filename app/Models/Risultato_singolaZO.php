<?php

namespace App\Models;
class Risultato_singolaZO {

    public $asis_name = "";

    public $tobe_name = "";

    public $investment_amount = 0;

    public $asis_maintenance_cost = [];

    public $tobe_maintenance_cost = 0;

    public $incentive_revenue = 0;

    public $delta_energy_expenditure = 0;

    public $delta_energy_consumption = 0;

    public $cash_flow = [];

    /**
     * @return string
     */
    public function getAsisName(): string
    {
        return $this->asis_name;
    }

    /**
     * @param string $asis_name
     */
    public function setAsisName(string $asis_name): void
    {
        $this->asis_name = $asis_name;
    }

    /**
     * @return string
     */
    public function getTobeName(): string
    {
        return $this->tobe_name;
    }

    /**
     * @param string $tobe_name
     */
    public function setTobeName(string $tobe_name): void
    {
        $this->tobe_name = $tobe_name;
    }

    /**
     * @return string
     */
    public function getInvestmentAmount()
    {
        return $this->investment_amount;
    }

    /**
     * @param string $investment_amount
     */
    public function setInvestmentAmount(string $investment_amount): void
    {
        $this->investment_amount = $investment_amount;
    }

    /**
     * @return string
     */
    public function getAsisMaintenanceCost()
    {
        return $this->asis_maintenance_cost;
    }

    /**
     * @param string $asis_maintenance_cost
     */
    public function setAsisMaintenanceCost(string $asis_maintenance_cost): void
    {
        $this->asis_maintenance_cost = $asis_maintenance_cost;
    }

    /**
     * @return string
     */
    public function getTobeMaintenanceCost()
    {
        return $this->tobe_maintenance_cost;
    }

    /**
     * @param string $tobe_maintenance_cost
     */
    public function setTobeMaintenanceCost(string $tobe_maintenance_cost): void
    {
        $this->tobe_maintenance_cost = $tobe_maintenance_cost;
    }

    /**
     * @return string
     */
    public function getIncentiveRevenue()
    {
        return $this->incentive_revenue;
    }

    /**
     * @param string $incentive_revenue
     */
    public function setIncentiveRevenue(string $incentive_revenue): void
    {
        $this->incentive_revenue = $incentive_revenue;
    }

    /**
     * @return string
     */
    public function getDeltaEnergyExpenditure()
    {
        return $this->delta_energy_expenditure;
    }

    /**
     * @param string $delta_energy_expenditure
     */
    public function setDeltaEnergyExpenditure(string $delta_energy_expenditure): void
    {
        $this->delta_energy_expenditure = $delta_energy_expenditure;
    }

    /**
     * @return string
     */
    public function getDeltaEnergyConsumption()
    {
        return $this->delta_energy_consumption;
    }

    /**
     * @param string $delta_energy_consumption
     */
    public function setDeltaEnergyConsumption(string $delta_energy_consumption): void
    {
        $this->delta_energy_consumption = $delta_energy_consumption;
    }

    /**
     * @return array
     */
    public function getCashFlow(): array
    {
        return $this->cash_flow;
    }

    /**
     * @param array $cash_flow
     */
    public function setCashFlow(array $cash_flow): void
    {
        $this->cash_flow = $cash_flow;
    }
}
