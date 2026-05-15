# Asira Visual Style Guide

## 🎨 Color System

### Primary Colors
```css
--primary: #2563EB;      /* Blue 600 - Main brand */
--secondary: #3B82F6;    /* Blue 500 - Supporting */
--cta: #F97316;          /* Orange 500 - Actions */
```

### Neutral Colors
```css
--background: #F8FAFC;   /* Slate 50 - Page bg */
--text: #1E293B;         /* Slate 800 - Primary text */
--text-muted: #64748B;   /* Slate 500 - Secondary text */
--border: #E2E8F0;       /* Slate 200 - Borders */
```

### Gradient Backgrounds
```css
/* Hero gradient */
background: linear-gradient(135deg, 
  #667eea 0%, #764ba2 25%, #f093fb 50%, 
  #4facfe 75%, #00f2fe 100%);

/* Button gradient */
background: linear-gradient(to right, #2563EB, #3B82F6);
```

## 📝 Typography Scale

### Font Family
```css
font-family: 'Plus Jakarta Sans', sans-serif;
```

### Size Scale
```css
--text-xs: 0.75rem;    /* 12px */
--text-sm: 0.875rem;   /* 14px */
--text-base: 1rem;     /* 16px */
--text-lg: 1.125rem;   /* 18px */
--text-xl: 1.25rem;    /* 20px */
--text-2xl: 1.5rem;    /* 24px */
--text-4xl: 2.25rem;   /* 36px */
--text-5xl: 3rem;      /* 48px */
--text-7xl: 4.5rem;    /* 72px */
```

### Weight Scale
```css
--font-light: 300;
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
```

## ✨ Glassmorphism Effects

### Standard Glass (Dark BG)
```css
.glass {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
```

### Light Glass (Light BG)
```css
.glass-light {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}
```

## 🎬 Animations

### Floating Animation
```css
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

.float {
  animation: float 6s ease-in-out infinite;
}
```

### Gradient Shift
```css
@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.gradient-bg {
  background-size: 400% 400%;
  animation: gradientShift 15s ease infinite;
}
```

### Smooth Transitions
```css
.transition-smooth {
  transition: all 0.2s ease;
}
```

## 📐 Spacing Scale

```css
--space-1: 0.25rem;   /* 4px */
--space-2: 0.5rem;    /* 8px */
--space-4: 1rem;      /* 16px */
--space-6: 1.5rem;    /* 24px */
--space-8: 2rem;      /* 32px */
--space-12: 3rem;     /* 48px */
--space-16: 4rem;     /* 64px */
--space-24: 6rem;     /* 96px */
```

## 🔘 Button Styles

### Primary Button
```css
.btn-primary {
  padding: 1rem 2rem;
  background: linear-gradient(to right, #2563EB, #3B82F6);
  color: white;
  border-radius: 0.75rem;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  transform: scale(1.05);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
```

### Secondary Button
```css
.btn-secondary {
  padding: 1rem 2rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(15px);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.75rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.25);
}
```

## 🎴 Card Styles

### Feature Card
```css
.feature-card {
  padding: 2rem;
  border: 2px solid #E2E8F0;
  border-radius: 1rem;
  transition: all 0.2s ease;
  cursor: pointer;
}

.feature-card:hover {
  border-color: #2563EB;
  background: rgba(37, 99, 235, 0.05);
  transform: scale(1.05);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
```

### Testimonial Card
```css
.testimonial-card {
  padding: 2rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.testimonial-card:hover {
  transform: scale(1.05);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
```

## 🎯 Icon Styles

### Gradient Icon Container
```css
.icon-gradient {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.icon-gradient.blue {
  background: linear-gradient(to bottom right, #3B82F6, #06B6D4);
}

.icon-gradient:hover {
  transform: scale(1.1);
}
```

## ♿ Accessibility

### Focus States
```css
*:focus {
  outline: 2px solid #2563EB;
  outline-offset: 2px;
}
```

### Reduced Motion
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

### Minimum Touch Targets
```css
.touch-target {
  min-width: 44px;
  min-height: 44px;
}
```

## 📱 Responsive Utilities

### Breakpoints
```css
/* Mobile first approach */
@media (min-width: 768px) { /* Tablet */ }
@media (min-width: 1024px) { /* Desktop */ }
@media (min-width: 1440px) { /* Large desktop */ }
```
