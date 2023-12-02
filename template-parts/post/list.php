<?php

use Core\Entity\Post\Post;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @var ArrayCollection $posts
 */

?>
<ul>
<?php
/** @var Post $post */
foreach($posts as $post){ ?>
	<li><?php echo $post->getPostTitle() ?></li>
<?php } ?>
</ul>
