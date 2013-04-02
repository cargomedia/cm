<?php

class CM_Component_Graph extends CM_Component_Abstract {

	public function prepare() {
		$xmode = $this->_params->has('xmode') ? $this->_params->get('xmode') : 'time';
		$stack = $this->_params->has('stack') ? $this->_params->get('stack') : false;
		$legend = $this->_params->has('legend') ? $this->_params->get('legend') : true;
		$series = $this->_params->get('series');

		$numPoints = 0;
		$valueMin = 0;
		foreach ($series as &$serie) {
			$serie['label'] = (string) $serie['label'];
			foreach ($serie['data'] as $key => &$value) {
				$value = (float) $value;
				$valueMin = min($valueMin, $value);
			}
			$numPoints = max($numPoints, count($serie['data']));
		}
		$flotOptions = array();
		$flotOptions['series'] = array();
		$flotOptions['xaxis'] = array();
		$flotOptions['yaxis'] = array('min' => $valueMin);
		$flotOptions['points'] = array('show' => true, 'radius' => 1.5);
		$flotOptions['series']['lines'] = array('show' => true);
		$flotOptions['legend'] = array('position' => 'nw', 'show' => $legend);

		if ($stack) {
			$flotOptions['series']['lines']['fill'] = 0.7;
			$flotOptions['series']['stack'] = 'stack';
		}
		if ($xmode == 'time') {
			$flotOptions['xaxis']['mode'] = 'time';
		}
		if ($numPoints > 0 && (700 / $numPoints) < 10) {
			$flotOptions['points']['show'] = false;
		}

		$this->_js->series = $series;
		$this->_js->flotOptions = $flotOptions;
	}

	public function checkAccessible() {
	}
}
