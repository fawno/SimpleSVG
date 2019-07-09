<?php
/*******************************************************************************
  Simple SVG - Simple PHP class for creating SVG documents
    Author          : Fernando Herrero
    Version         : 1.0.1
    License         : MIT
		Home page       : https://github.com/fawno/SimpleSVG
*******************************************************************************/

	namespace SimpleSVG;

	class SimpleSVGElement extends \SimpleXMLElement {
		public function addAttributes (array $attributes) {
			foreach ($attributes as $attribute => $value) {
				unset($this->attributes()[$attribute]);
				$this->addAttribute($attribute, $value);
			}
		}

		public function addNode (string $name, string $content = null, array $attr = []) {
			$node = $this->addChild($name, $content);
			$node->addAttributes($attr);

			return $node;
		}

		private function flipy () {
			return (int) current($this->xpath('//*[@flipy]'))->attributes()['flipy'];
		}

		private function scale () {
			return (float) current($this->xpath('//*[@scale]'))->attributes()['scale'];
		}

		public function rect (float $x, float $y, float $width, float $height, float $rx = null, float $ry = null, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			$attr['x'] = $scale * $x;
			$attr['y'] = $scale * $flipy * $y;
			$attr['width'] = $scale * $width;
			$attr['height'] = $scale * $height;
			$attr['rx'] = $scale * $rx;
			$attr['ry'] = $scale * $ry;

			$this->addNode('rect', null, $attr);
		}

		public function circle (float $cx, float $cy, float $r, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			$attr['cx'] = $scale * $cx;
			$attr['cy'] = $scale * $flipy * $cy;
			$attr['r'] = $scale * $r;

			$this->addNode('circle', null, $attr);
		}

		public function ellipse (float $cx, float $cy, float $rx, float $ry, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			$attr['cx'] = $scale * $cx;
			$attr['cy'] = $scale * $flipy * $cy;
			$attr['rx'] = $scale * $rx;
			$attr['ry'] = $scale * $ry;

			$this->addNode('ellipse', null, $attr);
		}

		public function vector (float $x, float $y, float $r, float $a, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			$attr['x1'] = $scale * $x;
			$attr['y1'] = $scale * $flipy * $y;
			$attr['x2'] = $scale * ($r * cos($a) + $x);
			$attr['y2'] = $scale * $flipy * ($r * sin($a) + $y);

			$this->addNode('line', null, $attr);
		}

		public function arc (float $x, float $y, float $r, float $a1, float $a2, bool $relative = false, bool $clockwise = false, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			$x = $scale * $x;
			$y = $scale * $flipy * $y;
			$r = $scale * $r;
			$x1 = $r * cos($a1) + $x;
			$y1 = $flipy * $r * sin($a1) + $y;
			$x2 = $r * cos(($relative ? $a1 : 0) + $a2) + $x;
			$y2 = $flipy * $r * sin(($relative ? $a1 : 0) + $a2) + $y;
			$clockwise = (int) ($relative and $clockwise);
			$attr['d'] = sprintf('M %1$.6f %2$.6f A %3$.6f %3$.6f 0 0 %4$d %5$.6f %6$.6f', $x1, $y1, $r, $clockwise, $x2, $y2);

			$this->addNode('path', null, $attr);
		}

		public function line (float $x1, float $y1, float $x2, float $y2, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			$attr['x1'] = $scale * $x1;
			$attr['y1'] = $scale * $flipy * $y1;
			$attr['x2'] = $scale * $x2;
			$attr['y2'] = $scale * $flipy * $y2;

			$this->addNode('line', null, $attr);
		}

		public function path ($commands, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			if (is_string($commands) and !empty($commands)) {
				$attr['d'] = $commands;
			}

			if (is_array($commands) and !empty($commands)) {
				foreach ($commands as $key => $value) {
					foreach ($value as $command => $params) {
						$flipy = strrpos('mlhvcsqta', $command) ? 1 : $this->flipy();
						switch (strtoupper($command)) {
							case 'M': //x,y
							case 'L': //x,y
							case 'C': //cX1,cY1 cX2,cY2 eX,eY
							case 'S': //cX2,cY2 eX,eY
							case 'Q': //cX,cY eX,eY
							case 'T': //eX,eY
								foreach ($params as $id => $point) {
									list($x, $y) = array_values($point);
									$params[$id] = implode(',', [$scale * $x, $scale * $flipy * $y]);
								}
								array_unshift($params, $command);
								$commands[$key] = implode(' ', $params);
								break;
							case 'H': //x
								$flipy = 1;
							case 'V': //y
								$commands[$key] = implode(' ', [$command, $scale * $flipy * $params]);
								break;
							case 'A': // rX,rY rotation, arc, sweep, eX,eY
								list($w, $h) = array_splice($params, 0, 2);
								list($x, $y) = array_splice($params, -2);
								array_unshift($params, $command, $scale * $w, $scale * $h);
								$params[] = $scale * $x;
								$params[] = $scale * $flipy * $y;
								$commands[$key] = implode(' ', $params);
								break;
							case 'Z':
								$commands[$key] = $command;
								break;
						}
					}
				}

				$attr['d'] = implode(' ', $commands);
			}

			$this->addNode('path', null, $attr);
		}

		public function text (float $x, float $y, string $text = null, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			$attr['x'] = $scale * $x;
			$attr['y'] = $scale * $flipy * $y;

			$this->addNode('text', $text, $attr);
		}

		public function polygon (array $points, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			foreach ($points as $id => $point) {
				list($x, $y) = array_values($point);
				$points[$id] = implode(',', [$scale * $x, $scale * $flipy * $y]);
			}

			$attr['points'] = implode(' ', $points);

			$this->addNode('polygon', null, $attr);
		}

		public function polyline (array $points, array $attr = []) {
			$flipy = $this->flipy();
			$scale = $this->scale();

			foreach ($points as $id => $point) {
				list($x, $y) = array_values($point);
				$points[$id] = implode(',', [$scale * $x, $scale * $flipy * $y]);
			}

			$attr['points'] = implode(' ', $points);

			$this->addNode('polyline', null, $attr);
		}

		public function sino (float $x, float $y, float $width = null, float $height, float $phase = 0, array $attr = []) {
			$points = null;

			for ($t = 0; $t <= $width; $t++) {
				$v = $height * sin(pi() * $t / 100 + $phase);
				$points[] = [$x + $t, $y + $v];
			}

			$this->polyline($points, $attr);
		}

		public function marker (string $id, array $attr = []) {
			$attr['id'] = $id;

			return $this->addNode('marker', null, $attr);
		}

		public function addDefs () {
			return $this->addNode('defs');
		}

		public function addGroup (array $attr = []) {
			$group = $this->addNode('g', null, $attr);

			return $group;
		}
	}
