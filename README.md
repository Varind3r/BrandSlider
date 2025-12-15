# Brand Slider - PrestaShop Module

A beautiful, responsive category slider module for PrestaShop 1.7+

## Features

- ✅ Display selected categories as a sliding carousel
- ✅ Configurable number of visible items (2-8)
- ✅ Autoplay with adjustable speed
- ✅ Navigation arrows (show/hide)
- ✅ Dots pagination (show/hide)
- ✅ Fully responsive design
- ✅ Touch/swipe support for mobile
- ✅ Display on homepage and/or above footer
- ✅ Modern, premium styling with hover effects
- ✅ Child theme override support

## Installation

1. Copy the `brandslider` folder to `/modules/` directory on your PrestaShop site
2. Go to **Back Office > Modules > Module Manager**
3. Search for "Brand Slider" and click **Install**
4. Configure the module settings

## Configuration Options

| Option | Description |
|--------|-------------|
| Categories to Display | Select which categories to show |
| Slider Title | Title displayed above the slider |
| Show Title | Toggle title visibility |
| Items Visible at Once | Number of categories visible (2-8) |
| Slide Transition Speed | Animation speed in milliseconds |
| Enable Autoplay | Auto-slide functionality |
| Autoplay Interval | Time between auto-slides |
| Show Navigation Arrows | Previous/Next buttons |
| Show Dots Pagination | Dot navigation |
| Display on Homepage | Enable homepage display |
| Display Above Footer | Enable footer display (all pages) |

## Child Theme Override

To customize the template in your child theme, copy:

```
modules/brandslider/views/templates/hook/displayHome.tpl
```

To:

```
themes/YOUR_CHILD_THEME/modules/brandslider/views/templates/hook/displayHome.tpl
```

## File Structure

```
brandslider/
├── brandslider.php              # Main module file
├── config.xml                   # Module configuration
├── logo.png                     # Module logo (create 32x32 image)
├── README.md                    # This file
└── views/
    ├── css/
    │   └── brandslider.css      # Slider styles
    ├── js/
    │   └── brandslider.js       # Carousel JavaScript
    └── templates/
        └── hook/
            └── displayHome.tpl  # Frontend template
```

## Requirements

- PrestaShop 1.7.0.0 or higher
- PHP 7.1 or higher

## Note

Remember to add a `logo.png` file (32x32 pixels) in the module root for the back office icon.

## License

MIT License
