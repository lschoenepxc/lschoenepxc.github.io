import bpy

inputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/test.glb"
            
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
bpy.ops.uv.smart_project(island_margin=0.001)
# Toggle out of Edit Mode
bpy.ops.object.mode_set(mode='OBJECT')

outputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/modelUV.glb"

bpy.ops.export_scene.gltf(filepath=outputPath)

bpy.ops.object.delete(use_global=False)