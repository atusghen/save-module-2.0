<?php


defined('BASEPATH') OR exit('No direct script access allowed');

/** @noinspection PhpIncludeInspection */
require_once APPPATH . 'libraries/REST_Controller.php';

class HypothesisManager extends REST_Controller implements HypothesisManagerInterface
{
	/* @var $session CI_Session */
	public $session;

	/* @var $uuid Uuid */
	public $uuid;

	private $municipalities = [];

	/* @var $nHypothesisModel nHypothesisModel */
	public $nHypothesisModel;
	/* @var $HypothesisLib HypothesisLib */
	public $HypothesisLib;
	/* @var $nRecapModel nRecapModel */
	public $nRecapModel;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');

		$this->load->lib_custom('isimm/HypothesisLib');
		$this->load->model('enea/isimm/nHypothesisModel');
		$this->load->model('enea/isimm/nRecapModel');
		$this->municipalities = $this->session->userdata('municipalities');
	}

	public function deleteHypothesis_post()
	{
		$hpGuid = $this->post('hypothesis_guid');
		$status = $this->nHypothesisModel->deleteHypothesis($hpGuid);
		$this->HypothesisLib->responseWithStatus($status);
	}

	/*
	 * Check if any hypothesis is associated
	 */
	public function checkHypothesis_post()
	{
		$params = $this->HypothesisLib->elaborateRequest(['guid'], 'post');
		$guid = array_get($params, 'guid');

		$hpsName = $this->nRecapModel->checkExistingHypothesisByInvestment($guid);

		$hpNameArr = [];
		/* @var $hpName Ipotesi */
		foreach ($hpsName as $hpName) {
			$hpNameArr[] = $hpName->getAlias();
		}

		$this->HypothesisLib->responseWithStatus(true, ['aliases' => $hpNameArr]);
	}


}
