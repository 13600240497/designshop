<?php
namespace Globalegrow\PhpDiff;

/**
 * 行内对比差异
 */
class Renderer
{
    /**
     * @var Diff_Renderer_Abstract $renderer
     */
    protected $renderer;

    /**
     * @var string $str1
     * @var string $str2
     * @var \Diff_Renderer_Abstract $renderer
     */
    public function __construct($str1, $str2, \Diff_Renderer_Abstract $renderer = null)
    {
        $this->renderer = $renderer ?: new \Diff_Renderer_Html_Array();
        $this->renderer->diff = new \Diff(
            explode("\n", $str1),
            explode("\n", $str2)
        );
    }

    /**
     * 渲染对比差异结果
     */
    public function render()
    {
        /** @var array[] $changes */
        $changes = $this->renderer->render();
        $html = '<table class="php-diff"><thead><tr><th>原值</th><th>新值</th><th>差异</th></tr></thead>';
        foreach ($changes as $i => $blocks) {
            // If this is a separate block, we're condensing code so output ...,
            // indicating a significant portion of the code has been collapsed as
            // it is the same
            if ($i > 0) {
                $html .= '<tbody class="skipped"><th>&hellip;</th><th>&hellip;</th><td>&nbsp;</td></tbody>';
            }
            /** @var array[] $change */
            foreach ($blocks as $change) {
                $html .= '<tbody class="change-' . $change['tag'] . '">';
                $html .= $this->{$change['tag']}($change);
                $html .= '</tbody>';
            }
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * 相等
     *
     * @param array $change
     * @return string
     */
    protected function equal(array $change)
    {
        $html = '';
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '  <th>' . $fromLine . '</th>';
            $html .= '  <th>' . $toLine . '</th>';
            $html .= '  <td class="left">' . $line . '</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * 插入
     *
     * @param array $change
     * @return string
     */
    protected function insert(array $change)
    {
        $html = '';
        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '  <th>&nbsp;</th>';
            $html .= '  <th>' . $toLine . '</th>';
            $html .= '  <td class="right">+<ins>' . $line . '</ins>&nbsp;</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * 删除
     *
     * @param array $change
     * @return string
     */
    protected function delete(array $change)
    {
        $html = '';
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '  <th>' . $fromLine . '</th>';
            $html .= '  <th>&nbsp;</th>';
            $html .= '  <td class="left">-<del>' . $line . '</del>&nbsp;</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * 替换
     *
     * @param array $change
     * @return string
     */
    protected function replace(array $change)
    {
        $html = '';
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '  <th class="delete">' . $fromLine . '</th>';
            $html .= '  <th class="delete">&nbsp;</th>';
            $html .= '  <td class="left">-' . $line . '</td>';
            $html .= '</tr>';
        }

        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '  <th class="insert">' . $toLine . '</th>';
            $html .= '  <th class="insert">&nbsp;</th>';
            $html .= '  <td class="right">+' . $line . '</td>';
            $html .= '</tr>';
        }
        return $html;
    }
}
