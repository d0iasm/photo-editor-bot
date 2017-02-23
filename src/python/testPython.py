import os
import sys
import cv2

print('testPython.py実行')

if __name__=='__main__':

    print('testPython.pyの__main__')

    def editImage():
        print('testPython.pyのeditImage()')
        print ('parameter1 is' + sys.argv[1])
        print ('parameter2 is' + sys.argv[2])
        print ('result is OK!')
        print ('result is NG!')

    editImage()
