import sys
import os
import cv2
import numpy as np
from PIL import Image
from realesrgan.utils import RealESRGANer
from basicsr.archs.rrdbnet_arch import RRDBNet
import time

if len(sys.argv) != 4:
    print("Usage: python upscale.py <input_path> <output_path> <scale>")
    sys.exit(1)

input_path = sys.argv[1]
output_path = sys.argv[2]
scale = int(sys.argv[3])

# Progress tracking
def update_progress(progress, message=""):
    print(f"PROGRESS:{progress}:{message}")
    sys.stdout.flush()
    time.sleep(0.1)  # Small delay to ensure output is captured

# Load image
update_progress(5, "Starting upscaling process...")
update_progress(10, "Loading image...")
img = cv2.imread(input_path, cv2.IMREAD_UNCHANGED)
if img is None:
    print(f"Failed to load image: {input_path}")
    sys.exit(1)

update_progress(15, "Image loaded successfully")

# Use absolute path for models directory
models_dir = os.path.join(os.path.dirname(__file__), 'models')

# Select model and weights for the scale
update_progress(20, "Preparing model...")
if scale == 2:
    model_path = os.path.join(models_dir, 'RealESRGAN_x2plus.pth')
    model = RRDBNet(num_in_ch=3, num_out_ch=3, num_feat=64, num_block=23, num_grow_ch=32, scale=2)
elif scale == 4:
    model_path = os.path.join(models_dir, 'RealESRGAN_x4plus.pth')
    model = RRDBNet(num_in_ch=3, num_out_ch=3, num_feat=64, num_block=23, num_grow_ch=32, scale=4)
else:
    print("Only scale=2 or scale=4 is supported by default.")
    sys.exit(1)

update_progress(25, "Model configuration ready")

# Create upsampler
update_progress(30, "Initializing upscaler...")
upsampler = RealESRGANer(
    scale=scale,
    model_path=model_path,
    model=model,
    tile=0,
    tile_pad=10,
    pre_pad=0,
    half=False
)

update_progress(35, "Upscaler initialized")

# Run upscaling
try:
    update_progress(40, "Starting upscaling process...")
    update_progress(50, "Processing image...")
    output, _ = upsampler.enhance(img, outscale=scale)
    update_progress(70, "Upscaling completed...")
    update_progress(80, "Saving image...")
    cv2.imwrite(output_path, output)
    update_progress(90, "Image saved successfully")
    update_progress(100, "Upscaling process completed")
    print(f"Upscaled image saved to {output_path}")
except Exception as e:
    print(f"Upscaling failed: {e}")
    sys.exit(1) 