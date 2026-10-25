<?php

/*
 * This file is part of the Theme Picker plugin for Shopclass.
 * Copyright (c) 2026 Navjot Tomer (Mindstellar) and contributors
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/**
 * URL building, escaping, and when the bar shows. Core's Demo class is loaded from
 * SHOPCLASS_CORE (or ../osclass) so "demo only" is checked against the real thing.
 */

require __DIR__ . '/../src/Picker.php';

$core = rtrim((string) (getenv('SHOPCLASS_CORE') ?: dirname(__DIR__) . '/../osclass'), '/');
require_once $core . '/oc-includes/osclass/classes/security/Demo.php';

use mindstellar\security\Demo;
use mindstellar\themepicker\Picker;

$fail = 0;
function check(string $name, bool $ok): void
{
    global $fail;
    if (!$ok) {
        echo "FAIL $name\n";
        $fail = 1;
    }
}
$has = static fn (string $h, string $n): bool => strpos($h, $n) !== false;

$labels = array('theme' => 'Theme: %s', 'nav' => 'Switch theme', 'reset' => 'Main theme');
$themes = array('aisle' => 'Aisle', 'glance' => 'Glance', 'folio' => 'Folio <b>');
$uri    = '/search?q=car&page=2';

/* URL building */
check('plain path', Picker::url('/', 'glance') === '/?theme=glance');
check('empty uri', Picker::url('', 'glance') === '/?theme=glance');
check('keeps query', Picker::url($uri, 'aisle') === '/search?q=car&page=2&theme=aisle');
check('replaces an old theme', Picker::url('/search?theme=folio&q=car', 'aisle') === '/search?q=car&theme=aisle');
check('reset has an empty value', Picker::url('/item/5?theme=folio', '') === '/item/5?theme=');
check('encoded key theme is replaced', Picker::url('/?%74heme=x', 'a') === '/?theme=a');
check('keeps odd keys as sent', Picker::url('/?a.b=1&c[]=2', 'a') === '/?a.b=1&c[]=2&theme=a');
check('drops the fragment', Picker::url('/p#frag', 'a') === '/p?theme=a');
check('// cannot leave the site', Picker::url('//evil.example/x', 'a') === '/evil.example/x?theme=a');
check('/\\ cannot leave the site', Picker::url('/\\evil.example', 'a') === '/evil.example?theme=a');
check('spaces and controls are dropped', Picker::url("/a b\r\nSet-Cookie:x", 'a') === '/abSet-Cookie:x?theme=a');
check('slug is encoded', Picker::url('/', 'a&b=c') === '/?theme=a%26b%3Dc');

/* Escaping */
$hostile = '/s?q="><script>alert(1)</script>&x=\'onmouseover=\'y';
$html    = Picker::html($themes, 'aisle', 'aisle', $hostile, $labels);
check('no raw script tag', !$has($html, '<script'));
check('no raw quote break', !$has($html, 'href="/s?q="'));
check('the query is escaped', $has($html, '&quot;&gt;&lt;script&gt;'));
check('theme names are escaped', $has($html, 'Folio &lt;b&gt;') && !$has($html, 'Folio <b>'));
$html = Picker::html(array('x' => 'X', 'y' => 'Y'), 'x"><i>', 'x', '/', $labels);
check('the current name is escaped', !$has($html, '<i>'));

/* Markup */
$html = Picker::html($themes, 'glance', 'aisle', $uri, $labels, 'https://s.example/p.css');
check('stylesheet link', $has($html, '<link rel="stylesheet" href="https://s.example/p.css">'));
check('summary names the current theme', $has($html, '<summary>Theme: Glance</summary>'));
check('nav is labelled', $has($html, '<nav aria-label="Switch theme">'));
check('one aria-current, on the current theme', substr_count($html, 'aria-current') === 1 && $has($html, 'page=2&amp;theme=glance" aria-current="true">Glance'));
check('every theme has a link', $has($html, 'theme=aisle"') && $has($html, 'theme=folio"'));
check('reset link while previewing', $has($html, 'class="theme-picker-reset" href="/search?q=car&amp;page=2&amp;theme=">Main theme'));
check('no reset link on the main theme', !$has(Picker::html($themes, 'aisle', 'aisle', $uri, $labels), 'theme-picker-reset'));
check('nothing for a single theme', Picker::html(array('a' => 'A'), 'a', 'a', '/', $labels) === '');
check('no stylesheet link without a url', !$has(Picker::html($themes, 'aisle', 'aisle', '/', $labels), '<link'));

/* When it shows */
check('hidden when demo is off', !Picker::visible(false, false, false, false));
check('shown on a demo page', Picker::visible(true, false, false, false));
check('hidden in admin', !Picker::visible(true, true, false, false));
check('hidden on the API', !Picker::visible(true, false, true, false));
check('hidden on AJAX', !Picker::visible(true, false, false, true));
check('core Demo is off without DEMO', !Demo::active() && !Picker::shouldRender());
define('DEMO', true);
check('core Demo is on with DEMO', Demo::active() && Picker::shouldRender());
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
check('hidden on an XHR request', !Picker::shouldRender());

echo $fail ? "failed\n" : "ok\n";
exit($fail);
