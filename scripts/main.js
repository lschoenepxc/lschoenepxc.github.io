// `use strict`;  // Strict mode helps you write cleaner code, like preventing you from using undeclared variables.

var loadFlag = true;

var worker = new Worker('scripts/workerReduction.js');

worker.onmessage = function(e) {
    const file = e.data.blob;
    if (file !== undefined) {
        const s_name = SIMPLIFY_FILE.simplify_name;
        file.name = s_name;

        let url = window.URL.createObjectURL(file);

        var download = document.getElementById('download');
        download.href = url;
        download.download = s_name;

        var download_button = document.getElementById('download_button');
        download_button.disabled = false;
        download_button.innerHTML = "Click to Download " + s_name;

        waitForReduction(file);

        put_status("Waiting for UV-Map");
        return;
    }

    console.error("Unknown Message from WebWorker", e.data);
}

worker.onerror = function(e) {
    console.log(e);
    console.log(
        'ERROR: Line ', e.lineno, ' in ', e.filename, ': ', e.message
    );
}

let SIMPLIFY_FILE = {
    'blob': undefined,
    get name(){
        if (this.exists)
            return this.blob.name;
    },
    get simplify_name() {
        if (this.exists)
            return "simplify_"+this.name;
    },
    get size() {
        if (this.exists)
            return this.blob.size;
    },
    get exists() {
        if (this.blob)
            return true;
        else {
            return false;
        }
    }
}

let GLOBAL = {
    color : [150, 150, 150, 1],
    get color_str() {
        return `rgba(${this.color[0]},
        ${this.color[1]},
        ${this.color[2]},
        ${this.color[3]})`;
    },
    get color_0to1() {
        return [this.color[0]/255,
                this.color[1]/255,
                this.color[2]/255,
                this.color[3]
        ];
    },
    stl_name: undefined
};

function uploaded(file) {
    if (file === undefined) { // via upload button
        var uploadform = document.getElementById("fileuploadform");
        file = uploadform.files[0];

        // this helps to force trigger even if user upload the same file
        // https://stackoverflow.com/a/12102992/5260518
        this.value = null;
    }

    // Reduction --> 
    if (file === undefined) // not via upload button defined otherwise
        file = SIMPLIFY_FILE.blob;

    var uploadform = document.getElementById("fileuploadform");
    uploadform.files[0] = file;

    SIMPLIFY_FILE.blob = file;
    if (SIMPLIFY_FILE.size > 8*1024*1024) {
        check_file_Reduction(post_to_worker);
    }
    else{
        waitForReduction(file);
    }
    
    // <-- Reduction
}

function waitForReduction(file){
    // now using reducted stl-file
    check_file(file, function(){check_file_success()});

    function check_file_success() {

            put_status("Converting by your browser");

            var filename = file.name;
            var fr = new FileReader();
            fr.readAsDataURL(file);

            fr.onload = function (){
            var stl_name = filename;

            var data = atob(fr.result.split(",")[1]); // base64 to Uint8 for emscripten
            Module['FS_createDataFile'](".", stl_name, data, true, true);

            Module.ccall("make_bin", // c function name
                undefined, // return
                ["string"], // param
                [stl_name]
            );

            // Using a file to output data from c++ to js
            // because if I use a pointer to array, if memory grow in wasm,
            // this array will be in a different place
            // then the pointer will be pointing to a wrong memory address
            let out_data = Module['FS_readFile']('data.txt', { encoding: 'utf8'});
            out_data = out_data.split(" ");
            GLOBAL.number_indices = parseInt(out_data[0]);
            GLOBAL.number_vertices = parseInt(out_data[1]);
            GLOBAL.indices_blength = parseInt(out_data[2]);
            GLOBAL.vertices_blength = parseInt(out_data[3]);
            GLOBAL.total_blength = parseInt(out_data[4]);
            GLOBAL.minx = parseFloat(out_data[5]);
            GLOBAL.miny = parseFloat(out_data[6]);
            GLOBAL.minz = parseFloat(out_data[7]);
            GLOBAL.maxx = parseFloat(out_data[8]);
            GLOBAL.maxy = parseFloat(out_data[9]);
            GLOBAL.maxz = parseFloat(out_data[10]);
            GLOBAL.stl_name = stl_name;
            download_glb();
        }
    }
}

function gltf_dict(
    total_blength, indices_blength, vertices_boffset, vertices_blength,
    number_indices, number_vertices, minx, miny, minz, maxx, maxy, maxz,
    color_r, color_g, color_b
) {
    return {
        "scenes" : [
            {
                "nodes" : [ 0 ]
            }
        ],
        "nodes" : [
            {
                "mesh" : 0,
                "rotation": [-0.70710678119, 0.0, 0.0, 0.70710678119]
            }
        ],
        "meshes" : [
            {
                "primitives" : [ {
                    "attributes" : {
                        "POSITION" : 1
                    },
                    "indices" : 0,
                    "material" : 0
                } ]
            }
        ],
        "buffers" : [
            {
                "byteLength" : total_blength
            }
        ],
        "bufferViews" : [
            {
                "buffer" : 0,
                "byteOffset" : 0,
                "byteLength" : indices_blength,
                "target" : 34963
            },
            {
                "buffer" : 0,
                "byteOffset" : vertices_boffset,
                "byteLength" : vertices_blength,
                "target" : 34962
            }
        ],
        "accessors" : [
            {
                "bufferView" : 0,
                "byteOffset" : 0,
                "componentType" : 5125,
                "count" : number_indices,
                "type" : "SCALAR",
                "max" : [ number_vertices - 1 ],
                "min" : [ 0 ]
            },
            {
                "bufferView" : 1,
                "byteOffset" : 0,
                "componentType" : 5126,
                "count" : number_vertices,
                "type" : "VEC3",
                "min" : [minx, miny, minz],
                "max" : [maxx, maxy, maxz]
            }
        ],
        "asset" : {
            "version" : "2.0",
             "generator": "STL2GLTF"
        },
        "materials": [
            {
                "pbrMetallicRoughness": {
                    "baseColorFactor": [
                        color_r,
                        color_g,
                        color_b,
                        1
                        ],
                    "metallicFactor": 0,
                    "roughnessFactor": 0
                }
            }
        ],
    } // end of dict
}

async function download_glb() {

    if (GLOBAL.stl_name === undefined) {
        put_status("Please upload a file");
        return;
    } else {
    }

    const stl_name = GLOBAL.stl_name;
    const color = GLOBAL.color_0to1;

    const total_blength = GLOBAL.total_blength;
    const indices_blength = GLOBAL.indices_blength;
    const vertices_boffset = GLOBAL.indices_blength;
    const vertices_blength = GLOBAL.vertices_blength;
    const number_indices = GLOBAL.number_indices;
    const number_vertices = GLOBAL.number_vertices;
    const minx = GLOBAL.minx;
    const miny = GLOBAL.miny;
    const minz = GLOBAL.minz;
    const maxx = GLOBAL.maxx;
    const maxy = GLOBAL.maxy;
    const maxz = GLOBAL.maxz;

    const gltf_json = JSON.stringify(
        gltf_dict(
            total_blength, indices_blength, vertices_boffset, vertices_blength,
            number_indices, number_vertices, minx, miny, minz, maxx, maxy, maxz,
            color[0], color[1], color[2]
        )
    );

    const out_bin_bytelength = total_blength;

    const header_bytelength = 20;
    const scene_len = gltf_json.length;
    const padded_scene_len = ((scene_len+ 3) & ~3);
    const body_offset = padded_scene_len + header_bytelength;
    const file_no_bin_len = body_offset + 8;
    const file_len = file_no_bin_len + out_bin_bytelength;

    let glb = new Uint8Array(file_no_bin_len);
    glb[0] = 0x67; // g
    glb[1] = 0x6c; // l
    glb[2] = 0x54; // t
    glb[3] = 0x46; // f

    glb[4]  = ( 2 ) & 0xFF;
    glb[5]  = ( 2>>8 ) & 0xFF;
    glb[6] = ( 2>>16 ) & 0xFF;
    glb[7] = ( 2>>24 ) & 0xFF;

    glb[8]  = ( file_len ) & 0xFF;
    glb[9]  = ( file_len>>8 ) & 0xFF;
    glb[10] = ( file_len>>16 ) & 0xFF;
    glb[11] = ( file_len>>24 ) & 0xFF;
    glb[12] = ( padded_scene_len ) & 0xFF;
    glb[13] = ( padded_scene_len>>8 ) & 0xFF;
    glb[14] = ( padded_scene_len>>16 ) & 0xFF;
    glb[15] = ( padded_scene_len>>24 ) & 0xFF;

    // JSON
    glb[16] = 0x4A; // J
    glb[17] = 0x53; // S
    glb[18] = 0x4F; // O
    glb[19] = 0x4E; // N

    for (let i=0;i<gltf_json.length;i++) {
        glb[i+header_bytelength] = gltf_json.charCodeAt(i);
    }
    for (let i=0;i<padded_scene_len - scene_len;i++) {
        glb[i+scene_len+header_bytelength] = 0x20;
    }

    glb[body_offset  ] = ( out_bin_bytelength ) & 0xFF;
    glb[body_offset+1] = ( out_bin_bytelength>>8 ) & 0xFF;
    glb[body_offset+2] = ( out_bin_bytelength>>16 ) & 0xFF;
    glb[body_offset+3] = ( out_bin_bytelength>>24 ) & 0xFF;
    glb[body_offset+4] = 0x42; // B
    glb[body_offset+5] = 0x49; // I
    glb[body_offset+6] = 0x4E; // N
    glb[body_offset+7] = 0x00; //

    let out_bin = Module['FS_readFile']('out.bin');

    let blob = new Blob([glb, out_bin], {type: 'application/sla'});
    let url = window.URL.createObjectURL(blob);
    document.querySelector('#model').innerHTML = '<model-viewer id="upload-gltf" alt="3d-View of uploaded stl" src="#" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>';
    
    addListener();
    
    document.querySelector('#upload-gltf').src = url;

    loadFlag = false;
}

function addListener () {
    const modelViewerTexture1 = document.querySelector("model-viewer#upload-gltf");
    // const scaleSlider = document.querySelector('#scaleSlider');

    document.querySelector('#color-controls').addEventListener('click', (event) => {
        const colorString = event.target.dataset.color;
        const [material] = modelViewerTexture1.model.materials;
        material.pbrMetallicRoughness.setBaseColorFactor(colorString);
    });
            
    modelViewerTexture1.addEventListener("load", () => {
        if (loadFlag == false) {
            createUV();
            loadFlag = true;
        }
            
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
}

function check_file(file, success_cb) {
    put_status("Checking file");
    const filename = file.name;
    const extension = filename.toLowerCase().slice(filename.lastIndexOf(".")+1, filename.length);
    if (extension!=="stl") {
        put_status("Please upload an stl file not "+ extension);
        return;
    }
    success_cb();
}

function check_file_Reduction(success_cb) {
    put_status("Checking file");
    const filename = SIMPLIFY_FILE.name;
    const extension = filename.toLowerCase().slice(filename.lastIndexOf(".")+1, filename.length);
    if (extension!=="stl") {
        put_status("Please upload an stl file not "+ extension);
        return;
    }
    success_cb();
}

function post_to_worker() {
    put_status("Simplifying by your browser...");
    worker.postMessage(
        {"blob":SIMPLIFY_FILE.blob,
        "percentage": (8*1024*1024)/SIMPLIFY_FILE.size,
         "simplify_name": SIMPLIFY_FILE.simplify_name
        }
    );
}

function dodrop(event) {
    var dt = event.dataTransfer;
    var file = dt.files[0];
    var filename = file.name;
    put_status("Converting " + filename + " to GLB using WebAssembly.");
    uploaded(file);
}

function put_status(text)
{
    document.getElementById("status").textContent = text;
}

async function exportGLB(){
    const modelViewer = document.getElementById("upload-gltf");
    const glTF = await modelViewer.exportScene();
    const file = new File([glTF], "export.glb");
    const link = document.createElement("a");
    link.download =file.name;
    link.href = URL.createObjectURL(file);
    link.click();
}

async function createUV(){
    const modelViewer = document.getElementById("upload-gltf");
    const glTF = await modelViewer.exportScene();
    var id = makeid(5);
    const file = new File([glTF], "export-" + id + ".glb");
    
    // If file size > 8 MB, then no upload
    // console.log(file.size);

    await uploadFile(file);

    var inputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/uploads/export-"+id+".glb";
    await insertPHP(inputPath);

    document.querySelector('#upload-gltf').src = "blender-files/models/uploads/export-"+id+".glb";
}

function insertPHP(inputPath){
    const xmlhttp = new XMLHttpRequest();
        xmlhttp.onload = function() {
        // document.getElementById("successPHP").innerHTML = this.responseText;
        put_status(this.responseText);
        return "Success";
    }
    // var inputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/uploads/export.glb";
    xmlhttp.open("GET", "include.php?inputPath=" + inputPath);
    xmlhttp.send();
}    
async function uploadFile(file) {
    let formData = new FormData();           
    formData.append("file", file);
    await fetch('upload.php', {
        method: "POST", 
        body: formData
    });  
    return "Success";  
}    

function makeid(length) {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    let counter = 0;
    while (counter < length) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
        counter += 1;
    }
    return result;
}