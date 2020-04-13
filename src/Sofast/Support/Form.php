<?php
namespace Sofast\Support;
use Sofast\Support\Layout;
use Sofast\Support\Element\AbstractElement;

class Form extends Layout
{
    /** 表单post方法 */
    const POST_METHOD = 'POST';

    /** 表单get方法 */
    const GET_METHOD = 'GET';
	
	const NAME = 'validateForm';

    /** 标准编码方法 */
    const STANDARD_ENCODE = 'application/x-www-form-urlencoded';

    /** 混合编码 */
    const MULTIPART_ENCODE = 'multipart/form-data';

    /** 文本编码 */
    const TEXT_ENCODE= 'text/plain';

    /**
     * 输入元素列表
     *
     * @access private
     * @var array
     */
    private $_inputs = array();
    /**
     * 构造函数,设置基本属性
     *
     * @access public
     * @return void
     */
    public function __construct($action = NULL,$name = self::NAME, $method = self::POST_METHOD, $enctype = self::STANDARD_ENCODE)
    {
        /** 设置表单标签 */
        parent::__construct('form');
        /** 关闭自闭合 */
        $this->setClose(false);
        /** 设置表单属性 */
		$this->setName($name);
        $this->setAction($action);
        $this->setMethod($method);
        $this->setEncodeType($enctype);
    }

    /**
     * 设置表单编码方案
     *
     * @access public
     * @param string $enctype 编码方法
     * @return Form
     */
    public function setEncodeType($enctype)
    {
        $this->setAttribute('enctype', $enctype);
        return $this;
    }

    /**
     * 增加输入元素
     *
     * @access public
     * @param AbstractElement $input 输入元素
     * @return Form
     */
    public function addInput(AbstractElement $input)
    {
        $this->_inputs[$input->name] = $input;
        $this->addItem($input);
        return $this;
    }

    /**
     * 增加元素(重载)
     *
     * @access public
     * @param Layout $item 表单元素
     * @return Layout
     */
    public function addItem(Layout $item)
    {
        if ($item instanceof Submit) {
            $this->addItem($item);
        } else {
            parent::addItem($item);
        }

        return $this;
    }

    /**
     * 获取输入项
     *
     * @access public
     * @param string $name 输入项名称
     * @return mixed
     */
    public function getInput($name)
    {
        return $this->_inputs[$name];
    }

    /**
     * 获取所有输入项的提交值
     *
     * @access public
     * @return array
     */
    public function getAllRequest()
    {
        $result = array();
        $source = (self::POST_METHOD == $this->getAttribute('method')) ? $_POST : $_GET;

        foreach ($this->_inputs as $name => $input) {
            $result[$name] = isset($source[$name]) ? $source[$name] : NULL;
        }
        return $result;
    }

    /**
     * 设置表单提交方法
     *
     * @access public
     * @param string $method 表单提交方法
     * @return Form
     */
    public function setMethod($method)
    {
        $this->setAttribute('method', $method);
        return $this;
    }

    /**
     * 设置表单提交目的
     *
     * @access public
     * @param string $action 表单提交目的
     * @return Form
     */
    public function setAction($action)
    {
        $this->setAttribute('action', $action);
        return $this;
    }
	
	public function setName($name)
    {
        $this->setAttribute('name', $name);
		$this->setAttribute('id', $name);
        return $this;
    }

    /**
     * 获取此表单的所有输入项固有值
     *
     * @access public
     * @return array
     */
    public function getValues()
    {
        $values = array();

        foreach ($this->_inputs as $name => $input) {
            $values[$name] = $input->value;
        }
        return $values;
    }

    /**
     * 获取此表单的所有输入项
     *
     * @access public
     * @return array
     */
    public function getInputs()
    {
        return $this->_inputs;
    }

    /**
     * 获取提交数据源
     *
     * @access public
     * @param array $params 数据参数集
     * @return array
     */
    public function getParams(array $params)
    {
        $result = array();
        $source = (self::POST_METHOD == $this->getAttribute('method')) ? $_POST : $_GET;

        foreach ($params as $param) {
            $result[$param] = isset($source[$param]) ? $source[$param] : NULL;
        }

        return $result;
    }

    /**
     * 验证表单
     *
     * @access public
     * @return void
     */
    public function validate()
    {
        $validator = new validate();
        $rules = array();

        foreach ($this->_inputs as $name => $input) {
            $rules[$name] = $input->rules;
        }

        $id = md5(implode('"', array_keys($this->_inputs)));

        /** 表单值 */
        $formData = $this->getParams(array_keys($rules));
        $error = $validator->run($formData, $rules);

        if ($error) {
            /** 利用session记录错误 */
            $_SESSION['__form_message_' . $id] = $error;

            /** 利用session记录表单值 */
            $_SESSION['__form_record_' . $id] = $formData;
        }

        return $error;
    }

    /**
     * 显示表单
     *
     * @access public
     * @return void
     */
    public function render()
    {
        $id = md5(implode('"', array_keys($this->_inputs)));

        /** 恢复表单值 */
        if (isset($_SESSION['__form_record_' . $id])) {
            $record = $_SESSION['__form_record_' . $id];
            $message = $_SESSION['__form_message_' . $id];
            foreach ($this->_inputs as $name => $input) {
                $input->value(isset($record[$name]) ? $record[$name] : $input->value);
                /** 显示错误消息 */
                if (isset($message[$name])) {
                    $input->message($message[$name]);
                }
            }

            unset($_SESSION['__form_record_' . $id]);
        }

        parent::render();
        unset($_SESSION['__form_message_' . $id]);
    }
}
