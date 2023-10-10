<?php


	class IsimmBaseLib
	{
		protected $CI;



		public function __construct()
		{
			$this->CI =& get_instance();
		}


		public function responseWithStatus($status, array $data = [])
		{
			if ($status !== false && $status !== 'false') {
				$status = true;

			} else {
				$data = [];
			}

			$res = [
				'success' => $status,
				'data'    => $data
			];

			$this->CI->response($res, REST_Controller::HTTP_OK);
		}

		public function elaborateRequest(array $params = [], $method = 'get', $map = false)
		{
			$result = [];

			if ($map === true) {

				foreach ($params as $key => $val) {
					/* support for multidimensional array MIXED; KEY is ignored when numeric (positional) */
					if (is_numeric($key)){
						$key = $val;
					}
					$result[$val] = $this->CI->$method($key);
				}

			} else {

				foreach ($params as $param) {
					$result[$param] = $this->CI->$method($param);
				}
			}

			return $result;
		}


		public function normalizeMunicipalities(&$municipalities, $normalizer = 'strtoupper'){
			$municipalities = array_map($normalizer, $municipalities);
		}


		public function getMunicipalityIdByName($municipalityName, $municipalities){
			$municipalityName = strtoupper($municipalityName);
			$this->normalizeMunicipalities($municipalities);
			return array_search( $municipalityName, $municipalities, true);
		}
	}