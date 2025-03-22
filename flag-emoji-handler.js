/**
 * flag-emoji-handler.js
 * Handles rendering of flag emojis on Windows devices
 */
document.addEventListener('DOMContentLoaded', function() {
    // Function to detect Windows OS
    function isWindowsDevice() {
        return window.navigator.userAgent.indexOf('Windows') !== -1;
    }
    
    // Process all flag emoji elements on the page
    function processFlagEmojis() {
        // Only proceed for Windows devices
        if (!isWindowsDevice()) return;
        
        // Find all flag emoji spans
        const flagElements = document.querySelectorAll('.flag-emoji');
        
        flagElements.forEach(function(element) {
            // Check if this element has a windows_flag_url data attribute
            const flagUrl = element.getAttribute('data-windows-flag-url');
            
            if (flagUrl) {
                // Create image element
                const imgElement = document.createElement('img');
                imgElement.src = flagUrl;
                imgElement.alt = 'Country flag';
                imgElement.className = 'windows-flag-img';
                imgElement.style.height = '1em';
                imgElement.style.verticalAlign = 'middle';
                imgElement.style.marginLeft = '4px';
                
                // Replace the emoji text with the image
                element.innerHTML = '';
                element.appendChild(imgElement);
            }
        });
    }
    
    // Run the processor
    processFlagEmojis();
    
    // Process flag emojis inserted after the page loads (for dynamic content)
    const observer = new MutationObserver(function(mutations) {
        processFlagEmojis();
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}); 