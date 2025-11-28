# TMDB Slider

A powerful WordPress plugin that integrates with The Movie Database (TMDb) API to display beautiful, customizable sliders for movies and TV shows. Perfect for entertainment websites, movie blogs, and streaming platforms.

![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.2%2B-blue.svg)
![License](https://img.shields.io/badge/license-GPL%20v2-green.svg)

## 🎬 Features

### Multiple Slider Types
- **Hero Slider**: Full-width backdrop slider for trending content
- **Row Sliders**: Horizontal scrolling poster galleries
- **Sports Slider**: Customizable sports TV shows slider

### Content Categories
- **Movies**: Popular, Top Rated, Now Playing, Trending
- **TV Shows**: Popular, Top Rated, On Air, Trending
- **Sports**: Custom keyword-based TV shows

### Customization Options
- ✅ **Reverse Direction**: Control slider animation direction for each slider
- ✅ **Stop on Hover**: Toggle pause-on-hover functionality per slider
- ✅ **Speed Control**: Adjustable animation speed for hero and row sliders
- ✅ **Poster Width**: Customizable poster image width
- ✅ **Display Options**: Show/hide play icons, ratings, and titles
- ✅ **Clickable Posters**: Enable/disable links to TMDb pages

### Developer-Friendly
- 🚀 **Shortcode-Based**: Easy to use shortcodes for all sliders
- 🎨 **Elementor Compatible**: Works seamlessly with Elementor page builder
- 🔄 **Auto-Updates**: Automatic updates from GitHub releases
- ⚡ **Performance Optimized**: Built-in caching for API responses
- 📱 **Responsive Design**: Mobile-friendly sliders

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- TMDb API Key (free at [themoviedb.org](https://www.themoviedb.org/settings/api))

## 🚀 Installation

### Method 1: Manual Installation

1. Download the latest release from the [Releases](https://github.com/Saber4Dev/tmdb-slider/releases) page
2. Upload the `tmdb-slider` folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **Settings > TMDB Slider** to configure your API key

### Method 2: Git Clone

```bash
cd wp-content/plugins
git clone https://github.com/Saber4Dev/tmdb-slider.git
```

Then activate the plugin through the WordPress admin panel.

## ⚙️ Configuration

### Getting Your TMDb API Key

1. Visit [The Movie Database](https://www.themoviedb.org/)
2. Create a free account
3. Go to [API Settings](https://www.themoviedb.org/settings/api)
4. Request an API key
5. Copy your API key and paste it in **Settings > TMDB Slider**

### Plugin Settings

Navigate to **Settings > TMDB Slider** to configure:

#### API Settings
- **TMDb API Key**: Your API key from themoviedb.org
- **Connection Status**: Verify your API connection

#### Global Slider Settings
- **Row Slider Speed**: Animation duration in seconds (default: 60)
- **Hero Slider Speed**: Animation duration in seconds (default: 50)
- **Poster Width**: Width of poster images in pixels (default: 220)

#### Display Options
- **Show Play Icon**: Display play icon on posters and hero slides
- **Show Rating**: Display TMDb rating
- **Show Names**: Display movie/TV show names under posters
- **Make Poster Clickable**: Enable links to TMDb pages

#### Per-Slider Settings
For each slider (Hero, Popular, Top Rated, Now Playing, Sports):
- **Enable/Disable**: Toggle slider on/off
- **Reverse Direction**: Animate in reverse direction
- **Stop on Hover**: Pause animation on hover

#### Sports Slider Configuration
- **Sports Keyword IDs**: Comma-separated TMDb keyword IDs for sports content

## 📝 Shortcodes

### Global Shortcodes (Movies & TV Shows)

| Shortcode | Description |
|-----------|-------------|
| `[tmdb_hero_slider]` | Backdrop hero slider (trending movies) |
| `[tmdb_popular_slider]` | Popular movies row |
| `[tmdb_top_rated_slider]` | Top rated movies row |
| `[tmdb_now_playing_slider]` | Now playing movies row |
| `[tmdb_sports_slider]` | Sports TV row |

### Movie-Specific Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[tmdb_movie_hero_slider]` | Movie hero slider (trending movies) |
| `[tmdb_movie_popular_slider]` | Popular movies row |
| `[tmdb_movie_top_rated_slider]` | Top rated movies row |
| `[tmdb_movie_now_playing_slider]` | Now playing movies row |

### TV Show-Specific Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[tmdb_tv_hero_slider]` | TV show hero slider (trending TV shows) |
| `[tmdb_tv_popular_slider]` | Popular TV shows row |
| `[tmdb_tv_top_rated_slider]` | Top rated TV shows row |
| `[tmdb_tv_on_air_slider]` | On air TV shows row |

### Usage Examples

**In Posts/Pages:**
```
[tmdb_hero_slider]
[tmdb_popular_slider]
```

**In Elementor:**
Simply add a "Shortcode" widget and paste any of the shortcodes above.

**In PHP Templates:**
```php
<?php echo do_shortcode('[tmdb_hero_slider]'); ?>
```

## 🎨 Customization

### Slider Behavior

Each slider can be individually configured with:
- **Reverse Animation**: Enable to make sliders scroll from right to left
- **Stop on Hover**: Disable to keep animation running even on hover

### Styling

The plugin includes responsive CSS that works out of the box. You can override styles in your theme's CSS:

```css
/* Custom hero slider height */
.tmdb-hero-slider {
    height: 600px;
}

/* Custom poster width */
.tmdb-poster-wrapper {
    width: 250px;
}
```

## 🔄 Updates

The plugin includes automatic update functionality from GitHub. When a new release is published:

1. You'll see an update notification in WordPress admin
2. Click "Update Now" to install the latest version
3. The plugin will automatically update from the GitHub release

## 📱 Responsive Design

All sliders are fully responsive and adapt to:
- Desktop screens
- Tablets
- Mobile devices

Poster sizes and slider heights automatically adjust for optimal viewing on all devices.

## 🛠️ Development

### File Structure

```
tmdb-slider/
├── assets/
│   ├── css/
│   │   └── tmdb-slider.css
│   └── js/
│       └── tmdb-slider.js
├── includes/
│   ├── class-tmdb-slider-admin.php
│   ├── class-tmdb-slider-api.php
│   ├── class-tmdb-slider-front.php
│   ├── class-tmdb-slider-plugin.php
│   └── class-tmdb-slider-updater.php
└── tmdb-slider.php
```

### Hooks & Filters

The plugin follows WordPress coding standards and can be extended using standard WordPress hooks and filters.

## 🐛 Troubleshooting

### Slider Not Displaying

1. **Check API Key**: Ensure your TMDb API key is correctly configured
2. **Check Connection**: Verify API connection status in settings
3. **Check Shortcode**: Make sure the slider is enabled in settings
4. **Clear Cache**: Clear any caching plugins if used

### API Errors

- Verify your API key is valid and active
- Check your server's ability to make external API calls
- Ensure your hosting allows outbound HTTPS connections

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 Ranber

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## 👤 Author

**Ranber**
- Website: [Ranber.com](https://Ranber.com)
- GitHub: [@Saber4Dev](https://github.com/Saber4Dev)

## 🙏 Credits

- **The Movie Database (TMDb)**: For providing the excellent API
- **WordPress Community**: For the amazing platform

## 📞 Support

For issues, feature requests, or contributions, please use the [GitHub Issues](https://github.com/Saber4Dev/tmdb-slider/issues) page.

## ⭐ Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Changelog

### 1.0.1
- Added reverse direction option for each slider
- Added stop on hover toggle for each slider
- Implemented automatic updates from GitHub
- Added Settings link in plugin row
- Improved code structure and documentation

### 1.0.0
- Initial release
- Basic slider functionality
- TMDb API integration
- Multiple shortcode support

---

**Made with ❤️ for the WordPress community**

