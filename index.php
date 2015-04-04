<?php
$_SERVER['REMOTE_ADDR'] === '127.0.0.1' || exit(0);
if(isset($_POST['code'])) {
    $code = $_POST['code'];
	error_reporting(E_ALL);
	ini_set ('display_errors', true);
	eval($code); exit();
} ?><!DOCTYPE html>
<html>
  <head>
    <title>PHP Eval Sandbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <style type="text/css" media="screen">
	    #editor { height: 800px; font-size: 1.1em;}
        #loader { z-index: 9999999; position: absolute; right: 0; top: 0; display: none; }
	</style>
  </head>
  <body>

    <div class="container-fluid">
		<div class="row">
		  <div class="col-md-6"><div id="editor"></div></div>
		  <div class="col-md-6"><pre id="result"></pre></div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<button id="historik-btn" class="btn btn-default btn-lg"><span class="glyphicon glyphicon-time"></span></button>
				<ul id="historik-list" class="list-unstyled"></ul>
			</div>
		</div>
	</div>

	
    <div id="loader"><img src="//cdnjs.cloudflare.com/ajax/libs/file-uploader/3.7.0/processing.gif"></div>

    <script src="https://code.jquery.com/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.9/ace.js"></script>
	<script>
	$(function(){

	    var editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
		editor.getSession().setMode("ace/mode/javascript");
	    editor.focus();

        var $loader = $('#loader');
		
		editor.commands.addCommand({
		    name: 'getResult',
		    bindKey: {win: 'Ctrl-Enter',  mac: 'Command-Enter'},
		    exec: function(editor) {
                var code = editor.getValue();
                if(!code) return;
                $loader.show();
                Historik.close();
                Historik.push(code);
				$
					.post('', {code:code}, function(r){$('#result').html(r)})
					.fail(function() { $('#result').html('Erreur serveur')})
                	.complete(function(){$loader.hide()});
			}
		});

		$('#historik-btn').on('click', function(){Historik.show(function(code){
			editor.setValue(code);
		})});
	});

	var Historik = function(){
		var stack = JSON.parse(window.localStorage.getItem("Historik")) || [];
		var maxHistorikLength = 300;
		var newVerMinTimeSec = 60*1000; // milliseconds
		var hasPushed = false;
		var $_uiList = $('#historik-list');

        var _show = function(onLoadHistory) {
            _close();
            var onClick = function(i){return function(){
                onLoadHistory(stack[i].code);
                _close();
            }};
            var pad0 = function(n) {return ('0'+n).slice(-2)};

            $(stack).each(function(i, vers){
                var d = new Date(vers.time);
                var ds = pad0(d.getMonth()+1)+'/'+pad0(d.getDate())+' '+pad0(d.getHours())+':'+pad0(d.getMinutes());
                var $versBtn = $('<li>')
                    .append($('<pre>').text(vers.code).addClass('small'))
                    .prepend($('<a class="btn btn-info">')
                        .text(ds)
                        .click(onClick(i))
                    )
                $_uiList.append($versBtn)
            });
        };

        var _close = function() {$_uiList.empty()};

		var _persist = function() {
			window.localStorage.setItem("Historik", JSON.stringify(stack));
		};

		var _cap = function() {
			if (stack.length > maxHistorikLength)
				stack = stack.slice(0, maxHistorikLength);
		};

		var _pushStack = function(code) {
			var last = stack[0];
			var now = (new Date()).getTime();
			
			if (last && code == last.code)
				return;
			if (hasPushed && (now - last.time < newVerMinTimeSec))
				stack.shift();
			
			stack.unshift({time: now, code: code});
			hasPushed = true
			_cap();
			_persist();
		};

		return {
			push: _pushStack,
            show: _show,
            close: _close
		};
	}();
	</script>
  </body>
</html>
