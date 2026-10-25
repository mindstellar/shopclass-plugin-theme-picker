<?php

/*
 * This file is part of the Theme Picker plugin for Shopclass.
 * Copyright (c) 2026 Navjot Tomer (Mindstellar) and contributors
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace mindstellar\themepicker;

use mindstellar\security\Demo;
use Params;
use WebThemes;

/**
 * The picker bar. Core does the switch: ?theme=<slug> previews an installed theme for the
 * visit, and ?theme= with no value goes back to the main one. This plugin sets no cookie.
 */
final class Picker
{
    /** Show the bar only on a demo site, and never on admin, API or AJAX requests. */
    public static function visible(bool $demo, bool $admin, bool $api, bool $ajax): bool
    {
        return $demo && !$admin && !$api && !$ajax;
    }

    public static function shouldRender(): bool
    {
        if (!class_exists(Demo::class)) {
            return false;
        }
        $ajax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || (class_exists(Params::class) && Params::getParamString('page') === 'ajax');

        return self::visible(
            Demo::active(),
            defined('OC_ADMIN') && OC_ADMIN,
            function_exists('osc_is_api_request') && osc_is_api_request(),
            $ajax
        );
    }

    /**
     * The current page's address with theme=$slug. The path and the other query pairs are kept
     * as sent. The result always starts with a single "/", so it can never point off the site.
     */
    public static function url(string $requestUri, string $slug): string
    {
        $requestUri = (string) preg_replace('/[^\x21-\x7e]/', '', explode('#', $requestUri, 2)[0]);
        $parts      = explode('?', $requestUri, 2);
        $path       = '/' . ltrim($parts[0], '/\\');
        $pairs      = array();
        foreach (explode('&', $parts[1] ?? '') as $pair) {
            if ($pair === '') {
                continue;
            }
            $name = urldecode(explode('=', $pair, 2)[0]);
            if ($name !== 'theme') {
                $pairs[] = $pair;
            }
        }
        $pairs[] = 'theme=' . rawurlencode($slug);

        return $path . '?' . implode('&', $pairs);
    }

    /**
     * Installed themes as slug => name, sorted by name.
     *
     * @return array<string,string>
     */
    public static function themes(): array
    {
        $web    = WebThemes::newInstance();
        $themes = array();
        foreach ((array) $web->getListThemes() as $slug) {
            $info          = $web->loadThemeInfo($slug);
            $name          = is_array($info) ? trim((string) ($info['name'] ?? '')) : '';
            $themes[$slug] = $name !== '' ? $name : $slug;
        }
        asort($themes, SORT_NATURAL | SORT_FLAG_CASE);

        return $themes;
    }

    /**
     * @param array<string,string> $themes slug => name
     * @param array<string,string> $labels theme, nav, reset (already translated)
     */
    public static function html(array $themes, string $current, string $main, string $uri, array $labels, string $cssUrl = ''): string
    {
        if (count($themes) < 2) {
            return '';
        }
        $e   = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $out = '';
        if ($cssUrl !== '') {
            $out .= '<link rel="stylesheet" href="' . $e($cssUrl) . '">';
        }
        $out .= '<details class="theme-picker"><summary>'
            . $e(sprintf($labels['theme'], $themes[$current] ?? $current))
            . '</summary><nav aria-label="' . $e($labels['nav']) . '"><ul>';
        foreach ($themes as $slug => $name) {
            $here = (string) $slug === $current ? ' aria-current="true"' : '';
            $out .= '<li><a href="' . $e(self::url($uri, (string) $slug)) . '"' . $here . '>' . $e($name) . '</a></li>';
        }
        $out .= '</ul>';
        if ($current !== $main) {
            $out .= '<a class="theme-picker-reset" href="' . $e(self::url($uri, '')) . '">' . $e($labels['reset']) . '</a>';
        }

        return $out . '</nav></details>';
    }

    public static function render(): void
    {
        if (!self::shouldRender()) {
            return;
        }
        $labels = array(
            'theme' => __('Theme: %s', 'theme-picker'),
            'nav'   => __('Switch theme', 'theme-picker'),
            'reset' => __('Main theme', 'theme-picker'),
        );
        echo self::html(
            self::themes(),
            WebThemes::newInstance()->getCurrentTheme(),
            osc_theme(),
            (string) ($_SERVER['REQUEST_URI'] ?? '/'),
            $labels,
            osc_asset_url_versioned(osc_plugin_url(dirname(__DIR__) . '/index.php') . 'assets/picker.css')
        );
    }
}
