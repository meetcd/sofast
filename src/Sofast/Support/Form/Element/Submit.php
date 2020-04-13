<?php
namespace Sofast\Support\Form\Element;

class Submit extends Element
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
        $this->setAttribute('class', 'typecho-option typecho-option-submit');
        $input = new Typecho_Widget_Helper_Layout('button', array('type' => 'submit'));
        $this->container($input);
        $this->inputs[] = $input;

        return $input;
    }

    /**
     * 设置表单元素值
     *
     * @access protected
     * @param mixed $value 表单元素值
     * @return void
     */
    protected function _value($value)
    {
        $this->input->html($value);
    }
}
