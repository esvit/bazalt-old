<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <link rel="alternate" type="text/xml" href="<?php echo $service['script']; ?>?disco" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>BAZALT Service - <?php echo $service['name']; ?></title>
<script type="text/javascript">
<?php readfile(dirname(__FILE__) . '/bazaltscriptservice.js'); ?>
</script>
  <script type="text/javascript" src="<?php echo $service['script']; ?>?js"></script>

<style>
html,body,address,blockquote,div,p,pre,h1,h2,h3,h4,h5,h6,hr,	/* block level 	*/
dd,dl,dt,ul,ol,li,												/* lists 		*/
a,abbr,acronym,b,big,br,cite,code,del,dfn,em,i,					/* inline text 	*/
ins,kbd,q,samp,small,span,strong,sub,sup,tt,var,				/* inline text 	*/
img,object,														/* misc 		*/
caption,table,tbody,td,tfoot,th,thead,tr,						/* table 		*/
input,textarea,select,button,form,fieldset,legend,label, 		/* form 		*/
font,u,s,center,dir,menu,strike,xmp								/* depricated 	*/
{
	margin: 0;
	padding: 0;
	vertical-align: baseline;
	text-align: left;
	text-indent: 0;
	font: normal 13px/20px Georgia, "Times New Roman";
	color: black;
	text-decoration: none;
}
hr,img,object {
	border: 0;
}
input,select,button {
	vertical-align: middle;						/* make all controls align middle to textline in all browsers */
}
textarea {
	vertical-align: top;						/* ...and all textareas, but... */
}
input,button{
	overflow: visible;							/* remove padding in buttons in IE */
}
select[size]{
	vertical-align: top;						/* make align top to textline for all selectboxes which has attribute "size",.. */
}

select[size="1"] {
	vertical-align: middle;						/* ...if attribute "size" of a selectbox is set to "1", make it align middle to textline */
}
fieldset legend {
	*margin-left: -7px;							/* remove mаrgin in IE6-7. Better place this in your IE6-7.css and include via Conditional Comments. */
}
caption,td,th,tr {
	vertical-align: top;
}
table {
	border-collapse: collapse;
}
#headline {
    background-color: #E4F2FD;
    border-bottom: 1px solid #C6D9E9;
    font-size: 300%;
    font-weight: bold;
    height: 87px;
    line-height: 1.3;
    min-width: 960px;
    padding-left: 20px;
    padding-top: 10px;
}
#headline h2 {
    border:0 none;
    color:#555555;
    float:left;
    font-size:36px;
    line-height:1em;
    padding-left:12px;
    padding-top:34px;
}
#main { padding: 15px 30px; }
a:link,
a:active,
a:visited
{
	color:#567CB0;
	text-decoration: none;
	border-bottom: 1px solid #E3E3E3;
	line-height: 1.0;
}
a:hover
{
	color:#D54E21;/*#003366;*/
	border-bottom: 1px solid #D54E21;
	text-decoration: none;
	/*padding-bottom: 1px;*/
	/*border-bottom: 0;*/
	line-height: 1.0;
}
.method {
    padding: 10px;
}
ol {
    margin-top: 5px;
    margin-left: 20px;
}
strong {
    font-weight: bold;
    margin-bottom: 4px;
}
label { padding: 4px 5px; }
.btn { margin-top: 5px; padding: 3px 5px; }
.result, .error { margin-top: 10px; padding: 10px; background-color: #F4F4F4; border:1px solid #CCC9A4;font-family:"Lucida Console","Courier New",Courier,monospace;font-weight:normal; }
.error { border:1px solid #D43317; background-color: #FEE8E3 }
</style>
<script>
String.prototype.escapeHTML = function () {
    return(
        this.replace(/>/g,'&gt;')
            .replace(/</g,'&lt;')
            .replace(/"/g,'&quot;')
    );;
};

function blocking(nr, state)
{
    if (document.getElementById)
    {
        current = (document.getElementById(nr).style.display == 'block') ? 'none' : 'block';
        if (state) current = state;
        document.getElementById(nr).style.display = current;
    }
    else if (document.all)
    {
        current = (document.all[nr].style.display == 'block') ? 'none' : 'block'
        if (state) current = state;
        document.all[nr].style.display = current;
    }
    else alert ('This link does not work in your browser.');
}
function onSuccess(result, context, method)
{
    blocking(context + '-error', 'none');
    context += '-result';
    blocking(context, 'block');
    document.getElementById(context).innerHTML = obj2str(result, 0);
}

function onFailure(result, context, method)
{
    blocking(context + '-result', 'none');
    context += '-error';
    blocking(context, 'block');
    document.getElementById(context).innerHTML = result;
}
function isArray(obj) {
    if (obj.constructor.toString().indexOf('Array') == -1)
        return false;
    else
        return true;
}
function obj2str(object, tab) {
    var output = '';
    var tabs = '';
    if (object == null) {
        return '[null]';
    }
    if (object == undefined) {
        return '[undefined]';
    }
    if (typeof object != 'object') {
        return '"' + object + '"';
    }
    if(object.data != undefined && object.page != undefined) {
        output += 'Page : '+object.page+', from : '+object.pagesCount+'\n';
        output += obj2str(object.data, tab);
        return output;
    }
    for (var i = 0; i < tab; i++) {
        tabs += '  ';
    }
    var isArr = isArray(object);
    for (var property in object) {
        var prop = object[property];
        if (typeof prop == 'object') {
            prop = obj2str(prop, tab + 1);
        }
        if (isArr) {
            output += '\n' + tabs + '  ' + prop;
        } else if (typeof prop == 'string') {
            output += '\n' + tabs + '  ' + property + ': ' + prop.escapeHTML();
        } else {
            output += '\n' + tabs + '  ' + property + ': ' + prop;
        }
    }
    if (isArr) {
        return tabs + '[ count: ' + object.length + output + tabs + ']' + "\n";
    }
    return '{' + output + '\n' + tabs + '}' + "\n";
}
</script>
</head>
<body>
    <div id="headline">
        <h2><?php echo $service['name']; ?></h2>
    </div>
    
    <div id="main">
        <p>Список методів цього сервісу:</p>
        <ol>
<?php foreach ($service['methods'] as $methodName => $arguments) {
        $argCount = count($arguments);
        $methodName = ltrim($methodName, '_');
?>
            <li>
                <a href="#" onclick="javascript: blocking('m-<?php echo $methodName; ?>'); return false;"><?php echo $methodName; ?></a>
                <div class="method" id="m-<?php echo $methodName; ?>" style="display: none">
                    <p><strong>Метод <?php echo $methodName; ?></strong></p>
                    <?php $args = ''; if ($argCount > 0) { ?>
                    <?php foreach ($arguments as $i => $arg) { $args .= "document.getElementById('m-" . $methodName . "-" . $arg . "').value,"; ?>
                    <p><label for="<?php echo $arg; ?>"><?php echo $arg; ?>:</label><input id="m-<?php echo $methodName; ?>-<?php echo $arg; ?>" type="text" /></p>
                    <?php } ?>
                    <?php } else { ?>
                    <p>В цього методу немає параметрів</p>
                    <?php } ?>
                    <p><input class="btn" onclick="javascript: blocking('m-<?php echo $methodName; ?>-result', 'none'); <?php echo $service['name']; ?>.<?php echo $methodName; ?>(<?php echo $args; ?>onSuccess, onFailure, 'm-<?php echo $methodName; ?>')" type="button" value="Виконати"></p>
                    <pre class="result" id="m-<?php echo $methodName; ?>-result" style="display: none"></pre>
                    <div class="error" id="m-<?php echo $methodName; ?>-error" style="display: none"></div>
                </div>
            </li>
<?php } ?>
<?php if (count($service['methods']) == 0) { echo '<p>Немає методів</p>'; } ?>
        </ol>
    </div>
</body>
</html>
