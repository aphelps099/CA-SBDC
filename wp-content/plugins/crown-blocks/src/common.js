

const CrownBlocks = {


	getThemeColorPalette: () => {
		return [
			{ "name": "Dark Blue", "slug": "dark-blue", "color": "#032040" },
			{ "name": "Dark Medium Blue", "slug": "dark-med-blue", "color": "#012D61" },
			{ "name": "Blue", "slug": "blue", "color": "#0381C3" },
			{ "name": "Light Medium Blue", "slug": "light-med-blue", "color": "#98DBF9" },
			{ "name": "Light Blue", "slug": "light-blue", "color": "#D0E4F8" },
			{ "name": "Extra Light Blue", "slug": "x-light-blue", "color": "#F4F8FD" },
			{ "name": "Red", "slug": "red", "color": "#D11141" },
			{ "name": "Green", "slug": "green", "color": "#84C318" },
			{ "name": "Light Gray", "slug": "light-gray", "color": "#CFD2D8" },
			{ "name": "Gray", "slug": "gray", "color": "#696D7D" },
			{ "name": "Rain", "slug": "rain", "color": "#6A8EAE" },
			{ "name": "Dark Gray", "slug": "dark-gray", "color": "#343a40" },
			{ "name": "Ghost", "slug": "ghost", "color": "#E0E0E0" },
			{ "name": "White", "slug": "white", "color": "#FFFFFF" }
		];
	},


	getColorLuminosity: (hex = '') => {
		hex = hex.replace(/[^0-9a-f]/i, '');
		if(hex == '' || hex.length < 3) hex = 'fff';
		if(hex.length < 6) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
		let c = [];
		for(var i = 0; i < 3; i++) c.push(parseInt(hex.substring(i * 2, i * 2 + 2), 16) / 255);
		for(var i = 0; i < 3; i++) {
			if(c[i] <= 0.03928) {
				c[i] = c[i] / 12.92;
			} else {
				c[i] = Math.pow((c[i] + 0.055) / 1.055, 2.4);
			}
		}
		let luminosity = (0.2126 * c[0]) + (0.7152 * c[1]) + (0.0722 * c[2]);
		return luminosity;
	},


	isDarkColor: (hex, threshold = 0.607843137) => {
		let luminosity = CrownBlocks.getColorLuminosity(hex);
		return luminosity <= threshold;
	},


	hexToRgb: (hex) => {
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
		hex = hex.replace(shorthandRegex, function(m, r, g, b) {
			return r + r + g + g + b + b;
		});
		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		return result ? {
			r: parseInt(result[1], 16),
			g: parseInt(result[2], 16),
			b: parseInt(result[3], 16)
		} : null;
	},


	reduce: (numerator, denominator) => {
		let gcd = function gcd(a, b) { return b ? gcd(b, a % b) : a; };
		gcd = gcd(numerator, denominator);
		return [numerator / gcd, denominator / gcd];
	}


};


export default CrownBlocks;