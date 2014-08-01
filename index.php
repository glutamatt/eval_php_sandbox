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

    <script src="http://code.jquery.com/jquery.js"></script>
    <script src="http://ace.ajax.org/build/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
	<script>
	$(function(){

	    var editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
		editor.getSession().setMode("ace/mode/javascript");
	    editor.focus();

        var $loader = $('#loader');
		
		editor.commands.addCommand({
		    name: 'getResult',
		    bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
		    exec: function(editor) {
                var code = editor.getValue();
                if(!code) return;
                $loader.show();
                Historik.push(code);
				$
					.post('', {code:code}, function(r){$('#result').html(r)})
					.fail(function() { $('#result').html('Erreur serveur')})
                	.complete(function(){$loader.hide()});
                Historik.show();
			}
		});

		$('#historik-btn').on('click', function(){
			Historik.show();
		});
	});

	var Historik = function(){
		var stack = JSON.parse(window.localStorage.getItem("Historik")) || [];
		var maxHistorikLength = 100;
		var newVerMinTimeSec = 20*1000; // milliseconds
		var hasPushed = false;

        var _show = function() {
            $list = $('#historik-list');
            $list.empty();
            var hist = Historik.get();
            var load = function(i){return function(){
                editor.setValue(hist[i].code);
                $list.empty();
            }};
            var addInitialZero = function(n) {
                return ('0'+n).slice(-2);
            };
            $(Historik.get()).each(function(i, vers){
                var d = new Date(vers.time);
                var ds = addInitialZero(d.getMonth()+1)+'/'+addInitialZero(d.getDate())+' '+addInitialZero(d.getHours())+':'+addInitialZero(d.getMinutes());
                var $versBtn = $('<li>')
                    .append($('<pre>').text(vers.code).addClass('small'))
                    .prepend($('<a class="btn btn-info">')
                        .text(ds)
                        .click(load(i))
                    )
                $list.append($versBtn)
            });
        };

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
			get: function() { return stack },
            show: _show
		};
	}();
	</script>
  </body>
</html>