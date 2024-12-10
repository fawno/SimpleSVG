<?php
/*******************************************************************************
  Simple SVG - Simple PHP class for creating SVG documents
    Author          : Fernando Herrero
    Version         : 1.0.3
    License         : MIT
		Home page       : https://github.com/fawno/SimpleSVG
*******************************************************************************/

	namespace SimpleSVG;

	use SimpleSVG\SimpleSVGElement;

	class SimpleSVGDocument {
		protected $svg = null;
		protected $attr = [];

		protected $width = null;
		protected $height = null;
		protected $units = null;
		protected $scale = 1;

		protected $xmin = null;
		protected $ymin = null;
		protected $margin = null;
		protected $flipy = 1;

		public function __construct (float $width, float $height, ?string $units = null, float $scale = 1, array $attr = []) {
			$this->attr = $attr;
			$this->width = $width;
			$this->height = $height;
			$this->units = $units;
			$this->scale = $scale;

			$this->svg = new SimpleSVGElement('<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"/>');
			$this->svg->addAttributes(['flipy' => $this->flipy, 'scale' => $this->scale]);
		}

		public function __call ($method, $arguments) {
			return call_user_func_array ([$this->svg, $method], $arguments);
		}

		public function getID () {
			return $this->attr['id'] ?? null;
		}

		public function getWidth () {
			return $this->width;
		}

		public function getHeight () {
			return $this->height;
		}

		public function setWidth (float $width) {
			$this->width = $width;
		}

		public function setHeight (float $height) {
			$this->height = $height;
		}

		public function getXmin () {
			return $this->xmin;
		}

		public function getYmin () {
			return $this->ymin;
		}

		public function setXmin (?float $xmin = null) {
			$this->xmin = $xmin;
		}

		public function setYmin (?float $ymin = null) {
			$this->ymin = $ymin;
			$this->flipy = ($this->ymin < 0) ? -1 : 1;
			$this->svg->addAttributes(['flipy' => $this->flipy]);
		}

		public function setUnits (?string $units = null) {
			$this->units = $units;
		}

		public function setScale (float $scale = 1) {
			$this->scale = $scale;
			$this->svg->addAttributes(['scale' => $this->scale]);
		}

		public function setMargin (?float $margin = null) {
			$this->margin = $margin;
		}

		public function setFlipY (bool $flip = false) {
			$this->flipy = $flip ? -1 : 1;
			$this->svg->addAttributes(['flipy' => $this->flipy]);
		}

		public function output (?string $filename = null) {
			$this->attr['viewBox'] = implode(',', [$this->scale * $this->xmin - $this->margin, $this->scale * $this->ymin - $this->margin, $this->scale * $this->width + 2 * $this->margin, $this->scale * $this->height + 2 * $this->margin]);
			$this->attr['x'] = $this->scale * $this->xmin - $this->margin;
			$this->attr['y'] = $this->scale * $this->ymin - $this->margin;
			$this->attr['width'] = ($this->scale * $this->width + 2 * $this->margin) . $this->units;
			$this->attr['height'] = ($this->scale * $this->height + 2 * $this->margin) . $this->units;
			$this->svg->addAttributes($this->attr);

			unset($this->svg->attributes()['flipy']);
			unset($this->svg->attributes()['scale']);

			if (strlen((string) $filename)) {
				$output = $this->svg->asXML($filename);
			} else {
				$output = $this->svg->asXML();
			}

			$this->svg->addAttributes(['flipy' => $this->flipy]);
			$this->svg->addAttributes(['scale' => $this->scale]);

			return $output;
		}
	}
