import cv2
import numpy as np
import argparse
import subprocess
import os
import sys
import tempfile

# Add rembg for human masking
try:
    from rembg import remove
except ImportError:
    remove = None

def sharpen(image, amount=0.5):
    # Unsharp masking: smooth, natural sharpening
    blurred = cv2.GaussianBlur(image, (0, 0), 3)
    sharpened = cv2.addWeighted(image, 1 + amount, blurred, -amount, 0)
    return sharpened

def color_correct(image):
    img_yuv = cv2.cvtColor(image, cv2.COLOR_BGR2YUV)
    img_yuv[:, :, 0] = cv2.equalizeHist(img_yuv[:, :, 0])
    return cv2.cvtColor(img_yuv, cv2.COLOR_YUV2BGR)

def denoise(image):
    return cv2.fastNlMeansDenoisingColored(image, None, 10, 10, 7, 21)

def sharpen_human(image):
    if remove is None:
        print('rembg not installed. Cannot use --sharpen-human.')
        return image
    # rembg expects PIL or bytes, but we use OpenCV, so convert
    import PIL.Image
    from io import BytesIO
    img_pil = PIL.Image.fromarray(cv2.cvtColor(image, cv2.COLOR_BGR2RGB))
    result = remove(img_pil)
    # result is RGBA, alpha is mask
    result_np = np.array(result)
    if result_np.shape[2] == 4:
        mask = result_np[:, :, 3]
    else:
        mask = (result_np[:, :, 0] > 0).astype(np.uint8) * 255
    mask = cv2.threshold(mask, 128, 255, cv2.THRESH_BINARY)[1]
    mask = cv2.cvtColor(mask, cv2.COLOR_GRAY2BGR) // 255
    sharpened = sharpen(image)
    output = image * (1 - mask) + sharpened * mask
    output = output.astype(np.uint8)
    return output

def download_deblurgan_weights():
    import os
    import gdown
    weights_dir = os.path.expanduser('~/.cache/deblurgan')
    os.makedirs(weights_dir, exist_ok=True)
    weights_path = os.path.join(weights_dir, 'deblurganv2_wild.pth')
    if not os.path.exists(weights_path):
        print('Downloading DeblurGAN-v2 weights...')
        gdown.download('https://drive.google.com/uc?id=1Fqgk1kQwQnQnQnQnQnQnQnQnQnQnQnQn', weights_path, quiet=False)
    return weights_path

def refocus(image_path, output_path):
    try:
        script_path = os.path.join(os.path.dirname(__file__), 'deblur_image.py')
        cmd = [sys.executable, script_path, '--input', image_path, '--output', output_path]
        subprocess.run(cmd, check=True)
        # Read the output image
        result = cv2.imread(output_path)
        if result is not None:
            return result
        else:
            print('DeblurGAN-v2 did not produce output image.')
            return cv2.imread(image_path)
    except Exception as e:
        print(f'DeblurGAN-v2 refocus failed: {e}')
        return cv2.imread(image_path)

def main():
    parser = argparse.ArgumentParser(description='Photo Enhancer: Sharpen, Color Correct, Denoise, Sharpen Human Only, Refocus')
    parser.add_argument('--input', '-i', required=True, help='Input image path')
    parser.add_argument('--output', '-o', required=True, help='Output image path')
    parser.add_argument('--sharpen', action='store_true', help='Apply sharpening')
    parser.add_argument('--color', action='store_true', help='Apply color correction')
    parser.add_argument('--denoise', action='store_true', help='Apply denoising')
    parser.add_argument('--sharpen-human', action='store_true', help='Sharpen only the detected human region')
    parser.add_argument('--refocus', action='store_true', help='Deblur/refocus the image')
    args = parser.parse_args()

    image = cv2.imread(args.input)
    if image is None:
        print('Error: Could not read input image.')
        exit(1)

    mask = None
    if args.sharpen_human or (args.refocus and args.sharpen_human):
        try:
            import PIL.Image
            from rembg import remove
            img_pil = PIL.Image.fromarray(cv2.cvtColor(image, cv2.COLOR_BGR2RGB))
            result = remove(img_pil)
            result_np = np.array(result)
            if result_np.shape[2] == 4:
                mask = result_np[:, :, 3]
            else:
                mask = (result_np[:, :, 0] > 0).astype(np.uint8) * 255
            mask = cv2.threshold(mask, 128, 255, cv2.THRESH_BINARY)[1]
        except Exception as e:
            print(f'Failed to generate mask for human region: {e}')
            mask = None

    if args.sharpen:
        image = sharpen(image)
    if args.sharpen_human:
        image = sharpen_human(image)
    if args.color:
        image = color_correct(image)
    if args.denoise:
        image = denoise(image)
    if args.refocus:
        with tempfile.NamedTemporaryFile(suffix='.png', delete=False) as tmp_in, tempfile.NamedTemporaryFile(suffix='.png', delete=False) as tmp_out:
            cv2.imwrite(tmp_in.name, image)
            image = refocus(tmp_in.name, tmp_out.name)
            os.remove(tmp_in.name)
            os.remove(tmp_out.name)

    cv2.imwrite(args.output, image)
    print(f'Enhanced image saved to {args.output}')

if __name__ == '__main__':
    main() 