<?php
    $id = $element->id();
    $className = get_class($element);
?>
<?php echo $className; ?> = function(id, className) {
    this.page = 1;
    this.sortColumn = 1;
    this.sortDirection = 'up';

    this.updateData = function(callback) {
        var self = this;
        this.lock();
        this.GetPage(this.page, this.sortColumn, this.sortDirection, function(result) {
            self.unlock();

            self.element.html(result);
            self.onUpdateData();
            if(typeof(callback) != 'undefined') {
                callback();
            }
        });
    };
    
    this.onUpdateData = function() {
    };
    
    this.lock = function() {
        $('.bz-table-overlay').show();
    };
    
    this.unlock = function() {
        $('.bz-table-overlay').hide();
    };

    this.confirmDialog = function(el, title, message, callback) {
        var dialog = $('#table-confirm'),
            titleDiv = $('.dialog-title', dialog),
            messageDiv = $('.dialog-message', dialog),
            ok = $('.btn-primary', dialog);

        dialog.modal({ keyboard: true, backdrop: true, show: true });

        titleDiv.text(title);
        messageDiv.text(message);
        ok.unbind('click').click(function() {
            callback(el);
            $(this).parents('.modal').modal('hide');
            return false;
        });
    };

    this.initElement = function() {
        var self = this;

        $('.pagination li a', this.element).live('click', function() {
            self.page = $(this).data('page');
            self.updateData();

            return false;
        });

        $('.bz-table .header', this.element).live('click', function() {
            var el = $(this);
            $('.bz-table .header', self.element).not(el)
                                                .removeClass('headerSortUp headerSortDown');
            var hasSort = el.hasClass('headerSortDown');

            if (hasSort) {
                el.removeClass('headerSortDown')
                  .addClass('headerSortUp');

                self.sortDirection = 'up';
            } else {
                el.addClass('headerSortDown')
                  .removeClass('headerSortUp');

                self.sortDirection = 'down';
            }
            self.sortColumn = $('.bz-table .header', self.element).index(el) + 1;
            self.updateData();
        });
        
        $('.bz-table-header-checkbox input[type="checkbox"]', this.element).live('click', function(e) {
            var els = $('.bz-table-column-checkbox input[type="checkbox"]', this.element);
            var checked = $(this).attr('checked');
            if (checked) {
                els.attr('checked', 'checked');
            } else {
                els.removeAttr('checked');
            }
        });
        
        $('.mass').click(function(){
            var massBtn = $(this);
            var confirm = massBtn.attr('confirm');
            if(confirm != '') {
                self.confirmDialog(massBtn, confirm, confirm, function(el){
                    self.onMassAction(massBtn);
                });
            } else {
                self.onMassAction(massBtn);
            }
            
            return false;
        });
        
        this.onMassAction = function(el) {
            var column = el.attr('column');
            var action = el.attr('rel');
            var ids = [];
            $('.bz-table-column-checkbox input:checked', self.element).each(function(){
                ids.push($(this).val());
            });
            if(ids.length == 0) {
                return;
            }
            $('.mass').attr('disabled', true);
            if(column != '') {
                self.container.elements[column].ajaxCall(action, ids, function(res){
                    self.updateData(function() {
                        $('.mass').removeAttr('disabled');
                    });
                });
            } else {
                self.ajaxCall(action, ids, function(res){
                    self.updateData(function() {
                        $('.mass').removeAttr('disabled');
                    });
                });
            }
        }
    };

    <?php echo $element->getAjaxMethodsJs(); ?>
};
<?php echo $className; ?>.prototype = new Html_FormElement();
<?php echo $className; ?>.prototype.constructor = <?php echo $className; ?>;
<?php echo $className; ?>.superclass = Html_FormElement.prototype;