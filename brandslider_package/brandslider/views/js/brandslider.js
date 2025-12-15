/**
 * Brand Slider JavaScript
 * Vanilla JS carousel with touch support
 */

(function () {
    'use strict';

    class BrandSlider {
        constructor(element) {
            this.slider = element;
            this.track = this.slider.querySelector('.brand-slider-track');

            if (!this.track) return;

            this.slides = Array.from(this.track.querySelectorAll('.brand-slide'));

            if (this.slides.length === 0) return;

            // Get configuration from data attributes
            this.config = {
                itemsVisible: parseInt(this.slider.dataset.itemsVisible) || 5,
                speed: parseInt(this.slider.dataset.speed) || 500,
                autoplay: this.slider.dataset.autoplay === 'true',
                autoplaySpeed: parseInt(this.slider.dataset.autoplaySpeed) || 3000,
                showNav: this.slider.dataset.showNav === 'true',
                showDots: this.slider.dataset.showDots === 'true'
            };

            this.currentIndex = 0;
            this.totalSlides = this.slides.length;
            this.autoplayTimer = null;
            this.isAnimating = false;
            this.touchStartX = 0;
            this.touchEndX = 0;

            this.init();
        }

        init() {
            this.setSlideWidth();
            this.setupNavigation();
            this.setupDots();
            this.setupTouchEvents();
            this.setupResizeHandler();

            if (this.config.autoplay) {
                this.startAutoplay();
            }

            // Pause autoplay on hover
            this.slider.parentElement.addEventListener('mouseenter', () => this.pauseAutoplay());
            this.slider.parentElement.addEventListener('mouseleave', () => {
                if (this.config.autoplay) this.startAutoplay();
            });
        }

        setSlideWidth() {
            const containerWidth = this.slider.offsetWidth;
            const itemsToShow = this.getItemsToShow();
            const slideWidth = containerWidth / itemsToShow;

            this.slides.forEach(slide => {
                slide.style.width = `${slideWidth}px`;
            });

            this.slideWidth = slideWidth;
            this.maxIndex = Math.max(0, this.totalSlides - itemsToShow);

            // Reset position if needed
            if (this.currentIndex > this.maxIndex) {
                this.currentIndex = this.maxIndex;
                this.updatePosition(false);
            }
        }

        getItemsToShow() {
            const width = window.innerWidth;

            if (width <= 480) {
                return Math.min(2, this.config.itemsVisible);
            } else if (width <= 768) {
                return Math.min(3, this.config.itemsVisible);
            } else if (width <= 992) {
                return Math.min(4, this.config.itemsVisible);
            } else {
                return this.config.itemsVisible;
            }
        }

        setupNavigation() {
            const wrapper = this.slider.parentElement;
            this.prevBtn = wrapper.querySelector('.brand-slider-prev');
            this.nextBtn = wrapper.querySelector('.brand-slider-next');

            if (this.prevBtn) {
                this.prevBtn.addEventListener('click', () => this.prev());
            }
            if (this.nextBtn) {
                this.nextBtn.addEventListener('click', () => this.next());
            }

            this.updateNavButtons();
        }

        setupDots() {
            if (!this.config.showDots) return;

            const section = this.slider.closest('.brand-slider-section');
            this.dotsContainer = section.querySelector('.brand-slider-dots');

            if (!this.dotsContainer) return;

            const dotsCount = this.maxIndex + 1;

            for (let i = 0; i < dotsCount; i++) {
                const dot = document.createElement('button');
                dot.classList.add('brand-slider-dot');
                dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                if (i === 0) dot.classList.add('active');

                dot.addEventListener('click', () => this.goToSlide(i));
                this.dotsContainer.appendChild(dot);
            }

            this.dots = Array.from(this.dotsContainer.querySelectorAll('.brand-slider-dot'));
        }

        setupTouchEvents() {
            this.track.addEventListener('touchstart', (e) => {
                this.touchStartX = e.changedTouches[0].screenX;
                this.pauseAutoplay();
            }, { passive: true });

            this.track.addEventListener('touchend', (e) => {
                this.touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
                if (this.config.autoplay) this.startAutoplay();
            }, { passive: true });
        }

        handleSwipe() {
            const threshold = 50;
            const diff = this.touchStartX - this.touchEndX;

            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        }

        setupResizeHandler() {
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    this.setSlideWidth();
                    this.updateDots();
                    this.updateNavButtons();
                }, 100);
            });
        }

        next() {
            if (this.isAnimating || this.currentIndex >= this.maxIndex) return;
            this.currentIndex++;
            this.updatePosition();
        }

        prev() {
            if (this.isAnimating || this.currentIndex <= 0) return;
            this.currentIndex--;
            this.updatePosition();
        }

        goToSlide(index) {
            if (this.isAnimating || index === this.currentIndex) return;
            this.currentIndex = Math.min(Math.max(0, index), this.maxIndex);
            this.updatePosition();
        }

        updatePosition(animate = true) {
            this.isAnimating = true;
            const offset = -this.currentIndex * this.slideWidth;

            this.track.style.transition = animate ?
                `transform ${this.config.speed}ms cubic-bezier(0.25, 0.1, 0.25, 1)` :
                'none';
            this.track.style.transform = `translateX(${offset}px)`;

            this.updateNavButtons();
            this.updateDots();

            setTimeout(() => {
                this.isAnimating = false;
            }, this.config.speed);
        }

        updateNavButtons() {
            if (this.prevBtn) {
                this.prevBtn.disabled = this.currentIndex <= 0;
            }
            if (this.nextBtn) {
                this.nextBtn.disabled = this.currentIndex >= this.maxIndex;
            }
        }

        updateDots() {
            if (!this.dots || this.dots.length === 0) return;

            // Recreate dots if count changed
            const newDotsCount = this.maxIndex + 1;
            if (this.dots.length !== newDotsCount) {
                this.dotsContainer.innerHTML = '';
                for (let i = 0; i < newDotsCount; i++) {
                    const dot = document.createElement('button');
                    dot.classList.add('brand-slider-dot');
                    dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                    dot.addEventListener('click', () => this.goToSlide(i));
                    this.dotsContainer.appendChild(dot);
                }
                this.dots = Array.from(this.dotsContainer.querySelectorAll('.brand-slider-dot'));
            }

            this.dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === this.currentIndex);
            });
        }

        startAutoplay() {
            this.pauseAutoplay();
            this.autoplayTimer = setInterval(() => {
                if (this.currentIndex >= this.maxIndex) {
                    this.currentIndex = -1;
                }
                this.next();
            }, this.config.autoplaySpeed);
        }

        pauseAutoplay() {
            if (this.autoplayTimer) {
                clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
            }
        }

        destroy() {
            this.pauseAutoplay();
            if (this.dotsContainer) {
                this.dotsContainer.innerHTML = '';
            }
        }
    }

    // Initialize all sliders when DOM is ready
    function initSliders() {
        const sliders = document.querySelectorAll('.brand-slider');
        sliders.forEach(slider => {
            new BrandSlider(slider);
        });
    }

    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSliders);
    } else {
        initSliders();
    }

    // Also run on prestashop page change (for AJAX navigation)
    if (typeof prestashop !== 'undefined') {
        prestashop.on('updatedProduct', initSliders);
    }
})();
