import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

// Modifies the range input value in real time
window.updateRangeValue = function(rangeInput) {

    // Get the current value of the range input
    const value = parseFloat(rangeInput);

    // Reference to the span showing the range value and the range input element
    const rangeValue = document.getElementById('rangeValue');
    const rangeElement = document.getElementById('task_progress');

    // Get the minimum and maximum values of the range input
    const min = parseFloat(rangeElement.min) || 0;
    const max = parseFloat(rangeElement.max) || 100;

    // Use setTimeout to wait for the DOM to be rendered
    setTimeout(() => {

        // Calculate the width of the range input
        const sliderWidth = rangeElement.clientWidth;
        
        // Calculate the percentage position based on the slider width
        const percentage = (value - min) / (max - min);
        const offset = sliderWidth * percentage;

        // Update the text and position of the span
        rangeValue.textContent = value + ' %';
        rangeValue.style.left = `${offset}px`;

    }, 0); // 0ms delay to allow the DOM to render
};

// Call the function after the page loads to set the initial value
document.addEventListener('turbo:load', function() {
    const rangeElement = document.getElementById('task_progress');
    if (rangeElement) {
        setTimeout(() => {
            updateRangeValue(rangeElement.value);
        }, 100);
        
    }
});