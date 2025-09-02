    <h1>Connexion</h1>
        <div class="form-container">
        <form class="form" method="POST" action="">
            {{errors}}
            {{loginform}}
        </form>
    </div>
    <script>
        let userok = document.getElementById('username')
        let passok = document.getElementById('password')
        let submitlogin = document.getElementById('submitlogin')
        function toggleSubmitButton() {
            if (userok.value !== '' && passok.value !== '') {
                submitlogin.classList.add('ok');
            } else {
                submitlogin.classList.remove('ok');
            }
        }
        userok.addEventListener('input', toggleSubmitButton);
        passok.addEventListener('input', toggleSubmitButton);
        
    </script>