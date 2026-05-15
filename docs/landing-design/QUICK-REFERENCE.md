# Landing Page Quick Reference

## 🎯 File Location
```
resources/js/Pages/Landing.vue
```

## 🚀 Quick Start

### View the Page
```bash
php artisan serve
# Visit: http://localhost:8000
```

### Edit Content
Open `resources/js/Pages/Landing.vue` and find:

**Hero Headline:**
```vue
Line ~120: "From Chaos to Organized Work"
```

**Features:**
```vue
Line ~30-60: const features = [...]
```

**Testimonials:**
```vue
Line ~62-85: const testimonials = [...]
```

**Stats:**
```vue
Line ~87-92: const stats = [...]
```

## 🎨 Quick Customizations

### Change Primary Color
Find and replace throughout the file:
```
blue-600 → your-color-600
blue-500 → your-color-500
```

### Update Company Name
Find and replace:
```
"Asira" → "Your Company"
```

### Change Gradient
```vue
Line ~40: background: linear-gradient(135deg, ...)
```

## 📱 Sections Overview

1. **Navbar** (Line ~95) - Fixed, glassmorphism, scroll effect
2. **Hero** (Line ~135) - Gradient background, floating elements, CTAs
3. **Features** (Line ~220) - 4 interactive cards
4. **Testimonials** (Line ~270) - 3 customer stories
5. **CTA** (Line ~330) - Conversion-focused section
6. **Footer** (Line ~370) - Links and copyright

## 🎯 Key Classes

### Glassmorphism
```css
.glass          /* Dark background */
.glass-light    /* Light background */
```

### Animations
```css
.gradient-bg    /* Animated gradient */
.float          /* Floating elements */
.transition-smooth  /* Hover effects */
```

### Responsive
```
md:  /* Tablet (768px+) */
lg:  /* Desktop (1024px+) */
```

## 🔗 Navigation Links

All links use Inertia.js:
```vue
<Link :href="route('login')">Sign In</Link>
<Link :href="route('register')">Get Started</Link>
```

## 📊 Stats to Update

Current placeholder stats:
- 50K+ Active Teams
- 2M+ Projects Completed
- 99.9% Uptime
- 4.9/5 User Rating

Update in the `stats` array (Line ~87)

## 🎨 Color Palette

```
Primary:    #2563EB (blue-600)
Secondary:  #3B82F6 (blue-500)
Text:       #1E293B (slate-800)
Background: #F8FAFC (slate-50)
```

## ⚡ Performance Tips

1. Replace placeholder image with optimized WebP
2. Add lazy loading to images
3. Minify for production: `npm run build`
4. Enable caching in production

## 🐛 Common Issues

**Styles not showing?**
```bash
npm run dev
# or
npm run build
```

**Links not working?**
Check routes exist in `routes/web.php`

**Animations too fast/slow?**
Edit animation duration in `<style>` section

## 📚 Full Documentation

See `docs/landing-design/` for:
- LARAVEL-INTEGRATION.md (this integration)
- README.md (complete design system)
- QUICK-START.md (detailed customization)
- DESIGN-NOTES.md (design decisions)
- STYLE-GUIDE.md (CSS reference)

## ✅ Pre-Launch Checklist

- [ ] Update all text content
- [ ] Replace placeholder image
- [ ] Add real testimonials
- [ ] Update stats
- [ ] Change colors to brand
- [ ] Add logo
- [ ] Test on mobile
- [ ] Test all links
- [ ] Run Lighthouse audit

## 🎉 You're Ready!

The landing page is fully integrated and ready to customize. Start with the hero headline and work your way down!
