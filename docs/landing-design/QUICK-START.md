# Quick Start Guide

## 🚀 Get Started in 30 Seconds

### Option 1: Open Directly (Fastest)

1. Open `index.html` in your browser
2. That's it! No installation needed.

### Option 2: Local Server (Recommended)

```bash
# Using Python
python -m http.server 8000

# Using Node.js
npx serve

# Using PHP
php -S localhost:8000
```

Then open: `http://localhost:8000`

## 🎨 Customization Guide

### Change Brand Colors (5 minutes)

Edit `index.html`, find the Tailwind config:

```javascript
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#2563EB',    // Your primary color
        secondary: '#3B82F6',  // Your secondary color
        cta: '#F97316',        // Your CTA button color
      }
    }
  }
}
```

**Popular color schemes:**

```javascript
// Purple theme (Notion-style)
primary: '#8B5CF6',
secondary: '#A78BFA',
cta: '#EC4899',

// Green theme (Productivity)
primary: '#10B981',
secondary: '#34D399',
cta: '#F59E0B',

// Dark blue theme (Professional)
primary: '#1E40AF',
secondary: '#3B82F6',
cta: '#EF4444',
```

### Change Company Name (2 minutes)

In `app.js`, search and replace "Asira" with your company name.

### Change Headline (1 minute)

In `app.js`, find:

```javascript
<h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight">
  From Chaos to<br />
  <span class="bg-gradient-to-r from-yellow-200 via-pink-200 to-blue-200 bg-clip-text text-transparent">
    Organized Work
  </span>
</h1>
```

Replace with your value proposition.

### Add Your Logo (3 minutes)

Replace the lightning bolt icon in the navbar:

```javascript
<!-- Current: Lightning bolt -->
<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
</svg>

<!-- Replace with: -->
<img src="your-logo.svg" alt="Your Company" class="w-5 h-5" />
```

### Change Features (10 minutes)

In `app.js`, edit the `features` array:

```javascript
const features = [
  {
    id: 1,
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    title: 'Your Feature Title',
    description: 'Your feature description here.',
    color: 'from-blue-500 to-cyan-500'
  },
  // Add more features...
];
```

**Find Heroicons paths:** https://heroicons.com/

### Update Testimonials (5 minutes)

In `app.js`, edit the `testimonials` array:

```javascript
const testimonials = [
  {
    id: 1,
    name: 'Customer Name',
    role: 'Job Title at Company',
    avatar: 'CN', // Initials
    content: 'Your customer testimonial quote here.',
    rating: 5
  },
  // Add more testimonials...
];
```

## 🎯 Common Customizations

### Add a Pricing Section

Add after the features section in `app.js`:

```javascript
<!-- Pricing Section -->
<section id="pricing" class="py-24 bg-white">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center mb-16">
      <h2 class="text-4xl md:text-5xl font-bold text-text mb-6">
        Simple, transparent pricing
      </h2>
    </div>
    
    <div class="grid md:grid-cols-3 gap-8">
      <!-- Pricing cards here -->
    </div>
  </div>
</section>
```

### Add Email Capture Form

Replace the CTA button with a form:

```javascript
<form class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
  <input 
    type="email" 
    placeholder="Enter your email" 
    class="flex-1 px-6 py-4 rounded-xl border-2 border-white/30 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:border-white"
  />
  <button 
    type="submit"
    class="px-8 py-4 bg-white text-primary rounded-xl hover:shadow-2xl hover:scale-105 transition-smooth cursor-pointer font-semibold"
  >
    Start Free Trial
  </button>
</form>
```

### Change Font

1. Go to [Google Fonts](https://fonts.google.com/)
2. Select your font and weights
3. Copy the `<link>` tag
4. Replace in `index.html`:

```html
<!-- Replace this -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- With your font -->
<link href="https://fonts.googleapis.com/css2?family=Your+Font:wght@400;600;700&display=swap" rel="stylesheet">
```

5. Update CSS:

```css
* {
  font-family: 'Your Font', sans-serif;
}
```

### Adjust Glassmorphism Intensity

In `index.html`, find the `.glass` class:

```css
/* More transparent (subtle) */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

/* More opaque (stronger) */
.glass {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(25px);
}
```

### Change Gradient Colors

In `index.html`, find `.gradient-bg`:

```css
/* Current: Purple to cyan */
background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);

/* Blue theme */
background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);

/* Warm theme */
background: linear-gradient(135deg, #dc2626 0%, #f97316 50%, #fbbf24 100%);

/* Green theme */
background: linear-gradient(135deg, #065f46 0%, #10b981 50%, #34d399 100%);
```

## 🔧 Troubleshooting

### Fonts not loading?

Check your internet connection. Google Fonts requires internet access.

**Solution:** Download fonts locally:
1. Download from Google Fonts
2. Add to project folder
3. Update CSS:

```css
@font-face {
  font-family: 'Plus Jakarta Sans';
  src: url('./fonts/PlusJakartaSans-Regular.woff2') format('woff2');
}
```

### Glassmorphism not working?

Check browser support. Backdrop-filter requires modern browsers.

**Fallback:**
```css
.glass {
  background: rgba(255, 255, 255, 0.9); /* Solid fallback */
  backdrop-filter: blur(15px);
}
```

### Animations too slow/fast?

Adjust animation duration in `index.html`:

```css
/* Gradient animation */
animation: gradientShift 15s ease infinite; /* Change 15s */

/* Floating animation */
animation: float 6s ease-in-out infinite; /* Change 6s */
```

### Mobile menu not working?

The demo uses a placeholder button. Implement functionality:

```javascript
const showMobileMenu = ref(false);

// In template:
<button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 cursor-pointer">
  <!-- Icon -->
</button>

<div v-if="showMobileMenu" class="md:hidden">
  <!-- Mobile menu items -->
</div>
```

## 📱 Testing Checklist

Before launching:

- [ ] Test on Chrome, Firefox, Safari
- [ ] Test on mobile (iOS and Android)
- [ ] Check all links work
- [ ] Verify forms submit correctly
- [ ] Test with slow internet connection
- [ ] Check accessibility (keyboard navigation)
- [ ] Verify colors have sufficient contrast
- [ ] Test with reduced motion enabled
- [ ] Check responsive breakpoints (375px, 768px, 1024px, 1440px)
- [ ] Validate HTML (https://validator.w3.org/)

## 🚀 Deployment

### Deploy to Netlify (Free)

1. Create account at [netlify.com](https://netlify.com)
2. Drag and drop the `landing-page` folder
3. Done! Your site is live.

### Deploy to Vercel (Free)

1. Create account at [vercel.com](https://vercel.com)
2. Install Vercel CLI: `npm i -g vercel`
3. Run: `vercel`
4. Follow prompts

### Deploy to GitHub Pages (Free)

1. Create GitHub repository
2. Push your code
3. Go to Settings → Pages
4. Select branch and folder
5. Save

## 💡 Pro Tips

1. **Use real screenshots:** Replace placeholder images with actual product screenshots
2. **Add video:** Replace "Watch Demo" with actual demo video
3. **Implement analytics:** Add Google Analytics or Mixpanel
4. **A/B test headlines:** Try different value propositions
5. **Collect emails:** Integrate with Mailchimp or ConvertKit
6. **Add live chat:** Consider Intercom or Crisp
7. **Optimize images:** Use WebP format, compress images
8. **Add meta tags:** Improve SEO and social sharing
9. **Set up redirects:** Handle 404s gracefully
10. **Monitor performance:** Use Lighthouse for audits

## 📚 Resources

- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Vue 3 Docs](https://vuejs.org/)
- [Heroicons](https://heroicons.com/)
- [Google Fonts](https://fonts.google.com/)
- [Can I Use](https://caniuse.com/) - Check browser support
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

## 🆘 Need Help?

Common issues and solutions:

**Q: How do I change the animation speed?**
A: Edit the animation duration in the CSS (e.g., `15s` → `10s`)

**Q: Can I use this for commercial projects?**
A: Yes! Customize as needed.

**Q: How do I add more sections?**
A: Copy an existing section in `app.js` and modify the content.

**Q: The gradient looks different on my screen**
A: Color rendering varies by display. Test on multiple devices.

**Q: How do I make the navbar solid instead of glass?**
A: Change `glass` class to `bg-white shadow-lg`

## 🎉 You're Ready!

Your landing page is ready to customize and deploy. Start with the brand colors and company name, then work your way through the content.

Good luck! 🚀
