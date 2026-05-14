import sys
import json
import os
import cv2
import numpy as np
from PIL import Image, ImageChops
import piexif

def ela_check(image_path, quality=90):
    """
    Error Level Analysis (ELA) detects if parts of an image 
    have been digitally modified by comparing the original 
    to a recompressed version.
    """
    try:
        original = Image.open(image_path).convert('RGB')
        temp_path = image_path + '_ela_temp.jpg'
        
        # Save at a specific quality
        original.save(temp_path, 'JPEG', quality=quality)
        recompressed = Image.open(temp_path)
        
        # Calculate difference
        ela_image = ImageChops.difference(original, recompressed)
        ela_array = np.array(ela_image)
        
        # Calculate ratio of pixels with high difference (> 30)
        # We use axis=2 to find any channel difference in a pixel
        suspicious_pixels = np.any(ela_array > 30, axis=2).sum()
        total_pixels = original.size[0] * original.size[1]
        ratio = float(suspicious_pixels) / total_pixels
        
        # Cleanup
        if os.path.exists(temp_path):
            os.remove(temp_path)
            
        return ratio
    except Exception:
        return 0.0

def blur_check(image_path):
    """
    Measures image sharpness using the Laplacian variance.
    Extremely high/low variance can indicate unnatural sharpness or 
    blur typical of digital inserts.
    """
    try:
        img = cv2.imread(image_path, cv2.IMREAD_GRAYSCALE)
        if img is None:
            return 0.0
        return float(cv2.Laplacian(img, cv2.CV_64F).var())
    except Exception:
        return 0.0

def metadata_check(image_path):
    """
    Checks EXIF metadata for signatures of common editing software.
    """
    try:
        exif_dict = piexif.load(image_path)
        software = exif_dict.get('0th', {}).get(piexif.ImageIFD.Software, b'')
        
        if isinstance(software, bytes):
            software = software.decode('utf-8', 'ignore')
            
        suspicious_software = ['Photoshop', 'GIMP', 'Lightroom', 'Snapseed']
        for s in suspicious_software:
            if s.lower() in software.lower():
                return True
        return False
    except Exception:
        return False

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No image path provided"}))
        return

    image_path = sys.argv[1]
    
    if not os.path.exists(image_path):
        print(json.dumps({"error": "Image file not found"}))
        return

    ela_ratio = ela_check(image_path)
    blur_variance = blur_check(image_path)
    suspicious_metadata = metadata_check(image_path)
    
    score = 100
    flags = []
    
    if ela_ratio > 0.10:
        score -= 40
        flags.append("High ELA anomaly")
    elif ela_ratio > 0.05:
        score -= 20
        flags.append("Moderate ELA anomaly")
        
    if suspicious_metadata:
        score -= 40
        flags.append("Suspicious EXIF software")
        
    # Detect blur inconsistency (heuristic: extreme sharpness or blur)
    if blur_variance > 1200 or blur_variance < 15:
        score -= 20
        flags.append("Sharpness/Blur inconsistency")

    status = 'verified' if score >= 60 else 'failed'
    
    print(json.dumps({
        "score": max(0, score),
        "status": status,
        "ela_ratio": round(ela_ratio, 4),
        "suspicious_metadata": suspicious_metadata,
        "blur_variance": round(blur_variance, 2),
        "flags": flags
    }))

if __name__ == "__main__":
    main()
