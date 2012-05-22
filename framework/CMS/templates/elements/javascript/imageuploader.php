<?php
    $id = $element->id();
    $className = get_class($element);
?>

<?php echo $className; ?> = function(id, className) {
    this.files = [];

    this.previewOptions = {
        transitionIn: 'elastic',
        transitionOut: 'elastic'
    };

    this.addImage = function(file, append) {
        this.files.push(file);
        this._build(file, append);
    }

    this._build = function(file, append) {
        var el;
        if(append != undefined && append == true) {
            el = $('#PhotoTpl').tmpl(file).appendTo($('#' + this.id).parent().find('.bz-image'));
        } else {
            el = $('#PhotoTpl').tmpl(file).prependTo($('#' + this.id).parent().find('.bz-image'));
        }
        el.find('.photo-preview').fancybox(this.previewOptions);
        this._bindEvents(el);
    }

    this._bindEvents = function(el) {
        var self = this;
        el.find('.bz-gallery-delete').show().click(function(){
            $('#photo-deleting').data('btn', $(this).parent().parent()).modal({ keyboard: true, backdrop: true, show: true });
            return false;
        });
        el.find('.bz-gallery-edit').show().click(function(){
                var id = $(this).parents('.bz-gallery-container').attr('id').slice(2);
                ComGallery_Webservice_Gallery.GetById(id, function(item){
                    $('#title').val(item.title);
                    $('#description').val(item.description);
                    $("#edit-form").data('curId', id).dialog("open");
                });
           });
        $('.bz-form-row .bz-image').sortable('destroy').sortable({
            appendTo: 'body',
            helper: 'clone',
            update: function(event, ui) {
                var order = self.element.parent().find('.bz-image').sortable('serialize');
                ComGallery_Webservice_Gallery.UpdateOrder(order, $('#photosCount').val());
            }
        });
    }

    this.initElement = function() {
        var self = this;
        this.uploader = new qq.FileUploader({
            maxConnections: 1,
            element: this.element.get(0),
            allowedExtensions: [<?php echo $element->getAllowedExtensions(); ?>],
            action: "?",
            template: '<div class="bz-gallery-uploader qq-uploader">' +
                            '<div class="bz-gallery-uploader-container ui-corner-all">' + 
                                '<a class="ui-corner-all qq-upload-drop-area qq-upload-button"><?php echo __('Click to upload files or drag them here', 'CMS'); ?></a>' + 
                            '</div>' + 
                            '<ul class="qq-upload-list"></ul>' + 
                      '</div>',
            params: <?php echo $element->buildParams(); ?>,

            onSubmit: function(id, fileName){
                var images = $('#' + self.id).parent().find('.bz-image');
                $("#PhotoTpl").tmpl({ photo: { id: 'new' + id, title: fileName } }).prependTo(images);

                $('.progressbar', $('#p_new' + id)).progressbar({
                    value: 1
                });
            },
            onProgress: function(id, fileName, loaded, total) {
                var perc = Math.round(loaded / total * 100);
                $('.progressbar', $('#p_new' + id)).progressbar({
                    value: perc
                });
            },
            onCancel: function(id, fileName){},

            onComplete: function(id, fileName, responseJSON) {
                $('.progressbar', $('#p_new' + id)).progressbar({
                    value: 100
                });
                if (responseJSON.success) {
                    $('#p_new' + id).remove();
                    self.addImage({ photo: responseJSON }, false);
                    $('#photosCount').val(parseInt($('#photosCount').val(), 10) + 1);
                }
            }
        });

        <?php foreach ($element->value() as $photo) { ?>
        <?php echo $element->form()->name(); ?>.elements['<?php echo $element->id(); ?>'].addImage( { photo: { url: "<?php echo $photo; ?>", filename: "<?php echo $photo; ?>", thumb: "<?php echo CMS_Image::getThumb($photo, $element->size()); ?>" } } );
        <?php } ?>

        $("#edit-form").dialog({
            autoOpen: false,
            height: 300,
            width: 350,
            modal: true,
            buttons: {
                Save: function() {
                    ComGallery_Webservice_Gallery.Update($("#edit-form").data('curId'), $('#title').val(), $('#description').val(), false, function(){
                        $('#p_'+$("#edit-form").data('curId'))
                            .find('span.title')
                            .empty().html($('#title').val());
                        $("#edit-form").dialog("close");
                    });
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            },
            close: function() {
                $('#title').val('');
                $('#description').val('');
            }
        });
    };

    this.initFancybox = function() {
        var imgOptions = { transitionIn: 'elastic', transitionOut: 'elastic'};

        this.element.parent().find('.bz-image .bz-gallery-image a').fancybox(imgOptions);
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