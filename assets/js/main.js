/**
 * SEHS4517 Web Application Development and Management
 * CineMax Theatre - Main JavaScript
 * Handles homepage animations and movie carousel functionality
 */

(function($) {
    'use strict';

    // ========================================
    // CAROUSEL CONFIGURATION
    // ========================================
    const CarouselConfig = {
        animationDuration: 500,
        cardWidths: {
            mobile: 200,
            tablet: 220,
            desktop: 240
        },
        gap: 30,
        breakpoints: {
            mobile: 768,
            tablet: 1024
        }
    };

    // ========================================
    // CAROUSEL MODULE
    // ========================================
    const MovieCarousel = {
        currentSlide: 0,
        totalSlides: 0,
        isAnimating: false,

        init: function() {
            this.totalSlides = $('.movie-card').length;
            this.bindEvents();
            this.update();
        },

        bindEvents: function() {
            const self = this;

            // Next button click
            $('.carousel-next').on('click', function() {
                self.next();
            });

            // Previous button click
            $('.carousel-prev').on('click', function() {
                self.previous();
            });

            // Click on card to center it
            $('.movie-card').on('click', function() {
                const index = $(this).index();
                self.goToSlide(index);
            });

            // Window resize with debounce
            let resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (!self.isAnimating) {
                        self.update();
                    }
                }, 250);
            });
        },

        next: function() {
            if (this.currentSlide < this.totalSlides - 1 && !this.isAnimating) {
                this.currentSlide++;
                this.animate();
            }
        },

        previous: function() {
            if (this.currentSlide > 0 && !this.isAnimating) {
                this.currentSlide--;
                this.animate();
            }
        },

        goToSlide: function(index) {
            if (!this.isAnimating && index !== this.currentSlide) {
                this.currentSlide = index;
                this.animate();
            }
        },

        animate: function() {
            const self = this;
            this.isAnimating = true;
            this.update();

            setTimeout(function() {
                self.isAnimating = false;
                self.update();
            }, CarouselConfig.animationDuration);
        },

        getCardWidth: function() {
            const windowWidth = $(window).width();

            if (windowWidth <= CarouselConfig.breakpoints.mobile) {
                return CarouselConfig.cardWidths.mobile;
            } else if (windowWidth <= CarouselConfig.breakpoints.tablet) {
                return CarouselConfig.cardWidths.tablet;
            }
            return CarouselConfig.cardWidths.desktop;
        },

        update: function() {
            const $cards = $('.movie-card');
            const cardWidth = this.getCardWidth();
            const containerWidth = $('.movie-carousel').width();

            // Calculate center offset
            const cardCenterPosition = (this.currentSlide * (cardWidth + CarouselConfig.gap)) + (cardWidth / 2);
            const containerCenter = containerWidth / 2;
            const offset = containerCenter - cardCenterPosition;

            // Apply transform
            $('.movie-track').css('transform', 'translateX(' + offset + 'px)');

            // Update center class
            $cards.removeClass('center');
            $cards.eq(this.currentSlide).addClass('center');

            // Update button states
            this.updateButtons();
        },

        updateButtons: function() {
            $('.carousel-prev').prop('disabled', this.currentSlide === 0);
            $('.carousel-next').prop('disabled', this.currentSlide >= this.totalSlides - 1);
        }
    };

    // ========================================
    // MOVIE TABS MODULE
    // ========================================
    const MovieTabs = {
        init: function() {
            $('.movie-tabs a').on('click', function(e) {
                e.preventDefault();
                $('.movie-tabs a').removeClass('active');
                $(this).addClass('active');
            });
        }
    };

    // ========================================
    // SMOOTH SCROLL MODULE
    // ========================================
    const SmoothScroll = {
        init: function() {
            $('a[href^="#"]').on('click', function(e) {
                const target = $(this).attr('href');
                if (target !== '#' && $(target).length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $(target).offset().top - 100
                    }, 800);
                }
            });
        }
    };

    // ========================================
    // SCROLL ANIMATIONS MODULE
    // ========================================
    const ScrollAnimations = {
        init: function() {
            $(window).on('scroll', this.handleScroll);
        },

        handleScroll: function() {
            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();

            $('.feature-card').each(function() {
                const elementTop = $(this).offset().top;
                if (scrollTop + windowHeight > elementTop + 100) {
                    $(this).addClass('fade-in');
                }
            });
        }
    };

    // ========================================
    // INITIALIZATION
    // ========================================
    $(document).ready(function() {
        // Add fade-in to main content
        $('main').addClass('fade-in');

        // Initialize all modules
        MovieCarousel.init();
        MovieTabs.init();
        SmoothScroll.init();
        ScrollAnimations.init();
    });

})(jQuery);
