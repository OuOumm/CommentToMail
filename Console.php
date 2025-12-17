<?php

/**
 * 评论邮件提醒 控制台
 */
class CommentToMail_Console extends Typecho_Widget
{
    /** @var  模板文件目录 */
    private $_dir;
    /**
     * 当前文件
     *
     * @access private
     * @var string
     */
    private $_currentFile;

    /**
     * 执行函数
     *
     * @access public
     * @return void
     * @throws Typecho_Widget_Exception
     */
    public function execute()
    {
        /** 管理员权限 */
        $this->widget('Widget_User')->pass('administrator');
        $this->_dir = dirname(__FILE__);

        // 安全获取模板文件列表，不使用GLOB_BRACE以提高兼容性
        $htmlFiles = array_merge(
            glob($this->_dir . '/*.html'),
            glob($this->_dir . '/*.HTML')
        );

        $this->_currentFile = $this->request->get('file', 'owner.html');

        // 严格验证文件名，只允许owner.html和guest.html
        $allowedFiles = array('owner.html', 'guest.html');
        if (
            in_array($this->_currentFile, $allowedFiles)
            && file_exists($this->_dir . '/' . $this->_currentFile)
        ) {
            foreach ($htmlFiles as $file) {
                if (file_exists($file)) {
                    $filename = basename($file);
                    // 只显示允许的模板文件
                    if (in_array($filename, $allowedFiles)) {
                        $this->push(array(
                            'file'      =>  $filename,
                            'current'   => ($filename == $this->_currentFile)
                        ));
                    }
                }
            }

            return;
        }

        throw new Typecho_Widget_Exception('模板文件不存在', 404);
    }

    /**
     * 获取菜单标题
     *
     * @access public
     * @return string
     */
    public function getMenuTitle()
    {
        return _t('编辑文件 %s', $this->_currentFile);
    }

    /**
     * 获取文件内容
     *
     * @access public
     * @return string
     */
    public function currentContent()
    {
        return htmlspecialchars(file_get_contents($this->_dir . '/' . $this->_currentFile));
    }

    /**
     * 获取文件是否可读
     *
     * @access public
     * @return string
     */
    public function currentIsWriteable()
    {
        return is_writeable($this->_dir . '/' . $this->_currentFile);
    }

    /**
     * 获取当前文件
     *
     * @access public
     * @return string
     */
    public function currentFile()
    {
        return $this->_currentFile;
    }

    /**
     * 邮件测试表单
     * @return Typecho_Widget_Helper_Form
     */
    public function testMailForm()
    {
        /** 构建表单 */
        $options = Typecho_Widget::widget('Widget_Options');
        $form = new Typecho_Widget_Helper_Form(
            Typecho_Common::url('/action/' . CommentToMail_Plugin::$action, $options->index),
            Typecho_Widget_Helper_Form::POST_METHOD
        );

        /** 收件人名称 */
        $toName = new Typecho_Widget_Helper_Form_Element_Text('toName', NULL, NULL, _t('收件人名称'), _t('为空则使用博主昵称'));
        $form->addInput($toName);

        /** 收件人邮箱 */
        $to = new Typecho_Widget_Helper_Form_Element_Text('to', NULL, NULL, _t('收件人邮箱'), _t('为空则使用博主邮箱'));
        $form->addInput($to);

        /** 邮件标题 */
        $title = new Typecho_Widget_Helper_Form_Element_Text('title', NULL, NULL, _t('邮件标题 *'));
        $form->addInput($title);

        /** 邮件内容 */
        $content = new Typecho_Widget_Helper_Form_Element_Textarea('content', NULL, NULL, _t('邮件内容 *'));
        $content->input->setAttribute('class', 'w-100 mono');
        $form->addInput($content);

        /** 动作 */
        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $form->addInput($do);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        /** 设置值 */
        $do->value('testMail');
        $submit->value('发送邮件');

        /** 添加规则 */
        $to->addRule('email', _t('邮箱地址不正确'));
        $title->addRule('required', _t('必须填写邮件标题'));
        $content->addRule('required', _t('必须填写邮件内容'));

        return $form;
    }
}
