<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>

<script async type="text/javascript" src="scripts/a.out.js"></script>

<script type="text/javascript" src="scripts/main.js"></script>

<!-- Import the modelviewer component -->
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.1.1/model-viewer.min.js"></script>


<style>

#dragAndDrop {
    min-height: 200px;
    white-space: pre;
    border: 1px dashed black;
    border-radius: 10px;
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

</style>

<body>
    <h1>3D-View of STL-File</h1>
    <div id="dragAndDrop"
         ondragenter="event.stopPropagation(); event.preventDefault();"
         ondragover="event.stopPropagation(); event.preventDefault();"
         ondrop="event.stopPropagation(); event.preventDefault();
         dodrop(event);">
        <span>Drag and drop STL files here or <input type="file" onChange="uploaded()" id="fileuploadform"/></span>
        <span></span>
        <span>Status: <span id="status">Waiting for upload</span>
    </div>
    <a id="download"><button id="download_button" disabled>Click to Download compressed stl</button></a>
    <p id="successPHP"></p>
    <div>
        <model-viewer id="upload-gltf" alt="3d-View of uploaded stl" src="#" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>
        <div class="controls">
            <div>
                <p>Normals</p>
                <select id="normals">
                    <option>None</option>
                    <option value="https://lschoenepxc.github.io/textures/texture-normal.jpg">Normal</option>
                    <option value="https://lschoenepxc.github.io/textures/Lantern_normal.png">Lantern Pole</option>
                    <option value="https://lschoenepxc.github.io/textures/gerillt-normal.jpg">Gerillt</option>
                    <option value="https://lschoenepxc.github.io/textures/PA12-normal.png">PA12</option>
                    <option value="https://lschoenepxc.github.io/textures/brick_normal_map.png">Brick Wall</option>
                </select>
            </div>
            <div>
                <p>Metalness: <span id="metalness-value"></span></p>
                <input id="metalness" type="range" min="0" max="1" step="0.01" value="0">
            </div>
            <div>
                <p>Roughness: <span id="roughness-value"></span></p>
                <input id="roughness" type="range" min="0" max="1" step="0.01" value="0">
            </div>
        </div>
        <div id="color-controls">
            <button data-color="#969696">Grey</button>
            <button data-color="#ff0000">Red</button>
            <button data-color="#00ff00">Green</button>
            <button data-color="#0000ff">Blue</button>
        </div>
        <!-- <p>Scale: <span id="texture-scale"></span></p>
        <input type="range" min="0.5" max="1.5" value="1" step="0.01" id="scaleSlider"> -->
        <button onclick="exportGLB()">Export GLB</button>

        <script type="module">       
            const modelViewerTexture1 = document.querySelector("model-viewer#upload-gltf");
            // const scaleSlider = document.querySelector('#scaleSlider');

            document.querySelector('#color-controls').addEventListener('click', (event) => {
                const colorString = event.target.dataset.color;
                const [material] = modelViewerTexture1.model.materials;
                material.pbrMetallicRoughness.setBaseColorFactor(colorString);
            });
            
            modelViewerTexture1.addEventListener("load", () => {
                if (loadFlag == false) {
                    console.log("Now uploading and creating UV Map!");
                    createUV();
                    loadFlag = true;
                }
                console.log("No repeating of uploading");
            
                const material = modelViewerTexture1.model.materials[0];

                let metalnessDisplay = document.querySelector("#metalness-value");
                let roughnessDisplay = document.querySelector("#roughness-value");

                metalnessDisplay.textContent = material.pbrMetallicRoughness.metallicFactor;
                roughnessDisplay.textContent = material.pbrMetallicRoughness.roughnessFactor;
                // Change color
                // material.pbrMetallicRoughness.setBaseColorFactor([0.7294, 0.5333, 0.0392]);

                document.querySelector('#metalness').addEventListener('input', (event) => {
                    material.pbrMetallicRoughness.setMetallicFactor(event.target.value);
                    metalnessDisplay.textContent = event.target.value;
                });

                document.querySelector('#roughness').addEventListener('input', (event) => {
                    material.pbrMetallicRoughness.setRoughnessFactor(event.target.value);
                    roughnessDisplay.textContent = event.target.value;
                });


                const createAndApplyTexture = async (channel, event) => {
                    if (event.target.value == "None") {
                        // Clears the texture.
                        material[channel].setTexture(null);
                    } else if (event.target.value) {
                        // Creates a new texture.
                        const texture = await modelViewerTexture1.createTexture(event.target.value);
                        // Applies the new texture to the specified channel.
                        if (channel.includes('base') || channel.includes('metallic')) {
                            material.pbrMetallicRoughness[channel].setTexture(texture);
                        } 
                        else {
                            material[channel].setTexture(texture);
                            //material[channel].texture.source.setURI(event.options[event.selectedIndex].text);
                            
                            // //sampler not working, when texture None
                            // const sampler = modelViewerTexture1.model.materials[0].normalTexture.texture.sampler;
                            // console.log(sampler);


                            // const scaleDisplay = document.querySelector('#texture-scale');
                            // scaleDisplay.textContent = scaleSlider.value;

                            // scaleSlider.addEventListener('input', (event) => {
                            //     const scale = {
                            //     x: scaleSlider.value,
                            //     y: scaleSlider.value
                            //     };
                            //     sampler.setScale(scale);
                            //     scaleDisplay.textContent = scale.x;
                            // });
                        }
                    }
                }

                document.querySelector('#normals').addEventListener('input', (event) => {
                    createAndApplyTexture('normalTexture', event);
                });
            });
            
        </script>
    </div>
</body>

</html>
