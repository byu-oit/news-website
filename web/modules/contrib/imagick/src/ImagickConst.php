<?php

namespace Drupal\imagick;

use Imagick;

class ImagickConst {

  // All possible blur types
  const NORMAL_BLUR = 0;
  const ADAPTIVE_BLUR = 1;
  const GAUSSIAN_BLUR = 2;
  const MOTION_BLUR = 3;
  const RADIAL_BLUR = 4;

  /**
   * @return array
   */
  public static function composites() {
    return [
      'Color' => [
        Imagick::COMPOSITE_COPY => 'Copy',
        Imagick::COMPOSITE_COPYOPACITY => 'Opacity',
        Imagick::COMPOSITE_COPYBLACK => 'Black',
        Imagick::COMPOSITE_COPYBLUE => 'Blue',
        Imagick::COMPOSITE_COPYCYAN => 'Cyan',
        Imagick::COMPOSITE_COPYGREEN => 'Green',
        Imagick::COMPOSITE_COPYMAGENTA => 'Magenta',
        Imagick::COMPOSITE_COPYRED => 'Red',
        Imagick::COMPOSITE_COPYYELLOW => 'Yellow',
      ],
      'Source' => [
        Imagick::COMPOSITE_SRC => 'Copy',
        Imagick::COMPOSITE_SRCATOP => 'Atop',
        Imagick::COMPOSITE_SRCIN => 'In',
        Imagick::COMPOSITE_SRCOUT => 'Out',
        Imagick::COMPOSITE_SRCOVER => 'Over (replace)',
      ],
      'Destination' => [
        Imagick::COMPOSITE_DST => 'Leave untouched',
        Imagick::COMPOSITE_DSTATOP => 'Atop',
        Imagick::COMPOSITE_DSTIN => 'In',
        Imagick::COMPOSITE_DSTOUT => 'Out',
        Imagick::COMPOSITE_DSTOVER => 'Over (replace)',
      ],
      Imagick::COMPOSITE_UNDEFINED => 'Undefined',
      Imagick::COMPOSITE_OVER => 'Over',
      Imagick::COMPOSITE_NO => 'None',
      Imagick::COMPOSITE_ADD => 'Add',
      Imagick::COMPOSITE_ATOP => 'Atop',
      Imagick::COMPOSITE_BLEND => 'Blend',
      Imagick::COMPOSITE_BUMPMAP => 'Bumpmap',
      Imagick::COMPOSITE_CLEAR => 'Clear',
      Imagick::COMPOSITE_COLORBURN => 'Color burn',
      Imagick::COMPOSITE_COLORDODGE => 'Color dodge',
      Imagick::COMPOSITE_COLORIZE => 'Colorize',
      Imagick::COMPOSITE_DARKEN => 'Darken',
      Imagick::COMPOSITE_DIFFERENCE => 'Difference',
      Imagick::COMPOSITE_DISPLACE => 'Displace',
      Imagick::COMPOSITE_DISSOLVE => 'Dissolve',
      Imagick::COMPOSITE_EXCLUSION => 'Exclusion',
      Imagick::COMPOSITE_HARDLIGHT => 'Hard light',
      Imagick::COMPOSITE_HUE => 'Hue',
      Imagick::COMPOSITE_IN => 'In',
      Imagick::COMPOSITE_LIGHTEN => 'Lighten',
      Imagick::COMPOSITE_LUMINIZE => 'Luminize',
      Imagick::COMPOSITE_MINUS => 'Minus',
      Imagick::COMPOSITE_MODULATE => 'Modulate',
      Imagick::COMPOSITE_MULTIPLY => 'Multiply',
      Imagick::COMPOSITE_OUT => 'Out',
      Imagick::COMPOSITE_OVERLAY => 'Overlay',
      Imagick::COMPOSITE_PLUS => 'Plus',
      Imagick::COMPOSITE_REPLACE => 'Replace',
      Imagick::COMPOSITE_SATURATE => 'Saturate',
      Imagick::COMPOSITE_SCREEN => 'Screen',
      Imagick::COMPOSITE_SOFTLIGHT => 'Soft light',
      Imagick::COMPOSITE_SUBTRACT => 'Subtract',
      Imagick::COMPOSITE_THRESHOLD => 'Threshold',
      Imagick::COMPOSITE_XOR => 'XOR',
    ];
  }

  /**
   * @return array
   */
  public static function channels() {
    return [
      Imagick::CHANNEL_DEFAULT => 'Default',
      Imagick::CHANNEL_UNDEFINED => 'Undefined',
      Imagick::CHANNEL_ALPHA => 'Alpha',
      Imagick::CHANNEL_RED => 'Red | Gray | Cyan',
      Imagick::CHANNEL_GREEN => 'Green | Magenta',
      Imagick::CHANNEL_BLUE => 'Blue | Yellow',
      Imagick::CHANNEL_BLACK => 'Black',
      Imagick::CHANNEL_INDEX => 'Index',
      Imagick::CHANNEL_ALL => 'All',
    ];
  }
}
