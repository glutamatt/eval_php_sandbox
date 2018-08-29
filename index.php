<?php
in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', '172.17.0.1']) || die('Seriously dude ?');
if(isset($_POST['code'])) {
    $code = $_POST['code'];
	error_reporting(E_ALL);
	ini_set ('display_errors', true);
	eval($code); exit();
} ?><!DOCTYPE html>
<html>
  <head>
    <title>PHP Eval Sandbox</title>
    <link rel="icon" type="image/png" href="http://www.favicon.cc/logo3d/243711.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <style type="text/css" media="screen">
        body, pre { font-family: monospace; font-size: 1.2em; color: rgba(51, 51, 51, 0.9);}
        .nopadding {padding: 0;}
        #editor { height: 800px; font: inherit; font-size: 1.2em;box-shadow: 5px 5px 10px 0px rgba(0,0,0,0.50);}
        #loader { z-index: 9999999; position: absolute; right: 30px; top: 30px; width: 100px; height: 100px; display: none;box-shadow: 1px 1px 5px 0px rgba(0,0,0,0.50); border-radius: 100px; background-color: white;}
        #result { background-color: transparent ; border: none; border-radius: none; }
        #historik-btn { width: 30px;height: 30px;padding: 6px 0;border-radius: 15px;margin: 10px;}

        .circular {-webkit-animation: rotate 2s linear infinite; animation: rotate 2s linear infinite; height: 100px; position: relative; width: 100px; }
        .circle_path {stroke-dasharray: 1,200; stroke-dashoffset: 0; -webkit-animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite; animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite; stroke-linecap: round; }
        @-webkit-keyframes rotate {100% {-webkit-transform: rotate(360deg); transform: rotate(360deg); } }
        @keyframes rotate {100% {-webkit-transform: rotate(360deg); transform: rotate(360deg); } } @-webkit-keyframes dash {
          0% {stroke-dasharray: 1,200; stroke-dashoffset: 0; }
          50% {stroke-dasharray: 89,200; stroke-dashoffset: -35; }
          100% {stroke-dasharray: 89,200; stroke-dashoffset: -124; }
        }
        @keyframes dash {
            0% {stroke-dasharray: 1,200; stroke-dashoffset: 0; } 50% {stroke-dasharray: 89,200; stroke-dashoffset: -35; } 100% {stroke-dasharray: 89,200; stroke-dashoffset: -124; } }
            @-webkit-keyframes color {100%, 0% {stroke: #d62d20; } 40% {stroke: #0057e7; } 66% {stroke: #008744; } 80%, 90% {stroke: #ffa700; } }
            @keyframes color {100%, 0% {stroke: #d62d20; } 40% {stroke: #0057e7; } 66% {stroke: #008744; } 80%, 90% {stroke: #ffa700; }
        }

	</style>
  </head>
  <body>

    <div>
		<div>
		  <div class="col-md-6 nopadding"><div id="editor"></div></div>
		  <div class="col-md-6"><pre id="result"></pre></div>
		</div>
		<div>
			<div class="col-md-12">
				<button id="historik-btn"><span class="glyphicon glyphicon-time"></span></button>
				<ul id="historik-list" class="list-unstyled"></ul>
			</div>
		</div>
	</div>

	
    <div id="loader"><svg class="circular">
        <circle class="circle_path" cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10"/>
    </svg></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.9/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.9/ext-language_tools.js"></script>
	<script>
	$(function(){

        ace.require("ace/ext/language_tools");
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/ambiance");
        editor.getSession().setMode({ path : "ace/mode/php", inline:true });
        editor.setOptions({showPrintMargin: false, enableBasicAutocompletion: true, enableLiveAutocompletion: true});
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
			editor.setValue(code);editor.focus();$(window).scrollTop(0);
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
                var ds = d.toLocaleDateString('fr-FR', {weekday: "long", year: "numeric", month: "long", day: "numeric"})
                    + ' ' + d.toLocaleTimeString('fr-FR');
                var $versBtn = $('<li>')
                    .append($('<pre>').text(vers.code))
                    .prepend($('<a class="btn btn-link btn-info">')
                        .text(ds).click(onClick(i))
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
