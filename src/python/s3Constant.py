class S3Constant:

    def __init__(self):
        self._aws_access_key_id = 'AKIAI756KHBT7MT5F2CA'
        self._asw_secret_access_key = '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'
        self._s3_bucket = 'photo-editor-bot'

    @property
    def getAwsAccessKeyId(self):
        return self._aws_access_key_id

    @property
    def getAwsSecretAccessKey(self):
        return self._asw_secret_access_key

    @property
    def getS3Bucket(self):
        return self._s3_bucket
