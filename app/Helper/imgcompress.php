<?php


namespace App\Helper;



   /**
4  * 图片压缩类：通过缩放来压缩。
5  * 如果要保持源图比例，把参数$percent保持为1即可。
6  * 即使原比例压缩，也可大幅度缩小。数码相机4M图片。也可以缩为700KB左右。如果缩小比例，则体积会更小。
7  *
8  * 结果：可保存、可直接显示。
9  */
  class imgcompress{

      /**
       * desription 压缩图片
       * @param sting $imgsrc 图片路径
       * @param string $imgdst 压缩后保存路径
       */
      public function compressedImage($imgsrc, $imgdst) {
          list($width, $height, $type) = getimagesize($imgsrc);

          $new_width = $width;//压缩后的图片宽
          $new_height = $height;//压缩后的图片高

          if($width >= 600){
              $per = 600 / $width;//计算比例
              $new_width = $width * $per;
              $new_height = $height * $per;
          }

          switch ($type) {
              case 1:
                  $giftype = check_gifcartoon($imgsrc);
                  if ($giftype) {
                      header('Content-Type:image/gif');
                      $image_wp = imagecreatetruecolor($new_width, $new_height);
                      $image = imagecreatefromgif($imgsrc);
                      imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                      //90代表的是质量、压缩图片容量大小
                      imagejpeg($image_wp, $imgdst, 90);
                      imagedestroy($image_wp);
                      imagedestroy($image);
                  }
                  break;
              case 2:
                  header('Content-Type:image/jpeg');
                  $image_wp = imagecreatetruecolor($new_width, $new_height);
                  $image = imagecreatefromjpeg($imgsrc);
                  imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                  //90代表的是质量、压缩图片容量大小
                  imagejpeg($image_wp, $imgdst, 90);
                  imagedestroy($image_wp);
                  imagedestroy($image);
                  break;
              case 3:
                  header('Content-Type:image/png');
                  $image_wp = imagecreatetruecolor($new_width, $new_height);
                  $image = imagecreatefrompng($imgsrc);
                  imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                  //90代表的是质量、压缩图片容量大小
                  imagejpeg($image_wp, $imgdst, 90);
                  imagedestroy($image_wp);
                  imagedestroy($image);
                  break;
          }
      }

 }
