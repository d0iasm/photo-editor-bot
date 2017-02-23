import os
import cv2

img = cv2.imread('../../images/camera.png', cv2.IMREAD_COLOR)

imgHeight, imgWidth = img.shape[:2]
size = (int(imgHeight/2), int(imgWidth/2))

halfImg = cv2.resize(img, size)

dirname = 'dest'
os.mkdir(dirname)

cv2.imwrite(os.path.join(dirname, 'half.jpg'), halfImg)
