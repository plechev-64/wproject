<?php

namespace Core\Module\Attachments;

use Core\Entity\Post\PostMeta;
use Core\ORM;
use Core\Service\StringService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class AttachmentsManager
{
    public array $attachments = [];

    public function __construct($attachments)
    {
        $this->attachments = $attachments;
    }

    public function isHas($id): bool
    {
        return isset($this->attachments[ $id ]);
    }

    public function attachment($id): ?SingleAttachment
    {
        return $this->attachments[ $id ] ?? null;
    }

    public function getThumbnailImage($post_id, $size, $atts = []): ?string
    {

        if (!$this->isHas($post_id)) {
            return null;
        }

        $attachment = $this->attachment($post_id);

        if ($image = $attachment->getImage($size, $atts)) {
            return $image;
        } else {
            return get_the_post_thumbnail($post_id, $size, $atts);
        }
    }

    public static function appendAlt($thumbnails)
    {

        $attachIds = [];
        foreach($thumbnails as $thumbnail) {
            $attachIds[$thumbnail['attachId']] = $thumbnail['attachId'];
        }

        $metaData = ORM::get()
            ->getRepository(PostMeta::class)
            ->createQueryBuilder('pm')
            ->addSelect('pm.value as alt')
            ->addSelect('pm.postId')
            ->andWhere('pm.key=\'_wp_attachment_image_alt\'')
            ->andWhere('pm.postId IN (:ids)')
            ->setParameter('ids', $attachIds)
            ->getQuery()
            ->getResult();

        if($metaData) {
            $alts = [];
            foreach($metaData as $meta) {
                $alts[$meta['postId']] = $meta['alt'];
            }

            foreach($thumbnails as $k => &$thumbnail) {
                if(!empty($alts[$thumbnail['attachId']])) {
                    $thumbnail['value'] = $alts[$thumbnail['attachId']];
                }
            }
        }

        return $thumbnails;

    }

    public static function setupPostThumbnailsByObject($arrayObjects, $propName, $meta_key = '_thumbnail_id', $get_attached_file = false): AttachmentsManager
    {

        $post_ids = [];
        foreach ($arrayObjects as $object) {
            $post_ids[] = $object->$propName;
        }

        return self::setupPostThumbnails($post_ids);

    }

    public static function setupPostThumbnails($post_ids, $meta_key = '_thumbnail_id', $get_attached_file = false): AttachmentsManager
    {

        $attachments = [];
        if ($thumbnails = self::getMetadataThumbnails($post_ids, $meta_key, $get_attached_file)) {

            $thumbnails = self::appendAlt($thumbnails);

            foreach ($thumbnails as $thumbnail) {
                $attachments[ $thumbnail['postId'] ] = new SingleAttachment($thumbnail['attachId'], StringService::maybeUnserialize($thumbnail['metadata']), !empty($thumbnail['attachedFile']), $thumbnail['alt'] ?? '');
            }
        }

        return new AttachmentsManager($attachments);

    }

    public static function setupAttachments($attach_ids): AttachmentsManager
    {

        $attachments = [];
        if ($thumbnails = self::getMetadataAttachments($attach_ids)) {

            $thumbnails = self::appendAlt($thumbnails);

            foreach ($thumbnails as $thumbnail) {
                $attachments[ $thumbnail['attachId'] ] = new SingleAttachment($thumbnail['attachId'], StringService::maybeUnserialize($thumbnail['metadata']), false, $thumbnail['alt'] ?? '');
            }
        }

        return new AttachmentsManager($attachments);

    }

    public static function getQueryAttachmentsRequest($attach_ids): QueryBuilder
    {

        return ORM::get()
            ->getRepository(PostMeta::class)
            ->createQueryBuilder('pm')
            ->addSelect('pm.value as metadata')
            ->addSelect('pm.postId as attachId')
            ->andWhere('pm.key=\'_wp_attachment_metadata\'')
            ->andWhere('pm.postId IN (:postIds)')
            ->setParameter('postIds', array_diff(array_unique($attach_ids), [ '' ]));

    }

    public static function getMetadataAttachments($attach_ids): ?array
    {

        if(!is_array($attach_ids)) {
            return null;
        }

        $attach_ids = array_values($attach_ids);

        if(empty($attach_ids)) {
            return null;
        }

        return self::getQueryAttachmentsRequest($attach_ids)->getQuery()->getResult();

    }

    public static function getQueryThumbnailsRequest($post_ids, $meta_key = '_thumbnail_id', $get_attached_file = false): QueryBuilder
    {

        $repo = ORM::get()->getRepository(PostMeta::class);

        $query = $repo->createQueryBuilder('pm')
           ->addSelect('pm.postId')
           ->addSelect('pm.value as attachId')
           ->addSelect('md.value as metadata')
           ->join(PostMeta::class, 'md', Join::WITH, 'pm.value=md.postId')
           ->andWhere('pm.key=:key')
           ->andWhere('pm.postId IN (:postIds)')
           ->andWhere('md.key=\'_wp_attachment_metadata\'')
           ->setParameter('key', $meta_key)
           ->setParameter('postIds', $post_ids);

        if ($get_attached_file) {
            $query
                ->join(PostMeta::class, 'md2', Join::WITH, 'pm.value=md2.postId')
                ->addSelect('md2.value as attachedFile')
                ->andWhere('md2.key=\'_wp_attached_file\'');
        }

        return $query;

    }

    public static function getMetadataThumbnails($post_ids, $meta_key = '_thumbnail_id', $get_attached_file = false): ?array
    {

        if(!is_array($post_ids)) {
            return null;
        }

        $post_ids = array_values($post_ids);

        if(empty($post_ids)) {
            return null;
        }

        return self::getQueryThumbnailsRequest($post_ids, $meta_key, $get_attached_file)->getQuery()->getResult();

    }

}
