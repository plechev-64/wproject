<?php

namespace Common\Controller;

use Core\Entity\Post\Post;
use Core\Attributes\Param;
use Core\Attributes\Route;
use Core\Model\ManagerIncomeModel;
use Core\ObjectManager\PostManager;
use Core\Rest\ControllerAbstract;
use Core\Rest\Response;
use Core\Service\ManagerService;
use Core\Template;
use JetBrains\PhpStorm\Pure;

#[Route(path: '/post')]
class PostController extends ControllerAbstract
{
    #[Pure] #[Route(path: '/{post}')]
    #[Param(name: 'post', entity: Post::class)]
    public function get(
        Post $post
    ) {
        return $this->response($post);
    }

	#[Route(path: '/list', method: 'POST')]
	#[Param(name: 'page', type: 'int')]
	#[Param(name: 'number', type: 'int')]
	#[Param(name: 'filters', type: 'array')]
	#[Param(name: 'isShuffle', type: 'bool')]
	#[Param(name: 'sort', type: 'array')]
	#[Param(name: 'queue', type: 'array')]
	#[Param(name: 'args', type: 'array')]
	public function getList(
		ManagerIncomeModel $managerModel,
		PostManager $manager,
		ManagerService $managerService
	): Response {

		$posts = $managerService->getDataByIncomeModel($manager, $managerModel);

		if (!$posts->count()) {
			return $this->error('Ничего не найдено', 404);
		}

		$args = [
			'posts' => $posts
		];

		if(!empty($managerModel->args['args'])) {
			$args = array_merge($args, $managerModel->args['args']);
		}

		$template = 'template-parts/post/list.php';
		if(!empty($managerModel->args['template'])) {
			$template = $managerModel->args['template'];
		}

		return $this->responsePager(
			Template::get($template, $args),
			$posts->count()
		);
    }

}
