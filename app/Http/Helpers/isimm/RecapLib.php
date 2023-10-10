<?php

	class RecapLib
	{

		public function hypothesisByMunicipality($hps, $municipalities)
		{
			$hypothesis = [];
			foreach ($hps as $hp)
			{
				$municipality_name = strtoupper(array_get($municipalities, array_get($hp, 'comune_id'), 'null'));

				$hypothesisGuid = array_get($hp, 'ipotesi_guid', 0);

				if (!isset($hypothesis[$municipality_name][$hypothesisGuid])) {
					$hypothesis[$municipality_name][$hypothesisGuid] = [
						'data'       => array_get($hp, 'created_at', 0),
						'hypothesis' => [
							'name' => array_get($hp, 'ipotesi_alias', 0),
							'guid' => array_get($hp, 'ipotesi_guid', 0),
						],

						'plant' => [
							'name' => array_get($hp, 'plant_alias', 0),
							'guid' => array_get($hp, 'plant_guid', 0),
						]
					];
				}
				$hypothesis[$municipality_name][$hypothesisGuid]['sections'][] = [
					'tobe' => [
						'name' => array_get($hp, 'tobe_alias', 0),
						'guid' => array_get($hp, 'tobe_guid', 0),
					],
					'asis' => [
						'name' => array_get($hp, 'asis_alias', 0),
						'guid' => array_get($hp, 'asis_guid', 0),
					]
				];
			}
			return $hypothesis;
		}

		public function extractPlantGuid($hypothesisList)
		{
			$result = [];
			/* @var $hp v_ComuneIpotesiSezione */
			foreach ($hypothesisList as $hp) {
				$plantGuid = $hp->getPlantGuid();
				if (!in_array($plantGuid, $result, true)) {
					$result[] = $plantGuid;
				}
			}
			return $result;
		}

		public function extractGuidsInHypothesis($hypothesisList)
		{
			$results = [];
			/* @var $hp v_ComuneIpotesiSezione */
			foreach ($hypothesisList as $hp) {
				$asisGuid = $hp->getAsisGuid();
				$tobeGuid = $hp->getTobeGuid();
				$plantGuid = $hp->getPlantGuid();
				$hpGuid = $hp->getIpotesiGuid();

				$results['pairs'][$hpGuid]['plant'] = $plantGuid;
				$results['pairs'][$hpGuid]['hypothesis'] = $hpGuid;
				$results['pairs'][$hpGuid]['sections'][] = [
					'asis'  => $asisGuid,
					'tobe'  => $tobeGuid
				];

				if (!in_array($asisGuid, array_get($results,'asis', []), true))
				{
					$results['asis'][] = $asisGuid;
				}
				if (!in_array($asisGuid,  array_get($results,'tobe', []), true)) {
					$results['tobe'][] = $tobeGuid;
				}
				$results['plant'] = $plantGuid;
				$results['hp'] = $hpGuid;
			}
			return $results;
		}

		public function pairPlantSectionPartialInvestmentData(array $sections, array $asis, array $tobe, array $investmentPartialData){
			$pairsWithData = [];

			foreach ($sections as $section){
				$asisGuid = $section['asis'];
				$tobeGuid = $section['tobe'];
				$tobeId = $tobe[$tobeGuid]->getId();

				$section['asis'] = $asis[$asisGuid];
				$section['tobe'] = $tobe[$tobeGuid];
				$section['investment_partial'] = $investmentPartialData[$tobeId];

				$pairsWithData[] = $section;
			}

			return $pairsWithData;
		}

	}
