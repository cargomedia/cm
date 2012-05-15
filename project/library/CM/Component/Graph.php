<?php

class CM_Component_Graph extends CM_Component_Abstract {

	public function prepare() {
		$xmode = $this->_params->has('xmode') ? $this->_params->get('xmode') : 'time';
		$stack = $this->_params->has('stack') ? $this->_params->get('stack') : false;
		$legend = $this->_params->has('legend') ? $this->_params->get('legend') : true;
		$series = $this->_params->get('series');

		$flotSeries = array();
		$numPoints = 0;
		foreach ($series as $serie) {
			$data = array();
			foreach ($serie['data'] as $key => $value) {
				$value = (float) $value;
				$data[] = array($key, $value);
			}
			$flotSeries[] = array('label' => $serie['label'], 'data' => $data);
			$numPoints = max($numPoints, count($data));
		}
		$flotOptions = array();
		$flotOptions['series'] = array();
		$flotOptions['xaxis'] = array();
		$flotOptions['yaxis'] = array('min' => 0);
		$flotOptions['points'] = array('show' => true, 'radius' => 1.5);
		$flotOptions['series']['lines'] = array('show' => true);
		$flotOptions['legend'] = array('position' => 'nw', 'show' => $legend);

		if ($stack) {
			$flotOptions['series']['lines']['fill'] = 0.7;
			$flotOptions['series']['stack'] = 'stack';
		}
		if ($xmode == 'time') {
			$flotOptions['xaxis']['mode'] = 'time';
			foreach ($flotSeries as &$serie) {
				foreach ($serie['data'] as &$point) {
					$point[0] = $point[0] . '000';
				}
			}
		}
		if (700 / $numPoints < 10) {
			$flotOptions['points']['show'] = false;
		}

		$this->_js->flotSeries = $flotSeries;
		$this->_js->flotOptions = $flotOptions;
	}

	public function checkAccessible() {
	}
}