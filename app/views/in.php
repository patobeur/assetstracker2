	<h1>Rendez un Pc !</h1>
	<div class="form-container">
		<form class="form"  method="POST" action="in">
			{{errors}}
			<div class="blocs">
				<div class="cards">
					<div>
						{{msgpc}}
					</div>
					<div>
						<svg id="barcodePC"></svg>
					</div>
				</div>
				<div class="input-container">
					<span class="icon">🔒</span>
					<input type="text" id="codepc" name="pc" placeholder="codebarre pc" autofocus>
				</div>
			</div>
			<div class="blocs center">
				<button type="submit">Check</button>
			</div>
		</form>
	</div>
	<script src="/vendor/JsBarcode/JsBarcode.all.min.js"></script>
	<script defer type="module" src="/js/in.js"></script>