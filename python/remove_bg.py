import sys
from rembg import remove
from PIL import Image
import numpy as np
import io

if len(sys.argv) < 3:
    print("Usage: python remove_bg.py <input_path> <output_path> [output_format] [jpeg_bgcolor]")
    sys.exit(1)

input_path = sys.argv[1]
output_path = sys.argv[2]
output_format = sys.argv[3] if len(sys.argv) > 3 else 'png'
jpeg_bgcolor = sys.argv[4] if len(sys.argv) > 4 else '#ffffff'

input_image = Image.open(input_path)
output = remove(
    input_image,
    alpha_matting=True,
    alpha_matting_foreground_threshold=240,
    alpha_matting_background_threshold=10,
    alpha_matting_erode_size=10
)

# Ensure output is a PIL Image
if isinstance(output, Image.Image):
    output_image = output
elif isinstance(output, bytes):
    output_image = Image.open(io.BytesIO(output))
elif isinstance(output, np.ndarray):
    output_image = Image.fromarray(output)
else:
    raise Exception("Unknown output type from rembg.remove")

def hex_to_rgb(hex_color):
    hex_color = hex_color.lstrip('#')
    return tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))

if output_format.lower() == 'jpeg' or output_format.lower() == 'jpg':
    bg_color = hex_to_rgb(jpeg_bgcolor)
    bg = Image.new("RGB", output_image.size, bg_color)
    if output_image.mode == 'RGBA':
        bg.paste(output_image, mask=output_image.split()[3])  # Use alpha channel as mask
    else:
        bg.paste(output_image)
    bg.save(output_path, "JPEG")
else:
    output_image.save(output_path, "PNG") 