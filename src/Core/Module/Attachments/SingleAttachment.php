<?php

namespace Core\Module\Attachments;

use Core\MainConfig;
use Common\Service\ImageService;
use Core\Service\StringService;

class SingleAttachment
{
    public int $attach_id = 0;
    public string $alt = '';
    public string $attached_file = '';
    public array $metadata = [];

    public function __construct($attach_id, $metadata, $attached_file = '', $alt = '')
    {
        $this->attach_id     = $attach_id;
        $this->metadata      = $metadata;
        $this->attached_file = $attached_file;
        $this->alt           = $alt;
    }

    public function getUrl(string|array $size): string
    {
        $imageData = $this->getImageData($size);

        if (!empty($imageData['url'])) {
            return $imageData['url'];
        }

        return sprintf('%s/%s/%s', get_option('siteurl'), 'wp-content/uploads', $imageData['path']);
    }

    public function getImage(string|array $size = 'thumbnail', ?array $attr = []): string
    {

        $src       = StringService::replaceOnCdnUrl($this->getUrl($size));
        $imagedata = $this->getImageData($size); //$this->metadata;

        $width  = $imagedata['width'];
        $height = $imagedata['height'];

        $hwstring   = image_hwstring($width, $height);
        $size_class = $size;

        if (is_array($size_class)) {
            $size_class = implode('x', $size_class);
        }

        $default_attr = array(
            'src'   => $src,
            'class' => "attachment-$size_class size-$size_class",
            'alt'   => trim(strip_tags($this->alt))
        );

        if (wp_lazy_loading_enabled('img', 'wp_get_attachment_image')) {
            $default_attr['loading'] = 'lazy';
        }

        $attr = wp_parse_args($attr, $default_attr);

        if (array_key_exists('loading', $attr) && !$attr['loading']) {
            unset($attr['loading']);
        }

        if (empty($attr['srcset'])) {
            if (is_array($imagedata)) {

                $size_array = array( absint($width), absint($height) );
                $srcset     = wp_calculate_image_srcset($size_array, $src, $this->metadata, $this->attach_id);
                $sizes      = wp_calculate_image_sizes($size_array, $src, $this->metadata, $this->attach_id);

                if ($srcset && ($sizes || !empty($attr['sizes']))) {
                    $attr['srcset'] = $srcset;
                    if (empty($attr['sizes'])) {
                        $attr['sizes'] = $sizes;
                    }
                }
            }
        }

        if (str_contains($attr['class'], 'lazy')) {

            if (isset($attr['src'])) {
                $attr['data-src'] = $attr['src'];
                $attr['src']      = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            }

            if (isset($attr['srcset'])) {
                // $attr['data-srcset'] = $attr['srcset'];
                // $attr['srcset']      = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                unset($attr['srcset']);

            }

            if (isset($attr['sizes'])) {
                // $attr['data-sizes'] = $attr['sizes'];
                unset($attr['sizes']);
            }
        }

        $attr = array_map('esc_attr', $attr);
        $html = rtrim("<img $hwstring");

        foreach ($attr as $name => $value) {
            $html .= " $name=" . '"' . $value . '"';
        }

        $html .= ' />';

        return $html;
    }

    public function getImageData(string|array $size = 'thumbnail'): ?array
    {

        if (!$size || !is_array($this->metadata)) {
            return null;
        }

        $imagedata = $this->metadata;

        $data = array();

        if (!isset($imagedata['sizes']['full']) && isset($imagedata['height'])) {
            $imagedata['sizes']['full'] = [
                'width'  => $imagedata['width'],
                'height' => $imagedata['height'],
                'file'   => str_replace(dirname($imagedata['file']) . '/', '', $imagedata['file']),
            ];
        }

        // Find the best match when '$size' is an array.
        if (is_array($size)) {
            $candidates = array();

            if (!isset($imagedata['file']) && isset($imagedata['sizes']['full'])) {
                $imagedata['height'] = $imagedata['sizes']['full']['height'];
                $imagedata['width']  = $imagedata['sizes']['full']['width'];
            }

            foreach ($imagedata['sizes'] as $_size => $data) {
                // If there's an exact match to an existing image size, short circuit.
                if ((int) $data['width'] === (int) $size[0] && (int) $data['height'] === (int) $size[1]) {
                    $candidates[ $data['width'] * $data['height'] ] = $data;
                    break;
                }

                // If it's not an exact match, consider larger sizes with the same aspect ratio.
                if (function_exists('wp_image_matches_ratio') && $data['width'] >= $size[0] && $data['height'] >= $size[1]) {
                    // If '0' is passed to either size, we test ratios against the original file.
                    if (0 === $size[0] || 0 === $size[1]) {
                        $same_ratio = wp_image_matches_ratio($data['width'], $data['height'], $imagedata['width'], $imagedata['height']);
                    } else {
                        $same_ratio = wp_image_matches_ratio($data['width'], $data['height'], $size[0], $size[1]);
                    }

                    if ($same_ratio) {
                        $candidates[ $data['width'] * $data['height'] ] = $data;
                    }
                }
            }

            if (!empty($candidates)) {
                // Sort the array by size if we have more than one candidate.
                if (1 < count($candidates)) {
                    ksort($candidates);
                }

                $data = array_shift($candidates);

            } elseif (!empty($imagedata['sizes']['thumbnail']) && $imagedata['sizes']['thumbnail']['width'] >= $size[0] && $imagedata['sizes']['thumbnail']['width'] >= $size[1]) {
                $data = $imagedata['sizes']['thumbnail'];
            }

            if (!empty($data)) {
                list($data['width'], $data['height']) = function_exists('image_constrain_size_for_editor') ? image_constrain_size_for_editor($data['width'], $data['height'], $size) : [
                    150,
                    150
                ];
            }


        } elseif (!empty($imagedata['sizes'][ $size ])) {
            $data = $imagedata['sizes'][ $size ];
        }

        // если данные не найдены
        if (empty($data)) {

            if (!is_array($size)) { //если размер задан строкой
                $all_sizes = ImageService::getImageSizes();
                if (isset($all_sizes[ $size ])) {
                    return $this->getImageData([ $all_sizes[ $size ]['width'], $all_sizes[ $size ]['height'] ]);
                }
            }

            if (!empty($imagedata['sizes']['full'])) {
                return $this->getImageData([
                    $imagedata['sizes']['full']['width'],
                    $imagedata['sizes']['full']['height']
                ]);
            }

        }

        // Include the full filesystem path of the intermediate file.
        if (empty($data['path']) && !empty($data['file']) && !empty($imagedata['file'])) {
            //$file_url     = wp_get_attachment_url( $post_id );
            //$data['url']  = path_join( dirname( $file_url ), $data['file'] );
            $data['path'] = path_join(dirname($imagedata['file']), $data['file']);
            $file_url     = $this->getAttachmentUrl();
            $data['url']  = $file_url ? path_join(dirname($file_url), $data['file']) : sprintf('%s/%s/%s', get_option('siteurl'), 'wp-content/uploads', $data['path']);
        }

        return $data;

    }

    public function getAttachmentUrl()
    {

        $url = '';

        if ($this->attached_file) {
            // Get upload directory.
            $uploads = wp_get_upload_dir();
            if ($uploads && false === $uploads['error']) {
                // Check that the upload base exists in the file location.
                if (0 === strpos($this->attached_file, $uploads['basedir'])) {
                    // Replace file location with url location.
                    $url = str_replace($uploads['basedir'], $uploads['baseurl'], $this->attached_file);
                } elseif (false !== strpos($this->attached_file, 'wp-content/uploads')) {
                    // Get the directory name relative to the basedir (back compat for pre-2.7 uploads).
                    $url = trailingslashit($uploads['baseurl'] . '/' . _wp_get_attachment_relative_path($this->attached_file)) . wp_basename($this->attached_file);
                } else {
                    // It's a newly-uploaded file, therefore $file is relative to the basedir.
                    $url = $uploads['baseurl'] . "/$this->attached_file";
                }
            }
        }

        return $url;
    }

}
