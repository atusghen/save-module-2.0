<?php

interface InvestmentInterface
{

//    recupera i parametri degli investimenti inseriti per comune (di login)
    public function getInvestmentsParamsByMunicipality_get();

//    stessa cosa di cui sopra ma per utente?
    public function getInvestmentsParams_get();

//    recupera il costo energia unitario
    public function getUnitaryEnergyCost_get();

//    prende i parametri di un singolo Investimento specificato per ID
    public function getInvestmentParams_get();

//    salva i parametri di un singolo investimento a DB
    public function addInvestmentParams_post();

//    aggiorna i parametri di investimento a DB
    public function updateInvestmentParams_post();

//  ovvio
    public function deleteInvestmentParams_post();

//  sembra inizializzi un calcolo di investimento
    public function calcInvestment_post();

//    sembra inizializzi il tempo di payback
    public function calcRecapPaybacktime_post();

//    sembra inizializzi  le tasse
    public function calcRecapFee_post();

//    sembra inizializzi  il VANTIR
    public function calcRecapVANTIR_post();
}


