<?php
/**
 * 伪静态规则
 */

function _rule_get_default_uri() {
        if (isset($_COOKIE['site_group_code']) && $_COOKIE['site_group_code'] == 'gb') return sprintf('activity/%s/activity/index', strtolower($_COOKIE['site_group_code']));
        if (isset($_COOKIE['site_group_code']) && in_array($_COOKIE['site_group_code'], ['rg','dl','zf','suk']) ) return 'activity/zf/activity/index';
        return 'activity/activity/index';
}

return [
    '' => _rule_get_default_uri(),
    '<controller:[\w-]+>/<action:[\w-]+>' => 'activity/<controller>/<action>',
    '<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<module>/<controller>/<action>',
    '<module:[\w-]+>/<submodule:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<module>/<submodule>/<controller>/<action>'
];
