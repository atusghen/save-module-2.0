<?php
interface IsimmController {

//    recupera un riassunto di una simulazione
//    @input Impianto, @input ZonaOmogenea, @input Investimento
    public function get_recap($plantGuid , $sectionGuid , $investmentGuid );

//    restituisce l'ENUM del tipo di tecnologia utilizzata
    public static function getLampTechType();

//  filtra un array (una sembra più una hashmap) sulla base di un array di chiavi consentite e
//  @return un array filtrato
    public function filterArray(&$array, $allowedKeys );

//  @return tutte le ipotesi di investimento, più probabilmente le simulazioni per quello specifico oggetto Investiemnto
    public function getAllHypothesis();

//    sembra una funzione di FE in grado di restituire un elemento della View dinamico
    public function addFormElem($formId, $elemId, $label, $placeholder = '', $required = false, $formType = 'number', array $formCol = ['col-sm-3', 'col-sm-7'] );

//  sembra un'altro componente di FE
    public function fullRender($fullPath, array $options = [] );

//    recupera i parametri dell'oggetto Investimento, probabilmente per una visualizzazione dettagliata
    public function getInvestmentDetailBySection($guid);

//    recupera i parametri dell'oggetto Impiantp, probabilmente per una visualizzazione dettagliata
    public function getPlantDetail($plantGuid);

//    recupera i parametri dell'oggetto HAS, recuperando anche i parametri aggiuntivi della TOBE
//    probabilmente per una visualizzazione dettagliata
    public function  getSectionDetail_TOBE($sectionGuid);

//    recupera i parametri dell'oggetto HAS, recuperando solo i parametri della ASIS
//    probabilmente per una visualizzazione dettagliata
	public function getSectionDetail_ASIS($sectionGuid);

//  sembra una funzione validatrice di una entry di parametri TOBE, ma non è ben chiaro
    public function splitTOBEParams($tobeParams, $allowed);

}

