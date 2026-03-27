# Text Animation Block

Create incredible text animations using GSAP with character, word, or line-based splitting effects.

## Features

✨ **Multiple Animation Styles**
- Character-by-character animations with 3D rotation
- Word-by-word animations with staggered effects
- Line-by-line animations for complex text

⚙️ **Customizable Settings**
- Adjustable animation duration (0.3s - 2s)
- Configurable stagger delay for sequential effects
- Multiple easing functions (expo.out, power2.out, power3.out, back.out, elastic.out)
- Toggle blur effect for tagline animations

🎨 **Color Control**
- Separate colors for headline initial and animation states
- Independent tagline color customization
- Pre-defined color palette with common choices
- Custom color input support

📐 **Layout Options**
- Left, center, or right text alignment
- Adjustable minimum height (200px - 800px)
- Full-width and wide block support
- Responsive spacing and padding controls

♿ **Accessibility**
- Respects `prefers-reduced-motion` for users who prefer minimal animations
- Supports high contrast mode
- Dark mode compatible
- Semantic HTML structure

📱 **Responsive Design**
- Mobile-first approach
- Responsive typography (clamp-based font sizing)
- Optimized for all device sizes

## Settings Reference

### Text Content
- **Headline**: Main animated heading text
- **Tagline**: Secondary text below headline

### Animation Settings
- **Animation Type**: Choose how text is split (character, word, or line)
- **Duration**: How long the animation takes (in seconds)
- **Stagger Delay**: Time between individual elements animating
- **Easing Function**: Animation curve (expo.out recommended for smooth feel)
- **Enable Blur Effect**: Adds blur-in effect to tagline text

### Colors
- **Headline Color**: Initial color of headline text
- **Headline Animation Color**: Color headlines transition to during animation
- **Tagline Color**: Color of tagline text

### Layout
- **Text Alignment**: Position text (left, center, right)
- **Minimum Height**: Block height in pixels

## Usage Example

```html
<!-- The block renders this structure -->
<div class="wp-block-blockxpert-text-animation">
  <div class="blockxpert-text-animation-wrapper" data-animation-type="characters" data-duration="0.8">
    <div class="headline-wrapper">
      <h1 class="main-headline">Web Developer</h1>
    </div>
    <div class="tagline-wrapper">
      <p class="tagline">Clean code. Smooth design.</p>
    </div>
  </div>
</div>
```

## Animation Flow

1. **Headline animates in** with selected animation type
   - Characters rotate in with Y-axis perspective
   - Words scale and fade in
   - Lines slide up from bottom

2. **Color transition** occurs simultaneously
   - From initial headline color to animation color
   - Smooth power2.out easing

3. **Tagline follows** after headline completes
   - Slides up with optional blur effect
   - Words stagger into view sequentially
   - Blur clears as animation completes

## Technical Details

### GSAP Implementation
- Uses GSAP timeline for sequenced animations
- Custom text-splitting function (no external SplitText plugin needed)
- Hardware-accelerated transforms for smooth performance
- Will-change CSS hints for optimization

### Browser Support
- Modern browsers with GSAP support
- Graceful degradation for older browsers
- IE 11 not supported (uses ES6+)

### Performance Considerations
- Animations use GPU acceleration (transform & perspective)
- Minimal repaints through efficient DOM structure
- Text splitting happens once on page load
- Timeline-based orchestration prevents frame drops

## Customization Tips

### Custom Easing Functions
The block uses standard GSAP easing functions. Add more by editing the easing options in `edit.js`:

```javascript
// Example: Add custom easing
{ label: 'Bounce Out', value: 'bounce.out' }
{ label: 'Back Elastic', value: 'back(2).out' }
```

### Inline Custom Colors
Extend color palette by modifying the colors array in `edit.js`:

```javascript
const colors = [
  { name: 'Custom Purple', color: '#9B59B6' },
  { name: 'Custom Blue', color: '#3498DB' },
];
```

### Animation Timing Adjustment
Modify default stagger and duration in `block.json` attributes:

```json
"duration": { "type": "number", "default": 1.0 },
"stagger": { "type": "number", "default": 0.05 }
```

## Accessibility Notes

The block includes:
- ✅ Keyboard navigation in editor
- ✅ Color contrast compliance for text
- ✅ Respect for `prefers-reduced-motion` system setting
- ✅ Semantic HTML with proper heading hierarchy
- ✅ Alternative text support via attributes

Users with motion sensitivity will see no animations when they enable "Reduce Motion" in system settings.

## Browser Compatibility

| Browser | Support |
|---------|---------|
| Chrome | ✅ Full |
| Firefox | ✅ Full |
| Safari | ✅ Full |
| Edge | ✅ Full |
| IE 11 | ❌ Not supported |
| Opera | ✅ Full |

## Known Limitations

1. **Very long text**: Performance may degrade with 1000+ characters
2. **Rapid block reloads**: May see animation flicker (cache clears on refresh)
3. **Server-side rendering**: Animations only work in DOM after client hydration
4. **Nested blocks**: Cannot nest text-animation blocks within each other

## Troubleshooting

**Animations not playing:**
- Verify GSAP is loaded (check browser console)
- Check that block content is rendered in frontend (not just editor)
- Clear WordPress cache and hard refresh browser

**Text looks cut off:**
- Adjust minimum height setting
- Check parent container width constraints
- Verify headline text isn't excessively long

**Animation choppy:**
- Reduce number of animating elements
- Increase duration value
- Check browser for heavy JavaScript elsewhere

**Colors not applying:**
- Clear cache and refresh
- Verify color format (hex values)
- Check for conflicting CSS from theme

## Version History

- **v1.0.0** - Initial release with character/word/line animations
