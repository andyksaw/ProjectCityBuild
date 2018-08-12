<?php
namespace Domains\Services\Queries\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Domains\Library\QueryServer\ServerQueryHandler;
use Domains\Services\Queries\ServerQueryService;
use Domains\Services\Queries\Entities\ServerJobEntity;

class ServerQueryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ServerJobEntity
     */
    private $entity;
    
    /**
     * Create a new job instance.
     *
     * @param ServerJobEntity $entity
     */
    public function __construct(ServerJobEntity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Execute the job.
     *
     * @param ServerQueryHandler $serverQueryHandler
     * @return void
     */
    public function handle(ServerQueryHandler $serverQueryHandler)
    {
        $serverQueryHandler->setAdapter($this->entity->getServerQueryAdapter());
        $status = $serverQueryHandler->queryServer($this->entity->getServerId(), 
                                                   $this->entity->getIp(), 
                                                   $this->entity->getPort());

        ServerQueryService::processServerResult($this->entity, $status);
    }
}