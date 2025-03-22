/**
 * flag-emoji-handler.js
 * Handles rendering of flag emojis on Windows devices
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Flag emoji handler loaded');
    
    // Function to detect Windows OS
    function isWindowsDevice() {
        return window.navigator.userAgent.indexOf('Windows') !== -1;
    }
    
    // Process all flag emoji elements on the page
    function processFlagEmojis() {
        // Only proceed for Windows devices
        if (!isWindowsDevice()) {
            console.log('Not a Windows device, skipping flag emoji processing');
            return;
        }
        
        console.log('Processing flag emojis for Windows device');
        
        // Find all flag emoji spans - only select those with the data-windows-flag-url attribute
        // to avoid affecting other elements that might use the same class
        const flagElements = document.querySelectorAll('.flag-emoji[data-windows-flag-url]');
        
        console.log('Found ' + flagElements.length + ' flag emoji elements to process');
        
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
    
    // Make sure the navbar is visible
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        console.log('Ensuring navbar is visible');
        navbar.style.display = '';
    } else {
        console.log('Navbar element not found!');
    }
    
    // Run the processor after ensuring navbar is visible
    setTimeout(processFlagEmojis, 500);
    
    // Process flag emojis inserted after the page loads (for dynamic content)
    const observer = new MutationObserver(function(mutations) {
        processFlagEmojis();
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}); 