import sys
import os
import argparse
import subprocess

def main():
    parser = argparse.ArgumentParser(description='Deblur image using DeblurGAN-v2')
    parser.add_argument('--input', '-i', required=True, help='Input image path')
    parser.add_argument('--output', '-o', required=True, help='Output image path')
    args = parser.parse_args()

    # Path to DeblurGAN-v2 repo
    deblurgan_dir = os.path.join(os.path.dirname(__file__), 'deblurganv2')
    infer_script = os.path.join(deblurgan_dir, 'test.py')
    weights = os.path.join(deblurgan_dir, 'experiments/pretrained_models/deblurganv2_wild.pth')

    # DeblurGAN-v2 expects an input folder and output folder
    input_dir = os.path.join(deblurgan_dir, 'input_tmp')
    output_dir = os.path.join(deblurgan_dir, 'output_tmp')
    os.makedirs(input_dir, exist_ok=True)
    os.makedirs(output_dir, exist_ok=True)

    # Copy input image to input_dir
    import shutil
    input_basename = os.path.basename(args.input)
    input_path = os.path.join(input_dir, input_basename)
    shutil.copy(args.input, input_path)

    # Run DeblurGAN-v2 inference
    cmd = [
        sys.executable, infer_script,
        '--input_dir', input_dir,
        '--results_dir', output_dir,
        '--weights_path', weights,
        '--dataset_mode', 'folder',
        '--model', 'deblurganv2',
        '--no_resize',
        '--save_img'
    ]
    subprocess.run(cmd, check=True)

    # Find output image
    output_img_path = os.path.join(output_dir, input_basename)
    if not os.path.exists(output_img_path):
        print('DeblurGAN-v2 did not produce output image.')
        sys.exit(1)
    shutil.copy(output_img_path, args.output)

    # Clean up temp files (optional)
    os.remove(input_path)
    os.remove(output_img_path)

if __name__ == '__main__':
    main() 