<?php
namespace Sofast\Support\Element;
use Sofast\Support\Layout;

abstract class AbstractElement extends Layout
{
    /**
     * 表单描述
     *
     * @access private
     * @var string
     */
    protected $description;

    /**
     * 表单消息
     *
     * @access protected
     * @var string
     */
    protected $message;

    /**
     * 多行输入
     *
     * @access public
     * @var array()
     */
    protected $multiline = array();

    /**
     * 单例唯一id
     *
     * @access protected
     * @var integer
     */
    protected static $uniqueId = 0;

    /**
     * 表单元素容器
     *
     * @access public
     * @var Layout
     */
    public $container;

    /**
     * 输入栏
     *
     * @access public
     * @var Layout
     */
    public $input;

    /**
     * inputs  
     * 
     * @var array
     * @access public
     */
    public $inputs = array();

    /**
     * 表单标题
     *
     * @access public
     * @var Layout
     */
    public $label;

    /**
     * 表单验证器
     *
     * @access public
     * @var array
     */
    public $rules = array();

    /**
     * 表单名称
     *
     * @access public
     * @var string
     */
    public $name;

    /**
     * 表单值
     *
     * @access public
     * @var mixed
     */
    public $value;

    /**
     * 构造函数
     *
     * @access public
     * @param string $name 表单输入项名称
     * @param array $options 选择项
     * @param mixed $value 表单默认值
     * @param string $label 表单标题
     * @param string $description 表单描述
     * @return void
     */
    public function __construct($name = NULL, array $options = NULL, $value = NULL, $label = NULL, $description = NULL)
    {
        /** 创建html元素,并设置class */
        parent::__construct('div', array('class' => 'form-group col-lg-6'));
        $this->name = $name;
        self::$uniqueId ++;

        /** 运行自定义初始函数 */
        $this->init();

        /** 初始化表单标题 */
        if (NULL !== $label) {
            $this->label($label);
        }

        /** 初始化表单项 */
        $this->input = $this->input($name, $options);

        /** 初始化表单值 */
        if (NULL !== $value) {
            $this->value($value);
        }

        /** 初始化表单描述 */
        if (NULL !== $description) {
            $this->description($description);
        }
    }

    /**
     * filterValue  
     * 
     * @param mixed $value 
     * @access protected
     * @return string
     */
    protected function filterValue($value)
    {
        if (preg_match_all('/[_0-9a-z-]+/i', $value, $matches)) {
            return implode('-', $matches[0]);
        }

        return '';
    }

    /**
     * 自定义初始函数
     *
     * @access public
     * @return void
     */
    public function init(){}

    /**
     * 创建表单标题
     *
     * @access public
     * @param string $value 标题字符串
     * @return Element
     */
    public function label($value)
    {
		/** 创建标题元素 */
        if (empty($this->label)) {
            $this->label = new Layout('label', array('class' => 'control-label col-lg-3'));
			$this->addItem($this->label);
			//$this->container($this->label);
        }
        $this->label->html($value);
        return $this;
    }

    /**
     * 在容器里增加元素
     *
     * @access public
     * @param Layout $item 表单元素
     * @return $this
     */
    public function container(Layout $item)
    {
        /** 创建表单容器 */
        if (empty($this->container)) {
            $this->container = new Layout('div', array('class' => 'col-lg-6'));
            $this->addItem($this->container);
        }

        $this->container->addItem($item);
        return $this;
    }

    /**
     * 设置提示信息
     *
     * @access public
     * @param string $message 提示信息
     * @return Element
     */
    public function message($message)
    {
        if (empty($this->message)) {
            $this->message =  new Layout('div', array('class' => 'help-block'));
            $this->container($this->message);
        }

        $this->message->html($message);
        return $this;
    }

    /**
     * 设置描述信息
     *
     * @access public
     * @param string $description 描述信息
     * @return Element
     */
    public function description($description)
    {
        if (empty($this->description)) {
            $this->description = new Layout('p', array('class' => 'help-block'));
            $this->container($this->description);
        }
        $this->description->html($description);
        return $this;
    }

    /**
     * 设置表单元素值
     *
     * @access public
     * @param mixed $value 表单元素值
     * @return Element
     */
    public function value($value)
    {
        $this->value = $value;
        $this->_value($value);
        return $this;
    }

    /**
     * 多行输出模式
     *
     * @access public
     * @return Layout
     */
    public function multiline()
    {
        $item = new Layout('span');
        $this->multiline[] = $item;
        return $item;
    }

    /**
     * 多行输出模式
     *
     * @access public
     * @return Element
     */
    public function multiMode()
    {
        foreach ($this->multiline as $item) {
            $item->setAttribute('class', 'multiline');
        }
        return $this;
    }

    /**
     * 初始化当前输入项
     *
     * @access public
     * @param Layout $container 容器对象
     * @param string $name 表单元素名称
     * @param array $options 选择项
     * @return Element
     */
    abstract public function input($name = NULL, array $options = NULL);

    /**
     * 设置表单元素值
     *
     * @access protected
     * @param mixed $value 表单元素值
     * @return void
     */
    abstract protected function _value($value);

    /**
     * 增加验证器
     *
     * @access public
     * @return Element
     */
    public function addRule($name)
    {
        $this->rules[] = func_get_args();
        return $this;
    }
}
