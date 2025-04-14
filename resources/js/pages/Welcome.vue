<template>
  <div class="min-h-screen bg-white">
    <Head title="Loan Bram - Streamlined Loan Management" />
    
    <!-- Ultra-Minimalist Navigation -->
    <nav class="fixed w-full z-10 bg-white/90 backdrop-blur-sm">
      <div class="container mx-auto px-6 py-5 flex justify-between items-center">
        <div class="flex items-center">
          <a href="/" class="flex items-center">
            <img src="/newlogo.png" alt="Loan Bram Logo" class="h-10 w-10 object-contain" />
            <img src="/loanbram-black.png" alt="Loan Bram" class="h-10 ml-2 object-contain" />
          </a>
        </div>
        <div class="flex items-center space-x-8">
          <a href="/login" class="text-gray-700 hover:text-black transition-colors duration-300 text-sm nav-item">Login</a>
          <a href="/register" class="px-6 py-2.5 bg-black text-white text-sm rounded-full hover:bg-gray-800 transition-all duration-300 nav-item button-animation">Register</a>
        </div>
      </div>
            </nav>

    <!-- Minimalist Hero Section -->
    <section class="relative pt-36 pb-24">
      <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div class="space-y-8 max-w-xl animate-fade-in-up">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-light leading-tight">
              <span class="font-normal animate-slide-in-left">Simple loans.</span>
              <span class="block font-semibold text-gray-900 mt-2 animate-slide-in-left" style="animation-delay: 0.2s;">Better management.</span>
            </h1>
            
            <p class="text-lg text-gray-600 leading-relaxed animate-fade-in" style="animation-delay: 0.4s;">
              Streamline your entire loan process from application to disbursement with a clean, intuitive platform.
            </p>
            
            <div class="pt-4 animate-fade-in" style="animation-delay: 0.6s;">
              <a href="/register" class="px-8 py-3.5 bg-black text-white rounded-full text-sm font-medium hover:bg-gray-900 transition-all duration-300 inline-block button-animation">
                Get Started
              </a>
            </div>
          </div>
          
          <div class="relative h-[400px] md:h-[500px] rounded-xl overflow-hidden shadow-xl animate-slide-in-right">
            <video src="/landingpage.mp4" class="object-cover w-full h-full" autoplay loop muted playsinline onerror="this.src='https://images.unsplash.com/photo-1618044733300-9472054094ee?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1471&q=80'; this.onerror=null;"></video>
          </div>
        </div>
                </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-gray-50">
      <div class="container mx-auto px-6">
        <h2 class="text-2xl md:text-3xl font-light mb-16 text-center animate-on-scroll">Why choose <span class="font-medium">LoanBram</span>?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
          <div v-for="(feature, index) in features" :key="index" 
               class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 animate-on-scroll"
               :style="`animation-delay: ${index * 0.2}s`">
            <div class="text-black mb-5 icon-animation">
              <svg v-if="feature.iconName === 'lightning'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
              <svg v-if="feature.iconName === 'chart'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
              <svg v-if="feature.iconName === 'shield'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
            </div>
            <h3 class="text-lg font-medium mb-3 text-gray-900">{{ feature.title }}</h3>
            <p class="text-gray-600 leading-relaxed text-sm">
              {{ feature.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <!-- <section class="py-24 bg-white">
      <div class="container mx-auto px-6">
        <h2 class="text-2xl md:text-3xl font-light mb-16 text-center animate-on-scroll">What our customers say</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
          <div class="p-6 bg-gray-50 rounded-xl animate-on-scroll" style="animation-delay: 0.1s">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-gray-300 rounded-full overflow-hidden">
                <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600"></div>
              </div>
              <div class="ml-4">
                <h4 class="font-medium text-gray-900">Sarah Johnson</h4>
                <p class="text-sm text-gray-500">Small Business Owner</p>
              </div>
            </div>
            <p class="text-gray-600">"Loan Bram has transformed how we manage our business loans. The interface is intuitive and the reporting tools are incredibly helpful."</p>
          </div>
          
          <div class="p-6 bg-gray-50 rounded-xl animate-on-scroll" style="animation-delay: 0.3s">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-gray-300 rounded-full overflow-hidden">
                <div class="w-full h-full bg-gradient-to-br from-green-400 to-green-600"></div>
              </div>
              <div class="ml-4">
                <h4 class="font-medium text-gray-900">Michael Chen</h4>
                <p class="text-sm text-gray-500">Financial Analyst</p>
              </div>
            </div>
            <p class="text-gray-600">"The analytics provided by Loan Bram give us insights we never had before. It's made our decision-making process much more data-driven."</p>
          </div>
          
          <div class="p-6 bg-gray-50 rounded-xl animate-on-scroll" style="animation-delay: 0.5s">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-gray-300 rounded-full overflow-hidden">
                <div class="w-full h-full bg-gradient-to-br from-purple-400 to-purple-600"></div>
              </div>
              <div class="ml-4">
                <h4 class="font-medium text-gray-900">Emma Rodriguez</h4>
                <p class="text-sm text-gray-500">Loan Officer</p>
              </div>
            </div>
            <p class="text-gray-600">"As someone who processes loans daily, I can't imagine going back to our old system. Loan Bram has streamlined everything."</p>
          </div>
        </div>
      </div>
    </section> -->

    <!-- Minimal CTA -->
    <section class="py-24 bg-black text-white">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-2xl md:text-3xl font-light mb-6 animate-on-scroll">Ready to simplify your loan management?</h2>
        <a href="/register" class="inline-block px-8 py-3.5 bg-white text-black rounded-full text-sm font-medium hover:bg-gray-100 transition-all duration-300 mt-6 animate-on-scroll button-animation" style="animation-delay: 0.2s;">
          Create Account
        </a>
      </div>
    </section>

    <!-- Ultra-Minimal Footer -->
    <footer class="py-12 bg-white border-t border-gray-100">
      <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="mb-6 md:mb-0 animate-on-scroll">
            <div class="flex items-center justify-center md:justify-start">
              <img src="/loanbram-black.png" alt="Loan Bram" class="h-9 ml-2 object-contain" />
            </div>
            <p class="text-gray-500 text-sm mt-2">© 2025 All rights reserved.</p>
          </div>
          <div class="flex space-x-8 animate-on-scroll" style="animation-delay: 0.2s;">
            <a href="#" class="text-gray-500 hover:text-black transition-colors duration-300 text-sm footer-link">Terms</a>
            <a href="#" class="text-gray-500 hover:text-black transition-colors duration-300 text-sm footer-link">Privacy</a>
            <a href="#" class="text-gray-500 hover:text-black transition-colors duration-300 text-sm footer-link">Contact</a>
                </div>
        </div>
      </div>
    </footer>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';

export default {
  name: 'Welcome',
  layout: null,
  components: {
    Head
  },
  data() {
    return {
      features: [
        {
          title: 'Streamlined Processing',
          description: 'Automate your entire loan workflow with an intuitive interface designed for simplicity and efficiency.',
          iconName: 'lightning'
        },
        {
          title: 'Advanced Analytics',
          description: 'Make data-driven decisions with clear insights and comprehensive performance metrics at a glance.',
          iconName: 'chart'
        },
        {
          title: 'Enterprise Security',
          description: 'Keep your sensitive data protected with bank-level security protocols and compliance tools.',
          iconName: 'shield'
        }
      ]
    }
  },
  mounted() {
    // Apply animations to all elements immediately that are above the fold
    document.querySelectorAll('.animate-fade-in, .animate-fade-in-up, .animate-slide-in-left, .animate-slide-in-right').forEach(el => {
      el.style.opacity = '1';
      el.style.transform = 'translateY(0) translateX(0)';
    });
    
    // Use Intersection Observer to trigger animations when elements enter viewport
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1 });
      
      document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
      });
    } else {
      // Fallback for browsers that don't support Intersection Observer
      document.querySelectorAll('.animate-on-scroll').forEach(el => {
        el.classList.add('visible');
      });
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

body {
  font-family: 'Inter', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.container {
  max-width: 1200px;
}

/* Above-the-fold animations */
.animate-fade-in {
  opacity: 1; /* Start visible */
  animation: fadeIn 0.8s ease-out;
}

.animate-fade-in-up {
  opacity: 1; /* Start visible */
  animation: fadeInUp 0.8s ease-out;
}

.animate-slide-in-left {
  opacity: 1; /* Start visible */
  animation: slideInLeft 0.8s ease-out;
}

.animate-slide-in-right {
  opacity: 1; /* Start visible */
  animation: slideInRight 0.8s ease-out;
}

/* Scroll-triggered animations */
.animate-on-scroll {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.8s ease, transform 0.8s ease;
}

.animate-on-scroll.visible {
  opacity: 1;
  transform: translateY(0);
}

/* Specific Animations */
.button-animation {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.button-animation:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.icon-animation {
  transition: transform 0.3s ease;
}

.feature-card:hover .icon-animation {
  transform: scale(1.1);
}

.logo-animation {
  position: relative;
  display: inline-block;
}

.logo-animation::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background-color: #000;
  transition: width 0.4s ease;
}

.logo-animation:hover::after {
  width: 100%;
}

.nav-item {
  position: relative;
}

.nav-item::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 1px;
  background-color: #000;
  transition: width 0.3s ease;
}

.nav-item:hover::after {
  width: 100%;
}

.footer-link {
  transition: transform 0.3s ease;
}

.footer-link:hover {
  transform: translateY(-2px);
}

@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

@keyframes fadeInUp {
  0% { 
    opacity: 0;
    transform: translateY(20px);
  }
  100% { 
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInLeft {
  0% { 
    opacity: 0;
    transform: translateX(-20px);
  }
  100% { 
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInRight {
  0% { 
    opacity: 0;
    transform: translateX(20px);
  }
  100% { 
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
