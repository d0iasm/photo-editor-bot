import os
import sys
import cv2
import boto3
import botocore
import s3Constant

class Filter(object):
    """docstring for ."""
    # def __init__(self, arg):
        # self.arg = arg

    s3Constant = s3Constant.S3Constant()
    AWS_ACCESS_KEY_ID = s3Constant.getAwsAccessKeyId
    AWS_SECRET_ACCESS_KEY = s3Constant.getAwsSecretAccessKey
    S3_BUCKET = s3Constant.getS3Bucket

    s3 = boto3.resource('s3')
    bucket = s3.Bucket(S3_BUCKET)

    print(bucket.name)

    # session = boto3.Session(
    #     aws_access_key_id = AWS_ACCESS_KEY_ID
    #     aws_secret_access_key = AWS_SECRET_ACCESS_KEY,
    #     region_name = 'ap-northeast-1'
    # )

    s3client = boto3.Session().client('s3')

    response = s3client.list_objects(
        Bucket = S3_BUCKET,
        Prefix = 'hoge'
    )

    if 'Contents' in response:
        keys = [content['Key'] for content in response['Contents']]
        print(keys)

    # rowImage = []
    # for key in keys:
    #     fp = StringIO()
    #     key.get_contents_to_file(fp)
    #     fp.seek(0)
    #
    #     print (fp.getvalue())
    #     fp.close()


    data = open('dest/half.jpg', 'rb')
    s3.Bucket(S3_BUCKET).put_object(Key='test.jpg', Body=data)


    # bucket = s3.Bucket(S3_BUCKET)
    # exists = True
    # try:
    #     s3.meta.client.head_bucket(Bucket=S3_BUCKET)
    # except botocore.exceptions.ClientError as e:
    #     # If a client error is thrown, then check that it was a 404 error.
    #     # If it was a 404 error, then the bucket does not exist.
    #     error_code = int(e.response['Error']['Code'])
    #     if error_code == 404:
    #         exists = False

    img = cv2.imread('../../images/camera.png', cv2.IMREAD_COLOR)

    imgHeight, imgWidth = img.shape[:2]
    size = (int(imgHeight/2), int(imgWidth/2))

    halfImg = cv2.resize(img, size)

    dirname = 'dest'
    if not os.path.exists(dirname):
        os.mkdir(dirname)

    cv2.imwrite(os.path.join(dirname, 'half.jpg'), halfImg)
