function PaintTask(resumeImage){    
    //**********
    //********** Variablen
    //**********
    var canvas = document.getElementById("paintCanvas");
    var textarea = document.getElementById("answerImage");
    var ctx = canvas.getContext("2d");    
    // flag -> wird maustaste gedrueckt?
    var flag = false;
    // vorhergehende mausposition
    var prevX = 0, prevY = 0;
    // stack funktioniert, koennte vllt. aber etwas performanter sein
    var undoRedoStack = new Array(); 
    var stackPos = -1;
    // soll durch mausemove inhalt geloescht (true) oder gezeichnet (false) werden?
    var erase = false;
    var divEraseIcon = document.getElementById("eraseIcon");
	
    //**********
    //********** Funktionen 
    //**********
    
    function save(){			
		var base = canvas.toDataURL(); 		
		textarea.value = base; // als base64-string
	}	
	
    this.undo = function() {
        if (stackPos > 0) {            
            stackPos--;
            var canvasPic = undoRedoStack[stackPos];
            ctx.putImageData(canvasPic, 0, 0);             
            save();
        }            
    }

    this.redo = function() {
        if (stackPos < undoRedoStack.length-1) {
            stackPos++;
            var canvasPic = undoRedoStack[stackPos];
            ctx.putImageData(canvasPic, 0, 0); 
            save();
        }
    }

    function pushDrawAction() {
        // erzeuge neues bild nach letzter zeichenaktion
        // und halte es im stack      
        stackPos++;
        if (stackPos < undoRedoStack.length){
            undoRedoStack.length = stackPos;
        }
        undoRedoStack.push(ctx.getImageData(0,0,ctx.canvas.width,ctx.canvas.height));   
        save();
    }

    this.clear = function() {
        if (confirm('Alles löschen?')) {
            // loesche den gesamten inhalt, hintergrundbild wird wieder vollstaendig angezeigt
            ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height);
            pushDrawAction();
            textarea.value = '';
        }
    }

    this.erasePaint = function(button){
        // der radiergummie ;)
        // button ist das object, welches diese funktion aufruft    
        if (button.id == "paint"){
            //button.value = "erase";
            button.disabled = true;
            document.getElementById('erase').disabled = false;
            divEraseIcon.style.display = 'none';
            erase = false;
        } else{
            //button.value = "paint";            
            document.getElementById('erase').disabled = true;
            document.getElementById('paint').disabled = false;
            
            //don't show this for now, a crossair might be enough
            //divEraseIcon.style.display = 'inline-block';
            erase = true;
        }
    }  
    
    function getMousePos(e) {
        // liefert mausposition passend zum canvas
        // wichtig vorallem dann, wenn canvas in einem div steht,
        // welches kleiner ist als das canvas
        var rect = canvas.getBoundingClientRect();
        return {
          x: e.clientX - rect.left,
          y: e.clientY - rect.top
        };
      }

    function draw(mousePos) {
        // if zeichnen?
        if (!erase){
            ctx.beginPath();
            ctx.strokeStyle = document.getElementById("selColor").value;
            ctx.lineWidth = document.getElementById("selWidth").value;
            ctx.lineJoin = "round";
            ctx.moveTo(prevX, prevY);
            ctx.lineTo(mousePos.x, mousePos.y);
            ctx.closePath();
            ctx.stroke();
        } else{
            // if loeschen?
            var breite = document.getElementById("selWidth").value * 1 + 3;            
            ctx.clearRect(prevX-Math.round(breite/2), prevY-Math.round(breite/2), breite, breite);            
        }
        prevX = mousePos.x;
        prevY = mousePos.y;
        // kein save() ?
        // nein, da pushDrawAction() aufgerufen wird, 
        // dort wird save() aufgerufen
    }

    function mouseMove(e){
        // wenn mousedown, dann zeichnen (oder loeschen)
        if (erase){
			var breite = document.getElementById("selWidth").value * 1 + 3		
			// zeige einen bereich um den mauszeiger an	
			divEraseIcon.style.width = breite+"px";
			divEraseIcon.style.height = breite+"px";				
			divEraseIcon.style.left = e.pageX-breite / 2 +"px";
			divEraseIcon.style.top = e.pageY-breite / 2 +"px";			
		}
        if (flag){                
            draw(getMousePos(e));
        }            
    }

    function mouseDown(e){        
        flag = true;
        // setzte startpunkt auf aktuelle mauskoordinaten
        var mousePos = getMousePos(e);
        prevX = mousePos.x;
        prevY = mousePos.y;        
    }

    function mouseUp(e){
        flag = false;
        // lege nach jeder zeichenaktion ein neues bild im undoRedoStack ab
        pushDrawAction();
    }

    function mouseOut(e){
        // stoppe alle zeichnenaktionen
        if (flag){
            // wurde vor verlassen gezeichnet, dann erzeuge bild
            pushDrawAction();            
        }
        flag = false;        
    }

    //**********
    //********** EventListener
    //**********
    
    canvas.oncontextmenu = function() {
        // unterdruecke Kontextmenu vom canvas
        return false;  
    }

    function init() {
        canvas.addEventListener("mousemove", function (e) {
            mouseMove(e);
        }, false);
        canvas.addEventListener("mousedown", function (e) {
            mouseDown(e);
        }, false);
        canvas.addEventListener("mouseup", function (e) {
            mouseUp(e);
        }, false);
        canvas.addEventListener("mouseout", function (e) {
            mouseOut(e);
        }, false);
    }

    //**********
    //********** weitere initialisierung
    //**********

    function resume() {
        if (resumeImage){
            var img = new Image;
            $(img).load(function() {
                ctx.drawImage(img,0,0);
                pushDrawAction();
                save(); // aufruf notwendig, da sonst 'nichts' als abgabe gespeichert wird
                init();
            });
            img.src = resumeImage;
        } else {
            init();
        }
    }

    $(document).ready(function() {
        resume();
    });

    // pushDrawAction();
}
