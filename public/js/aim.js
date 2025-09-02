document.addEventListener("DOMContentLoaded", () => {
    // const gamezone = document.createElement('div');
    const gamezone = document.getElementById('container');
    // gamezone.style.width = "100%";
    // gamezone.style.height = "100%";
    // gamezone.style.position = "absolute";
    // gamezone.style.overflow = "hidden";
    // gamezone.style.zIndex = "2";
    // document.body.prepend(gamezone);

    const updateGamezoneDimensions = () => {
        const rect = gamezone.getBoundingClientRect();
        return {
            width: rect.width,
            height: rect.height,
        };
    };

    let { width: gamezoneWidth, height: gamezoneHeight } = updateGamezoneDimensions();

    window.addEventListener("resize", () => {
        const dimensions = updateGamezoneDimensions();
        gamezoneWidth = dimensions.width;
        gamezoneHeight = dimensions.height;
    });

    let score = 0;
    let bestScore = localStorage.getItem("bestScore") ? parseInt(localStorage.getItem("bestScore"), 10) : 0;
    const maxObjects = 1; // Limite d'objets simultanés
    const pointTimeout = 5000; // Durée de vie maximale d'un objet (en ms)
    const colorSteps = [
        "yellow",
        "gold",
        "orange",
        "darkorange",
        "orangered",
        "black",
    ]; // Dégradé des couleurs

    const objects = []; // Liste des objets actifs

    // Affichage des scores
    const scoreDisplay = document.createElement("div");
    scoreDisplay.style.position = "absolute";
    scoreDisplay.style.top = "70px";
    scoreDisplay.style.right = "10px";
    scoreDisplay.style.fontSize = "16px";
    scoreDisplay.style.fontWeight = "bold";
    scoreDisplay.innerText = `Score: ${score}`;
    document.body.append(scoreDisplay);

    const bestScoreDisplay = document.createElement("div");
    bestScoreDisplay.style.position = "absolute";
    bestScoreDisplay.style.top = "90px";
    bestScoreDisplay.style.right = "10px";
    bestScoreDisplay.style.fontSize = "16px";
    bestScoreDisplay.style.fontWeight = "bold";
    bestScoreDisplay.innerText = `Best Score: ${bestScore}`;
    document.body.append(bestScoreDisplay);

    function createMovingObject() {
        if (objects.length >= maxObjects) return;

        const size = 50;
        const x = Math.random() * (gamezoneWidth - size);
        const y = Math.random() * (gamezoneHeight - size);

        const object = document.createElement("div");
        object.style.width = `${size}px`;
        object.style.height = `${size}px`;
        object.style.position = "absolute";
        object.style.left = `${x}px`;
        object.style.top = `${y}px`;
        object.style.backgroundColor = colorSteps[0]; // Couleur initiale
        object.style.borderRadius = "50%";
        object.style.transition = "opacity 0.5s ease-out, background-color 0.5s";
        object.style.display = "flex";
        object.style.justifyContent = "center";
        object.style.alignItems = "center";
        object.style.color = "black";
        object.style.color = "black";
        object.style.userSelect = 'none';



        gamezone.appendChild(object);

        const objData = {
            element: object,
            x: x,
            y: y,
            dx: Math.random() * 2 - 1,
            dy: Math.random() * 2 - 1,
            size: size,
            speed: 3,
            rotation: 0,
            isClickable: true,
            colorIndex: 0,
            startTime: Date.now(), // Initialisation correcte de startTime
        };

        objects.push(objData);

        // Gestion de la durée de vie et du dégradé de couleurs
        manageObjectLifecycle(objData);

        // Gestion des clics
        object.addEventListener("click", () => handleObjectClick(objData),false);

        // Déplacement de l'objet
        moveObject(objData);
    }

    function handleObjectClick(obj) {
        if (!obj.isClickable) return;

        const reactionTime = Date.now() - obj.startTime; // Utilisation correcte de startTime
        const points = Math.max(10000 - reactionTime, 0) / 20;
        obj.element.textContent = points;
        obj.element.style.width = `40px`;
        obj.element.style.height = `40px`;
        let point = Math.round(points)
        score += point;
        scoreDisplay.textContent = `Score: ${point}`;

        // Mettre à jour le meilleur score si nécessaire
        if (score > bestScore) {
            bestScore = score;
            localStorage.setItem("bestScore", bestScore);
            bestScoreDisplay.innerText = `Best Score: ${bestScore}`;
        }

        removeObject(obj);
    }

    function manageObjectLifecycle(obj) {
        const colorChangeInterval = pointTimeout / (colorSteps.length - 1);

        const colorChange = setInterval(() => {
            if (obj.colorIndex >= colorSteps.length - 1) {
                clearInterval(colorChange);
                obj.isClickable = false;
                obj.element.style.backgroundColor = "black";

                // Supprimer l'objet après qu'il devienne noir
                setTimeout(() => removeObject(obj), 500);
            } else {
                obj.colorIndex++;
                obj.element.style.backgroundColor = colorSteps[obj.colorIndex];
            }
        }, colorChangeInterval);
    }

    function removeObject(obj) {
        obj.element.style.opacity = "0";
        setTimeout(() => {
            obj.element.remove();
            const index = objects.indexOf(obj);
            if (index > -1) {
                objects.splice(index, 1);
            }

            // Créer un nouvel objet si nécessaire
            createMovingObject();
        }, 500);
    }

    function moveObject(obj) {
        let currentDx = obj.dx;
        let currentDy = obj.dy;

        function updatePosition() {
            if (!obj || !obj.element || !obj.element.parentNode) return;

            obj.x += currentDx * obj.speed;
            obj.y += currentDy * obj.speed;

            if (obj.x <= 0 || obj.x + obj.size >= gamezoneWidth) currentDx *= -1;
            if (obj.y <= 0 || obj.y + obj.size >= gamezoneHeight) currentDy *= -1;

            obj.element.style.left = `${Math.min(Math.max(0, obj.x), gamezoneWidth - obj.size)}px`;
            obj.element.style.top = `${Math.min(Math.max(0, obj.y), gamezoneHeight - obj.size)}px`;

            requestAnimationFrame(updatePosition);
        }

        updatePosition();
    }

    // Créer un seul objet au départ
    createMovingObject();
});
