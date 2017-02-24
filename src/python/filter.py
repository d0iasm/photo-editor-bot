import os
import sys
import numpy
import cv2
import boto3
import botocore
import s3Constant

s3Constant = s3Constant.S3Constant()
AWS_ACCESS_KEY_ID = s3Constant.getAwsAccessKeyId
AWS_SECRET_ACCESS_KEY = s3Constant.getAwsSecretAccessKey
S3_BUCKET = s3Constant.getS3Bucket

s3client = boto3.Session(
    aws_access_key_id = AWS_ACCESS_KEY_ID,
    aws_secret_access_key = AWS_SECRET_ACCESS_KEY,
    region_name = 'ap-northeast-1'
).client('s3')

class Filter(object):
    """docstring for ."""
    # def __init__(self, arg):
        # self.arg = arg

    # s3 = boto3.resource('s3')
    # bucket = s3.Bucket(S3_BUCKET)

    # response = s3client.list_objects(
    #     Bucket = S3_BUCKET,
    #     Prefix = 'hoge'
    # )

    # if 'Contents' in response:
    #     keys = [content['Key'] for content in response['Contents']]
    #     print(keys)


    # img = cv2.imread('../../images/camera.png', cv2.IMREAD_COLOR)

    def edit_image():
        raw_data = s3client.get_object(Bucket=S3_BUCKET, Key='raw_image.jpg')
        img = numpy.asarray(bytearray(raw_data['Body'].read()), dtype="uint8")
        img = cv2.imdecode(img, cv2.IMREAD_COLOR)

        imgHeight, imgWidth = img.shape[:2]
        size = (int(imgHeight/2), int(imgWidth/2))

        halfImg = cv2.resize(img, size)

        dirname = 'dest'
        if not os.path.exists(dirname):
            os.mkdir(dirname)

        cv2.imwrite(os.path.join(dirname, 'edited_image.jpg'), halfImg)

    def send_edited_image():
        data = open('dest/edited_image.jpg', 'rb')
        s3client.put_object(Bucket=S3_BUCKET, Key='edited_image.jpg', Body=data)
        data.close()

    edit_image()
    send_edited_image()
