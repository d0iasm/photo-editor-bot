import os
import sys
import cv2
import boto3
import s3Constant

class Filter(object):
    """docstring for ."""
    # def __init__(self, arg):
        # self.arg = arg

    s3Constant = s3Constant.S3Constant()
    AWS_ACCESS_KEY_ID = s3Constant.getAwsAccessKeyId
    AWS_SECRET_ACCESS_KEY = s3Constant.getAwsSecretAccessKey
    S3_BUCKET = s3Constant.getS3Bucket

    print(AWS_ACCESS_KEY_ID)

    s3 = boto3.resource('s3')

    img = cv2.imread('../../images/camera.png', cv2.IMREAD_COLOR)

    imgHeight, imgWidth = img.shape[:2]
    size = (int(imgHeight/2), int(imgWidth/2))

    halfImg = cv2.resize(img, size)

    dirname = 'dest'
    if not os.path.exists(dirname):
        os.mkdir(dirname)

    cv2.imwrite(os.path.join(dirname, 'half.jpg'), halfImg)
