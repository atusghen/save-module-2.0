<?php

interface HypothesisManagerInterface {

//    cancella una entry di Simulazione dal DB
    public function deleteHypothesis_post();

//    Check if any hypothesis is associated all'investimento
    public function checkHypothesis_post();


}
