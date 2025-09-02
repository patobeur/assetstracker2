const _front = {
	id: new Number(0),
	createDiv: function (params) {
		let element = document.createElement(params.tag??'div');
		if (params.attributes) {
			for (const key in params.attributes) {
				if (Object.hasOwnProperty.call(params.attributes, key)){
					element[key] = params.attributes[key];
				}
				
			}
		}
		if (params.style) {
				for (const key2 in params.style) {
					if (Object.hasOwnProperty.call(params.style, key2))
						element.style[key2] = params.style[key2];
			}
		}
		if (params.recenter === true && (params.style.left && params.style.top)) {
			let n = (params.attributes && params.attributes.className) ? params.attributes.className : 'vide'
			let t = params.style.top.slice(0, -2);
			let l = params.style.left.slice(0, -2);
			let w = params.style.width.slice(0, -2);
			let h = params.style.height.slice(0, -2);
			this.recentering(element, t, l, w, h, n)
		}

		return element;
	},
	addCss(stringcss, styleid) {
		let style = document.createElement("style");
		style.setAttribute('type','text/css');
		style.textContent = stringcss;
		style.id = "css_" + styleid;
		document.getElementsByTagName("head")[0].appendChild(style);
	},
	recentering: function (element, t, l, w, h, n) {
		// console.log('pos', t, l, w, h, n)
		if (element.style.left && element.style.width) {
			element.style.left = l - (w / 2) + 'px'
		}
		if (element.style.top && element.style.height) {
			element.style.top = t - (h / 2) + 'px'
		}
		return element

	},
	replace: function (string) {
		// TODO
		// const map = {
		// 	"&": "&amp;",
		// 	"<": "&lt;",
		// 	">": "&gt;",
		// 	'"': "&quot;",
		// 	"'": "&#x27;",
		// 	"/": "&#x2F;",
		// 	"`": "&#x60;",
		// 	"=": "&#x3D;",
		// 	"-": "&#x2D;"
		// };
		// const reg = /[&<>"'/`=-]/g;
		// return string.replace(reg, (match) => map[match]);
		console.log('replace is inactive')
	},
	sanitize: function (string) {
		// TODO
		const regex = /[^a-zA-Z0-9 ,:'._-]/g;
		return string.replace(regex, '');
	},
	sanitizeName: function (string) {
		// TODO
		const regex = /[^a-zA-Z0-9]/g;
		return string.replace(regex, '');
	},
	rand: (min, max) => { return Math.floor(Math.random() * (max - min + 1) + min); },
};
export { _front }
