<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/order')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_order',methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->json($orderRepository->findBy(["profile"=>$this->getUser()->getProfile()]),200,[],['groups'=>'group:order-all']);

    }
    #[Route('/make', name: 'app_order_make' ,methods: ['POST'])]
    public function make(Request $request,ProductRepository $productRepository,EntityManagerInterface $entityManager): Response
    {
        $json = $request->getContent();
        $decodedData = json_decode($json, true);

        $entityManager->beginTransaction(); // Début de la transaction

        $total = 0;
        $order = new Order();
        $order->setProfile($this->getUser()->getProfile());

        foreach ($decodedData as $item) {
            if (!isset($item['id']) || !is_array($item['id'])) {
                $entityManager->rollback(); // Annuler la transaction
                return $this->json(["error" => "Format JSON invalide"], 400);
            }

            foreach ($item['id'] as $productInfo) {
                // Vérifie si $productInfo est un tableau avec une seule paire clé-valeur
                if (!is_array($productInfo) || count($productInfo) !== 1) {
                    $entityManager->rollback(); // Annuler la transaction
                    return $this->json(["error" => "Format des informations produit invalide"], 400);
                }

                // Récupérer l'identifiant du produit et la quantité à partir de la paire clé-valeur
                $productId = key($productInfo); // Clé = identifiant du produit
                $newQuantity = current($productInfo); // Valeur = quantité du produit

                // Recherche du produit par son identifiant
                $product = $productRepository->find($productId);

                if (!$product) {
                    $entityManager->rollback(); // Annuler la transaction
                    return $this->json(["error" => "Produit avec l'ID $productId introuvable"], 404);
                }

                if ($newQuantity < 0) {
                    $entityManager->rollback(); // Annuler la transaction
                    return $this->json(["error" => "La quantité du produit $productId est inférieure à 0"], 400);
                }

                // Création de l'entité OrderProduct
                $orderProduct = new OrderProduct();
                $orderProduct->setProduct($product);
                $orderProduct->setQuantity($newQuantity);
                $orderProduct->setOrders($order);
                $orderProduct->setTotal($newQuantity * $product->getPrice());

                $entityManager->persist($orderProduct);

                $total += $orderProduct->getTotal();
            }
        }

        $order->setTotal($total);

        $entityManager->persist($order);
        $entityManager->flush();

        $entityManager->commit(); // Valider la transaction

        return $this->json($order, 200, [], ['groups' => 'group:order-all']);


    }
    #[Route('/admin', name: 'app_order_admin')]
    public function indexAdmin(OrderRepository $orderRepository): Response
    {
        if (! in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->json('User no admin', 200);
        }
        return $this->json($orderRepository->findAll(),200,[],['groups'=>'group:order-all']);
    }
    #[Route('/show/{id}', name: 'app_order_show',methods: ['GET'])]
    public function show(Order $order ,ProductRepository $productRepository): Response
    {
        return $this->json($order,200,[],['groups'=>'group:order-all']);

    }
    #[Route('/product/{id}', name: 'app_order_show_orderProduct',methods: ['GET'])]
    public function showOrderProduct(OrderProduct $orderProduct ,ProductRepository $productRepository): Response
    {
        return $this->json($orderProduct,200,[],['groups'=>'group:order-product']);

    }
}
