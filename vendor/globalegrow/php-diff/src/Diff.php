<?php
namespace Globalegrow\PhpDiff;

use Globalegrow\Base\Component;
use Globalegrow\Base\Str;

/**
 * 将两个字符串差异转化为字符串
 */
class Diff extends Component
{
    /**
     * @var string 不进行详细比较模板
     */
    public $noDiffTpl = <<<EOT
   <li class="no-diff">修改了 <b><i>{label}</i></b>，
   原值：“<del>{old}</del>”，新值：“<ins>{new}</ins>”</li>
EOT;
    /**
     * @var string 详细比较模板
     */
    public $hasDiffTpl = <<<EOT
   <li class="has-diff">修改了 <b><i>{label}</i></b>，
   差别为 <i class="fa fa-plus-square" data-toggle="diff"></i>{diff}</li>
EOT;

    /**
     * 将数组的差异转化为字符串
     *
     * @param array|string $changes
     * @param array $labels
     * @return string
     */
    public function changes2detail($changes, $labels = [])
    {
        if (!is_array($changes)) {
            $changes = json_decode($changes, true);
        }
        if (!$changes) {
            return '';
        }

        $detail = '<ol class="changes">';
        foreach ($changes as $field => $item) {
            $label = isset($labels[$field]) ? $labels[$field] : $field;
            if ($item['diff']) {
                $detail .= Str::format(
                    $this->hasDiffTpl,
                    [
                        'label' => $label,
                        'diff' => $this->renderDiff($item['old'], $item['new']),
                    ]
                );
            } else {
                $detail .= Str::format(
                    $this->noDiffTpl,
                    [
                        'label' => $label,
                        'old' => $item['old'],
                        'new' => $item['new']
                    ]
                );
            }
        }
        return $detail . '</ol>';
    }

    /**
     * 获取差异
     *
     * @param string $str1
     * @param string $str2
     * @return string
     */
    public function renderDiff($str1, $str2)
    {
        $str1 = (string) $str1;
        $str2 = (string) $str2;
        return $str1 === $str2 ? '' : (new Renderer($str1, $str2))->render();
    }
}
