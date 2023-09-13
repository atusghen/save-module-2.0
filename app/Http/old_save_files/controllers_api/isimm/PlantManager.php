<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	/** @noinspection PhpIncludeInspection */
	require_once APPPATH . 'libraries/REST_Controller.php';

	class PlantManager extends REST_Controller
	{

		/* @var $session CI_Session */
		public $session;

		/* @var $uuid Uuid */
		public $uuid;

		private $municipalities = [];

		/*
		 * Loading models
		 */
		/* @var $nPlantModel nPlantModel */
		public $nPlantModel;
		/* @var $nSectionModel nSectionModel */
		public $nSectionModel;
		/* @var $PlantManagerLib PlantManagerLib */
		public $PlantManagerLib;
		/* @var $nRecapModel nRecapModel */
		public $nRecapModel;

		public function __construct()
		{
			parent::__construct();
//			$this->auth_required();
			$this->load->library('session');
			$this->load->library('uuid');

			$this->load->model('enea/isimm/nPlantModel');
			$this->load->model('enea/isimm/nSectionModel');
			$this->load->model('enea/isimm/nRecapModel');

			$this->load->lib_custom('isimm/PlantManagerLib');

			$this->municipalities = $this->session->userdata('municipalities');
        }

        public function clusterList_get() {
            $params         = $this->input->get();
            $isimm_id 	    = array_get($params, 'isimm_id');
			$isimm_type	    = array_get($params, 'isimm_type');
            $data = $this->nPlantModel->getClusterList($isimm_id,$isimm_type);
            $this->PlantManagerLib->responseWithStatus(true, (array)$data);
        }

        public function setCluster_post() {
            $params     = $this->input->post();
            $mode = $params["mode"];
            unset($params["mode"]);
            if ( $mode === "add") {
                $result     = $this->nPlantModel->setCluster($params);
            } else {
                $result     = $this->nPlantModel->updateCluster($params);
            }

            $this->PlantManagerLib->responseWithStatus(true, []);
        }

        public function deleteCluster_post() {
            $params     = $this->input->post();
            $result     = $this->nPlantModel->deleteCluster($params);
            $this->PlantManagerLib->responseWithStatus(true, []);
        }



		public function addPlant_post(){

			$params = $this->PlantManagerLib->elaborateRequest( ['municipality_name', 'plant_name'], 'post' );
			$municipalityName = $params['municipality_name'];
			$municipalityId = $this->PlantManagerLib->getMunicipalityIdByName($municipalityName, $this->municipalities);

			if ($municipalityId > 0){
				$result = $this->nPlantModel->savePlant($params['plant_name'], $municipalityId);

				$this->PlantManagerLib->responseWithStatus($result, ['guid' => $result->getGuid()] );
			}
			$this->PlantManagerLib->responseWithStatus(false);
		}

		public function ctsByMunicipality_post()
        {
            $params = $this->PlantManagerLib->elaborateRequest(['municipality','mode','asis_guid'], 'post');
			$municipality 	= array_get($params, 'municipality');
			$mode			= array_get($params, 'mode');
			$asis_guid		= array_get($params, 'asis_guid');
			$origin = [
				'sc' => null,
				'zo' => null
			];

			if ($asis_guid != null) {
				$section 		= $this->nSectionModel->getSectionASISByGuid($asis_guid);
				$origin['sc'] = $section->getOriginSc();
				$origin['zo'] = $section->getOriginZo();
			}

			$municipalityId = $this->PlantManagerLib->getMunicipalityIdByName($municipality, $this->municipalities);
            $cts 			= $this->nPlantModel->getCtsByMunicipality($municipality,$municipalityId,$mode);
            $userData       = $this->session->userdata();
            $this->PlantManagerLib->responseWithStatus(true, ['municipality' => $municipality,'cts' => $cts,'userData'=>$userData, 'origin' => $origin]);
        }

        public function zoDataReal_post()
        {
            $params = $this->PlantManagerLib->elaborateRequest(['municipality','schede','zo','mode'], 'post');
            $municipality 	= array_get($params, 'municipality');
            $schede 		= array_get($params, 'schede');
			$zo 			= array_get($params, 'zo');
			$mode			= array_get($params, 'mode');

			$zoDataReal 	= $this->nPlantModel->getZoDataReal($municipality,$schede,$zo,$mode);
			$zoDataReal['origin_sc'] 	= $schede;
			$zoDataReal['origin_zo'] 	= $zo;
            $userData 		= $this->session->userdata();
            $this->PlantManagerLib->responseWithStatus(true, ['zoDataReal' => $zoDataReal,'userData'=>$userData]);
        }


		public function clonePlant_post()
		{
			$params = $this->PlantManagerLib->elaborateRequest(['plant_guid', 'alias'], 'post');
			$parentPlantGuid = array_get($params, 'plant_guid');

			$plantFactory = $this->nPlantModel->clonePlant($parentPlantGuid);
			$sections = $this->nPlantModel->getPlantAndSections($parentPlantGuid);
			$asisCollection = [];
			$sectionASISGuid = null;

			/* @var $section v_ImpiantoAsisTobe */
			foreach ($sections as $section) {

				if (!in_array($section->getGuidAsis(), $asisCollection, true)) {
					$asisCollection[] = $section->getGuidAsis();
					$sectionASISGuid = $this->nSectionModel->cloneSectionASIS($plantFactory->getId(), $section->getGuidAsis(), $section->getAliasAsis());
				}

				$sectionTOBEGuid = $this->nSectionModel->cloneSectionTOBE($sectionASISGuid, $section->getGuidTobe(), $section->getAliasTobe());
			}

			$this->PlantManagerLib->responseWithStatus(true, ['guid' => $plantFactory->getGuid()]);
		}

		public function addSectionASIS_post(){
            $params = $this->PlantManagerLib->parseSectionASIS('post');
            $plant = $this->nPlantModel->getPlantByGuid($params['plant_guid']);
            $plantId = $plant->getId();

			if ($plantId === null){
				$this->PlantManagerLib->responseWithStatus(false);
			}
			$sectionGuid = $this->nSectionModel->createSectionASIS($plantId, $params);
			$this->PlantManagerLib->responseWithStatus($sectionGuid, ['guid' => $sectionGuid] );
		}

		public function cloneSectionASIS_post(){
			$params = $this->PlantManagerLib->elaborateRequest(['clone_guid', 'parent_guid', 'alias'], 'post');
			$parentSectionGuid = array_get($params,'section_guid', 0);
			$parentPlantGuid = array_get($params,'parent_guid', 0);
			$alias = array_get($params,'alias');

			$plantId = $this->nPlantModel->getPlantByGuid($parentPlantGuid)->getId();
			if (!($plantId > 0)){
				$this->PlantManagerLib->responseWithStatus(false);
			}

			$sectionGuid = $this->nSectionModel->cloneSectionASIS($plantId, $parentSectionGuid, $alias);
			$this->PlantManagerLib->responseWithStatus($sectionGuid, ['guid' => $sectionGuid] );
		}


		public function addSectionTOBE_post(){
            $params = $this->PlantManagerLib->parseSectionTOBE('post');
            foreach ($params as $key => $value) {
                if ($value === null) {
                    $params[$key] = "";
                }
            }
            $section = $this->nSectionModel->getSectionASISByGuid($params['section_guid']);
			$sectionId = $section->getId();
			if ($sectionId === null){
				$this->PlantManagerLib->responseWithStatus(false);
			}
			$sectionGuid = $this->nSectionModel->createSectionTOBE($sectionId, $params);
			$this->PlantManagerLib->responseWithStatus($sectionGuid, ['guid' => $sectionGuid] );
		}

		public function cloneSectionTOBE_post(){
			$params = $this->PlantManagerLib->elaborateRequest(['clone_guid', 'parent_guid', 'alias'], 'post');
			$parentSectionGuid = array_get($params,'clone_guid' );
			$parentASISGuid = array_get($params,'parent_guid');
			$alias = array_get($params,'alias');

			$sectionGuid = $this->nSectionModel->cloneSectionTOBE($parentASISGuid, $parentSectionGuid, $alias);
			$this->PlantManagerLib->responseWithStatus($sectionGuid, ['guid' => $sectionGuid] );
		}

		public function getPlantInfo_get(){
			$plantGuid = $this->get('guid');
			$plantArr = $this->nPlantModel->getPlantAndSections($plantGuid);
			$aggregateData = $this->PlantManagerLib->parsePlantAndSections($plantArr);
			$aggregateDataWithHpInfo = $this->nRecapModel->checkExistingHypothesisBySection($aggregateData);
			$this->PlantManagerLib->responseWithStatus(true, $aggregateDataWithHpInfo );
		}

		public function getPlants_get()
		{
			$plants = $this->nPlantModel->getPlantByMunicipality(array_keys($this->municipalities));
			$plantGuids = $this->PlantManagerLib->associatePlantsMunicipalities($plants, $this->municipalities);
			$plantArr = $this->nPlantModel->getPlantAndSections(array_keys($plantGuids));
			$aggregateData = $this->PlantManagerLib->parsePlantAndSections($plantArr, $plantGuids);
			$this->PlantManagerLib->responseWithStatus(true, $aggregateData);
		}


		public function getASIS_get(){
			$guid = $this->get('guid');
			$section = $this->nSectionModel->getSectionASISByGuid($guid);
			if ($section->getGuid() === null){
				$this->PlantManagerLib->responseWithStatus(false );
            }
			$sectionData = $section->safeExtractWithID();
			$this->PlantManagerLib->responseWithStatus(true, $sectionData );
		}

		public function getTOBE_get(){
			$guid = $this->get('guid');
			$section = $this->nSectionModel->getSectionTOBEByGuid($guid);
			$services = $this->nSectionModel->getSectionTOBEServices($section->getId());
			$sectionAndService = $this->PlantManagerLib->pairSectionTOBEAndServices($section, $services);

			if ($section->getGuid() === null){
				$this->PlantManagerLib->responseWithStatus(false );
			}
			$this->PlantManagerLib->responseWithStatus(true, $sectionAndService );
		}

		public function deleteTOBE_post(){
			$guid = $this->post('tobe_guid');

			$tobe = $this->nSectionModel->getSectionTOBEByGuid($guid);
            $tobeId = $tobe->getId();

            $this->nSectionModel->deleteTobe($tobeId);

            // delete all clusters to be connected
            $this->nPlantModel->deleteClusterbyHA($tobeId,"to_be");

			$this->PlantManagerLib->responseWithStatus(true );
		}

		public function deleteASIS_post(){
			$guid = $this->post('asis_guid');

			$asis = $this->nSectionModel->getSectionASISByGuid($guid);
			$asisId = $asis->getId();

            $this->nSectionModel->deleteAsis($asisId);
            // delete all clusters as is connected
            $this->nPlantModel->deleteClusterbyHA($asisId,"as_is");

			$this->PlantManagerLib->responseWithStatus(true );
		}


		public function deletePlant_post(){
			$guid = $this->post('plant_guid');

			$plant = $this->nPlantModel->getPlantByGuid($guid);
			$plantId = $plant->getId();

			$this->nPlantModel->deletePlant($plantId);
			$this->PlantManagerLib->responseWithStatus(true );
		}

		public function updateSectionASIS_post(){

			$sectionParams = $this->PlantManagerLib->parseSectionASIS('post', true);
			$plant = $this->nPlantModel->getPlantByGuid($sectionParams['plant_guid']);
			$plantId = $plant->getId();
			if ($plantId === null){
				$this->PlantManagerLib->responseWithStatus(false);
			}
			$resultStatus = $this->nSectionModel->updateSectionASIS($plantId,$sectionParams );
			$this->PlantManagerLib->responseWithStatus($resultStatus);
		}

		public function updateSectionTOBE_post(){
			$sectionParams = $this->PlantManagerLib->parseSectionTOBE('post', true);
			$section = $this->nSectionModel->getSectionASISByGuid($sectionParams['section_guid']);
			$sectionId = $section->getId();
			if ($sectionId === null){
				$this->PlantManagerLib->responseWithStatus(false);
			}
			$resultStatus = $this->nSectionModel->updateSectionTOBE($sectionId, $sectionParams );
			$this->PlantManagerLib->responseWithStatus($resultStatus);
		}

		public function updatePlant_post(){
			$params = $this->PlantManagerLib->elaborateRequest(['plant_guid', 'plant_name', 'municipality_name'], 'post');
			$params['municipality_id'] = $this->PlantManagerLib->getMunicipalityIdByName($params['municipality_name'], $this->municipalities);
			$this->nPlantModel->updatePlant($params);
			$guid = $params['plant_guid'];
			$this->PlantManagerLib->responseWithStatus(true, compact('guid') );
		}


		/*
		 * Check if any hypothesis is associated
		 */
		public function checkHypothesis_post()
		{
			$params = $this->PlantManagerLib->elaborateRequest(['guid', 'type'], 'post');
			$type = strtoupper( array_get($params, 'type'));
			$guid = array_get($params, 'guid');
			switch ($type) {
				case 'ASIS':
					$hpsName = $this->nRecapModel->checkExistingHypothesisByASIS($guid);
					break;
				case 'TOBE':
					$hpsName = $this->nRecapModel->checkExistingHypothesisByTOBE($guid);
					break;
				case 'PLANT':
					$hpsName = $this->nRecapModel->checkExistingHypothesisByPlant($guid);
					break;
				default:
					$hpsName = [];
					break;
			}

			$hpNameArr = [];
			foreach ($hpsName as $hpName){
				$hpNameArr[] = $hpName->getIpotesiAlias();
			}

			$this->PlantManagerLib->responseWithStatus(true, ['aliases' => $hpNameArr]);
		}


	}