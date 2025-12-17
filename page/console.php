<?php
include 'header.php';
include 'menu.php';

$current = $request->get('act', 'index');
$theme = $request->get('file', 'owner.html');
$title = $current == 'index' ? $menu->title : '编辑邮件模板 ' . $theme;

// 获取邮件队列统计信息
$db = Typecho_Db::get();
$prefix = $db->getPrefix();
$totalQueue = $db->fetchObject($db->select(array('COUNT(id)' => 'count'))->from($prefix . 'mail'))->count;
$pendingQueue = $db->fetchObject($db->select(array('COUNT(id)' => 'count'))->from($prefix . 'mail')->where('sent = ?', 0))->count;
$sentQueue = $db->fetchObject($db->select(array('COUNT(id)' => 'count'))->from($prefix . 'mail')->where('sent = ?', 1))->count;
?>

<!-- Apple风格控制台页面重构 -->
<div class="main">
    <div class="body container">
        <!-- 页头 -->
        <header class="apple-header">
            <h1 class="apple-title"><?=$title?></h1>
            <p class="apple-subtitle">评论邮件提醒插件控制台</p>
        </header>
        
        <!-- 统计卡片区域 -->
        <section class="apple-stats-section">
            <div class="apple-stats-grid">
                <!-- 总邮件数 -->
                <div class="apple-stat-card">
                    <div class="apple-stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 18H4V8L12 13L20 8V18ZM20 6L12 11L4 6H20Z" fill="#8E8E93"/>
                        </svg>
                    </div>
                    <h3 class="apple-stat-label">总邮件数</h3>
                    <p class="apple-stat-value"><?php echo $totalQueue; ?></p>
                </div>
                
                <!-- 待发送 -->
                <div class="apple-stat-card">
                    <div class="apple-stat-icon pending">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM13 7H11V13L16.2 16.2L17 14.9L13 12.2V7Z" fill="#FF9500"/>
                        </svg>
                    </div>
                    <h3 class="apple-stat-label">待发送</h3>
                    <p class="apple-stat-value pending"><?php echo $pendingQueue; ?></p>
                </div>
                
                <!-- 已发送 -->
                <div class="apple-stat-card">
                    <div class="apple-stat-icon success">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill="#34C759"/>
                        </svg>
                    </div>
                    <h3 class="apple-stat-label">已发送</h3>
                    <p class="apple-stat-value success"><?php echo $sentQueue; ?></p>
                </div>
                
                <!-- 处理队列 -->
                <div class="apple-stat-card action-card">
                    <div class="apple-stat-icon action">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19ZM12 8V16M8 12H16" stroke="#007AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="apple-stat-label">处理队列</h3>
                    <a href="<?php $options->index('/commentToMailProcessQueue/'); ?>" class="apple-button primary" target="_blank">
                        立即处理
                    </a>
                </div>
            </div>
        </section>
        
        <!-- 选项卡导航 -->
        <nav class="apple-tabs">
            <div class="apple-tabs-container">
                <a href="<?php $options->adminUrl('extending.php?panel=' . CommentToMail_Plugin::$panel); ?>" class="apple-tab-item <?php if($current == 'index') echo 'active'; ?>">
                    <span class="apple-tab-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 18H4V8L12 13L20 8V18ZM20 6L12 11L4 6H20Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="apple-tab-label">邮件测试</span>
                </a>
                
                <a href="<?php $options->adminUrl('extending.php?panel=' . CommentToMail_Plugin::$panel . '&act=theme'); ?>" class="apple-tab-item <?php if($current == 'theme') echo 'active'; ?>">
                    <span class="apple-tab-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.5 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14.5 2ZM18 20H6V4H13V9H18V20Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="apple-tab-label">模板编辑</span>
                </a>
                
                <a href="<?php $options->adminUrl('options-plugin.php?config=CommentToMail') ?>" class="apple-tab-item">
                    <span class="apple-tab-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19.14 12.94C19.04 12.65 18.96 12.33 18.9 12C18.96 11.67 19.04 11.35 19.14 11.06L22.71 7.49C22.9 7.3 22.99 7.01 22.99 6.71C22.99 6.4 22.9 6.13 22.71 5.93L21.07 4.29C20.87 4.1 20.58 4 20.29 4H19.5C19.22 4 18.97 4.11 18.79 4.29L15.12 7.96C14.83 7.86 14.51 7.78 14.2 7.78C13.89 7.78 13.57 7.86 13.28 7.96L9.61 4.29C9.43 4.11 9.18 4 8.9 4H8.11C7.82 4 7.53 4.1 7.33 4.29L5.69 5.93C5.5 6.13 5.41 6.4 5.41 6.71C5.41 7.01 5.5 7.3 5.69 7.49L9.26 11.06C9.16 11.35 9.08 11.67 9.08 12C9.08 12.33 9.16 12.65 9.26 12.94L5.69 16.51C5.5 16.7 5.41 16.99 5.41 17.29C5.41 17.6 5.5 17.87 5.69 18.07L7.33 19.71C7.53 19.9 7.82 20 8.11 20H8.9C9.18 20 9.43 19.89 9.61 19.71L13.28 16.04C13.57 16.14 13.89 16.22 14.2 16.22C14.51 16.22 14.83 16.14 15.12 16.04L18.79 19.71C18.97 19.89 19.22 20 19.5 20H20.29C20.58 20 20.87 19.9 21.07 19.71L22.71 18.07C22.9 17.87 22.99 17.6 22.99 17.29C22.99 16.99 22.9 16.7 22.71 16.51L19.14 12.94ZM12 14.5C10.62 14.5 9.5 13.38 9.5 12C9.5 10.62 10.62 9.5 12 9.5C13.38 9.5 14.5 10.62 14.5 12C14.5 13.38 13.38 14.5 12 14.5Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="apple-tab-label">插件设置</span>
                </a>
            </div>
        </nav>
        
        <!-- 主要内容区域 -->
        <main class="apple-content">
            <!-- 邮件测试页面 -->
            <?php if ($current == 'index'): ?>
            <div class="apple-panel">
                <div class="apple-panel-header">
                    <h2 class="apple-panel-title">邮件发送测试</h2>
                    <p class="apple-panel-description">发送测试邮件以验证您的邮件配置是否正确</p>
                </div>
                <div class="apple-panel-content">
                    <?php Typecho_Widget::widget('CommentToMail_Console')->testMailForm()->render(); ?>
                </div>
            </div>
            
            <!-- 模板编辑页面 -->
            <?php else:
                Typecho_Widget::widget('CommentToMail_Console')->to($files);
            ?>
            <div class="apple-template-editor">
                <div class="apple-editor-main">
                    <div class="apple-panel">
                        <div class="apple-panel-header">
                            <h2 class="apple-panel-title">编辑邮件模板</h2>
                            <p class="apple-panel-description">修改邮件模板内容，支持HTML格式</p>
                        </div>
                        <div class="apple-panel-content">
                            <form method="post" name="theme" id="theme" action="<?php $options->index('/action/' . CommentToMail_Plugin::$action); ?>">
                                <input type="hidden" name="do" value="editTheme" />
                                <input type="hidden" name="edit" value="<?php echo $files->currentFile(); ?>" />
                                
                                <div class="apple-editor-container">
                                    <div class="apple-editor-toolbar">
                                        <div class="apple-editor-info">
                                            <span class="apple-editor-filename"><?php echo $files->currentFile(); ?></span>
                                            <span class="apple-editor-status"><?php echo $files->currentIsWriteable() ? '可编辑' : '只读'; ?></span>
                                        </div>
                                        <div class="apple-editor-actions">
                                            <?php if($files->currentIsWriteable()): ?>
                                            <button type="button" class="apple-button secondary" onclick="previewTemplate()">
                                                预览模板
                                            </button>
                                            <button type="submit" class="apple-button primary">
                                                保存文件
                                            </button>
                                            <?php else: ?>
                                            <span class="apple-status-text error">此文件无法写入</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="apple-editor-textarea-wrapper">
                                        <textarea name="content" id="content" class="apple-editor-textarea" rows="25" <?php if(!$files->currentIsWriteable()): ?>readonly<?php endif; ?>><?php echo $files->currentContent(); ?></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="apple-editor-sidebar">
                    <div class="apple-panel">
                        <div class="apple-panel-header">
                            <h3 class="apple-panel-title">模板文件</h3>
                        </div>
                        <div class="apple-panel-content">
                            <ul class="apple-template-list">
                                <?php while($files->next()): ?>
                                <li class="apple-template-item <?php if($files->current): ?>active<?php endif; ?>">
                                    <a href="<?php $options->adminUrl('extending.php?panel=' . CommentToMail_Plugin::$panel . '&act=theme' . '&file=' . $files->file); ?>">
                                        <span class="apple-template-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14.5 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14.5 2ZM18 20H6V4H13V9H18V20Z" fill="currentColor"/>
                                            </svg>
                                        </span>
                                        <span class="apple-template-name"><?php $files->file(); ?></span>
                                    </a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Apple风格CSS样式 -->
<style>
/* Apple Design System Variables */
:root {
    /* Colors */
    --apple-blue: #007AFF;
    --apple-blue-dark: #0056CC;
    --apple-green: #34C759;
    --apple-yellow: #FFCC00;
    --apple-orange: #FF9500;
    --apple-red: #FF3B30;
    --apple-gray: #8E8E93;
    --apple-gray-light: #C7C7CC;
    --apple-gray-dark: #48484A;
    --apple-black: #000000;
    --apple-white: #FFFFFF;
    --apple-background: #F2F2F7;
    --apple-card-background: #FFFFFF;
    --apple-separator: #E5E5EA;
    
    /* Spacing */
    --apple-spacing-xs: 4px;
    --apple-spacing-sm: 8px;
    --apple-spacing-md: 16px;
    --apple-spacing-lg: 24px;
    --apple-spacing-xl: 32px;
    --apple-spacing-xxl: 48px;
    
    /* Border Radius */
    --apple-radius-sm: 8px;
    --apple-radius-md: 12px;
    --apple-radius-lg: 16px;
    --apple-radius-full: 9999px;
    
    /* Shadows */
    --apple-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --apple-shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
    --apple-shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
    
    /* Typography */
    --apple-font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    --apple-font-size-xs: 12px;
    --apple-font-size-sm: 13px;
    --apple-font-size-md: 14px;
    --apple-font-size-lg: 16px;
    --apple-font-size-xl: 18px;
    --apple-font-size-xxl: 24px;
    
    /* Transitions */
    --apple-transition-fast: 0.15s ease-in-out;
    --apple-transition-normal: 0.3s ease-in-out;
    --apple-transition-slow: 0.5s ease-in-out;
}

/* 全局样式重置 */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: var(--apple-font-family);
    font-size: var(--apple-font-size-md);
    line-height: 1.5;
    color: var(--apple-black);
    background-color: var(--apple-background);
}

/* 页头样式 */
.apple-header {
    margin-bottom: var(--apple-spacing-xl);
    padding-bottom: var(--apple-spacing-lg);
    border-bottom: 1px solid var(--apple-separator);
}

.apple-title {
    font-size: var(--apple-font-size-xxl);
    font-weight: 700;
    color: var(--apple-black);
    margin-bottom: var(--apple-spacing-xs);
}

.apple-subtitle {
    font-size: var(--apple-font-size-md);
    color: var(--apple-gray-dark);
}

/* 统计卡片样式 */
.apple-stats-section {
    margin-bottom: var(--apple-spacing-lg);
}

.apple-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--apple-spacing-md);
}

.apple-stat-card {
    background-color: var(--apple-card-background);
    border-radius: var(--apple-radius-md);
    padding: var(--apple-spacing-md);
    box-shadow: var(--apple-shadow-sm);
    transition: all var(--apple-transition-normal);
    border: 1px solid var(--apple-separator);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--apple-spacing-xs);
}

.apple-stat-card:hover {
    box-shadow: var(--apple-shadow-md);
    transform: translateY(-1px);
}

.apple-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--apple-radius-full);
    background-color: var(--apple-background);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--apple-spacing-xs);
}

.apple-stat-icon svg {
    width: 20px;
    height: 20px;
}

.apple-stat-icon.pending {
    background-color: rgba(255, 149, 0, 0.1);
}

.apple-stat-icon.success {
    background-color: rgba(52, 199, 89, 0.1);
}

.apple-stat-icon.action {
    background-color: rgba(0, 122, 255, 0.1);
}

.apple-stat-label {
    font-size: calc(var(--apple-font-size-sm) - 1px);
    color: var(--apple-gray-dark);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.apple-stat-value {
    font-size: var(--apple-font-size-xl);
    font-weight: 700;
    color: var(--apple-black);
}

.apple-stat-value.pending {
    color: var(--apple-orange);
}

.apple-stat-value.success {
    color: var(--apple-green);
}

/* 选项卡样式 */
.apple-tabs {
    margin-bottom: var(--apple-spacing-md);
    background-color: var(--apple-card-background);
    border-radius: var(--apple-radius-md);
    padding: var(--apple-spacing-xs);
    box-shadow: var(--apple-shadow-sm);
    border: 1px solid var(--apple-separator);
}

.apple-tabs-container {
    display: flex;
    gap: var(--apple-spacing-xs);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.apple-tabs-container::-webkit-scrollbar {
    display: none;
}

.apple-tab-item {
    display: flex;
    align-items: center;
    gap: var(--apple-spacing-xs);
    padding: calc(var(--apple-spacing-xs) + 4px) var(--apple-spacing-md);
    border-radius: var(--apple-radius-sm);
    text-decoration: none;
    color: var(--apple-gray-dark);
    transition: all var(--apple-transition-normal);
    white-space: nowrap;
    font-weight: 500;
    border: 1px solid transparent;
}

.apple-tab-item:hover {
    background-color: var(--apple-background);
}

.apple-tab-item.active {
    background-color: var(--apple-background);
    color: var(--apple-blue);
    border-color: var(--apple-separator);
}

.apple-tab-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.apple-tab-icon svg {
    width: 16px;
    height: 16px;
}

.apple-tab-label {
    font-size: var(--apple-font-size-sm);
}

/* 主要内容区域 */
.apple-content {
    background-color: var(--apple-card-background);
    border-radius: var(--apple-radius-md);
    box-shadow: var(--apple-shadow-sm);
    border: 1px solid var(--apple-separator);
    overflow: hidden;
}

/* 面板样式 */
.apple-panel {
    padding: var(--apple-spacing-md);
}

.apple-panel-header {
    margin-bottom: var(--apple-spacing-md);
}

.apple-panel-title {
    font-size: var(--apple-font-size-lg);
    font-weight: 600;
    color: var(--apple-black);
    margin-bottom: var(--apple-spacing-xs);
}

.apple-panel-description {
    font-size: calc(var(--apple-font-size-md) - 1px);
    color: var(--apple-gray-dark);
}

.apple-panel-content {
    /* 面板内容样式 */
}

/* 模板编辑器样式 */
.apple-template-editor {
    display: grid;
    grid-template-columns: 1fr 250px;
    gap: var(--apple-spacing-md);
    padding: var(--apple-spacing-md);
}

.apple-editor-main {
    /* 主编辑区域样式 */
}

.apple-editor-sidebar {
    /* 侧边栏样式 */
}

/* 编辑器容器 */
.apple-editor-container {
    background-color: var(--apple-background);
    border-radius: var(--apple-radius-sm);
    overflow: hidden;
    border: 1px solid var(--apple-separator);
}

/* 编辑器工具栏 */
.apple-editor-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--apple-spacing-sm);
    background-color: var(--apple-card-background);
    border-bottom: 1px solid var(--apple-separator);
}

.apple-editor-info {
    display: flex;
    align-items: center;
    gap: var(--apple-spacing-sm);
}

.apple-editor-filename {
    font-weight: 500;
    color: var(--apple-black);
    font-size: var(--apple-font-size-sm);
}

.apple-editor-status {
    font-size: calc(var(--apple-font-size-sm) - 1px);
    color: var(--apple-gray);
    background-color: var(--apple-background);
    padding: var(--apple-spacing-xs) var(--apple-spacing-sm);
    border-radius: var(--apple-radius-full);
}

.apple-editor-actions {
    display: flex;
    gap: var(--apple-spacing-sm);
    align-items: center;
}

/* 按钮样式 */
.apple-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--apple-spacing-xs);
    padding: calc(var(--apple-spacing-xs) + 2px) var(--apple-spacing-md);
    border-radius: var(--apple-radius-sm);
    font-weight: 500;
    font-size: var(--apple-font-size-sm);
    text-decoration: none;
    cursor: pointer;
    transition: all var(--apple-transition-normal);
    border: none;
    outline: none;
    min-height: 32px;
    min-width: 70px;
}

.apple-button.primary {
    background-color: var(--apple-blue);
    color: var(--apple-white);
    box-shadow: var(--apple-shadow-sm);
}

.apple-button.primary:hover {
    background-color: var(--apple-blue-dark);
    box-shadow: var(--apple-shadow-md);
    transform: translateY(-1px);
}

.apple-button.secondary {
    background-color: var(--apple-card-background);
    color: var(--apple-blue);
    border: 1px solid var(--apple-separator);
}

.apple-button.secondary:hover {
    background-color: var(--apple-background);
    border-color: var(--apple-gray-light);
}

.apple-button:active {
    transform: translateY(0);
    opacity: 0.9;
}

/* 文本域样式 */
.apple-editor-textarea-wrapper {
    position: relative;
}

.apple-editor-textarea {
    width: 100%;
    min-height: 350px;
    padding: var(--apple-spacing-md);
    font-family: 'SF Mono', Monaco, Consolas, 'Courier New', monospace;
    font-size: calc(var(--apple-font-size-sm) - 1px);
    line-height: 1.5;
    border: none;
    background-color: transparent;
    resize: vertical;
    outline: none;
    color: var(--apple-black);
}

.apple-editor-textarea:focus {
    /* 聚焦样式 */
}

.apple-editor-textarea:read-only {
    opacity: 0.7;
}

/* 模板列表样式 */
.apple-template-list {
    list-style: none;
    background-color: var(--apple-background);
    border-radius: var(--apple-radius-sm);
    overflow: hidden;
    border: 1px solid var(--apple-separator);
}

.apple-template-item {
    /* 模板列表项样式 */
}

.apple-template-item a {
    display: flex;
    align-items: center;
    gap: var(--apple-spacing-xs);
    padding: var(--apple-spacing-sm) var(--apple-spacing-md);
    text-decoration: none;
    color: var(--apple-black);
    transition: all var(--apple-transition-normal);
    border-bottom: 1px solid var(--apple-separator);
}

.apple-template-item:last-child a {
    border-bottom: none;
}

.apple-template-item a:hover {
    background-color: var(--apple-card-background);
}

.apple-template-item.active a {
    background-color: var(--apple-blue);
    color: var(--apple-white);
}

.apple-template-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.apple-template-icon svg {
    width: 16px;
    height: 16px;
}

.apple-template-name {
    font-weight: 500;
    font-size: var(--apple-font-size-sm);
}

/* 状态文本样式 */
.apple-status-text {
    font-size: var(--apple-font-size-sm);
    padding: var(--apple-spacing-xs) var(--apple-spacing-sm);
    border-radius: var(--apple-radius-full);
    font-weight: 500;
}

.apple-status-text.error {
    color: var(--apple-red);
    background-color: rgba(255, 59, 48, 0.1);
}

.apple-status-text.success {
    color: var(--apple-green);
    background-color: rgba(52, 199, 89, 0.1);
}

/* 响应式设计 */
@media (max-width: 1024px) {
    .apple-template-editor {
        grid-template-columns: 1fr;
    }
    
    .apple-editor-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .apple-stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--apple-spacing-sm);
    }
    
    .apple-panel {
        padding: var(--apple-spacing-sm);
    }
    
    .apple-template-editor {
        padding: var(--apple-spacing-sm);
        gap: var(--apple-spacing-sm);
    }
    
    .apple-editor-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: var(--apple-spacing-sm);
    }
    
    .apple-editor-actions {
        justify-content: stretch;
    }
    
    .apple-button {
        flex: 1;
        padding: calc(var(--apple-spacing-xs) + 2px) var(--apple-spacing-sm);
        font-size: calc(var(--apple-font-size-sm) - 1px);
    }
}

@media (max-width: 480px) {
    .apple-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .apple-header {
        margin-bottom: var(--apple-spacing-md);
        padding-bottom: var(--apple-spacing-sm);
    }
    
    .apple-title {
        font-size: var(--apple-font-size-lg);
    }
    
    .apple-subtitle {
        font-size: var(--apple-font-size-sm);
    }
    
    .apple-stats-section {
        margin-bottom: var(--apple-spacing-md);
    }
    
    .apple-tabs {
        margin-bottom: var(--apple-spacing-sm);
    }
    
    .apple-tab-item {
        padding: calc(var(--apple-spacing-xs) + 2px) var(--apple-spacing-sm);
        font-size: calc(var(--apple-font-size-sm) - 1px);
    }
    
    .apple-panel-title {
        font-size: var(--apple-font-size-md);
    }
    
    .apple-panel-description {
        font-size: calc(var(--apple-font-size-sm) - 1px);
    }
}

/* 动画效果 */
@keyframes apple-fadeIn {
    from {
        opacity: 0;
        transform: translateY(var(--apple-spacing-md));
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.apple-stats-grid,
.apple-tabs,
.apple-content {
    animation: apple-fadeIn 0.5s ease-out forwards;
}

/* 平滑滚动 */
html {
    scroll-behavior: smooth;
}

/* 表单样式适配 */
.typecho-option {
    margin-bottom: var(--apple-spacing-lg);
}

.typecho-option-label {
    margin-bottom: var(--apple-spacing-sm);
    font-weight: 600;
    color: var(--apple-black);
}

.typecho-option-description {
    margin-top: var(--apple-spacing-xs);
    font-size: var(--apple-font-size-sm);
    color: var(--apple-gray-dark);
}

.typecho-option input[type="text"],
.typecho-option input[type="password"],
.typecho-option textarea {
    width: 100%;
    padding: var(--apple-spacing-sm) var(--apple-spacing-md);
    border: 1px solid var(--apple-separator);
    border-radius: var(--apple-radius-sm);
    font-size: var(--apple-font-size-md);
    transition: all var(--apple-transition-normal);
    background-color: var(--apple-white);
}

.typecho-option input[type="text"]:focus,
.typecho-option input[type="password"]:focus,
.typecho-option textarea:focus {
    border-color: var(--apple-blue);
    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
    outline: none;
}

.typecho-option input[type="submit"] {
    background-color: var(--apple-blue);
    color: var(--apple-white);
    border: none;
    border-radius: var(--apple-radius-sm);
    padding: var(--apple-spacing-sm) var(--apple-spacing-lg);
    font-size: var(--apple-font-size-md);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--apple-transition-normal);
}

.typecho-option input[type="submit"]:hover {
    background-color: var(--apple-blue-dark);
    transform: translateY(-1px);
}
</style>

<script>
// Apple风格模板预览功能
function previewTemplate() {
    var content = document.getElementById('content').value;
    
    // 添加模拟数据用于预览
    var mockData = {
        '{siteTitle}': '我的博客',
        '{title}': '示例文章标题',
        '{author}': '测试用户',
        '{author_p}': '原评论者',
        '{mail}': 'test@example.com',
        '{contactme}': 'admin@example.com',
        '{ip}': '192.168.1.1',
        '{status}': '已通过',
        '{permalink}': 'https://example.com/article/1',
        '{manage}': 'https://example.com/admin/manage',
        '{text}': '这是一条测试评论内容，用于模板预览功能的演示。',
        '{text_p}': '这是原评论的内容，用于显示评论回复的上下文。',
        '{time}': '2025-12-17 14:30:00'
    };
    
    // 替换模板变量
    var previewContent = content;
    for (var key in mockData) {
        if (mockData.hasOwnProperty(key)) {
            previewContent = previewContent.replace(new RegExp(key, 'g'), mockData[key]);
        }
    }
    
    // 创建预览窗口
    var previewWindow = window.open('', 'templatePreview', 'width=900,height=700,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes');
    
    // 写入预览内容，包含Apple风格样式
    previewWindow.document.write('<!DOCTYPE html><html><head><title>模板预览 - 评论邮件提醒</title>');
    previewWindow.document.write('<style>');
    previewWindow.document.write('/* Apple风格预览样式 */');
    previewWindow.document.write('body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; margin: 0; padding: 20px; background-color: #F2F2F7; }');
    previewWindow.document.write('.preview-container { max-width: 600px; margin: 0 auto; background: white; border: 1px solid #E5E5EA; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }');
    previewWindow.document.write('.preview-header { padding: 20px; background: linear-gradient(135deg, #007AFF 0%, #0056CC 100%); color: white; text-align: center; }');
    previewWindow.document.write('.preview-header h1 { margin: 0; font-size: 20px; font-weight: 600; }');
    previewWindow.document.write('.preview-content { padding: 20px; }');
    previewWindow.document.write('.preview-footer { padding: 15px; background: #F2F2F7; text-align: center; font-size: 12px; color: #8E8E93; border-top: 1px solid #E5E5EA; }');
    previewWindow.document.write('.preview-note { background: rgba(255, 204, 0, 0.1); border: 1px solid #FFCC00; border-radius: 8px; padding: 12px; margin: 12px 0; font-size: 14px; color: #8E8E93; }');
    previewWindow.document.write('.preview-close { margin: 20px 0; text-align: center; }');
    previewWindow.document.write('.preview-close button { padding: 10px 24px; background: #007AFF; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s ease; }');
    previewWindow.document.write('.preview-close button:hover { background: #0056CC; transform: translateY(-1px); }');
    previewWindow.document.write('</style>');
    previewWindow.document.write('</head><body>');
    previewWindow.document.write('<div class="preview-container">');
    previewWindow.document.write('<div class="preview-header">');
    previewWindow.document.write('<h1>模板预览</h1>');
    previewWindow.document.write('</div>');
    previewWindow.document.write('<div class="preview-content">');
    previewWindow.document.write('<div class="preview-note">');
    previewWindow.document.write('💡 提示：这是模板预览，使用了模拟数据。实际发送时会替换为真实数据。');
    previewWindow.document.write('</div>');
    previewWindow.document.write('<div class="email-preview">');
    previewWindow.document.write(previewContent);
    previewWindow.document.write('</div>');
    previewWindow.document.write('<div class="preview-close">');
    previewWindow.document.write('<button onclick="window.close();">关闭预览</button>');
    previewWindow.document.write('</div>');
    previewWindow.document.write('</div>');
    previewWindow.document.write('<div class="preview-footer">');
    previewWindow.document.write('© 评论邮件提醒插件 - 模板预览');
    previewWindow.document.write('</div>');
    previewWindow.document.write('</div>');
    previewWindow.document.write('</body></html>');
    previewWindow.document.close();
    
    // 聚焦预览窗口
    previewWindow.focus();
}

// 添加平滑滚动效果
window.addEventListener('load', function() {
    // 平滑滚动功能
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
</script>

<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>