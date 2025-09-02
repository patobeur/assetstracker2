"use strict";
import { _Contact } from './Contact.js'
class _App {
	constructor() {
	}
	init = () => {
        console.log('-----------------EXAMPLE ok--------------------')
		const Contact = new _Contact();
		Contact.init()
		this.removeJsScript();
    }
	removeJsScript = () => {
		document.getElementById('example-js').remove()
	}
}
export {_App}