<h1>Examples: Selectboxes</h1>

<div class="dev-panel">

    <h2>Default: "popup" Style</h2>
    <fieldset>
        <label for="speedA">Select a Speed:</label>
        <select name="speedA" id="speedA">
            <option value="Slower">Slower</option>
            <option value="Slow">Slow</option>
            <option value="Medium" selected="selected">Medium</option>

            <option value="Fast">Fast</option>
            <option value="Faster">Faster</option>
        </select>
    </fieldset>
    
    <h2>Default: "popup" Style with maxHeight set </h2>
    <fieldset>
        <label for="speedAa">Select a Speed:</label>

        <select name="speedAa" id="speedAa">
            <option value="Slower">Slower</option>
            <option value="Slow">Slow</option>
            <option value="Medium" selected="selected">Medium</option>
            <option value="Fast">Fast</option>
            <option value="Faster">Faster</option>

            <option value="Slower">Slower</option>
            <option value="Slow">Slow</option>
            <option value="Medium" selected="selected">Medium</option>
            <option value="Fast">Fast</option>
            <option value="Faster">Faster</option>
            <option value="Slower">Slower</option>

            <option value="Slow">Slow</option>
            <option value="Medium" selected="selected">Medium</option>
            <option value="Fast">Fast</option>
            <option value="Faster">Faster</option>
        </select>
    </fieldset>
    
    <h2>Same with option text formatting</h2>

    <fieldset>
        <label for="speedB">Select an Address:</label>
        <select name="speedB" id="speedB">
            <optgroup label="Admins">
                <option>esvit|esvit666@gmail.com|Савчук Віталій</option>
                <option>botsula|botsula@gmail.com|Боцула Мирослав</option>
                <option selected="selected">aslubsky|aslubsky@gmail.com|Олександр Слубський</option>
            </optgroup>
            <optgroup label="Lamers">
                <option>Smayluk|Smayluk@gmail.com|Зайчук Віталій</option>
            </optgroup>
        </select>
    </fieldset>
    
    <h2>"dropdown" Style</h2>
    <fieldset>
        <label for="speedC">Select a Speed:</label>
        <select name="speedC" id="speedC">
            <option value="Slower" class="whoo">Slower</option>

            <option value="Slow">Slow</option>
            <option value="Medium" selected="selected">Medium</option>
            <option value="Fast">Fast</option>
            <option value="Faster">Faster</option>
        </select>
    </fieldset>
    
    
    <h2>"dropdown" Style with menuWidth wider than menu and text formatting</h2>

    <fieldset>
        <label for="speedD">Select an Address:</label>
        <select name="speedD" id="speedD">
            <option>esvit|esvit666@gmail.com|Савчук Віталій</option>
            <option>botsula|botsula@gmail.com|Боцула Мирослав</option>
            <option selected="selected">aslubsky|aslubsky@gmail.com|Олександр Слубський</option>
        </select>
    </fieldset>
    
    <h2>Default: "popup" Style with framework icons</h2>
    <fieldset>
        <label for="files">Select a File:</label>
        <select name="files" id="files">
            <option value="jquery" class="script">jQuery.js</option>

            <option value="jquerylogo" class="image">jQuery Logo</option>
            <option value="jqueryui" class="script">ui.jQuery.js</option>
            <option value="jqueryuilogo" selected="selected" class="image">jQuery UI Logo</option>
            <option value="somefile">Some unknown file</option>
        </select>
    </fieldset>
    
    <h2>Default: "popup" Style with custom icon images</h2>

    <fieldset>
        <label for="filesB">Select a File:</label>
        <select name="filesB" id="filesB" class="customicons">
            <option value="mypodcast" class="podcast">John Resig Podcast</option>
            <option value="myvideo" class="video">Scott Gonzales Video</option>
            <option value="myrss" class="rss">jQuery RSS XML</option>
        </select>

    </fieldset>
    
    
    <h2>Demo with optgroups</h2>
    <fieldset>
        <label for="filesC">Select a File:</label>
        <select name="filesC" id="filesC">
            <optgroup label="images">
                <option value="jquerylogo" class="image">jQuery Logo</option>

                <option value="jqueryuilogo" selected="selected" class="image">jQuery UI Logo</option>
            </optgroup>
            <optgroup label="scripts">
                <option value="jquery" class="script">jQuery.js</option>
                <option value="jqueryui" class="script">ui.jQuery.js</option>
            </optgroup>
            <optgroup label="other & files">
                <option value="somefile">Some unknown file</option>
                <option value="someotherfile">Some other unknown file</option>
            </optgroup>
        </select>
    </fieldset>

</div>

{literal}
<script type="text/javascript">
    $(function(){
        
        $('select#speedA').selectmenu();
        
        $('select#speedAa').selectmenu({maxHeight: 150});
        
        $('select#speedB').selectmenu({
            maxHeight: 450,
            width: 300,
            format: addressFormatting
        });
        
        $('select#speedC').selectmenu({style:'dropdown'});
        
        $('select#speedD').selectmenu({
            style:'dropdown', 
            menuWidth: 400,
            format: addressFormatting
        });
        
        $('select#files, select#filesC').selectmenu({
            icons: [
                {find: '.script', icon: 'ui-icon-script'},
                {find: '.image', icon: 'ui-icon-image'}
            ]
        });
        
        $('select#filesB').selectmenu({
            icons: [
                {find: '.video'},
                {find: '.podcast'},
                {find: '.rss'}
            ]
        });
        
        
    });
    
    
    //a custom format option callback
    var addressFormatting = function(text){
        var info = text.split('|');

        var newText = '';
        var email = info[1];

        newText = '<div class="ui-selectmenu-info">';
        newText +=  '<div class="ui-selectmenu-smallinfo">';
        newText +=      '<img src="http://www.gravatar.com/avatar/' + md5(email) + '?s=16" />';
        newText +=      '<div class="username">' + info[2] + '</div>';
        newText +=  '</div>';
        newText +=  '<div class="ui-selectmenu-detailinfo">';
        newText +=      '<img src="http://www.gravatar.com/avatar/' + md5(email) + '?s=32" />';
        newText +=      '<div class="username">' + info[0] + '</div>';
        newText +=      '<div class="realname">' + info[2] + '</div>';
        newText +=  '</div>';
        newText +=  '<div class="spacer"></div>';
        newText += '</div>';
        return newText;
    }
</script>
<style>
select { width: 200px }
fieldset { border:0;  margin-bottom: 40px;}	
label,select,.ui-select-menu { float: left; margin-right: 10px; }

.ui-selectmenu-smallinfo img, .ui-selectmenu-detailinfo img {
    float: left;
}
.ui-selectmenu-smallinfo .username {
    margin-left:1.7em;
    white-space:nowrap;
    margin-right:1.4em;
    overflow:hidden;
}
.ui-selectmenu-detailinfo .username {
    margin-left:3em;
    white-space:nowrap;
    color: black;
}
.ui-selectmenu-detailinfo .realname {
    margin-left:3em;
    white-space:nowrap;
}
.ui-selectmenu-smallinfo, .ui-selectmenu-status .ui-selectmenu-detailinfo { display: none; }
.ui-selectmenu-status .ui-selectmenu-smallinfo { 
    display: block;
    margin-bottom:1em;
}

/*select with custom icons*/
body a.customicons { height: 2.5em;}
body .customicons li a, body a.customicons span.ui-selectmenu-status { line-height: 2em; padding-left: 30px !important; }
body .video .ui-selectmenu-item-icon, body .podcast .ui-selectmenu-item-icon, body .rss .ui-selectmenu-item-icon { height: 24px; width: 24px; }
body .video .ui-selectmenu-item-icon { background: url(/admin/media/images/sample_icons/24-video-square.png) 0 0 no-repeat; }
body .podcast .ui-selectmenu-item-icon { background: url(/admin/media/images/sample_icons/24-podcast-square.png) 0 0 no-repeat; }
body .rss .ui-selectmenu-item-icon { background: url(/admin/media/images/sample_icons/24-rss-square.png) 0 0 no-repeat; }
</style>
{/literal}