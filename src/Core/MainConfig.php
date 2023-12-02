<?php

namespace Core;

use Common\Command\FirstCommand;
use Common\Controller\PostController;
use Common\Cron\CronCheckMessage;
use Common\Cron\CronCheckMessageHandler;
use Common\Cron\CronService;
use Core\Controller\CronController;
use Core\Service\ImageService;

class MainConfig
{
    public const AMPQ_DNS = '';
    public const WORKER_AMPQ = 'ampq-async';
    public const TRANSPORT_AMPQ = 'ampq';
    public const TRANSPORT_DEFAULT = 'default';
    public const TRANSPORTS = [
        self::TRANSPORT_AMPQ,
        self::TRANSPORT_DEFAULT,
    ];

    public const AMPQ_MESSAGES = [
        self::TRANSPORT_AMPQ => [
            CronCheckMessage::class    => [
                CronCheckMessageHandler::class
            ],
        ],
    ];

    public const ATTACHMENT_SIZES = [
        ImageService::SIZE_SMALL => [
            'width'  => 150,
            'height' => 150,
            'crop'   => ['center', 'top'],
        ],
        ImageService::SIZE_MEDIUM => [
            'width'  => 350,
            'height' => 350,
            'crop'   => false,
        ],
        ImageService::SIZE_LARGE => [
            'width'  => 700,
            'height' => 700,
            'crop'   => false,
        ],
        //принудительно отключаем
        'large' => [
            'width'  => 0,
            'height' => 0,
            'crop'   => false,
        ],
        'medium_large' => [
            'width'  => 0,
            'height' => 0,
            'crop'   => false,
        ],
        'medium' => [
            'width'  => 0,
            'height' => 0,
            'crop'   => false,
        ],
    ];

    public const COMMANDS = [
        FirstCommand::class
    ];

    public const CONTROLLERS = [
        PostController::class,
	    CronController::class,
    ];

    public const TRANSFORMERS = [
//        UploadFileListDTO::class => [
//            UploadFileListDTOFromFilesArrayTransformer::class
//        ],
    ];

    public const CRON_TASKS = [

    ];

    public const ITERABLE_CONSTRUCT_PARAMS = [
        CronService::class => [
            'handlers' => self::CRON_TASKS
        ],
    ];

}
