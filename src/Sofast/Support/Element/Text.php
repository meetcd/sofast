<?php
namespace Sofast\Support\Element;
use Sofast\Support\Element\AbstractElement;
use Sofast\Support\Layout;

class Text extends AbstractElement
{
    /**
     * 初始化当前输入项
     *
     * @access public
     * @param string $name 表单元素名称
     * @param array $options 选择项
     * @return Typecho_Widget_Helper_Layout
     */
    public function input($name = NULL, array $options = NULL)
    {
		$input = new Layout('input', array('id' => $name . '-0-' . self::$uniqueId,
        'name' => $name, 'type' => 'text', 'class' => 'form-control col-lg-8'));
        $this->container($input);
		$this->label->setAttribute('for', $name . '-0-' . self::$uniqueId);
        $this->inputs[] = $input;
        return $input;
    }

    /**
     * 设置表单项默认值
     *
     * @access protected
     * @param mixed $value 表单项默认值
     * @return void
     */
    protected function _value($value)
    {
        $this->input->setAttribute('value', htmlspecialchars($value));
    }
}
