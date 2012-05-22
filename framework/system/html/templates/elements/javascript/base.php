Html_FormElement = function() {
    this.init = function(id, className) {
        this.id = id;
        this.element = $('#' + id);
        this.className = className;

        //this.log('Added element "%s" with id "%s"', this.className, this.id);
        
        return this;
    }
    this.initElement = function() {};

    this.log = function() {
        this.history = this.history || [];   // store logs to an array for reference
        this.history.push(arguments);
        if (console) {
            arguments.callee = arguments.callee.caller;
            var newarr = [].slice.call(arguments);
            (typeof console.log === 'object' ? this.apply.call(console.log, console, newarr) : console.log.apply(console, newarr));
        }
    };

    this.ajaxCall = function() {
        var params = [].slice.call(arguments)
        var method = params.shift();
        var callback = params.pop();
        if (this.container) {
            this.container.ajaxCall(method, params, this.id, callback);
        } else {
            console.error('Container for element not found');
        }
    };
};

Html_Form = function(id, className) {
    this.elements = {};

    this.addElement = function(name, element, className) {
        element.container = this;
        element.init(name, className);
        this.elements[name] = element;
    };
    
    this.initElement = function() {
        for(e in this.elements) {
            this.elements[e].initElement();
            //this.log('Init element "%s"', e);
        }
        $('body').trigger('formInit', this);
        var e = $.Event("formInit");
        e.handleObj = this;
        this.formInit(e);
    };
    
    this.formInit = function(form) {};

    this.ajaxCall = function(method, params, element, callback) {
        $.post("?", { 'form': this.id, 'method': method, 'params': params, 'element': element }, callback, 'json')
        .error(function(res) {
            switch (res.status) {
                case 500:
                case 403:
                    if (typeof console != 'undefined' && typeof console.error == 'function') {
                        var info =  "Response: " + res.responseText + "\n" +
                                    "Method: " + method + "\n";
                        console.error("Internal Server Error\n\n", info);
                    }
                    return;
                default:
                    break;
            }
            alert("error"); 
        })
    };
};

Html_Form.prototype = new Html_FormElement();
Html_Form.prototype.constructor = Html_Form;
Html_Form.superclass = Html_FormElement.prototype; 

window.<?php echo $form->getOriginalName(); ?> = new Html_Form();
window.<?php echo $form->id(); ?> = window.<?php echo $form->getOriginalName(); ?>;