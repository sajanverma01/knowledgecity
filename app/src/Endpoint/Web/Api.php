<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Entity\Order;
use Cycle\ORM\ORMInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final class Api
{
    private ResponseWrapper $response;
    
    public function __construct(ResponseWrapper $response)
    {
        $this->response = $response;
    }
    
    #[Route(route: '/show-orders', name: 'show-orders', methods: 'GET')]
    public function showOrders(ORMInterface $orm): \Psr\Http\Message\ResponseInterface
    {
        $repository = $orm->getRepository(Order::class);
        
        $orders = $repository->findAll();
        $data = array_map(fn(Order $order) => $order->toArray(), $orders);
        
        return $this->response->json(['data' => $data]);
    }
}