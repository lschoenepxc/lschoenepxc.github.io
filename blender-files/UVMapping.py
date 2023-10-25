import bpy 
import sys
import time

# Command from command line
# blender --background --python UVMapping.py -- blobUrl localPath outputPath
# blender/blender --background --python blender-files/UVMapping.py -- C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/test.glb

# Grab Currrent Time Before Running the Code
start = time.time()

argv = sys.argv
argv = argv[argv.index("--") + 1:]  # get all args after "--"

# print(argv)  # --> ['inputPath']

# LocalPath on server where we write the glb to
inputPath = argv[0] 

# Output path on server where we write the UV-Mapped glb to
outputPath = inputPath

# Delete all previous objects
for o in bpy.context.scene.objects:
    o.select_set(True)

# Call the DELETE-operator only once
bpy.ops.object.delete()
            
bpy.ops.import_scene.gltf(filepath=inputPath)

obj = bpy.context.object.children[0].children[0]

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
# Scale UV-Map
#bpy.ops.uv.muv_world_scale_uv_apply_manual(tgt_density=250, tgt_texture_size=(2376, 1584), origin='CENTER', show_dialog=False, tgt_area_calc_method='MESH', only_selected=True)
bpy.ops.uv.muv_world_scale_uv_apply_manual(tgt_density=208.12, tgt_texture_size=(8000, 6000), origin='CENTER', show_dialog=False, tgt_area_calc_method='MESH', only_selected=True)
print("Scales UV")
# UVW Mapping
# bpy.ops.uv.muv_uvw_best_planer_map(assign_uvmap=True)
# print("UVW Mapping")
# Toggle out of Edit Mode
bpy.ops.object.mode_set(mode='OBJECT')

bpy.ops.export_scene.gltf(filepath=outputPath)

# Grab Currrent Time After Running the Code
end = time.time()
#Subtract Start Time from The End Time
total_time = end - start
print("\n"+ "Running time for this script: " +str(total_time) + " seconds")

t = time.localtime()
current_time = time.strftime("%H:%M:%S", t)
print(current_time)