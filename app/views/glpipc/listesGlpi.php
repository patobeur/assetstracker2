    <h2>{{PAGETITLE}}</h2>
    <div class="form-container-table">
		<form class="form" method="POST"{{FORMACTION}} id="{{FORMNAME}}">
            {{ACTIONS}}
            <table class="table-responsive" border="1" cellspacing="0" cellpadding="5">
                <thead>
                    {{TITLES}}
                </thead>
                <tbody>
                    {{CONTENT}}
                </tbody>
            </table>
            {{buttons}}
		</form>
    <div class="form-container-table">{{SQL}}</div>