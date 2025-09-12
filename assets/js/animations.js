
// Animation JavaScript
(function() {
    'use strict';
    
    // Scroll animations
    function initScrollAnimations() {
        const elements = document.querySelectorAll('.scroll-fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        elements.forEach(el => observer.observe(el));
    }
    
    // Smooth scrolling for anchor links
    function initSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    
    // Parallax effect for background images
    function initParallax() {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.parallax');
            
            parallaxElements.forEach(element => {
                const speed = element.dataset.speed || 0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });
        });
    }
    
    // Hover effects
    function initHoverEffects() {
        const hoverElements = document.querySelectorAll('.hover-scale, .hover-rotate, .hover-shadow');
        
        hoverElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
            });
        });
    }
    
    // Loading animations
    function initLoadingAnimations() {
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
            
            // Animate elements on page load
            const animateElements = document.querySelectorAll('.animate-on-load');
            animateElements.forEach((el, index) => {
                setTimeout(() => {
                    el.classList.add('animate-fadeInUp');
                }, index * 100);
            });
        });
    }
    
    // Initialize all animations when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initScrollAnimations();
            initSmoothScrolling();
            initParallax();
            initHoverEffects();
            initLoadingAnimations();
        });
    } else {
        initScrollAnimations();
        initSmoothScrolling();
        initParallax();
        initHoverEffects();
        initLoadingAnimations();
    }
    
    // Add CSS for loaded state
    const style = document.createElement('style');
    style.textContent = `
        body:not(.loaded) .animate-on-load {
            opacity: 0;
            transform: translateY(30px);
        }
        
        body.loaded .animate-on-load {
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
    `;
    document.head.appendChild(style);
    
})();
