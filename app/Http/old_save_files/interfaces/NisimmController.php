<?php

interface NisimmController
{

//  renderizza component di FE
    public function fullRender($fullPath, array $options = []);

//    renderizza l'index del modulo SAVE per impianto?
    public function index();

//    altra roba di FE inutile
    public function content();

//    altra roba di FE inutile
    public function recap($hp_guid, $out = 'recap');

//    inizializza il FE degli impianti?
    public function impianti();

//   inizializza il FE degli investimenti?
    public function investimenti();

//    inizializza il calcolo del tempo di Payback per l'investimento
    public function calcRecapPaybacktime($inputData);

//    inizializza il calcolo delle tasse
    public function calcRecapFee($inputData);

//    inizializza il calcolo del VAN?
    public function calcRecapVANTIR($inputData);

//    self-explanatory
    public function download_recap();

//    per la visualizzazione d grafici
    public function chartRender($chartData);

}

