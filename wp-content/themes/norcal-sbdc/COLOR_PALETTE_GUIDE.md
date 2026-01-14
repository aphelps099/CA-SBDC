# NorCal SBDC Color Palette Sample Page

## Overview
This guide explains how to use the new color palette sample page template that demonstrates the NorCal SBDC brand colors.

## How to Create the Sample Page in WordPress

1. **Log in to WordPress Admin**
2. **Navigate to Pages → Add New**
3. **Enter a title** (e.g., "Color Palette Sample" or "Brand Colors Demo")
4. **Select the template:**
   - In the right sidebar, find "Page Attributes"
   - Under "Template", select **"Color Palette Sample"**
5. **Publish the page**

The page will automatically display with all the color palette examples and demonstrations.

## Color Palette Reference

### Primary Colors
- **Navy (#0F1C2E)** - 4 parts
  - Role: Primary brand color
  - Usage: Backgrounds, primary text, main logo color
  - Personality: Professional, trustworthy, authoritative

- **Pool (#8FC5D9)** - 2 parts
  - Role: Secondary color for highlights and emphasis
  - Usage: Accent text, highlighted words, tags, secondary elements
  - Personality: Approachable, fresh, forward-thinking

### Accent Color
- **Wine (#8A2432)** - 1 part (use sparingly!)
  - Role: Strategic accent for emphasis and energy
  - Usage: Accent lines, borders, call-to-action elements
  - Personality: Bold, energetic, memorable

### Supporting Colors
- **Cream (#F5F2EB)** - 2 parts
  - Role: Light backgrounds and contrast
  - Usage: Light slide backgrounds, cards, alternate sections
  - Personality: Warm, inviting, sophisticated

- **Black (#0a0a0f)**
  - Role: Deep backgrounds
  - Usage: Dark slide backgrounds, creates depth with vignette effects
  - Personality: Modern, premium, focused

- **White (#ffffff)**
  - Role: Clean contrast
  - Usage: Text on dark backgrounds, logo in light version
  - Personality: Clean, clear, professional

## CSS Classes Available

The page template includes these utility classes:

### Background Colors
- `.navy-bg` - Navy background with white text
- `.pool-bg` - Pool background with navy text
- `.wine-bg` - Wine background with white text
- `.cream-bg` - Cream background with navy text
- `.black-bg` - Black background with white text

### Text Colors
- `.navy-text` - Navy colored text
- `.pool-text` - Pool colored text
- `.wine-text` - Wine colored text

### Special Effects
- `.pool-highlight` - Highlighted text with pool background
- `.wine-accent-line` - Left border accent in wine color
- `.tag-pool` - Pool colored tag/badge
- `.vignette-overlay` - Radial gradient overlay for depth

### Buttons
- `.btn-navy` - Navy solid button
- `.btn-pool` - Pool solid button
- `.btn-wine` - Wine solid button
- `.btn-outline-navy` - Navy outline button

## Design Principles

**"Navy leads. Wine accents. Everything else supports."**

### Color Ratio: 4:2:1:2
- Navy: 4 parts (largest)
- Pool: 2 parts
- Wine: 1 part (smallest, most powerful)
- Cream: 2 parts

This ratio ensures:
- Visual balance
- Professional appearance
- Strategic emphasis
- Appropriate for a 20-year-old established organization

## Examples Included in the Page

The sample page demonstrates:

1. **Color Swatches** - All six colors with descriptions and usage guidelines
2. **Ratio Visualization** - Visual representation of the 4:2:1:2 balance
3. **Hero Section** - Black background with vignette, pool highlights, wine CTA
4. **Service Cards** - Navy headers, cream backgrounds, wine accent lines
5. **Tags** - Pool colored tags for categorization
6. **Statistics** - Different background colors for data display
7. **Callout Boxes** - Cream backgrounds with wine borders
8. **Button Variations** - All button styles demonstrated
9. **Design Principles** - Visual explanation of the color hierarchy

## Using These Colors in Other Pages

To use these colors in regular WordPress pages:

1. **In Block Editor:**
   - Select text or block
   - Use Custom HTML block for specific styling
   - Add inline styles or classes from this template

2. **In Custom Templates:**
   - Copy the CSS variables from the `<style>` section
   - Use the utility classes defined in the template
   - Follow the 4:2:1:2 ratio for balance

3. **CSS Variables (add to your styles):**
   ```css
   :root {
       --navy: #0F1C2E;
       --pool: #8FC5D9;
       --wine: #8A2432;
       --cream: #F5F2EB;
       --black: #0a0a0f;
       --white: #ffffff;
   }
   ```

## Best Practices

1. **Navy Dominance** - Let navy be the primary color in most designs
2. **Wine Sparingly** - Use wine only for important accents and CTAs
3. **Pool for Warmth** - Use pool to make content approachable
4. **Cream for Comfort** - Use cream backgrounds for readable sections
5. **Black for Drama** - Use black backgrounds with vignettes for hero sections

## Integration with Existing Theme

This template is standalone and won't affect existing pages. To integrate these colors into the main theme:

1. Add the CSS variables to `/assets/src/scss/config/_variables.scss`
2. Update `/config.json` color palette
3. Regenerate theme styles with Gulp
4. Update button and alert component styles

## File Location

- **Template:** `/wp-content/themes/norcal-sbdc/page-color-palette.php`
- **This Guide:** `/wp-content/themes/norcal-sbdc/COLOR_PALETTE_GUIDE.md`

---

**Questions or need help?** Contact the web development team or refer to the WordPress admin for page creation assistance.
