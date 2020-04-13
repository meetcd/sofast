<?php
namespace Sofast\Support\Form\Element;
use Sofast\Support\Element\AbstractElement;
use Sofast\Support\Layout;

class Radio extends AbstractElement
{
    /**
     * 选择值
     *
     * @access private
     * @var array
     */
    private $_options = array();

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
        foreach ($options as $value => $label) {
            $this->_options[$value] = new Layout('input');
            $item = $this->multiline();
            $id = $this->name . '-' . $this->filterValue($value);
            $this->inputs[] = $this->_options[$value];

            $item->addItem($this->_options[$value]->setAttribute('name', $this->name)
            ->setAttribute('type', 'radio')
            ->setAttribute('value', $value)
            ->setAttribute('id', $id));

            $labelItem = new Layout('label');
            $item->addItem($labelItem->setAttribute('for', $id)->setAttribute('class',"col-lg-3 control-label")->html($label));
			
            $this->container($item);
        }

        return current($this->_options);
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
        foreach ($this->_options as $option) {
            $option->removeAttribute('checked');
        }

        if (isset($this->_options[$value])) {
            $this->value = $value;
            $this->_options[$value]->setAttribute('checked', 'true');
            $this->input = $this->_options[$value];
        }
    }
}
