// flag-emoji-handler.js - Simple script to handle Windows flag emojis
window.addEventListener('load', function() {
    // Make sure we don't affect anything until the page fully loads
    setTimeout(function() {
        // Only run on Windows
        if (navigator.userAgent.indexOf('Windows') === -1) return;
        
        // IMPORTANT: Never touch the navbar or any of its elements
        var navbarElements = document.querySelectorAll('.navbar, .navbar *, .navbar-logo, .navbar-toggle, .navbar-container, .navbar-list');
        for (var i = 0; i < navbarElements.length; i++) {
            navbarElements[i].setAttribute('data-protected', 'true');
        }
        
        // Find flag elements with the data-windows-flag-url attribute
        // But ONLY process those that are NOT inside the navbar
        var flags = document.querySelectorAll('.flag-emoji[data-windows-flag-url]:not([data-protected="true"])');
        
        // Replace each flag with an image
        for (var i = 0; i < flags.length; i++) {
            var flagElement = flags[i];
            
            // Skip this element if it's inside the navbar
            var isInNavbar = false;
            var parent = flagElement.parentNode;
            while (parent) {
                if (parent.classList && (parent.classList.contains('navbar') || 
                    parent.hasAttribute('data-protected'))) {
                    isInNavbar = true;
                    break;
                }
                parent = parent.parentNode;
            }
            
            if (isInNavbar) continue;
            
            // Process the flag
            var windowsFlagUrl = flagElement.getAttribute('data-windows-flag-url');
            if (windowsFlagUrl) {
                var img = document.createElement('img');
                img.src = windowsFlagUrl;
                img.alt = 'Flag';
                img.style.height = '1em';
                img.style.verticalAlign = 'middle';
                
                // Replace content
                flagElement.innerHTML = '';
                flagElement.appendChild(img);
            }
        }
    }, 300); // Small delay to ensure everything is loaded
}); 