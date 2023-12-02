<?php

namespace Common\Cron\TaskHandler;

use Common\Cron\CronTaskHandlerAbstract;
use Common\Entity\BlogPost;
use Common\Entity\CronTask;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @description параметры для блога
 */
class TestTaskHandler extends CronTaskHandlerAbstract
{
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getCode(): string
    {
        return 'test';
    }

    public function getPeriodSec(): int
    {
        return 600;
    }

    public function handle(CronTask $task): bool
    {
        global $wpdb;

        require_once(ABSPATH . 'cron-funcs.php');

        $query_blog = $wpdb->get_results("SELECT ID, post_content, post_title FROM wp_posts WHERE post_type = 'blog' AND post_status = 'publish'");

        foreach ($query_blog as $post) {
            $meta_ids_list[] = $post->ID;
        }

        $meta_keys = "'all_photo_count','all_video_count','journal_city', 'blogpost_cat'";

        $blog_metas = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE post_id IN ( "  . implode(',', $meta_ids_list) .   " ) AND meta_key IN (" . $meta_keys . ")");

        foreach ($blog_metas as $meta) {
            $metas[$meta->post_id][$meta->meta_key] = is_serial($meta->meta_value) ? unserialize($meta->meta_value) : $meta->meta_value;
        }

        $zero_terms = [];

        $repo = $this->entityManager->getRepository(BlogPost::class);

        foreach($query_blog as $key => $blog) {

            /** @var BlogPost $blogPost */
            $blogPost = $repo->find($blog->ID);

            $content   = $blog->post_content;
            $file_urls = get_file_urls($content);
            $nbImg     = $file_urls[ 'total_unique' ];

            if(@$metas[$blog->ID]['all_photo_count'] != $nbImg) {
                if(isset($metas[$blog->ID]['all_photo_count'])) {
                    wp_update_postmeta(array($blog->ID => $nbImg), 'all_photo_count');
                } else {
                    wp_insert_postmeta(array($blog->ID => $nbImg), 'all_photo_count');
                }
            }

            $blogPost->setCntPhoto((int) $nbImg);

            $file_urls = get_file_video_urls($content);
            $nbImg     = $file_urls[ 'total_unique' ];

            if(@$metas[$blog->ID]['all_video_count'] != $nbImg) {
                if(isset($metas[$blog->ID]['all_video_count'])) {
                    wp_update_postmeta(array($blog->ID => $nbImg), 'all_video_count');
                } else {
                    wp_insert_postmeta(array($blog->ID => $nbImg), 'all_video_count');
                }
            }

            $blogPost->setCntVideo((int) $nbImg);

            if(!isset($metas[$blog->ID]['journal_city']) || 'all' != $metas[$blog->ID]['journal_city']) {
                continue;
            }


            if(isset($metas[$blog->ID]['blogpost_cat']) && !in_array($metas[$blog->ID]['blogpost_cat'], $zero_terms)) {
                $zero_terms[] = $metas[$blog->ID]['blogpost_cat'];
                if(is_array($metas[$blog->ID]['blogpost_cat'])) {
                    foreach($metas[$blog->ID]['blogpost_cat'] as $id) {
                        $zero_terms[] = $id;
                    }
                } else {
                    $zero_terms[] = $metas[$blog->ID]['blogpost_cat'];
                }
            }


        }

        $querystr_city = "
		    SELECT P.*
		    FROM $wpdb->posts P
		    WHERE P.post_status = 'publish'
		    AND P.post_type = 'city'
		";

        $cities = $wpdb->get_results($querystr_city);

        foreach ($cities as $city) {

            $all_terms = $zero_terms;

            foreach ($query_blog as $post) {

                $post_id = $post->ID;

                if(!isset($metas[$post_id]['journal_city']) || $metas[$post_id]['journal_city'] != $city->ID) {
                    continue;
                }

                if(!empty($metas[$post_id]['blogpost_cat']) && !in_array($metas[$post_id]['blogpost_cat'], $all_terms)) {
                    if(is_array($metas[$post_id]['blogpost_cat'])) {
                        foreach($metas[$post_id]['blogpost_cat'] as $id) {
                            $all_terms[] = $id;
                        }
                    } else {
                        $all_terms[] = $metas[$post_id]['blogpost_cat'];
                    }
                }

            }

            $all_terms_str = implode(',', array_unique($all_terms));

            my_update_option('all_blog_tax_' . $city->ID, $all_terms_str);

        }

        $this->entityManager->flush();

        return true;
    }

}
