<div class="wrap aiwriter-row">
    <form name="aiwriter-request" method="post" action="">
        <?php wp_nonce_field( 'generate_content', 'generate_content_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="title">Prompt</label></th>
                <td><textarea name="title"  rows="6" style="width:100%;" placeholder="Generated content will be based on this prompt..." required></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="heading_no">Number of titles</label></th>
                <td><input name="heading_no" type="number" id="heading_no" value="" min="1" max="10" placeholder="3" required></td>
            </tr>
        </table>
        <?php aiwriter_content_submit_button(); ?>
    </form>
    <form class="aiwriter-postform" name="aiwriter-post" method="post" action="" style="display:none;">
        <?php wp_nonce_field( 'create_post', 'create_post_nonce' ); ?>
        <h3 class="wp-heading-inline">AI Content</h3>
        <div id="titlewrap">
            <input type="text" size="30" class="regular-text ai-post-title" name="post_title" id="post_title" spellcheck="true" value="">
        </div>
        <?php
        $content = '';
        $custom_editor_id = "post_content_ifr";
        $custom_editor_name = "post_content";
        $args = array(
            'media_buttons' => true, // This setting removes the media button.
            'textarea_name' => $custom_editor_name, // Set custom name.
            'quicktags' => false, // Remove view as HTML button.
            'editor_height' => 425, // In pixels, takes precedence and has no default value
            'textarea_rows' => 20,  // Has no visible effect if editor_height is set, default is 20
        );
        wp_editor( $content, $custom_editor_id, $args );
        ?>
        <p class="aiwriter-ploading">
            <span></span>
        </p>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="post_type">Post type</label></th>
                <td>
                    <select name="post_type" id="post_type">
                        <option value="post">Post</option>
                        <option value="page">Page</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="post_status">Publish?</label></th>
                <td>
                    <select name="post_status" id="post_status">
                        <option value="draft">Draft</option>
                        <option value="publish">Publish</option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit aiwriter-post">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Create Post" disabled>
        </p>
    </form>
</div>
