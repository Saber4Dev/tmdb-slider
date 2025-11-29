# TMDB Slider

A WordPress plugin that integrates with The Movie Database (TMDb) API to display beautiful, customizable sliders for movies and TV shows.

![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.2%2B-blue.svg)
![License](https://img.shields.io/badge/license-GPL%20v2-green.svg)

## 🎬 Features

- **Hero & Row Sliders**: Full-width backdrop sliders and horizontal poster galleries
- **Dynamic Backgrounds**: Auto-apply TMDb backgrounds to divs with specific IDs
- **Content Types**: Movies and TV shows (Popular, Top Rated, Now Playing, Trending)
- **Flexible Shortcodes**: Smart attributes for easy customization
- **Advanced Styling**: Control colors, fonts, spacing, alignment for poster names
- **Animation Control**: Reverse direction, speed control, pause on hover
- **Elementor Compatible**: Works seamlessly with Elementor page builder
- **Auto-Updates**: Automatic updates from GitHub releases
- **Responsive Design**: Mobile-friendly sliders

## 📋 Requirements

- WordPress 5.0+
- PHP 7.2+
- TMDb API Key ([Get it free](https://www.themoviedb.org/settings/api))

## 🚀 Installation

1. Download from [Releases](https://github.com/Saber4Dev/tmdb-slider/releases) or clone:
   ```bash
   git clone https://github.com/Saber4Dev/tmdb-slider.git
   ```
2. Upload to `/wp-content/plugins/`
3. Activate in WordPress admin
4. Configure API key at **Settings > TMDB Slider**

## ⚙️ Configuration

### API Setup
1. Get your free API key from [themoviedb.org](https://www.themoviedb.org/settings/api)
2. Paste it in **Settings > TMDB Slider**

### Settings Overview
- **Slider Speeds**: Control animation duration for hero and row sliders
- **Poster Width**: Customize poster image width
- **Poster Gap**: Control spacing between posters
- **Display Options**: Show/hide play icons, ratings, names
- **Name Styling**: Color, font size, alignment, padding, margin
- **Clickable Posters**: Enable/disable links to TMDb pages

## 📝 Shortcodes

### Main Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[tmdb_hero_slider]` | Backdrop hero slider (trending content) |
| `[tmdb_popular_slider]` | Popular content row |
| `[tmdb_top_rated_slider]` | Top rated content row |
| `[tmdb_now_playing_slider]` | Now playing/on air content row |

### Attributes

| Attribute | Description | Example |
|-----------|-------------|---------|
| `type` | Filter by content type (`movie` or `tv`) | `[tmdb_hero_slider type=movie]` |
| `reverse` | Control animation direction (`on` or `off`) | `[tmdb_popular_slider reverse=on]` |
| `stop_on_hover` | Control pause on hover (`on` or `off`) | `[tmdb_hero_slider stop_on_hover=off]` |
| `speed` | Override animation speed (seconds) | `[tmdb_hero_slider speed=40]` |
| `poster_width` | Override poster width (pixels) | `[tmdb_popular_slider poster_width=250]` |

### Examples

```php
// Basic usage
[tmdb_hero_slider]
[tmdb_popular_slider]

// With type filter
[tmdb_hero_slider type=movie]
[tmdb_popular_slider type=tv]

// Advanced examples
[tmdb_hero_slider type=movie reverse=on]
[tmdb_popular_slider type=tv speed=40 stop_on_hover=off]
[tmdb_top_rated_slider reverse=on poster_width=250]
[tmdb_now_playing_slider type=tv reverse=on speed=30]
```

**In Elementor**: Add a Shortcode widget and paste any shortcode.

**In PHP**: `<?php echo do_shortcode('[tmdb_hero_slider type=movie reverse=on]'); ?>`

### Backward Compatibility

Old shortcodes still work:
- `[tmdb_movie_hero_slider]` → `[tmdb_hero_slider type=movie]`
- `[tmdb_tv_hero_slider]` → `[tmdb_hero_slider type=tv]`

## 🖼️ Dynamic Backgrounds

Automatically apply TMDb movie/TV backgrounds to any div by adding a specific ID.

### Available Background IDs

| ID | Description |
|----|-------------|
| `#tmdb--popular-movie-background` | Popular movies |
| `#tmdb--popular-tv-background` | Popular TV shows |
| `#tmdb--trending-movie-background` | Trending movies |
| `#tmdb--trending-tv-background` | Trending TV shows |
| `#tmdb--top-rated-movie-background` | Top rated movies |
| `#tmdb--top-rated-tv-background` | Top rated TV shows |
| `#tmdb--now-playing-movie-background` | Now playing movies |
| `#tmdb--on-air-tv-background` | On air TV shows |

### Usage

Simply add the ID to any div:

```html
<div id="tmdb--popular-movie-background" style="width: 100%; height: 400px;">
    Your content here
</div>
```

The plugin will automatically:
- Fetch backdrop images from TMDb
- Apply them as backgrounds
- Cycle through images at the configured interval
- Apply overlay if enabled

### Background Settings

Configure in **Settings > TMDB Slider**:
- **Background Position**: Center, Top, Bottom, Left, Right, etc.
- **Background Size**: Cover, Contain, Auto, 100% 100%
- **Change Interval**: How often to change background (seconds)
- **Overlay**: Enable dark overlay with customizable color

## 🎨 Customization

### Name Styling
Control poster name appearance:
- **Text Color**: Color picker
- **Font Size**: Custom size (e.g., `16px`, `1.2em`)
- **Text Align**: Left, Center, Right
- **Padding**: CSS padding values (e.g., `5px`, `5px 10px`)
- **Margin**: CSS margin values (e.g., `10px 0 0 0`)

### CSS Overrides
Override styles in your theme's CSS:

```css
.tmdb-hero-slider {
    height: 600px;
}

.tmdb-poster-wrapper {
    width: 250px;
}
```

## 🔄 Updates

Automatic updates from GitHub releases. Update notifications appear in WordPress admin.

## 🐛 Troubleshooting

**Slider not displaying?**
- Verify API key is configured correctly
- Check API connection status in settings
- Clear caching plugins

**API errors?**
- Ensure API key is valid and active
- Check server can make external HTTPS calls

## 📄 License

GPL v2 or later

## 👤 Author

**Ranber** - [Ranber.com](https://Ranber.com) | [GitHub](https://github.com/Saber4Dev)

## 📞 Support & Contributing

- **Issues**: [GitHub Issues](https://github.com/Saber4Dev/tmdb-slider/issues)
- **Contributions**: Pull requests welcome!

---

**Made with ❤️ for the WordPress community**
