<h1>{{TITLE}}</h1>
{{CONTENT}}
<style>
        .config-panel {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
            align-content: center;
            flex-direction: row;
        }
        label,span {
            display: inline;
            margin-bottom: 5px;
            padding-right: 5px;
        }
        input {
            padding: 8px;
            box-sizing: border-box;
        }
        select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>
    
    
    <div class="config-panel">
        <div class="form-group">
            <label for="theme">Thème</label>
            <select id="theme">
                <option value="clair">Clair</option>
                <option value="sombre">Sombre</option>
            </select>
        </div>
        <!-- <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" placeholder="Entrez votre nom">
        </div> -->
        <div class="form-group">
            <!-- <label for="notifications">Notifications</label> -->
            <span>Activer la console : </span><input type="checkbox" id="notifications">
        </div>
        <div class="form-group">
            <label for="navigation">Navigation</label>
            <select id="navigation">
                <option value="1">un</option>
                <option value="2">deux</option>
            </select>
        </div>
    </div>


    <script>
        // Charger les options depuis le localStorage au chargement
        document.addEventListener('DOMContentLoaded', () => {
            const theme = localStorage.getItem('theme');
            if (theme) document.getElementById('theme').value = theme;

            const navigation = localStorage.getItem('navigation');
            if (navigation) document.getElementById('navigation').value = theme;

            // const username = localStorage.getItem('username');
            // if (username) document.getElementById('username').value = username;

            const notifications = localStorage.getItem('notifications') === 'true';
            document.getElementById('notifications').checked = notifications;
        });


        // Mettre à jour localStorage à chaque changement
        const updateLocalStorage = (key, value) => {
            localStorage.setItem(key, value);
        };

        function applyDarkTheme() {
            // Récupérer le thème depuis le localStorage
            const theme = localStorage.getItem('theme');
            let themes = {'sombre':'sombre','clair':'clair',}
            let currentTheme = themes[theme];

            // Vérifier si le thème est 'sombre'
            if (theme != null && currentTheme) {
                document.body.classList.remove('sombre');
                document.body.classList.remove('clair');
                document.body.classList.add(currentTheme);
            }
        }
        function applyNotifications() {
            const notif = localStorage.getItem('notifications');
            (notif === 'true') 
                ? document.getElementById('console').classList.remove('hidden')
                : document.getElementById('console').classList.add('hidden');
            
        }

        // Écouter les changements pour chaque champ
        document.getElementById('theme').addEventListener('change', (e) => {
            updateLocalStorage('theme', e.target.value);
            applyDarkTheme(e.target.value);
        });
        document.getElementById('navigation').addEventListener('change', (e) => {
            updateLocalStorage('navigation', e.target.value);
            applyDarkTheme(e.target.value);
        });

        // document.getElementById('username').addEventListener('input', (e) => {
        //     updateLocalStorage('username', e.target.value);
        // });

        document.getElementById('notifications').addEventListener('change', (e) => {
            updateLocalStorage('notifications', e.target.checked);
            applyNotifications(e.target.value);
        });



    </script>