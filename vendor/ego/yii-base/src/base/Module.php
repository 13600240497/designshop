<?php
namespace ego\base;

/**
 * 基础Module，继承`yii\base\Module`
 */
class Module extends \yii\base\Module
{
    use ServiceLocatorTrait;

    /**
     * 域名
     *
     * 配置后，访问当前模块必须使用指定的域名进行访问，比如user模块必须使用**http://user.xxx.com**
     *
     * ```php
     *  // web.php
     *  [
     *      'modules' => [
     *          'user' => [
     *              'class' => 'app\modules\user\Module',
     *              'domain' => 'http://user.xxx.com'
     *          ],
     *      ],
     *  ]
     * ```
     *
     * @var string
     */
    public $domain = null;
}
