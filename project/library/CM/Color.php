<?php

class CM_Color {

	/**
	 * @var int
	 */
	private $_red, $_green, $_blue;

	/**
	 * @var float
	 */
	private $_alpha;

	/**
	 * @param int		$red
	 * @param int		$green
	 * @param int		$blue
	 * @param float|null $alpha
	 */
	public function __construct($red, $green, $blue, $alpha = null) {
		if (null === $alpha) {
			$alpha = 1;
		}
		$this->_red = max(0, min(255, (int) $red));
		$this->_green = max(0, min(255, (int) $green));
		$this->_blue = max(0, min(255, (int) $blue));
		$this->_alpha = max(0, min(1, (float) $alpha));
	}

	/**
	 * @return int
	 */
	public function getRed() {
		return $this->_red;
	}

	/**
	 * @return int
	 */
	public function getGreen() {
		return $this->_green;
	}

	/**
	 * @return int
	 */
	public function getBlue() {
		return $this->_blue;
	}

	/**
	 * @return float
	 */
	public function getAlpha() {
		return $this->_alpha;
	}

	/**
	 * @param string $colorName
	 * @return CM_Color
	 * @throws CM_Exception_Invalid
	 */
	public static function parseX11($colorName) {
		$colorName = strtolower($colorName);
		switch ($colorName) {
			case 'aliceblue':
				return new self(240, 248, 255);
				break;
			case 'antiquewhite':
				return new self(250, 235, 215);
				break;
			case 'aqua':
				return new self(0, 255, 255);
				break;
			case 'aquamarine':
				return new self(127, 255, 212);
				break;
			case 'azure':
				return new self(240, 255, 255);
				break;
			case 'beige':
				return new self(245, 245, 220);
				break;
			case 'bisque':
				return new self(255, 228, 196);
				break;
			case 'black':
				return new self(0, 0, 0);
				break;
			case 'blanchedalmond':
				return new self(255, 235, 205);
				break;
			case 'blue':
				return new self(0, 0, 255);
				break;
			case 'blueviolet':
				return new self(138, 43, 226);
				break;
			case 'brown':
				return new self(165, 42, 42);
				break;
			case 'burlywood':
				return new self(222, 184, 135);
				break;
			case 'cadetblue':
				return new self(95, 158, 160);
				break;
			case 'chartreuse':
				return new self(127, 255, 0);
				break;
			case 'chocolate':
				return new self(210, 105, 30);
				break;
			case 'coral':
				return new self(255, 127, 80);
				break;
			case 'cornflower':
				return new self(100, 149, 237);
				break;
			case 'cornsilk':
				return new self(255, 248, 220);
				break;
			case 'crimson':
				return new self(220, 20, 60);
				break;
			case 'cyan':
				return new self(0, 255, 255);
				break;
			case 'darkblue':
				return new self(0, 0, 139);
				break;
			case 'darkcyan':
				return new self(0, 139, 139);
				break;
			case 'darkgoldenrod':
				return new self(184, 134, 11);
				break;
			case 'darkgray':
				return new self(169, 169, 169);
				break;
			case 'darkgreen':
				return new self(0, 100, 0);
				break;
			case 'darkkhaki':
				return new self(189, 183, 107);
				break;
			case 'darkmagenta':
				return new self(139, 0, 139);
				break;
			case 'darkolivegreen':
				return new self(85, 107, 47);
				break;
			case 'darkorange':
				return new self(255, 140, 0);
				break;
			case 'darkorchid':
				return new self(153, 50, 204);
				break;
			case 'darkred':
				return new self(139, 0, 0);
				break;
			case 'darksalmon':
				return new self(233, 150, 122);
				break;
			case 'darkseagreen':
				return new self(143, 188, 143);
				break;
			case 'darkslateblue':
				return new self(72, 61, 139);
				break;
			case 'darkslategray':
				return new self(47, 79, 79);
				break;
			case 'darkturquoise':
				return new self(0, 206, 209);
				break;
			case 'darkviolet':
				return new self(148, 0, 211);
				break;
			case 'deeppink':
				return new self(255, 20, 147);
				break;
			case 'deepskyblue':
				return new self(0, 191, 255);
				break;
			case 'dimgray':
				return new self(105, 105, 105);
				break;
			case 'dodgerblue':
				return new self(30, 144, 255);
				break;
			case 'firebrick':
				return new self(178, 34, 34);
				break;
			case 'floralwhite':
				return new self(255, 250, 240);
				break;
			case 'forestgreen':
				return new self(34, 139, 34);
				break;
			case 'fuchsia':
				return new self(255, 0, 255);
				break;
			case 'gainsboro':
				return new self(220, 220, 220);
				break;
			case 'ghostwhite':
				return new self(248, 248, 255);
				break;
			case 'gold':
				return new self(255, 215, 0);
				break;
			case 'goldenrod':
				return new self(218, 165, 32);
				break;
			case 'gray':
				return new self(128, 128, 128);
				break;
			case 'green':
				return new self(0, 128, 0);
				break;
			case 'greenyellow':
				return new self(173, 255, 47);
				break;
			case 'honeydew':
				return new self(240, 255, 240);
				break;
			case 'hotpink':
				return new self(255, 105, 180);
				break;
			case 'indianred':
				return new self(205, 92, 92);
				break;
			case 'indigo':
				return new self(75, 0, 130);
				break;
			case 'ivory':
				return new self(255, 255, 240);
				break;
			case 'khaki':
				return new self(240, 230, 140);
				break;
			case 'lavender':
				return new self(230, 230, 250);
				break;
			case 'lavenderblush':
				return new self(255, 240, 245);
				break;
			case 'lawngreen':
				return new self(124, 252, 0);
				break;
			case 'lemonchiffon':
				return new self(255, 250, 205);
				break;
			case 'lightblue':
				return new self(173, 216, 230);
				break;
			case 'lightcoral':
				return new self(240, 128, 128);
				break;
			case 'lightcyan':
				return new self(224, 255, 255);
				break;
			case 'lightgoldenrod':
				return new self(250, 250, 210);
				break;
			case 'lightgray':
				return new self(211, 211, 211);
				break;
			case 'lightgreen':
				return new self(144, 238, 144);
				break;
			case 'lightpink':
				return new self(255, 182, 193);
				break;
			case 'lightsalmon':
				return new self(255, 160, 122);
				break;
			case 'lightseagreen':
				return new self(32, 178, 170);
				break;
			case 'lightskyblue':
				return new self(135, 206, 250);
				break;
			case 'lightslategray':
				return new self(119, 136, 153);
				break;
			case 'lightsteelblue':
				return new self(176, 196, 222);
				break;
			case 'lightyellow':
				return new self(255, 255, 224);
				break;
			case 'lime':
				return new self(0, 255, 0);
				break;
			case 'limegreen':
				return new self(50, 205, 50);
				break;
			case 'linen':
				return new self(250, 240, 230);
				break;
			case 'magenta':
				return new self(255, 0, 255);
				break;
			case 'maroon':
				return new self(127, 0, 0);
				break;
			case 'mediumaquamarine':
				return new self(102, 205, 170);
				break;
			case 'mediumblue':
				return new self(0, 0, 205);
				break;
			case 'mediumorchid':
				return new self(186, 85, 211);
				break;
			case 'mediumpurple':
				return new self(147, 112, 219);
				break;
			case 'mediumseagreen':
				return new self(60, 179, 113);
				break;
			case 'mediumslateblue':
				return new self(123, 104, 238);
				break;
			case 'mediumspringgreen':
				return new self(0, 250, 154);
				break;
			case 'mediumturquoise':
				return new self(72, 209, 204);
				break;
			case 'mediumvioletred':
				return new self(199, 21, 133);
				break;
			case 'midnightblue':
				return new self(25, 25, 112);
				break;
			case 'mintcream':
				return new self(245, 255, 250);
				break;
			case 'mistyrose':
				return new self(255, 228, 225);
				break;
			case 'moccasin':
				return new self(255, 228, 181);
				break;
			case 'navajowhite':
				return new self(255, 222, 173);
				break;
			case 'navy':
				return new self(0, 0, 128);
				break;
			case 'oldlace':
				return new self(253, 245, 230);
				break;
			case 'olive':
				return new self(128, 128, 0);
				break;
			case 'olivedrab':
				return new self(107, 142, 35);
				break;
			case 'orange':
				return new self(255, 165, 0);
				break;
			case 'orangered':
				return new self(255, 69, 0);
				break;
			case 'orchid':
				return new self(218, 112, 214);
				break;
			case 'palegoldenrod':
				return new self(238, 232, 170);
				break;
			case 'palegreen':
				return new self(152, 251, 152);
				break;
			case 'paleturquoise':
				return new self(175, 238, 238);
				break;
			case 'palevioletred':
				return new self(219, 112, 147);
				break;
			case 'papayawhip':
				return new self(255, 239, 213);
				break;
			case 'peachpuff':
				return new self(255, 218, 185);
				break;
			case 'peru':
				return new self(205, 133, 63);
				break;
			case 'pink':
				return new self(255, 192, 203);
				break;
			case 'plum':
				return new self(221, 160, 221);
				break;
			case 'powderblue':
				return new self(176, 224, 230);
				break;
			case 'purple':
				return new self(160, 32, 240);
				break;
			case 'purple':
				return new self(127, 0, 127);
				break;
			case 'red':
				return new self(255, 0, 0);
				break;
			case 'rosybrown':
				return new self(188, 143, 143);
				break;
			case 'royalblue':
				return new self(65, 105, 225);
				break;
			case 'saddlebrown':
				return new self(139, 69, 19);
				break;
			case 'salmon':
				return new self(250, 128, 114);
				break;
			case 'sandybrown':
				return new self(244, 164, 96);
				break;
			case 'seagreen':
				return new self(46, 139, 87);
				break;
			case 'seashell':
				return new self(255, 245, 238);
				break;
			case 'sienna':
				return new self(160, 82, 45);
				break;
			case 'silver':
				return new self(192, 192, 192);
				break;
			case 'skyblue':
				return new self(135, 206, 235);
				break;
			case 'slateblue':
				return new self(106, 90, 205);
				break;
			case 'slategray':
				return new self(112, 128, 144);
				break;
			case 'snow':
				return new self(255, 250, 250);
				break;
			case 'springgreen':
				return new self(0, 255, 127);
				break;
			case 'steelblue':
				return new self(70, 130, 180);
				break;
			case 'tan':
				return new self(210, 180, 140);
				break;
			case 'teal':
				return new self(0, 128, 128);
				break;
			case 'thistle':
				return new self(216, 191, 216);
				break;
			case 'tomato':
				return new self(255, 99, 71);
				break;
			case 'turquoise':
				return new self(64, 224, 208);
				break;
			case 'violet':
				return new self(238, 130, 238);
				break;
			case 'wheat':
				return new self(245, 222, 179);
				break;
			case 'white':
				return new self(255, 255, 255);
				break;
			case 'whitesmoke':
				return new self(245, 245, 245);
				break;
			case 'yellow':
				return new self(255, 255, 0);
				break;
			case 'yellowgreen':
				return new self(154, 205, 50);
				break;
			default:
				throw new CM_Exception_Invalid('Cannot find X11 color match for `' . $colorName . '`');
		}
	}
}
