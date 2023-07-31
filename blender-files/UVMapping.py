import bpy
import requests 
import sys
import time

# https://blender.stackexchange.com/questions/6817/how-to-pass-command-line-arguments-to-a-blender-python-script

# Command from command line
# blender --background --python UVMapping.py -- blobUrl localPath outputPath
# blender/blender --background --python blender-files/UVMapping.py -- http://localhost:3000/1c54761d-b230-457b-b375-a0ccb5f3fd1f C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/test.glb C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/modelUV.glb

# without blob
# blender --background --python UVMapping.py -- C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/test.glb C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/modelUV.glb

# Grab Currrent Time Before Running the Code
start = time.time()

argv = sys.argv
argv = argv[argv.index("--") + 1:]  # get all args after "--"

print(argv)  # --> ['blobUrl', 'localPath', 'outputPath']

# Blob URL from website where we read the glb from
blobUrl = argv[0]
# blobUrl = blobUrl[blobUrl.index("blob:") + 5:]
print(blobUrl)

# LocalPath on server where we write the glb to
inputPath = argv[1] 

# Output path on server where we write the UV-Mapped glb to
outputPath = argv[2]

r = requests.get(blobUrl)
if r.status_code == 200:
    print("Writing to file:")
    with open(inputPath, 'wb') as f:
        for chunk in r:
            f.write(chunk)

# From the command line in the future
#inputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/test.glb"

# Delete all previous objects
for o in bpy.context.scene.objects:
    o.select_set(True)

# Call the DELETE-operator only once
bpy.ops.object.delete()
            
bpy.ops.import_scene.gltf(filepath=inputPath)

obj = bpy.context.object.children[0]

# Select each object
obj.select_set(True)
# Make it active
bpy.context.view_layer.objects.active = obj
# Toggle into Edit Mode
bpy.ops.object.mode_set(mode='EDIT')
# Select the geometry
bpy.ops.mesh.select_all(action='SELECT')
# Call the smart project operator
bpy.ops.uv.smart_project(island_margin=0.008)
# Toggle out of Edit Mode
bpy.ops.object.mode_set(mode='OBJECT')

# From command line in the future
#outputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/modelUV.glb"

bpy.ops.export_scene.gltf(filepath=outputPath)

# Grab Currrent Time After Running the Code
end = time.time()
#Subtract Start Time from The End Time
total_time = end - start
print("\n"+ "Running time for this script: " +str(total_time) + " seconds")