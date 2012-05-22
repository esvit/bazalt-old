<?php
    $id = $element->id();
    $className = get_class($element);
?>

<?php echo $className; ?> = function(id, className) {
    this.loader = null;
    this.progress = null;

    this.initElement = function() {
        var self = this;
        this.uploader = new qq.FileUploader({
            element: this.element.get(0),
            allowedExtensions: [<?php echo $element->getAllowedExtensions(); ?>],
            action: location.pathname,
            multiple: false,
            template: '<div class="qq-uploader">' +
                          '<div class="qq-upload-drop-area"><span><?php echo __('Drop files here to upload', 'CMS'); ?></span></div>' +
                          '<ul class="qq-upload-list"></ul>' +
                          '<a class="qq-upload-button" href="javascript:;"> <?php echo __('Upload', 'CMS'); ?></a>' +
                      '</div>',
            params: <?php echo $element->buildParams(); ?>,
            messages: {
                typeError: "<?php echo __('{file} has invalid extension. Only {extensions} are allowed.', 'CMS'); ?>",
                sizeError: "<?php echo __('{file} is too large, maximum file size is {sizeLimit}.', 'CMS'); ?>",
                minSizeError: "<?php echo __('{file} is too small, minimum file size is {minSizeLimit}.', 'CMS'); ?>",
                emptyError: "<?php echo __('{file} is empty, please select files again without it.', 'CMS'); ?>",
                onLeave: "<?php echo __('The files are being uploaded, if you leave now the upload will be cancelled.', 'CMS'); ?>"
            },
            onSubmit: function(id, fileName) {
                self.loader = $.pnotify({
                    pnotify_title: 'Upload ' + fileName,
                    pnotify_text: "<div class=\"progress_bar\" />",
                    pnotify_notice_icon: "picon picon-throbber",
                    pnotify_hide: false,
                    pnotify_closer: false,
                    pnotify_sticker: false,
                    pnotify_history: false,
                    pnotify_before_open: function(pnotify) {
                        self.progress = pnotify.find("div.progress_bar");
                        self.progress.progressbar({
                            value: 0
                        });
                    }
                });
            
                $('.qq-upload-list .qq-upload-success').remove();
            },
            onProgress: function(id, fileName, loaded, total){
                self.progress.progressbar("option", "value", Math.round(loaded / total * 100));
            },
            onComplete: function(id, fileName, responseJSON) {
                self.loader.pnotify_remove();
                if (responseJSON.success) {
                    var photo = responseJSON.url;
                    $('input[name="<?php echo $element->name(); ?>"]').val(photo);
                    var file = $('.qq-upload-list .qq-upload-file');
                    file.html('<a target="_blank" href="' + photo + '">' + file.text() + '</a>');
                    
                    file.after('<a href="#" class="bz-uploader-delete"><?php echo __('Delete', 'CMS'); ?></a>')
                }
            }
        });
        $('.bz-uploader-delete').live('click', function(){
             $('input[name="<?php echo $element->name(); ?>"]').val('');
             $(this).parent().remove();
             return false;
        });
    };

    this.value = function(value) {
        if (typeof value == 'undefined') {
            return this.element.val();
        }
        return this.element.val(value);
    };

    <?php echo $element->getAjaxMethodsJs(); ?>
};
<?php echo $className; ?>.prototype = new Html_FormElement();
<?php echo $className; ?>.prototype.constructor = <?php echo $className; ?>;
<?php echo $className; ?>.superclass = Html_FormElement.prototype;