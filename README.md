# Theme Picker

A small bar for demo sites. A visitor opens it, picks any installed theme, and sees the site in
that theme for the visit.

## Requirements

- Shopclass 7.0.0 or later
- PHP 8.0 or later
- A demo site (`OSC_DEMO=1`)

## Install

This plugin is not in the catalogue. It is for demo sites only.

1. Download the zip from the GitHub release.
2. Open Plugins → Add new plugin and upload it.
3. Activate.

## Set up

Start the site with `OSC_DEMO=1`. Nothing else is needed. On a site that is not a demo, the bar never shows.

## How it works

- The bar lists the installed themes and marks the current one. *Main theme* goes back to the theme set in the admin.
- Each link adds `?theme=<slug>` to the page you are on and keeps the rest of the address. Shopclass does the switch and remembers it for the visit.
- The plugin sets no cookies and saves no settings.
- It adds nothing to the admin, the API or AJAX requests.

## Notes

- The bar shows only when two or more themes are installed.
- A visitor who picks a theme gets pages that are not shared-cached.
- Works on shared hosting.

## For developers

Run the tests with `./tests/run.sh`. They need no network. Core is read from `../osclass`, or from `SHOPCLASS_CORE`.

## Support

https://github.com/mindstellar/shopclass-plugin-theme-picker/issues

## License

GPL-3.0-or-later
