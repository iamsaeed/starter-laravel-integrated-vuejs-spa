/**
 * Guest Pages JavaScript - Interactive features for landing page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Scroll reveal animation
    initScrollReveal();

    // Smooth scrolling for anchor links
    initSmoothScroll();

    // Stats counter animation
    initStatsCounter();

    // Parallax effect
    initParallax();

    // Mobile menu toggle
    initMobileMenu();

    // Typing animation
    initTypingAnimation();
});

/**
 * Scroll reveal animation
 */
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal');

    if (reveals.length === 0) return;

    const revealOnScroll = () => {
        const windowHeight = window.innerHeight;
        const revealPoint = 100;

        reveals.forEach(reveal => {
            const revealTop = reveal.getBoundingClientRect().top;

            if (revealTop < windowHeight - revealPoint) {
                reveal.classList.add('active');
            }
        });
    };

    // Initial check
    revealOnScroll();

    // On scroll
    window.addEventListener('scroll', revealOnScroll, { passive: true });
}

/**
 * Smooth scrolling for anchor links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');

            // Skip if it's just "#"
            if (href === '#') return;

            const target = document.querySelector(href);

            if (target) {
                e.preventDefault();

                const offsetTop = target.offsetTop - 80; // Account for fixed header

                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Animate stats counters
 */
function initStatsCounter() {
    const stats = document.querySelectorAll('.stat-number');

    if (stats.length === 0) return;

    let hasAnimated = false;

    const animateCounter = (element) => {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;

        const updateCounter = () => {
            current += increment;

            if (current < target) {
                element.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target;
            }
        };

        updateCounter();
    };

    const checkPosition = () => {
        if (hasAnimated) return;

        const statsSection = document.querySelector('.stats-section');
        if (!statsSection) return;

        const rect = statsSection.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;

        if (isVisible) {
            hasAnimated = true;
            stats.forEach(stat => animateCounter(stat));
        }
    };

    window.addEventListener('scroll', checkPosition, { passive: true });
    checkPosition(); // Initial check
}

/**
 * Parallax scrolling effect
 */
function initParallax() {
    const parallaxElements = document.querySelectorAll('.parallax');

    if (parallaxElements.length === 0) return;

    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;

        parallaxElements.forEach(element => {
            const speed = element.getAttribute('data-speed') || 0.5;
            const yPos = -(scrolled * speed);
            element.style.transform = `translateY(${yPos}px)`;
        });
    }, { passive: true });
}

/**
 * Mobile menu toggle
 */
function initMobileMenu() {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuClose = document.getElementById('mobile-menu-close');

    if (!menuToggle || !mobileMenu) return;

    menuToggle.addEventListener('click', () => {
        mobileMenu.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    if (menuClose) {
        menuClose.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Close on outside click
    mobileMenu.addEventListener('click', (e) => {
        if (e.target === mobileMenu) {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

/**
 * Typing animation for hero text
 */
function initTypingAnimation() {
    const typingElement = document.querySelector('.typing-text');

    if (!typingElement) return;

    const texts = typingElement.getAttribute('data-texts')?.split('|') || [
        'Web Applications',
        'Mobile Apps',
        'SaaS Products',
        'Custom Solutions'
    ];

    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typingSpeed = 100;

    const type = () => {
        const currentText = texts[textIndex];

        if (isDeleting) {
            typingElement.textContent = currentText.substring(0, charIndex - 1);
            charIndex--;
            typingSpeed = 50;
        } else {
            typingElement.textContent = currentText.substring(0, charIndex + 1);
            charIndex++;
            typingSpeed = 100;
        }

        if (!isDeleting && charIndex === currentText.length) {
            // Pause at end
            typingSpeed = 2000;
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            textIndex = (textIndex + 1) % texts.length;
            typingSpeed = 500;
        }

        setTimeout(type, typingSpeed);
    };

    // Start typing animation
    setTimeout(type, 1000);
}

/**
 * Form validation and submission
 */
function initContactForm() {
    const form = document.getElementById('contact-form');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;

        // Disable button and show loading
        submitButton.disabled = true;
        submitButton.textContent = 'Sending...';

        // Get form data
        const formData = new FormData(form);

        try {
            // Replace with your actual endpoint
            const response = await fetch('/api/contact', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (response.ok) {
                // Success
                showNotification('Message sent successfully!', 'success');
                form.reset();
            } else {
                // Error
                showNotification('Failed to send message. Please try again.', 'error');
            }
        } catch (error) {
            showNotification('An error occurred. Please try again.', 'error');
        } finally {
            // Re-enable button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in-up ${
        type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    } text-white`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Dark mode toggle - matches Vue.js frontend pattern
 */
function initDarkMode() {
    const toggle = document.getElementById('dark-mode-toggle');
    const mobileToggle = document.getElementById('mobile-dark-mode-toggle');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const lightIcon = document.getElementById('theme-toggle-light-icon');
    const mobileDarkIcon = document.getElementById('mobile-theme-toggle-dark-icon');
    const mobileLightIcon = document.getElementById('mobile-theme-toggle-light-icon');

    // Update icon visibility based on current mode
    const updateIcons = () => {
        const isDark = document.documentElement.classList.contains('dark');

        if (darkIcon && lightIcon) {
            if (isDark) {
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
            } else {
                darkIcon.classList.add('hidden');
                lightIcon.classList.remove('hidden');
            }
        }

        if (mobileDarkIcon && mobileLightIcon) {
            if (isDark) {
                mobileDarkIcon.classList.remove('hidden');
                mobileLightIcon.classList.add('hidden');
            } else {
                mobileDarkIcon.classList.add('hidden');
                mobileLightIcon.classList.remove('hidden');
            }
        }
    };

    // Initial icon state
    updateIcons();

    // Toggle dark mode
    const toggleDarkMode = () => {
        document.documentElement.classList.toggle('dark');

        // Save preference using 'setting_dark_mode' key (matches Vue.js frontend)
        const isDark = document.documentElement.classList.contains('dark');
        localStorage.setItem('setting_dark_mode', isDark ? 'dark' : 'light');

        // Update icons
        updateIcons();
    };

    // Desktop toggle
    if (toggle) {
        toggle.addEventListener('click', toggleDarkMode);
    }

    // Mobile toggle
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleDarkMode);
    }
}

/**
 * Initialize app cards hover effect
 */
function initAppCards() {
    const appCards = document.querySelectorAll('.app-card');

    appCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });

        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
}

// Auto-initialize optional features
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initAppCards();
        initContactForm();
        initDarkMode();
    });
} else {
    initAppCards();
    initContactForm();
    initDarkMode();
}
