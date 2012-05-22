<h1>Examples: Color Picker</h1>
{jsmodule name="colorpicker"}
<div class="dev-panel">

    <p>Flat mode.</p>
    <p id="colorpickerHolder">
    </p>
    <pre>
$('#colorpickerHolder').ColorPicker({flat: true});
    </pre>
    <p>Custom skin and using flat mode to display the color picker in a custom widget.</p>
    <div id="customWidget">
        <div id="colorSelector2"><div style="background-color: #00ff00"></div></div>
        <div id="colorpickerHolder2">
        </div>
    </div>

    <p>Attached to an text field and using callback functions to update the color with field's value and set the value back in the field by submiting the color.</p>
    <p><input type="text" maxlength="6" size="6" id="colorpickerField1" value="00ff00" /></p>
    <p><input type="text" maxlength="6" size="6" id="colorpickerField3" value="0000ff" /></p>
    <p><input type="text" maxlength="6" size="6" id="colorpickerField2" value="ff0000" /></p>
    <pre>$('#colorpickerField1, #colorpickerField2, #colorpickerField3').ColorPicker({
	onSubmit: function(hsb, hex, rgb, el) {
		$(el).val(hex);
		$(el).ColorPickerHide();
	},
	onBeforeShow: function () {
		$(this).ColorPickerSetColor(this.value);
	}
})
.bind('keyup', function(){
	$(this).ColorPickerSetColor(this.value);
});
</pre>
<p>Attached to DOMElement and using callbacks to live preview the color and adding animation.</p>
<p>
    <div id="colorSelector"><div style="background-color: #0000ff"></div></div>
</p>
<pre>
$('#colorSelector').ColorPicker({
	color: '#0000ff',
	onShow: function (colpkr) {
		$(colpkr).fadeIn(500);
		return false;
	},
	onHide: function (colpkr) {
		$(colpkr).fadeOut(500);
		return false;
	},
	onChange: function (hsb, hex, rgb) {
		$('#colorSelector div').css('backgroundColor', '#' + hex);
	}
});
</pre>

<h3>Invocation code</h3>
<p>All you have to do is to select the elements in a jQuery way and call the plugin.</p>
<pre>
$('input').ColorPicker(options);
</pre>
<h3>Options</h3>
<p>A hash of parameters. All parameters are optional.</p>
<table>
    <tr>
        <td><strong>eventName</strong></td>
        <td>string</td>
        <td>The desired event to trigger the colorpicker. Default: 'click'</td>
    </tr>
    <tr>
        <td><strong>color</strong></td>
        <td>string or hash</td>
        <td>The default color. String for hex color or hash for RGB and HSB ({r:255, r:0, b:0}) . Default: 'ff0000'</td>
    </tr>
    <tr>
        <td><strong>flat</strong></td>
        <td>boolean</td>
        <td>Whatever if the color picker is appended to the element or triggered by an event. Default false</td>
    </tr>
    <tr>
        <td><strong>livePreview</strong></td>
        <td>boolean</td>
        <td>Whatever if the color values are filled in the fields while changing values on selector or a field. If false it may improve speed. Default true</td>
    </tr>
    <tr>
        <td><strong>onShow</strong></td>
        <td>function</td>
        <td>Callback function triggered when the color picker is shown</td>
    </tr>
    <tr>
        <td><strong>onBeforeShow</strong></td>
        <td>function</td>
        <td>Callback function triggered before the color picker is shown</td>
    </tr>
    <tr>
        <td><strong>onHide</strong></td>
        <td>function</td>
        <td>Callback function triggered when the color picker is hidden</td>
    </tr>
    <tr>
        <td><strong>onChange</strong></td>
        <td>function</td>
        <td>Callback function triggered when the color is changed</td>
    </tr>
    <tr>
        <td><strong>onSubmit</strong></td>
        <td>function</td>
        <td>Callback function triggered when the color it is chosen</td>
    </tr>
</table>
<h3>Set color</h3>
<p>If you want to set a new color.</p>
<pre>$('input').ColorPickerSetColor(color);</pre>
<p>The 'color' argument is the same format as the option color, string for hex color or hash for RGB and HSB ({r:255, r:0, b:0}).</p>

</div>
{literal}
<script>
(function($){
var initLayout = function() {
    var hash = window.location.hash.replace('#', '');

    $('#colorpickerHolder').ColorPicker({flat: true});
    $('#colorpickerHolder2').ColorPicker({
        flat: true,
        color: '#00ff00',
        onSubmit: function(hsb, hex, rgb) {
            $('#colorSelector2 div').css('backgroundColor', '#' + hex);
        }
    });
    $('#colorpickerHolder2>div').css('position', 'absolute');
    var widt = false;
    $('#colorSelector2').bind('click', function() {
        $('#colorpickerHolder2').stop().animate({height: widt ? 0 : 173}, 500);
        widt = !widt;
    });
    $('#colorpickerField1, #colorpickerField2, #colorpickerField3').ColorPicker({
        onSubmit: function(hsb, hex, rgb, el) {
            $(el).val(hex);
            $(el).ColorPickerHide();
        },
        onBeforeShow: function () {
            $(this).ColorPickerSetColor(this.value);
        }
    })
    .bind('keyup', function(){
        $(this).ColorPickerSetColor(this.value);
    });
    $('#colorSelector').ColorPicker({
        color: '#0000ff',
        onShow: function (colpkr) {
            $(colpkr).fadeIn(500);
            return false;
        },
        onHide: function (colpkr) {
            $(colpkr).fadeOut(500);
            return false;
        },
        onChange: function (hsb, hex, rgb) {
            $('#colorSelector div').css('backgroundColor', '#' + hex);
        }
    });
};
EYE.register(initLayout, 'init');
})(jQuery)
</script>
{/literal}