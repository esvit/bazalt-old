angular.module('ace', []).directive('ace', function() {
  var ACE_EDITOR_CLASS = 'ace-editor';

  
    var event = ace.require("ace/lib/event");
  
  function loadAceEditor(element, mode) {
    var container = $(element).find('.' + ACE_EDITOR_CLASS)[0];
    var editor = ace.edit(container);
    editor.setShowInvisibles(true); // show hidden symbols
    editor.setFadeFoldWidgets(true);
    editor.session.setMode("ace/mode/" + mode);
    editor.session.setUseSoftTabs(true) 
    editor.renderer.setShowPrintMargin(false);
    
    
    event.addListener(container, "dragover", function(e) {
        var types = e.dataTransfer.types;
        if (types && Array.prototype.indexOf.call(types, 'Files') !== -1)
            return event.preventDefault(e);
    });

    event.addListener(container, "drop", function(e) {
        var file;
        try {
            file = e.dataTransfer.files[0];
            if (window.FileReader) {
                var reader = new FileReader();
                reader.onload = function() {
                    //var mode = modelist.getModeForPath(file.name);

                    editor.session.doc.setValue(reader.result);
                    //modeEl.value = mode.name;
                    //env.editor.session.setMode(mode.mode);
                    //env.editor.session.modeName = mode.name;
                };
                reader.readAsText(file);
            }
            return event.preventDefault(e);
        } catch(err) {
            return event.stopEvent(e);
        }
    });

    return editor;
  }

  function valid(editor) {
    return (Object.keys(editor.getSession().getAnnotations()).length == 0);
  }

  return {
    restrict: 'A',
    require: '?ngModel',
    transclude: true,
    template: '<div class="transcluded" ng-transclude></div><div class="' + ACE_EDITOR_CLASS + '"></div>',

    link: function(scope, element, attrs, ngModel) {
      var textarea = $(element).find('textarea');
      textarea.hide();

      var mode = attrs.ace;
      var editor = loadAceEditor(element, 'text');

      scope.$watch(attrs.ace, function(mode) {
        if (mode)
            editor.getSession().setMode("ace/mode/" + mode);
      });

      scope.ace = editor;

      if (!ngModel) return; // do nothing if no ngModel

      ngModel.$render = function() {
        var value = ngModel.$viewValue || '';
        editor.getSession().setValue(value);
        textarea.val(value);
      };

      editor.getSession().on('change', function() {
      
        if (valid(editor)) {
            if (!scope.$$phase)
                scope.$apply(read);
            else
                read();
        }
      });

      editor.getSession().setValue(textarea.val());
      read();

      function read() {
        ngModel.$setViewValue(editor.getValue());
        textarea.val(editor.getValue());
      }
    }
  }
});

