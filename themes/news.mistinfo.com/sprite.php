<?php

class Sprite
{
    public static function combine($images, $output_dir, $distance = 0, $postfix = '', $output_format = 'png')
    {
        $half = 0;

        $coord = array();
        $y = 0;

        foreach ($images as $i => $data) {
            $ext = pathinfo($data, PATHINFO_EXTENSION);
            $imagesOutput[$i] = array(
                'filename' => str_replace('.' . $ext, '', basename($data)),
                'x' => 0,
                'y' => $y
            );

            $coord[] = '-page +0+' . $y . ' ' . $data;
            $y += $distance;
        }

        $cmd = 'convert ' . implode(' ', $coord) . ' -background none -channel RGBA -matte -colorspace gray -mosaic -bordercolor none -border ' . $half . 'x' . $half . ' ' . $output_dir . '/assets/images/icons' . $postfix . '.' . $output_format;
        system($cmd, $ret);

        //$cmd = 'convert ' . implode(' ', $coord) . '  -background none -mosaic -bordercolor none -border ' . $half . 'x' . $half . ' ' . $output_dir . '/assets/images/_icons' . $postfix . '.' . $output_format;
        //system($cmd, $ret);

        $css = '.bz-ctg-icon' . $postfix . '{background-image:url("../images/icons' . $postfix . '.png");}';
        //$css .= '.b-maincategory:hover .bz-ctg-icon' . $postfix . ', .bz-ctg-icon' . $postfix . '.active, .nav a:hover .bz-ctg-icon' . $postfix . '{background-image:url("../images/_icons' . $postfix . '.png");}';
        foreach ($imagesOutput as $i => $img) {
            $css .= '.bz-ctg-' . $img['filename'] . $postfix . '{background-position:' . $img['x'] . ' -' . $img['y'] . 'px;}';
        }
        file_put_contents(dirname(__FILE__) . '/assets/css/icons' . $postfix . '.css', $css);
        return $ret === 0;
    }
}

$images = glob(dirname(__FILE__) . '/assets/images/icons/*.png');
$output_dir = dirname(__FILE__);

//Sprite::combine($images, $output_dir, 64);

foreach ($images as $i => $data) {
    $ext = pathinfo($data, PATHINFO_EXTENSION);
    $cmd = 'convert ' . $data . ' -filter box -negate -resize 48x48 ' . $output_dir . '/assets/images/icons/small/' . str_replace('.' . $ext, '', basename($data)) . '.png';
    system($cmd, $ret);
}

$images = glob(dirname(__FILE__) . '/assets/images/icons/small/*.png');
Sprite::combine($images, $output_dir, 48);