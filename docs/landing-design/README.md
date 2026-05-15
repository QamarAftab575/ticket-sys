# Asira Landing Page

A high-converting landing page for Asira - a modern team project management and collaboration app.

## 🎨 Design System

### Color Palette

```css
Primary:    #2563EB (Blue 600) - Main brand color
Secondary:  #3B82F6 (Blue 500) - Supporting brand color
CTA:        #F97316 (Orange 500) - Call-to-action buttons
Background: #F8FAFC (Slate 50) - Page background
Text:       #1E293B (Slate 800) - Primary text
```

**Gradient Background (Hero):**
```css
background: linear-gradient(135deg, 
  #667eea 0%,   /* Purple-blue */
  #764ba2 25%,  /* Deep purple */
  #f093fb 50%,  /* Pink */
  #4facfe 75%,  /* Cyan */
  #00f2fe 100%  /* Bright cyan */
);
```

### Typography

**Font Family:** Plus Jakarta Sans
- Weights: 300 (Light), 400 (Regular), 500 (Medium), 600 (Semibold), 700 (Bold)
- Google Fonts: [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans)

**Usage:**
- Headings: 600-700 weight
- Body: 400 weight
- Captions/Labels: 500 weight

### Glassmorphism Effects

**Standard Glass (Dark backgrounds):**
```css
.glass {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
```

**Light Glass (Light backgrounds):**
```css
.glass-light {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}
```

**Key Properties:**
- Backdrop blur: 15-20px
- Background opacity: 15-30% (dark) or 80% (light)
- Border: 1px solid with 20-30% white opacity
- Always ensure 4.5:1 text contrast ratio

### Animations

**Floating Animation (Hero elements):**
```css
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}
/* Duration: 6s, easing: ease-in-out */
```

**Gradient Shift (Background):**
```css
@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
/* Duration: 15s, easing: ease */
```

**Micro-interactions:**
- Hover transitions: 200ms (0.2s)
- Scale on hover: 1.05
- Shadow on hover: shadow-xl or shadow-2xl

### Accessibility

✅ **Implemented:**
- `prefers-reduced-motion` media query support
- 4.5:1 minimum text contrast ratio
- Semantic HTML structure
- Focus states on interactive elements
- `cursor-pointer` on all clickable elements

## 🚀 Tech Stack

- **Framework:** Vue 3 (Composition API with `<script setup>` pattern)
- **Styling:** Tailwind CSS (CDN)
- **Icons:** Heroicons (inline SVG)
- **Fonts:** Google Fonts (Plus Jakarta Sans)

## 📁 File Structure

```
landing-page/
├── index.html          # Main HTML file with styles
├── app.js              # Vue 3 application logic
└── README.md           # This file
```

## 🎯 Key Sections

### 1. Navbar
- Fixed position with glassmorphism
- Floating design (top-4, left-4, right-4)
- Transitions to more opaque on scroll
- Responsive mobile menu

### 2. Hero Section
- Animated gradient background
- Floating glass elements
- Clear value proposition
- Dual CTA buttons (primary + secondary)
- Social proof stats in glass cards
- Dashboard preview mockup

### 3. Features Section
- 4 key features in grid layout
- Hover effects with scale and shadow
- Gradient icons matching feature theme
- Interactive state management

### 4. Testimonials Section
- 3 customer testimonials
- 5-star ratings
- Avatar placeholders
- Company logos section

### 5. CTA Section
- Gradient background card
- Dual CTA (trial + demo)
- Floating background elements

### 6. Footer
- 4-column layout
- Brand, Product, Company, Resources
- Social links
- Legal links

## 🎨 Design Principles

### Conversion Focus
- **Hero CTA:** Primary action above the fold
- **Repetition:** CTAs in hero, after features, and dedicated CTA section
- **Social Proof:** Stats and testimonials build trust
- **Clear Value:** "From chaos to organized work" messaging

### Visual Hierarchy
1. Hero headline (largest, gradient accent)
2. Feature titles (bold, clear)
3. Body text (readable, sufficient contrast)
4. Supporting text (muted colors)

### Interaction Design
- Smooth transitions (150-300ms)
- Hover feedback on all interactive elements
- Scale transforms for emphasis (1.05)
- No layout shift on hover (avoid scale on cards in grid)

## 🔧 Customization

### Changing Colors

Edit the Tailwind config in `index.html`:

```javascript
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#2563EB',    // Change this
        secondary: '#3B82F6',  // Change this
        cta: '#F97316',        // Change this
      }
    }
  }
}
```

### Changing Fonts

Replace the Google Fonts link in `index.html`:

```html
<link href="https://fonts.googleapis.com/css2?family=Your+Font:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

Update the CSS:

```css
* {
  font-family: 'Your Font', sans-serif;
}
```

### Adding More Features

In `app.js`, add to the `features` array:

```javascript
{
  id: 5,
  icon: 'M12 4v16m8-8H4', // Heroicons path
  title: 'Your Feature',
  description: 'Feature description',
  color: 'from-indigo-500 to-purple-500'
}
```

## 📱 Responsive Breakpoints

- **Mobile:** < 768px (1 column layouts)
- **Tablet:** 768px - 1024px (2 column layouts)
- **Desktop:** > 1024px (full layouts)

## ⚡ Performance Tips

1. **Replace CDN with local files** for production
2. **Optimize images** - use WebP format
3. **Lazy load** below-the-fold content
4. **Minify** CSS and JS
5. **Use proper image dimensions** to avoid layout shift

## 🚀 Deployment

### Quick Start (Local)

1. Open `index.html` in a browser
2. No build process required (uses CDN)

### Production Build

For production, consider:

1. **Use Vite or Vue CLI** for proper build process
2. **Install dependencies locally:**
   ```bash
   npm install vue@3 tailwindcss
   ```
3. **Optimize assets** (images, fonts)
4. **Enable compression** (gzip/brotli)
5. **Use a CDN** for static assets

## 🎯 Conversion Optimization Checklist

- [x] Clear value proposition in hero
- [x] Primary CTA above the fold
- [x] Social proof (stats + testimonials)
- [x] Feature benefits clearly explained
- [x] Multiple CTA placements
- [x] Trust indicators (ratings, company logos)
- [x] Mobile-responsive design
- [x] Fast loading (minimal dependencies)
- [x] Accessible (WCAG AA compliant)

## 📊 A/B Testing Suggestions

1. **Hero CTA text:** "Start Free Trial" vs "Get Started Free"
2. **Hero headline:** Test different value propositions
3. **CTA colors:** Test orange vs green vs blue
4. **Social proof placement:** Above vs below features
5. **Feature order:** Test different feature priorities

## 🔍 SEO Recommendations

Add to `<head>`:

```html
<meta name="description" content="Asira - Transform team chaos into organized work with powerful task management, Kanban boards, and real-time collaboration.">
<meta property="og:title" content="Asira - From Chaos to Organized Work">
<meta property="og:description" content="Powerful project management for modern teams.">
<meta property="og:image" content="/og-image.jpg">
<meta name="twitter:card" content="summary_large_image">
```

## 📝 License

This is a demo landing page. Customize as needed for your project.

## 🤝 Credits

- **Design System:** UI UX Pro Max
- **Icons:** Heroicons
- **Fonts:** Google Fonts (Plus Jakarta Sans)
- **Framework:** Vue 3
- **Styling:** Tailwind CSS
