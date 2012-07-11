# F3Site
Lightweight content management system with support for multilingual sites.

## Requirements
- PHP 5.2+
- MySQL 5.1+ or SQLite 3+
- PDO driver enabled
- web server like Apache, Nginx...

## Installation
1. Clone or download this repository to web server.
2. Open web browser and navigate to `install` directory.
3. Follow instructions on screen.

### .htaccess

Some web servers require `RewriteBase` directive in `.htaccess` files. If you encounter HTTP 500 error or nice URLs don't work for you, then you should try to replace `.htaccess` contents with the following code:

```.htaccess
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.+) index.php?go=$1 [L,QSA]
```

## Key features

### Own template system based on HTML comments
Conditional rendering:
```html
<!-- IF feature_enabled -->
<ins>Feature enabled</ins>
<!-- ELSE -->
<del>Feature disabled</del>
<!-- END -->
```
Loops - variables inside are in scope:
```html
<!-- START news -->
<h4>{title} {lang.by} {author}</h4>
<p>{content}</p>
<!-- STOP -->
```
Forms - no need to set value for checkbox, radio and select:
```html
<form method="post" f3:array="news" f3:mode="isset">
  <input type="text" name="title" value="{news.title}">
  <textarea name="content">{news.content}</textarea>
  <input type="checkbox" name="enabled">
  <select name="is_important">
    <option>Yes</option>
    <option>No</option>
  </select>
</form>
```

### Content management
- content types \
  📑 articles \
  💾 files \
  🖼️ gallery (images, audio, video) \
  🔗 links \
  📆 news
- own HTML and BBCode editor in JavaScript
- separated admin panel from editor panel
- unlimited levels of categories
- tags and Web 2.0 tag cloud

### Multiple languages support
- content for all or specified language
- home page selection for each language
- automatic detection from Content-Language header

### Social features
- users and user groups
- advanced permissions
- private messages
- Gravatar support
- comments and stars
- polls (with SVG pie chart)

### Other features
- nice URLs based on mod_rewrite or PATH_INFO
- syntax highlighting by Google Code Prettify
- anti-spam systems (Sblam, reCAPTCHA, Asirra)
- plugins (guestbook, chat, bug tracker)
- file manager
- RSS channels

## History
This project was started in 2005 inspired by other content management systems like jPortal, PHP-Fusion and Joomla! The main idea was to create a CMS that has low hardware requirements and works fast on free hostings.

Initial releases stored data in .php files so they did not need database. Version 2.0 switched to MySQL. The milestone was version 3.0 with rebuilt architecture, advanced template system and many new features.

As market expectations changed and minor content management systems lost the battle with the most popular ones and more specialized solutions, this project has been discontinued.

| Year | Version | Major changes |
|------|---------|------------|
| 2005 | 1.0 | 🟢 Initial version formely F3CMS |
| 2005 | 1.1 | 🟢 Bug fixes |
| 2005 | 1.2 | 🟢 Bug fixes |
| 2005 | 1.3 | 🟢 Bug fixes |
| 2006 | 1.4 | 🟢 File manager<br>🟢 Private messages |
| 2006 | 2.0 | 🟢 Switch from files to MySQL<br>🟢 Search content and users<br>🟢 Database backup<br>🟢 User groups |
| 2007 | 2.1 | 🟢 Events log<br>🟢 Emoticons<br>🟢 AJAX support<br>🟢 Auto-detecting browser language<br>🟢 New settings center |
| 2009 | 3.0 | 🟢 Advanced template system<br>🟢 Fancy HTML/BBCode editor<br>🟢 One True Layout aka. Holy Grail<br>🟢 Switch from ISO-8859-2 to UTF-8<br>🟢 Separate admin and editor panel<br>🟢 SQLite support<br>🟢 RSS channels |
| 2010 | 3.1 | 🟢 Nice URLS with mod_rewrite or PATH_INFO<br>🟢 Google reCAPTCHA™ and Microsoft Asirra<br>🟢 Syntax highlighting with Prettify<br>🟢 New user-friendly installer<br>🟢 Tags and Web 2.0 tag cloud |
| 2011 | 3.2 | 🟢 New modern template<br>🟢 TinyMCE plugin (option)<br>🟢 Improved template system |
| 2012 | 3.3 dev | 🟡 Sblam! blacklist<br>🟡 SVG pie chart in polls<br>🟡 Gravatar support<br>🟡 Integrated breadcrumb with top menu<br>🔴 New security features<br>🔴 Re-design template system<br>🔴 Make main template responsive<br>🔴 Allow to mix content types<br>🔴 New home page module |

## Third-party libraries

- Silk Icons
- Function Icons
- Google Code Prettify
- Slimbox 2
- Google reCAPTCHA
- Microsoft Asirra
- TinyMCE

## Credits

- Kamil881 (modern layout)
- PanYeti (CyfroGraf)
- jareQ
- rafal
- skygod
- Fenek
- Sh1moda
- bolek12
- Lavanos
- BŁogi
- DJ ProG
- Ervil
- mati1d
- Petermechanic
- RoboKomp

## License

GPL version 3

## Support
This project is no more maintained since 2012 and will not receive security updates.
