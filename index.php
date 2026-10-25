<?php
/*
Plugin Name: Theme Picker
Plugin URI: https://github.com/mindstellar/shopclass-plugin-theme-picker
Description: Demo sites only. A small bar lets each visitor try every installed theme, and go back to the main one.
Version: 0.1.0
Author: Mindstellar
Author URI: https://mindstellar.com
Short Name: theme-picker
Requires Shopclass: 7.0.0
Tested up to: 7.1
Requires PHP: 8.0
Support URI: https://github.com/mindstellar/shopclass-plugin-theme-picker/issues
Modes: classifieds, shop
*/

/*
 * This file is part of the Theme Picker plugin for Shopclass.
 * Copyright (c) 2026 Navjot Tomer (Mindstellar) and contributors
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use mindstellar\themepicker\Picker;

if (!defined('ABS_PATH')) {
    exit('Direct access is not allowed.');
}

require_once __DIR__ . '/src/Picker.php';

osc_register_plugin(osc_plugin_path(__FILE__), '');

osc_add_hook('footer', array(Picker::class, 'render'));
