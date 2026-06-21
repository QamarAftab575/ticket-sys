# Admin Dashboard - Visual Design Guide

## Color Palette

### Primary Colors
```
Blue Primary        #2563EB    rgb(37, 99, 235)      Used for: Main CTAs, active states
Blue Secondary      #3B82F6    rgb(59, 130, 246)     Used for: Secondary elements
```

### Semantic Colors
```
Success/Growth      #10B981    rgb(16, 185, 129)     Used for: Positive metrics, growth
Warning/Caution     #EA580C    rgb(234, 88, 12)      Used for: Warnings, attention
Secondary          #8B5CF6    rgb(139, 92, 246)     Used for: Secondary workspaces
```

### Background Palette
```
Page Background     #F8FAFC    rgb(248, 250, 252)    Slate-50
Card Background     #FFFFFF    rgb(255, 255, 255)    White
Hover Background    #F1F5F9    rgb(241, 245, 249)    Slate-100
Header Background   #F9FAFB    rgb(249, 250, 251)    Slate-50
```

### Text Colors
```
Primary Text       #0F172A    rgb(15, 23, 42)       Slate-900 - Headlines, important text
Secondary Text     #475569    rgb(71, 85, 105)      Slate-600 - Body text
Tertiary Text      #64748B    rgb(100, 116, 139)    Slate-500 - Secondary info
Muted Text         #94A3B8    rgb(148, 163, 184)    Slate-400 - Hints, labels

Border Colors
Subtle Border      #E2E8F0    rgb(226, 232, 240)    Slate-200 - Main borders
Minimal Border     #F1F5F9    rgb(241, 245, 249)    Slate-100 - Dividers
```

### Icon Background Colors
```
Blue Icon BG       #EFF6FF → #DBEAFE    from-blue-50 to-blue-100
Purple Icon BG     #FAF5FF → #F3E8FF    from-purple-50 to-purple-100
Emerald Icon BG    #F0FDF4 → #DCFCE7    from-emerald-50 to-emerald-100
Orange Icon BG     #FFF7ED → #FFEDD5    from-orange-50 to-orange-100
```

---

## Typography Scale

### Headings
```
Page Title          48px (text-4xl)     font-bold       #0F172A
Section Title       18px (text-lg)      font-semibold   #0F172A
Card Label          14px (text-sm)      font-medium     #64748B
```

### Body Text
```
Primary Body        16px                font-normal     #0F172A
Secondary Body      14px (text-sm)      font-normal     #475569
Caption             12px (text-xs)      font-normal     #64748B
```

### Numbers
```
Large KPI           36px (text-4xl)     font-bold       #0F172A
Medium KPI          24px (text-3xl)     font-bold       #0F172A
Small KPI           20px (text-2xl)     font-bold       #0F172A
```

---

## Component Styles

### KPI Cards (Primary Metrics)

**Structure:**
```
┌─────────────────────────────────┐
│ ╭─────────╮                      │
│ │ [ICON]  │                      │
│ ╰─────────╯                      │
│                                  │
│ Label Text                       │
│ 1,234,567                        │
│ +123 today                       │
└─────────────────────────────────┘
```

**Sizing:**
- Padding: 32px (p-8)
- Icon box: 48px × 48px (w-12 h-12)
- Icon radius: 12px (rounded-xl)
- Card radius: 20-24px (rounded-2xl)

**Spacing:**
- Icon to bottom: 24px (mb-6)
- Label to number: 4px (mb-1)
- Number to trend: 12px (mb-3)

**Shadow:**
- Default: Subtle shadow
- Hover: Medium shadow
- Duration: 200ms

---

### Secondary Metrics (Smaller Cards)

**Structure:**
```
┌──────────────────────┐
│ Label                │
│ 456                  │
│ Supporting text      │
└──────────────────────┘
```

**Sizing:**
- Padding: 24px (p-6)
- Card radius: 20-24px (rounded-2xl)

**Spacing:**
- Label to number: 12px (mb-3)
- Number to support: 4px (mt-1)

---

### Icon Styling

**Small Icons** (Inline, inline-flex)
- Size: 16px × 16px (w-4 h-4)
- Color: Emerald-600 (growth indicators)
- Stroke width: 2

**Medium Icons** (Card headers)
- Size: 24px × 24px (w-6 h-6)
- Color: Brand color (blue-600, purple-600, etc)
- Stroke width: 1.5

**Large Icons** (Performance indicators)
- Size: 20px × 20px (w-5 h-5)
- Color: Brand color (emerald-600, orange-600, etc)
- Background: 40px × 40px (w-10 h-10) rounded-lg

---

### Table Styling

**Header Row:**
```
Background: #F9FAFB (slate-50)
Border-bottom: 1px #E2E8F0 (slate-200)
Padding: 16px 32px (py-4 px-8)
Font: 12px uppercase semibold #64748B
```

**Data Rows:**
```
Background: #FFFFFF (white)
Border-bottom: 1px #F1F5F9 (slate-100)
Padding: 16px 32px (py-4 px-8)
Hover: #F9FAFB (slate-50) with smooth transition
```

**Avatar:**
```
Size: 32px × 32px (w-8 h-8)
Radius: 8px (rounded-lg)
Background: Gradient (blue to purple)
Text: White, 12px, semibold
```

**Status Badge:**
```
Padding: 6px 10px (px-2.5 py-1.5)
Radius: 8px (rounded-lg)
Font: 12px semibold
Background: #F0FDF4 (emerald-50)
Text: #047857 (emerald-700)
Icon: 12px (w-3 h-3)
```

---

### Chart Styling

**Line Chart:**
```
Line Color:        #2563EB (blue-600)
Fill Color:        rgba(37, 99, 235, 0.05)  5% opacity
Border Width:      2px
Grid Color:        rgba(148, 163, 184, 0.08)  8% opacity
Point Radius:      0 (hidden by default)
Point on Hover:    6px, blue-600
```

**Bar Chart:**
```
Bar Color:         #2563EB (blue-600)
Border Radius:     6px
Border:            None
Grid Color:        rgba(148, 163, 184, 0.08)
Spacing:           Automatic
```

---

### Navigation Bar

**Structure:**
```
┌──────────────────────────────────────┐
│ Admin  Dashboard Users ...    [Avatar]│
└──────────────────────────────────────┘
```

**Styling:**
- Height: 64px (h-16)
- Background: White
- Border-bottom: 1px #E2E8F0 (slate-200)
- Position: Sticky top-0 z-50
- Backdrop: blur-xl with 95% opacity

**Links:**
- Padding: 8px 12px (px-3 py-2)
- Radius: 8px (rounded-lg)
- Active: #0F172A text, #F1F5F9 background
- Hover: #0F172A text, #F9FAFB background
- Transition: 200ms

**Avatar:**
- Size: 32px × 32px (w-8 h-8)
- Radius: 8px (rounded-lg)
- Gradient: Blue to purple
- Text: White, bold, 12px (text-xs)

---

## Spacing System

### Vertical Spacing
```
Between Major Sections:    32px (space-y-8)
Between Card Rows:         24px (gap-6)
Between Elements in Card:  12-16px (mb-3, mt-2, mb-1)
Section Padding Top/Bottom: 32px (py-8)
Header Padding:            20px (py-5)
```

### Horizontal Spacing
```
Container Padding:         24px on mobile, 32px on desktop (px-6 lg:px-8)
Card Padding:              32px (p-8) primary, 24px (p-6) secondary
Element Spacing:           8-16px (gap-2, gap-3, gap-4)
Icon to Text:              12px (gap-3)
Table Cell Padding:        32px horizontal (px-8)
```

---

## Shadow System

### Shadow Definitions
```
shadow-sm     0px 1px 2px rgba(0, 0, 0, 0.05)
shadow-md     0px 4px 6px rgba(0, 0, 0, 0.1)
shadow-lg     0px 10px 15px rgba(0, 0, 0, 0.1)
```

### Usage
```
Cards Default:             shadow-sm
Cards on Hover:            shadow-md
Action Cards on Hover:     shadow-lg
Charts:                    shadow-sm
Tables:                    shadow-sm
Dropdowns:                 shadow-lg
```

---

## Responsive Behavior

### Grid Layouts

**Primary KPIs:**
```
Mobile (< 640px):   1 column    grid-cols-1
Tablet (640px+):    2 columns   sm:grid-cols-2
Desktop (1024px+):  4 columns   lg:grid-cols-4
```

**Charts:**
```
Mobile (< 1024px):  1 column    grid-cols-1
Desktop (1024px+):  2 columns   lg:grid-cols-2
```

**Actions:**
```
Mobile (< 768px):   1 column    grid-cols-1
Tablet (768px+):    2 columns   md:grid-cols-2
```

### Font Scaling
```
Page Title:         Always text-4xl (responsive via clamp internally)
Section Title:      Always text-lg
Body Text:          Always text-sm to base
Numbers:            Consistent sizes across breakpoints
```

### Padding Scaling
```
Mobile:             px-6 (24px)
Desktop:            lg:px-8 (32px)
Consistent:         Content always has proper breathing room
```

---

## Animation & Transitions

### Transition Timing
```
Shadow Changes:     duration-200 (200ms)
Color Changes:      duration-200 (200ms)
Scale Changes:      duration-200 (200ms)
Easing:             ease-in-out (default)
```

### Hover Effects
```
Cards:              shadow-sm → shadow-md
Action Cards:       Scale 1.0 → 1.05 (scale-105)
Links:              Color change with 200ms
Buttons:            Scale + shadow changes
```

---

## Accessibility Considerations

### Color Contrast
```
Text on White:      #0F172A on #FFFFFF = 21:1 (AAA)
Text on Gray:       #0F172A on #F8FAFC = 19:1 (AAA)
Muted on White:     #94A3B8 on #FFFFFF = 4.6:1 (AA)
```

### Focus States
```
Navigation Links:   2px ring-slate-200
Form Elements:      2px ring-blue-500
Buttons:            Outline with brand color
```

### Icon Meaning
```
All icons have SVG-based visual meaning
No emojis used
Icons paired with text labels where needed
```

---

## Implementation Examples

### KPI Card HTML Structure
```html
<div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow">
  <div class="flex items-start justify-between mb-6">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100">
      <!-- Icon goes here -->
    </div>
  </div>
  <p class="text-slate-500 text-sm font-medium mb-1">Label</p>
  <p class="text-4xl font-bold text-slate-900 mb-3">1,234</p>
  <div class="flex items-center gap-2">
    <!-- Trend indicator -->
  </div>
</div>
```

### Card Grid
```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
  <!-- Cards -->
</div>
```

### Modern Table
```html
<div class="bg-white rounded-2xl overflow-hidden">
  <table class="w-full">
    <thead class="bg-slate-50 border-b border-slate-100">
      <tr>
        <th class="px-8 py-4 text-xs font-semibold text-slate-600">Column</th>
      </tr>
    </thead>
    <tbody>
      <tr class="border-b border-slate-100 hover:bg-slate-50">
        <td class="px-8 py-4 text-slate-900">Content</td>
      </tr>
    </tbody>
  </table>
</div>
```

---

## Design Tokens Summary

```
Colors:           14 (blues, purples, emeralds, oranges, slates)
Spacing:          7 (0, 4, 8, 12, 16, 24, 32px)
Typography:       3 scales (text-xs to text-4xl)
Shadows:          3 levels (sm, md, lg)
Radius:           4 values (4px, 8px, 12px, 20-24px)
Transitions:      1 timing (duration-200)
Breakpoints:      4 major (mobile, sm, md, lg)
```

This guide ensures consistent, professional design across the admin dashboard.
