<div class="bz-form-row bz-imageuploader">
    <?php echo $element->renderLabel(); ?>
    <div <?php echo $element->getAttributesString(); ?>></div>
    <div class="bz-image"></div>
    <div class="spacer"></div>
    <?php echo $element->renderError(); ?>
</div>

<script id="PhotoTpl" type="text/x-jquery-tmpl">
    <div class="image-container" id="p_${photo.id}">
        <div class="image ui-corner-all">
            <span class="title">${photo.title}</span>
            {{if photo.url}}
            <img src="${photo.thumb}" />
            <input type="hidden" name="<?php echo $element->name(); ?>[]" value="${photo.url}" />
            {{else}}
            <div class="bz-image-preloading"></div>
            <div class="progressbar"></div>
            {{/if}}
        </div>
        <div class="clear"></div>
        {{if photo.url}}
        <div class="info">
            <div class="transparent"></div>
            <a title="${photo.title}" href="${photo.url}" class="photo-preview">
                <span class="ui-icon ui-icon-zoomin"></span> Preview original size
            </a>
            <a href="javascript: void(null);" class="bz-gallery-delete">
                <span class="ui-icon ui-icon-trash"></span> Delete image
            </a>
        </div>
        {{/if}}
    </div>
</script>


<div class="modal hide fade" id="photo-deleting" style="display: none;">
    <div class="modal-header">
        <a class="close" href="#">?</a>
        <h3>Deleting image...</h3>
    </div>
    <div class="modal-body">
        <p>Are you realy want to delete this image?</p>
    </div>
    <div class="modal-footer">
        <a class="btn secondary" href="#" onclick="$(this).parents('.modal').modal('hide'); return false;">No</a>
        <a class="btn primary" href="#" onclick="var el = $(this).parents('.modal').data('btn'); el.remove(); $(this).parents('.modal').modal('hide'); return false;">Yes</a>
    </div>
</div>