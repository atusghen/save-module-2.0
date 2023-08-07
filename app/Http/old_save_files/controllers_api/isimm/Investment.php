<?php


	defined('BASEPATH') OR exit('No direct script access allowed');

	/** @noinspection PhpIncludeInspection */
	require_once APPPATH . 'libraries/REST_Controller.php';

	class Investment extends REST_Controller implements InvestmentInterface
	{
		/* @var $session CI_Session */
		public $session;

		/* @var $uuid Uuid */
		public $uuid;

		private $municipalities = [];

		/*
		 * Loading models
		 */
		/* @var $nInvestmentModel nInvestmentModel */
		public $nInvestmentModel ;

		/* @var $nSectionModel nSectionModel */
		public $nSectionModel ;

		/* @var $InvestmentLib InvestmentLib */
		public $InvestmentLib;

		public function __construct()
		{
			parent::__construct();
			$this->load->library('session');
			$this->load->library('uuid');

			$this->load->lib_custom('isimm/InvestmentLib');
			$this->load->model('enea/isimm/nInvestmentModel');
			$this->load->model('enea/isimm/nSectionModel');

			$this->municipalities = $this->session->userdata('municipalities');
		}

		public function getInvestmentsParamsByMunicipality_get(){
			$municipalityName = $this->get('municipality_name');
			$this->InvestmentLib->checkUserMunicipality($municipalityName, $this->municipalities);
			$municipalityId = $this->InvestmentLib->getMunicipalityIdByName($municipalityName, $this->municipalities);
			$investments = $this->nInvestmentModel->getInvestmentsParamsByMunicipality($municipalityId);

			$this->InvestmentLib->responseWithStatus(true,$investments);
		}

		public function getInvestmentsParams_get()
		{
			$investments = $this->nInvestmentModel->getInvestmentsParamsByUser($this->municipalities);
			$this->InvestmentLib->responseWithStatus(true, $investments);
		}

		public function getUnitaryEnergyCost_get()
		{
			$investmentGuid = $this->get('municipality');
			$unitaryEnergyCost = "0,19";
			$this->InvestmentLib->responseWithStatus(true, $unitaryEnergyCost);
		}


		public function getInvestmentParams_get()
		{
			$investmentGuid = $this->get('guid');

			$investments = $this->nInvestmentModel->getInvestmentParamsWithMunicipality($investmentGuid, $this->municipalities);
			$this->InvestmentLib->responseWithStatus(true, $investments);
		}

		public function addInvestmentParams_post()
		{
			$data = $this->InvestmentLib->parseInvestmentParams();
			$municipalityName = $data['municipality_name'];
			$data['investment_guid'] = $this->uuid->v4();
			$this->InvestmentLib->checkUserMunicipality($municipalityName, $this->municipalities);
			$data['municipality_id'] = $this->InvestmentLib->getMunicipalityIdByName($municipalityName, $this->municipalities);

			$investmentGuidRes = $this->nInvestmentModel->addInvestmentParamsByMunicipality($data);
			$this->InvestmentLib->responseWithStatus($investmentGuidRes,['guid' => $investmentGuidRes]);

		}

		public function updateInvestmentParams_post(){
			$data = $this->InvestmentLib->parseInvestmentParams();
			$municipalityName = $data['municipality_name'];
			$this->InvestmentLib->checkUserMunicipality($municipalityName, $this->municipalities);
			$data['municipality_id'] = $this->InvestmentLib->getMunicipalityIdByName($municipalityName, $this->municipalities);
			$investmentGuidRes = $this->nInvestmentModel->addInvestmentParamsByMunicipality($data, true);
			$this->InvestmentLib->responseWithStatus($investmentGuidRes,['guid' => $investmentGuidRes]);
		}

		public function deleteInvestmentParams_post(){
			$investmentGuid = $this->post('investment_guid');
			$status = $this->nInvestmentModel->deleteInvestmentParams($investmentGuid);
			$this->InvestmentLib->responseWithStatus($status);
		}


		public function calcInvestment_post(){
			$data = $this->InvestmentLib->parseCalcInvestmentParams();
			$sections = array_get($data, 'sections', []);
			$municipalityName = $data['municipality_name'];
			$this->InvestmentLib->checkUserMunicipality($municipalityName, $this->municipalities);

			$sectionsPairData = $this->nSectionModel->getGenericMultipleSectionsByGuid($sections);
			$investmentData = $this->nInvestmentModel->getInvestmentParams($data['investment_guid']);

			$partialInvestmentsResult = $this->InvestmentLib->calcPartialInvestment($sectionsPairData, $investmentData);
			$hpId = $this->nInvestmentModel->addHypothesis($investmentData->getId(), $data['hp_alias']);
			$this->nInvestmentModel->addPartialInvestmentResult($partialInvestmentsResult, $hpId);

			$totalInvestmentResult = $this->InvestmentLib->calcTotalInvestment($partialInvestmentsResult, $investmentData);
			$invTotId = $this->nInvestmentModel->addTotalInvestmentResult($totalInvestmentResult, $hpId);

			if ($invTotId > 0){
				$hypothesis = $this->nInvestmentModel->getHypothesis($hpId);
				$this->InvestmentLib->responseWithStatus(true, ['guid' => $hypothesis->getGuid()]);
			}

			$this->InvestmentLib->responseWithStatus(false);
		}

		public function calcRecapPaybacktime_post(){
			$inputData = $this->InvestmentLib->elaborateRequest(['sections', 'municipality_name', 'investment_guid', 'cost_unit_energy_min', 'cost_unit_energy_max'], 'post');
			$this->InvestmentLib->checkUserMunicipality($inputData['municipality_name'], $this->municipalities);

			$sectionsPairData = $this->nSectionModel->getGenericMultipleSectionsByGuid($inputData['sections']);
			$investmentData = $this->nInvestmentModel->getInvestmentParams($inputData['investment_guid']);

			$paybackTimeList = $this->InvestmentLib->recapPaybackTime($sectionsPairData, $investmentData, $inputData['cost_unit_energy_min'], $inputData['cost_unit_energy_max']  );

			$this->InvestmentLib->responseWithStatus(true,  $paybackTimeList);
		}

		public function calcRecapFee_post(){
			$inputData = $this->InvestmentLib->elaborateRequest(['sections', 'municipality_name', 'investment_guid', 'duration_min', 'duration_max', 'taxes', 'quota_finanziata'], 'post');
			$this->InvestmentLib->checkUserMunicipality($inputData['municipality_name'], $this->municipalities);

			$sectionsPairData = $this->nSectionModel->getGenericMultipleSectionsByGuid($inputData['sections']);
			$investmentData = $this->nInvestmentModel->getInvestmentParams($inputData['investment_guid']);

			$feeList = $this->InvestmentLib
				->recapFeeMinMax(
					$sectionsPairData,
					$investmentData,
					array_get($inputData, 'duration_min', 0),
					array_get($inputData, 'duration_max', 0),
					array_get($inputData, 'taxes', 0),
					array_get($inputData, 'quota_finanziata', 0)
				);

			$this->InvestmentLib->responseWithStatus(true,  $feeList);
		}


		public function calcRecapVANTIR_post(){
			$inputData = $this->InvestmentLib->elaborateRequest(['sections', 'municipality_name', 'investment_guid', 'taxes', 'duration'], 'post');
			$this->InvestmentLib->checkUserMunicipality($inputData['municipality_name'], $this->municipalities);

			$sectionsPairData = $this->nSectionModel->getGenericMultipleSectionsByGuid($inputData['sections']);
			$investmentData = $this->nInvestmentModel->getInvestmentParams($inputData['investment_guid']);

			$VAN_TIR = $this->InvestmentLib->recapVANTIR($sectionsPairData, $investmentData, $inputData['taxes'], $inputData['duration']  );

			$this->InvestmentLib->responseWithStatus(true,  $VAN_TIR);
		}


	}
