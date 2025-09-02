function applyInterface() {
    // Récupérer le thème depuis le localStorage
    const theme = localStorage.getItem('theme');
    let themes = {'sombre':'sombre','clair':'clair'}
    let currentTheme = themes[theme] ?? 'clair';
    
    if (theme != null && currentTheme) {
        document.body.classList.remove('sombre');
        document.body.classList.remove('clair');
        document.body.classList.add(currentTheme);
    }
    const notif = localStorage.getItem('notifications');
    (notif === 'true') 
        ? document.getElementById('console').classList.remove('hidden')
        : document.getElementById('console').classList.add('hidden');
}
document.addEventListener('DOMContentLoaded', (applyInterface));