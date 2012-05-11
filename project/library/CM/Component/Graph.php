<?php

class CM_Component_Graph extends CM_Component_Abstract {
	public function checkAccessible() {

	}

	public function prepare() {
		$xmode = $this->_params->has('xmode') ? $this->_params->get('xmode') : 'time';
		$height = $this->_params->has('height') ? $this->_params->get('height') : 250;
		$width = $this->_params->has('width') ? $this->_params->get('width') : 740;
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
		if ($width / $numPoints < 10) {
			$flotOptions['points']['show'] = false;
		}

		$this->setTplParam('graphId', $this->_params->getString('graphId'));
		$this->setTplParam('width', $width);
		$this->setTplParam('height', $height);
		$this->setTplParam('flotSeries', json_encode($flotSeries));
		$this->setTplParam('flotOptions', json_encode($flotOptions));
	}
}