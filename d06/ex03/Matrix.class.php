
<?php
	class Matrix
	{
		const IDENTITY = "IDENTITY";
		const SCALE = "SCALE";
		const RX = "Ox ROTATION";
		const RY = "Oy ROTATION";
		const RZ = "Oz ROTATION";
		const TRANSLATION = "TRANSLATION";
		const PROJECTION = "PROJECTION";
		protected $matrix = array();
		private $_preset;
		private $_scale;
		private $_angle;
		private $_vtc;
		private $_fov;
		private $_ratio;
		private $_near;
		private $_far;
		static $verbose = false;

		public static function doc()
		{
			return file_get_contents('Matrix.doc.txt');
		}

		function __toString()
		{
			$tmp = "M | vtcX | vtcY | vtcZ | vtxO\n";
			$tmp .= "-----------------------------\n";
			$tmp .= "x | %0.2f | %0.2f | %0.2f | %0.2f\n";
			$tmp .= "y | %0.2f | %0.2f | %0.2f | %0.2f\n";
			$tmp .= "z | %0.2f | %0.2f | %0.2f | %0.2f\n";
			$tmp .= "w | %0.2f | %0.2f | %0.2f | %0.2f";
			return (vsprintf($tmp, array($this->matrix[0], $this->matrix[1], $this->matrix[2], $this->matrix[3], $this->matrix[4], $this->matrix[5], $this->matrix[6], $this->matrix[7], $this->matrix[8], $this->matrix[9], $this->matrix[10], $this->matrix[11], $this->matrix[12], $this->matrix[13], $this->matrix[14], $this->matrix[15])));
		}

		public function __construct($array = null)
		{
			if (isset($array)) {
				if (isset($array['preset']))
					$this->_preset = $array['preset'];
				if (isset($array['scale']))
					$this->_scale = $array['scale'];
				if (isset($array['angle']))
					$this->_angle = $array['angle'];
				if (isset($array['vtc']))
					$this->_vtc = $array['vtc'];
				if (isset($array['fov']))
					$this->_fov = $array['fov'];
				if (isset($array['ratio']))
					$this->_ratio = $array['ratio'];
				if (isset($array['near']))
					$this->_near = $array['near'];
				if (isset($array['far']))
					$this->_far = $array['far'];
				$this->ft_check();
				$this->createMatrix();
				if (Self::$verbose) {
					if ($this->_preset == Self::IDENTITY)
						echo "Matrix " . $this->_preset . " instance constructed\n";
					else
						echo "Matrix " . $this->_preset . " preset instance constructed\n";
				}
				$this->dispatch();
			}
		}

		function __destruct()
		{
			if (Self::$verbose)
				printf("Matrix instance destructed\n");
		}

		private function dispatch()
		{
			switch ($this->_preset) {
				case (self::IDENTITY) :
					$this->identity(1);
					break;
				case (self::TRANSLATION) :
					$this->translation();
					break;
				case (self::SCALE) :
					$this->identity($this->_scale);
					break;
				case (self::RX) :
					$this->rotation_x();
					break;
				case (self::RY) :
					$this->rotation_y();
					break;
				case (self::RZ) :
					$this->rotation_z();
					break;
				case (self::PROJECTION) :
					$this->projection();
					break;
			}
		}
		private function createMatrix()
		{
			for ($i = 0; $i < 16; $i++) {
				$this->matrix[$i] = 0;
			}
		}
		private function identity($scale)
		{
			$this->matrix[0] = $scale;
			$this->matrix[5] = $scale;
			$this->matrix[10] = $scale;
			$this->matrix[15] = 1;
		}
		private function translation()
		{
			$this->identity(1);
			$this->matrix[3] = $this->_vtc->get_x();
			$this->matrix[7] = $this->_vtc->get_y();
			$this->matrix[11] = $this->_vtc->get_z();
		}
		private function rotation_x()
		{
			$this->identity(1);
			$this->matrix[0] = 1;
			$this->matrix[5] = cos($this->_angle);
			$this->matrix[6] = -sin($this->_angle);
			$this->matrix[9] = sin($this->_angle);
			$this->matrix[10] = cos($this->_angle);
		}
		private function rotation_y()
		{
			$this->identity(1);
			$this->matrix[0] = cos($this->_angle);
			$this->matrix[2] = sin($this->_angle);
			$this->matrix[5] = 1;
			$this->matrix[8] = -sin($this->_angle);
			$this->matrix[10] = cos($this->_angle);
		}
		private function rotation_z()
		{
			$this->identity(1);
			$this->matrix[0] = cos($this->_angle);
			$this->matrix[1] = -sin($this->_angle);
			$this->matrix[4] = sin($this->_angle);
			$this->matrix[5] = cos($this->_angle);
			$this->matrix[10] = 1;
		}
		private function projection()
		{
			$this->identity(1);
			$this->matrix[5] = 1 / tan(0.5 * deg2rad($this->_fov));
			$this->matrix[0] = $this->matrix[5] / $this->_ratio;
			$this->matrix[10] = -1 * (-$this->_near - $this->_far) / ($this->_near - $this->_far);
			$this->matrix[14] = -1;
			$this->matrix[11] = (2 * $this->_near * $this->_far) / ($this->_near - $this->_far);
			$this->matrix[15] = 0;
		}

		private function ft_check()
		{
			if (empty($this->_preset))
				return "error";
			if ($this->_preset == self::SCALE && empty($this->_scale))
				return "error";
			if (($this->_preset == self::RX || $this->_preset == self::RY || $this->_preset == self::RZ) && empty($this->_angle))
				return "error";
			if ($this->_preset == self::TRANSLATION && empty($this->_vtc))
				return "error";
			if ($this->_preset == self::PROJECTION && (empty($this->_fov) || empty($this->_radio) || empty($this->_near) || empty($this->_far)))
				return "error";
		}

		public function mult(Matrix $rhs)
		{
			$tmp = array();
			for ($i = 0; $i < 16; $i += 4) {
				for ($j = 0; $j < 4; $j++) {
					$tmp[$i + $j] = 0;
					$tmp[$i + $j] += $this->matrix[$i + 0] * $rhs->matrix[$j + 0];
					$tmp[$i + $j] += $this->matrix[$i + 1] * $rhs->matrix[$j + 4];
					$tmp[$i + $j] += $this->matrix[$i + 2] * $rhs->matrix[$j + 8];
					$tmp[$i + $j] += $this->matrix[$i + 3] * $rhs->matrix[$j + 12];
				}
			}
			$matr = new Matrix();
			$matr->matrix = $tmp;
			return $matr;
		}

		public function transformVertex(Vertex $vtx)
		{
			$tmp = array();
			$tmp['x'] = ($vtx->get_x() * $this->matrix[0]) + ($vtx->get_y() * $this->matrix[1]) + ($vtx->get_z() * $this->matrix[2]) + ($vtx->get_w() * $this->matrix[3]);
			$tmp['y'] = ($vtx->get_x() * $this->matrix[4]) + ($vtx->get_y() * $this->matrix[5]) + ($vtx->get_z() * $this->matrix[6]) + ($vtx->get_w() * $this->matrix[7]);
			$tmp['z'] = ($vtx->get_x() * $this->matrix[8]) + ($vtx->get_y() * $this->matrix[9]) + ($vtx->get_z() * $this->matrix[10]) + ($vtx->get_w() * $this->matrix[11]);
			$tmp['w'] = ($vtx->get_x() * $this->matrix[11]) + ($vtx->get_y() * $this->matrix[13]) + ($vtx->get_z() * $this->matrix[14]) + ($vtx->get_w() * $this->matrix[15]);
			$tmp['color'] = $vtx->get_color();
			$vert = new Vertex($tmp);
			return $vert;
		}
}