<?php

namespace Core\Service;

use Core\MainConfig;
use Core\Module\Attachments\AttachmentsManager;
use Core\Module\Attachments\SingleAttachment;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @method static ImageService init()
 */
class ImageService extends ServiceAbstract
{
    public const SIZE_SMALL = 'thumbnail';
    public const SIZE_MEDIUM = 'size-350';
    public const SIZE_LARGE = 'size-700';
    public const SIZE_FULL = 'full';

    private const REGENERATE_ENDPOINT = '/wp-json/don/v2/file/attachment/regenerate';

    public function regeneratedAttachmentsByImageId(int $imageId): SingleAttachment
    {
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachments = AttachmentsManager::setupAttachments([ $imageId ]);
        $attachment  = $attachments->attachment($imageId);

        $originalSource = self::getUploadBaseDir($attachment->metadata['file']);

        if (!is_file($originalSource)) {
            throw new FileNotFoundException();
        }

        if ($attachment->metadata) {
            $currentSizes = self::getImageSizes();
            foreach ($attachment->metadata['sizes'] as $size => $data) {

                if (isset($currentSizes[ $size ])) {
                    continue;
                }

                $imageData = $attachment->getImageData($size);

                $fileSource = self::getUploadBaseDir($imageData['path']);

                if (is_file($fileSource)) {
                    unlink($fileSource);
                }
            }
        }

        $attach_data = wp_generate_attachment_metadata($imageId, $originalSource);
        wp_update_attachment_metadata($imageId, $attach_data);
        $attachment->metadata = $attach_data;

        return $attachment;
    }

    public static function getGeneratedUrlByAttachment(SingleAttachment $attachment, string $sizeName): string
    {
        if ($sizeName !== self::SIZE_FULL && !self::isHasAttachmentSizeByString($attachment, $sizeName)) {
            $originalSource = self::getUploadBaseDir($attachment->metadata['file']);
            if (is_file($originalSource)) {
                return self::REGENERATE_ENDPOINT . '?id=' . $attachment->attach_id . '&size=' . $sizeName . '&nonce=' . md5('nonce|' . $attachment->attach_id);
            }
        }

        return StringService::replaceOnCdnUrl($attachment->getUrl($sizeName));

    }

    public static function isHasAttachmentSizeByString(SingleAttachment $attachment, string $sizeName): bool
    {
        if (isset($attachment->metadata['sizes'][ $sizeName ])) {
            return true;
        }

        $sizes = self::getImageSizes();

        $size = $sizes[ $sizeName ] ?? null;

        if (!$size) {
            return false;
        }

        if ($size['width'] >= $attachment->metadata['width'] && $size['height'] >= $attachment->metadata['height']) {
            return true;
        }

        return false;
    }

    public static function getImageSizes(?bool $unset_disabled = true): array
    {

        $sizes = array();

        foreach (array( 'thumbnail', 'medium', 'medium_large', 'large' ) as $_size) {
            $sizes[ $_size ] = array(
                'width'  => get_option("{$_size}_size_w"),
                'height' => get_option("{$_size}_size_h"),
                'crop'   => (bool) get_option("{$_size}_crop"),
            );

        }

        foreach (MainConfig::ATTACHMENT_SIZES as $_size => $config) {
            $sizes[ $_size ] = array(
                'width'  => $config['width'],
                'height' => $config['height'],
                'crop'   => $config['crop'],
            );
        }

        if ($unset_disabled) {
            foreach ($sizes as $_size => $config) {
                if ($config['width'] == 0 && $config['height'] == 0) {
                    unset($sizes[ $_size ]);
                }
            }
        }

        return $sizes;
    }

    private static function getUploadBaseDir(string $path): string
    {
        return WP_CONTENT_DIR . '/uploads/' . $path;
    }

}
