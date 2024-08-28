import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

window.updateRangeValue = function(rangeInput) {

    // Obtenez la valeur actuelle du curseur
    const value = parseFloat(rangeInput);

    const rangeValue = document.getElementById('rangeValue');
    const rangeElement = document.getElementById('task_progress');

    // Obtenez le minimum et maximum de la plage du curseur
    const min = parseFloat(rangeElement.min) || 0;
    const max = parseFloat(rangeElement.max) || 100;


    // Utilisation de setTimeout pour attendre que le DOM soit rendu
    setTimeout(() => {
        const sliderWidth = rangeElement.clientWidth;
        
        // Calcul de la position en pourcentage par rapport à la largeur du slider
        const percentage = (value - min) / (max - min);
        const offset = sliderWidth * percentage;

        // Mise à jour du texte et de la position du span
        rangeValue.textContent = value + ' %';
        rangeValue.style.left = `${offset}px`;
    }, 0); // Délai de 0ms pour permettre le rendu du DOM
};

// Appeler la fonction après le chargement de la page pour la valeur initiale
document.addEventListener('turbo:load', function() {
    const rangeElement = document.getElementById('task_progress');
    if (rangeElement) {
        setTimeout(() => {
            updateRangeValue(rangeElement.value);
        }, 100);
        
    }
});