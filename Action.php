<?php

/**
 * CommentToMail Plugin
 * 网页监控发送提醒邮件到博主或访客的邮箱
 * 二开：https://blog.warhut.cn/dmbj/1136.html
 * 
 * @copyright  Copyright (c) 2020 Byends (https://blog.uniartisan.com)
 * @license    GNU General Public License 3.0
 * @version    5.0.0
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/lib/PHPMailer.php';
require_once __DIR__ . '/lib/SMTP.php';
require_once __DIR__ . '/lib/Exception.php';

class CommentToMail_Action extends Typecho_Widget implements Widget_Interface_Do
{
    /** @var  数据操作对象 */
    private $_db;
    private $_prefix;

    /** @var  插件根目录 */
    private $_dir;

    /** @var  插件配置信息 */
    private $_cfg;

    /** @var  系统配置信息 */
    private $_options;

    /** @var 当前登录用户 */
    private $_user;

    /** @var  邮件内容信息 */
    private $_email;

    public function deliverMail($key)
    {
        if ($key != $this->_cfg->key) {
            $this->response->throwJson(array(
                'result' => 0,
                'msg' => 'No permission'
            ));
        }

        try {
            $mailQueue = $this->_db->fetchAll($this->_db->select('id', 'content')->from($this->_prefix . 'mail')
                ->where('sent = ?', 0));

            $success_id = array();
            $fail_id = array();

            foreach ($mailQueue as &$mail) {
                $mailInfo = null;
                $is_success = false;

                try {
                    // 解码邮件信息
                    $mailContent = base64_decode($mail['content'], true);
                    if ($mailContent === false) {
                        array_push($fail_id, $mail['id']);
                        continue;
                    }

                    $mailInfo = unserialize($mailContent);
                    if ($mailInfo === false) {
                        array_push($fail_id, $mail['id']);
                        continue;
                    }

                    // 验证邮件信息完整性
                    if (!isset($mailInfo->cid, $mailInfo->coid, $mailInfo->created, $mailInfo->author, $mailInfo->mail)) {
                        array_push($fail_id, $mail['id']);
                        continue;
                    }

                    // 发送邮件
                    if ($this->processMail($mailInfo)) {
                        $this->_db->query($this->_db->update($this->_prefix . 'mail')
                            ->rows(array('sent' => 1))
                            ->where('id = ?', $mail['id']));
                        $is_success = true;
                    }
                } catch (Exception $e) {
                    $is_success = false;
                }

                /** 记录结果 */
                if ($is_success) {
                    array_push($success_id, $mail['id']);
                } else {
                    array_push($fail_id, $mail['id']);
                }

                /** 排队反垃圾 */
                if (in_array('force_wait', $this->_cfg->other)) {
                    $waitingTime = (int)$this->_cfg->force_waiting_time;
                    if ($waitingTime > 0) {
                        sleep($waitingTime);
                    }
                }
            }

            // 返回结果
            $this->response->throwJson(array(
                'result' => true,
                'amount' => count($mailQueue),
                'success' => array(
                    'amount' => count($success_id),
                    'id' => $success_id
                ),
                'fail' => array(
                    'amount' => count($fail_id),
                    'id' => $fail_id
                )
            ));
        } catch (Typecho_Db_Exception $e) {
            $this->response->throwJson(array(
                'result' => 0,
                'msg' => 'Database error: ' . $e->getMessage()
            ));
        } catch (Exception $e) {
            $this->response->throwJson(array(
                'result' => 0,
                'msg' => 'Error: ' . $e->getMessage()
            ));
        }
    }

    public function processMail($mailInfo)
    {
        $this->_email = $mailInfo;
        $success = false;

        // 初始化邮件配置
        $this->_email->from = $this->_cfg->user;
        $this->_email->fromName = $this->_cfg->fromName ? $this->_cfg->fromName : $this->_email->siteTitle;
        $this->_email->titleForOwner = $this->_cfg->titleForOwner;
        $this->_email->titleForGuest = $this->_cfg->titleForGuest;

        // 验证博主是否接收自己的邮件
        $isSelfComment = ($this->_email->ownerId == $this->_email->authorId);

        // 向博主发信（新评论）
        if (0 == $this->_email->parent) {
            if (
                in_array($this->_email->status, $this->_cfg->status) && in_array('to_owner', $this->_cfg->other)
                && !$isSelfComment
            ) {
                // 获取博主邮箱
                if (empty($this->_cfg->mail)) {
                    $user = Typecho_Widget::widget('Widget_Users_Author@temp' . $this->_email->cid, array('uid' => $this->_email->ownerId))->to($user);
                    $this->_email->to = $user->mail;
                } else {
                    $this->_email->to = $this->_cfg->mail;
                }

                if (!empty($this->_email->to)) {
                    $this->authorMail()->sendMail();
                    $success = true;
                }
            }
        }

        // 向访客发信（回复评论）
        if (0 != $this->_email->parent) {
            if (
                'approved' == $this->_email->status
                && in_array('to_guest', $this->_cfg->other)
            ) {
                // 获取被回复者信息
                $original = $this->_db->fetchRow($this->_db->select('author', 'mail', 'text')
                    ->from('table.comments')
                    ->where('coid = ?', $this->_email->parent));

                if (!empty($original) && !empty($original['mail']) && $this->_email->mail != $original['mail']) {
                    // 获取联系邮箱
                    if (empty($this->_email->contactme)) {
                        $user = Typecho_Widget::widget('Widget_Users_Author@temp' . $this->_email->cid, array('uid' => $this->_email->ownerId))->to($user);
                        $this->_email->contactme = $user->mail;
                    } else {
                        $this->_email->contactme = $this->_cfg->contactme;
                    }

                    // 设置访客邮件信息
                    $this->_email->to = $original['mail'];
                    $this->_email->originalText = $original['text'];
                    $this->_email->originalAuthor = $original['author'];

                    $this->guestMail()->sendMail();
                    $success = true;
                }
            }
        }

        return $success;
    }

    /**
     * 作者邮件信息
     * @return $this
     */
    public function authorMail()
    {
        $this->_email->toName = $this->_email->siteTitle;
        $date = new Typecho_Date($this->_email->created);
        $time = $date->format('Y-m-d H:i:s');
        $status = array(
            "approved" => '通过',
            "waiting"  => '待审',
            "spam"     => '垃圾'
        );

        // 使用关联数组进行更可靠的变量替换
        $variables = array(
            '{siteTitle}' => $this->_email->siteTitle,
            '{title}' => $this->_email->title,
            '{author}' => $this->_email->author,
            '{ip}' => $this->_email->ip,
            '{mail}' => $this->_email->mail,
            '{permalink}' => $this->_email->permalink,
            '{manage}' => $this->_email->manage,
            '{text}' => $this->_email->text,
            '{time}' => $time,
            '{status}' => $status[$this->_email->status]
        );

        // 先替换较长的变量名，再替换较短的变量名
        $template = $this->getTemplate('owner');
        $subject = $this->_email->titleForOwner;

        foreach ($variables as $var => $value) {
            $template = str_replace($var, $value, $template);
            $subject = str_replace($var, $value, $subject);
        }

        $this->_email->msgHtml = $template;
        $this->_email->subject = $subject;
        $this->_email->altBody = "作者：" . $this->_email->author . "\r\n链接：" . $this->_email->permalink . "\r\n评论：\r\n" . $this->_email->text;

        return $this;
    }

    /**
     * 访问邮件信息
     * @return $this
     */
    public function guestMail()
    {
        $this->_email->toName = $this->_email->originalAuthor ? $this->_email->originalAuthor : $this->_email->siteTitle;
        $date    = new Typecho_Date($this->_email->created);
        $time    = $date->format('Y-m-d H:i:s');
        // 使用关联数组进行更可靠的变量替换
        $variables = array(
            '{siteTitle}' => $this->_email->siteTitle,
            '{title}' => $this->_email->title,
            '{author_p}' => $this->_email->originalAuthor,
            '{author}' => $this->_email->author,
            '{permalink}' => $this->_email->permalink,
            '{text}' => $this->_email->text,
            '{contactme}' => $this->_email->contactme,
            '{text_p}' => $this->_email->originalText,
            '{time}' => $time
        );

        // 先替换较长的变量名，再替换较短的变量名
        $template = $this->getTemplate('guest');
        $subject = $this->_email->titleForGuest;

        foreach ($variables as $var => $value) {
            $template = str_replace($var, $value, $template);
            $subject = str_replace($var, $value, $subject);
        }

        $this->_email->msgHtml = $template;
        $this->_email->subject = $subject;
        $this->_email->altBody = "作者：" . $this->_email->author . "\r\n链接：" . $this->_email->permalink . "\r\n评论：\r\n" . $this->_email->text;

        return $this;
    }

    /*
     * 发送邮件
     */
    public function sendMail()
    {
        /** 载入邮件组件 */
        $mailer = new PHPMailer(true); // 使用true启用异常
        $mailer->CharSet = 'UTF-8';
        $mailer->Encoding = 'base64';

        try {
            /** 选择发信模式 */
            switch ($this->_cfg->mode) {
                case 'mail':
                    $mailer->isMail();
                    break;
                case 'sendmail':
                    $mailer->isSendmail();
                    break;
                case 'smtp':
                    $mailer->isSMTP();

                    if (in_array('validate', $this->_cfg->validate)) {
                        $mailer->SMTPAuth = true;
                    }

                    if (in_array('ssl', $this->_cfg->validate)) {
                        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else if (in_array('tls', $this->_cfg->validate)) {
                        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    } else {
                        $mailer->SMTPSecure = false;
                    }

                    $mailer->Host     = $this->_cfg->host;
                    $mailer->Port     = (int)$this->_cfg->port;
                    $mailer->Username = $this->_cfg->user;
                    $mailer->Password = $this->_cfg->pass;

                    // 配置SMTP选项
                    $mailer->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    break;
            }

            $mailer->setFrom($this->_email->from, $this->_email->fromName);
            $mailer->addReplyTo($this->_email->to, $this->_email->toName);
            $mailer->Subject = $this->_email->subject;
            $mailer->AltBody = $this->_email->altBody;
            if (in_array('solve544', $this->_cfg->validate)) {          /* 躲避审查造成的 544 错误 */
                $mailer->addCC($this->_email->from);
            }
            $mailer->msgHTML($this->_email->msgHtml);
            $mailer->addAddress($this->_email->to, $this->_email->toName);

            $result = $mailer->send();
            return $result;
        } catch (Exception $e) {
            $error = $e->getMessage();
            return $error;
        } finally {
            // 清理资源
            $mailer->clearAddresses();
            $mailer->clearReplyTos();
            $mailer->clearAttachments();
        }
    }



    /*
     * 获取邮件正文模板
     * $author owner为博主 guest为访客
     */
    public function getTemplate($template = 'owner')
    {
        // 只允许owner或guest模板
        if (!in_array($template, ['owner', 'guest'])) {
            $template = 'owner';
        }

        $template .= '.html';
        $filename = $this->_dir . '/' . $template;

        if (!file_exists($filename)) {
            throw new Typecho_Widget_Exception('模板文件' . $template . '不存在', 404);
        }

        if (!is_readable($filename)) {
            throw new Typecho_Widget_Exception('模板文件' . $template . '不可读取', 403);
        }

        return file_get_contents($filename);
    }




    /**
     * 邮件发送测试
     */
    public function testMail()
    {
        if (Typecho_Widget::widget('CommentToMail_Console')->testMailForm()->validate()) {
            $this->response->goBack();
        }

        $this->init();
        $email = $this->request->from('toName', 'to', 'title', 'content');

        $this->_email->from = $this->_cfg->user;
        $this->_email->fromName = $this->_cfg->fromName ? $this->_cfg->fromName : $this->_options->title;
        $this->_email->to = $email['to'] ? $email['to'] : $this->_user->mail;
        $this->_email->toName = $email['toName'] ? $email['toName'] : $this->_user->screenName;
        $this->_email->subject = $email['title'];
        $this->_email->altBody = $email['content'];
        $this->_email->msgHtml = $email['content'];

        $result = $this->sendMail();

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(
            true === $result ? _t('邮件发送成功') : _t('邮件发送失败：' . $result),
            true === $result ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->goBack();
    }

    /**
     * 编辑模板文件
     * @param $file
     * @throws Typecho_Widget_Exception
     */
    public function editTheme($file)
    {
        $this->init();
        $path = $this->_dir . '/' . $file;

        if (file_exists($path) && is_writeable($path)) {
            $handle = fopen($path, 'wb');
            if ($handle && fwrite($handle, $this->request->content)) {
                fclose($handle);
                $this->widget('Widget_Notice')->set(_t("文件 %s 的更改已经保存", $file), 'success');
            } else {
                $this->widget('Widget_Notice')->set(_t("文件 %s 无法被写入", $file), 'error');
            }
            $this->response->goBack();
        } else {
            throw new Typecho_Widget_Exception(_t('您编辑的模板文件不存在'));
        }
    }

    /**
     * 初始化
     * @return $this
     */
    public function init()
    {
        $this->_dir = dirname(__FILE__);
        $this->_db = Typecho_Db::get();
        $this->_prefix = $this->_db->getPrefix();

        $this->_user = $this->widget('Widget_User');
        $this->_options = $this->widget('Widget_Options');
        $this->_cfg = Helper::options()->plugin('CommentToMail');
    }

    /**
     * action 入口
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->init();
        $this->on($this->request->is('do=testMail'))->testMail();
        $this->on($this->request->is('do=editTheme'))->editTheme($this->request->edit);
        $this->on($this->request->is('do=deliverMail'))->deliverMail($this->request->key);
    }
}
