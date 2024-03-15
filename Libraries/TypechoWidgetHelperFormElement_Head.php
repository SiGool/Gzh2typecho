<?php
/**
 *  @ SiGool
 *  2024/02/25
 */

namespace TypechoPlugin\Gzh2typecho\Libraries;

use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Layout;

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * 标题显示组件
 * Class TypechoWidgetHelperFormElement_Head
 * @package TypechoPlugin\Gzh2typecho\Libraries
 */
class TypechoWidgetHelperFormElement_Head extends Text
{
    private $styles = [];

    public function __construct($name = null, $text = null, $styles = [])
    {
        $this->styles = $styles;
        parent::__construct($name, null, null, $text);
    }

    public function init()
    {
        $styles = '';
        foreach ($this->styles as $attrK => $attrV)
            $styles .= $attrK . ': ' . $attrV . ';';

        $this->setAttribute('style', $styles);
    }

    public function input(?string $name = null, ?array $options = null): ?Layout
    {
        return parent::input($name, $options)
                    ->setAttribute('style', 'display: none;');
    }
}