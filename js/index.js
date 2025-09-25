/**
 * Samridhi Book Dress - Index Page JavaScript
 * Handles animations and interactive elements
 */

document.addEventListener('DOMContentLoaded', function() {
  // Function to check if elements are in viewport and animate them
  function checkScroll() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    elements.forEach(element => {
      const position = element.getBoundingClientRect();
      
      // Check if element is in viewport
      if(position.top < window.innerHeight * 0.9) {
        element.classList.add('is-visible');
      }
    });
  }
  
  // Run once on page load
  checkScroll();
  
  // Listen for scroll events
  window.addEventListener('scroll', checkScroll);
  
  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      if(this.getAttribute('href') !== "#" && this.getAttribute('href') !== "#carouselExampleIndicators") {
        e.preventDefault();
        
        const target = document.querySelector(this.getAttribute('href'));
        const navbarHeight = document.querySelector('.navbar').offsetHeight;
        
        if(target) {
          window.scrollTo({
            top: target.offsetTop - navbarHeight,
            behavior: 'smooth'
          });
        }
      }
    });
  });
  
  // Hero carousel animation timing
  const heroCarousel = document.querySelector('#heroCarousel');
  if(heroCarousel) {
    heroCarousel.addEventListener('slide.bs.carousel', function() {
      const activeSlide = this.querySelector('.carousel-item.active');
      
      if(activeSlide) {
        // Remove animation classes
        activeSlide.querySelector('.carousel-title').classList.remove('animate-fade-in');
        activeSlide.querySelector('.carousel-subtitle').classList.remove('animate-fade-in');
      }
    });
    
    heroCarousel.addEventListener('slid.bs.carousel', function() {
      const activeSlide = this.querySelector('.carousel-item.active');
      
      if(activeSlide) {
        // Add animation classes
        activeSlide.querySelector('.carousel-title').classList.add('animate-fade-in');
        activeSlide.querySelector('.carousel-subtitle').classList.add('animate-fade-in');
      }
    });
  }
}); 