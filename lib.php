<?php

namespace benignware\webp_otf {
  function get_image_webp($source, $destination = null, $quality = 100) {
    if (!$destination) {
      $dir = pathinfo($source, PATHINFO_DIRNAME);
      $name = pathinfo($source, PATHINFO_FILENAME);
      $ext = pathinfo($source, PATHINFO_EXTENSION);
      $destination = $dir . DIRECTORY_SEPARATOR . $name . '.' . $ext . '.webp';
    }
        
    $info = getimagesize($source);
    $is_alpha = false;

    if ($info['mime'] == 'image/jpeg') {
      $image = imagecreatefromjpeg($source);
    } elseif ($is_alpha = $info['mime'] == 'image/gif') {
      $image = imagecreatefromgif($source);
    } elseif ($is_alpha = $info['mime'] == 'image/png') {
      $image = imagecreatefrompng($source);
    } else {
      return $source;
    }

    if ($is_alpha) {
      imagepalettetotruecolor($image);
      imagealphablending($image, true);
      imagesavealpha($image, true);
    }

    imagewebp($image, $destination, $quality);

    return $destination;
  }

  function get_src_dir() {
    return wp_upload_dir()['path'];
  }

  function get_dest_dir() {
    return wp_upload_dir()['path'] . '/webp-otf';
  }

  function get_image_file($image_url, $relative = false) {
    $upload_info = wp_upload_dir();
    $upload_url = $upload_info['url'];
    $upload_dir = $upload_info['path'];
    $image_path = '';

    if (substr($image_url, 0, strlen($upload_url)) == $upload_url) {
      $image_path = substr($image_url, strlen($upload_url));
      $image_file = $upload_dir . $image_path;

      if (file_exists($image_file)) {
        return $relative ? ltrim($image_path, '/') : $image_file;
      }
    }

    return null;
  }

  function get_image_url($image_file) {
    $upload_info = wp_upload_dir();
    $upload_url = $upload_info['url'];
    $upload_dir = $upload_info['path'];

    if (substr($image_file, 0, strlen($upload_dir)) == $upload_dir) {
      $image_path = substr($image_file, strlen($upload_dir));
      $image_url = $upload_url . $image_path;

      return $image_url;
    }

    return null;
  }

  function get_image_webp_url($image_url) {
    $image_file = get_image_file($image_url, true);

    if (!$image_file) {
      return $image_url;
    }

    $src_dir = get_src_dir();
    $src_file = $src_dir . '/' . $image_file;

    $dest_dir = get_dest_dir();

    if (!is_dir($dest_dir)) {
      mkdir($dest_dir, 0777, true);
    }

    $dest_file = $dest_dir . '/' . $image_file . '.webp';
    $dest_file_dir = dirname($dest_file);

    if (!is_dir($dest_file_dir)) {
      mkdir($dest_file_dir, 0777, true);
    }

    if (!file_exists($dest_file) || filemtime($src_file) < filemtime($dest_file)) {
      $dest_file = get_image_webp($src_file, $dest_file);
    }

    if (file_exists($dest_file)) {
      return get_image_url($dest_file);
    }

    return $image_url;
  }
}
