<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<!-- Scripts now being added to the head tag in the main.js window.onload function-->

<!-- <script async type="text/javascript" src="scripts/a.out.js"></script> -->
<!-- Import the modelviewer component -->
<!-- <script type="module" src="scripts/model-viewer.min.js"></script> -->

<script type="text/javascript" src="scripts/main.js"></script>

<style>

* {
    font-family: verdana;
}

#dragAndDrop {
    height: 300px;
    white-space: pre;
    border: 1px dashed black;
    border-radius: 10px;
    display: inline-block;
    min-width:49%;
}

#steps {
    height: 300px;
    white-space: pre;
    vertical-align:top;
    display: inline-block;
    min-width:49%;
    border: 1px dashed black;
    border-radius: 10px;
}

#contains {
    max-width: 75%;
}

#upload-gltf {
    left: 50px; 
    resize: both;
    overflow: auto;
}

::-webkit-resizer{  	
    border: 9px solid rgba(0,0,0,.1); 	
    border-bottom-color: rgba(0,0,0,.5); 	
    border-right-color: rgba(0,0,0,.5); 	
    outline: 1px solid rgba(0,0,0,.2); 	
    box-shadow: 0 0 5px 3px rgba(0,0,0,.1);
}

li + li {
  margin-top: -30px;
}

ol {
    margin-top: -40px;
    margin-bottom: -40px;
}

</style>

<body>
    <h1>3D-View of STL-File with possible modification</h1>
    <div id="contains">
        <div id="dragAndDrop"
            ondragenter="event.stopPropagation(); event.preventDefault();"
            ondragover="event.stopPropagation(); event.preventDefault();"
            ondrop="event.stopPropagation(); event.preventDefault();
            dodrop(event);">
            <span>Drag and drop STL files here or <input type="file" onChange="uploaded()" id="fileuploadform"/></span>
            <span></span>
            <span>Status: <span id="status">Waiting for upload</span>
        </div>
        <div id="steps">
            Step by step:
            <ol>
                <li>Upload STL file</li>
                <li>STL file gets simplified if filesize > 8MB (Client-side via WebAssembly)</li>
                <li>STL gets converted to GLB (Client-side via WebAssembly)</li>
                <li>GLB is loaded into model-viewer (Client-side)</li>
                <li>GLB from model-viewer gets send to server (Client -> Server via JS AJAX call)</li>
                <li>Server adds UV-Map via Blender python script (Server-side command called via php)</li>
                <li>GLB with UV-Map is loaded into model-viewer (Client-side)</li>
            </ol>
            Now you can finally add textures to your uploaded model!
        </div>
    </div>
    <a id="download"><button id="download_button" disabled>Click to Download compressed stl</button></a>
    <div>
        <div id="model">
            Nothing here yet.
        </div>
        <div class="controls">
            <div>
                <p>Materials</p>
                <select id="material-buttons">
                    <option value="None">None</option>
                    <option value="PA12">PA12</option>
                </select>
            </div>
        </div>
        <br/>
        <button onclick="exportGLB()">Export GLB</button>
    </div>
</body>

</html>
