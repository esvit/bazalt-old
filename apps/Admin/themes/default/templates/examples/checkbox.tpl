<h1>Examples: Checkboxes & radiobuttons</h1>
{jsmodule name="jsmallcheck"}

<div class="dev-panel">

    <div class="smallcheck">
        <h3>Default skin</h3>
        <p><label><input name="checkbox.1.1" type="checkbox" onclick="var j = jQuery('#check').attr('disabled', jQuery('#check').attr('disabled') ? false : true)"> Unchecked checkbox (by clicking on this checkbox you can disable/enable the checkbox below)</label></p>

        <p><input name="checkbox.1.2" type="checkbox" id="check" checked> <label for="check">Checked  checkbox (this one)</label></p>

        <p><input name="checkbox.1.3" type="checkbox" disabled> Disabled & unchecked checkbox</p>
        <p><input name="checkbox.1.4" type="checkbox" disabled checked> Disabled & checked checkbox</p>

        <h3>Radio button (wrapped in &lt;label&gt;)</h3>
        <p><label><input name="radio.1" value="1" type="radio"> 1st radio button</label></p>
        <p><label><input name="radio.1" value="2" type="radio" checked> 2nd radio button</label></p>
        <p><label><input name="radio.1" value="3" type="radio"> 3rd radio button</label></p>
    </div>
    <br />
    <fieldset>
        <legend>Which genres do you like?</legend>
        <input type="checkbox" name="genre" id="check-1" value="action" />
        <label for="check-1">Action / Adventure</label>
        
        <input type="checkbox" name="genre" id="check-2" value="comedy" />

        <label for="check-2">Comedy</label>
        
        <input type="checkbox" name="genre" id="check-3" value="epic" />
        <label for="check-3">Epic / Historical</label>
        
        <input type="checkbox" name="genre" id="check-4" value="science" />
        <label for="check-4">Science Fiction</label>
        
        <input type="checkbox" name="genre" id="check-5" value="romance" />
        <label for="check-5">Romance</label>

        
        <input type="checkbox" name="genre" id="check-6" value="western" />
        <label for="check-6">Western</label>
    </fieldset>
    <br />
    <fieldset>
        <legend>Caddyshack is the greatest movie of all time, right?</legend>
        <input type="radio" name="opinions" id="radio-1" value="1" />
        <label for="radio-1">Totally</label>

        
        <input type="radio" name="opinions" id="radio-2" value="1" />
        <label for="radio-2">You must be kidding</label>
        
        <input type="radio" name="opinions" id="radio-3" value="1" />
        <label for="radio-3">What's Caddyshack?</label>
    </fieldset>

</div>

{literal}
  <script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        $('fieldset input').customInput();
    
        $('.smallcheck input:checkbox').checkbox({empty: '/admin/media/images/empty.png'});
        $('.smallcheck input:radio').checkbox({empty: '/admin/media/images/empty.png'});
    });
  </script>
{/literal}