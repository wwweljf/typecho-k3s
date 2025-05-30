<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$post = \Widget\Contents\Post\Edit::alloc()->prepare();
?>
<main class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <form class="row typecho-page-main typecho-post-area" action="<?php $security->index('/action/contents-post-edit'); ?>" method="post" name="write_post">
            <div class="col-mb-12 col-tb-9" role="main">
                <?php if ($post->draft): ?>
                    <?php if ($post->draft['cid'] != $post->cid): ?>
                        <?php $postModifyDate = new \Typecho\Date($post->draft['modified']); ?>
                        <cite
                            class="edit-draft-notice"><?php _e('你正在编辑的是保存于 %s 的修订版, 你也可以 <a href="%s">删除它</a>', $postModifyDate->word(),
                                $security->getIndex('/action/contents-post-edit?do=deleteDraft&cid=' . $post->cid)); ?></cite>
                    <?php else: ?>
                        <cite class="edit-draft-notice"><?php _e('当前正在编辑的是未发布的草稿'); ?></cite>
                    <?php endif; ?>
                    <input name="draft" type="hidden" value="<?php echo $post->draft['cid'] ?>"/>
                <?php endif; ?>

                <p class="title">
                    <label for="title" class="sr-only"><?php _e('标题'); ?></label>
                    <input type="text" id="title" name="title" autocomplete="off" value="<?php $post->title(); ?>"
                           placeholder="<?php _e('标题'); ?>" class="w-100 text title"/>
                </p>
                <?php $permalink = \Typecho\Common::url($options->routingTable['post']['url'], $options->index);
                [$scheme, $permalink] = explode(':', $permalink, 2);
                $permalink = ltrim($permalink, '/');
                $permalink = preg_replace("/\[([_a-z0-9-]+)[^\]]*\]/i", "{\\1}", $permalink);
                if ($post->have()) {
                    $permalink = preg_replace_callback(
                        "/\{(cid|category|year|month|day)\}/i",
                        function ($matches) use ($post) {
                            $key = $matches[1];
                            return $post->getRouterParam($key);
                        },
                        $permalink
                    );
                }
                $input = '<input type="text" id="slug" name="slug" autocomplete="off" value="' . htmlspecialchars($post->slug ?? '') . '" class="mono" />';
                ?>
                <p class="mono url-slug">
                    <label for="slug" class="sr-only"><?php _e('网址缩略名'); ?></label>
                    <?php echo preg_replace("/\{slug\}/i", $input, $permalink); ?>
                </p>
                <p>
                    <label for="text" class="sr-only"><?php _e('文章内容'); ?></label>
                    <textarea style="height: <?php $options->editorSize(); ?>px" autocomplete="off" id="text"
                              name="text" class="w-100 mono"><?php echo htmlspecialchars($post->text); ?></textarea>
                </p>

                <?php include 'custom-fields.php'; ?>

                <p class="submit">
                    <span class="left">
                        <button type="button" id="btn-cancel-preview" class="btn"><i
                                class="i-caret-left"></i> <?php _e('取消预览'); ?></button>
                    </span>
                    <span class="right">
                        <input type="hidden" name="do" value="publish" />
                        <input type="hidden" name="cid" value="<?php $post->cid(); ?>"/>
                        <button type="button" id="btn-preview" class="btn"><i
                                class="i-exlink"></i> <?php _e('预览文章'); ?></button>
                        <button type="submit" name="do" value="save" id="btn-save"
                                class="btn"><?php _e('保存草稿'); ?></button>
                        <button type="submit" name="do" value="publish" class="btn primary"
                                id="btn-submit"><?php _e('发布文章'); ?></button>
                        <?php if ($options->markdown && (!$post->have() || $post->isMarkdown)): ?>
                            <input type="hidden" name="markdown" value="1"/>
                        <?php endif; ?>
                    </span>
                </p>

                <?php \Typecho\Plugin::factory('admin/write-post.php')->call('content', $post); ?>
            </div>

            <div id="edit-secondary" class="col-mb-12 col-tb-3" role="complementary">
                <ul class="typecho-option-tabs">
                    <li class="active w-50"><a href="#tab-advance"><?php _e('选项'); ?></a></li>
                    <li class="w-50"><a href="#tab-files" id="tab-files-btn"><?php _e('附件'); ?></a></li>
                </ul>


                <div id="tab-advance" class="tab-content">
                    <section class="typecho-post-option" role="application">
                        <label for="date" class="typecho-label"><?php _e('发布日期'); ?></label>
                        <p><input class="typecho-date w-100" type="text" name="date" id="date" autocomplete="off"
                                  value="<?php $post->have() && $post->created > 0 ? $post->date('Y-m-d H:i') : ''; ?>"/>
                        </p>
                    </section>

                    <section class="typecho-post-option category-option">
                        <label class="typecho-label"><?php _e('分类'); ?></label>
                        <?php \Widget\Metas\Category\Rows::alloc()->to($category); ?>
                        <ul>
                            <?php $categories = array_column($post->categories, 'mid'); ?>
                            <?php while ($category->next()): ?>
                                <li><?php echo str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $category->levels); ?><input
                                        type="checkbox" id="category-<?php $category->mid(); ?>"
                                        value="<?php $category->mid(); ?>" name="category[]"
                                        <?php if (in_array($category->mid, $categories)): ?>checked="true"<?php endif; ?>/>
                                    <label
                                        for="category-<?php $category->mid(); ?>"><?php $category->name(); ?></label>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </section>

                    <section class="typecho-post-option">
                        <label for="token-input-tags" class="typecho-label"><?php _e('标签'); ?></label>
                        <p><input id="tags" name="tags" type="text" value="<?php $post->have() ? $post->tags(',', false) : ''; ?>"
                                  class="w-100 text"/></p>
                    </section>

                    <?php \Typecho\Plugin::factory('admin/write-post.php')->call('option', $post); ?>

                    <details id="advance-panel">
                        <summary class="btn btn-xs"><?php _e('高级选项'); ?> <i class="i-caret-down"></i></summary>

                        <?php if ($user->pass('editor', true)): ?>
                            <section class="typecho-post-option visibility-option">
                                <label for="visibility" class="typecho-label"><?php _e('公开度'); ?></label>
                                <p>
                                    <select id="visibility" name="visibility">
                                        <?php if ($user->pass('editor', true)): ?>
                                            <option
                                                value="publish"<?php if (($post->status == 'publish' && !$post->password) || !$post->status): ?> selected<?php endif; ?>><?php _e('公开'); ?></option>
                                            <option
                                                value="hidden"<?php if ($post->status == 'hidden'): ?> selected<?php endif; ?>><?php _e('隐藏'); ?></option>
                                            <option
                                                value="password"<?php if (strlen($post->password ?? '') > 0): ?> selected<?php endif; ?>><?php _e('密码保护'); ?></option>
                                            <option
                                                value="private"<?php if ($post->status == 'private'): ?> selected<?php endif; ?>><?php _e('私密'); ?></option>
                                        <?php endif; ?>
                                        <option
                                            value="waiting"<?php if (!$user->pass('editor', true) || $post->status == 'waiting'): ?> selected<?php endif; ?>><?php _e('待审核'); ?></option>
                                    </select>
                                </p>
                                <p id="post-password"<?php if (strlen($post->password ?? '') == 0): ?> class="hidden"<?php endif; ?>>
                                    <label for="protect-pwd" class="sr-only">内容密码</label>
                                    <input type="text" name="password" id="protect-pwd" class="text-s"
                                           value="<?php $post->password(); ?>" size="16"
                                           placeholder="<?php _e('内容密码'); ?>" autocomplete="off"/>
                                </p>
                            </section>
                        <?php endif; ?>

                        <section class="typecho-post-option allow-option">
                            <label class="typecho-label"><?php _e('权限控制'); ?></label>
                            <ul>
                                <li><input id="allowComment" name="allowComment" type="checkbox" value="1"
                                           <?php if ($post->allow('comment')): ?>checked="true"<?php endif; ?> />
                                    <label for="allowComment"><?php _e('允许评论'); ?></label></li>
                                <li><input id="allowPing" name="allowPing" type="checkbox" value="1"
                                           <?php if ($post->allow('ping')): ?>checked="true"<?php endif; ?> />
                                    <label for="allowPing"><?php _e('允许被引用'); ?></label></li>
                                <li><input id="allowFeed" name="allowFeed" type="checkbox" value="1"
                                           <?php if ($post->allow('feed')): ?>checked="true"<?php endif; ?> />
                                    <label for="allowFeed"><?php _e('允许在聚合中出现'); ?></label></li>
                            </ul>
                        </section>

                        <section class="typecho-post-option">
                            <label for="trackback" class="typecho-label"><?php _e('引用通告'); ?></label>
                            <p><textarea id="trackback" class="w-100 mono" name="trackback" rows="2"></textarea></p>
                            <p class="description"><?php _e('每一行一个引用地址, 用回车隔开'); ?></p>
                        </section>

                        <?php \Typecho\Plugin::factory('admin/write-post.php')->call('advanceOption', $post); ?>
                    </details><!-- end #advance-panel -->

                    <?php if ($post->have()): ?>
                        <?php $modified = new \Typecho\Date($post->modified); ?>
                        <section class="typecho-post-option">
                            <p class="description">
                                <br>&mdash;<br>
                                <?php _e('本文由 <a href="%s">%s</a> 撰写',
                                    \Typecho\Common::url('manage-posts.php?uid=' . $post->author->uid, $options->adminUrl), $post->author->screenName); ?>
                                <br>
                                <?php _e('最后更新于 %s', $modified->word()); ?>
                            </p>
                        </section>
                    <?php endif; ?>
                </div><!-- end #tab-advance -->

                <div id="tab-files" class="tab-content hidden">
                    <?php include 'file-upload.php'; ?>
                </div><!-- end #tab-files -->
            </div>
        </form>
    </div>
</main>

<?php
include 'copyright.php';
include 'common-js.php';
include 'form-js.php';
include 'write-js.php';

\Typecho\Plugin::factory('admin/write-post.php')->trigger($plugged)->call('richEditor', $post);
if (!$plugged) {
    include 'editor-js.php';
}

include 'file-upload-js.php';
include 'custom-fields-js.php';
\Typecho\Plugin::factory('admin/write-post.php')->call('bottom', $post);
include 'footer.php';
?>
