"use strict";
// import { _Com } from './Com.js'
import { _Login } from './Login.js'
class _Contact {
	constructor() {
		this.user = null;
		// this.Com = new _Com();
		this.Login = new _Login();
	}
	async init() {
		console.log('-----------------first _Contact pending ...--------------------')
		const messageDiv = document.getElementById("message");

		const response = await fetch("/contact", {
			method: "POST",
			headers: {"Content-Type": "application/x-www-form-urlencoded"},
			body: `say=hi`
		});
		const data = await response.json();
		if (response.ok) {
			console.log('-----------------first _Contact Done !--------------------')
			console.log(data)
			if(data.message==="NEED LOGIN"){
				this.Login.addForm();
			}
		}
	 }
}
export { _Contact };
